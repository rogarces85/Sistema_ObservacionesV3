## Why

El sistema usa Tabler 1.4.0 como framework CSS principal, pero varias vistas aún dependen de clases CSS legacy (`styles.css`) que causan conflictos visuales, duplicación de estilos y una experiencia inconsistente. Además, hay componentes nativos de Tabler que no se están usando y que mejorarían significativamente la UX: tooltips declarados pero no inicializados, toasts personalizados en vez de los nativos, falta de skeleton loading, progress bars, timeline para historial, stepper para importación, y más.

## What Changes

- **Inicializar tooltips** de Bootstrap en `app.js` (actualmente declarados pero no funcionan)
- **Migrar `login.php` a Tabler** — la única vista 100% legacy, standalone
- **Reemplazar sistema de toasts custom** (`notifications.js` + 115 líneas CSS) por toasts nativos de Tabler/Bootstrap 5
- **Agregar skeleton loading** para reemplazar spinners en `supervision.php`, `eliminadas.php`, `asignaciones.php`
- **Agregar progress bars** para operaciones de importación y acciones masivas
- **Usar Tabler timeline** para el historial de cambios en el modal de detalle de supervisión
- **Usar Tabler stepper** para el flujo de importación en `observaciones.php`
- **Reemplazar botones de acción inline** en tablas por dropdown action menus (`...`)
- **Usar form-switch** para toggles de activar/desactivar en `usuarios.php` y `establecimientos.php`
- **Migrar clases legacy en JS-generated HTML** de `supervision.php`, `asignaciones.php`, `observaciones.php` a clases Tabler/Bootstrap
- **Eliminar definiciones duplicadas** de `.btn`, `.card`, `.badge`, `table`, `input` en `styles.css` que sobrescriben Tabler
- **Corregir orden de carga CSS** para que `styles.css` cargue antes de `tabler-override.css`
- **Corregir doble page-wrapper** anidado en `reportes.php`
- **Estandarizar colores de badges** (Tabler `bg-yellow text-yellow-fg` vs Bootstrap `bg-warning`)
- **Agregar status-dot** para indicadores compactos de estado

## Capabilities

### New Capabilities
- `tabler-components`: Integración de componentes Tabler faltantes (tooltips, skeleton, progress, timeline, stepper, dropdown actions, form-switch, status-dot)
- `toast-nativo`: Reemplazo del sistema de notificaciones custom por toasts nativos de Bootstrap 5/Tabler
- `login-tabler`: Migración de la página de login a componentes Tabler

### Modified Capabilities
- `mod-supervision`: Historial con timeline, skeleton loading, dropdown actions en tabla
- `mod-importacion`: Stepper para el flujo de importación, progress bar de carga
- `mod-usuarios`: form-switch para activar/desactivar usuarios
- `mod-establecimientos`: form-switch para activar/desactivar establecimientos
- `mod-asignaciones`: Migración de clases legacy en JS-generated HTML

## Non-goals

- No se migra el backend ni la API
- No se cambia la arquitectura MVC
- No se agrega dark mode completo (solo se prepara la infraestructura)
- No se rediseña el layout general del sistema
- No se agregan nuevas funcionalidades de negocio

## Impact

- **Archivos CSS**: `assets/css/styles.css` (reducción significativa), `assets/css/tabler-override.css`
- **Archivos JS**: `assets/js/app.js` (tooltips init), `assets/js/notifications.js` (reemplazo por toasts nativos)
- **Vistas**: `views/login.php`, `views/supervision.php`, `views/observaciones.php`, `views/asignaciones.php`, `views/usuarios.php`, `views/establecimientos.php`, `views/eliminadas.php`, `views/reportes.php`
- **Layout**: `includes/header.php`, `includes/footer.php`
- Sin impacto en backend, API ni base de datos
