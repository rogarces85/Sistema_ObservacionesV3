## 1. Reestructurar charts.js

- [x] 1.1 Eliminar plugin `chartEffects` (registro + definición) de `assets/js/charts.js`
- [x] 1.2 Simplificar tooltips: eliminar emojis y barras unicode de `buildTooltipCallbacks()`, tooltips con label + valor + porcentaje limpio
- [x] 1.3 Simplificar datalabels: eliminar `textStrokeColor`/`textStrokeWidth` de `datalabelsBarInside` y `datalabelsBarVerticalInside`
- [x] 1.4 Simplificar botón export PNG: quitar o reducir a versión mínima

## 2. Migrar Dashboard Charts a tipos Tabler

- [x] 2.1 Reemplazar `createEstadoChart()`: cambiar de barra horizontal a doughnut con leyenda, tooltips con %, datalabels circulares condicionales
- [x] 2.2 Reemplazar `createTendenciaChart()`: cambiar de barra vertical a line chart con área gradiente, tooltips con valor, sin datalabels
- [x] 2.3 Mantener `createTipoErrorChart()` como barra horizontal, simplificar tooltips y datalabels

## 3. Verificación

- [x] 3.1 Verificar que doughnut chart renderice correctamente con 5 estados (pendiente, aprobado, rechazado, error, justificado)
- [x] 3.2 Verificar que line chart muestre tendencia mensual con área gradiente
- [x] 3.3 Verificar que barra horizontal de tipos de error funcione sin el plugin chartEffects
- [x] 3.4 Verificar que tooltips no contengan emojis ni barras unicode
- [x] 3.5 Verificar que las funciones de reportes (createBarHorizontal, createBarVertical, renderStackedBarChart) sigan funcionando
