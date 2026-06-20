# Quickstart: Tabler Dashboard Review

This quickstart describes how to execute the evaluation without mutating REM data.

## Prerequisites

- Local application running in a non-production environment.
- One supervisor account available.
- One registrador account available.
- Test data sufficient to display dashboard counts, observations, reports, assignments, and deleted observations where possible.
- Browser developer tools available for responsive viewport checks.

## 1. Confirm Scope

Read:

- `specs/001-tabler-dashboard-review/spec.md`
- `specs/001-tabler-dashboard-review/plan.md`
- `specs/001-tabler-dashboard-review/contracts/ui-evaluation.md`
- `README.md`
- `openspec/changes/migrar-tabler-dashboard/tasks.md`

Confirm this evaluation does not change backend, database, permissions, imports, exports, or report logic.

## 2. Review Shared Layout

Inspect:

- `includes/header.php`
- `includes/sidebar.php`
- `includes/footer.php`
- `assets/css/styles.css`
- `assets/css/tabler-override.css`

Record:

- Tabler asset loading strategy.
- Legacy CSS dependency.
- Navigation behavior.
- Year selector behavior.
- User menu behavior.

## 3. Supervisor Walkthrough

Login as supervisor and review:

- Dashboard
- Supervision
- Reportes
- Usuarios
- Asignaciones
- Eliminadas
- Establecimientos
- Perfil

For each view, record:

- Main actions visible and usable.
- Tables readable.
- Forms and filters usable.
- Modals open, focus, close, and submit correctly.
- Charts render where expected.
- No registrador-only assumptions appear incorrectly.

## 4. Registrador Walkthrough

Login as registrador and review:

- Dashboard
- Observaciones
- Reportes, if available to the role
- Perfil

For each view, record:

- Main actions visible and usable.
- Supervisor-only links/actions are not exposed.
- Assigned-establishment context remains understandable.
- Import and observation-entry UI remains clear.

## 5. Responsive Review

Use browser viewport tools and review at minimum:

- Desktop: 1366px or wider
- Tablet: approximately 768px
- Mobile: approximately 390px

Record pass/conditional/fail for:

- Sidebar/header navigation
- Dashboard cards
- Tables
- Forms
- Modals
- Charts
- Action buttons

## 6. Accessibility Spot Check

For representative screens, check:

- Text contrast and readability.
- Visible focus for keyboard navigation.
- Modal focus and close behavior.
- Form labels and error visibility.
- Dropdown usability.
- Table readability without relying only on color.

## 7. Asset Strategy Review

Compare:

- Current CDN usage.
- Controlled local compiled CSS/JS assets.
- Optional package/build pipeline.

Record recommendation using the contract table.

## 8. Produce Evaluation Report

Create a report following `contracts/ui-evaluation.md`.

The final recommendation must be one of:

- `consolidate`
- `consolidate_with_conditions`
- `postpone`
- `partial_revert`

## 9. Completion Gate

Before moving to implementation tasks, confirm:

- 100% of required views have been reviewed.
- Both roles have been considered.
- Desktop, tablet, and mobile have been checked.
- Any high-risk issue has a mitigation or blocks consolidation.
- Any functional issue is split into a separate feature spec.
