# Auditoría de Calidad: Especificaciones Observaciones REM

**Propósito**: Validar la calidad, consistencia y completitud de las 11 especificaciones del sistema
**Creado**: 2026-06-01
**Feature**: `specs/001-unificar-specs/spec.md`
**Alcance**: Análisis profundo, enfoque completo (seguridad, datos, UX, edge cases)

## Consistencia Entre Módulos

- [x] CHK001 ¿Los nombres de APIs son consistentes en idioma? Se detectan `api/deleted.php` (inglés), `api/versioning.php` (inglés) vs `api/observations.php` (spanglish), `api/usuarios.php`, `api/supervision.php` (español). ¿Se normaliza todo a español? [Inconsistencia]
- [x] CHK002 ¿La estructura de URLs de API es consistente? `api/dashboard/stats.php` usa subdirectorio, mientras que el resto usa `api/nombre.php?action=`. ¿Se unifica el patrón? [Consistencia]
- [x] CHK003 ¿Los formatos de error response están definidos en todos los módulos? Solo `observaciones.md` define `{success:false, error, code}`. Los demás módulos no especifican formato de error. [Gap, Consistencia]
- [ ] CHK004 ¿Las especificaciones se referencian entre sí explícitamente? Por ejemplo, `asignaciones.md` asigna establecimientos pero no reference `establecimientos.md`. `importacion.md` importa observaciones pero no reference `observaciones.md`. [Completitud]
- [x] CHK005 ¿Los estados del sistema (pendiente, aprobado, error, rechazado) están definidos en un único lugar canónico? Aparecen en observaciones.md, supervision.md y reportes-exportacion.md sin una fuente central. [Gap]

## Nomenclatura y Terminología

- [x] CHK006 ¿"historico_observaciones" vs "historial_usuarios" se normaliza a un solo nombre? Un módulo usa "historico", otro usa "historial". [Inconsistencia]
- [x] CHK007 ¿Las columnas que son comunes entre tablas (ej: `estado_actual` en observaciones, `estado` en reportes) usan el mismo nombre? Se detecta `estado_actual` en observaciones.md vs `estado` en reportes-exportacion.md para el mismo concepto. [Consistencia]
- [x] CHK008 ¿Los placeholders como `vXXX` en versionado.md están resueltos antes de implementar? FR-VER-004 dice "incrementa automáticamente" pero el formato vXXX aparece 6 veces como placeholder. [Completitud]

## Modelo de Datos

- [x] CHK009 ¿Todas las tablas del sistema tienen campos de auditoría (`fecha_creacion`, `fecha_actualizacion`)? Solo `versiones_sistema` tiene `fecha_creacion`. `observaciones`, `usuarios`, `establecimientos`, etc. no tienen timestamps. [Gap]
- [ ] CHK010 ¿Las longitudes de VARCHAR están especificadas en todas las tablas? Se detectan VARCHAR sin límite en varias entidades (ej: `establecimientos.nombre`, `comunas.nombre`, `referentes.cargo`). [Clarity]
- [ ] CHK011 ¿Las relaciones FK entre tablas están documentadas en todas las entidades? `observaciones_eliminadas` especifica "sin FK" para establecimiento/comuna, pero otras tablas no explicitan si tienen o no FK. [Completitud]
- [ ] CHK012 ¿Los ENUMs están completos y son consistentes entre tablas? `usuarios.rol` usa `ENUM('registrador','supervisor')`, `reportes.estado` usa `ENUM('pendiente','aprobado','error','rechazado')`, pero `observaciones.estado_actual` usa VARCHAR. [Consistencia]

## Seguridad y Permisos

- [ ] CHK013 ¿Cada especificación de API explicita qué roles pueden acceder a cada endpoint? La mayoría lo hace en la tabla FR, pero algunos (ej: dashboard) no definen permisos por endpoint individual. [Completitud]
- [x] CHK014 ¿La validación CSRF está referenciada en todos los módulos con endpoints POST/PUT/DELETE? Solo `auth-sesion.md` define CSRF. El resto asume que aplica pero no lo explicita. [Consistencia]
- [ ] CHK015 ¿Existe rate limiting o protección de fuerza bruta para endpoints sensibles (login, reset password, importación)? Solo `auth-sesion.md` lo define. [Gap]
- [ ] CHK016 ¿Los códigos de error HTTP (400, 401, 403, 404) están especificados para cada escenario de error? Algunos módulos los detallan en Gherkin, otros no. [Completitud]

## UX y Casos Borde

- [ ] CHK017 ¿Los estados de carga (skeleton/spinner) están definidos para todos los componentes que cargan datos asíncronos? Solo `dashboard.md` menciona skeleton loader. [Gap, Coverage]
- [ ] CHK018 ¿Los estados vacíos (sin datos) están definidos para todos los listados y filtros? La mayoría sí, pero algunos módulos (ej: `dashboard.md` EC-DASH-03) los cubren bien. [Coverage]
- [ ] CHK019 ¿Las operaciones masivas tienen definido el comportamiento ante fallos parciales en todos los módulos? `supervision.md` y `papelera-eliminadas.md` lo definen, pero `observaciones.md` y `asignaciones.md` no lo explicitan para operaciones masivas. [Completitud]
- [ ] CHK020 ¿La concurrencia (dos usuarios operando sobre el mismo registro) está considerada en todos los módulos? Solo `papelera-eliminadas.md` (EC-DEL-03) y `observaciones.md` (last-write-wins) lo mencionan. [Gap]

## Paginación y Volumen

- [ ] CHK021 ¿El tamaño de paginación es consistente entre módulos? 50 en observaciones, 50 en supervisión, 50 en papelera, 20 en reportes. La diferencia en reportes (20) está justificada o es un error? [Consistencia]
- [ ] CHK022 ¿Los límites de volumen de datos están definidos? `reportes-exportacion.md` define 50,000 registros máx. Los demás módulos no especifican límites. [Gap]

## Claridad de Criterios de Éxito

- [x] CHK023 ¿Todos los criterios de éxito incluyen métricas medibles (tiempos, cantidades)? La mayoría sí (< 3s, < 500ms, etc.), pero algunos son ambiguos como "se refleja inmediatamente". [Clarity]
- [ ] CHK024 ¿Los criterios de éxito cubren escenarios de carga/estrés además del caso feliz? No se encontraron criterios de rendimiento bajo carga en ningún módulo. [Gap]
- [ ] CHK025 ¿Las dependencias entre módulos están documentadas como supuestos? Por ejemplo, `observaciones.md` asume que los establecimientos existen (vienen de `establecimientos.md`) pero no lo reference explícitamente. [Completitud]

## Notes

- Auditoría general de calidad sobre 11 especificaciones unificadas
- Referencias: [Spec §index] para spec.md, [Spec §MOD] para cada módulo individual
- Items CHK001-CHK025 distribuidos en 8 categorías de calidad
