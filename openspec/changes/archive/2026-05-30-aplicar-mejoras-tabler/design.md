## Context

El sistema usa Tabler 1.4.0 como framework CSS, pero la migración desde clases CSS legacy (`styles.css`) está incompleta. El archivo `styles.css` (2000+ líneas) redefine componentes que Tabler ya provee (`.btn`, `.card`, `.badge`, `table`, `input`), causando conflictos visuales. Además, componentes nativos de Tabler como tooltips, skeleton loading, progress bars, timeline, stepper, form-switch y dropdown actions no se están utilizando.

La página de login es la única vista 100% legacy (no carga Tabler). El sistema de notificaciones usa un módulo custom (`notifications.js` + 115 líneas CSS) que puede reemplazarse por toasts nativos de Bootstrap 5.

## Goals / Non-Goals

**Goals:**
- Inicializar tooltips de Bootstrap en todas las vistas
- Migrar `login.php` a componentes Tabler
- Reemplazar sistema de toasts custom por toasts nativos de Bootstrap 5
- Agregar skeleton loading, progress bars, timeline, stepper, form-switch, dropdown actions
- Migrar clases legacy en JS-generated HTML a clases Tabler/Bootstrap
- Eliminar definiciones duplicadas en `styles.css`
- Corregir orden de carga CSS y doble page-wrapper en `reportes.php`
- Estandarizar colores de badges

**Non-Goals:**
- No se implementa dark mode completo
- No se cambia la arquitectura del backend
- No se agregan nuevas funcionalidades de negocio
- No se rediseña el layout general

## Decisions

### D1: Toasts nativos de Bootstrap 5
Se reemplaza `notifications.js` por toasts nativos de Bootstrap 5. Se mantiene la API pública (`showSuccess`, `showError`, `showWarning`, `showInfo`) para compatibilidad con el código existente.
- Alternativa considerada: Mantener el sistema custom. Rechazada porque agrega 230+ líneas de código que duplican funcionalidad nativa.
- Implementación: Contenedor `toast-container` en `footer.php`, funciones en `app.js` que crean toasts dinámicamente usando la API de Bootstrap.

### D2: Tooltips inicializados en app.js
Se agrega inicialización global de tooltips en el `DOMContentLoaded` de `app.js`:
```js
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el))
```
- Esto activa los tooltips ya declarados en `observaciones.php`, `usuarios.php`, `eliminadas.php`, `establecimientos.php`.

### D3: Skeleton loading con placeholder-glow
Se usa `placeholder-glow` de Bootstrap 5 para skeletons en tablas. Se generan 5 filas skeleton con la misma estructura de columnas que la tabla real.
- Se reemplaza el spinner actual en `supervision.php`, `eliminadas.php`, `asignaciones.php`.

### D4: Timeline para historial
Se usa el componente `timeline` de Tabler para el historial de cambios en el modal de detalle de supervisión.
- Cada evento del historial se renderiza como un `timeline-item` con ícono, título, descripción y fecha.

### D5: Stepper para importación
Se usa el componente `steps` de Tabler para el flujo de importación.
- Pasos: "Seleccionar archivo" → "Vista previa" → "Confirmar"
- Se reemplaza el toggle `d-none`/`hidden` por un stepper visual con estado activo.

### D6: Dropdown actions en tablas
Se reemplazan los botones inline de acciones por un dropdown con ícono `...` (`btn-ghost-secondary btn-icon`).
- Cada acción es un `dropdown-item` con ícono SVG de Tabler.
- Se aplica en `observaciones.php` y `supervision.php`.

### D7: Form-switch para toggles
Se reemplazan los checkboxes de activar/desactivar por `form-switch` de Tabler.
- Se aplica en `usuarios.php` (activar/desactivar usuarios) y `establecimientos.php` (activar/desactivar establecimientos).
- El switch se conecta al endpoint API existente mediante `onchange`.

### D8: Login con Tabler
Se migra `login.php` a componentes Tabler:
- Layout: `page` + `page-wrapper` + `container-tight`
- Formulario: `card` + `card-body` + `form-control` + `form-label` + `form-select`
- Botón: `btn btn-primary w-100`
- Errores: `alert alert-danger`
- Se cargan `tabler.min.css`, `tabler-override.css`, `tabler.min.js`

### D9: Orden de carga CSS
Se cambia el orden de carga en `header.php` para que `styles.css` cargue ANTES de `tabler-override.css`:
```
tabler.min.css → styles.css → tabler-override.css
```
- Esto permite que `tabler-override.css` tenga la última palabra en conflictos.
- Se eliminan de `styles.css` las definiciones de `.btn`, `.card`, `.badge`, `table`, `input` que sobrescriben Tabler.

### D10: Estandarización de badges
Se unifica el uso de badges a la convención Tabler: `bg-yellow text-yellow-fg`, `bg-green text-green-fg`, `bg-red text-red-fg`, `bg-blue text-blue-fg`.
- Se reemplazan `bg-warning`, `bg-success`, `bg-danger`, `bg-info` en todas las vistas.

## Risks / Trade-offs

- **Riesgo**: Eliminar definiciones de `styles.css` puede romper vistas que aún usan clases legacy → Mitigación: eliminar gradualmente, probando cada vista después de cada cambio
- **Riesgo**: Cambiar el orden de carga CSS puede causar cambios visuales inesperados → Mitigación: probar todas las vistas después del cambio
- **Riesgo**: Los toasts nativos pueden tener diferente comportamiento que los custom → Mitigación: mantener la misma API pública y probar todos los flujos que muestran notificaciones
- **Trade-off**: Skeleton loading agrega complejidad al HTML → Beneficio: mejor UX percibida durante cargas
- **Trade-off**: Dropdown actions oculta las acciones bajo un clic extra → Beneficio: ahorra espacio horizontal, especialmente en móvil
