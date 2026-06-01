## MODIFIED Requirements

### Requirement: Toggle de estado de establecimiento con form-switch
El control de activar/desactivar establecimiento SHALL usar `form-switch` de Tabler.

#### Scenario: Switch en listado de establecimientos
- **WHEN** el supervisor ve la tabla de establecimientos
- **THEN** la columna de estado SHALL mostrar un `form-switch`
- **AND** el switch SHALL estar en posición "on" para establecimientos activos y "off" para inactivos

#### Scenario: Cambio de estado con switch
- **WHEN** el supervisor hace clic en el switch
- **THEN** SHALL ejecutar la acción de activar/desactivar
- **AND** SHALL mostrar un toast de confirmación
