-- Migración: Agregar columna tipo_asignacion a asignaciones_establecimientos
-- Fecha: 2026-05-20
-- Descripción: Distinguir entre asignaciones anuales (base) y reasignaciones temporales (override por meses)

USE observaciones_rem;

-- Agregar columna tipo_asignacion
ALTER TABLE asignaciones_establecimientos 
ADD COLUMN tipo_asignacion ENUM('anual', 'temporal') DEFAULT 'anual' 
AFTER meses;

-- Actualizar registros existentes a 'anual' por defecto
UPDATE asignaciones_establecimientos 
SET tipo_asignacion = 'anual' 
WHERE tipo_asignacion IS NULL OR tipo_asignacion = '';

-- Agregar índice compuesto para optimizar consultas por tipo
ALTER TABLE asignaciones_establecimientos 
ADD INDEX idx_establecimiento_anio_tipo (establecimiento_id, anio, tipo_asignacion);

-- Verificar la migración
SELECT 
    COUNT(*) as total_registros,
    SUM(CASE WHEN tipo_asignacion = 'anual' THEN 1 ELSE 0 END) as anuales,
    SUM(CASE WHEN tipo_asignacion = 'temporal' THEN 1 ELSE 0 END) as temporales
FROM asignaciones_establecimientos;
