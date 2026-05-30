## ADDED Requirements

### Requirement: Estado chart uses doughnut type
The "Distribución por Estado" chart SHALL use Chart.js `type: 'doughnut'` instead of horizontal bar.

#### Scenario: Estado chart is doughnut
- **WHEN** `createEstadoChart()` is called
- **THEN** the chart SHALL be created with `type: 'doughnut'`

#### Scenario: Estado chart shows 5 state segments
- **WHEN** the estado chart renders
- **THEN** it SHALL display one segment per estado (pendiente, aprobado, rechazado, error, justificado) with its corresponding color from `PALETTE_SISTEMA`

### Requirement: Tendencia chart uses line type with fill
The "Observaciones por Mes" chart SHALL use Chart.js `type: 'line'` with a gradient fill area instead of vertical bar.

#### Scenario: Tendencia chart is line with fill
- **WHEN** `createTendenciaChart()` is called
- **THEN** the chart SHALL be created with `type: 'line'` and a fill gradient using `PALETTE_TENDENCIA`

#### Scenario: Tendencia chart has no fill if single data point
- **WHEN** the data has fewer than 2 points
- **THEN** the fill SHALL be disabled to avoid rendering artifacts

### Requirement: TipoError chart remains horizontal bar
The "Top Tipos de Error" chart SHALL remain as `type: 'bar'` with `indexAxis: 'y'`.

#### Scenario: TipoError chart is horizontal bar
- **WHEN** `createTipoErrorChart()` is called
- **THEN** the chart SHALL be created with `type: 'bar'` and `indexAxis: 'y'`

### Requirement: Tooltips use clean Tabler style without emojis or unicode bars
Tooltips SHALL NOT contain emoji characters or unicode bar chart characters.

#### Scenario: Tooltip shows label, value and percentage
- **WHEN** a user hovers over any chart segment
- **THEN** the tooltip SHALL display the dataset label, the numeric value, and percentage (for doughnut/line) without emojis or unicode bars

### Requirement: No chartEffects plugin registered
The custom `chartEffects` plugin (gradient bars + hover dim) SHALL be removed from the codebase for dashboard charts.

#### Scenario: chartEffects is not registered
- **WHEN** any dashboard chart initializes
- **THEN** the `chartEffects` plugin SHALL NOT be registered with Chart.js

### Requirement: Datalabels use simple style without text stroke
Datalabels SHALL NOT use `textStrokeColor` or `textStrokeWidth` properties.

#### Scenario: Datalabels have no text stroke
- **WHEN** a datalabel renders on any dashboard chart
- **THEN** it SHALL have no `textStrokeColor` or `textStrokeWidth` styling

### Requirement: Doughnut chart has legend
The doughnut chart SHALL display a legend showing each state label and color.

#### Scenario: Doughnut legend visible
- **WHEN** the doughnut chart renders
- **THEN** the legend SHALL be displayed at the bottom with colored dots and state labels
