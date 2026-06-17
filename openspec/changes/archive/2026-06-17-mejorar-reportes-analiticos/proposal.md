## Why

La sección de Reportes del sistema ofrece actualmente una vista general con exportación agregada y un Informe de Errores trimestral/anual. Los usuarios (supervisores y registradores) no tienen una herramienta analítica que les permita explorar los datos por categoría de error, plazo de entrega o uso de validador, con filtros consistentes y exportación individual por categoría. Esto obliga a generar exports completos y filtrarlos externamente, dificultando la lectura operativa y gerencial.

## What Changes

- Agregar cinco categorías analíticas independientes en `views/reportes.php` con tabs navegables: errores por establecimiento, plazos de entrega, uso de validador, errores por serie, errores por hoja.
- Cada categoría muestra resumen visual (ApexCharts) + tabla sincronizada + indicador(es) destacado(s).
- Nuevo endpoint `reportes-analiticos` en `api/reports.php` que devuelve las cinco categorías en una sola llamada con payload unificado `{success, data, message}`.
- Conjunto compartido de filtros (año, trimestre, mes, comuna, establecimiento) que se preserva al cambiar de categoría y se sincroniza entre todas las pestañas.
- Exportación individual por categoría con `api/export.php` validando CSRF, permisos por rol y bloqueo cuando la categoría no tiene datos.
- Métodos agregados en `models/Observation.php` para las cinco categorías (errores/plazos/validador × establecimiento/serie/hoja) con helper compartido de alcance por rol.
- Tres tareas pendientes (T055, T056) para refinar la UX de carga (spinner por categoría) y error (botón "Reintentar") conforme al refinamiento F4 de la spec.

## Capabilities

### New Capabilities
- `mod-reportes`: Nueva capacidad analítica con cinco categorías, filtros compartidos, exportación individual e indicadores. Esta capacidad complementa `mod-exportacion` (exports masivos clásicos) y el Informe de Errores del supervisor, sin reemplazarlos.

### Modified Capabilities
- `mod-exportacion`: Se agrega un nuevo tipo de reporte (`reportes-analiticos`) y exportación por categoría con payload `{tipo_reporte, formato, filtros}`. La exportación general previa se mantiene sin cambios (preservación de no-regresión según FR-013).
- `mod-supervision`: Sin cambios directos. Los reportes de errores (`reporteErroresPor*`) siguen filtrando por `tipo_error = 'ERROR'` y consumen el mismo modelo.

## Impact

- `views/reportes.php`: Estructura de tabs con `data-categoria` y paneles con `data-panel-categoria`. Renderiza 5 categorías con `CATEGORIAS_ANALITICAS` mapeadas desde PHP.
- `assets/js/reportes.js`: Catálogo `CATEGORIAS_ANALITICAS` con 5 entradas; `obtenerFiltrosAnaliticos()`, `validarMesTrimestre()`, `cargarReportesAnaliticos()`, `renderizarGraficoAnalitico()`, `renderizarTablaAnalitica()`, `renderizarDestacados()`, `exportarReporteAnalitico()`, `setEstadoAnalitico()`, `setBotonExportarAnalitico()`. Helpers de carga (`mostrarCargando`).
- `assets/css/tabler-override.css`: Estilos BEM `reportes-analytics__*` para paneles, estados, charts y botones.
- `api/reports.php`: Nuevo caso `reportes-analiticos` que orquesta 5 sub-reportes y devuelve totales por categoría.
- `api/export.php`: Validación de `tipo_reporte` con valores permitidos para las 5 categorías; CSRF; permisos; bloqueo sin datos.
- `models/Observation.php`: Métodos `reporteErroresPorEstablecimiento/Serie/Hoja`, `reportePlazosPorEstablecimiento/Mes/Comuna`, `reporteValidadorPorEstablecimiento/Mes/Comuna`, `reportePlazoAgregado`, `reporteValidadorAgregado`, `reporteNoValidadorPorEstablecimiento`; helper `aplicarAlcancePorRol()`.
- `models/Exporter.php`: Formato y encabezados para exportaciones analíticas (`exportErroresExcel` extendido).
- `docs/manuales/reportes-exportacion.md`: Manual extendido con mockups de las 5 categorías (T047).
- Sin migraciones SQL. Sin nuevas dependencias Composer/npm.

## Known Gaps (post-archive)

- **T055 [pendiente]**: Spinner HTML y texto dinámico por categoría `Cargando {titulo_categoria}...` según FR-010 refinado. Implementación externa.
- **T056 [pendiente]**: Botón "Reintentar" por categoría en mensaje de error que repita la consulta con los filtros activos según FR-011 refinado. Implementación externa.
- Las cinco tareas de validación manual (T025, T033, T040, T046, T052) están marcadas [X] con la nota "validación parcial, gap F4 pendiente en T055/T056". Re-validación humana completa requerida tras cierre de T055 y T056.
