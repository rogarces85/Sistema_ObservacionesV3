## Why

Los reportes actuales solo permiten filtrar por año y mes individual. Los usuarios necesitan agrupar y filtrar por trimestre calendario (Q1-Q4) para ver el comportamiento por períodos trimestrales sin tener que seleccionar mes por mes.

## What Changes

- Nuevo selector "Trimestre" en el panel de filtros junto a Año, Mes, Comuna y Establecimiento
- Al seleccionar un trimestre, se envían los 3 meses correspondientes al backend como `meses[]`
- El filtro de trimestre reemplaza al filtro de mes individual (si se selecciona uno, el otro se ignora)
- `plazo-agregado` y `validador-agregado` aceptan filtro `meses[]` para poder filtrar por trimestre
- `clearFilters()` también resetea el trimestre

## Capabilities

### New Capabilities

- `filtro-trimestre`: Filtro por trimestre calendario en la vista de reportes. Convierte Q1-Q4 a los 3 meses correspondientes y los pasa al backend mediante el parámetro `meses[]` existente.

### Modified Capabilities

_(Ninguna — solo se agrega UI y se extienden endpoints existentes)_

## Impact

- **views/reportes.php**: Nuevo `<select id="filterTrimestre">` en el formulario de filtros + lógica JS para convertir trimestre a meses + actualizar `loadPlazoAgregado()`, `loadValidadorAgregado()`, `clearFilters()`
- **models/Observation.php**: Agregar parámetro `$meses = []` a `reportePlazoAgregado()` y `reporteValidadorAgregado()` con filtro `o.mes IN (...)`
- **api/reports.php**: Pasar `$_GET['meses']` a `reportePlazoAgregado()` y `reporteValidadorAgregado()`
- Sin cambios en base de datos, ni nuevas dependencias
