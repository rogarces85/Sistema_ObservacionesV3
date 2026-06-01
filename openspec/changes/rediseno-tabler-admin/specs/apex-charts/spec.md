## ADDED Requirements

### Requirement: Charts use ApexCharts library
All data visualizations SHALL use ApexCharts instead of Chart.js. The ApexCharts library SHALL be loaded from CDN.

#### Scenario: ApexCharts CDN loads
- **WHEN** any page with charts loads
- **THEN** SHALL include `<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>`

### Requirement: Chart colors use Tabler CSS variables
All chart color configurations SHALL reference Tabler CSS variables (`var(--tblr-primary)`, `var(--tblr-success)`, etc.) for theme consistency.

#### Scenario: Bar chart uses Tabler primary color
- **WHEN** a bar chart renders
- **THEN** its fill color SHALL reference `var(--tblr-primary)`
- **THEN** hover state SHALL reference `var(--tblr-primary-rgb)`

### Requirement: Dashboard charts render correctly
The dashboard SHALL render 3 charts: estado distribution (horizontal bar), top error types (horizontal bar), and monthly trend (vertical bar).

#### Scenario: Estado chart renders
- **WHEN** dashboard loads with stats data
- **THEN** a horizontal bar chart SHALL render in `#chartEstado` showing counts by estado_actual
- **THEN** bars SHALL use status colors (pending=yellow, approved=green, error=red, justified=blue)

#### Scenario: Top error types chart renders
- **WHEN** dashboard loads with stats data
- **THEN** a horizontal bar chart SHALL render in `#chartTipoError` showing top error types by count
- **THEN** chart SHALL limit to top 10 error types

#### Scenario: Monthly trend chart renders
- **WHEN** dashboard loads with stats data
- **THEN** a vertical bar chart SHALL render in `#chartTendencia` showing observations per month
- **THEN** the x-axis SHALL show month names (Enero through Diciembre)

### Requirement: Report charts render correctly
The reports page SHALL render 5 charts: errors by establishment, plazos, validador, serie, hoja.

#### Scenario: Report tab charts render
- **WHEN** user opens reports page and selects a tab
- **THEN** the corresponding chart SHALL render in the active tab pane
- **THEN** each chart SHALL be a horizontal bar chart (except hoja which is vertical)

### Requirement: Chart export button
Each chart SHALL have an export-to-PNG button rendered alongside the chart container.

#### Scenario: Export button is present
- **WHEN** any chart is rendered
- **THEN** a download/export button SHALL appear near the chart
- **THEN** clicking it SHALL download the chart as PNG image

### Requirement: Charts have data labels
All bar charts SHALL display data labels (value counts) on or near the bars.

#### Scenario: Bar chart shows values
- **WHEN** a bar chart renders
- **THEN** each bar SHALL have a data label showing its numeric value
- **THEN** labels SHALL be positioned at the end of each bar

### Requirement: Charts have tooltips
All charts SHALL display tooltips on hover showing the category name, value, and percentage.

#### Scenario: Hover shows tooltip
- **WHEN** user hovers over a bar/point
- **THEN** a tooltip SHALL appear with category name and value
