## ADDED Requirements

### Requirement: Dashboard incluye kanban board de observaciones
El dashboard SHALL incluir un tablero kanban con columnas por estado (Pendiente, En Revisión, Aprobado, Rechazado, Justificado) donde cada observación se representa como una card movible.

#### Scenario: Kanban muestra observaciones por columna
- **WHEN** el usuario visualiza el kanban board
- **THEN** SHALL renderizar columnas con headers de estado usando `.card-header`
- **THEN** cada observación SHALL aparecer como card con: establecimiento, mes, tipo de error, registrador
- **THEN** las cards SHALL agruparse en la columna correspondiente a su estado_actual

#### Scenario: Supervisor puede mover cards entre columnas
- **WHEN** un supervisor arrastra una card a otra columna
- **THEN** el sistema SHALL actualizar el estado de la observación vía API
- **THEN** al soltar, la card SHALL permanecer en la nueva columna y mostrar toast de confirmación

#### Scenario: Registrador ve kanban de solo sus observaciones
- **WHEN** un registrador accede al kanban
- **THEN** solo SHALL mostrar sus propias observaciones
- **THEN** no podrá mover cards entre columnas (solo visual)

#### Scenario: Kanban vacío muestra estado empty
- **WHEN** no hay observaciones para mostrar
- **THEN** cada columna SHALL mostrar `.empty` con mensaje según estado
