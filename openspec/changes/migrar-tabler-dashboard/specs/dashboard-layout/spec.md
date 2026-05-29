## ADDED Requirements

### Requirement: Sidebar vertical con navegación
The sidebar SHALL use Tabler `<aside class="navbar navbar-vertical">` layout, with navigation items agrupados por sección (Dashboard, Gestión, Reportes, Configuración). Debe ser responsiva: overlay en móvil, visible en desktop. SHALL mostrar el nombre del sistema "Sistema REM" y "Servicio de Salud" como branding.

#### Scenario: Sidebar se renderiza en desktop
- **WHEN** un usuario autenticado carga cualquier página del sistema en un viewport ≥ 768px
- **THEN** la sidebar SHALL mostrarse visible a la izquierda, con 260px de ancho, mostrando todos los grupos de navegación permitidos según el rol del usuario

#### Scenario: Sidebar se oculta en móvil
- **WHEN** un usuario carga el sistema en un viewport < 768px
- **THEN** la sidebar SHALL estar oculta inicialmente, y un botón hamburguesa SHALL mostrarse en el header para alternar su visibilidad

### Requirement: Header con selector de año y datos del usuario
The header SHALL usar `<header class="navbar navbar-expand-md">` de Tabler, mostrando: botón hamburguesa (móvil), búsqueda, selector de año, botón de notificaciones, y menú de usuario con avatar, nombre, rol y botón de cerrar sesión.

#### Scenario: Header muestra información del usuario
- **WHEN** un usuario autenticado carga el sistema
- **THEN** el header SHALL mostrar las iniciales del usuario en un avatar circular, su nombre completo, su rol, y un botón de cerrar sesión

#### Scenario: Selector de año cambia el contexto
- **WHEN** el usuario selecciona un año diferente en el `<select>` del header
- **THEN** la página SHALL recargarse con el nuevo año como parámetro `?year=` y la sesión SHALL actualizar el año activo

### Requirement: Paleta de colores institucional
The Tabler theme SHALL usar `--tblr-primary: #0ea5e9`, `--tblr-secondary: #1e3a5f`, y la escala de grises slate existente. SHALL definirse en `assets/css/tabler-override.css` como variables CSS sobre Tabler.

#### Scenario: Colores se aplican consistentemente
- **WHEN** cualquier componente Tabler se renderiza (botones, enlaces, badges)
- **THEN** el color primario SHALL ser `#0ea5e9` (sky) y el secundario SHALL ser `#1e3a5f` (navy)

### Requirement: Migración gradual sin regresiones
Las vistas aún no migradas SHALL seguir funcionando con las clases CSS legacy. No SHALL haber regresiones visuales en las vistas no migradas mientras se realiza la migración.

#### Scenario: Vista no migrada funciona correctamente
- **WHEN** se carga una vista que aún usa clases BEM legacy
- **THEN** los estilos legacy SHALL seguir aplicándose correctamente, sin interferencia de Tabler
