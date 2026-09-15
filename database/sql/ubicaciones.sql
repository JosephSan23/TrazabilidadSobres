-- 1
CREATE TABLE IF NOT EXISTS ubicaciones (
    id_ubicacion INT NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(30) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    descripcion VARCHAR(500) NULL,
    tipo ENUM('INTERNA', 'EXTERNA') NOT NULL DEFAULT 'INTERNA',
    id_ubicacion_padre INT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_ubicacion),
    UNIQUE KEY uq_ubicaciones_codigo (codigo),
    KEY ix_ubicaciones_padre (id_ubicacion_padre),
    KEY ix_ubicaciones_activo_tipo (activo, tipo),
    CONSTRAINT fk_ubicaciones_padre
        FOREIGN KEY (id_ubicacion_padre) REFERENCES ubicaciones(id_ubicacion)
        ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;