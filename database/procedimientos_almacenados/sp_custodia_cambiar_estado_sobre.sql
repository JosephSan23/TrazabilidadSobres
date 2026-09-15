-- Procedimiento del módulo de custodia de sobres.
-- Ejecutar después de schema-custodia-sobres.sql.
-- La aplicación debe usar este procedimiento y no actualizar las tablas de custodia directamente.

--3

DELIMITER $$

CREATE PROCEDURE sp_custodia_cambiar_estado_sobre(
    IN p_id_sobre INT,
    IN p_estado_nuevo VARCHAR(30),
    IN p_id_usuario_registra INT,
    IN p_nombre_usuario_registra VARCHAR(150),
    IN p_observaciones TEXT
)
BEGIN
    DECLARE v_estado_anterior VARCHAR(30);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    IF p_estado_nuevo NOT IN ('CREADO', 'GESTION', 'INCOMPLETO', 'RADICADO', 'FINALIZADO') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El estado nuevo no es válido.';
    END IF;

    START TRANSACTION;

    SELECT estado INTO v_estado_anterior
    FROM sobre
    WHERE id_sobre = p_id_sobre
    FOR UPDATE;

    IF v_estado_anterior IS NULL THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El sobre indicado no existe.';
    END IF;

    UPDATE sobre
    SET estado = p_estado_nuevo,
        fecha_ultimo_movimiento = CURRENT_TIMESTAMP
    WHERE id_sobre = p_id_sobre;

    INSERT INTO historial_movimientos (
        id_sobre, tipo_evento, estado_anterior, estado_nuevo,
        id_usuario_registra, nombre_usuario_registra, observaciones
    ) VALUES (
        p_id_sobre, 'CAMBIO_ESTADO_SOBRE', v_estado_anterior, p_estado_nuevo,
        p_id_usuario_registra, NULLIF(TRIM(p_nombre_usuario_registra), ''), p_observaciones
    );

    COMMIT;
END$$

DELIMITER ;