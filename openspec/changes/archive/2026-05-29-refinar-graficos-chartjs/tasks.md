## 1. Configuración central de tema (CHART_THEME)

- [x] 1.1 Crear objeto `CHART_THEME` con defaults: font (Inter), color (slate-500), animation, tooltip base
- [x] 1.2 Definir paleta de colores unificada `PALETTE_SISTEMA` mapeando estados a colores del sistema
- [x] 1.3 Definir paleta cíclica `PALETTE_ERRORES` (10 colores) para Top Tipos de Error
- [x] 1.4 Crear función `createGradient(ctx, color, chartArea)` que genera gradiente horizontal clear→intenso

## 2. Tooltip unificado

- [x] 2.1 Crear objeto `TOOLTIP_THEME` con estilo card blanca, sombra, border-radius del sistema
- [x] 2.2 Implementar `customTooltipLabel(ctx)` que retorna: emoji según estado + valor + porcentaje + barra unicode
- [x] 2.3 Integrar tooltip en config default de CHART_THEME

## 3. Datalabels dentro de barra

- [x] 3.1 Crear `datalabelsBarInside` con posición dentro de la barra, texto blanco, font bold
- [x] 3.2 Implementar `smartLabelDisplay(ctx)` que oculta label si barra < 30px de ancho
- [x] 3.3 Crear `datalabelsBarVerticalInside` (para gráficos verticales, posición top)

## 4. Animación secuencial

- [x] 4.1 Configurar `animation.duration: 800`, `easing: 'easeOutQuart'` en default
- [x] 4.2 Implementar stagger con `animation.onProgress` que retrasa cada barra 150ms
- [x] 4.3 Desactivar animación secuencial si dataset tiene más de 30 items (usar animación simple)

## 5. Interactividad hover

- [x] 5.1 Implementar `onHover` que resalta barra activa (opacidad 1.0) y opaca las demás (0.3)
- [x] 5.2 Agregar efecto de elevación en hover (sombra simulada con borderColor highlight)

## 6. Botón exportar PNG

- [x] 6.1 Crear función `addExportButton(containerId, chart)` que agrega botón "📥 Exportar"
- [x] 6.2 Implementar descarga: `chart.toBase64Image()` → crea link y dispara download
- [x] 6.3 Integrar botón en dashboard (chartEstado, chartTendencia, chartTipoError)
- [x] 6.4 Integrar botón en reportes (los 5 charts de tabs)

## 7. Refactor funciones de dashboard

- [x] 7.1 Reescribir `createEstadoChart(canvasId, data)` con CHART_THEME + gradiente + paleta
- [x] 7.2 Reescribir `createTipoErrorChart(canvasId, data)` con paleta cíclica + gradiente
- [x] 7.3 Reescribir `createTendenciaChart(canvasId, data)` con gradiente sky-100 → sky-500
- [x] 7.4 Actualizar `initializeCharts(statsData)` para pasar referencia de chart al export button

## 8. Refactor funciones de reportes

- [x] 8.1 Reescribir `createBarHorizontal(canvasId, labels, values, color)` con nuevo tema
- [x] 8.2 Reescribir `createBarVertical(canvasId, labels, values, color)` con nuevo tema
- [x] 8.3 Actualizar `renderTabChart(tabId, data)` para integrar export button

## 9. Ajustes en vistas PHP

- [x] 9.1 dashboard.php: Agregar contenedor `<div class="chart-header">` para título + botón export
- [x] 9.2 reportes.php: Agregar chart-header en cada tab-panel
- [x] 9.3 Verificar que los estilos CSS del chart-header existan o agregarlos

## 10. Verificación

- [x] 10.1 Probar dashboard: verificar gradientes, animación, tooltip, datalabels, hover, export
- [x] 10.2 Probar reportes: verificar los 5 tabs con sus charts y export
- [x] 10.3 Probar responsive: charts en mobile no se rompen, botón export visible
- [x] 10.4 Probar con datos vacíos: mostrar mensaje "Sin datos" sin errores
- [x] 10.5 Verificar consola: sin errores JS ni warnings de Chart.js
