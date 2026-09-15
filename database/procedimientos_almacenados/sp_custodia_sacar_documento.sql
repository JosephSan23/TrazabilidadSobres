-- Procedimiento del módulo de custodia de sobres.
-- Ejecutar después de schema-custodia-sobres.sql.
-- La aplicación debe usar este procedimiento y no actualizar las tablas de custodia directamente.

--4

DELIMITER $$

CREATE PROCEDURE sp_custodia_sacar_documento(
    IN p_id_sobre INT,
    IN p_id_documento INT,
    IN p_estado VARCHAR(30),
    IN p_id_ubicacion INT,
    IN p_nombre_custodio VARCHAR(150),
    IN p_id_usuario_custodia INT,
    IN p_fecha_devolucion_esperada DATE,
    IN p_id_usuario_registra INT,
    IN p_nombre_usuario_registra VARCHAR(150),
    IN p_observaciones TEXT
)
BEGIN
    DECLARE v_id_tramite INT;
    DECLARE v_ubicacion_sobre INT;
    DECLARE v_responsable_sobre VARCHAR(150);
    DECLARE v_id_custodia INT;
    DECLARE v_ubicacion_activa INT DEFAULT 0;
    DECLARE v_documento_validado INT DEFAULT 0;
    DECLARE v_evento VARCHAR(40);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    IF p_estado NOT IN ('PRESTADO', 'ENVIADO_ENTIDAD', 'ENTREGADO_CLIENTE', 'EXTRAVIADO') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El estado de salida del documento no es válido.';
    END IF;

    IF p_id_ubicacion IS NULL AND (p_nombre_custodio IS NULL OR TRIM(p_nombre_custodio) = '') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El documento debe quedar con ubicación o custodio.';
    END IF;

    IF p_estado IN ('ENVIADO_ENTIDAD', 'ENTREGADO_CLIENTE', 'EXTRAVIADO')
       AND (p_observaciones IS NULL OR TRIM(p_observaciones) = '') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La salida indicada requiere una observación.';
    END IF;

    START TRANSACTION;

    SELECT id_tramite, id_ubicacion, nombre_responsable
    INTO v_id_tramite, v_ubicacion_sobre, v_responsable_sobre
    FROM sobre
    WHERE id_sobre = p_id_sobre
    FOR UPDATE;

    IF v_id_tramite IS NULL THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El sobre indicado no existe.';
    END IF;

    SELECT COUNT(*) INTO v_documento_validado
    FROM ValidacionDocumentos
    WHERE IdTramite = v_id_tramite
      AND Id_DocumentoTramite = p_id_documento
      AND Validacion = 1;

    IF v_documento_validado = 0 THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Solo puede salir un documento recibido y validado para el trámite.';
    END IF;

    IF EXISTS (
        SELECT 1 FROM sobre_documento_custodia
        WHERE id_sobre = p_id_sobre AND id_documento = p_id_documento AND vigente = 1
    ) THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El documento ya tiene una custodia vigente.';
    END IF;

    IF p_id_ubicacion IS NOT NULL THEN
        SELECT COUNT(*) INTO v_ubicacion_activa
        FROM ubicaciones
        WHERE id_ubicacion = p_id_ubicacion AND activo = 1;
        IF v_ubicacion_activa = 0 THEN
            ROLLBACK;
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La ubicación destino no existe o está inactiva.';
        END IF;
    END IF;

    INSERT INTO sobre_documento_custodia (
        id_sobre, id_documento, estado, id_ubicacion, nombre_custodio,
        id_usuario_custodia, fecha_devolucion_esperada, observaciones
    ) VALUES (
        p_id_sobre, p_id_documento, p_estado, p_id_ubicacion, NULLIF(TRIM(p_nombre_custodio), ''),
        p_id_usuario_custodia, p_fecha_devolucion_esperada, p_observaciones
    );

    SET v_id_custodia = LAST_INSERT_ID();
    SET v_evento = CASE p_estado
        WHEN 'PRESTADO' THEN 'PRESTAMO_DOCUMENTO'
        WHEN 'ENVIADO_ENTIDAD' THEN 'ENVIO_ENTIDAD'
        WHEN 'ENTREGADO_CLIENTE' THEN 'ENTREGA_CLIENTE'
        WHEN 'EXTRAVIADO' THEN 'EXTRAVIO_DOCUMENTO'
    END;

    INSERT INTO historial_movimientos (
        id_sobre, id_documento, id_custodia, tipo_evento,
        id_ubicacion_origen, id_ubicacion_destino,
        nombre_responsable_origen, nombre_responsable_destino,
        estado_nuevo, id_usuario_registra, nombre_usuario_registra, observaciones
    ) VALUES (
        p_id_sobre, p_id_documento, v_id_custodia, v_evento,
        v_ubicacion_sobre, p_id_ubicacion,
        v_responsable_sobre, NULLIF(TRIM(p_nombre_custodio), ''),
        p_estado, p_id_usuario_registra, NULLIF(TRIM(p_nombre_usuario_registra), ''), p_observaciones
    );

    COMMIT;
    SELECT v_id_custodia AS id_custodia;
END$$

DELIMITER ;