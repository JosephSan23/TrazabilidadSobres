-- 2
CREATE TABLE IF NOT EXISTS sobre (
    id_sobre INT NOT NULL AUTO_INCREMENT,
    id_tramite INT NOT NULL,
    codigo_sobre VARCHAR(40) NOT NULL,
    id_ubicacion INT NULL,
    nombre_responsable VARCHAR(150) NULL,
    id_usuario_responsable INT NULL,
    estado ENUM('CREADO', 'GESTION', 'INCOMPLETO', 'RADICADO', 'FINALIZADO') NOT NULL DEFAULT 'CREADO',
    observaciones TEXT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_ultimo_movimiento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_sobre),
    UNIQUE KEY uq_sobre_tramite (id_tramite),
    UNIQUE KEY uq_sobre_codigo (codigo_sobre),
    KEY ix_sobre_ubicacion (id_ubicacion),
    KEY ix_sobre_responsable (nombre_responsable),
    KEY ix_sobre_estado (estado),
    CONSTRAINT fk_sobre_tramite
        FOREIGN KEY (id_tramite) REFERENCES tramite(id_tramite)
        ON UPDATE RESTRICT ON DELETE RESTRICT,
    CONSTRAINT fk_sobre_ubicacion
        FOREIGN KEY (id_ubicacion) REFERENCES ubicaciones(id_ubicacion)
        ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;