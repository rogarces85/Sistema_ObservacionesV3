## Why

Los gráficos del Dashboard actualmente se renderizan con CSS puro (barras estáticas), lo que limita la interactividad, los tooltips y la capacidad de comparar períodos. Los usuarios necesitan analizar tendencias comparando años y filtrar por rangos de meses (trimestres/semestres) para los informes que entregan. Unificar todo a Chart.js con carga AJAX permite una experiencia más profesional, dinámica y útil para la toma de decisiones.

## What Changes

- **Migrar D1, D2 y D3 del Dashboard** de CSS puro a **Chart.js** con carga AJAX dinámica (sin recargar la página).
- **Activar plugin `chartjs-plugin-datalabels`** para mostrar valores numéricos directamente sobre las barras en D1 y D2.
- **Agregar comparativo de años en el Dashboard**: selector para elegir hasta 2 años, mostrando barras agrupadas en D3 (observaciones por mes).
- **Agregar filtros de meses** en Dashboard y Reportes: checkboxes individuales + botones rápidos Q1, Q2, Q3, Q4, H1, H2, Todos.
- **Crear nuevo endpoint API** `dashboard-stats` que acepte múltiples años y meses como parámetros.
- **Adaptar `Observation::getStats()`** para soportar filtros de múltiples años y meses acumulados.
- **Mantener Reportes con un solo año** (sin comparativo), pero agregarle botones rápidos de trimestre/semestre.

## Capabilities

### New Capabilities
- `dashboard-chartjs`: Gráficos del Dashboard con Chart.js, carga AJAX, comparativo de 2 años y data labels visibles.
- `reportes-filtros-meses`: Filtros de meses con selección múltiple y botones rápidos de trimestre/semestre en la vista de Reportes.

### Modified Capabilities
- *(Ninguna — no existen specs previos que modifiquen requisitos funcionales)*

## Impact

- **Vistas**: `views/dashboard.php`, `views/reportes.php`, layout compartido (CDN del plugin).
- **Frontend**: `assets/js/charts.js` (nuevas funciones comparativas), `assets/js/app.js` (helpers de filtros).
- **Backend API**: `api/reports.php` (nuevo endpoint `dashboard-stats`).
- **Modelo**: `models/Observation.php` (`getStats()` con filtros de años y meses).
- **Dependencias**: Nuevo CDN `chartjs-plugin-datalabels`.

## Non-goals

- No se modificarán los 5 gráficos de la vista Reportes (R1-R5) — solo se agregan filtros de meses.
- No se implementa exportación de gráficos a imagen/PDF.
- No se agrega comparativo de años en la vista Reportes.
