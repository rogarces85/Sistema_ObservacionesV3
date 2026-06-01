## ADDED Requirements

### Requirement: Cards de gráficos incluyen dropdown filters
Los headers de las cards de gráficos del dashboard SHALL incluir dropdown menus para filtrar los datos mostrados sin recargar la página.

#### Scenario: Filtro de año/mes en gráfico de tendencia
- **WHEN** el usuario abre el dropdown del header "Observaciones por Mes"
- **THEN** SHALL mostrar opciones de filtro por año y mes
- **WHEN** el usuario selecciona un filtro
- **THEN** el gráfico SHALL actualizarse vía AJAX con los datos filtrados

#### Scenario: Filtro de comuna en gráfico de distribución
- **WHEN** el usuario abre el dropdown del header "Distribución por Estado"
- **THEN** SHALL listar comunas disponibles
- **WHEN** el usuario selecciona una comuna
- **THEN** el gráfico SHALL filtrar observaciones de esa comuna

#### Scenario: Filtros sincronizados con página de reportes
- **WHEN** el usuario aplica filtros en el dashboard
- **THEN** al navegar a la página de reportes, los mismos filtros SHALL pre-aplicarse
- **THEN** los filtros SHALL persistir en sessionStorage durante la sesión

#### Scenario: Reset de filtros
- **WHEN** el usuario hace clic en "Limpiar filtros"
- **THEN** todos los dropdown filters SHALL volver a su estado por defecto
- **THEN** los gráficos SHALL restaurar datos sin filtrar
