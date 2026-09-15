-- 4
CREATE TABLE IF NOT EXISTS historial_movimientos (
    id_movimiento BIGINT NOT NULL AUTO_INCREMENT,
    id_sobre INT NOT NULL,
    id_documento INT NULL,
    id_custodia INT NULL,
    tipo_evento ENUM(
        'CREACION_SOBRE',
        'CAMBIO_UBICACION_SOBRE',
        'CAMBIO_RESPONSABLE_SOBRE',
        'CAMBIO_ESTADO_SOBRE',
        'PRESTAMO_DOCUMENTO',
        'DEVOLUCION_DOCUMENTO',
        'ENVIO_ENTIDAD',
        'ENTREGA_CLIENTE',
        'EXTRAVIO_DOCUMENTO',
        'CARGA_INICIAL',
        'INVENTARIO_FISICO'
    ) NOT NULL,
    id_ubicacion_origen INT NULL,
    id_ubicacion_destino INT NULL,
    nombre_responsable_origen VARCHAR(150) NULL,
    nombre_responsable_destino VARCHAR(150) NULL,
    estado_anterior VARCHAR(30) NULL,
    estado_nuevo VARCHAR(30) NULL,
    id_usuario_registra INT NULL,
    nombre_usuario_registra VARCHAR(150) NULL,
    fecha_evento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    observaciones TEXT NULL,
    PRIMARY KEY (id_movimiento),
    KEY ix_historial_sobre_fecha (id_sobre, fecha_evento),
    KEY ix_historial_documento_fecha (id_documento, fecha_evento),
    KEY ix_historial_custodia (id_custodia),
    KEY ix_historial_tipo_fecha (tipo_evento, fecha_evento),
    CONSTRAINT fk_historial_sobre
        FOREIGN KEY (id_sobre) REFERENCES sobre(id_sobre)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_historial_documento
        FOREIGN KEY (id_documento) REFERENCES Documentos(Id_documento)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_historial_custodia
        FOREIGN KEY (id_custodia) REFERENCES sobre_documento_custodia(id_custodia)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_historial_ubicacion_origen
        FOREIGN KEY (id_ubicacion_origen) REFERENCES ubicaciones(id_ubicacion)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_historial_ubicacion_destino
        FOREIGN KEY (id_ubicacion_destino) REFERENCES ubicaciones(id_ubicacion)
        ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
