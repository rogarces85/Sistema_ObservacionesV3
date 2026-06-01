## ADDED Requirements

### Requirement: Toast notifications use Bootstrap Toast
The system SHALL replace custom `notifications.js` with Bootstrap Toast component via `bootstrap.Toast` API.

#### Scenario: Success toast appears
- **WHEN** an operation succeeds (e.g., observation saved)
- **THEN** a toast notification SHALL appear using `.toast` component with `.bg-success` or `.text-bg-success`
- **THEN** the toast SHALL auto-hide after 4 seconds

#### Scenario: Error toast appears
- **WHEN** an operation fails
- **THEN** a toast notification SHALL appear using `.toast` component with `.bg-danger` or `.text-bg-danger`

### Requirement: Empty states use Tabler .empty component
Empty data states SHALL use `<div class="empty">` with `.empty-icon`, `.empty-title`, `.empty-subtitle`, and `.empty-action`.

#### Scenario: No observations shows empty state
- **WHEN** there are no observations to display
- **THEN** the view SHALL render `<div class="empty">` with title and action button

### Requirement: Loading states use Tabler spinners
Loading indicators SHALL use `.spinner-border` and/or `.animated-dots` instead of custom overlay.

#### Scenario: Data is loading
- **WHEN** an async operation is in progress
- **THEN** a `.spinner-border` SHALL be displayed
- **THEN** the loading text SHALL use `.animated-dots`

### Requirement: Modals use Bootstrap modal component
All modal dialogs SHALL use Bootstrap `.modal` structure with `.modal-dialog`, `.modal-content`, `.modal-header`, `.modal-body`, `.modal-footer`.

#### Scenario: Modal opens on button click
- **WHEN** user clicks a button with `data-bs-toggle="modal"` and `data-bs-target="#id"`
- **THEN** the modal with matching `id` SHALL open
- **THEN** the modal SHALL have proper `tabindex="-1"` and `aria-labelledby`

### Requirement: Tables use Tabler table classes
Data tables SHALL use `.table.table-vcenter.card-table` inside `.table-responsive`.

#### Scenario: Table renders with Tabler styling
- **WHEN** a data table is displayed
- **THEN** it SHALL have class `.table.table-vcenter`
- **THEN** it SHALL be wrapped in `.table-responsive`

### Requirement: Badges use Tabler badge classes
Status indicators SHALL use `.badge.bg-{color}.text-{color}-fg` consistent with Tabler palette.

#### Scenario: Observation status shows as badge
- **WHEN** showing observation estado_actual
- **THEN** it SHALL render as `<span class="badge bg-{color} text-{color}-fg">`

### Requirement: Forms use Tabler form classes
All form elements SHALL use `.form-control`, `.form-select`, `.form-label`, `.form-hint`, `.form-check`, `.form-switch` as appropriate.

#### Scenario: Input renders with Tabler styling
- **WHEN** a text input is rendered
- **THEN** it SHALL have class `.form-control`

#### Scenario: Select renders with Tabler styling
- **WHEN** a select dropdown is rendered
- **THEN** it SHALL have class `.form-select`

### Requirement: Alerts use Tabler alert component
Alert messages SHALL use `.alert.alert-{context}` with optional `.alert-icon` and `.alert-description`.

#### Scenario: Warning alert displays
- **WHEN** a warning condition exists (e.g., no assignments)
- **THEN** it SHALL render as `<div class="alert alert-warning">` with icon

### Requirement: Cards use Tabler card-status for colored accents
Stat cards and highlighted cards SHALL use `.card-status-top.bg-{color}` instead of inline gradient backgrounds.

#### Scenario: Stat card shows with status bar
- **WHEN** displaying a stat card (e.g., total registradas)
- **THEN** the card SHALL include `.card-status-top` with matching color
