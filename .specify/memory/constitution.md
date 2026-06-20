<!--
Sync Impact Report
Version change: template -> 1.0.0
Modified principles:
- PRINCIPLE_1_NAME -> I. README and Specs Are the Source of Truth
- PRINCIPLE_2_NAME -> II. Role-Based Access and REM Data Integrity
- PRINCIPLE_3_NAME -> III. Secure Configuration and Session Safety
- PRINCIPLE_4_NAME -> IV. Testable, Reversible Data Changes
- PRINCIPLE_5_NAME -> V. Simple Monolith, Observable Operations
Added sections:
- Technical Constraints
- Development Workflow
Removed sections:
- Placeholder-only SECTION_2_NAME and SECTION_3_NAME
Templates requiring updates:
- ✅ updated: .specify/templates/plan-template.md
- ✅ updated: .specify/templates/spec-template.md
- ✅ updated: .specify/templates/tasks-template.md
- ✅ reviewed: .specify/templates/checklist-template.md
- ✅ reviewed: .specify/templates/constitution-template.md
- ✅ reviewed: .specify/extensions/git/commands/*.md
Runtime guidance updates:
- ✅ updated: README.md
Follow-up TODOs:
- None
-->
# Sistema de Observaciones REM Constitution

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

**Version**: 1.0.0 | **Ratified**: 2026-06-20 | **Last Amended**: 2026-06-20
