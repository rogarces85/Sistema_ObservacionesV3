## Why

Los reportes de Plazo de Entrega y Uso Validador actualmente muestran solo una cara del dato: solo las observaciones "fuera de plazo" y solo las que "no usan validador". Esto impide ver la proporción real de cumplimiento. Se necesita mostrar ambos lados (dentro/fuera plazo, usa/no usa validador) con la agregación correcta: por establecimiento, contabilizando un registro por mes si al menos una serie aplica.

## What Changes

- Nuevos endpoints en `api/reports.php` para obtener datos agregados de plazo y validador con ambos lados (dentro + fuera, si + no)
- Nuevas consultas SQL en `Observation.php` con agregación por establecimiento+mes (lógica: si alguna serie cumple, cuenta)
- Reemplazar gráficos actuales (barra simple de una sola categoría) por **barras apiladas** (stacked horizontal) mostrando ambos lados
- Agregar vista mensual detallada (tabla coloreada o heatmap) por establecimiento
- Mantener tabla de datos asociada a cada gráfico

## Capabilities

### New Capabilities

- `reporte-plazo-entrega`: Visualización mes a mes del cumplimiento de plazo de entrega por establecimiento. Agregación: si una serie del establecimiento en ese mes está dentro/fuera de plazo, cuenta como 1.
- `reporte-uso-validador`: Visualización mes a mes del uso del validador por establecimiento. Agregación: si una serie del establecimiento en ese mes usa validador, cuenta como 1.

### Modified Capabilities

_(Ninguna — los reportes existentes se mantienen, se agregan nuevas visualizaciones)_

## Impact

- **models/Observation.php**: Nuevos métodos: `reportePlazoAgregado()` y `reporteValidadorAgregado()` con nueva lógica de agregación por establecimiento+mes
- **api/reports.php**: Nuevo case en switch (ej. `reporte-plazo-validador`) que devuelva datos combinados de plazo y validador con ambos lados
- **views/reportes.php**: Reemplazar tabs 2 (Plazos) y 3 (Validador) con stacked bar charts + tabla mensual
- **assets/js/charts.js**: Nueva función para crear stacked horizontal bar charts (si no existe)
- Sin cambios en base de datos ni en la lógica de creación/supervisión de observaciones
