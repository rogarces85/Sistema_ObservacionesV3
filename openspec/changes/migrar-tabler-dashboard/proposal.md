## Why

El frontend actual es 100% CSS custom (~1920 líneas, sin framework), lo que dificulta el mantenimiento, la consistencia visual y la capacidad de respuesta. Migrar a **Tabler** (Bootstrap 5 + componentes preconstruidos) acelera el desarrollo, unifica la UI y facilita incorporar nuevas funcionalidades tipo dashboard.

## What Changes

- Instalar Tabler via CDN (free, MIT license)
- Reemplazar `includes/header.php` + `includes/sidebar.php` con layout shell de Tabler
- Crear `assets/css/tabler-override.css` con la paleta de colores actual (sky #0ea5e9 primario, navy #1e3a5f secundario, slate neutrals)
- Migrar las 10 vistas gradualmente, una por una, usando componentes Tabler (cards, tables, modals, forms, badges, toasts)
- Reemplazar JS nativo de modales/notificaciones por los componentes de Tabler/Bootstrap 5
- Eliminar estilos CSS legacy al final del proceso
- No hay cambios en backend, base de datos ni lógica de negocio

## Capabilities

### New Capabilities
- `dashboard-layout`: Layout shell con sidebar vertical responsive, header con navegación, footer con scripts. Usa componentes de Tabler (navbar-vertical, page-wrapper, etc.) y sobreescribe variables Sass para mantener la paleta actual.

### Modified Capabilities
<!-- No existing spec-level behavior changes — solo refactor de frontend -->

## Impact

| Archivo | Tipo de Cambio |
|---------|---------------|
| `includes/header.php` | Reemplazo completo con layout Tabler |
| `includes/sidebar.php` | Reemplazo completo con navbar-vertical Tabler |
| `includes/footer.php` | Reemplazo de scripts (Bootstrap JS + Tabler) |
| `assets/css/styles.css` | Eliminación progresiva, solo quedan overrides |
| `assets/js/app.js` | Refactor: modales con Bootstrap, toasts con Tabler |
| `views/*.php` (10 archivos) | Refactor de clases HTML a clases Bootstrap/Tabler |
| `assets/css/tabler-override.css` | **Nuevo** — overrides de color palette |
