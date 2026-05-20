-- Tabla de asignaciones de establecimientos a registradores
-- Ejecutar este script para crear la tabla necesaria

USE observaciones_rem;

CREATE TABLE IF NOT EXISTS asignaciones_establecimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    establecimiento_id INT NOT NULL,
    anio INT NOT NULL,
    meses VARCHAR(50) DEFAULT 'ALL' COMMENT 'ALL para todo el año, o lista de IDs de meses ej: 1,2,3',
    tipo_asignacion ENUM('anual', 'temporal') DEFAULT 'anual' COMMENT 'anual: asignación base para el año, temporal: reasignación por meses específicos',
    fecha_asignacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (establecimiento_id) REFERENCES establecimientos(id) ON DELETE CASCADE,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_establecimiento_id (establecimiento_id),
    INDEX idx_anio (anio),
    INDEX idx_establecimiento_anio_tipo (establecimiento_id, anio, tipo_asignacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
