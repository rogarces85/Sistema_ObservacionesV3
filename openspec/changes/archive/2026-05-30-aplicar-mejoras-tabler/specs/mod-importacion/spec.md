## MODIFIED Requirements

### Requirement: Flujo de importación con stepper
El modal de importación SHALL usar un stepper visual de Tabler para indicar el progreso del flujo.

#### Scenario: Stepper visible en importación
- **WHEN** el usuario abre el modal de importación
- **THEN** SHALL mostrar un stepper con pasos: "Archivo" → "Vista previa" → "Confirmar"
- **AND** el paso activo SHALL estar resaltado

#### Scenario: Progress bar durante importación
- **WHEN** el usuario confirma la importación
- **THEN** SHALL mostrar una `progress-bar` animada durante el procesamiento

### Requirement: Vista previa de importación sin clases legacy
La tabla de vista previa de importación SHALL usar clases Tabler.

#### Scenario: Preview table con Tabler
- **WHEN** se muestra la vista previa del archivo importado
- **THEN** la tabla SHALL usar `table table-sm table-vcenter` de Tabler
- **AND** las filas SHALL usar clases Tabler en lugar de legacy
