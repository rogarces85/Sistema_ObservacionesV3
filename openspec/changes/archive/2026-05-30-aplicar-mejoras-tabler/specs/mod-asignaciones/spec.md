## MODIFIED Requirements

### Requirement: JS-generated HTML sin clases legacy en asignaciones
El HTML generado por JavaScript en asignaciones SHALL usar clases Tabler/Bootstrap.

#### Scenario: Lista de registradores con Tabler
- **WHEN** JavaScript genera la lista de registradores
- **THEN** SHALL usar `list-group`, `list-group-item` de Tabler
- **AND** los badges SHALL usar `badge bg-*` de Tabler

#### Scenario: Lista de establecimientos disponibles con Tabler
- **WHEN** JavaScript genera la lista de establecimientos disponibles
- **THEN** SHALL usar `list-group` de Tabler
- **AND** los estados SHALL usar `badge` de Tabler

#### Scenario: Empty states con componente Tabler
- **WHEN** no hay datos para mostrar
- **THEN** SHALL usar el componente `empty` de Tabler (`empty`, `empty-header`, `empty-title`)
