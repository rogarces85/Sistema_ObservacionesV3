<!--
Sync Impact Report
Version change: 1.1.0 -> 2.0.0
Modified principles:
- Expanded: VI. Coherent and Traceable Visual Theming
- Added: VII. Tabler-Only UI Ecosystem and Modular Views
- Added: VIII. Secure API Consumption and Domain Fidelity
Added sections:
- UI Architecture Contract
- Security and Domain UI Contract
Removed sections:
- None
Templates requiring updates:
- ✅ reviewed: .specify/templates/plan-template.md
- ✅ reviewed: .specify/templates/spec-template.md
- ✅ reviewed: .specify/templates/tasks-template.md
- ✅ reviewed: .specify/templates/checklist-template.md
- ✅ reviewed: .specify/templates/constitution-template.md
- ✅ reviewed: .specify/extensions/git/commands/*.md
Runtime guidance updates:
- ✅ updated: README.md mirrors the v2 UI, theming, API, accessibility, and domain contracts.
Follow-up TODOs:
- Audit existing inline styles and migrate them to Tabler classes or `assets/css/tabler-override.css`.
- Start the report module restructuring from `views/reportes.php` and `assets/js/charts.js` under this contract.
-->
# Sistema de Observaciones REM Constitution v2

## Core Principles

### I. README and Specs Are the Source of Truth

Every feature, fix, migration, or operational change MUST preserve traceability to
the discovered system truth in `README.md` and to the relevant Spec Kit/OpenSpec
artifact under `specs/`, `openspec/`, or `.specify/`. If implementation behavior
differs from documentation, the documentation and the governing spec MUST be
updated in the same change. Hidden business rules MUST be documented as explicit
requirements or assumptions before they are expanded.

Rationale: this is an existing REM system with legacy behavior, documented
assumptions, and operational risk. Future work must not reintroduce undocumented
logic or drift between code, data model, and process documentation.

### II. Role-Based Access and REM Data Integrity

All user-facing and API behavior MUST enforce the `registrador` and `supervisor`
roles at the backend boundary, not only in views. Registradores MUST only create,
edit, import, and report data that belongs to their assigned establishments and
periods. Supervisory actions MUST preserve auditability through state history,
paper-trail records, or explicit permanent-delete intent.

REM catalog values, observation states, assignment rules, year context, and month
handling MUST remain consistent across manual entry, import, reports, and
supervision. Any change to assignment priority, soft-delete behavior, or report
filters MUST include data impact analysis.

Rationale: the system manages institutional reporting data where incorrect
permissions or inconsistent business rules can alter official operational
evidence.

### III. Secure Configuration and Session Safety

New work MUST NOT add credentials, host-specific secrets, or production passwords
to tracked source files. Configuration changes MUST prefer environment-specific
settings outside version control and MUST document required variables or local
setup steps. Session, CSRF, password, and authorization changes MUST be reviewed
against production security expectations, including HTTPS and secure cookies.

Any feature touching authentication, user management, password reset, exports,
uploads, or administrative APIs MUST document threat considerations and expected
failure behavior. Default or demo credentials MUST be limited to development and
MUST NOT be presented as production-safe.

Rationale: the discovered system currently contains hardcoded environment and
credential assumptions. The constitution prevents those risks from becoming
accepted practice.

### IV. Testable, Reversible Data Changes

Any change that modifies database schema, migrations, imports, deletes,
assignments, reports, or observation state transitions MUST include a verification
plan that is safe for non-production data. Tests or manual verification steps MUST
state whether they mutate the database. Destructive operations MUST have a
rollback, backup, soft-delete path, or explicit irreversible-action rationale.

Schema changes MUST include migration ordering, expected preconditions, and
validation queries or acceptance checks. Feature delivery MUST not rely solely on
PHP syntax validation when behavior or data integrity is affected.

Rationale: existing test scripts mutate configured data, migrations are spread
across directories, and deletion behavior is mixed. Safe verification is required
for operational confidence.

### V. Simple Monolith, Observable Operations

The PHP monolith architecture MUST remain simple unless a spec justifies added
abstraction. Prefer small, localized changes that align with existing `api/`,
`models/`, `views/`, and `includes/` structure. New frameworks, services, queues,
or architectural layers require an explicit tradeoff in the implementation plan.

Operationally important actions MUST produce useful errors, logs, or audit
records. Report generation, imports, exports, user changes, supervision actions,
and background workers MUST expose enough status to diagnose failures without
inspecting raw database state first.

Rationale: the system is a pragmatic PHP application. Maintainability improves
through disciplined simplicity plus targeted observability, not unnecessary
rewrites.

### VI. Coherent and Traceable Visual Theming

All new or modified visual UI work MUST consume the project theme tokens from
`assets/css/tokens.css` instead of hardcoded presentation colors, shadows, or
visual variables. Semantic Tabler customizations MUST live in
`assets/css/tabler-override.css`. `assets/css/styles.css` is deprecated and MUST
NOT be extended under any circumstance; existing dependencies on it are legacy
debt to be removed during module restructuring.

Authenticated views, login, shared includes, tables, forms, cards, dropdowns,
modals, status indicators, reports, and charts MUST be valid in both explicit
themes: `light` and `dark`. Theme switching MUST use the current contract:
`data-bs-theme` on the HTML element, persistence in cookie `rem.theme`,
`localStorage` fallback, and the `rem:theme-changed` JavaScript event for
dependent UI such as Chart.js.

Chart.js usage MUST read colors from theme tokens, update or recreate chart
instances when the theme changes, and avoid fixed light-only values for tooltip,
legend, grid, point, and export controls.

Rationale: the system is used for operational REM review where contrast,
readability, and navigation consistency affect data interpretation. A formal
theme contract prevents regressions such as dark/light switching during
navigation or invisible charts after visual changes.

### VII. Tabler-Only UI Ecosystem and Modular Views

The UI ecosystem MUST use strictly Tabler 1.4 assets: `@tabler/core` and
`@tabler/icons-webfont`. New standalone CSS frameworks or competing UI systems,
including Tailwind CSS or standalone Bootstrap assets, MUST NOT be introduced or
mixed into the application. Tabler's Bootstrap-compatible component behavior may
be used only through the Tabler 1.4 dependency already loaded by the shell.

Each `views/*.php` module MUST remain structurally independent and reuse the
shared shell from `includes/header.php`, `includes/sidebar.php`,
`includes/footer.php`, and `includes/breadcrumbs.php`. A module MUST NOT couple
module-specific styles, scripts, or selectors to another module. Shared behavior
belongs in `assets/js/app.js`, `assets/js/theme.js`, `assets/js/charts.js`, or a
documented shared asset; module-specific behavior must be scoped to that module.

Inline visual styles (`style="..."`) are prohibited for new or modified markup.
All visual customization MUST be expressed through Tabler classes, Tabler utility
classes, project semantic classes in `assets/css/tabler-override.css`, or tokens
in `assets/css/tokens.css`. Existing inline styles are constitution violations to
be removed as the affected module is restructured.

Rationale: the system is entering a module restructuring phase. A single UI
ecosystem and strict style boundaries prevent CSS conflicts, visual drift, and
cross-module regressions, especially in the report and chart screens.

### VIII. Secure API Consumption and Domain Fidelity

The UI MUST consume `api/*.php` endpoints through `fetch` or existing shared
helpers. Every mutable request (`POST`, `PUT`, `PATCH`, `DELETE`, or equivalent
action-changing request) MUST include CSRF protection managed by
`includes/csrf.php`, either as the `X-CSRF-Token` header or an accepted POST
field. UI work MUST NOT bypass backend role checks or rely on hidden controls as
security enforcement.

The interface MUST reflect the domain catalogs, roles, states, months, and REM
constants defined in `config/constants.php`. Frontend code MUST NOT invent
states, role labels, report classifications, observation types, or status colors
that are not backed by the configured domain vocabulary, such as `pendiente`,
`aprobado`, `error`, `S/OBSERVACION`, and `ERROR`.

Rationale: reports and charts can affect operational interpretation. Secure API
use and domain fidelity ensure visual restructuring does not silently change REM
meaning, permissions, or official workflow semantics.

## Technical Constraints

- The primary runtime is PHP on Apache/XAMPP with MySQL or MariaDB via PDO.
- Existing dependencies are managed with Composer and currently include
  PhpSpreadsheet and TCPDF.
- The source layout is the existing monolith: `api/`, `models/`, `views/`,
  `includes/`, `config/`, and `assets/`.
- Server-side authorization is mandatory for all APIs that expose or mutate REM
  data.
- Mutating requests that originate from authenticated browser sessions MUST use
  CSRF protection unless a spec documents a safer equivalent.
- Uploads, exports, generated files, logs, and environment-specific secrets MUST
  not be committed to source control.
- Database changes MUST be represented by reviewed SQL migrations or an approved
  migration mechanism, not ad hoc manual edits.
- User-visible REM catalog changes MUST document their effect on existing data,
  imports, reports, and filters.

## Theming System

- `assets/css/tokens.css` is the source of truth for visual tokens. It MUST define
  light defaults in `:root` and dark overrides under `[data-bs-theme="dark"]`.
- `assets/css/tabler-override.css` MAY style semantic REM components and Tabler
  overrides, but SHOULD consume tokens for colors, surfaces, borders, shadows,
  status states, chart-adjacent UI, and focus states.
- `assets/css/styles.css` is legacy/deprecated. It MUST NOT be extended for new
  theme work unless a spec records a compatibility need.
- Theme persistence uses an explicit `rem.theme` browser cookie with `path=/`,
  one-year lifetime, and `SameSite=Lax`; `localStorage` MAY be kept as a client
  fallback. The default theme is `light` unless the user explicitly selects
  `dark`.
- Supported themes are only `light` and `dark`. `prefers-color-scheme` MUST NOT
  silently change the default without a future spec adding a `system` mode.
- Charts MUST use chart tokens for text, grid, tooltip, legend, point, and export
  controls and MUST refresh on the `rem:theme-changed` client event.
- New components MUST document whether they are covered by both themes when a
  feature spec or review touches substantial UI.

## UI Architecture Contract

- The only allowed UI framework layer is Tabler 1.4 via `@tabler/core` and
  `@tabler/icons-webfont`.
- Do not introduce Tailwind, standalone Bootstrap CSS/JS, Material UI, Bulma, or
  other competing UI frameworks.
- All colors, shadows, gradients, chart colors, surface colors, and visual custom
  properties MUST be declared in `assets/css/tokens.css`.
- Semantic component styling and Tabler customizations MUST be placed in
  `assets/css/tabler-override.css`.
- `assets/css/styles.css` is deprecated and MUST NOT receive new selectors,
  tokens, components, or module-specific styles.
- New and modified markup MUST NOT use inline `style` attributes. If a value
  needs to vary dynamically, use a class, data attribute, CSS custom property
  defined in the theme contract, or a documented component API.
- Report, dashboard, and chart modules MUST use shared chart helpers rather than
  duplicating Chart.js configuration in view files.
- Components MUST meet WCAG 2.1 AA contrast expectations in both light and dark
  themes. Skip links and native Tabler/ARIA structures MUST be preserved.

## Security and Domain UI Contract

- Browser UI MUST call `api/*.php` endpoints through shared fetch patterns or
  local `fetch` code that preserves CSRF and error handling.
- Mutable requests MUST include `X-CSRF-Token` or an accepted POST CSRF field
  from `includes/csrf.php`.
- View-level role hiding is only usability; backend/API authorization remains
  mandatory.
- UI labels, filters, badges, report categories, chart datasets, and status
  colors MUST map to `config/constants.php` or documented database/catalog values.
- Frontend code MUST NOT create unofficial REM states, roles, observation types,
  report states, or status meanings.

## Development Workflow

- Start from the current `README.md` and the relevant module spec before planning
  implementation.
- Every feature spec MUST include prioritized user scenarios, acceptance
  scenarios, affected roles, data entities, assumptions, and security/data
  integrity notes.
- Every implementation plan MUST pass the Constitution Check before research and
  be rechecked after design.
- Tasks MUST be independently verifiable by user story and MUST include concrete
  file paths.
- For database-mutating work, tasks MUST include migration, backup/rollback, and
  verification steps.
- For API or UI work, tasks MUST include backend authorization checks and user
  feedback/error handling.
- Before completion, run PHP syntax validation for touched PHP files or the whole
  project and execute any safe tests or documented manual checks.
- If a verification step is unsafe against the configured database, document it
  instead of running it.

## Governance

This constitution supersedes conflicting local practices for new Spec Kit work.
If legacy code violates a principle, new changes MUST not expand the violation
without explicitly recording the reason, risk, and mitigation in the plan.

Amendment procedure:

- A constitution amendment MUST update `.specify/memory/constitution.md` and
  include a Sync Impact Report.
- The amendment MUST identify affected principles, templates, runtime guidance,
  and follow-up work.
- Any affected templates under `.specify/templates/` MUST be updated in the same
  change or listed as pending with justification.
- `README.md` MUST remain aligned with constitution changes that affect system
  truth, workflow, security, or deployment expectations.

Versioning policy:

- MAJOR version changes redefine or remove existing principles or governance in a
  backward-incompatible way.
- MINOR version changes add principles, sections, or materially expand governance.
- PATCH version changes clarify wording, correct errors, or make non-semantic
  refinements.

Compliance review expectations:

- Plans MUST include a Constitution Check with explicit pass/fail notes.
- Specs MUST record assumptions and data/security implications for affected REM
  workflows.
- Tasks MUST include validation work appropriate to the risk of the change.
- Reviews MUST reject changes that add secrets, bypass backend authorization,
  mutate data without a verification plan, or leave documentation inconsistent
  with implemented behavior.

**Version**: 2.0.0 | **Ratified**: 2026-06-20 | **Last Amended**: 2026-06-20
