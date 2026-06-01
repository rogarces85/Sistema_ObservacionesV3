## ADDED Requirements

### Requirement: Dashboard visualiza progress steps del flujo de trabajo
El dashboard SHALL incluir un componente `.steps` de Tabler que visualice el ciclo de vida de una observación: Registrada → En Revisión → Aprobada/Rechazada → Resuelta.

#### Scenario: Progress steps muestra estado actual de observaciones
- **WHEN** el usuario visualiza el componente de progress steps
- **THEN** SHALL mostrar los 4 pasos del flujo con indicadores `.step-item`
- **THEN** el paso actual de las observaciones pendientes SHALL resaltar como activo

#### Scenario: Progress steps para supervisores muestra resumen
- **WHEN** un supervisor accede al dashboard
- **THEN** el progress steps SHALL mostrar conteo de observaciones en cada paso del flujo
- **THEN** cada paso SHALL ser clickeable para filtrar la tabla a ese estado

#### Scenario: Progress steps para registradores muestra sus observaciones
- **WHEN** un registrador accede al dashboard
- **THEN** el progress steps SHALL reflejar solo el estado de sus propias observaciones
- **THEN** los pasos sin observaciones SHALL aparecer como inactivos
