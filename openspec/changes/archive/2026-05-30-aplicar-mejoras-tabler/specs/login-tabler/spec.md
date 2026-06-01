## ADDED Requirements

### Requirement: Login con componentes Tabler
La página de login SHALL usar componentes Tabler/Bootstrap 5 en lugar de clases CSS legacy.

#### Scenario: Layout de login con Tabler
- **WHEN** el usuario accede a la página de login
- **THEN** la página SHALL usar `page`, `page-wrapper`, `container-tight` de Tabler
- **AND** el formulario SHALL estar dentro de un `card` de Tabler

#### Scenario: Formulario de login con Tabler
- **WHEN** el usuario ve el formulario de login
- **THEN** los campos SHALL usar `form-control`, `form-label`, `form-select` de Tabler
- **AND** el botón SHALL usar `btn btn-primary w-100` de Tabler

#### Scenario: Mensajes de error con Tabler
- **WHEN** ocurre un error de autenticación
- **THEN** el mensaje SHALL mostrarse usando `alert alert-danger` de Tabler

### Requirement: Login carga Tabler CSS y JS
La página de login SHALL cargar los assets de Tabler.

#### Scenario: Assets de Tabler en login
- **WHEN** la página de login se renderiza
- **THEN** SHALL incluir `tabler.min.css`, `tabler-override.css` y `tabler.min.js`
- **AND** SHALL incluir la fuente Inter de Google Fonts

### Requirement: Login responsivo con Tabler
La página de login SHALL ser responsiva usando el grid de Bootstrap 5.

#### Scenario: Login en móvil
- **WHEN** la pantalla tiene menos de 576px
- **THEN** el formulario SHALL ocupar el ancho completo con padding lateral
- **AND** los inputs SHALL tener font-size 16px para prevenir zoom en iOS

#### Scenario: Login en escritorio
- **WHEN** la pantalla tiene 768px o más
- **THEN** el formulario SHALL tener un ancho máximo de 420px centrado
