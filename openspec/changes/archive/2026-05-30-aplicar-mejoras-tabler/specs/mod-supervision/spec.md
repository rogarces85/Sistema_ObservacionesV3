## MODIFIED Requirements

### Requirement: Panel de supervisión con componentes Tabler
El panel de supervisión SHALL usar componentes Tabler para filtros, tabla y modales.

#### Scenario: Filtros con grid Tabler
- **WHEN** el supervisor ve los filtros
- **THEN** los filtros SHALL usar `row`/`col-*` de Tabler en lugar de `grid` legacy
- **AND** los selects SHALL usar `form-select` de Tabler

#### Scenario: Tabla con skeleton loading
- **WHEN** la tabla está cargando observaciones
- **THEN** SHALL mostrar skeleton placeholders en lugar del spinner actual

#### Scenario: Acciones de fila con dropdown
- **WHEN** la tabla muestra las acciones por fila
- **THEN** las acciones SHALL estar en un dropdown menu (`...`)
- **AND** cada acción SHALL tener su ícono Tabler correspondiente

#### Scenario: Historial con timeline
- **WHEN** el supervisor abre el detalle de una observación
- **THEN** el historial de cambios SHALL mostrarse como `timeline` de Tabler

### Requirement: JS-generated HTML sin clases legacy
El HTML generado por JavaScript en supervisión SHALL usar clases Tabler/Bootstrap.

#### Scenario: Filas de tabla sin clases legacy
- **WHEN** JavaScript genera las filas de la tabla
- **THEN** SHALL usar `fw-semibold`, `text-secondary`, `small` en lugar de `font-medium`, `text-slate-500`, `text-xs`
