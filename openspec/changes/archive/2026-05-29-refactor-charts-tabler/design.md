## Context

Actualmente `assets/js/charts.js` tiene 583 líneas con 3 gráficos de barra para el dashboard, más funciones genéricas para reportes. Incluye un plugin de efectos visuales custom (gradientes en barras + hover dim), tooltips con emojis y barras unicode, datalabels con stroke complejo, y botón de export PNG. Todo esto sobre un único tipo de gráfico (barra) aunque los datos representan conceptos distintos (composición, ranking, tendencia).

## Goals / Non-Goals

**Goals:**
- "Distribución por Estado" → doughnut chart (muestra proporciones, coherente con el título "distribución")
- "Observaciones por Mes" → line chart con área gradiente (muestra tendencia temporal)
- "Top Tipos de Error" → mantener barra horizontal (ranking)
- Simplificar tooltips: sin emojis, sin barras unicode, diseño Tabler limpio
- Eliminar plugin `chartEffects` (gradientes en barras + hover dim)
- Simplificar datalabels: sin textStroke, sin animación compleja
- Simplificar botón export PNG (o eliminar si es muy custom)

**Non-Goals:**
- No cambiar funciones de reportes (`createBarHorizontal`, `createBarVertical`, `renderStackedBarChart`)
- No modificar vistas PHP ni HTML
- No cambiar Chart.js ni añadir nuevas dependencias
- No modificar datos ni lógica de backend

## Decisions

### Doughnut en lugar de barra para Distribución por Estado
Tabler admin usa doughnut charts para mostrar composición (porcentajes). El título "Distribución por Estado" implica proporciones. El doughnut muestra de forma intuitiva qué estado predomina, a diferencia de la barra horizontal actual que fuerza a comparar longitudes.

### Line chart con fill para tendencia mensual
Tabler usa line charts con área semitransparente para datos temporales. Muestra mejor la evolución a lo largo del año que las barras verticales. El fill gradiente usa `PALETTE_TENDENCIA` (de azul claro a `#0ea5e9`).

### Eliminación de chartEffects plugin
El plugin custom añade gradientes lineales a las barras y efecto hover dim. Con doughnut y line charts estos efectos no aplican (no hay barras en los charts del dashboard después del cambio). Las funciones de reportes (que sí usan barras) seguirán sin el plugin — si es necesario se puede reimplementar más adelante.

### Tooltips sin emojis ni barras unicode
Los emojis en tooltips (`⏳ Pending`, `✅ Approved`) y las barras unicode (`█████░░░░░░░`) son visualmente distractores y no alinean con el estilo Tabler. Se reemplazan por tooltips simples con label, valor y porcentaje.

### Datalabels con display condicional simple
Se elimina `textStrokeColor`/`textStrokeWidth` (sombra de texto). Se mantiene `smartLabelDisplay` para ocultar etiquetas cuando la barra/dato es muy pequeño. En doughnut se usa datalabels circulares solo si hay suficiente espacio.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|-----------|
| Doughnut con muchos estados (>5) puede saturarse | Tabler maneja hasta 6-7 segmentos con leyenda; solo tenemos 5 estados (pendiente, aprobado, rechazado, error, justificado) |
| Line chart con pocos meses se ve vacío | Con datos demo hay 12 meses; si hay menos de 3 puntos, se muestra mensaje "datos insuficientes" |
| chartEffects plugin se usaba en charts de reportes | Las funciones de reportes (createBarHorizontal, etc.) son independientes y no dependen del plugin — siguen funcionando sin él |
