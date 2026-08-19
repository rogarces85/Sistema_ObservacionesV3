-- ---------------------------------------------------------------------------
-- Migracion: soporte para el Boletin Informativo de Errores REM
-- Fecha: 2026-08-12
--
-- Regulariza dos columnas que el codigo PHP ya usaba (Observation::create(),
-- Observation::update(), Observation::updateStatus(), views/supervision.php)
-- pero que nunca estuvieron definidas en el esquema versionado del repositorio.
-- Sin ellas, la creacion de observaciones falla con "Unknown column".
--
-- Es IDEMPOTENTE: se puede ejecutar varias veces sin error. MySQL 8 / MariaDB
-- no soportan ADD COLUMN IF NOT EXISTS de forma portable, por lo que se
-- consulta information_schema y se ejecuta SQL dinamico.
--
-- Uso:
--   mysql -u root -p observaciones_rem < config/migration_2026_08_12_boletin.sql
-- ---------------------------------------------------------------------------

SET @db := DATABASE();

-- 1) respuesta_establecimiento: texto libre con lo que responde el establecimiento
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'observaciones'
       AND COLUMN_NAME = 'respuesta_establecimiento') > 0,
  "SELECT 'observaciones.respuesta_establecimiento ya existe, sin cambios' AS resultado",
  "ALTER TABLE observaciones ADD COLUMN respuesta_establecimiento TEXT NULL AFTER detalle_observacion"
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2) detalle_error: anotacion breve del supervisor al clasificar la observacion
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'observaciones'
       AND COLUMN_NAME = 'detalle_error') > 0,
  "SELECT 'observaciones.detalle_error ya existe, sin cambios' AS resultado",
  "ALTER TABLE observaciones ADD COLUMN detalle_error TEXT NULL AFTER clasificacion"
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3) Indice de apoyo a la consulta del boletin
--    (filtra por anio + tipo_error y ordena por establecimiento/mes)
SET @sql := (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'observaciones'
       AND INDEX_NAME = 'idx_boletin') > 0,
  "SELECT 'observaciones.idx_boletin ya existe, sin cambios' AS resultado",
  "ALTER TABLE observaciones ADD INDEX idx_boletin (anio, tipo_error, establecimiento_id, mes)"
));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT 'Migracion 2026_08_12_boletin aplicada' AS resultado;
