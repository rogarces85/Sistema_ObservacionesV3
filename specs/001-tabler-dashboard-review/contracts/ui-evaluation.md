# Contract: UI Evaluation Report

The output of this feature must be a reviewable UI evaluation report. This is a documentation contract, not a runtime API contract.

## Required Sections

### 1. Executive Summary

Must include:

- Final recommendation: `consolidate`, `consolidate_with_conditions`, `postpone`, or `partial_revert`.
- One-paragraph rationale.
- Top 3 risks.
- Top 3 next actions.

### 2. Pros and Cons

Must include at least:

- 5 pros of Tabler in the REM system context.
- 5 cons/risks of Tabler in the REM system context.
- Mitigation for each high-impact risk.

### 3. Asset Strategy Comparison

Must compare at least:

| Strategy | Availability | Maintenance | Customization | Recommendation |
|---|---|---|---|---|
| CDN | Required | Required | Required | Required |
| Controlled local assets | Required | Required | Required | Required |

Package/build pipeline may be included as an optional third strategy.

### 4. View Evaluation Matrix

Must include one row per view:

| View | Path | Roles Checked | Tabler Status | Responsive | Accessibility | Business Flow | Recommendation |
|---|---|---|---|---|---|---|---|
| Dashboard | `views/dashboard.php` | supervisor, registrador | Required | Required | Required | Required | Required |

Required views:

- Login
- Dashboard
- Observaciones
- Supervision
- Reportes
- Usuarios
- Asignaciones
- Eliminadas
- Establecimientos
- Perfil
- Shared header/sidebar/footer

### 5. Component Evaluation Matrix

Must include at least:

- Header
- Sidebar
- Cards
- Tables
- Forms
- Modals
- Dropdowns
- Toasts/notifications
- Charts
- Badges/status indicators
- Loading overlay

### 6. Responsive Evidence

Must document results for:

- Desktop
- Tablet
- Mobile

Each result must state pass, conditional, or fail, with findings.

### 7. Accessibility Evidence

Must document checks for:

- Contrast/readability
- Keyboard navigation
- Focus visibility
- Modal focus and close behavior
- Form labels and errors
- Table readability

### 8. Role Safety Evidence

Must state whether visual changes preserve:

- Registrador-only actions
- Supervisor-only actions
- Year selector visibility and context
- Navigation filtering by role
- No accidental exposure of administrative actions

### 9. Closure Criteria

Must include a checklist of conditions required before declaring Tabler the official visual standard for the project.

## Acceptance Rules

- The report must not recommend full consolidation if any primary view is unreviewed.
- The report must not recommend full consolidation if any high-risk visual or role-safety issue remains unresolved.
- Any finding that requires backend, database, permission, import, export, or report logic changes must be split into a separate feature specification.
