-- Sprint 4: Exportación Asíncrona
-- Tabla para gestionar la cola de reportes

CREATE TABLE IF NOT EXISTS `reportes_pendientes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usuario_id` INT NOT NULL,
  `tipo_reporte` VARCHAR(50) NOT NULL,
  `formato` VARCHAR(10) NOT NULL,
  `parametros` TEXT DEFAULT NULL COMMENT 'JSON con filtros y configuración',
  `estado` ENUM('PENDIENTE', 'PROCESANDO', 'LISTO', 'ERROR') DEFAULT 'PENDIENTE',
  `archivo_url` VARCHAR(255) DEFAULT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_procesamiento` TIMESTAMP NULL DEFAULT NULL,
  `mensaje_error` TEXT DEFAULT NULL,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
