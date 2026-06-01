## ADDED Requirements

### Requirement: Interfaz de login responsiva
La interfaz de inicio de sesión SHALL adaptarse al tamaño de pantalla del dispositivo.

#### Scenario: Layout responsivo del login
- **WHEN** la pantalla tiene menos de 576px de ancho
- **THEN** el formulario SHALL ocupar el 90% del viewport
- **AND** el selector de año SHALL estar debajo de los campos de credenciales

#### Scenario: Mensajes de error visibles en móvil
- **WHEN** ocurre un error de autenticación en una pantalla menor a 576px
- **THEN** el mensaje de error SHALL mostrarse en un tamaño legible sin romper el layout

### Requirement: Sesión persistente en cambio de viewport
La sesión del usuario NO SHALL perderse al cambiar entre orientaciones del dispositivo (landscape/portrait).

#### Scenario: Rotación de dispositivo
- **WHEN** el usuario rota su dispositivo móvil durante la sesión
- **THEN** la sesión SHALL permanecer activa
- **AND** la interfaz SHALL reajustarse al nuevo tamaño sin errores
