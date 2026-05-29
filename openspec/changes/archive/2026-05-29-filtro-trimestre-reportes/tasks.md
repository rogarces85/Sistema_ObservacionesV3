## 1. Backend — Modelo (Observation.php)

- [x] 1.1 Agregar parámetro `$meses = []` a `reportePlazoAgregado()` con filtro condicional `o.mes IN (...)` dentro del CTE
- [x] 1.2 Agregar parámetro `$meses = []` a `reporteValidadorAgregado()` con filtro condicional `o.mes IN (...)` dentro del CTE

## 2. Backend — API (api/reports.php)

- [x] 2.1 En case `plazo-agregado`, leer `$_GET['meses']` y pasarlo a `reportePlazoAgregado()`
- [x] 2.2 En case `validador-agregado`, leer `$_GET['meses']` y pasarlo a `reporteValidadorAgregado()`

## 3. Frontend — Vista (views/reportes.php)

- [x] 3.1 Agregar `<select id="filterTrimestre">` en el grid de filtros con opciones: Todos, 1er Trimestre, 2do Trimestre, 3er Trimestre, 4to Trimestre
- [x] 3.2 Agregar constante `TRIMESTRES` en JS con mapeo trimestre → meses
- [x] 3.3 Modificar `loadErrorReports()` para incluir meses del trimestre seleccionado (trimestre anula mes individual)
- [x] 3.4 Modificar `loadPlazoAgregado()` y `loadValidadorAgregado()` para enviar `meses[]` según trimestre seleccionado
- [x] 3.5 Modificar `clearFilters()` para resetear el trimestre

## 4. Verificación

- [ ] 4.1 Probar filtro trimestre en tab 1 (Total Errores) — verificar que filtra correctamente los 3 meses
- [ ] 4.2 Probar filtro trimestre en tabs 2 y 3 (Plazo y Validador) — verificar que el filtro se aplica
- [ ] 4.3 Probar que mes individual sigue funcionando cuando trimestre está en "Todos"
- [ ] 4.4 Probar clearFilters resetea todo correctamente
