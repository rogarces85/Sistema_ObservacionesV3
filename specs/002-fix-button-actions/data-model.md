# Data Model: Button Action Completion

This feature primarily models user-facing action behavior and existing REM entities. It does not add database tables or change persisted schema.

## Entity: Action Control

**Represents**: A visible interactive control that initiates a user task.

**Fields**:

- `screen`: dashboard, observaciones, supervision, reportes, usuarios, asignaciones, eliminadas, establecimientos, perfil, or versionado.
- `label`: user-visible text or accessible label.
- `control_type`: button, link, dropdown item, switch, file picker, form submit, tab, or download.
- `role_visibility`: registrador, supervisor, or both.
- `required_context`: selected records, selected registrador, assignment availability, selected file, selected filters, or none.
- `expected_outcome`: navigate, open dialog, submit data, download, queue work, toggle state, show warning, or no-op-disabled.
- `feedback_required`: success, error, validation, confirmation, progress, disabled explanation, or none for pure navigation.
- `destructive_level`: none, reversible, high-impact, or irreversible.

**Validation Rules**:

- Every visible action must have exactly one expected outcome.
- Disabled actions must expose why they are unavailable.
- Destructive and irreversible actions require confirmation.
- Actions visible to one role must not bypass backend authorization.

## Entity: Observation

**Represents**: A REM observation visible in listing, detail, supervision, import, reports, and deleted-record workflows.

**Fields**:

- `year`, `month`, `establishment`, `type`, `series`, `sheet`, `detail`, `delivery_deadline`, `uses_validator`, `response`, `status`, `owner`, `supervisor`, `history`.

**Relationships**:

- Belongs to one establishment and one recording user.
- May have supervision history.
- May be moved to Deleted Observation.

**State Transitions**:

- Pending observation can be edited by permitted registrador or supervisor.
- Supervisor actions can approve, classify as error, cancel, or move to deleted records according to existing rules.

## Entity: Import Batch

**Represents**: A file selected by a registrador for mass observation import.

**Fields**:

- `file_name`, `file_type`, `year`, `total_rows`, `valid_rows`, `error_rows`, `preview_rows`, `row_errors`, `confirmation_state`.

**Validation Rules**:

- Only accepted spreadsheet formats are valid.
- Preview must complete before confirmation.
- Invalid rows must be shown with row-level messages.
- Confirmation must not insert invalid rows.

## Entity: Supervision Selection

**Represents**: The current selected observation set for supervisor mass actions.

**Fields**:

- `selected_ids`, `selected_count`, `available_actions`, `confirmation_comment`, `approval_classification`, `resulting_status`.

**Validation Rules**:

- Mass action controls are disabled when selection is empty.
- Approval requires a resulting status choice.
- Selection count must match selected items.

## Entity: User Account

**Represents**: A system user administered by supervisors or updated by the current user through profile.

**Fields**:

- `username`, `full_name`, `role`, `active_state`, `created_date`, `password_policy_state`, `audit_history`.

**Validation Rules**:

- New and changed passwords must satisfy the real policy.
- Users cannot perform supervisor-only actions without supervisor role.
- Self-deactivation and self-deletion remain disallowed.

## Entity: Assignment

**Represents**: A registrador-establishment relationship for a year and optional month coverage.

**Fields**:

- `registrador`, `establishment`, `year`, `assignment_type`, `months`, `contacts`, `annual_owner`, `temporary_owner`.

**Validation Rules**:

- Temporary assignment requires specific months.
- Annual assignment may cover the full year.
- Conflicting assignments must remain blocked by existing assignment rules.

## Entity: Report Request

**Represents**: A report screen/output/queue request initiated from visible filters.

**Fields**:

- `year`, `trimester`, `month`, `comuna`, `establishment`, `active_category`, `output_format`, `queue_status`, `download_state`.

**Validation Rules**:

- Output period must match visible filters.
- Unsupported report category/output combinations must not be silently sent.
- Empty results must show a clear message.

## Entity: Deleted Observation

**Represents**: A soft-deleted observation in the deleted-observations area.

**Fields**:

- `deleted_id`, `original_observation_id`, `deleted_date`, `deletion_reason`, `original_status`, `restore_state`, `permanent_delete_confirmation`.

**State Transitions**:

- Deleted observation can be restored.
- Deleted observation can be permanently deleted only with irreversible confirmation.

## Entity: Establishment

**Represents**: A health facility catalog item managed by supervisors.

**Fields**:

- `code`, `name`, `short_name`, `comuna`, `active_state`.

**Validation Rules**:

- Code, name, and comuna are required.
- Duplicate codes are rejected.
- Toggle state must update list and statistics.

## Entity: Version Snapshot

**Represents**: A named system snapshot available to supervisors.

**Fields**:

- `tag`, `description`, `author`, `created_date`, `rollback_confirmation`.

**Validation Rules**:

- Snapshot creation requires description.
- Rollback requires explicit warning and confirmation.
- Rollback must not be executed during normal verification unless explicitly approved.
