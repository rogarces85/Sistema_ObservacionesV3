## Why

Los gráficos del sistema (dashboard y reportes) usan Chart.js con configuración mínima: colores planos, animaciones genéricas, tooltips oscuros básicos y sin interactividad. Se ven "básicos" frente al resto del diseño del sistema que usa sombras, gradientes, border-radius y tipografía Inter. Se busca refinar la apariencia visual sin cambiar de librería ni agregar nuevas dependencias.

## What Changes

- Aplicar gradiente horizontal en barras con paleta de colores mapeada a tokens CSS del sistema
- Agregar animación secuencial (cascada) en la carga de gráficos
- Tooltip rediseñado: estilo card blanca con sombra, barra de progreso, ícono de estado y formato enriquecido
- Datalabels dentro de la barra (texto blanco) con ocultamiento inteligente si la barra es muy pequeña
- Interactividad hover: barra activa brillante + resto opaco
- Botón de exportar gráfico como PNG en cada chart
- Unificar tipografía de charts con Inter (la del sistema)

## Capabilities

### New Capabilities

_(Ninguna — no se introducen nuevas funcionalidades, solo mejora visual)_

### Modified Capabilities

_(Ninguna — los cambios son exclusivamente de presentación, no de comportamiento ni requisitos)_

## Impact

- **assets/js/charts.js**: Reescritura completa de todas las funciones de gráficos
- **views/dashboard.php**: Ajuste menor en el script inline de inicialización (pasar config de export)
- **views/reportes.php**: Ajuste menor en los charts de reportes
- **includes/footer.php**: Sin cambios (Chart.js ya está cargado vía CDN)
- Sin cambios en backend, API, base de datos ni dependencies
