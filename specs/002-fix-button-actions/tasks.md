# Tasks: Button Action Completion

**Input**: Design documents from `/specs/002-fix-button-actions/`

**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/action-behavior.md, quickstart.md

**Tests**: Automated tests were not requested. This feature requires safe manual verification, PHP syntax validation for touched PHP files, browser console checks, and explicit warnings for any data-mutating checks against the configured official database.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this belongs to (US1, US2, US3)
- Every task includes an exact file path

## Path Conventions

- PHP endpoints: `api/*.php`
- Domain logic: `models/*.php`
- Screens: `views/*.php`
- Shared client behavior: `assets/js/*.js`
- Shared layout/security: `includes/*.php`
- Feature docs: `specs/002-fix-button-actions/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare shared action inventory and verification evidence so implementation can proceed page by page without losing coverage.

- [X] T001 Create action inventory table in `specs/002-fix-button-actions/action-inventory.md` using the fields from `specs/002-fix-button-actions/contracts/action-behavior.md`
- [X] T002 [P] Record current dashboard and observation controls in `specs/002-fix-button-actions/action-inventory.md` from `views/dashboard.php` and `views/observaciones.php`
- [X] T003 [P] Record current supervisor-admin controls in `specs/002-fix-button-actions/action-inventory.md` from `views/supervision.php`, `views/usuarios.php`, `views/asignaciones.php`, `views/eliminadas.php`, and `views/establecimientos.php`
- [X] T004 [P] Record current report/profile/version controls in `specs/002-fix-button-actions/action-inventory.md` from `views/reportes.php`, `views/perfil.php`, and `views/versionado.php`
- [X] T005 Create verification evidence template in `specs/002-fix-button-actions/verification-evidence.md` using fields from `specs/002-fix-button-actions/quickstart.md`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Fix shared mismatches and establish safe verification before any story-specific completion.

**CRITICAL**: No user story work should begin until this phase is complete.

- [X] T006 Review mutable action CSRF coverage and document any gaps in `specs/002-fix-button-actions/action-inventory.md` for `api/observations.php`, `api/supervision.php`, `api/users.php`, `api/assignments.php`, `api/deleted.php`, `api/locations.php`, `api/report_queue.php`, and `api/versioning.php`
- [X] T007 [P] Confirm role guards for affected screens in `index.php`, `views/observaciones.php`, `views/supervision.php`, `views/usuarios.php`, `views/asignaciones.php`, `views/eliminadas.php`, `views/establecimientos.php`, and `views/versionado.php`
- [X] T008 [P] Normalize dependent-establishment lookup contract in `api/locations.php` so visible comuna filters can use one documented action name without breaking existing callers
- [X] T009 [P] Add or confirm safe user feedback fallback behavior in `assets/js/app.js` for failed JSON/action requests without changing REM business rules
- [X] T010 Update `specs/002-fix-button-actions/verification-evidence.md` with a warning that configured `ENVIRONMENT=production` requires controlled records or confirmation-only walkthroughs for mutating checks

**Checkpoint**: Foundation ready - user story implementation can now proceed.

---

## Phase 3: User Story 1 - Registrar y revisar observaciones sin acciones rotas (Priority: P1) MVP

**Goal**: Registradores can enter observation workflows from Dashboard and Observaciones, open the correct forms/modals, import valid Excel files, preview results, and see clear feedback.

**Independent Test**: Login as `registrador2`, open Dashboard and Observaciones, verify new-observation entry, detail/edit modals, import modal, Excel preview behavior, and template download without destructive production-data actions.

### Verification for User Story 1

- [X] T011 [P] [US1] Verify current registrador dashboard action outcomes and record pass/fail notes in `specs/002-fix-button-actions/verification-evidence.md`
- [X] T012 [P] [US1] Verify current Observaciones create, edit, detail, trash, import, preview, confirm, and template-download controls and record pass/fail notes in `specs/002-fix-button-actions/verification-evidence.md`
- [X] T013 [P] [US1] Verify `api/observations.php`, `api/import.php`, and `api/import_template.php` behavior expectations against `specs/002-fix-button-actions/contracts/action-behavior.md`

### Implementation for User Story 1

- [X] T014 [US1] Update Dashboard new-observation links in `views/dashboard.php` to use an explicit action parameter such as `?page=observaciones&action=new&year=...`
- [X] T015 [US1] Add action-parameter handling in `views/observaciones.php` so `action=new` opens the new observation modal after page load when the registrador has assignments
- [X] T016 [US1] Restrict the import file picker and visible import copy in `views/observaciones.php` to Excel formats accepted by `api/import.php`
- [X] T017 [US1] Fix import preview and reset visibility in `views/observaciones.php` by using consistent Bootstrap visibility classes for `importStep1`, `importStep2`, `importErrors`, `importProgress`, and `importActions`
- [X] T018 [US1] Ensure import confirmation in `views/observaciones.php` restores progress/action controls after success and failure and leaves the user able to retry
- [X] T019 [US1] Ensure observation detail and edit flows in `views/observaciones.php` display user-visible errors when `api/observations.php` rejects or cannot load a record
- [X] T020 [US1] Run `php -l views/dashboard.php`, `php -l views/observaciones.php`, `php -l api/import.php`, and `php -l api/observations.php` and record results in `specs/002-fix-button-actions/verification-evidence.md`
- [ ] T021 [US1] Perform the registrador quickstart steps for Dashboard and Observaciones from `specs/002-fix-button-actions/quickstart.md` and record evidence in `specs/002-fix-button-actions/verification-evidence.md`

**Checkpoint**: US1 is complete when registrador observation entry, import preview, detail/edit, and template actions are functional or intentionally disabled with explanation.

---

## Phase 4: User Story 2 - Gestionar acciones supervisoras críticas (Priority: P2)

**Goal**: Supervisors can reliably use supervision, user, assignment, deleted-observation, and establishment actions with correct filters, confirmations, validation, and feedback.

**Independent Test**: Login as `supervisor1`, walk through supervisor screens, verify modal opening, filters, selection counters, confirmations, controlled create/update actions, and confirmation-only irreversible actions.

### Verification for User Story 2

- [ ] T022 [P] [US2] Verify current Supervisión filters, dependent establishment loading, selection counter, and mass-action behavior in `views/supervision.php` and record findings in `specs/002-fix-button-actions/verification-evidence.md`
- [ ] T023 [P] [US2] Verify current Usuarios create/edit/toggle/reset/audit/delete behavior in `views/usuarios.php` and `api/users.php` and record findings in `specs/002-fix-button-actions/verification-evidence.md`
- [ ] T024 [P] [US2] Verify current Asignaciones year, assign/reassign, annual/temporary, remove, copy, and temporary-list behavior in `views/asignaciones.php` and `api/assignments.php` and record findings in `specs/002-fix-button-actions/verification-evidence.md`
- [ ] T025 [P] [US2] Verify current Eliminadas and Establecimientos action behavior in `views/eliminadas.php`, `api/deleted.php`, `views/establecimientos.php`, and `api/locations.php` and record findings in `specs/002-fix-button-actions/verification-evidence.md`

### Implementation for User Story 2

- [ ] T026 [US2] Fix Supervisión comuna-dependent establishment loading in `views/supervision.php` to use the normalized locations contract from `api/locations.php`
- [ ] T027 [US2] Fix Supervisión selected-count update in `views/supervision.php` so the displayed count uses the actual DOM structure and mass-action buttons stay synchronized
- [ ] T028 [US2] Harden Supervisión approve/cancel/delete feedback in `views/supervision.php` so validation failures and server errors keep the confirmation modal recoverable
- [ ] T029 [US2] Align new-user password field attributes, hints, and client validation in `views/usuarios.php` with the real policy of 8 characters, one uppercase letter, and one number
- [ ] T030 [US2] Clarify reset-password messaging in `views/usuarios.php` so default reset behavior is distinct from the new-user password policy
- [ ] T031 [US2] Ensure user create/edit/toggle/reset/audit/delete error paths in `views/usuarios.php` show actionable feedback from `api/users.php`
- [ ] T032 [US2] Fix Asignaciones selected-registrador action visibility in `views/asignaciones.php` by using consistent visibility classes for `accionesAsignacion`
- [ ] T033 [US2] Fix Asignaciones annual/temporary month selector visibility in `views/asignaciones.php` by using consistent visibility classes for `mesesEspecificosContainer`
- [ ] T034 [US2] Move or expose temporary reassignment listing as a GET-compatible action in `api/assignments.php` so `views/asignaciones.php` can load `action=temporales&anio=...`
- [ ] T035 [US2] Ensure Asignaciones save/remove/copy/temporary-remove flows in `views/asignaciones.php` refresh registrador list, assigned establishments, and temporary list consistently after success
- [ ] T036 [US2] Confirm Eliminadas restore and permanent-delete confirmation behavior in `views/eliminadas.php` keeps irreversible confirmation mandatory and records skipped permanent-delete evidence in `specs/002-fix-button-actions/verification-evidence.md`
- [ ] T037 [US2] Confirm Establecimientos create/edit/toggle validation and duplicate-code feedback in `views/establecimientos.php` and `api/locations.php`; patch only if observed feedback is missing or ambiguous
- [ ] T038 [US2] Run `php -l views/supervision.php`, `php -l views/usuarios.php`, `php -l views/asignaciones.php`, `php -l views/eliminadas.php`, `php -l views/establecimientos.php`, `php -l api/users.php`, `php -l api/assignments.php`, `php -l api/deleted.php`, and `php -l api/locations.php` and record results in `specs/002-fix-button-actions/verification-evidence.md`
- [ ] T039 [US2] Perform the supervisor quickstart steps for Supervisión, Usuarios, Asignaciones, Eliminadas, and Establecimientos from `specs/002-fix-button-actions/quickstart.md` and record evidence in `specs/002-fix-button-actions/verification-evidence.md`

**Checkpoint**: US2 is complete when supervisor administrative actions are functional, recoverable on error, role-safe, and confirmed before high-impact changes.

---

## Phase 5: User Story 3 - Obtener reportes, perfil y operaciones auxiliares confiables (Priority: P3)

**Goal**: Users can rely on report, profile, and versioning actions to reflect visible choices, validate inputs, and warn before high-impact operations.

**Independent Test**: Login as supervisor and registrador as needed, verify report filters/output/queue, profile password validation on a controlled account, and version snapshot/rollback warning without executing rollback unless explicitly approved.

### Verification for User Story 3

- [ ] T040 [P] [US3] Verify current Reportes filter, tab, export, PDF, queue, refresh, and download behavior in `views/reportes.php`, `api/reports.php`, `api/export.php`, and `api/report_queue.php` and record findings in `specs/002-fix-button-actions/verification-evidence.md`
- [ ] T041 [P] [US3] Verify current Perfil password-change validation and feedback in `views/perfil.php` and `api/users.php` and record findings in `specs/002-fix-button-actions/verification-evidence.md`
- [ ] T042 [P] [US3] Verify current Versionado create snapshot and rollback warning behavior in `views/versionado.php` and `api/versioning.php` without executing rollback and record findings in `specs/002-fix-button-actions/verification-evidence.md`

### Implementation for User Story 3

- [ ] T043 [US3] Fix Reportes comuna-dependent establishment loading in `views/reportes.php` to use the normalized locations contract from `api/locations.php`
- [ ] T044 [US3] Update Reportes export parameter construction in `views/reportes.php` so trimester selections map to the full visible period rather than an empty month
- [ ] T045 [US3] Align Reportes queue payloads in `views/reportes.php` with supported report categories or clearly disable unsupported active-tab queue actions with explanation
- [ ] T046 [US3] Update `api/report_queue.php` to support any report category intentionally left enabled by `views/reportes.php`, including `serie_detalle` and `hoja_detalle` if those tabs remain queueable
- [ ] T047 [US3] Ensure Reportes empty-result and export/download failures in `views/reportes.php` show clear feedback rather than silent windows or no-op buttons
- [ ] T048 [US3] Align Perfil password field attributes, hints, and client validation in `views/perfil.php` with the real policy of 8 characters, one uppercase letter, and one number
- [ ] T049 [US3] Improve Versionado rollback confirmation text in `views/versionado.php` to explicitly state that rollback modifies system files and must not be used without a maintenance window
- [ ] T050 [US3] Run `php -l views/reportes.php`, `php -l views/perfil.php`, `php -l views/versionado.php`, `php -l api/report_queue.php`, `php -l api/reports.php`, `php -l api/export.php`, `php -l api/users.php`, and `php -l api/versioning.php` and record results in `specs/002-fix-button-actions/verification-evidence.md`
- [ ] T051 [US3] Perform the quickstart steps for Reportes, Perfil, and Versionado from `specs/002-fix-button-actions/quickstart.md` and record evidence in `specs/002-fix-button-actions/verification-evidence.md`

**Checkpoint**: US3 is complete when reports reflect visible filters, profile password validation matches policy, and versioning actions warn clearly before high-impact operations.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Verify coverage, documentation consistency, and readiness for handoff.

- [ ] T052 [P] Check `specs/002-fix-button-actions/action-inventory.md` against `specs/002-fix-button-actions/contracts/action-behavior.md` and mark every action functional, disabled with explanation, removed, or deferred with reason
- [ ] T053 [P] Check modified UI against Tabler-only and no-new-inline-style expectations from `.specify/memory/constitution.md`
- [ ] T054 [P] Search modified files for obsolete endpoint references such as `action=get_establecimientos` and record result in `specs/002-fix-button-actions/verification-evidence.md`
- [ ] T055 Update `README.md` only if final behavior changes operating guidance, credentials, password-policy wording, report behavior, or action workflow expectations
- [ ] T056 Run final PHP syntax validation for all touched PHP files and record exact command/output in `specs/002-fix-button-actions/verification-evidence.md`
- [ ] T057 Complete the full role smoke checklist from `specs/002-fix-button-actions/quickstart.md` and record skipped destructive actions with reasons in `specs/002-fix-button-actions/verification-evidence.md`
- [ ] T058 Review `specs/002-fix-button-actions/tasks.md`, `specs/002-fix-button-actions/verification-evidence.md`, and `specs/002-fix-button-actions/action-inventory.md` for unresolved placeholders before handoff

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; starts immediately.
- **Foundational (Phase 2)**: Depends on Setup completion and blocks all user stories.
- **US1 (Phase 3)**: Depends on Foundational; MVP scope.
- **US2 (Phase 4)**: Depends on Foundational; can run after or parallel with US1 if file conflicts are coordinated.
- **US3 (Phase 5)**: Depends on Foundational; can run after or parallel with US1/US2 if report/profile/versioning files are isolated.
- **Polish (Phase 6)**: Depends on all selected user stories.

### User Story Dependencies

- **User Story 1 (P1)**: Independent after Foundational; delivers registrador observation workflow MVP.
- **User Story 2 (P2)**: Independent after Foundational; supervisor workflows do not require US1 implementation but share action feedback conventions.
- **User Story 3 (P3)**: Independent after Foundational; report/profile/versioning work should reuse shared feedback and locations contract decisions.

### Within Each User Story

- Verification tasks come first to confirm current failure modes.
- Endpoint/API contract alignment precedes UI calls that depend on it.
- UI visibility/validation changes precede manual walkthrough evidence.
- Syntax checks and quickstart evidence complete each story checkpoint.
- Destructive data checks must be confirmation-only or use controlled test records with restore notes.

### Parallel Opportunities

- T002, T003, T004 can run in parallel.
- T007, T008, T009 can run in parallel after T006 defines shared coverage.
- T011, T012, T013 can run in parallel for US1 verification.
- T022, T023, T024, T025 can run in parallel for US2 verification.
- T040, T041, T042 can run in parallel for US3 verification.
- T052, T053, T054 can run in parallel during polish.

---

## Parallel Example: User Story 1

```bash
# Parallel verification for US1:
Task: "T011 Verify registrador dashboard action outcomes"
Task: "T012 Verify Observaciones controls"
Task: "T013 Verify observations/import/template API expectations"
```

## Parallel Example: User Story 2

```bash
# Parallel verification for US2:
Task: "T022 Verify Supervisión controls"
Task: "T023 Verify Usuarios controls"
Task: "T024 Verify Asignaciones controls"
Task: "T025 Verify Eliminadas and Establecimientos controls"
```

## Parallel Example: User Story 3

```bash
# Parallel verification for US3:
Task: "T040 Verify Reportes controls"
Task: "T041 Verify Perfil password-change controls"
Task: "T042 Verify Versionado controls"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup.
2. Complete Phase 2: Foundational.
3. Complete Phase 3: US1 registrar observation actions.
4. Stop and validate that Dashboard to Observaciones, creation modal, detail/edit, import preview, confirmation, and template download work for registrador.

### Incremental Delivery

1. Deliver US1 to restore the primary registrador workflow.
2. Deliver US2 to restore supervisor administrative and review workflows.
3. Deliver US3 to restore reporting, profile, and versioning support workflows.
4. Complete Phase 6 to validate full action inventory and documentation consistency.

### Suggested MVP Scope

The MVP is Phase 1 + Phase 2 + Phase 3. It delivers the highest-value operational flow: registrador observation entry, import preview, and observation review actions.

---

## Notes

- No database schema migration is planned.
- The configured environment points to the official database, so mutating checks must be controlled and documented.
- Do not execute permanent delete or rollback during normal verification unless explicitly approved.
- Keep Tabler as the official visual standard and do not extend `assets/css/styles.css`.
- Run `php -l` for every touched PHP file before marking the relevant story complete.
