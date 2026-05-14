-- Sprint 3: Papelera de Observaciones Eliminadas
-- Crear tabla de observaciones eliminadas (soft-delete)

CREATE TABLE IF NOT EXISTS `observaciones_eliminadas` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `observacion_id` INT NOT NULL,
  `anio` INT NOT NULL,
  `mes` VARCHAR(20) NOT NULL,
  `establecimiento_id` INT NOT NULL,
  `establecimiento_nombre` VARCHAR(200) NOT NULL,
  `establecimiento_nombre_corto` VARCHAR(50) DEFAULT NULL,
  `comuna` VARCHAR(100) NOT NULL,
  `codigo_serie` VARCHAR(50) NOT NULL,
  `codigo_hoja` VARCHAR(50) NOT NULL,
  `tipo_error` VARCHAR(100) NOT NULL,
  `detalle_observacion` TEXT NOT NULL,
  `plazo_entrega` ENUM('dentro_plazo', 'fuera_plazo') NOT NULL,
  `usa_validador` ENUM('si', 'no') NOT NULL,
  `estado_actual` VARCHAR(50) NOT NULL,
  `clasificacion` VARCHAR(200) DEFAULT NULL,
  `usuario_registro_id` INT NOT NULL,
  `nombre_registro` VARCHAR(100) NOT NULL,
  `usuario_supervisor_id` INT DEFAULT NULL COMMENT 'Supervisor que eliminó',
  `motivo_eliminacion` TEXT DEFAULT NULL,
  `fecha_eliminacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `fecha_registro_original` TIMESTAMP NULL DEFAULT NULL,
  `fecha_revision` TIMESTAMP NULL DEFAULT NULL,
  INDEX idx_observacion_id (`observacion_id`),
  INDEX idx_anio (`anio`),
  INDEX idx_mes (`mes`),
  INDEX idx_establecimiento_id (`establecimiento_id`),
  INDEX idx_comuna (`comuna`),
  INDEX idx_usuario_registro_id (`usuario_registro_id`),
  INDEX idx_fecha_eliminacion (`fecha_eliminacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sprint 3: Índice compuesto para filtros frecuentes
CREATE INDEX idx_filtros_eliminadas ON `observaciones_eliminadas` (`anio`, `mes`, `establecimiento_id`);
