---

description: "Task list template for feature implementation"
---

# Tasks: [FEATURE NAME]

**Input**: Design documents from `/specs/[###-feature-name]/`

**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Include verification tasks required by the specification and
constitution. For behavior/data changes, include safe tests or explicit manual
verification with database mutation warnings.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **PHP monolith**: `api/`, `models/`, `views/`, `includes/`, `config/`,
  `assets/`, `specs/`
- **Runtime files**: `uploads/` is for generated/imported files and must not be
  committed except allowed placeholders
- Paths MUST reflect the real repository structure from plan.md

<!--
  ============================================================================
  IMPORTANT: The tasks below are SAMPLE TASKS for illustration purposes only.

  The /speckit.tasks command MUST replace these with actual tasks based on:
  - User stories from spec.md (with their priorities P1, P2, P3...)
  - Feature requirements from plan.md
  - Entities from data-model.md
  - Endpoints from contracts/

  Tasks MUST be organized by user story so each story can be:
  - Implemented independently
  - Tested independently
  - Delivered as an MVP increment

  DO NOT keep these sample tasks in the generated tasks.md file.
  ============================================================================
-->

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [ ] T001 Create project structure per implementation plan
- [ ] T002 Initialize [language] project with [framework] dependencies
- [ ] T003 [P] Configure linting and formatting tools

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

Examples of foundational tasks (adjust based on your project):

- [ ] T004 Define or update SQL migration(s) in config/ or specs/ with ordering notes
- [ ] T005 [P] Implement backend role/permission checks in relevant api/*.php or models/*.php
- [ ] T006 [P] Add CSRF/session/config handling for mutating authenticated flows
- [ ] T007 Create or update shared models/entities that all stories depend on
- [ ] T008 Configure useful error handling, logging, history, or audit records
- [ ] T009 Document environment/configuration changes without committing secrets
- [ ] T010 Define rollback/backup steps for database-mutating work

**Checkpoint**: Foundation ready - user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - [Title] (Priority: P1) 🎯 MVP

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Verification for User Story 1 ⚠️

> **NOTE: Use automated tests when safe. If verification mutates the configured
> database, state the target database and restore/rollback step.**

- [ ] T011 [P] [US1] Verify API contract for [endpoint] with expected status codes
- [ ] T012 [P] [US1] Verify user journey for [flow] including role restrictions

### Implementation for User Story 1

- [ ] T013 [P] [US1] Update model/entity logic in models/[entity].php
- [ ] T014 [US1] Implement API behavior in api/[endpoint].php
- [ ] T015 [US1] Update UI flow in views/[view].php and assets/js/[script].js if needed
- [ ] T016 [US1] Add backend validation, authorization, and CSRF handling
- [ ] T017 [US1] Add user-visible errors plus logs/history/audit records where required

**Checkpoint**: At this point, User Story 1 should be fully functional and testable independently

---

## Phase 4: User Story 2 - [Title] (Priority: P2)

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Verification for User Story 2 ⚠️

- [ ] T018 [P] [US2] Verify API contract for [endpoint] with expected status codes
- [ ] T019 [P] [US2] Verify user journey for [flow] including role restrictions

### Implementation for User Story 2

- [ ] T020 [P] [US2] Update model/entity logic in models/[entity].php
- [ ] T021 [US2] Implement API/model behavior in api/[endpoint].php or models/[model].php
- [ ] T022 [US2] Update view/client behavior in views/[view].php or assets/js/[script].js
- [ ] T023 [US2] Integrate with User Story 1 components (if needed)

**Checkpoint**: At this point, User Stories 1 AND 2 should both work independently

---

## Phase 5: User Story 3 - [Title] (Priority: P3)

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Verification for User Story 3 ⚠️

- [ ] T024 [P] [US3] Verify API contract for [endpoint] with expected status codes
- [ ] T025 [P] [US3] Verify user journey for [flow] including role restrictions

### Implementation for User Story 3

- [ ] T026 [P] [US3] Update model/entity logic in models/[entity].php
- [ ] T027 [US3] Implement API/model behavior in api/[endpoint].php or models/[model].php
- [ ] T028 [US3] Update view/client behavior in views/[view].php or assets/js/[script].js

**Checkpoint**: All user stories should now be independently functional

---

[Add more user story phases as needed, following the same pattern]

---

## Phase N: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] TXXX [P] Documentation updates in docs/
- [ ] TXXX Code cleanup and refactoring
- [ ] TXXX Performance optimization across all stories
- [ ] TXXX [P] Additional unit tests (if requested) in tests/unit/
- [ ] TXXX Security hardening
- [ ] TXXX Run quickstart.md validation

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phase 3+)**: All depend on Foundational phase completion
  - User stories can then proceed in parallel (if staffed)
  - Or sequentially in priority order (P1 → P2 → P3)
- **Polish (Final Phase)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational (Phase 2) - No dependencies on other stories
- **User Story 2 (P2)**: Can start after Foundational (Phase 2) - May integrate with US1 but should be independently testable
- **User Story 3 (P3)**: Can start after Foundational (Phase 2) - May integrate with US1/US2 but should be independently testable

### Within Each User Story

- Verification tasks MUST be defined before implementation
- Models before APIs when API behavior depends on data logic
- APIs before views/client code when UI consumes changed contracts
- Core implementation before integration
- Story complete before moving to next priority

### Parallel Opportunities

- All Setup tasks marked [P] can run in parallel
- All Foundational tasks marked [P] can run in parallel (within Phase 2)
- Once Foundational phase completes, all user stories can start in parallel (if team capacity allows)
- All verification tasks for a user story marked [P] can run in parallel
- Models within a story marked [P] can run in parallel
- Different user stories can be worked on in parallel by different team members

---

## Parallel Example: User Story 1

```bash
# Launch all verification tasks for User Story 1 together:
Task: "Verify API contract for [endpoint]"
Task: "Verify user journey for [flow] including role restrictions"

# Launch independent PHP file changes together:
Task: "Update model logic in models/[entity].php"
Task: "Update view behavior in views/[view].php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL - blocks all stories)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: Test User Story 1 independently
5. Deploy/demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add User Story 1 → Test independently → Deploy/Demo (MVP!)
3. Add User Story 2 → Test independently → Deploy/Demo
4. Add User Story 3 → Test independently → Deploy/Demo
5. Each story adds value without breaking previous stories

### Parallel Team Strategy

With multiple developers:

1. Team completes Setup + Foundational together
2. Once Foundational is done:
   - Developer A: User Story 1
   - Developer B: User Story 2
   - Developer C: User Story 3
3. Stories complete and integrate independently

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Define safe verification before implementing
- Mark database-mutating checks explicitly and include restore/rollback steps
- Run `php -l` for touched PHP files or the whole project before completion
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Avoid: vague tasks, same file conflicts, cross-story dependencies that break independence
