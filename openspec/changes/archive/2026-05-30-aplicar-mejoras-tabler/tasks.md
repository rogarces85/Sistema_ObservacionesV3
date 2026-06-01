## 1. CSS — Limpieza y Orden de Carga

- [x] 1.1 Cambiar orden de carga CSS en `includes/header.php`: `tabler.min.css` → `styles.css` → `tabler-override.css`
- [x] 1.2 Eliminar definición duplicada de `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-danger` en `styles.css`
- [x] 1.3 Eliminar definición duplicada de `.card` y `.card:hover` en `styles.css`
- [x] 1.4 Eliminar definición duplicada de `.badge` y variantes en `styles.css`
- [x] 1.5 Eliminar definición duplicada de `table`, `thead`, `th`, `td`, `tr:hover` en `styles.css`
- [x] 1.6 Eliminar definición duplicada de `input`, `select`, `textarea` y focus states en `styles.css`
- [x] 1.7 Eliminar definición duplicada de `.form-label`, `.form-input`, `.form-select`, `.form-textarea` en `styles.css`
- [x] 1.8 Eliminar CSS de toasts custom (`.toast-container`, `.toast`, `.toast-*`) en `styles.css`

## 2. Frontend — Login con Tabler

- [x] 2.1 Agregar carga de `tabler.min.css`, `tabler-override.css`, `tabler.min.js` e Inter font en `views/login.php`
- [x] 2.2 Reemplazar layout de login: `page` + `page-wrapper` + `container-tight` + `card`
- [x] 2.3 Reemplazar campos del formulario con `form-control`, `form-label`, `form-select` de Tabler
- [x] 2.4 Reemplazar botón con `btn btn-primary w-100` de Tabler
- [x] 2.5 Reemplazar mensajes de error/éxito con `alert alert-danger` / `alert alert-success` de Tabler
- [x] 2.6 Eliminar clases legacy del login (`flex`, `items-center`, `text-3xl`, `rounded-xl`, etc.)

## 3. Frontend JS — Toasts Nativos y Tooltips

- [x] 3.1 Agregar contenedor de toasts en `includes/footer.php` usando `toast-container` de Bootstrap 5
- [x] 3.2 Implementar `showSuccess()`, `showError()`, `showWarning()`, `showInfo()` en `app.js` usando toasts nativos de Bootstrap
- [x] 3.3 Eliminar `assets/js/notifications.js` y su referencia en `header.php`
- [x] 3.4 Agregar inicialización de tooltips en `DOMContentLoaded` de `app.js`

## 4. Frontend — Componentes Tabler en Vistas

- [x] 4.1 Agregar skeleton loading en tabla de `supervision.php` (reemplazar spinner)
- [x] 4.2 Agregar skeleton loading en tabla de `eliminadas.php` (reemplazar spinner)
- [x] 4.3 Agregar skeleton loading en tabla de `asignaciones.php` (reemplazar spinner)
- [x] 4.4 Reemplazar historial de cambios en modal de detalle de `supervision.php` con `timeline` de Tabler
- [x] 4.5 Agregar stepper en modal de importación de `observaciones.php`
- [x] 4.6 Agregar progress bar en confirmación de importación de `observaciones.php`
- [x] 4.7 Reemplazar botones de acción inline por dropdown actions en tabla de `observaciones.php`
- [x] 4.8 Reemplazar botones de acción inline por dropdown actions en tabla de `supervision.php`
- [x] 4.9 Reemplazar checkbox de activar/desactivar por `form-switch` en `usuarios.php`
- [x] 4.10 Reemplazar checkbox de activar/desactivar por `form-switch` en `establecimientos.php`

## 5. Frontend — Migración de Clases Legacy en JS

- [x] 5.1 Migrar clases legacy en JS-generated HTML de `supervision.php` (filas de tabla, badges, estados)
- [x] 5.2 Migrar clases legacy en JS-generated HTML de `observaciones.php` (detalle modal, import preview)
- [x] 5.3 Migrar clases legacy en JS-generated HTML de `asignaciones.php` (lista registradores, establecimientos, disponibles)
- [x] 5.4 Migrar clases legacy en JS-generated HTML de `reportes.php` (filas de tabla, empty states)
- [x] 5.5 Corregir doble `page-wrapper` anidado en `reportes.php`
- [x] 5.6 Estandarizar badges a convención Tabler (`bg-yellow text-yellow-fg`, etc.) en todas las vistas

## 6. Frontend — Progress Bars y Status Dots

- [x] 6.1 Agregar progress bar en acciones masivas de `supervision.php` (aprobar/cancelar/eliminar múltiples)
- [x] 6.2 Agregar `status-dot` para indicadores compactos de estado en tablas

## 7. Verificación

- [x] 7.1 Verificar que tooltips funcionan en todas las vistas
- [x] 7.2 Verificar que toasts nativos se muestran correctamente (éxito, error, warning, info)
- [x] 7.3 Verificar que login se ve correctamente en móvil, tablet y escritorio
- [x] 7.4 Verificar que skeleton loading se muestra durante cargas AJAX
- [x] 7.5 Verificar que dropdown actions funcionan en tablas
- [x] 7.6 Verificar que form-switch funciona para activar/desactivar
- [x] 7.7 Verificar que timeline muestra el historial correctamente
- [x] 7.8 Verificar que stepper muestra el progreso de importación
- [x] 7.9 Verificar que no hay regressiones visuales en ninguna vista
