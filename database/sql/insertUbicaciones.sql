INSERT INTO ubicaciones (codigo, nombre, descripcion, tipo, activo)
VALUES ('PEND-UBICAR', 'Pendiente de ubicar', 'Ubicación temporal para sobres existentes aún no levantados físicamente.', 'INTERNA', 1)
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    descripcion = VALUES(descripcion),
    activo = 1;
