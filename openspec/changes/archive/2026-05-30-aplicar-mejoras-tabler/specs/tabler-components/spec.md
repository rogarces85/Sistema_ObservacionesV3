## ADDED Requirements

### Requirement: Tooltips inicializados automáticamente
El sistema SHALL inicializar todos los tooltips declarados con `data-bs-toggle="tooltip"` al cargar la página.

#### Scenario: Tooltips funcionan en todas las vistas
- **WHEN** la página carga completamente
- **THEN** todos los elementos con `data-bs-toggle="tooltip"` SHALL mostrar su tooltip al hacer hover
- **AND** los tooltips SHALL usar la configuración por defecto de Bootstrap 5

### Requirement: Skeleton loading en tablas
Las tablas que cargan datos vía AJAX SHALL mostrar skeleton placeholders en lugar de spinners.

#### Scenario: Skeleton en tabla de supervisión
- **WHEN** la tabla de observaciones está cargando datos
- **THEN** SHALL mostrar 5 filas skeleton con `placeholder-glow`
- **AND** las filas skeleton SHALL coincidir con la estructura de columnas de la tabla

#### Scenario: Skeleton en tabla de eliminadas
- **WHEN** la tabla de observaciones eliminadas está cargando
- **THEN** SHALL mostrar skeleton placeholders en lugar del spinner actual

### Requirement: Progress bars para operaciones largas
Las operaciones que toman más de 2 segundos SHALL mostrar una barra de progreso.

#### Scenario: Progress bar en importación
- **WHEN** el usuario confirma una importación masiva
- **THEN** SHALL mostrar una `progress-bar` animada indicando el progreso

#### Scenario: Progress bar en acciones masivas
- **WHEN** el supervisor ejecuta acciones masivas (aprobar/cancelar/eliminar múltiples)
- **THEN** SHALL mostrar una `progress-bar` con el conteo de procesados

### Requirement: Timeline para historial de cambios
El historial de cambios de una observación SHALL mostrarse usando el componente `timeline` de Tabler.

#### Scenario: Timeline en detalle de supervisión
- **WHEN** el supervisor abre el detalle de una observación
- **THEN** el historial SHALL mostrarse como un `timeline` vertical
- **AND** cada evento SHALL mostrar usuario, fecha, cambio de estado y comentario

### Requirement: Stepper para flujos multi-paso
Los flujos con múltiples pasos SHALL usar el componente `steps` de Tabler.

#### Scenario: Stepper en importación
- **WHEN** el usuario abre el modal de importación
- **THEN** SHALL mostrar un stepper con los pasos: "Seleccionar archivo" → "Vista previa" → "Confirmar"
- **AND** el paso activo SHALL estar visualmente destacado

### Requirement: Dropdown action menus en tablas
Las acciones por fila en tablas SHALL agruparse en un dropdown menu (`...`) en lugar de botones inline.

#### Scenario: Dropdown en tabla de observaciones
- **WHEN** la tabla muestra acciones por fila (ver, editar, etc.)
- **THEN** las acciones SHALL estar dentro de un dropdown con ícono `...`
- **AND** el dropdown SHALL abrirse al hacer clic

#### Scenario: Dropdown en tabla de supervisión
- **WHEN** la tabla muestra acciones por fila (ver, aprobar, cancelar, eliminar)
- **THEN** las acciones SHALL estar dentro de un dropdown
- **AND** cada acción SHALL tener su ícono correspondiente

### Requirement: Form-switch para toggles
Los controles de activar/desactivar SHALL usar `form-switch` en lugar de checkboxes.

#### Scenario: Switch para activar usuario
- **WHEN** el supervisor ve la lista de usuarios
- **THEN** el estado activo/inactivo SHALL mostrarse como un `form-switch`

#### Scenario: Switch para activar establecimiento
- **WHEN** el supervisor ve la lista de establecimientos
- **THEN** el estado activo/inactivo SHALL mostrarse como un `form-switch`

### Requirement: Status-dot para indicadores compactos
Los indicadores de estado en contextos compactos SHALL usar `status-dot` de Tabler.

#### Scenario: Status-dot en listas
- **WHEN** se muestra el estado de una observación en una lista compacta
- **THEN** SHALL usar un `status-dot` con el color correspondiente al estado
