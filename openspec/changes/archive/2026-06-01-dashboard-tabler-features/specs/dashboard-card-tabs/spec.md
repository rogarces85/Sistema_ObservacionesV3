## ADDED Requirements

### Requirement: Cards del dashboard usan tabs para múltiples vistas
Las cards del dashboard que agrupan información SHALL utilizar `.nav-tabs.card-header-tabs` para permitir alternar entre diferentes vistas dentro de la misma card.

#### Scenario: Card de observaciones con tabs Recientes/Pendientes/Problemas
- **WHEN** el usuario visualiza la card "Últimas Observaciones"
- **THEN** SHALL mostrar tabs: "Recientes" | "Pendientes" | "Con Problemas"
- **WHEN** el usuario hace clic en un tab
- **THEN** la tabla SHALL filtrarse para mostrar solo observaciones de esa categoría

#### Scenario: Card de gráficos con tabs Gráfico/Tabla
- **WHEN** el usuario visualiza una card de gráficos
- **THEN** SHALL mostrar tabs: "Gráfico" | "Tabla de Datos"
- **WHEN** el usuario selecciona "Tabla de Datos"
- **THEN** SHALL mostrar una tabla con los datos numéricos del gráfico

#### Scenario: Card de acciones con tabs Acciones/Notificaciones
- **WHEN** el usuario visualiza la card de acciones rápidas
- **THEN** SHALL mostrar tabs: "Acciones" | "Notificaciones"
- **WHEN** el usuario selecciona "Notificaciones"
- **THEN** SHALL listar notificaciones recientes del sistema (si existen)

#### Scenario: Tabs persistentes entre navegaciones
- **WHEN** el usuario cambia de página y vuelve al dashboard
- **THEN** el tab activo en cada card SHALL recordarse vía localStorage
