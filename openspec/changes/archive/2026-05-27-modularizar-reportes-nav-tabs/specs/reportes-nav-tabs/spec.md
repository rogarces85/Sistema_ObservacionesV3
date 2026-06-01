## ADDED Requirements

### Requirement: Nav-tabs en Reportes
El sistema SHALL proveer un sistema de navegación por pestañas (nav-tabs) en la vista Reportes, con 5 pestañas identificadas: Total Errores, Plazos Entrega, Uso Validador, Errores por Serie, Errores por Hoja.

#### Scenario: Carga de la vista Reportes
- **WHEN** el usuario accede a la vista Reportes
- **THEN** se muestran las pestañas y por defecto está activa "Total Errores"

#### Scenario: Cambio de pestaña
- **WHEN** el usuario hace clic en una pestaña
- **THEN** se muestra únicamente el contenido (gráfico y tabla) de esa pestaña, ocultando las demás

### Requirement: Lazy loading por pestaña
El sistema SHALL cargar los datos del gráfico de una pestaña solo cuando el usuario la active por primera vez, evitando cargas innecesarias.

#### Scenario: Primera vez en pestaña
- **WHEN** el usuario hace clic en una pestaña por primera vez
- **THEN** se realiza la petición AJAX para obtener los datos de esa pestaña

#### Scenario: Volver a pestaña ya cargada
- **WHEN** el usuario regresa a una pestaña previamente cargada
- **THEN** los datos se muestran desde caché sin nueva petición AJAX

### Requirement: Filtros globales persistentes
El sistema SHALL mantener los filtros (Año, Mes, Comuna, Establecimiento) visibles y aplicables en todo momento, independientemente de la pestaña activa.

#### Scenario: Aplicar filtros desde cualquier pestaña
- **WHEN** el usuario aplica filtros estando en cualquier pestaña
- **THEN** el gráfico de la pestaña activa se actualiza con los nuevos filtros

#### Scenario: Cambiar de pestaña con filtros aplicados
- **WHEN** el usuario cambia de pestaña con filtros aplicados
- **THEN** el nuevo gráfico se carga respetando los filtros actuales
