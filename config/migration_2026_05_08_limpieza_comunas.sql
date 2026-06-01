-- Limpieza de comunas duplicadas
-- Unifica comunas antiguas (10001-10007) hacia las oficiales (10301-10307)
-- Fecha: 2026-05-08

USE observaciones_rem;

-- Mapeo de comunas antiguas -> nuevas
-- 10001 (Osorno) -> 10301 (OSORNO)
-- 10002 (Purranque) -> 10303 (PURRANQUE)
-- 10003 (Entre Lagos) -> 10304 (PUYEHUE)
-- 10004 (Río Negro) -> 10305 (RIO NEGRO)
-- 10005 (San Pablo) -> 10307 (SAN PABLO)
-- 10006 (San Juan de la Costa) -> 10306 (SAN JUAN DE LA COSTA)
-- 10007 (Puerto Octay) -> 10302 (PUERTO OCTAY)

-- Paso 1: Obtener IDs internos de comunas nuevas
SET @nueva_osorno = (SELECT id FROM comunas WHERE codigo_comuna = 10301 LIMIT 1);
SET @nueva_puerto_octay = (SELECT id FROM comunas WHERE codigo_comuna = 10302 LIMIT 1);
SET @nueva_purranque = (SELECT id FROM comunas WHERE codigo_comuna = 10303 LIMIT 1);
SET @nueva_puyehue = (SELECT id FROM comunas WHERE codigo_comuna = 10304 LIMIT 1);
SET @nueva_rio_negro = (SELECT id FROM comunas WHERE codigo_comuna = 10305 LIMIT 1);
SET @nueva_sjc = (SELECT id FROM comunas WHERE codigo_comuna = 10306 LIMIT 1);
SET @nueva_san_pablo = (SELECT id FROM comunas WHERE codigo_comuna = 10307 LIMIT 1);

-- Paso 2: Actualizar establecimientos que apuntan a comunas antiguas
UPDATE establecimientos SET comuna_id = @nueva_osorno WHERE comuna_id = (SELECT id FROM comunas WHERE codigo_comuna = 10001 LIMIT 1);
UPDATE establecimientos SET comuna_id = @nueva_purranque WHERE comuna_id = (SELECT id FROM comunas WHERE codigo_comuna = 10002 LIMIT 1);
UPDATE establecimientos SET comuna_id = @nueva_puyehue WHERE comuna_id = (SELECT id FROM comunas WHERE codigo_comuna = 10003 LIMIT 1);
UPDATE establecimientos SET comuna_id = @nueva_rio_negro WHERE comuna_id = (SELECT id FROM comunas WHERE codigo_comuna = 10004 LIMIT 1);
UPDATE establecimientos SET comuna_id = @nueva_san_pablo WHERE comuna_id = (SELECT id FROM comunas WHERE codigo_comuna = 10005 LIMIT 1);
UPDATE establecimientos SET comuna_id = @nueva_sjc WHERE comuna_id = (SELECT id FROM comunas WHERE codigo_comuna = 10006 LIMIT 1);
UPDATE establecimientos SET comuna_id = @nueva_puerto_octay WHERE comuna_id = (SELECT id FROM comunas WHERE codigo_comuna = 10007 LIMIT 1);

-- Paso 3: Eliminar comunas antiguas (ya no tienen establecimientos referenciándolas)
DELETE FROM comunas WHERE codigo_comuna IN (10001, 10002, 10003, 10004, 10005, 10006, 10007);

-- Paso 4: Verificar resultado
SELECT codigo_comuna, nombre FROM comunas ORDER BY codigo_comuna;
SELECT COUNT(*) as total_comunas FROM comunas;

SELECT 'Limpieza de comunas duplicadas completada' AS mensaje;
