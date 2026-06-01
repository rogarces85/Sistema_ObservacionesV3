## MODIFIED Requirements

### Requirement: Toggle de estado de usuario con form-switch
El control de activar/desactivar usuario SHALL usar `form-switch` de Tabler.

#### Scenario: Switch en listado de usuarios
- **WHEN** el supervisor ve la tabla de usuarios
- **THEN** la columna de estado SHALL mostrar un `form-switch`
- **AND** el switch SHALL estar en posición "on" para usuarios activos y "off" para inactivos

#### Scenario: Cambio de estado con switch
- **WHEN** el supervisor hace clic en el switch
- **THEN** SHALL ejecutar la acción de activar/desactivar
- **AND** SHALL mostrar un toast de confirmación
