## ADDED Requirements

### Requirement: Sidebar colapsable en móviles
El sidebar SHALL ser ocultable en pantallas menores a 768px mediante un botón toggle, y SHALL mostrarse como overlay semitransparente al activarse.

#### Scenario: Sidebar oculto por defecto en móvil
- **WHEN** la pantalla tiene menos de 768px de ancho
- **THEN** el sidebar SHALL estar oculto (transformado fuera de la vista)
- **AND** el contenido principal SHALL ocupar el ancho completo

#### Scenario: Sidebar se abre con toggle
- **WHEN** el usuario hace clic en el botón de menú móvil
- **THEN** el sidebar SHALL deslizarse hacia la vista con animación suave
- **AND** un overlay semitransparente SHALL cubrir el contenido principal

#### Scenario: Sidebar se cierra al hacer clic fuera
- **WHEN** el sidebar está abierto y el usuario hace clic en el overlay
- **THEN** el sidebar SHALL cerrarse

### Requirement: Header adaptativo
El header SHALL ocultar elementos no esenciales en pantallas pequeñas (búsqueda, selector de año si aplica) y mostrar solo el toggle de menú y el perfil de usuario.

#### Scenario: Header compacto en móvil
- **WHEN** la pantalla tiene menos de 576px de ancho
- **THEN** el header SHALL mostrar solo el botón toggle de menú, el nombre de la app y el avatar del usuario

#### Scenario: Header completo en escritorio
- **WHEN** la pantalla tiene 768px o más de ancho
- **THEN** el header SHALL mostrar todos los elementos (búsqueda, selector de año, notificaciones, perfil)

### Requirement: Footer responsivo
El footer SHALL centrar su contenido y adaptar el espaciado en pantallas pequeñas.

#### Scenario: Footer en móvil
- **WHEN** la pantalla tiene menos de 576px de ancho
- **THEN** el footer SHALL tener padding reducido y texto centrado

### Requirement: Contenedor principal fluido
El contenedor principal (`#main-content`) SHALL usar `container-fluid` en pantallas pequeñas para maximizar el espacio disponible.

#### Scenario: Contenido sin scroll horizontal
- **WHEN** la pantalla tiene menos de 768px de ancho
- **THEN** el contenedor principal SHALL usar `container-fluid` en vez de `container-xl`
- **AND** NO SHALL haber scroll horizontal en la página
