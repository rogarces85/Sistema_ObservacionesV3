-- Sprint 5: Sistema de Versionado
-- Tabla para registrar versiones del sistema

CREATE TABLE IF NOT EXISTS `versiones_sistema` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version_tag` VARCHAR(50) NOT NULL UNIQUE,
  `descripcion` TEXT NOT NULL,
  `usuario_id` INT NOT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `snapshot_path` VARCHAR(255) NOT NULL COMMENT 'Ruta relativa al directorio del snapshot',
  `archivos_json` JSON DEFAULT NULL COMMENT 'Manifiesto de archivos incluidos (rutas y hashes)',
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
