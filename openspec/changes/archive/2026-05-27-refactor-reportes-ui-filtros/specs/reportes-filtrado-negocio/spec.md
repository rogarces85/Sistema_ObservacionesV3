## ADDED Requirements

### Requirement: Filtrado estricto por pestaña
El sistema SHALL aplicar reglas de filtrado estrictas según la pestaña activa en la vista Reportes, mostrando únicamente los datos que cumplan con las condiciones de negocio definidas.

#### Scenario: Pestaña Total Errores
- **WHEN** el usuario selecciona la pestaña "Total Errores"
- **THEN** el listado muestra observaciones agrupadas por Establecimiento y Comuna

#### Scenario: Pestaña Plazos de Entrega
- **WHEN** el usuario selecciona la pestaña "Plazos de Entrega"
- **THEN** se muestran EXCLUSIVAMENTE observaciones con plazo_entrega = 'fuera_plazo', agrupadas por Comuna y Establecimiento

#### Scenario: Pestaña Uso del Validador
- **WHEN** el usuario selecciona la pestaña "Uso del Validador"
- **THEN** se muestran EXCLUSIVAMENTE observaciones con usa_validador = 'no', agrupadas por Comuna y Establecimiento

#### Scenario: Pestaña Errores por Serie
- **WHEN** el usuario selecciona la pestaña "Errores por Serie"
- **THEN** se muestran ÚNICAMENTE series que contengan errores registrados, ocultando series limpias

#### Scenario: Pestaña Errores por Hojas
- **WHEN** el usuario selecciona la pestaña "Errores por Hojas"
- **THEN** se muestran ÚNICAMENTE hojas que contengan errores registrados, ocultando hojas limpias
