## ADDED Requirements

### Requirement: Login responsivo
La página de login SHALL adaptarse a cualquier tamaño de pantalla, centrando vertical y horizontalmente el formulario.

#### Scenario: Login en escritorio
- **WHEN** la pantalla tiene 768px o más de ancho
- **THEN** el formulario de login SHALL mostrarse centrado con un ancho máximo de 400px

#### Scenario: Login en móvil
- **WHEN** la pantalla tiene menos de 576px de ancho
- **THEN** el formulario de login SHALL ocupar el 90% del ancho de la pantalla
- **AND** los campos SHALL tener tamaño de fuente 16px para prevenir zoom automático en iOS

#### Scenario: Login en tablets
- **WHEN** la pantalla tiene entre 576px y 767px
- **THEN** el formulario de login SHALL tener un ancho máximo de 360px
