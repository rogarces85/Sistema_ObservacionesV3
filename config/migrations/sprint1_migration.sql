-- Sprint 1: Auditoría de Usuarios
-- Crear tabla de historial de cambios en usuarios

CREATE TABLE IF NOT EXISTS `historial_usuarios` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `usuario_responsable_id` INT DEFAULT NULL,
  `accion` VARCHAR(50) NOT NULL COMMENT 'CREACION, ACTUALIZACION, CAMBIO_PASSWORD, ACTIVADO, DESACTIVADO, ELIMINACION',
  `detalles` TEXT DEFAULT NULL,
  `fecha_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`usuario_responsable_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sprint 1: Ajuste de Política de Contraseñas
-- Nota: Este cambio es a nivel de código (PHP), no requiere alteración de tabla.
-- Se recomienda ejecutar este query para invalidar contraseñas antiguas que no cumplan la política
-- y forzar a los usuarios a cambiarlas en el próximo login (opcional).
-- UPDATE usuarios SET password_hash = password_hash WHERE 1=1; -- No ejecutar sin lógica de forzado.

-- Sprint 1: Nombre Corto Opcional
-- Asegurar que la columna nombre_corto permita NULL si no lo permite actualmente
ALTER TABLE `establecimientos` MODIFY COLUMN `nombre_corto` VARCHAR(100) DEFAULT NULL;
