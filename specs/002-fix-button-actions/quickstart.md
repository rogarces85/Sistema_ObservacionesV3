# Quickstart: Button Action Completion Validation

## Purpose

Validate that visible page actions in the REM system are functional, intentional, and safe after implementation.

## Preconditions

- Use a browser session with access to the REM application.
- Use supervisor credentials for administrative screens.
- Use registrador credentials with assigned establishments for observation and import checks.
- Avoid irreversible actions on official data unless explicit approval and controlled test records are available.
- Keep browser developer tools open to detect JavaScript errors during walkthroughs.

## Suggested Accounts

- Supervisor: `supervisor1 / admin123`.
- Registrador: `registrador2 / admin123`.

## Safe Walkthrough Order

1. Login as registrador.
2. Open Dashboard and verify action navigation to Observaciones, report navigation, and template download availability.
3. Open Observaciones and verify create modal, detail modal, edit modal for permitted records, import modal, accepted file messaging, and preview behavior.
4. Logout and login as supervisor.
5. Open Supervisión and verify filters, comuna-to-establishment dependency, selection counter, and confirmation dialogs for approve/cancel/delete without executing destructive actions unless using controlled records.
6. Open Usuarios and verify create/edit modal validation, password policy messaging, audit modal, toggle confirmation behavior, reset password confirmation, and delete confirmation without deleting real accounts.
7. Open Asignaciones and verify year change, registrador selection, assign/reassign visibility, annual/temporary controls, month selection visibility, temporary list loading, and remove confirmations.
8. Open Reportes and verify filters, trimester behavior, tabs, exports, queue actions, refresh, and ready-download links if available.
9. Open Perfil and verify password validation behavior with invalid values; use a controlled account for successful password change if needed.
10. Open Establecimientos and verify create/edit/toggle modal behavior and validation without adding duplicate official data unless controlled.
11. Open Eliminadas and verify filters, selection, restore confirmation, and permanent-delete irreversible confirmation without permanent deletion unless approved.
12. Open Versionado and verify snapshot description prompt; verify rollback warning without executing rollback unless explicitly approved.

## Evidence To Capture

- Page and role.
- Control label.
- Preconditions.
- Expected result.
- Observed result.
- Feedback or confirmation shown.
- Whether data was mutated.
- Screenshot or brief note for any skipped irreversible action.

## Pass Criteria

- No visible action is an unexplained no-op.
- No browser console error appears during tested actions.
- Required confirmations appear before high-impact actions.
- Password policy messaging matches actual validation.
- Filters and dependent dropdowns load expected options.
- Import preview and report output reflect the visible user selections.
