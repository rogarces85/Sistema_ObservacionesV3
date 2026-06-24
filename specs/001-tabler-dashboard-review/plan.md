# Implementation Plan: Tabler Dashboard Review

**Branch**: `004-tabler-dashboard-review` | **Date**: 2026-06-20 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001-tabler-dashboard-review/spec.md`

**Setup Note**: `.specify/scripts/powershell/setup-plan.ps1` is not present in this repository. The plan paths were resolved from `.specify/feature.json`, the active branch, and `.specify/templates/plan-template.md`.

## Summary

This feature converts the existing Tabler investigation into a technical evaluation and consolidation plan. The system already has partial Tabler adoption through the shared layout, login, overrides, and several migrated views. The plan focuses on documenting the current visual architecture, comparing CDN versus controlled local assets, auditing role-based journeys, and producing a go/no-go recommendation for Tabler as the primary dashboard UI standard.

No backend behavior, database schema, REM business rules, permissions, imports, exports, or report calculations are changed by this plan.

## Technical Context

**Language/Version**: PHP 7.4+ project; PHP 8.2.12 observed in local CLI.

**Primary Dependencies**: Tabler UI 1.4.0 by CDN, Bootstrap-compatible Tabler components, Chart.js 4.4, existing CSS overrides, existing PHP views.

**Storage**: No new storage. Evaluation artifacts are Markdown documents under `specs/001-tabler-dashboard-review/`.

**Testing**: Documentation review, manual UI walkthrough, responsive checks for desktop/tablet/mobile, accessibility spot checks, `php -l` only if PHP files are later touched.

**Target Platform**: Existing Apache/XAMPP-served PHP web application used by registradores and supervisores.

**Project Type**: Existing PHP monolith with server-rendered views and JavaScript enhancements.

**Performance Goals**: Dashboard and primary views remain usable without perceptible delay from visual assets; no additional blocking dependencies beyond the selected Tabler asset strategy.

**Constraints**: Must not alter REM roles, backend authorization, database schema, imports, exports, report logic, or session behavior. Must respect current monolith structure. Must document CDN dependency risk and responsive/accessibility gaps.

**Scale/Scope**: Review 10 primary views plus shared layout and login: dashboard, observaciones, supervision, reportes, usuarios, asignaciones, eliminadas, establecimientos, perfil, login, header/sidebar/footer.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **Source of Truth**: PASS. Plan references `README.md`, existing OpenSpec change `openspec/changes/migrar-tabler-dashboard`, and current feature spec. Documentation updates are limited to plan artifacts; any later implementation must update README if behavior or visual standard changes.
- **RBAC & REM Data Integrity**: PASS. Affected roles are registrador and supervisor for visual walkthrough only. No backend permission, assignment, year, month, report, import, or data visibility behavior changes are planned.
- **Security & Session Safety**: PASS. The plan explicitly evaluates CDN/resource dependency, visual exposure of administrative actions, focus behavior, and degradation if visual assets fail. No session, password, CSRF, or upload behavior changes are planned.
- **Testable & Reversible Data Changes**: PASS. No database-mutating work is part of this plan. Verification is manual/non-mutating. Any future UI changes must be reversible through normal source control and documented acceptance checks.
- **Simple Monolith & Observability**: PASS. The plan works with existing `views/`, `includes/`, `assets/`, and `specs/`. It adds documentation and UI evaluation contracts only; no new framework, service, queue, or architectural layer.

Post-design re-check: PASS. `research.md`, `data-model.md`, `contracts/ui-evaluation.md`, and `quickstart.md` preserve the same constraints and add no unresolved clarifications.

## Project Structure

### Documentation (this feature)

```text
specs/001-tabler-dashboard-review/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── ui-evaluation.md
├── checklists/
│   └── requirements.md
└── spec.md
```

### Source Code (repository root)

```text
includes/header.php        # Current shared shell loads Tabler CSS and layout wrapper
includes/sidebar.php       # Current Tabler-style vertical navigation
includes/footer.php        # Current Tabler JS and Chart.js loading
assets/css/styles.css      # Legacy CSS still present as fallback/legacy layer
assets/css/tabler-override.css # Current Tabler color and typography overrides
assets/js/app.js           # Existing shared client behavior
assets/js/charts.js        # Existing dashboard/report charts
views/*.php                # Primary screens to audit visually
openspec/changes/migrar-tabler-dashboard/ # Prior Tabler migration evidence
```

**Structure Decision**: Keep the existing PHP monolith. This planning phase creates only documentation artifacts and a UI evaluation contract. Any later implementation must be incremental inside the existing `includes/`, `views/`, `assets/css/`, and `assets/js/` structure.

## Complexity Tracking

No constitution violations or added architectural complexity are required.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | N/A | N/A |

## Phase 0: Research Output

Research is captured in [research.md](./research.md). It resolves the main decisions:

- Treat Tabler as already partially adopted, not a greenfield framework choice.
- Evaluate CDN as current state and local controlled assets as resilience option.
- Keep CSS legacy until visual and dynamic-content audit proves safe removal.
- Use role-based manual walkthroughs as primary verification.

## Phase 1: Design Output

Design artifacts generated:

- [data-model.md](./data-model.md): evaluation entities, fields, states, and validation rules.
- [contracts/ui-evaluation.md](./contracts/ui-evaluation.md): required UI evaluation report contract.
- [quickstart.md](./quickstart.md): manual validation workflow for the evaluator.

## Risk & Verification Strategy

Primary risks:

- CDN unavailability can degrade the UI if no local fallback exists.
- Mixed Tabler and legacy CSS can produce inconsistent spacing, colors, or component behavior.
- Large tables and charts can become hard to use on mobile.
- Visual refactors can accidentally expose or hide role-specific actions.

Verification approach:

- Non-mutating role walkthroughs with registrador and supervisor sessions.
- Responsive checks at desktop, tablet, and mobile widths.
- Accessibility spot checks for contrast, focus, keyboard navigation, modals, and forms.
- Evidence table per view using the UI evaluation contract.

## Readiness For Tasks

Ready for `/speckit.tasks`. Task generation should create documentation and audit tasks first, then optional implementation tasks only if the evaluation recommends specific changes. Any implementation task must preserve the no-backend/no-database scope unless a new spec is created.
