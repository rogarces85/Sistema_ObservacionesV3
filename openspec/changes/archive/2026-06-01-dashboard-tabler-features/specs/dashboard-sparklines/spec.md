## ADDED Requirements

### Requirement: Stat cards incluyen sparklines de tendencia
Cada stat card del dashboard SHALL incluir un mini-gráfico sparkline que visualice la tendencia de la métrica correspondiente en los últimos 7 días.

#### Scenario: Sparkline muestra tendencia de total observaciones
- **WHEN** el dashboard carga
- **THEN** la stat card "Total Registradas" SHALL incluir un sparkline horizontal mostrando la evolución diaria de observaciones creadas
- **THEN** el sparkline SHALL usar color `--tblr-primary`

#### Scenario: Sparkline muestra tendencia de pendientes
- **WHEN** el dashboard carga
- **THEN** la stat card "Pendientes" SHALL incluir un sparkline con tendencia de observaciones pendientes
- **THEN** el sparkline SHALL usar color `--tblr-warning`

#### Scenario: Sparkline sin datos históricos
- **WHEN** no existen datos históricos suficientes para el período
- **THEN** la stat card SHALL mostrar solo el número sin sparkline
- **THEN** no SHALL mostrar error ni placeholder vacío
