-- Procedimiento del módulo de custodia de sobres.
-- Ejecutar después de schema-custodia-sobres.sql.
-- La aplicación debe usar este procedimiento y no actualizar las tablas de custodia directamente.


-- 2
DELIMITER $$

CREATE PROCEDURE sp_custodia_mover_sobre(
    IN p_id_sobre INT,
    IN p_id_ubicacion_destino INT,
    IN p_nombre_responsable_destino VARCHAR(150),
    IN p_id_usuario_responsable_destino INT,
    IN p_id_usuario_registra INT,
    IN p_nombre_usuario_registra VARCHAR(150),
    IN p_observaciones TEXT
)
BEGIN
    DECLARE v_ubicacion_origen INT;
    DECLARE v_responsable_origen VARCHAR(150);
    DECLARE v_responsable_destino VARCHAR(150);
    DECLARE v_ubicacion_final INT;
    DECLARE v_evento VARCHAR(40);
    DECLARE v_ubicacion_activa INT DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT id_ubicacion, nombre_responsable
    INTO v_ubicacion_origen, v_responsable_origen
    FROM sobre
    WHERE id_sobre = p_id_sobre
    FOR UPDATE;

    IF v_ubicacion_origen IS NULL AND v_responsable_origen IS NULL THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El sobre indicado no existe.';
    END IF;

    IF p_id_ubicacion_destino IS NOT NULL THEN
        SELECT COUNT(*) INTO v_ubicacion_activa
        FROM ubicaciones
        WHERE id_ubicacion = p_id_ubicacion_destino AND activo = 1;
        IF v_ubicacion_activa = 0 THEN
            ROLLBACK;
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La ubicación destino no existe o está inactiva.';
        END IF;
    END IF;

    SET v_ubicacion_final = COALESCE(p_id_ubicacion_destino, v_ubicacion_origen);
    SET v_responsable_destino = COALESCE(NULLIF(TRIM(p_nombre_responsable_destino), ''), v_responsable_origen);

    IF v_ubicacion_final IS NULL AND v_responsable_destino IS NULL THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El sobre no puede quedar sin ubicación y sin responsable.';
    END IF;

    SET v_evento = CASE
        WHEN NOT (p_id_ubicacion_destino <=> v_ubicacion_origen) THEN 'CAMBIO_UBICACION_SOBRE'
        ELSE 'CAMBIO_RESPONSABLE_SOBRE'
    END;

    UPDATE sobre
    SET id_ubicacion = v_ubicacion_final,
        nombre_responsable = v_responsable_destino,
        id_usuario_responsable = COALESCE(p_id_usuario_responsable_destino, id_usuario_responsable),
        fecha_ultimo_movimiento = CURRENT_TIMESTAMP
    WHERE id_sobre = p_id_sobre;

    INSERT INTO historial_movimientos (
        id_sobre, tipo_evento, id_ubicacion_origen, id_ubicacion_destino,
        nombre_responsable_origen, nombre_responsable_destino,
        id_usuario_registra, nombre_usuario_registra, observaciones
    ) VALUES (
        p_id_sobre, v_evento, v_ubicacion_origen, v_ubicacion_final,
        v_responsable_origen, v_responsable_destino,
        p_id_usuario_registra, NULLIF(TRIM(p_nombre_usuario_registra), ''), p_observaciones
    );

    COMMIT;
END$$

DELIMITER ;