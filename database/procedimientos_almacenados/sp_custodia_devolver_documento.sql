-- Procedimiento del módulo de custodia de sobres.
-- Ejecutar después de schema-custodia-sobres.sql.
-- La aplicación debe usar este procedimiento y no actualizar las tablas de custodia directamente.

--5
DELIMITER $$

CREATE PROCEDURE sp_custodia_devolver_documento(
    IN p_id_custodia INT,
    IN p_id_usuario_registra INT,
    IN p_nombre_usuario_registra VARCHAR(150),
    IN p_observaciones TEXT
)
BEGIN
    DECLARE v_id_sobre INT;
    DECLARE v_id_documento INT;
    DECLARE v_ubicacion_origen INT;
    DECLARE v_custodio_origen VARCHAR(150);
    DECLARE v_estado_origen VARCHAR(30);
    DECLARE v_ubicacion_sobre INT;
    DECLARE v_responsable_sobre VARCHAR(150);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT id_sobre, id_documento, id_ubicacion, nombre_custodio, estado
    INTO v_id_sobre, v_id_documento, v_ubicacion_origen, v_custodio_origen, v_estado_origen
    FROM sobre_documento_custodia
    WHERE id_custodia = p_id_custodia AND vigente = 1
    FOR UPDATE;

    IF v_id_sobre IS NULL THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La custodia indicada no existe o ya fue cerrada.';
    END IF;

    SELECT id_ubicacion, nombre_responsable
    INTO v_ubicacion_sobre, v_responsable_sobre
    FROM sobre
    WHERE id_sobre = v_id_sobre
    FOR UPDATE;

    UPDATE sobre_documento_custodia
    SET estado = 'DEVUELTO',
        vigente = 0,
        fecha_devolucion_real = CURRENT_TIMESTAMP
    WHERE id_custodia = p_id_custodia;

    INSERT INTO historial_movimientos (
        id_sobre, id_documento, id_custodia, tipo_evento,
        id_ubicacion_origen, id_ubicacion_destino,
        nombre_responsable_origen, nombre_responsable_destino,
        estado_anterior, estado_nuevo,
        id_usuario_registra, nombre_usuario_registra, observaciones
    ) VALUES (
        v_id_sobre, v_id_documento, p_id_custodia, 'DEVOLUCION_DOCUMENTO',
        v_ubicacion_origen, v_ubicacion_sobre,
        v_custodio_origen, v_responsable_sobre,
        v_estado_origen, 'DEVUELTO',
        p_id_usuario_registra, NULLIF(TRIM(p_nombre_usuario_registra), ''), p_observaciones
    );

    COMMIT;
END$$

DELIMITER ;