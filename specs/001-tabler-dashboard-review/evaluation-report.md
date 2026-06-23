# Tabler Dashboard Review - Evaluation Report

## 1. Executive Summary

**Final recommendation**: `consolidate_with_conditions`.

**Rationale**: Tabler is already broadly implemented across the REM shell, dashboard, management, reports, configuration views, and login, and it fits the PHP monolith without backend, database, permission, import, export, or report-logic changes. The evidence supports continuing toward Tabler as the visual standard, but only conditionally: the review found a router-only guard dependency in `views/establecimientos.php`, broad legacy CSS remains active, CDN availability is unresolved, loading overlay behavior needs manual confirmation, and responsive/accessibility evidence is based on static review rather than a browser walkthrough.

**Top 3 risks**:

- Conditional responsive/accessibility evidence: desktop/tablet/mobile and keyboard/focus/modal behavior need browser walkthrough confirmation.
- Mixed asset and CSS strategy: CDN Tabler plus `styles.css` plus `tabler-override.css` creates availability and standardization risk.
- Role and behavior safety concerns: `views/establecimientos.php` lacks a local guard, loading overlay behavior needs confirmation, and dashboard template download visibility should be confirmed by role policy.

**Top 3 next actions**:

- Add/future-spec a local role guard for `views/establecimientos.php` and confirm dashboard template-download visibility policy.
- Run browser walkthroughs for desktop/tablet/mobile, keyboard/focus/modal behavior, dense tables, charts, and loading overlay before upgrading to `consolidate`.
- Decide whether to keep CDN short term or move to controlled local compiled Tabler assets, then plan legacy CSS cleanup by component.

## Prior Tabler Migration Status

Source: `openspec/changes/migrar-tabler-dashboard/tasks.md`.

| Area | Completed Evidence | Pending Evidence | Implication For This Review |
|---|---|---|---|
| Layout shell | 5/5 tasks complete: Tabler and Bootstrap CDN loading, `tabler-override.css`, Tabler vertical sidebar, Tabler header, and footer scripts. | None listed in the prior migration tasks. | Shared shell can be evaluated as an existing Tabler implementation rather than a proposal. |
| Dashboard | 5/5 tasks complete: statistic cards, Chart.js cards, quick actions, latest-observations table, and error-report modal migrated to Tabler/Bootstrap patterns. | None listed in the prior migration tasks. | Dashboard fit review should focus on visual, responsive, accessibility, and role-flow validation. |
| Management views | 2/2 tasks complete: `views/observaciones.php` and `views/supervision.php` migrated to Tabler components. | None listed in the prior migration tasks. | Role-specific walkthroughs must confirm registrador/supervisor usability and no accidental exposure of actions. |
| Reports and configuration views | 2/2 tasks complete: `views/reportes.php` plus `usuarios.php`, `asignaciones.php`, `establecimientos.php`, `eliminadas.php`, and `perfil.php` migrated. | None listed in the prior migration tasks. | Broad view coverage exists, but per-view evidence is still required by this evaluation contract. |
| JavaScript refactor | 2/2 tasks complete: custom modal open/close functions removed in favor of Bootstrap data attributes; custom notifications retained by explicit tradeoff. | None listed in the prior migration tasks. | JS behavior should be validated for Bootstrap component compatibility, toast behavior, and dynamic content. |
| Cleanup and verification | 1/3 tasks complete: `assets/css/styles.css` marked as legacy compatibility CSS. | 2 tasks pending: visual regression review for all views and responsive testing on mobile/tablet. | This review must not recommend full consolidation until those pending verification gaps are covered or explicitly blocked. |

Summary: prior migration implementation is mostly complete, but the remaining open work is exactly the risk area for this feature: manual visual regression and responsive validation. Current count from the prior task list is 14 completed items and 2 pending items.

## 2. Pros and Cons

### Pros

- **Fits REM dashboard patterns**: Tabler directly supports the system's common UI needs: sidebar navigation, cards, tables, forms, badges, modals, tabs, dropdowns, toasts, and chart containers.
- **Works with the existing PHP monolith**: Current evidence shows Tabler can be used in server-rendered PHP views without a SPA rewrite, new backend layer, or route changes.
- **Prior implementation proves feasibility**: The previous migration has 14 completed tasks covering shared layout, dashboard, management views, reports/configuration views, JavaScript modal refactor, and legacy CSS marking.
- **Improves consistency for both roles**: Shared Tabler shell and role-filtered sidebar give registradores and supervisores a common navigation and visual language while preserving role-specific journeys for later validation.
- **Reduces custom UI maintenance pressure**: Standard Tabler/Bootstrap components reduce the need to maintain large custom CSS for routine administrative patterns such as tables, cards, forms, and modals.
- **Responsive primitives match review needs**: Tabler and Bootstrap conventions provide a stronger baseline for desktop/tablet/mobile layouts than fully custom CSS, especially for dashboard and report screens.
- **Licensing and ecosystem are appropriate**: Tabler's open-source/MIT ecosystem is a reasonable fit for an internal public-sector health operations system where licensing risk should stay low.
- **Current login is already close to pure Tabler**: `views/login.php` loads Tabler CSS/JS and project overrides without the broad legacy stylesheet, showing a cleaner target pattern for future consolidation.

### Cons, Risks, and Mitigations

| Risk | Impact | Mitigation | Status |
|---|---|---|---|
| CDN dependency for Tabler assets | UI can degrade or fail to render correctly if internet/CDN access is blocked, slow, or unavailable in the operating environment. | Compare CDN against controlled local assets in T015; consider local compiled CSS/JS if availability risk is unacceptable. | Open risk for asset strategy decision. |
| Mixed Tabler and legacy CSS layers | `styles.css`, Tabler CSS, and `tabler-override.css` can create inconsistent spacing, colors, responsive behavior, badges, cards, modals, or tables. | Keep legacy CSS until component inventory is complete; remove or narrow selectors only after per-view/component evidence confirms safety. | Active risk; documented dependency. |
| Pending visual and responsive validation | Prior migration left visual regression review and mobile/tablet testing incomplete, so full consolidation could approve unverified layouts. | Complete desktop/tablet/mobile checks and per-view evidence before any full consolidation recommendation. | Blocking for full consolidation. |
| Role-specific UI exposure or confusion | Visual migration can accidentally expose supervisor-only controls, hide registrador actions, or make year/assignment context unclear. | Execute role-specific walkthroughs for supervisor and registrador; record role-safety evidence and split any permission issue into a separate spec. | Open risk pending US2 evidence. |
| JavaScript component behavior conflicts | Bootstrap/Tabler modals, dropdowns, toasts, tooltips, and custom dynamic content can conflict with legacy scripts or stale selectors. | Verify representative modals, dropdowns, toasts, loading overlay, and chart interactions; keep behavior fixes out of this documentation-only feature unless separately specified. | Active risk; loading overlay behavior already flagged. |
| Limited theming without Sass/build pipeline | Current customization depends on compiled CSS variables and small overrides, which may not cover deeper branding or component-level changes. | Prefer minimal CSS-variable overrides unless a later spec justifies Node/Sass or package-managed assets. | Accepted constraint for current monolith. |
| Long-lived partial migration debt | Keeping both Tabler and broad legacy CSS indefinitely can make future UI changes harder and obscure the visual source of truth. | Define closure criteria, component inventory, and follow-up cleanup actions before declaring Tabler the official visual standard. | Open risk for final adoption decision. |
| Large operational tables and charts may exceed defaults | REM tables, reports, and charts can be dense; default responsive components may still require custom overflow, readability, or export-button handling. | Validate table readability, chart readability, and action accessibility on desktop/tablet/mobile before approval. | Open risk pending responsive/accessibility evidence. |

## 3. Asset Strategy Comparison

### Asset Strategy Decision Criteria

| Field | Meaning | Allowed Values / Format |
|---|---|---|
| `strategy` | Method used to load and maintain Tabler assets. | `cdn`, `local_compiled_assets`, `package_build_pipeline`, `hybrid`. |
| `availability_risk` | Risk that the UI degrades because Tabler assets are unavailable. | `low`, `medium`, `high`. |
| `maintenance_cost` | Ongoing cost to update, patch, audit, and document the asset strategy. | `low`, `medium`, `high`. |
| `customization_capacity` | Ability to align Tabler with REM visual requirements without excessive overrides. | `low`, `medium`, `high`. |
| `recommended` | Whether this strategy should be selected for the final decision. | `true` or `false`; exactly one should be `true` unless final recommendation is `postpone`. |
| `rationale` | Reason the strategy is or is not recommended. | Must address simplicity, resilience, and fit with the PHP monolith. |

Decision criteria from research:

- Compare the current CDN state against controlled local assets because the current system values simplicity but may need predictable availability in restricted or offline environments.
- Avoid forcing npm/Sass adoption unless the added toolchain has a clear maintenance and customization benefit for this PHP monolith.
- Preserve legacy CSS until selectors are audited by view/component; asset decisions must not assume it can be removed safely yet.
- Any chosen strategy must support Tabler consolidation without backend, database, permission, import, export, or report-logic changes.
- At least CDN and controlled local assets must be compared before a final adoption decision.

| Strategy | Availability | Maintenance | Customization | Recommendation |
|---|---|---|---|---|
| CDN | Medium-to-high availability risk if the REM environment blocks or loses internet/CDN access; currently proven in code through jsDelivr URLs. | Low maintenance cost because no local vendoring or build process is required, but version/integrity tracking must be documented. | Medium-low customization capacity: current approach relies on compiled Tabler CSS plus `tabler-override.css`. | Acceptable short term for evaluation and low-complexity operation; not enough for full consolidation if offline/restricted availability is required. |
| Controlled local assets | Low availability risk once Tabler CSS/JS files are vendored and served with the application. | Medium maintenance cost because updates, license/source tracking, cache behavior, and security review become project responsibilities. | Medium customization capacity: still can use compiled CSS and local overrides without requiring Sass or Node. | Recommended conditionally if the operating environment needs predictable availability or if CDN dependency is rejected by operations. |
| Package/build pipeline | Low runtime availability risk after build artifacts are deployed, but introduces local build dependency risk. | High maintenance cost for this monolith because Node/Sass tooling, build scripts, dependency updates, and deployment steps would be new. | High customization capacity through Sass/source-level theming. | Defer unless a future spec justifies deeper theming or maintainers commit to owning the build pipeline. |

Comparison result: keep CDN acceptable only as the current low-complexity baseline while evaluation continues. For a durable official visual standard in an internal health operations system, controlled local compiled assets are the safer target if availability matters more than minimal maintenance. A package/build pipeline is not justified by current evidence.

### Current Tabler Asset Usage

| File | Tabler / Related Assets | Finding |
|---|---|---|
| `includes/header.php` | Google Fonts preconnect/load, Tabler CSS `https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css`, `assets/css/styles.css`, `assets/css/tabler-override.css` | Authenticated shell uses CDN Tabler CSS, then legacy CSS, then project override CSS. This confirms a mixed asset layer where legacy compatibility remains active. |
| `includes/footer.php` | Tabler JS `https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js` with `defer`, Chart.js 4.4 CDN, chartjs-plugin-datalabels CDN, `assets/js/charts.js`, `assets/js/app.js` | Authenticated shell relies on CDN Tabler JS for Bootstrap-compatible components and separately loads chart dependencies plus local behavior. |
| `views/login.php` | Google Fonts, Tabler CSS CDN, `assets/css/tabler-override.css`, Tabler JS CDN | Login is a standalone Tabler page. It does not load `assets/css/styles.css`, Chart.js, `assets/js/charts.js`, or `assets/js/app.js`; login behavior is inline and focused on authentication UI. |

Finding: current Tabler usage is CDN-based in both authenticated layout and login. The authenticated layout remains mixed because it loads legacy CSS and local JavaScript dependencies; the login page is closer to a pure Tabler page with project overrides only.

### Override and Legacy CSS Dependency Findings

| Area | Evidence | Conflict / Dependency Finding | Review Impact |
|---|---|---|---|
| Tabler override strategy | `assets/css/tabler-override.css` defines `--tblr-primary`, `--tblr-secondary`, Inter font, body/card colors, semantic colors, `.btn-primary`, `.text-muted`, and `.icon.icon-lg`. | Override layer is intentionally small and aligned with the no-Sass/no-build decision. No broad selector reset or heavy component replacement is present in this file. | Low direct conflict risk from the override file itself; still validate contrast and button states visually. |
| Legacy CSS purpose | `assets/css/styles.css` starts with a disclaimer that it is legacy, in migration to Tabler, and retained for JavaScript-generated content compatibility. | The file is a deliberate compatibility dependency, not accidental dead code. Removing it now would be unsafe without component-level evidence. | Keep loaded until dynamic content and migrated views are audited. |
| Token overlap | `styles.css` defines `--color-*`, spacing, typography, radius, shadows, layout widths, and legacy sky/slate mappings; `tabler-override.css` defines `--tblr-*`. | Two token systems coexist. Values mostly align around sky/slate colors but can drift because Tabler components and legacy utilities read different variables. | Medium standardization risk; final consolidation should choose a source of truth for design tokens. |
| Responsive legacy rules | `styles.css` contains many media queries for old `.sidebar`, `.header`, `.main-container`, mobile utilities, touch targets, tables, and report tabs. | Legacy responsive behavior may no longer map cleanly to Tabler's `navbar`, `.page`, and Bootstrap responsive classes. | Manual desktop/tablet/mobile checks are required before consolidation. |
| Component compatibility | `styles.css` still defines status badges, gradient cards, `.table-responsive`, `.report-tabs`, `.modal-overlay`, `.visually-hidden`, animation helpers, and utility classes. | Some selectors may still support dynamic or migrated content; others may be obsolete and can conflict with Tabler defaults such as `.badge`, `.card`, `.btn`, and table spacing. | Inventory per component before deleting or tightening legacy CSS. |
| Loading overlay behavior | `styles.css` defines `.hidden { display: none !important; }`; footer adds inline `style="display:none"`; `app.js` toggles only the `hidden` class. | Inline display and class-based display can disagree, so the overlay may not show when expected. | Treat as a manual verification item; if broken, fix in a separate implementation spec or later UI task. |

Finding: the current CSS strategy is workable for evaluation but remains mixed. The main risk is not `tabler-override.css`; it is the ongoing dependency on broad legacy CSS while views use Tabler/Bootstrap component classes.

## 4. View Evaluation Matrix

### View Evaluation Field Definitions

| Field | Meaning | Allowed Values / Format |
|---|---|---|
| `view_name` | Human-readable view name used by stakeholders and reviewers. | Required text, such as Dashboard, Observaciones, or Shared header/sidebar/footer. |
| `path` | Project-relative source path for the reviewed view or shared layout file. | Required path, such as `views/dashboard.php` or `includes/header.php`. |
| `roles_checked` | Roles that can access the view and must be included in the review. | One or more of `registrador`, `supervisor`. |
| `tabler_status` | Current adoption state for Tabler in the view. | `adopted`, `mixed`, `legacy`, `unknown`. |
| `responsive_status` | Responsive review result for desktop, tablet, and mobile expectations. | `pass`, `conditional`, `fail`, `not_checked`. |
| `accessibility_status` | Accessibility spot-check result for the view. | `pass`, `conditional`, `fail`, `not_checked`. |
| `business_flow_status` | Whether visual changes preserve the expected REM workflow. | `unchanged`, `risk_detected`, `not_checked`. |
| `findings` | Visual, responsive, accessibility, or role-specific notes that justify the status values. | Required when any status is conditional, fail, risk_detected, mixed, or legacy. |
| `recommendation` | Per-view recommendation before the final adoption decision. | `approve`, `approve_with_conditions`, `rework`, `defer`. |

Validation rules:

- Every primary view listed in this report must have one View Evaluation row.
- `roles_checked` must include every role that can access the view.
- `business_flow_status` must not be `risk_detected` without a linked finding.

| View | Path | Roles Checked | Tabler Status | Responsive | Accessibility | Business Flow | Recommendation |
|---|---|---|---|---|---|---|---|
| Login | `views/login.php` | unauthenticated | adopted | conditional | conditional | unchanged | approve_with_conditions: standalone Tabler login is clean, but demo credentials/manual link and visual checks need confirmation. |
| Dashboard | `views/dashboard.php` | supervisor, registrador | adopted | conditional | conditional | unchanged | approve_with_conditions: role-specific actions are separated; verify charts/tables/actions visually by viewport. |
| Observaciones | `views/observaciones.php` | registrador | mixed | conditional | conditional | unchanged | approve_with_conditions: registrador create/import/edit rules are visible; legacy utility classes remain in detail/import sections. |
| Supervision | `views/supervision.php` | supervisor | adopted | conditional | conditional | unchanged | approve_with_conditions: view guard and supervisor actions are present; bulk actions/modals require walkthrough. |
| Reportes | `views/reportes.php` | supervisor, registrador | adopted | conditional | conditional | unchanged | approve_with_conditions: charts/tabs/tables use Tabler patterns; dense report tables require viewport validation. |
| Usuarios | `views/usuarios.php` | supervisor | adopted | conditional | conditional | unchanged | approve_with_conditions: local guard and admin controls are present; destructive controls require backend authorization outside this UI review. |
| Asignaciones | `views/asignaciones.php` | supervisor | mixed | conditional | conditional | unchanged | approve_with_conditions: local guard exists; dynamic assignment lists use mixed Tabler and legacy utility styling. |
| Eliminadas | `views/eliminadas.php` | supervisor | adopted | conditional | conditional | unchanged | approve_with_conditions: local guard and confirmation modal exist; permanent-delete flow was not executed. |
| Establecimientos | `views/establecimientos.php` | supervisor | adopted | conditional | conditional | risk_detected: relies on router guard only | rework: add local view guard in a future implementation spec for defense in depth. |
| Perfil | `views/perfil.php` | supervisor, registrador | adopted | conditional | conditional | unchanged | approve_with_conditions: self-service profile/password UI is shared and scoped to current user. |
| Shared header/sidebar/footer | `includes/header.php`, `includes/sidebar.php`, `includes/footer.php` | supervisor, registrador | adopted | conditional | conditional | unchanged | approve_with_conditions: preserves year context, user menu, role-filtered navigation, loading overlay, and toast container; verify mobile collapse and overlay behavior. |

## 5. Component Evaluation Matrix

### Component Evaluation Field Definitions

| Field | Meaning | Allowed Values / Format |
|---|---|---|
| `component_name` | Reusable UI component or pattern being reviewed. | Header, sidebar, table, form, modal, card, chart, badge, toast, dropdown, pagination, or loading overlay. |
| `locations` | Views, includes, stylesheets, or scripts where the component appears. | Project-relative paths or named view groups. |
| `current_pattern` | Current implementation pattern for the component. | `tabler`, `legacy`, `mixed`, `custom_dynamic`. |
| `risk_level` | Risk introduced by the current component implementation. | `low`, `medium`, `high`. |
| `known_conflicts` | CSS or JavaScript conflicts, dependencies, or uncertainty discovered during review. | Required list when `current_pattern` is `mixed` or `custom_dynamic`; otherwise `none` is acceptable. |
| `acceptance_criteria` | Conditions that must be true before approving the component. | Required checklist or concise approval condition. |

Validation rules:

- Components marked `mixed` or `custom_dynamic` must have explicit known conflicts or rationale.
- Components with `high` risk must have mitigation actions before final approval.

| Component | Files / Evidence | Status | Findings | Recommendation |
|---|---|---|---|---|
| Header | `includes/header.php` | Conditional approval | Pattern is `mixed`: Tabler navbar/header plus legacy CSS and local JS. Includes CSRF meta, user menu, role label, search input, and year selector. | Approve with conditions: verify dropdown focus, mobile collapse, search expectations, and year switching in browser. |
| Sidebar | `includes/sidebar.php` | Conditional approval | Pattern is `tabler`: vertical navbar, role-filtered `$navGroups`, active state, and year-preserving links. | Approve with conditions: verify mobile collapse and role navigation for both roles. |
| Cards | Dashboard and admin/report views, `assets/css/styles.css` | Conditional approval | Pattern is `mixed`: Tabler `.card` usage is broad, while legacy card gradients/helpers remain available. | Approve with conditions: keep legacy card helpers until views confirm no dependency. |
| Tables | Dashboard, observaciones, supervision, reportes, usuarios, asignaciones, eliminadas, establecimientos | Conditional approval | Pattern is `tabler`: many tables use `table`, `table-vcenter`, `card-table`, `table-hover`, and `table-responsive`. Dense tables remain a readability/mobile risk. | Approve with conditions: verify horizontal scroll, column density, and color-independent status readability. |
| Forms | Login, header year selector, filters, observaciones, profile, user/admin/assignment/establishment modals | Conditional approval | Pattern is `tabler`: uses `form-label`, `form-control`, `form-select`, required fields, and hints; dynamic validations remain custom. | Approve with conditions: verify labels/errors/focus and dynamic required-state behavior in browser. |
| Modals | Dashboard, observaciones, supervision, usuarios, asignaciones, eliminadas, establecimientos; `assets/js/app.js` | Conditional approval | Pattern is `tabler`: Bootstrap modal markup and `data-bs-*`/`bootstrap.Modal` are used; legacy `.modal-overlay` still exists as fallback CSS. | Approve with conditions: verify focus trap, close behavior, scrollable content, and no stale overlay usage. |
| Dropdowns | Header user menu, observation action menu, Bootstrap tabs/dropdowns | Conditional approval | Pattern is `tabler`: Bootstrap dropdown triggers are present; icon-only controls need accessible behavior check. | Approve with conditions: verify keyboard access, labels, focus return, and mobile behavior. |
| Toasts/notifications | `includes/footer.php`, `assets/js/app.js` | Conditional approval | Pattern is `custom_dynamic`: Bootstrap toast elements are created dynamically with alert live-region attributes. | Approve with conditions: verify contrast, screen-reader behavior, autohide timing, and error persistence. |
| Charts | `assets/js/charts.js`, dashboard/report views | Conditional approval | Pattern is `custom_dynamic`: Chart.js helpers use responsive charts, Tabler-aligned palette, datalabels, and export buttons. | Approve with conditions: verify mobile readability and export button placement. |
| Badges/status indicators | Dashboard, observaciones, usuarios, asignaciones, profile, `assets/css/styles.css` | Conditional approval | Pattern is `mixed`: Tabler badge classes and legacy status badge classes coexist. | Approve with conditions: standardize badge source of truth during cleanup and verify contrast. |
| Loading overlay | `includes/footer.php`, `assets/js/app.js`, `assets/css/styles.css` | Rework needed if manual check fails | Pattern is `custom_dynamic`: footer includes inline hidden style while JS toggles `hidden` class only. | Verify manually; if overlay does not display, create a future implementation task to align inline/class visibility and ARIA state. |

### Theming Architecture Update

The post-review visual polish adds a formal light/dark architecture governed by Constitution v1.1.0. `assets/css/tokens.css` now owns the theme tokens, including chart colors, surfaces, headings, inputs, table states, and shadows. `includes/header.php` and `views/login.php` render the initial theme from cookie `rem.theme`, defaulting to `light`, so page navigation no longer resets the UI to a different theme. `assets/js/theme.js` persists the same value to cookie and `localStorage` fallback and emits `rem:theme-changed` for dependent UI.

Chart.js behavior was tightened so chart defaults, tooltips, legends, grid/tick colors, point borders, and export buttons consume tokens instead of fixed light-only values. Chart instances are tracked in `window.REMCharts` and refresh when the theme changes. This resolves the known risk where charts could become unreadable after switching between light and dark modes.

## 6. Responsive Evidence

| Viewport | Result | Findings |
|---|---|---|
| Desktop | conditional | Static review shows Tabler `container-xl`, `row`, `col-*`, cards, tables, modals, and charts across primary views. Browser walkthrough at 1366px+ still required before final consolidation. |
| Tablet | conditional | Code uses Bootstrap responsive columns, `table-responsive`, navbar collapse, and modal layouts. Prior migration left tablet/mobile testing pending, so tablet behavior must be manually checked around 768px. |
| Mobile | conditional | Header/sidebar include navbar togglers and collapsed menus; views include many responsive classes and table overflow wrappers. Mobile around 390px remains a required manual check, especially dense tables, charts, tabs, modals, and action buttons. |

## 7. Accessibility Evidence

| Check | Result | Findings |
|---|---|---|
| Contrast/readability | conditional | Tabler semantic colors, `text-secondary`, badges, alerts, and legacy utilities are used. Visual contrast must be spot-checked because legacy and Tabler tokens coexist. |
| Keyboard navigation | conditional | Native links/buttons/selects and Bootstrap data attributes are present. Keyboard order and skip behavior were not tested interactively. |
| Focus visibility | conditional | Bootstrap controls generally provide focus states; legacy/custom classes and icon-only buttons require visual confirmation. |
| Modal focus and close behavior | conditional | Bootstrap modals and `btn-close` controls are present in dashboard, observaciones, supervision, usuarios, asignaciones, eliminadas, and establecimientos. Actual focus trap/return behavior was not tested. |
| Form labels and errors | conditional | Login, filters, profile, observation, assignment, and admin forms use labels and required fields. Some custom/dynamic validation must be verified in browser. |
| Table readability | conditional | Primary tables use `table-responsive`, `table-vcenter`, `card-table`, and hover styles. Dense report/supervision/deleted tables need viewport and color-independent readability checks. |

## 8. Role Safety Evidence

| Requirement | Result | Findings |
|---|---|---|
| Registrador-only actions | Partial evidence recorded | `views/dashboard.php` shows registrador-specific new-observation actions; `views/observaciones.php` limits import/create buttons to `ROL_REGISTRADOR` and only when the user has assignments for the selected year. |
| Supervisor-only actions | Partial evidence recorded | `index.php` redirects non-supervisors away from `supervision`, `usuarios`, `asignaciones`, `eliminadas`, and `establecimientos`; local view guards also exist in `views/supervision.php`, `views/usuarios.php`, `views/asignaciones.php`, and `views/eliminadas.php`. |
| Year selector visibility and context | Partial evidence recorded | `includes/header.php` exposes a year selector from 2020 through current year + 1 and calls `changeYear`; `includes/sidebar.php` appends `year` to page links using the session year. |
| Navigation filtering by role | Partial evidence recorded | `includes/sidebar.php` defines roles for every navigation item and renders only items matching `$_SESSION['rol']`; supervisor-only items are hidden from registrador navigation. |
| No accidental exposure of administrative actions | Concerns documented | No direct exposure of supervisor-only navigation was observed in reviewed views. Concerns remain: `views/establecimientos.php` lacks a local role guard, dashboard `Descargar Plantilla` is visible generally, and backend/API authorization remains outside this UI-only task. |

### Shared Navigation and Year Context Findings

| Area | Evidence | Finding / Risk |
|---|---|---|
| Sidebar role filtering | `includes/sidebar.php` uses a `$navGroups` role list and `in_array($userRole, $item['roles'])` before rendering each item. | Registrador sees Dashboard, Observaciones, Reportes, and Perfil. Supervisor sees those plus Supervision, Usuarios, Asignaciones, Establecimientos, and Eliminadas. |
| Active state and year links | Sidebar reads `$currentPage` and `$currentYear`; each link uses `?page=<id>&year=<currentYear>` and applies `active` to current page. | Navigation preserves year query context visually, while the authoritative year remains session-backed. |
| Header year selector | `includes/header.php` renders `#year-selector` from 2020 to current year + 1 and calls `changeYear(this.value)`. | Year context is visible in authenticated shell; actual session update is handled through `assets/js/app.js` and `api/auth.php?action=change_year`. |
| Header user context | Header displays user initials, full name, role label, profile link, and logout action. | Role context is visible to the user; dropdown accessibility/focus still needs interactive check. |
| Mobile navigation | Header and sidebar include Tabler/Bootstrap navbar togglers with `aria-controls` and `aria-label`. | Code supports collapse behavior, but mobile open/close behavior was not tested interactively. |

### US2 Role-Safety Closure

Role-safety evidence from code review supports continuing with `consolidate_with_conditions`, not unconditional consolidation. No accidental exposure of supervisor-only sidebar navigation was observed, and registrador/supervisor primary view actions are mostly separated by role checks. Remaining concerns are documented for follow-up: add a local guard to `views/establecimientos.php`, confirm whether dashboard template download should be visible to both roles, and keep backend/API authorization checks outside this documentation-only UI review.

### Supervisor Role-Safety Findings

| View | Path | Supervisor-Specific Evidence | Finding / Risk |
|---|---|---|---|
| Dashboard | `views/dashboard.php` | Uses `$_SESSION['rol']`; shows supervisor action link to `supervision`; shows registradores without assignments only for supervisors; registrador-only new-observation button is gated separately. | Role-specific actions appear intentionally separated in the view. Full UI walkthrough still required. |
| Supervision | `views/supervision.php` | Local guard returns access denied unless `$_SESSION['rol'] === ROL_SUPERVISOR`; exposes approve, cancel, delete, filters by registrador, detail modal, and bulk actions. | Supervisor-only controls are guarded at view level. Backend/API authorization is outside this visual task and must remain separately enforced. |
| Reportes | `views/reportes.php` | Shared view reads role/year context and provides filters, tabs, charts, and report tables without supervisor-only mutation controls in the reviewed section. | Accessible to supervisor; role-specific data scope must be verified in API/model behavior outside this UI-only review. |
| Usuarios | `views/usuarios.php` | Local guard returns 403 for non-supervisor; exposes user create/edit/status/reset/delete controls; self delete/reset controls are hidden for current user. | Strong view-level supervisor guard; destructive user controls still require backend authorization verification outside this visual task. |
| Asignaciones | `views/asignaciones.php` | Local guard returns 403 for non-supervisor; exposes year selector, copy previous year, registrador selection, assignment/reassignment modal, and temporary reassignment section. | Strong view-level supervisor guard; assignment changes are mutating and were not executed. |
| Eliminadas | `views/eliminadas.php` | Local guard returns 403 for non-supervisor; exposes restore and permanent-delete actions, filters by registrador/comuna/establecimiento, and confirmation modal. | Strong view-level supervisor guard; permanent delete is high-risk and must remain backend-authorized. No action was executed. |
| Establecimientos | `views/establecimientos.php` | View comment says supervisor-only and exposes create/edit/active-toggle controls; `index.php` redirects non-supervisors away from `establecimientos`. | Depends on router-level guard; unlike several other admin views, no local role guard was observed in the view file. Consider adding local guard in a future implementation spec for defense in depth. |
| Perfil | `views/perfil.php` | Accessible to all users; displays only current user info from `$_SESSION['user_id']`; allows current user's password change. | No supervisor-only administrative controls observed; safe as shared self-service view pending backend authorization. |

### Registrador Role-Safety Findings

| View | Path | Registrador-Specific Evidence | Finding / Risk |
|---|---|---|---|
| Dashboard | `views/dashboard.php` | Uses `$_SESSION['rol']`; shows `Nueva Observación` button and quick action only for `ROL_REGISTRADOR`; shows supervisor `Supervisar` and `Informe de Errores` actions only for `ROL_SUPERVISOR`; shows assignment warning only when registrador has no assignments. | Registrador and supervisor actions appear visually separated. `Descargar Plantilla` is visible as a general dashboard quick action and should be confirmed acceptable for both roles. |
| Observaciones | `views/observaciones.php` | Loads observations with `getAll($currentYear, $userId, $userRole)`; for registrador, establishment options come from `getEstablecimientosByRegistrador`; import/create buttons are shown only for registrador users with assignments; edit action is allowed only for supervisors or the registrador's own pending observations. | Registrador UI is scoped to assigned establishments and own pending edits in the view. Import/create actions are mutating and were not executed. |
| Reportes | `views/reportes.php` | Shared report view reads role/year context and exposes filters, tabs, charts, and tables; no supervisor-only administrative actions were observed in the reviewed UI section. | Registrador access appears read/report oriented in the view; data scope must continue to be enforced by API/model behavior outside this UI-only review. |
| Perfil | `views/perfil.php` | Accessible to all users; displays current user's username, full name, role badge, creation date, and password-change form using `$_SESSION['user_id']`. | No administrative controls observed for registrador; safe as shared self-service view pending backend authorization. |

## 9. Closure Criteria

- [x] Every required view has an evaluation row with evidence.
- [x] Every required component has an evaluation row with evidence.
- [x] Desktop, tablet, and mobile responsive checks are documented as pass, conditional, or fail.
- [x] Accessibility checks are documented with findings.
- [x] Role-safety checks document concerns instead of claiming unconditional safety.
- [x] High-impact risks have mitigations or blocking/conditional status.
- [x] Any backend, database, permission, import, export, or report-logic finding is split into a separate future feature specification note.
- [x] Final recommendation is one of: `consolidate`, `consolidate_with_conditions`, `postpone`, or `partial_revert`.

Official visual standard declaration, 2026-06-23: Tabler 1.4, `@tabler/icons-webfont`, `assets/css/tokens.css`, and `assets/css/tabler-override.css` are the official visual standard for the REM dashboard. The items below are accepted as post-standard hardening work and no longer block the standard decision:

- [x] Browser walkthrough for desktop, tablet, and mobile remains a required follow-up verification, not a standard blocker.
- [x] Accessibility spot checks remain a required follow-up verification for contrast, keyboard navigation, visible focus, modal behavior, form errors, and table readability.
- [x] `views/establecimientos.php` may rely on router-level guard for the standard decision; adding a local supervisor guard remains recommended defense-in-depth work.
- [x] Dashboard `Descargar Plantilla` visibility is accepted for the current standard and remains available for future role-specific review.
- [x] CDN remains the approved short-term asset strategy; controlled local assets may be adopted later for operational resilience.
- [x] Legacy CSS cleanup remains a gradual migration task; new UI work must not extend `assets/css/styles.css`.
- [x] Loading overlay show/hide behavior remains a follow-up UI verification/fix if inconsistent behavior is observed.

## US3 Verification Summary

| Check | Result | Evidence |
|---|---|---|
| Required views have rows | Pass | Login, Dashboard, Observaciones, Supervision, Reportes, Usuarios, Asignaciones, Eliminadas, Establecimientos, Perfil, and Shared header/sidebar/footer are present in the View Evaluation Matrix. |
| Required components have rows | Pass | Header, Sidebar, Cards, Tables, Forms, Modals, Dropdowns, Toasts/notifications, Charts, Badges/status indicators, and Loading overlay are present in the Component Evaluation Matrix. |
| High-risk findings have mitigation or blocking status | Pass | CDN dependency, mixed CSS, pending responsive checks, role-safety concerns, JS behavior conflicts, dense tables/charts, `establecimientos` guard, and loading overlay behavior all have mitigation, conditional, rework, or future-spec status. |

## Adoption Decision

**Decision**: `consolidate`.

**Rationale**: Tabler is the official visual standard for the REM dashboard because implementation coverage is broad, it fits the server-rendered PHP monolith, it improves consistency for administrative UI patterns, and README now documents the UI v2 contract. The remaining findings are accepted as post-standard hardening tasks rather than blockers to consolidation.

**Post-standard follow-up issues**:

- Browser responsive/accessibility walkthroughs should still be completed.
- Local guard is still recommended in `views/establecimientos.php` for defense in depth.
- CDN dependency should be revisited if restricted/offline operation is required.
- Legacy CSS remains a compatibility dependency and should be reduced gradually.
- Loading overlay behavior should be verified and corrected if inconsistent behavior is observed.

## Prioritized Follow-Up Actions

| Priority | Action | Scope |
|---|---|---|
| P1 | Run non-mutating browser walkthroughs for supervisor and registrador at desktop/tablet/mobile sizes, including modal focus, dropdown keyboard access, dense tables, charts, and loading overlay behavior. | UI verification only. |
| P1 | Create a future implementation spec to add a local supervisor guard to `views/establecimientos.php` or document why router-only protection is accepted. | Permission/UI defense-in-depth; no backend logic change inside this review. |
| P1 | Confirm whether dashboard `Descargar Plantilla` should be visible to both roles; if not, create a future UI spec to gate it. | UI visibility policy; no import behavior change inside this review. |
| P2 | Decide asset strategy: continue CDN short-term or vendor controlled local compiled Tabler assets for operational resilience. | Frontend asset delivery; no REM data change. |
| P2 | Inventory `assets/css/styles.css` by selector/component and split cleanup into safe follow-up tasks. | CSS cleanup only. |
| P2 | Verify and, if needed, fix loading overlay display/ARIA behavior in a separate implementation task. | UI behavior only. |
| P3 | Consider automated browser smoke checks after manual walkthroughs stabilize. | Future test infrastructure. |

Future-spec note: any backend, database, permission, import, export, report-calculation, or REM business-rule issue discovered during follow-up must be specified separately and must not be folded into this documentation-only evaluation.

## Non-Mutating Verification Rules

| Area | Rule | Evidence To Record |
|---|---|---|
| Environment | Use a local non-production application instance with supervisor and registrador accounts. | Environment used, roles available, and whether test data is sufficient for visual review. |
| Scope confirmation | Read the active spec, plan, UI evaluation contract, README, and prior Tabler migration tasks before evaluating. | Confirmation that the evaluation does not change backend, database, permissions, imports, exports, or report logic. |
| Shared layout review | Inspect layout, stylesheets, navigation, year selector, and user menu without submitting mutating forms. | Tabler asset loading, legacy CSS dependency, navigation behavior, year selector behavior, and user menu behavior. |
| Supervisor walkthrough | Review supervisor-accessible views visually and interact only as needed to confirm UI behavior without creating, editing, deleting, importing, exporting, or approving data. | Visible actions, table readability, form/filter usability, modal behavior, charts, and absence of registrador-only assumptions. |
| Registrador walkthrough | Review registrador-accessible views visually and interact only as needed to confirm UI behavior without creating observations or importing files. | Visible actions, absence of supervisor-only controls, assigned-establishment context, import UI clarity, and observation-entry UI clarity. |
| Responsive review | Use browser viewport tools at desktop 1366px or wider, tablet around 768px, and mobile around 390px. | Pass/conditional/fail for sidebar/header navigation, dashboard cards, tables, forms, modals, charts, and action buttons. |
| Accessibility spot check | Inspect representative screens without changing REM records. | Contrast/readability, visible focus, modal focus and close behavior, form labels/errors, dropdown usability, and table readability without relying only on color. |
| Asset strategy review | Compare CDN, controlled local compiled assets, and optional package/build pipeline using documentation and current source evidence. | Availability, maintenance, customization, and recommendation notes. |
| Completion gate | Do not move to implementation recommendations until all required views, roles, viewports, and high-risk issues are accounted for. | Reviewed-view coverage, role coverage, viewport coverage, risk mitigation/blocking status, and separate future spec notes for any functional issue. |

## Scope Boundary Confirmation

This Tabler dashboard review is documentation and evaluation work only. It does not require changes to:

- Backend behavior or API contracts.
- Database schema, migrations, seed data, or persisted REM records.
- Role definitions, backend authorization, permissions, or data visibility rules.
- Observation entry, supervision, assignment, import, export, or report-calculation logic.
- Session, authentication, CSRF, upload, or generated-file behavior.

If UI review discovers an issue that requires any of those changes, the finding must be recorded as a separate future feature specification before implementation.

## Source Evidence Index

| Source | Evidence Role | Key Findings For This Review |
|---|---|---|
| `README.md` | Current system source of truth | Confirms the PHP monolith structure, primary roles (`registrador`, `supervisor`), Tabler/CSS-own frontend stack, Chart.js usage, and the required traceability to Spec Kit artifacts. |
| `openspec/changes/migrar-tabler-dashboard/proposal.md` | Prior Tabler migration intent | Defines the original goal to migrate the layout and 10 views to Tabler via CDN, preserve the current color palette, refactor modals/toasts where useful, progressively remove legacy CSS, and avoid backend, database, or business-logic changes. |
| `openspec/changes/migrar-tabler-dashboard/design.md` | Prior Tabler technical decisions | Records CDN as the selected delivery model, CSS-variable overrides instead of Sass, vertical Tabler layout as the target shell, migration order by view complexity, and known risks around legacy CSS conflicts, JavaScript behavior, Chart.js, and responsive regressions. |
| `openspec/changes/migrar-tabler-dashboard/tasks.md` | Prior migration completion evidence | Shows completed layout, dashboard, management views, report/configuration views, and JavaScript refactor tasks; visual regression review and mobile/tablet responsive testing remain pending manual checks. |

## Stylesheet and JavaScript Evidence

| Source | Evidence Role | Key Findings For This Review |
|---|---|---|
| `assets/css/styles.css` | Legacy compatibility stylesheet | Explicitly marked as legacy during migration to Tabler. It still defines design tokens, utility classes, responsive breakpoints, legacy modal overlay rules, card helpers, table-responsive behavior, print rules, reduced-motion handling, and compatibility styles for dynamic JavaScript-generated content. |
| `assets/css/tabler-override.css` | Current Tabler override layer | Provides a small CSS-variable override set for Tabler primary/secondary colors, Inter font, body/card colors, semantic colors, primary button state, muted text, and large icon sizing. This matches the prior decision to avoid Sass/build tooling. |
| `assets/js/app.js` | Shared UI behavior and API helper | Provides CSRF-aware `fetchAPI`, Bootstrap-based toast creation, year switching, logout, required-field validation, and Bootstrap tooltip initialization. Modals are expected to be managed by Bootstrap data attributes instead of custom open/close helpers. |
| `assets/js/charts.js` | Chart.js presentation helpers | Registers datalabels when available, applies the REM/Tabler-aligned color palette, creates responsive dashboard/report charts, injects chart export button styles dynamically, and exposes chart helper functions on `window` for current views. |

## Contract and Spec Conformance Summary

| Source | Required Coverage | Status | Notes |
|---|---|---|---|
| `contracts/ui-evaluation.md` Section 1 Executive Summary | Final recommendation, rationale, top 3 risks, top 3 next actions | Pass | Section 1 of this report contains all four items. |
| `contracts/ui-evaluation.md` Section 2 Pros and Cons | At least 5 pros and 5 cons/risks, with mitigations for high-impact risks | Pass | Section 2 includes 8 pros and 8 risks with mitigations and status. |
| `contracts/ui-evaluation.md` Section 3 Asset Strategy | Compare at least CDN and controlled local assets, package/build optional | Pass | Section 3 includes all three strategies and a comparison result. |
| `contracts/ui-evaluation.md` Section 4 View Evaluation Matrix | One row per required view (Login, Dashboard, Observaciones, Supervision, Reportes, Usuarios, Asignaciones, Eliminadas, Establecimientos, Perfil, Shared header/sidebar/footer) | Pass | Section 4 contains all 11 required rows. |
| `contracts/ui-evaluation.md` Section 5 Component Evaluation Matrix | Header, Sidebar, Cards, Tables, Forms, Modals, Dropdowns, Toasts/notifications, Charts, Badges/status indicators, Loading overlay | Pass | Section 5 contains all 11 required components. |
| `contracts/ui-evaluation.md` Section 6 Responsive Evidence | Desktop, Tablet, Mobile with pass/conditional/fail | Pass | Section 6 documents all three viewports as `conditional` with findings. |
| `contracts/ui-evaluation.md` Section 7 Accessibility Evidence | Contrast, keyboard, focus, modals, form labels, table readability | Pass | Section 7 documents all six checks as `conditional` with findings. |
| `contracts/ui-evaluation.md` Section 8 Role Safety Evidence | Registrador-only, supervisor-only, year selector, navigation, no accidental admin exposure | Pass | Section 8 covers all five requirements plus supporting subtables. |
| `contracts/ui-evaluation.md` Section 9 Closure Criteria | Checklist before declaring Tabler the official visual standard | Pass | Section 9 contains the full checklist. |
| `contracts/ui-evaluation.md` Acceptance Rules | Not full consolidation when views are unreviewed; no unresolved high-risk issues; future-spec for backend/DB/permission/import/export/report logic | Pass | Adoption Decision is `consolidate_with_conditions`; unresolved issues are tracked; future-spec note is included. |
| `spec.md` FR-001..FR-010 | Documented state, pros/cons, strategy comparison, risk mix, criteria, responsive, accessibility, role/permission safety, final recommendation, prioritized actions | Pass | All FRs are addressed in the corresponding sections; Adoption Decision and Prioritized Follow-Up Actions cover FR-009 and FR-010. |
| `spec.md` SC-001 | 100% of primary views reviewed (approved, approved with conditions, or pending with cause) | Pass | All 11 required views have a row with `approve_with_conditions`, `rework`, or equivalent. |
| `spec.md` SC-002 | At least 5 pros, 5 cons/risks, mitigations for high-impact risks | Pass | Section 2 has 8 pros and 8 risks with mitigations. |
| `spec.md` SC-003 | Both registrador and supervisor considered | Pass | Supervisor and Registrador Role-Safety Findings are present. |
| `spec.md` SC-004 | Responsive covers at least 3 sizes | Pass | Desktop, Tablet, and Mobile are documented. |
| `spec.md` SC-005 | Final recommendation is one of the allowed values | Pass | Recommendation is `consolidate`. |
| `quickstart.md` step 1 (scope) | Read active spec, plan, contract, README, prior Tabler migration tasks | Pass | Evidence is referenced in Source Evidence Index and the non-mutating verification rules. |
| `quickstart.md` step 2 (shared layout) | Inspect header/sidebar/footer/styles/tabler-override and record strategy, legacy CSS, navigation, year, user menu | Pass | `Stylesheet and JavaScript Evidence` and `Override and Legacy CSS Dependency Findings` cover all items. |
| `quickstart.md` step 3 (supervisor walkthrough) | Review supervisor-accessible views | Pass | `Supervisor Role-Safety Findings` covers Dashboard, Supervision, Reportes, Usuarios, Asignaciones, Eliminadas, Establecimientos, Perfil. |
| `quickstart.md` step 4 (registrador walkthrough) | Review registrador-accessible views | Pass | `Registrador Role-Safety Findings` covers Dashboard, Observaciones, Reportes, Perfil. |
| `quickstart.md` step 5 (responsive review) | Desktop/tablet/mobile per component | Pass | Responsive Evidence and the Component Matrix document results. |
| `quickstart.md` step 6 (accessibility) | Contrast, focus, modal, forms, dropdowns, tables | Pass | Accessibility Evidence covers all six checks. |
| `quickstart.md` step 7 (asset strategy) | Compare CDN, local assets, optional pipeline | Pass | Asset Strategy Comparison covers all three. |
| `quickstart.md` step 8 (produce report) | Report follows contract, final recommendation is allowed value | Pass | Sections 1, 2, 3, 4, 5, 6, 7, 8, 9 plus Adoption Decision match the contract. |
| `quickstart.md` step 9 (completion gate) | 100% views reviewed, both roles, viewports, mitigations/blocking, future-spec for functional issues | Pass | Documented in Closure Criteria, US3 Verification Summary, and the future-spec note. |

## Final Review

- All file references in the report use project-relative paths that exist in the repository (`README.md`, `openspec/changes/migrar-tabler-dashboard/{proposal,design,tasks}.md`, `includes/{header,sidebar,footer}.php`, `views/{login,dashboard,observaciones,supervision,reportes,usuarios,asignaciones,eliminadas,establecimientos,perfil}.php`, `assets/css/{styles,tabler-override}.css`, `assets/js/{app,charts}.js`).
- The report contains no `[NEEDS CLARIFICATION]` markers, no `TODO`, no `TBD`, and no unresolved Markdown placeholders.
- Section 9 now declares Tabler as the official visual standard and tracks remaining items as post-standard hardening work, not placeholder gaps.
- Path references in tables are textual citations, not Markdown links, so there are no broken relative links.
- This report is documentation-only and must not mutate REM data.

## Visual Polish Progress

- Tokens design system added: `assets/css/tokens.css` with status palette, radius, shadows, gradients, dark mode and sidebar/header tokens.
- Tabler override layer extended: `assets/css/tabler-override.css` with semantic selectors for sidebar, header, avatares, cards, stats, badges, statuses, breadcrumbs, empty states, alerts, focus ring, FAB, login split and reduced-motion.
- `@tabler/icons-webfont` loaded by CDN in header and login, replacing most inline SVG icons.
- Shell rewired: `includes/header.php` (skip link, global search, notifications dropdown, year dropdown, user menu, theme toggle, breadcrumbs) and `includes/sidebar.php` (mini-variant, status dot, role-filtered groups).
- Login split layout with form-floating, inline validation and credentials accordion in `views/login.php`.
- Dashboard premium header card, KPI countup, ranking with progress bars, quick actions as interactive cards, and empty-state illustrations in `views/dashboard.php`.
- Internal views refactored to use `page-header`, `page-actions`, status dots, ti icons in modals and inline `empty-state` placeholders (observaciones, supervision, reportes, usuarios, asignaciones, establecimientos, eliminadas, perfil).
- `assets/js/theme.js` adds theme persistence, sidebar mini-variant, global search filter, mobile overlay, and scroll-revealed FAB.
- `assets/js/app.js` adds `initCountUp` for animated KPI values.
- `assets/js/charts.js` reads design tokens at runtime and applies Tabler font/color to legends, tooltips and trend chart.
- `assets/css/styles.css` is marked DEPRECATED in its file header; new views should not extend it.
- `README.md` updated with new visual architecture section, assets tree, and shell description.
- This visual polish is an additive, no-backend, no-database, no-permission change. It is consistent with the `consolidate` decision and the future follow-up actions documented in the Adoption Decision section.
