-- Sprint 2: Asignaciones Mensuales
-- Agregar columna para definir meses específicos de la asignación

ALTER TABLE `asignaciones_establecimientos` 
ADD COLUMN `meses` VARCHAR(50) DEFAULT 'ALL' COMMENT 'ALL para todo el año, o lista de IDs de meses ej: 1,2,3' AFTER `anio`;

-- Sprint 2: Observaciones - Bloqueo de Supervisor
-- Nota: Este cambio es a nivel de código (PHP), no requiere alteración de tabla.
