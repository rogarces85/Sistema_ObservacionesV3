## 1. Backend Preparation

- [x] 1.1 Verificar que `api/reports.php` endpoint `error-reports` acepte parámetros `establecimiento_id` y `comuna_id`
- [x] 1.2 Confirmar que `Observation.php` métodos de reporte soportan filtrado por establecimiento y comuna
- [x] 1.3 Ajustar endpoint si es necesario para recibir filtros de estab/comuna y aplicarlos a las 5 consultas

## 2. Frontend UI Refactor

- [x] 2.1 Reemplazar sección de filtros checkbox en `views/reportes.php` por selects estilo `supervision.php`
- [x] 2.2 Crear selects para: Año, Mes (individual), Comuna, Establecimiento (cascada con Comuna)
- [x] 2.3 Agregar botones "Aplicar Filtros" y "Limpiar"
- [x] 2.4 Mantener intacto el sistema de pestañas (tabs) existente
- [x] 3.1 Reescribir `loadErrorReports()` para leer valores de los nuevos selects
- [x] 3.2 Implementar carga AJAX al hacer clic en "Aplicar Filtros"
- [x] 3.3 Implementar función "Limpiar" que resetea selects a "Todos"
- [x] 3.4 Implementar carga cascada de establecimientos según comuna seleccionada
- [x] 3.5 Asegurar que los gráficos se destruyen y recrean correctamente al cambiar filtros o pestañas
- [x] 4.1 Verificar que cada pestaña aplica su regla de filtrado estricta
- [x] 4.2 Probar filtros cascada (comuna → establecimiento)
- [x] 4.3 Verificar responsive en móvil y desktop
- [x] 4.4 Confirmar que no quedan checkboxes visibles en la vista
- [x] 4.5 Probar botón "Limpiar" restaura todo a estado inicial
