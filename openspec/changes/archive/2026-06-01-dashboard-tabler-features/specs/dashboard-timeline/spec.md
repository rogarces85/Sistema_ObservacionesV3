## ADDED Requirements

### Requirement: Dashboard muestra timeline de actividad reciente
El dashboard SHALL incluir un componente de timeline visual que muestre los eventos más recientes del sistema (creación de observaciones, cambios de estado, aprobaciones) ordenados cronológicamente.

#### Scenario: Timeline muestra eventos recientes
- **WHEN** el usuario accede al dashboard
- **THEN** SHALL renderizar un componente `.timeline` con los últimos eventos ordenados por fecha descendente
- **THEN** cada evento SHALL mostrar: icono según tipo, descripción, usuario responsable y timestamp relativo

#### Scenario: Timeline filtra eventos por rol
- **WHEN** un registrador accede al dashboard
- **THEN** el timeline SHALL mostrar solo eventos relacionados con sus observaciones y establecimientos asignados
- **WHEN** un supervisor accede al dashboard
- **THEN** el timeline SHALL mostrar todos los eventos del sistema

#### Scenario: Timeline muestra estado vacío
- **WHEN** no hay eventos recientes para mostrar
- **THEN** SHALL renderizar `.empty` con mensaje "No hay actividad reciente"
