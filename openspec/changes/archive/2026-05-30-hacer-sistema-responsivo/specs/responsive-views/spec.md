## ADDED Requirements

### Requirement: Tablas con scroll horizontal responsivo
Todas las tablas del sistema SHALL tener scroll horizontal en pantallas menores a 992px, manteniendo la primera columna (o las columnas clave) visibles al hacer scroll.

#### Scenario: Tabla con scroll responsivo en Dashboard
- **WHEN** la tabla de observaciones recientes tiene más columnas de las que caben en la pantalla
- **THEN** la tabla SHALL estar envuelta en un contenedor con `overflow-x: auto`
- **AND** las filas SHALL mantener su altura completa

#### Scenario: Tabla en vistas de gestión
- **WHEN** se visualizan las vistas `observaciones.php`, `supervision.php`, `usuarios.php`, `asignaciones.php`, `establecimientos.php`, `eliminadas.php`
- **AND** la pantalla tiene menos de 992px
- **THEN** las tablas SHALL tener scroll horizontal

### Requirement: Modales adaptables a móvil
Los modales SHALL ocupar el 100% del ancho en pantallas menores a 576px y mantener un máximo de 90% en tablets.

#### Scenario: Modal fullscreen en móvil
- **WHEN** la pantalla tiene menos de 576px de ancho
- **THEN** el modal SHALL ocupar 100% del ancho
- **AND** el modal SHALL tener bordes redondeados solo en la parte superior

#### Scenario: Modal en tablets
- **WHEN** la pantalla tiene entre 576px y 991px
- **THEN** el modal SHALL tener un ancho máximo de 95%

### Requirement: Formularios apilables en móvil
Los formularios con múltiples columnas SHALL apilar sus campos verticalmente en pantallas menores a 576px.

#### Scenario: Formulario de creación de observación
- **WHEN** la pantalla tiene menos de 576px de ancho
- **AND** el formulario tiene campos en grid de 2 o más columnas
- **THEN** los campos SHALL apilarse en una sola columna

#### Scenario: Formularios de filtros
- **WHEN** la pantalla tiene menos de 576px de ancho
- **THEN** los filtros de las tablas SHALL apilarse verticalmente

### Requirement: Touch targets mínimos de 44px
Todos los elementos interactivos (botones, enlaces, inputs) SHALL tener un área táctil mínima de 44x44px.

#### Scenario: Botones en formularios
- **WHEN** la pantalla tiene menos de 768px de ancho
- **THEN** los botones SHALL tener `min-height: 44px` y padding suficiente para el área táctil

#### Scenario: Elementos de navegación
- **WHEN** la pantalla tiene menos de 768px de ancho
- **THEN** los ítems del sidebar y menú SHALL tener altura mínima de 44px

### Requirement: Gráficos responsivos
Los gráficos de Chart.js SHALL redimensionarse automáticamente al ancho del contenedor.

#### Scenario: Gráficos en dashboard
- **WHEN** el contenedor del gráfico cambia de tamaño
- **THEN** el gráfico SHALL redibujarse para ajustarse al nuevo ancho

### Requirement: Vistas legacy con estilos Tabler responsivos
Las vistas que usan clases CSS legacy (dashboard, observaciones, supervision) SHALL migrar sus contenedores a clases Tabler responsivas.

#### Scenario: Dashboard responsivo
- **WHEN** la pantalla tiene menos de 576px de ancho
- **THEN** las tarjetas de estadísticas SHALL ocupar el ancho completo
- **AND** los gráficos SHALL apilarse verticalmente

#### Scenario: Cards de observaciones
- **WHEN** la pantalla tiene menos de 576px
- **THEN** las cards de observaciones SHALL tener padding reducido
