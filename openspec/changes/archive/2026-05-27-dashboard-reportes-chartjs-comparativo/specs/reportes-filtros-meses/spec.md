## ADDED Requirements

### Requirement: Filtros de meses con selección múltiple en Reportes
El sistema SHALL proveer en la vista Reportes filtros de meses mediante checkboxes individuales que permitan seleccionar múltiples meses simultáneamente para acumular datos en los reportes.

#### Scenario: Selección múltiple de meses
- **WHEN** el usuario marca los checkboxes de "Enero", "Febrero" y "Marzo"
- **THEN** los 5 gráficos de Reportes se actualizan mostrando datos acumulados de esos tres meses

#### Scenario: Todos los meses seleccionados por defecto
- **WHEN** el usuario carga la vista Reportes
- **THEN** todos los checkboxes de meses están marcados por defecto

### Requirement: Botones rápidos de trimestre y semestre en Reportes
El sistema SHALL proveer botones rápidos en la vista Reportes para seleccionar trimestres (Q1, Q2, Q3, Q4) y semestres (H1, H2), además de un botón "Todos".

#### Scenario: Selección rápida de Q1 en Reportes
- **WHEN** el usuario hace clic en el botón "Q1"
- **THEN** los checkboxes de Enero, Febrero y Marzo se marcan y los gráficos se actualizan

#### Scenario: Selección rápida de H2 en Reportes
- **WHEN** el usuario hace clic en el botón "H2"
- **THEN** los checkboxes de Julio a Diciembre se marcan y los gráficos se actualizan

#### Scenario: Botón "Todos" en Reportes
- **WHEN** el usuario hace clic en el botón "Todos"
- **THEN** todos los checkboxes de meses se marcan y los gráficos muestran datos del año completo

### Requirement: Estado de filtros sincronizado
El sistema SHALL mantener sincronizados los checkboxes individuales de meses con el estado de los botones rápidos, de modo que la selección visual sea consistente.

#### Scenario: Consistencia visual de botones
- **WHEN** el usuario marca manualmente los meses de Q1
- **THEN** el botón "Q1" refleja visualmente que está activo (ej: resaltado)

#### Scenario: Deselección parcial
- **WHEN** el usuario desmarca un mes de un trimestre previamente seleccionado
- **THEN** el botón de ese trimestre deja de estar resaltado, pero los demás meses seleccionados permanecen activos
