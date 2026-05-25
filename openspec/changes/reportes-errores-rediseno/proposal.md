## Why

La vista actual de reportes tiene 6 tabs con 20+ gráficos mezclando todas las temáticas. El equipo necesita una vista enfocada exclusivamente en **errores** con 5 gráficos específicos, filtros por año/meses/comunas, que permita identificar rápidamente dónde se concentran los problemas.

## What Changes

- **Reemplazo completo** de `views/reportes.php`: nueva interfaz con 5 gráficos de errores + filtros multi-select (meses y comunas)
- **Nuevo endpoint** `api/reports.php?report=error-reports`: devuelve los 5 datasets con filtros
- **Nuevos métodos** en `models/Observation.php`: `reporteNoValidadorPorEstablecimiento()`, `reporteErroresPorSerie()` (solo ERROR), `reporteErroresPorHoja()`
- **Ampliación** de métodos existentes con parámetros opcionales `$meses` y `$comunaIds` (backward compatible)
- **Nuevas funciones** en `assets/js/charts.js`: `createBarHorizontal()`, `createBarVertical()`
- Se eliminan KPIs numéricos, tabs, y sección de exportación de la vista actual

## Capabilities

### New Capabilities
<!-- No new capabilities — modification of existing report system -->

### Modified Capabilities
- `mod-exportacion`: La vista de reportes ahora es exclusivamente de errores con 5 gráficos temáticos. Los filtros soportan selección múltiple de meses y comunas.

## Impact

- `views/reportes.php`: Reemplazo completo (de ~684 líneas a ~250 líneas estimadas)
- `api/reports.php`: Nuevo case `error-reports` + los existentes no se modifican
- `models/Observation.php`: 3 métodos nuevos + 2 métodos existentes ampliados con parámetros opcionales
- `assets/js/charts.js`: 2 funciones nuevas de gráficos + se eliminan funciones no usadas
- **BREAKING**: La vista anterior de reportes desaparece. La exportación de reportes generales se mantiene vía `api/export.php`
