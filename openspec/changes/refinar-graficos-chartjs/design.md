## Context

Los gráficos del sistema (dashboard.php y reportes.php) se renderizan con Chart.js 4.4 usando funciones independientes en `assets/js/charts.js`. Cada función recibe datos, crea un canvas y configura opciones de Chart.js. Actualmente no hay una capa de configuración compartida — cada gráfico duplica opciones de tooltip, colores, fuentes, etc.

El diseño del sistema usa Inter como tipografía principal (#font-family-base), sombras (#shadow-*), border-radius (#radius-*), y una paleta de colores definida en CSS custom properties. Los charts no aprovechan estos tokens visuales.

## Goals / Non-Goals

**Goals:**
- Unificar todas las opciones visuales de Chart.js en una configuración central compartida
- Aplicar gradiente horizontal en barras usando la paleta del sistema
- Animación secuencial tipo cascada en todos los gráficos de barras
- Tooltip rediseñado con estilo card blanca, ícono de estado, barra de progreso porcentual
- Datalabels dentro de la barra (texto blanco), con ocultamiento inteligente
- Interactividad hover: barra activa con brillo + opacidad reducida en las demás
- Botón de exportar PNG nativo de Chart.js en cada contenedor de gráfico
- Mantener compatibilidad total con datos existentes (sin cambios en API ni backend)

**Non-Goals:**
- No se agregan nuevos tipos de gráfico (solo refinar los existentes: bar horizontal, bar vertical)
- No se cambia la librería (sigue siendo Chart.js 4.4)
- No se agregan plugins adicionales (chartjs-plugin-datalabels ya está)
- No se modifican datos, APIs, base de datos, ni vistas PHP (solo JS)

## Decisions

| Decisión | Opción | Por qué |
|----------|--------|---------|
| **Config central** | Objeto `CHART_THEME` global con defaults de font, colores, tooltip, animación | Evita duplicación en cada función; un solo punto de cambio |
| **Gradiente** | `createLinearGradient` horizontal (x1=0, y1=0, x2=1, y2=0) | Se alinea con la dirección de lectura del valor en barras horizontales |
| **Paleta** | Mapeo directo a tokens CSS del sistema: pendiente→amber (#f59e0b), aprobado→emerald (#059669), rechazado→red (#dc2626), error→red (#b91c1c), justificado→sky (#0284c7) | Consistencia visual con badges y botones del sistema |
| **Animación secuencial** | `animation.duration` global 800ms + `animation.onProgress` con contador para stagger por barra | Efecto cascada sin plugins extra |
| **Tooltip** | Modo `'nearest'` con `intersect: false`, usando `callbacks` para formato personalizado con emoji+porcentaje+progress bar | Coexistencia con hover interactivo sin conflicto |
| **Datalabels** | `display: function(ctx)` que retorna true solo si el ancho de barra > 30px (smart hide) | Evita etiquetas ilegibles en barras pequeñas |
| **Hover interactivo** | `onHover` handler que setea `datasetProperties` para cambiar opacidad | No requiere plugin, es API nativa de Chart.js |
| **Exportar PNG** | Botón `<button>` sobre cada canvas, llama a `chart.toBase64Image()` + descarga | Chart.js lo soporta nativo, sin dependencias |

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|------------|
| **Rendimiento**: Gradientes + animaciones + datalabels en gráficos con muchos datos (ej: 50+ errores por establecimiento) | Limitar animación secuencial a charts con <30 items; sobre eso, animación simple. Evaluar si hay degradación. |
| **Smart labels**: El cálculo de ancho de barra depende del tamaño del canvas, que puede variar en responsive | Usar `chart.chartArea` para calcular disponible en lugar de valores fijos |
| **Exportar PNG**: El botón puede superponerse al canvas en mobile | Posicionar botón fuera del contenedor del canvas, como parte del header de la card |
| **Tooltip progress bar**: Usar emoji (█) para simular barra es limitado; no se puede renderizar HTML real en tooltips de Chart.js | Alternativa: usar caracteres Unicode █ y ░ que funcionan en canvas |
