## Context

El frontend actual usa CSS custom puro (~1920 líneas en `assets/css/styles.css`) con metodología BEM. No hay framework CSS. Tabler es un template admin basado en Bootstrap 5 con 100+ componentes, MIT license, y soporte Sass para theming. Se migrará gradualmente para minimizar riesgo.

## Goals / Non-Goals

**Goals:**
- Reemplazar layout shell (sidebar + header) con componentes de Tabler
- Migrar las 10 vistas una por una usando clases Bootstrap 5/Tabler
- Mantener la paleta de colores actual (sky #0ea5e9 primario, navy #1e3a5f secundario)
- Chart.js debe seguir funcionando sin cambios

**Non-Goals:**
- No se cambia backend, rutas PHP, ni base de datos
- No se agregan nuevas funcionalidades
- No se modifica el login (`views/login.php`)

## Decisions

### Instalación vía CDN (no npm)
Tabler se cargará desde CDN para evitar compilar Sass localmente (no hay toolchain en el proyecto). Los overrides de color se harán con CSS variables sobre las clases de Bootstrap, no mediante variables Sass.

### Override de paleta con CSS (no Sass)
Como no hay compilador Sass en el proyecto, los colores se sobreescriben con un archivo `tabler-override.css` que usa `--tblr-primary: #0ea5e9` y demás variables CSS que Tabler expone. Esto evita instalar Node.js solo para build.

### Layout Tabler elegido: Vertical (navbar-vertical)
El layout actual tiene sidebar izquierda fija de 260px + header superior. Tabler ofrece `layout-vertical` que es casi idéntico: un `<aside class="navbar navbar-vertical">` + `<div class="page-wrapper">`. La migración es directa.

### Orden de migración de vistas
1. `includes/header.php` + `includes/sidebar.php` + `includes/footer.php` (base)
2. `dashboard.php` (tiene más componentes: cards, charts, tabla, modal)
3. `observaciones.php` + `supervision.php` (formularios + tablas)
4. `reportes.php` (tabs + tablas)
5. Vistas restantes: `usuarios.php`, `asignaciones.php`, `establecimientos.php`, `eliminadas.php`, `perfil.php`

Este orden maximiza el testing temprano de los componentes más complejos.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|-----------|
| Conflictos CSS entre estilos legacy y Bootstrap | Namespace: las vistas migradas usan clases Bootstrap puras; las no migradas mantienen clases BEM. El override CSS legacy se elimina al final. |
| Tabler/Bootstrap JS conflictúa con JS vanilla existente | Bootstrap usa eventos jQuery-style pero funciona sin jQuery. Se probará en fase 1 con un módulo (ej: modal) antes de migrar todo. |
| Chart.js deja de renderizar | Chart.js se carga desde footer.php independiente de Bootstrap. Se verifica en fase 1 después del layout shell. |
| Responsive se rompe en vistas no migradas | Mantener media queries legacy en styles.css hasta que la vista sea migrada. No eliminarlas antes. |
