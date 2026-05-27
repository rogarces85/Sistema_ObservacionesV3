## ADDED Requirements

### Requirement: Dashboard renderiza gráficos con Chart.js
El sistema SHALL mostrar los tres gráficos del Dashboard (D1, D2, D3) usando Chart.js en elementos `<canvas>`, reemplazando las visualizaciones CSS puro existentes.

#### Scenario: Carga inicial del Dashboard
- **WHEN** el usuario accede al Dashboard
- **THEN** los tres gráficos se renderizan con Chart.js y muestran datos del año actual y, para D3, el año anterior como comparativo

#### Scenario: Gráfico D1 muestra valores visibles
- **WHEN** el gráfico D1 (Distribución por Estado) se renderiza
- **THEN** cada barra muestra su valor numérico sobre la barra usando el plugin datalabels

#### Scenario: Gráfico D2 muestra valores visibles
- **WHEN** el gráfico D2 (Top Tipos de Error) se renderiza
- **THEN** cada barra muestra su valor numérico sobre la barra usando el plugin datalabels

### Requirement: Dashboard carga datos vía AJAX
El sistema SHALL cargar los datos de los gráficos del Dashboard mediante una llamada AJAX a un endpoint API, sin recargar la página.

#### Scenario: Cambio de filtros actualiza gráficos
- **WHEN** el usuario cambia los filtros de año o meses
- **THEN** los gráficos se actualizan con nuevos datos vía AJAX mostrando un indicador de carga

#### Scenario: Endpoint devuelve datos comparativos
- **WHEN** el frontend solicita datos al endpoint `dashboard-stats` con dos años seleccionados
- **THEN** el endpoint responde con datos de ambos años organizados por estado, tipo de error y mes

### Requirement: Comparativo de dos años en gráfico de meses
El sistema SHALL permitir seleccionar hasta dos años en el Dashboard y mostrar el gráfico D3 (Observaciones por Mes) como barras agrupadas comparando ambos años.

#### Scenario: Selección de año comparativo
- **WHEN** el usuario selecciona un segundo año en el filtro de años
- **THEN** el gráfico D3 muestra barras agrupadas para cada mes, con un color distinto por año

#### Scenario: Solo un año seleccionado
- **WHEN** el usuario selecciona un solo año
- **THEN** el gráfico D3 muestra solo un dataset con barras de un solo color

### Requirement: Filtros de meses con botones rápidos
El sistema SHALL proveer en el Dashboard filtros de meses mediante checkboxes individuales y botones rápidos para seleccionar trimestres (Q1-Q4) y semestres (H1-H2).

#### Scenario: Selección por trimestre
- **WHEN** el usuario hace clic en el botón "Q1"
- **THEN** los checkboxes de Enero, Febrero y Marzo se marcan como seleccionados

#### Scenario: Selección por semestre
- **WHEN** el usuario hace clic en el botón "H1"
- **THEN** los checkboxes de Enero a Junio se marcan como seleccionados

#### Scenario: Selección de todos los meses
- **WHEN** el usuario hace clic en el botón "Todos"
- **THEN** todos los checkboxes de meses se marcan como seleccionados

#### Scenario: Acumulación de filtros en gráficos
- **WHEN** el usuario selecciona Q1 y Q2
- **THEN** los gráficos muestran datos acumulados de Enero a Junio
