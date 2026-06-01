## Why

La página de Reportes actual muestra los 5 gráficos (Total Errores, Plazos Entrega, Uso Validador, Errores por Serie, Errores por Hoja) todos en una misma vista, uno debajo del otro. Esto genera una página muy larga, difícil de navegar y visualmente abrumadora. Los supervisores necesitan una interfaz más limpia y modular donde puedan enfocarse en un tipo de reporte a la vez, reduciendo el scroll y mejorando la legibilidad.

Implementar un sistema de pestañas (nav-tabs) permite dividir la página en módulos independientes, manteniendo los filtros globales visibles siempre y mostrando solo el gráfico activo.

## What Changes

- **Agregar sistema de navegación por pestañas** (nav-tabs) en `views/reportes.php` con 5 tabs: Total Errores, Plazos Entrega, Uso Validador, Errores por Serie, Errores por Hoja.
- **Mover cada gráfico a su propia pestaña**: solo el contenido de la pestaña activa se renderiza y muestra.
- **Mantener filtros globales visibles** en todo momento, arriba de las pestañas.
- **Adaptar JavaScript** para cargar datos del gráfico correspondiente al cambiar de pestaña (lazy loading o recarga según diseño).
- **Diseño responsive**: las pestañas deben adaptarse a móvil (scroll horizontal o menú desplegable).

## Capabilities

### New Capabilities
- `reportes-nav-tabs`: Sistema de navegación por pestañas en la vista de Reportes, con lazy loading de gráficos por tab.

### Modified Capabilities
- *(Ninguna — no existen specs previos que modifiquen requisitos funcionales)*

## Impact

- **Vista**: `views/reportes.php` — refactorización de layout para incluir nav-tabs.
- **Frontend**: JavaScript inline en `views/reportes.php` (adaptar `loadErrorReports()` para cargar solo el gráfico activo).
- **Backend API**: Sin cambios en endpoints; se reutiliza `api/reports.php` con el endpoint `error-reports` existente.
- **CSS**: Posibles ajustes menores en `assets/css/styles.css` para estilos de tabs.

## Non-goals

- No se modifica la lógica de filtrado ni los endpoints del backend.
- No se agregan nuevos tipos de reportes.
- No se modifica el Dashboard ni Supervisión.
