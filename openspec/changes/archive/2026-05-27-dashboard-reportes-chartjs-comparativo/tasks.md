## 1. Backend Foundation

- [x] 1.1 Adaptar `Observation::getStats()` en `models/Observation.php` para aceptar array de años (`$years`) y array de meses (`$meses`) con placeholders dinámicos en SQL
- [x] 1.2 Crear método `getDashboardStats($years, $meses, $userId, $userRole)` en `models/Observation.php` que devuelva datos estructurados por estado, tipo de error y mes (con clave por año)
- [x] 1.3 Agregar endpoint `dashboard-stats` en `api/reports.php` que reciba `years[]` y `meses[]` por GET y devuelva JSON con datos comparativos

## 2. Frontend Foundation

- [x] 2.1 Agregar CDN de `chartjs-plugin-datalabels` al layout compartido (archivo que incluye Chart.js actualmente)
- [x] 2.2 Registrar plugin globalmente en `assets/js/charts.js` con `Chart.register(ChartDataLabels)`
- [x] 2.3 Crear función `createDashboardEstadoChart(canvasId, data)` en `assets/js/charts.js` para D1 (barras horizontales con datalabels)
- [x] 2.4 Crear función `createDashboardTiposChart(canvasId, data)` en `assets/js/charts.js` para D2 (barras horizontales con datalabels, Top 10)
- [x] 2.5 Crear función `createDashboardMesesChart(canvasId, data)` en `assets/js/charts.js` para D3 (barras agrupadas comparando 2 años, sin datalabels)

## 3. Dashboard Implementation

- [x] 3.1 Reescribir sección D1 en `views/dashboard.php`: reemplazar HTML estático por `<canvas id="chartDashboardEstado">` y agregar contenedor con altura fija
- [x] 3.2 Reescribir sección D2 en `views/dashboard.php`: reemplazar lista numerada por `<canvas id="chartDashboardTipos">`
- [x] 3.3 Reescribir sección D3 en `views/dashboard.php`: reemplazar barras CSS por `<canvas id="chartDashboardMeses">`
- [x] 3.4 Agregar controles de filtros en `views/dashboard.php`: selector de hasta 2 años (checkboxes), checkboxes de 12 meses, botones rápidos Q1/Q2/Q3/Q4/H1/H2/Todos
- [x] 3.5 Implementar `loadDashboardStats()` en script inline o archivo JS que haga fetch al endpoint, maneje estado de carga y destruya/recree los 3 gráficos
- [x] 3.6 Implementar helpers de filtros de meses en `assets/js/app.js`: `selectQuarter(q)`, `selectSemester(h)`, `selectAllMonths()`, `updateMonthCheckboxesUI()`

## 4. Reportes Filters Implementation

- [x] 4.1 Agregar botones rápidos Q1/Q2/Q3/Q4/H1/H2/Todos en `views/reportes.php` debajo o junto a los checkboxes de meses existentes
- [x] 4.2 Implementar sincronización entre botones rápidos y checkboxes individuales en `views/reportes.php` (mismo comportamiento que Dashboard)
- [x] 4.3 Verificar que `loadErrorReports()` en `views/reportes.php` envíe correctamente los meses seleccionados al API existente

## 5. Testing & Verification

- [x] 5.1 Probar endpoint `dashboard-stats` con 1 año, 2 años, todos los meses, subset de meses, y verificar que respeta permisos por rol
- [x] 5.2 Probar Dashboard: cambio de año comparativo actualiza D3 con barras agrupadas, cambio de meses actualiza D1/D2/D3
- [x] 5.3 Probar Reportes: botones Q1/H1/Todos seleccionan/deseleccionan correctamente los meses y los gráficos se actualizan
- [x] 5.4 Verificar que datalabels muestran valores en D1 y D2, y NO en D3
- [x] 5.5 Verificar diseño responsive en pantallas medianas y grandes (Dashboard grid debe mantenerse)
- [x] 5.6 Probar edge case: sin datos para filtros seleccionados → debe mostrar mensaje "Sin datos" en lugar de gráfico vacío
