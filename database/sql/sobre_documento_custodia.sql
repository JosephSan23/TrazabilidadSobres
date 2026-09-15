-- 3
CREATE TABLE IF NOT EXISTS sobre_documento_custodia (
    id_custodia INT NOT NULL AUTO_INCREMENT,
    id_sobre INT NOT NULL,
    id_documento INT NOT NULL,
    estado ENUM('PRESTADO', 'ENVIADO_ENTIDAD', 'ENTREGADO_CLIENTE', 'EXTRAVIADO', 'DEVUELTO') NOT NULL,
    id_ubicacion INT NULL,
    nombre_custodio VARCHAR(150) NULL,
    id_usuario_custodia INT NULL,
    fecha_salida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_devolucion_esperada DATE NULL,
    fecha_devolucion_real DATETIME NULL,
    vigente TINYINT(1) NOT NULL DEFAULT 1,
    -- Permite una sola custodia vigente por documento y sobre,
    -- pero conserva múltiples salidas históricas ya cerradas.
    id_documento_vigente INT GENERATED ALWAYS AS (
        CASE WHEN vigente = 1 THEN id_documento ELSE NULL END
    ) STORED,
    observaciones TEXT NULL,
    PRIMARY KEY (id_custodia),
    UNIQUE KEY uq_custodia_documento_vigente (id_sobre, id_documento_vigente),
    KEY ix_custodia_sobre_vigente (id_sobre, vigente),
    KEY ix_custodia_documento_vigente (id_documento, vigente),
    KEY ix_custodia_ubicacion (id_ubicacion),
    KEY ix_custodia_fecha_esperada (fecha_devolucion_esperada),
    CONSTRAINT fk_custodia_sobre
        FOREIGN KEY (id_sobre) REFERENCES sobre(id_sobre)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_custodia_documento
        FOREIGN KEY (id_documento) REFERENCES Documentos(Id_documento)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_custodia_ubicacion
        FOREIGN KEY (id_ubicacion) REFERENCES ubicaciones(id_ubicacion)
        ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;