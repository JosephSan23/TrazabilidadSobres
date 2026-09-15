-- Procedimiento del módulo de custodia de sobres.
-- Ejecutar después de schema-custodia-sobres.sql.
-- La aplicación debe usar este procedimiento y no actualizar las tablas de custodia directamente.
--1
DELIMITER $$

CREATE PROCEDURE sp_custodia_crear_sobre(
    IN p_id_tramite INT,
    IN p_id_ubicacion INT,
    IN p_nombre_responsable VARCHAR(150),
    IN p_id_usuario_responsable INT,
    IN p_id_usuario_registra INT,
    IN p_nombre_usuario_registra VARCHAR(150),
    IN p_observaciones TEXT
)
BEGIN
    DECLARE v_id_sobre INT;
    DECLARE v_codigo_sobre VARCHAR(40);
    DECLARE v_ubicacion_activa INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    IF p_id_ubicacion IS NULL AND (p_nombre_responsable IS NULL OR TRIM(p_nombre_responsable) = '') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El sobre debe tener una ubicación o un responsable.';
    END IF;

    IF EXISTS (SELECT 1 FROM sobre WHERE id_tramite = p_id_tramite) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El trámite ya tiene un sobre registrado.';
    END IF;

    IF p_id_ubicacion IS NOT NULL THEN
        SELECT COUNT(*) INTO v_ubicacion_activa
        FROM ubicaciones
        WHERE id_ubicacion = p_id_ubicacion AND activo = 1;
        IF v_ubicacion_activa = 0 THEN
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La ubicación indicada no existe o está inactiva.';
        END IF;
    END IF;

    START TRANSACTION;

    -- Se usa un código temporal para obtener el consecutivo del sobre y luego se reemplaza.
    INSERT INTO sobre (
        id_tramite, codigo_sobre, id_ubicacion, nombre_responsable,
        id_usuario_responsable, estado, observaciones
    ) VALUES (
        p_id_tramite, CONCAT('TMP-', UUID()), p_id_ubicacion, NULLIF(TRIM(p_nombre_responsable), ''),
        p_id_usuario_responsable, 'CREADO', p_observaciones
    );

    SET v_id_sobre = LAST_INSERT_ID();
    SET v_codigo_sobre = CONCAT('SOB-', YEAR(CURDATE()), '-', LPAD(v_id_sobre, 5, '0'));

    UPDATE sobre
    SET codigo_sobre = v_codigo_sobre,
        fecha_ultimo_movimiento = CURRENT_TIMESTAMP
    WHERE id_sobre = v_id_sobre;

    INSERT INTO historial_movimientos (
        id_sobre, tipo_evento, id_ubicacion_destino, nombre_responsable_destino,
        estado_nuevo, id_usuario_registra, nombre_usuario_registra, observaciones
    ) VALUES (
        v_id_sobre, 'CREACION_SOBRE', p_id_ubicacion, NULLIF(TRIM(p_nombre_responsable), ''),
        'CREADO', p_id_usuario_registra, NULLIF(TRIM(p_nombre_usuario_registra), ''), p_observaciones
    );

    COMMIT;

    SELECT v_id_sobre AS id_sobre, v_codigo_sobre AS codigo_sobre;
END$$

DELIMITER ;