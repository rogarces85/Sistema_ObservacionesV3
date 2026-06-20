# Tasks: Tabler Dashboard Review

**Input**: Design documents from `/specs/001-tabler-dashboard-review/`

**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/ui-evaluation.md, quickstart.md

**Tests**: This feature uses safe manual verification and documentation review. No automated tests are required because the scope is evaluation/consolidation documentation and must not mutate REM data.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3)
- Every task includes an exact file path

## Path Conventions

- Feature docs: `specs/001-tabler-dashboard-review/`
- Evaluation report: `specs/001-tabler-dashboard-review/evaluation-report.md`
- UI source evidence: `includes/`, `views/`, `assets/css/`, `assets/js/`
- Prior migration evidence: `openspec/changes/migrar-tabler-dashboard/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare the evaluation workspace and source evidence list.

- [X] T001 Create `specs/001-tabler-dashboard-review/evaluation-report.md` from the required sections in `specs/001-tabler-dashboard-review/contracts/ui-evaluation.md`
- [X] T002 Create source evidence index in `specs/001-tabler-dashboard-review/evaluation-report.md` referencing `README.md`, `openspec/changes/migrar-tabler-dashboard/proposal.md`, `openspec/changes/migrar-tabler-dashboard/design.md`, and `openspec/changes/migrar-tabler-dashboard/tasks.md`
- [X] T003 [P] Record current shared layout files in `specs/001-tabler-dashboard-review/evaluation-report.md` from `includes/header.php`, `includes/sidebar.php`, and `includes/footer.php`
- [X] T004 [P] Record current stylesheet and JavaScript evidence in `specs/001-tabler-dashboard-review/evaluation-report.md` from `assets/css/styles.css`, `assets/css/tabler-override.css`, `assets/js/app.js`, and `assets/js/charts.js`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Establish evaluation criteria that all user stories depend on.

**CRITICAL**: No user story work can begin until this phase is complete.

- [ ] T005 Define View Evaluation fields in `specs/001-tabler-dashboard-review/evaluation-report.md` using `specs/001-tabler-dashboard-review/data-model.md`
- [ ] T006 Define Component Evaluation fields in `specs/001-tabler-dashboard-review/evaluation-report.md` using `specs/001-tabler-dashboard-review/data-model.md`
- [ ] T007 Define Asset Strategy Decision criteria in `specs/001-tabler-dashboard-review/evaluation-report.md` using `specs/001-tabler-dashboard-review/research.md`
- [ ] T008 Define non-mutating verification rules in `specs/001-tabler-dashboard-review/evaluation-report.md` using `specs/001-tabler-dashboard-review/quickstart.md`
- [ ] T009 Confirm no backend, database, permission, import, export, or report logic changes are required in `specs/001-tabler-dashboard-review/evaluation-report.md`

**Checkpoint**: Foundation ready - user story work can now begin.

---

## Phase 3: User Story 1 - Evaluate Tabler Fit for REM Dashboard (Priority: P1) 🎯 MVP

**Goal**: Produce a decision-ready comparison of Tabler benefits, risks, alternatives, and adoption conditions for the REM system.

**Independent Test**: A stakeholder can read `specs/001-tabler-dashboard-review/evaluation-report.md` and understand whether Tabler should be consolidated, conditionally consolidated, postponed, or partially reverted.

### Verification for User Story 1 ⚠️

- [ ] T010 [P] [US1] Verify prior Tabler migration status from `openspec/changes/migrar-tabler-dashboard/tasks.md` and summarize completed versus pending items in `specs/001-tabler-dashboard-review/evaluation-report.md`
- [ ] T011 [P] [US1] Verify current Tabler asset usage in `includes/header.php`, `includes/footer.php`, and `views/login.php` and document the finding in `specs/001-tabler-dashboard-review/evaluation-report.md`
- [ ] T012 [P] [US1] Verify current override strategy in `assets/css/tabler-override.css` and legacy CSS dependency in `assets/css/styles.css`, then document conflicts or dependencies in `specs/001-tabler-dashboard-review/evaluation-report.md`

### Implementation for User Story 1

- [ ] T013 [US1] Write at least 5 Tabler pros for this REM system in `specs/001-tabler-dashboard-review/evaluation-report.md`
- [ ] T014 [US1] Write at least 5 Tabler cons or risks with mitigations in `specs/001-tabler-dashboard-review/evaluation-report.md`
- [ ] T015 [US1] Compare CDN and controlled local asset strategies in `specs/001-tabler-dashboard-review/evaluation-report.md`
- [ ] T016 [US1] Add an executive recommendation draft in `specs/001-tabler-dashboard-review/evaluation-report.md` with top risks and next actions

**Checkpoint**: US1 is complete when the report contains a standalone pros/cons and asset-strategy recommendation.

---

## Phase 4: User Story 2 - Validate Dashboard Experience by Role (Priority: P2)

**Goal**: Confirm registrador and supervisor journeys remain clear, usable, and role-safe across the Tabler-influenced UI.

**Independent Test**: A reviewer can follow `specs/001-tabler-dashboard-review/quickstart.md`, complete the walkthroughs, and see per-view evidence in `specs/001-tabler-dashboard-review/evaluation-report.md`.

### Verification for User Story 2 ⚠️

- [ ] T017 [P] [US2] Review supervisor-accessible views `views/dashboard.php`, `views/supervision.php`, `views/reportes.php`, `views/usuarios.php`, `views/asignaciones.php`, `views/eliminadas.php`, `views/establecimientos.php`, and `views/perfil.php`, then record role-safety findings in `specs/001-tabler-dashboard-review/evaluation-report.md`
- [ ] T018 [P] [US2] Review registrador-accessible views `views/dashboard.php`, `views/observaciones.php`, `views/reportes.php`, and `views/perfil.php`, then record role-safety findings in `specs/001-tabler-dashboard-review/evaluation-report.md`
- [ ] T019 [P] [US2] Review shared navigation and year context in `includes/sidebar.php` and `includes/header.php`, then record navigation findings in `specs/001-tabler-dashboard-review/evaluation-report.md`

### Implementation for User Story 2

- [ ] T020 [US2] Populate the View Evaluation Matrix in `specs/001-tabler-dashboard-review/evaluation-report.md` for login, dashboard, observaciones, supervision, reportes, usuarios, asignaciones, eliminadas, establecimientos, perfil, and shared header/sidebar/footer
- [ ] T021 [US2] Populate responsive evidence for desktop, tablet, and mobile in `specs/001-tabler-dashboard-review/evaluation-report.md`
- [ ] T022 [US2] Populate accessibility evidence for contrast, focus, keyboard navigation, modal behavior, form labels, and table readability in `specs/001-tabler-dashboard-review/evaluation-report.md`
- [ ] T023 [US2] Document any role-safety concerns or confirm no accidental exposure of administrative actions in `specs/001-tabler-dashboard-review/evaluation-report.md`

**Checkpoint**: US2 is complete when all required views have role, responsive, accessibility, and business-flow evaluation states.

---

## Phase 5: User Story 3 - Define Adoption Scope and Closure Criteria (Priority: P3)

**Goal**: Define what is approved, conditional, deferred, or outside scope so the team can close or continue the Tabler effort without ambiguity.

**Independent Test**: A maintainer can use the closure criteria in `specs/001-tabler-dashboard-review/evaluation-report.md` to decide the next implementation or cleanup tasks without extra discovery.

### Verification for User Story 3 ⚠️

- [ ] T024 [P] [US3] Verify all required views from `specs/001-tabler-dashboard-review/contracts/ui-evaluation.md` have evaluation rows in `specs/001-tabler-dashboard-review/evaluation-report.md`
- [ ] T025 [P] [US3] Verify all required components from `specs/001-tabler-dashboard-review/contracts/ui-evaluation.md` have component evaluation rows in `specs/001-tabler-dashboard-review/evaluation-report.md`
- [ ] T026 [P] [US3] Verify high-risk findings in `specs/001-tabler-dashboard-review/evaluation-report.md` have mitigations or blocking status

### Implementation for User Story 3

- [ ] T027 [US3] Populate the Component Evaluation Matrix in `specs/001-tabler-dashboard-review/evaluation-report.md` for header, sidebar, cards, tables, forms, modals, dropdowns, toasts, charts, badges, and loading overlay
- [ ] T028 [US3] Write closure criteria checklist in `specs/001-tabler-dashboard-review/evaluation-report.md`
- [ ] T029 [US3] Finalize Adoption Decision in `specs/001-tabler-dashboard-review/evaluation-report.md` as consolidate, consolidate_with_conditions, postpone, or partial_revert
- [ ] T030 [US3] List prioritized follow-up actions in `specs/001-tabler-dashboard-review/evaluation-report.md`, splitting any backend, database, permission, import, export, or report-logic issue into a separate future spec note

**Checkpoint**: US3 is complete when the report has a final adoption decision, closure criteria, and prioritized next actions.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Validate documentation quality and prepare the feature for handoff.

- [ ] T031 [P] Check `specs/001-tabler-dashboard-review/evaluation-report.md` against `specs/001-tabler-dashboard-review/contracts/ui-evaluation.md`
- [ ] T032 [P] Check `specs/001-tabler-dashboard-review/evaluation-report.md` against success criteria in `specs/001-tabler-dashboard-review/spec.md`
- [ ] T033 [P] Confirm quickstart evidence in `specs/001-tabler-dashboard-review/evaluation-report.md` covers every step in `specs/001-tabler-dashboard-review/quickstart.md`
- [ ] T034 Update `README.md` only if the final adoption decision changes the documented visual architecture or operating guidance
- [ ] T035 Run final documentation review for unresolved placeholders, broken relative links, and unchecked required evidence in `specs/001-tabler-dashboard-review/evaluation-report.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; starts immediately.
- **Foundational (Phase 2)**: Depends on Setup; blocks all user-story work.
- **US1 (Phase 3)**: Depends on Foundational; MVP scope.
- **US2 (Phase 4)**: Depends on Foundational; can run after or parallel with US1 if a reviewer is available.
- **US3 (Phase 5)**: Depends on US1 and US2 because it finalizes scope using their evidence.
- **Polish (Phase 6)**: Depends on all desired user stories.

### User Story Dependencies

- **User Story 1 (P1)**: Independent after Foundational; creates the decision basis.
- **User Story 2 (P2)**: Independent after Foundational; creates role and UI evidence.
- **User Story 3 (P3)**: Requires outputs from US1 and US2 to finalize adoption decision.

### Within Each User Story

- Verification tasks come before report finalization tasks.
- Evidence must be recorded before recommendation text is finalized.
- High-risk findings must have mitigation or blocking status before final adoption decision.
- No task may mutate production or REM data.

### Parallel Opportunities

- T003 and T004 can run in parallel.
- T010, T011, and T012 can run in parallel.
- T017, T018, and T019 can run in parallel.
- T024, T025, and T026 can run in parallel after US1 and US2 evidence exists.
- T031, T032, and T033 can run in parallel during polish.

---

## Parallel Example: User Story 1

```bash
# Parallel evidence collection for US1:
Task: "T010 Verify prior Tabler migration status from openspec/changes/migrar-tabler-dashboard/tasks.md"
Task: "T011 Verify current Tabler asset usage in includes/header.php, includes/footer.php, and views/login.php"
Task: "T012 Verify current override strategy in assets/css/tabler-override.css and assets/css/styles.css"
```

## Parallel Example: User Story 2

```bash
# Parallel role walkthrough evidence for US2:
Task: "T017 Review supervisor-accessible views and record findings"
Task: "T018 Review registrador-accessible views and record findings"
Task: "T019 Review shared navigation and year context"
```

## Parallel Example: User Story 3

```bash
# Parallel completeness checks for US3:
Task: "T024 Verify all required views have evaluation rows"
Task: "T025 Verify all required components have component evaluation rows"
Task: "T026 Verify high-risk findings have mitigations or blocking status"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup.
2. Complete Phase 2: Foundational criteria.
3. Complete Phase 3: US1 Tabler fit evaluation.
4. Stop and validate that pros/cons, asset strategy, and recommendation draft are understandable without additional context.

### Incremental Delivery

1. Deliver US1 to establish decision basis.
2. Deliver US2 to validate real role-based user experience.
3. Deliver US3 to finalize closure criteria and adoption decision.
4. Complete polish to verify report contract and success criteria.

### Suggested MVP Scope

The MVP is Phase 1 + Phase 2 + Phase 3 (US1). It provides a decision basis for whether continuing Tabler evaluation is worthwhile before investing in full role/responsive walkthroughs.

---

## Notes

- All tasks are non-mutating documentation and review tasks unless a future spec explicitly authorizes implementation.
- Any functional issue discovered during review must be documented as a separate future spec note, not fixed inside this feature.
- If later implementation touches PHP files, run `php -l` on touched files before completion.
- Keep `specs/001-tabler-dashboard-review/evaluation-report.md` as the single output artifact for the evaluation evidence and final adoption decision.
