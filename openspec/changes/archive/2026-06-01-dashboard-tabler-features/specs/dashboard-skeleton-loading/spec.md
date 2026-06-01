## ADDED Requirements

### Requirement: Dashboard muestra skeleton loading durante carga
Durante la carga inicial de datos del dashboard, los componentes que aún no tienen datos SHALL mostrar skeleton screens animados en lugar de espacios vacíos o spinners genéricos.

#### Scenario: Stat cards muestran skeleton
- **WHEN** el dashboard inicia la carga de estadísticas
- **THEN** cada stat card SHALL mostrar `.skeleton` animado en lugar del número y texto
- **THEN** al completarse la carga, los skeletons SHALL reemplazarse por los datos reales con transición fade

#### Scenario: Tabla muestra skeleton rows
- **WHEN** la tabla de últimas observaciones está cargando
- **THEN** SHALL mostrar 5 filas de `.skeleton-line` como placeholder
- **THEN** al recibir datos, los placeholders SHALL reemplazarse por las filas reales

#### Scenario: Gráficos muestran skeleton containers
- **WHEN** los gráficos ApexCharts están inicializando
- **THEN** el contenedor del gráfico SHALL mostrar `.skeleton` con proporción 16:9
- **THEN** al renderizar el chart, el skeleton SHALL desaparecer
