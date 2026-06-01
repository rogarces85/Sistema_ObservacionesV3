## 1. CSS — Layout Responsivo (sidebar, header, footer)

- [x] 1.1 Agregar meta viewport tag en `<head>` de `includes/header.php`
- [x] 1.2 Refactorizar sidebar para usar colapso nativo de Tabler (`navbar-vertical` + `collapse`) en `includes/sidebar.php`
- [x] 1.3 Agregar CSS para sidebar overlay en móviles (<768px) en `styles.css`
- [x] 1.4 Hacer header adaptativo: ocultar búsqueda y selector de año en <576px
- [x] 1.5 Ajustar footer responsivo: padding reducido y texto centrado en <576px
- [x] 1.6 Cambiar `container-xl` a `container-fluid` en <768px para maximizar espacio
- [x] 1.7 Consolidar media queries existentes en `styles.css` usando breakpoints Tabler estándar

## 2. CSS — Vistas Responsivas (tablas, modales, formularios)

- [x] 2.1 Envolver todas las tablas en `<div class="table-responsive">` en todas las vistas
- [x] 2.2 Agregar CSS para modales fullscreen en <576px en `styles.css`
- [x] 2.3 Agregar clases `.modal-dialog-centered` y `.modal-dialog-scrollable` a modales existentes
- [x] 2.4 Hacer formularios apilables en <576px (inputs en una sola columna)
- [x] 2.5 Agregar touch targets mínimos de 44px para botones e inputs en <768px
- [x] 2.6 Reemplazar modales manuales legacy por modales Tabler en `dashboard.php`, `observaciones.php`, `supervision.php`

## 3. CSS — Login Responsivo

- [x] 3.1 Rediseñar login con layout flexbox centrado (vertical y horizontal)
- [x] 3.2 Establecer ancho máximo responsivo del formulario (400px escritorio, 360px tablet, 90% móvil)
- [x] 3.3 Asegurar font-size 16px en inputs de login para prevenir zoom automático en iOS
- [ ] 3.4 Verificar que mensajes de error no rompan el layout responsivo

## 4. JavaScript — Sidebar Toggle y Chart.js

- [x] 4.1 Integrar sidebar toggle con Tabler JS en lugar del toggle manual en `app.js`
- [x] 4.2 Configurar Chart.js con `resize: true` y responsive en todos los gráficos
- [x] 4.3 Asegurar que los contenedores de gráficos tengan `width: 100%`
- [ ] 4.4 Probar que tooltips y dropdowns no se corten en viewports pequeños

## 5. Vistas Legacy — Migración a Tabler Responsivo

- [x] 5.1 Migrar `views/dashboard.php` a contenedores Tabler con clases responsivas
- [x] 5.2 Migrar `views/observaciones.php` a contenedores Tabler con clases responsivas
- [x] 5.3 Migrar `views/supervision.php` a contenedores Tabler con clases responsivas
- [x] 5.4 Verificar que vistas Tabler (`reportes.php`, `usuarios.php`, `perfil.php`, `asignaciones.php`, `establecimientos.php`, `eliminadas.php`) ya usan clases responsivas correctas

## 6. Verificación

- [x] 6.1 Probar todas las vistas en resolución <576px (móvil)
- [x] 6.2 Probar todas las vistas en resolución 768px (tablet)
- [x] 6.3 Probar todas las vistas en resolución ≥992px (escritorio)
- [x] 6.4 Verificar que no hay scroll horizontal en ninguna vista
- [x] 6.5 Verificar sidebar toggle funcional en móvil con overlay
- [x] 6.6 Verificar que login se ve correctamente en los 3 breakpoints
- [x] 6.7 Probar rotación de dispositivo (landscape/portrait) sin pérdida de sesión
- [x] 6.8 Verificar que gráficos Chart.js se redimensionan correctamente
