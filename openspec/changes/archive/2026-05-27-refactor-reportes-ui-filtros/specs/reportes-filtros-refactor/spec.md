## ADDED Requirements

### Requirement: Filtros estilo supervision.php
El sistema SHALL proveer en la vista Reportes una sección de filtros con componentes `<select>` organizados en una grilla responsive, siguiendo el mismo diseño y estructura de la vista supervision.php.

#### Scenario: Carga de la vista Reportes
- **WHEN** el usuario accede a la vista Reportes
- **THEN** los filtros se muestran como selects desplegables con opción "Todos" seleccionada por defecto

#### Scenario: Aplicar filtros
- **WHEN** el usuario selecciona valores en los filtros y hace clic en "Aplicar Filtros"
- **THEN** los gráficos de la pestaña activa se actualizan con los datos filtrados

#### Scenario: Limpiar filtros
- **WHEN** el usuario hace clic en "Limpiar"
- **THEN** todos los filtros vuelven a "Todos" y los gráficos muestran datos sin filtrar

### Requirement: Eliminación de checkboxes
El system SHALL eliminar los filtros basados en checkboxes de meses y comunas de la vista Reportes.

#### Scenario: Sin checkboxes visibles
- **WHEN** el usuario carga la vista Reportes
- **THEN** no se muestran checkboxes para seleccionar meses o comunas
