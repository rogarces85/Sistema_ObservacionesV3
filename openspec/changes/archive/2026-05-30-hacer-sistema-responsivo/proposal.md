## Why

El sistema actual funciona correctamente en escritorio pero tiene deficiencias en tablets y móviles: sidebar no colapsa correctamente, tablas sin scroll horizontal en pantallas pequeñas, y formularios/modal no se adaptan. Con el aumento de acceso desde dispositivos móviles, es necesario garantizar una experiencia de usuario consistente en todos los tamaños de pantalla.

## What Changes

- Sidebar colapsable completamente funcional en móviles con overlay y toggle suave
- Tablas con scroll horizontal responsivo en todas las vistas
- Modales adaptables a pantallas pequeñas (fullscreen en móvil)
- Formularios con inputs apilables en móviles
- Login page responsiva
- Header adaptativo: ocultar elementos según espacio disponible
- Mejorar touch targets (mínimo 44px) en todos los elementos interactivos
- Eliminar scroll horizontal en vistas principales
- Unificar estilos responsivos entre vistas legacy (CSS custom) y vistas Tabler

## Capabilities

### New Capabilities
- `responsive-layout`: Layout responsivo del sistema (sidebar, header, footer, contenedor principal)
- `responsive-views`: Adaptación de todas las vistas del sistema a distintos dispositivos

### Modified Capabilities
- `login`: La página de login debe ser responsiva
- `mod-auth`: El layout de sesión debe adaptarse a móviles

## Impact

- Archivos afectados: `assets/css/styles.css`, `assets/css/tabler-override.css`, `assets/js/app.js`
- Vistas afectadas: `views/login.php`, `views/dashboard.php`, `views/observaciones.php`, `views/supervision.php`, `views/reportes.php`, `views/usuarios.php`, `views/perfil.php`, `views/asignaciones.php`, `views/establecimientos.php`, `views/eliminadas.php`
- Layout: `includes/header.php`, `includes/footer.php`
- No afecta backend, API ni base de datos
- No agrega dependencias externas
