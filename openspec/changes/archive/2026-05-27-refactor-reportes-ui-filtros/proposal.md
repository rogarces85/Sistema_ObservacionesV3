## Why

La vista Reportes actual presenta problemas de usabilidad: los filtros con checkboxes son difíciles de visualizar y operar, especialmente cuando hay muchas comunas o meses. La sección de filtros ocupa espacio excesivo y no sigue un diseño jerárquico claro. Además, cada pestaña de reportes muestra todos los datos sin filtrar por defecto, lo que genera gráficos saturados y poco útiles para el análisis.

Refactorizar la vista con un diseño coherente con `supervision.php` (selects, layout de grilla, botones Aplicar/Limpiar) mejorará significativamente la experiencia del supervisor al generar informes trimestrales o semestrales.

## What Changes

- **Eliminar filtros checkbox** de meses y comunas en `views/reportes.php`.
- **Nueva sección de filtros** con selects tipo `supervision.php`: Año, Meses (select múltiple o individual según diseño), Comuna, Establecimiento, y botones "Aplicar Filtros" / "Limpiar".
- **Mantener sistema de pestañas** actual que divide los 5 tipos de reportes (Total Errores, Plazos, Validador, Serie, Hoja).
- **Ajustar lógica por pestaña** con reglas estrictas de filtrado:
  - **Total Errores**: Agrupado por Establecimiento y Comuna.
  - **Plazos de Entrega**: Solo registros con `plazo_entrega = 'fuera_plazo'`.
  - **Uso del Validador**: Solo registros con `usa_validador = 'no'`.
  - **Errores por Serie**: Solo series que contengan errores (ocultar series limpias).
  - **Errores por Hojas**: Solo hojas que contengan errores (ocultar hojas limpias).
- **Cargar datos vía AJAX** según filtros aplicados, destruyendo y recreando gráficos Chart.js.

## Capabilities

### New Capabilities
- `reportes-filtros-refactor`: Nueva UI de filtros estilo supervision.php para la vista de reportes.
- `reportes-filtrado-negocio`: Lógica de filtrado estricta por pestaña (solo errores, solo fuera de plazo, solo sin validador, series/hojas con errores).

### Modified Capabilities
- *(Ninguna — no existen specs previos que modifiquen requisitos funcionales)*

## Impact

- **Vista**: `views/reportes.php` — refactorización completa de la sección de filtros.
- **Frontend**: `assets/js/charts.js` (posible adaptación de funciones de renderizado).
- **Backend API**: `api/reports.php` (ajustar endpoint `error-reports` para aceptar nuevos parámetros de filtro).
- **Modelo**: `models/Observation.php` (posible ajuste de métodos de reporte para filtros de establecimiento/comuna).
- **Dependencias**: Ninguna nueva, se reutiliza Chart.js existente.

## Non-goals

- No se modifica la vista `supervision.php`.
- No se agregan nuevos tipos de reportes (se mantienen los 5 actuales).
- No se implementa exportación a Excel/PDF desde esta refactorización.
- No se modifica el Dashboard.
