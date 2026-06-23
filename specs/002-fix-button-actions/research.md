# Research: Button Action Completion

## Decision 1: Treat each visible action as an auditable UI contract

**Decision**: Each button, link, dropdown action, switch, file picker, and submit control must be classified as functional, intentionally disabled with explanation, or removed when not applicable.

**Rationale**: The user's problem is not a single endpoint failure; it is inconsistent behavior across pages. A per-action contract creates a complete inventory and prevents silent no-op controls.

**Alternatives considered**: Fix only the known broken examples first. Rejected because it would leave undiscovered controls broken and would not satisfy the acceptance criterion that every visible action has an intentional outcome.

## Decision 2: Preserve existing REM rules and backend boundaries

**Decision**: Correct UI/API alignment without changing REM roles, states, assignment semantics, report meanings, or permission boundaries unless a separate spec is created.

**Rationale**: This feature is about action functionality, not business-rule redesign. The constitution requires role and data integrity to remain authoritative at backend boundaries.

**Alternatives considered**: Normalize all workflows while fixing buttons. Rejected because it risks changing official REM behavior and expands scope beyond action completion.

## Decision 3: Use existing monolith surfaces for corrections

**Decision**: Keep changes in the existing views, shared JavaScript helpers, endpoints, and models where those surfaces already own behavior.

**Rationale**: The application is a pragmatic PHP monolith, and the action issues are localized mismatches between visible controls, page scripts, and existing endpoints.

**Alternatives considered**: Create a new client-side action framework. Rejected because it adds complexity, risks CSS/JS coupling, and violates the simple monolith principle.

## Decision 4: Keep Tabler as the only visual interaction standard

**Decision**: Any changed dialogs, disabled states, buttons, validation hints, and feedback messages must continue using Tabler-compatible patterns and project tokens.

**Rationale**: Tabler is now the official visual standard. This feature touches many controls and must avoid reintroducing legacy visual drift.

**Alternatives considered**: Use ad hoc styles to patch broken controls quickly. Rejected because new inline or legacy styles would expand known visual debt.

## Decision 5: Align visible password policy with actual policy

**Decision**: User-facing password creation/change messaging must state the same policy enforced by the system: at least 8 characters, one uppercase letter, and one number.

**Rationale**: Mismatched policy is a direct user-facing action failure. Users need to know why a submitted password is rejected.

**Alternatives considered**: Lower backend password policy to match existing UI hints. Rejected because it would weaken security and change behavior beyond this feature.

## Decision 6: Validate destructive actions through controlled or confirmation-only checks

**Decision**: Permanent delete and rollback are not to be executed against official data during normal verification; they are validated by confirmation flow, disabled/guarded behavior, or controlled test data only.

**Rationale**: The environment is connected to the official database. Verification must prove the UI behavior without risking irreversible data loss or file rollback.

**Alternatives considered**: Run all actions end-to-end in production-like configuration. Rejected because it violates safe verification expectations.

## Decision 7: Treat report period consistency as a user-visible contract

**Decision**: Report actions must reflect the period visible to the user, including trimester selections, for screen, export, and queue workflows.

**Rationale**: Users expect the exported or queued result to match the selected filters. Inconsistent period handling is an action failure even when the button responds.

**Alternatives considered**: Leave exports/queue using only month/year while chart filters use trimester. Rejected because it creates mismatched outputs and undermines report trust.

## Decision 8: Action feedback must be explicit and recoverable

**Decision**: Every action that mutates state, requests output, queues work, or depends on user input must produce success, validation, warning, or error feedback and leave the user able to retry or cancel.

**Rationale**: The system currently has cases where action failures can appear as no-ops. Explicit feedback is required for operators to diagnose and recover.

**Alternatives considered**: Rely on browser console errors or network failures. Rejected because end users cannot reasonably diagnose system state that way.
