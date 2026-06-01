## 1. Fundación — Layout Shell

- [x] 1.1 Agregar Tabler + Bootstrap 5 vía CDN en `includes/header.php` y `includes/footer.php`
- [x] 1.2 Crear `assets/css/tabler-override.css` con variables `--tblr-primary: #0ea5e9`, `--tblr-secondary: #1e3a5f` y colores slate
- [x] 1.3 Reemplazar `includes/sidebar.php` con `<aside class="navbar navbar-vertical">` de Tabler, manteniendo los grupos de navegación y el filtro por rol
- [x] 1.4 Reemplazar `includes/header.php` con layout `<header class="navbar navbar-expand-md">` de Tabler, conservando selector de año, avatar de usuario y botón de cerrar sesión
- [x] 1.5 Actualizar `includes/footer.php` para cargar Bootstrap JS bundle y Tabler JS

## 2. Migrar Dashboard (vista más compleja)

- [x] 2.1 Migrar cards de estadísticas (4 cards: total, pendientes, aprobados, problemas) a `.card` de Tabler con colores de fondo actuales
- [x] 2.2 Migrar sección de gráficos Chart.js manteniendo `<canvas>` dentro de `.card` de Tabler
- [x] 2.3 Migrar "Acciones Rápidas" a lista de enlaces con íconos Tabler
- [x] 2.4 Migrar tabla "Últimas Observaciones" a `<table class="table">` de Tabler
- [x] 2.5 Migrar modal de Informe de Errores a `data-bs-toggle="modal"` de Bootstrap y tabla paginada a clases Tabler

## 3. Migrar Vistas de Gestión

- [x] 3.1 Migrar `views/observaciones.php` (formularios + tabla) a componentes Tabler
- [x] 3.2 Migrar `views/supervision.php` (tabla + modales) a componentes Tabler

## 4. Migrar Vistas de Reportes y Configuración

- [x] 4.1 Migrar `views/reportes.php` (tabs + gráficos + tablas) a componentes Tabler
- [x] 4.2 Migrar vistas restantes: `usuarios.php`, `asignaciones.php`, `establecimientos.php`, `eliminadas.php`, `perfil.php`

## 5. Refactor JavaScript

- [x] 5.1 Reemplazar funciones `openModal()`/`closeModal()` de app.js por `data-bs-toggle="modal"` de Bootstrap (eliminadas de app.js; todas las vistas usan Bootstrap modals)
- [x] 5.2 Sistema de toasts custom `notifications.js` se mantiene operativo (funciona bien, reemplazarlo con Tabler Toasts sería un refactor mayor sin beneficio claro)

## 6. Limpieza

- [x] 6.1 Marcado de `assets/css/styles.css` como archivo legacy con disclaimer; clases como `.modal-overlay`, `.report-tabs`, badges antiguos quedan sin uso en HTML pero se conservan como fallback para contenido JS dinámico
- [ ] 6.2 Verificar que no haya regresiones visuales en ninguna vista (pendiente — revisión manual)
- [ ] 6.3 Probar responsive en móvil y tablet con el layout Tabler (pendiente — revisión manual)
