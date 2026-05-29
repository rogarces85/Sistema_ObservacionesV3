## 1. Backend — Modelo (Observation.php)

- [x] 1.1 Crear método `reportePlazoAgregado(int $anio): array` con CTE de dos niveles (per-establishment+mes → agregado)
- [x] 1.2 Crear método `reportePlazoMensual(int $anio): array` con detalle por establecimiento y mes (para tabla mensual)
- [x] 1.3 Crear método `reporteValidadorAgregado(int $anio): array` con CTE de dos niveles (similar a plazo)
- [x] 1.4 Crear método `reporteValidadorMensual(int $anio): array` con detalle por establecimiento y mes

## 2. Backend — API (api/reports.php)

- [x] 2.1 Agregar case `plazo-agregado` que llame a `reportePlazoAgregado()` y devuelva JSON con `{establecimientos: [...], detalle_mensual: [...]}`
- [x] 2.2 Agregar case `validador-agregado` que llame a `reporteValidadorAgregado()` y devuelva JSON con estructura análoga

## 3. Frontend — JS (charts.js)

- [x] 3.1 Crear función `renderStackedBarChart(canvasId, labels, datasets, options)` genérica para barras horizontales apiladas con Chart.js

## 4. Frontend — Vista (views/reportes.php)

- [x] 4.1 Reemplazar contenido del tab 2 (Plazos) con: stacked horizontal bar chart (dentro vs fuera por establecimiento) + tabla mensual color-coded debajo
- [x] 4.2 Reemplazar contenido del tab 3 (Validador) con: stacked horizontal bar chart (usa vs no usa por establecimiento) + tabla mensual color-coded debajo
- [x] 4.3 Agregar función JS en reportes.php para cargar datos del endpoint `plazo-agregado` y renderizar gráfico + tabla
- [x] 4.4 Agregar función JS en reportes.php para cargar datos del endpoint `validador-agregado` y renderizar gráfico + tabla

## 5. Verificación

- [ ] 5.1 Probar endpoint `?report=plazo-agregado` en navegador — verificar JSON con ambos lados y agregación correcta
- [ ] 5.2 Probar endpoint `?report=validador-agregado` en navegador — verificar JSON con ambos lados
- [ ] 5.3 Probar tab 2 visualmente: stacked bar + tabla mensual con datos reales
- [ ] 5.4 Probar tab 3 visualmente: stacked bar + tabla mensual con datos reales
- [ ] 5.5 Verificar que tabs 1, 4 y 5 sigan funcionando sin cambios
