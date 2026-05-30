## Why

Los 3 gráficos del dashboard actualmente son todos de tipo barra (horizontal y vertical), incluso "Distribución por Estado" que debería mostrar composición/proporción. Tabler admin template usa tipos de gráfico específicos según el contexto: doughnut para distribuciones, línea para tendencias, barra horizontal para rankings. Esto mejora la legibilidad de los datos y la coherencia visual con el resto del sistema.

## What Changes

- Cambiar "Distribución por Estado" de barra horizontal → **doughnut chart** (más apropiado para porcentajes/composición)
- Cambiar "Observaciones por Mes" de barra vertical → **line chart** con área gradiente (mejor para tendencias temporales)
- Mantener "Top Tipos de Error" como barra horizontal (apropiado para ranking)
- Simplificar `charts.js`: eliminar plugin de efectos visuales (hover dim, gradientes en barras), emojis en tooltips, lógica de export PNG compleja
- Simplificar datalabels: solo mostrar valores donde quepan, sin efectos de texto
- Las funciones genéricas de reportes (`createBarHorizontal`, `createBarVertical`, `renderStackedBarChart`) se mantienen sin cambios
- No hay cambios en backend, datos, ni vistas PHP

## Capabilities

### New Capabilities
- `dashboard-charts`: Charts del dashboard con tipos Tabler (doughnut, line, bar) y código simplificado

### Modified Capabilities
- (ninguno — solo refactor de frontend)

## Impact

| Archivo | Tipo de Cambio |
|---------|---------------|
| `assets/js/charts.js` | Refactor: reemplazar `createEstadoChart` (→doughnut), `createTendenciaChart` (→line), simplificar tooltips/datalabels/efectos |
| `views/dashboard.php` | Ningún cambio estructural (los canvas IDs se mantienen) |
