## 1. Setup

- [x] 1.1 Revisar estructura actual de `views/reportes.php` y documentar puntos de integración (T001)
- [x] 1.2 Verificar métodos de agregación existentes en `models/Observation.php` (T002)
- [x] 1.3 Verificar flujo de exportación general y específica en `api/export.php` (T003)
- [x] 1.4 Verificar estructura de filtros, preview e informe supervisor en `views/reportes.php` (T004)
- [x] 1.5 Verificar inicialización actual del módulo Reportes en `assets/js/reportes.js` (T005)

## 2. Foundational

- [x] 2.1 Definir catálogo canónico de categorías analíticas y etiquetas en `assets/js/reportes.js` (T006)
- [x] 2.2 Definir validación compartida de filtros año/trimestre/mes/comuna/establecimiento en `api/reports.php` (T007)
- [x] 2.3 Agregar normalización de filtros con meses derivados de trimestre en `api/reports.php` (T008)
- [x] 2.4 Agregar helper de alcance por rol para consultas analíticas en `models/Observation.php` (T009)
- [x] 2.5 Asegurar estructura `success/data/message` en respuestas analíticas (T010)
- [x] 2.6 Asegurar ruta dinámica en AJAX de reportes (T011)
- [x] 2.7 Preparar estilos BEM reutilizables en `assets/css/tabler-override.css` (T012)

## 3. User Story 1 — Explorar reportes por categoría (P1)

- [x] 3.1 Estructura de tabs para 5 categorías analíticas (T013)
- [x] 3.2 Contenedores de gráfico, tabla, estado vacío y error por categoría (T014)
- [x] 3.3 Carga condicional de ApexCharts en la vista Reportes (T015)
- [x] 3.4 Métodos agregados para errores por establecimiento/serie/hoja en `Observation.php` (T016)
- [x] 3.5 Métodos agregados para plazos de entrega y uso de validador en `Observation.php` (T017)
- [x] 3.6 Caso `reportes-analiticos` y sub-categorías en switch de `api/reports.php` (T018)
- [x] 3.7 Respuesta con reportes, totales, resultados y estado por categoría (T019)
- [x] 3.8 Cliente de carga de categorías analíticas (T020)
- [x] 3.9 Renderizado ApexCharts por categoría (T021)
- [x] 3.10 Renderizado de tabla sincronizada (T022)
- [x] 3.11 Estados cargando/listo/vacío/error por categoría (T023)
- [x] 3.12 Cambio de categoría sin modificar filtros activos (T024)
- [x] 3.13 Validación manual US1 (T025) — *marcada con nota "validación parcial, gap F4 pendiente en T055/T056"*

## 4. User Story 2 — Filtrar por periodo y ubicación (P1)

- [x] 4.1 Controles de trimestre y filtros analíticos compartidos (T026)
- [x] 4.2 Obtención de filtros con trimestre y compatibilidad mes/trimestre (T027)
- [x] 4.3 Sincronización filtro establecimiento ↔ comuna (T028)
- [x] 4.4 Aplicar filtros compartidos a llamadas de categorías analíticas (T029)
- [x] 4.5 Aplicar filtros a consultas agregadas en `Observation.php` (T030)
- [x] 4.6 Validación backend mes/trimestre y establecimiento/comuna (T031)
- [x] 4.7 Acción limpiar filtros preservando año de trabajo (T032)
- [x] 4.8 Validación manual US2 (T033) — *marcada con nota "validación parcial, gap F4 pendiente en T055/T056"*

## 5. User Story 3 — Exportar cada reporte analítico (P2)

- [x] 5.1 Botón de exportación por categoría analítica (T034)
- [x] 5.2 Solicitud de exportación con categoría y filtros activos (T035)
- [x] 5.3 Validación de `tipo_reporte` analítico, formato, CSRF y permisos en `api/export.php` (T036)
- [x] 5.4 Reutilización de consultas agregadas para datos exportables (T037)
- [x] 5.5 Formato de archivo y encabezados para exportaciones analíticas en `Exporter.php` (T038)
- [x] 5.6 Bloqueo de exportación sin datos con mensaje en español (T039)
- [x] 5.7 Validación manual US3 (T040) — *marcada con nota "validación parcial, gap F4 pendiente en T055/T056"*

## 6. User Story 4 — Indicadores clave (P3)

- [x] 6.1 Tarjetas de indicadores principales (T041)
- [x] 6.2 Cálculo de totales de observaciones, errores, fuera de plazo, sin validador (T042)
- [x] 6.3 Obtención de totales base desde `Observation.php` (T043)
- [x] 6.4 Renderizado y actualización de indicadores al cambiar filtros (T044)
- [x] 6.5 Estado vacío o no disponible para indicadores sin datos (T045)
- [x] 6.6 Validación manual US4 (T046) — *marcada con nota "validación parcial, gap F4 pendiente en T055/T056"*

## 7. Polish & Cross-Cutting

- [x] 7.1 Actualizar manual de usuario con mockups de reportes analíticos (T047)
- [x] 7.2 Revisar textos en español en `views/reportes.php` (T048)
- [x] 7.3 Revisar textos en español en `assets/js/reportes.js` (T049)
- [x] 7.4 Revisar ausencia de estilos inline en `views/reportes.php` (T050)
- [x] 7.5 Ejecutar lint PHP sobre archivos modificados (T051)
- [x] 7.6 Verificar no-regresión de exportación general e Informe de Errores — T054 (FR-013)
- [x] 7.7 Verificar ausencia de migraciones nuevas en `config/` (T053)
- [x] 7.8 Validación manual completa desde `quickstart.md` (T052) — *marcada con nota "validación parcial, gap F4 pendiente en T055/T056"*

## 8. Known Gaps (pendientes externos)

- [ ] 8.1 T055 — Implementar spinner HTML y texto dinámico por categoría `Cargando {titulo_categoria}...` según FR-010 refinado
- [ ] 8.2 T056 — Implementar botón "Reintentar" por categoría en mensaje de error que repita la consulta con los filtros activos según FR-011 refinado

## Resumen de ejecución

- **Total tareas**: 56
- **Tareas completadas [X]**: 53
- **Tareas completadas con nota parcial**: 5 (T025, T033, T040, T046, T052)
- **Tareas pendientes [ ]**: 2 (T055, T056)
- **Tasa de cierre técnico**: 53/56 = 94.6%
- **Tasa de cierre real tras gap F4**: 56/56 = 100% (una vez cerradas T055 y T056)
