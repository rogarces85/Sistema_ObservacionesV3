## ADDED Requirements

### Requirement: Dashboard cards use Tabler utility classes without inline styles
The 4 statistics cards SHALL use Tabler utility classes for colors instead of inline style attributes.

#### Scenario: Total card uses bg-primary-lt
- **WHEN** the dashboard renders the total observations card
- **THEN** it SHALL have class `bg-primary-lt` instead of `style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%)"`

#### Scenario: Pending card uses bg-warning-lt
- **WHEN** the dashboard renders the pending observations card
- **THEN** it SHALL have class `bg-warning-lt` instead of `style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)"`

#### Scenario: Approved card uses bg-success-lt
- **WHEN** the dashboard renders the approved observations card
- **THEN** it SHALL have class `bg-success-lt` instead of `style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%)"`

#### Scenario: Problems card uses bg-danger-lt
- **WHEN** the dashboard renders the problems card
- **THEN** it SHALL have class `bg-danger-lt` instead of `style="background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%)"`

### Requirement: Card icon containers use solid background colors
The icon container inside each card SHALL use solid background utility classes instead of gradient inline styles.

#### Scenario: Total card icon uses bg-primary
- **WHEN** the total card renders
- **THEN** its icon container SHALL have `class="bg-primary"` instead of `style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%)"`

#### Scenario: Pending card icon uses bg-warning
- **WHEN** the pending card renders
- **THEN** its icon container SHALL have `class="bg-warning"` instead of `style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%)"`

#### Scenario: Approved card icon uses bg-success
- **WHEN** the approved card renders
- **THEN** its icon container SHALL have `class="bg-success"` instead of `style="background: linear-gradient(135deg, #10b981 0%, #059669 100%)"`

#### Scenario: Problems card icon uses bg-danger
- **WHEN** the problems card renders
- **THEN** its icon container SHALL have `class="bg-danger"` instead of `style="background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%)"`

### Requirement: Card text values use appropriate color classes
The numeric value and label text inside each card SHALL use Tabler text color utility classes.

#### Scenario: Total card text uses text-primary
- **WHEN** the total card renders
- **THEN** its numeric value SHALL have class `text-primary` and label SHALL have class `text-secondary`

#### Scenario: Pending card text uses text-warning
- **WHEN** the pending card renders
- **THEN** its numeric value SHALL have class `text-warning` and label SHALL have class `text-warning`

#### Scenario: Approved card text uses text-success
- **WHEN** the approved card renders
- **THEN** its numeric value SHALL have class `text-success` and label SHALL have class `text-success`

#### Scenario: Problems card text uses text-danger
- **WHEN** the problems card renders
- **THEN** its numeric value SHALL have class `text-danger` and label SHALL have class `text-danger`

### Requirement: Emoji icons replaced by Tabler SVG icons
All emoji characters used as icons SHALL be replaced by inline Tabler SVG icons.

#### Scenario: Total card shows chart-bar icon
- **WHEN** the total card renders
- **THEN** its icon SHALL be a `<svg>` with Tabler `chart-bar` icon path instead of 📊 emoji

#### Scenario: Pending card shows clock icon
- **WHEN** the pending card renders
- **THEN** its icon SHALL be a `<svg>` with Tabler `clock-hour-4` icon path instead of ⏳ emoji

#### Scenario: Approved card shows circle-check icon
- **WHEN** the approved card renders
- **THEN** its icon SHALL be a `<svg>` with Tabler `circle-check` icon path instead of ✅ emoji

#### Scenario: Problems card shows alert-triangle icon
- **WHEN** the problems card renders
- **THEN** its icon SHALL be a `<svg>` with Tabler `alert-triangle` icon path instead of ⚠️ emoji

### Requirement: Chart section headers use SVG icons
The chart section card titles SHALL use Tabler SVG icons instead of emojis.

#### Scenario: Distribution chart uses chart-pie icon
- **WHEN** the "Distribución por Estado" card renders
- **THEN** its title SHALL contain a `<svg>` with Tabler `chart-pie` icon instead of 📈 emoji

#### Scenario: Error types chart uses search icon
- **WHEN** the "Top Tipos de Error" card renders
- **THEN** its title SHALL contain a `<svg>` with Tabler `search` icon instead of 🔍 emoji

#### Scenario: Quick actions uses zap icon
- **WHEN** the "Acciones Rápidas" card renders
- **THEN** its title SHALL contain a `<svg>` with Tabler `zap` icon instead of ⚡ emoji

### Requirement: Monthly chart title uses calendar icon
The card title for "Observaciones por Mes" SHALL use a Tabler SVG calendar icon.

#### Scenario: Monthly chart header
- **WHEN** the monthly chart card renders
- **THEN** its title SHALL contain a `<svg>` with Tabler `calendar` icon instead of 📅 emoji

### Requirement: Recent observations table title uses clipboard-list icon
The card title for "Últimas Observaciones" SHALL use a Tabler SVG clipboard icon.

#### Scenario: Recent table header
- **WHEN** the recent observations table card renders
- **THEN** its title SHALL contain a `<svg>` with Tabler `clipboard-list` icon instead of 📋 emoji

### Requirement: Alerts use Tabler alert-icon component
The warning banners for "no assignments" and "registrators without assignments" SHALL use the Tabler `.alert-icon` component with SVG icons.

#### Scenario: Registrator no-assignment alert
- **WHEN** a registrador has no assigned establishments
- **THEN** the alert SHALL use `<div class="alert alert-warning alert-icon">` with an SVG icon

#### Scenario: Supervisor registrators-without-assignment alert
- **WHEN** there are registradores without assignments
- **THEN** the alert SHALL use `<div class="alert alert-danger alert-icon">` with an SVG icon

### Requirement: Quick actions list items use SVG icons
Each list item in the "Acciones Rápidas" section SHALL use Tabler SVG icons instead of emojis.

#### Scenario: New observation action
- **WHEN** the "Nueva Observación" link renders
- **THEN** its icon SHALL be a `<svg>` with Tabler `edit` icon instead of 📝 emoji

#### Scenario: Download template action
- **WHEN** the "Descargar Plantilla" link renders
- **THEN** its icon SHALL be a `<svg>` with Tabler `download` icon instead of 📥 emoji

#### Scenario: Generate reports action
- **WHEN** the "Generar Reportes" link renders
- **THEN** its icon SHALL be a `<svg>` with Tabler `chart-bar` icon instead of 📊 emoji

#### Scenario: Supervise action
- **WHEN** the "Supervisar" link renders
- **THEN** its icon SHALL be a `<svg>` with Tabler `eye` icon instead of 👁️ emoji

#### Scenario: Error report action
- **WHEN** the "Informe de Errores" button renders
- **THEN** its icon SHALL be a `<svg>` with Tabler `file-text` icon instead of 📄 emoji

### Requirement: No inline styles remain in dashboard
The dashboard view SHALL NOT contain any `style` attribute on HTML elements.

#### Scenario: Zero inline style attributes
- **WHEN** the dashboard HTML is inspected
- **THEN** there SHALL be zero occurrences of `style="` in the file, except for the span badge inline (which will be cleaned in a separate pass)
