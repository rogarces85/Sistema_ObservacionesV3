# Contract: Action Behavior

This contract defines the expected behavior for user-facing actions covered by the Button Action Completion feature.

## Global Action Rules

| Rule | Required Behavior | Evidence |
|---|---|---|
| Intentional outcome | Every visible control performs an action, navigates, opens a dialog, downloads/queues output, or is disabled with explanation. | Page action inventory and manual walkthrough. |
| Role safety | Role-specific actions remain unavailable to unauthorized roles and server authorization remains authoritative. | Registrador/supervisor walkthrough plus forbidden-action checks. |
| Feedback | Mutating, queued, uploaded, exported, or validation-dependent actions show success/error/progress/confirmation feedback. | Manual action result evidence. |
| Confirmation | Destructive, irreversible, or high-impact actions require explicit confirmation before execution. | Confirmation dialog evidence. |
| Recoverability | Validation or communication errors leave the user able to retry, correct input, or cancel. | Error-path walkthrough. |
| Visual consistency | Modified controls, dialogs, disabled states, and messages remain aligned with the official visual standard. | Visual review in light and dark themes where touched. |

## Screen Action Coverage

| Screen | Required Action Groups | Acceptance Evidence |
|---|---|---|
| Dashboard | New observation entry, observations navigation, supervision/report navigation, report modal, template download. | Each visible action reaches expected destination or opens expected modal/download. |
| Observaciones | Create, first create, edit, detail, move to trash, import, preview, confirm import, template download. | Registrador walkthrough with controlled records/file. |
| Supervisión | Filters, dependent establishment loading, select all, mass approve/cancel/delete, individual detail/approve/cancel/delete. | Supervisor walkthrough with selection counter and confirmation evidence. |
| Reportes | Filters, clear, tabs, export, detailed PDF, queue Excel/PDF, refresh queue, download ready report. | Report result evidence for visible period and active category. |
| Usuarios | Create, edit, active toggle, reset password, audit view, delete. | Supervisor walkthrough with password policy consistency and audit modal evidence. |
| Asignaciones | Year change, copy prior year, select registrador, assign/reassign, period/month selection, save, remove, temporary list, remove temporary. | Supervisor walkthrough with annual and temporary control visibility. |
| Eliminadas | Filters, select all, restore, permanent delete, irreversible confirmation. | Restore path and confirmation-only permanent delete evidence unless approved test data exists. |
| Establecimientos | Create, edit, active toggle, duplicate/required validation. | Supervisor walkthrough with validation and refreshed stats/list. |
| Perfil | Change password. | Password policy validation and success/error feedback evidence. |
| Versionado | Create snapshot, rollback warning. | Snapshot creation evidence and rollback confirmation-only evidence unless explicitly approved. |

## Verification Matrix Fields

Each implementation task should record these fields for affected controls:

- Screen
- Role tested
- Control label
- Preconditions
- Expected outcome
- Actual outcome
- Feedback shown
- Data mutation risk
- Verification result: pass, pass with note, blocked, or skipped with reason

## Non-Goals

- No new REM states, roles, assignment meanings, or report definitions.
- No replacement of the existing application architecture.
- No irreversible production-data operation during normal verification.
