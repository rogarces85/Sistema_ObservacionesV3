# Action Inventory: Button Action Completion

## Status Legend

- `functional`: action has a verified intended outcome.
- `needs_fix`: action has a known mismatch or unclear behavior.
- `confirmation_only`: action is high-impact and should be verified without executing against official data.
- `documented_disabled`: action is intentionally unavailable until context exists.

## Shared Security Coverage

| Area | Path | Mutable Actions | CSRF / Session / Role Notes | Status |
|---|---|---|---|---|
| Observations | `api/observations.php` | Create, update, move to trash | Session required; POST/PUT/DELETE validate CSRF; registrador/supervisor role checks present. | functional |
| Import | `api/import.php` | Preview, confirm import | Session and registrador role required; upload accepts Excel only; multipart request currently relies on session/role and does not use JSON CSRF header. | functional |
| Import template | `api/import_template.php` | Download only | Session required; non-mutating download. | functional |
| Locations | `api/locations.php` | Create/update/toggle establishments | Session required; POST validates CSRF; supervisor required for mutable actions. | functional |
| Supervision | `api/supervision.php` | Approve, cancel, move to trash | Session and supervisor required; mutable actions validate CSRF. | functional |
| Users | `api/users.php` | Create, update, toggle, reset, delete, password change | Session required; role checks present; password policy enforced. | functional |
| Assignments | `api/assignments.php` | Assign, remove, copy | Session and supervisor required; temporary assignment list supports GET and mutable actions require authenticated supervisor. | functional |
| Deleted observations | `api/deleted.php` | Restore, permanent delete | Session and supervisor required; POST validates CSRF. | functional |
| Report queue | `api/report_queue.php` | Enqueue reports | Session required; enqueue validates CSRF. | functional |
| Versioning | `api/versioning.php` | Create snapshot, rollback | Session and supervisor required; POST validates CSRF. | confirmation_only |

## Dashboard (`views/dashboard.php`)

| Role | Control | Expected Outcome | Required Context | Risk | Status |
|---|---|---|---|---|---|
| registrador | Nueva Observación | Navigate to Observaciones with new-record action. | Registrador session; assignments required to create. | Reversible create flow. | functional |
| registrador | Mis Observaciones | Navigate to Observaciones list. | Registrador session. | None. | functional |
| supervisor | Supervisar | Navigate to Supervisión. | Supervisor session. | None. | functional |
| supervisor | Reportes | Navigate to Reportes. | Supervisor session. | None. | functional |
| supervisor | Informe | Open report modal; view web or PDF. | Supervisor session. | Non-mutating report output. | functional |
| both | Descargar Plantilla | Download Excel template. | Authenticated session. | Non-mutating download. | functional |
| both | Ver todas | Navigate to Observaciones list. | Authenticated session. | None. | functional |

## Observaciones (`views/observaciones.php`)

| Role | Control | Expected Outcome | Required Context | Risk | Status |
|---|---|---|---|---|---|
| registrador | Nueva Observación | Open clean creation modal. | Registrador with assignments. | Creates record only on save. | functional |
| registrador | Crear primera observación | Open clean creation modal from empty state. | Registrador with assignments. | Creates record only on save. | functional |
| registrador/supervisor | Ver detalle | Open complete detail modal and history. | Visible observation. | Non-mutating. | functional |
| permitted users | Editar | Load record into edit modal and save valid changes. | Own pending record or supervisor. | Mutates record on save. | functional |
| supervisor | Enviar a papelera | Prompt reason and move to deleted records. | Supervisor session. | Reversible soft delete. | confirmation_only |
| registrador | Importar | Open import modal. | Registrador with assignments. | Mutates only on confirm. | functional |
| registrador | Seleccionar Archivo Excel | Accept only Excel and request preview. | Selected `.xlsx` or `.xls`. | Preview is non-mutating. | functional |
| registrador | Confirmar Importación | Insert valid preview rows. | Valid preview and selected file. | Mutates observations. | functional |
| registrador | Descargar Plantilla Excel | Download template. | Authenticated session. | Non-mutating. | functional |

## Supervisor/Admin Screens

Detailed implementation is planned in US2. Initial inventory source files: `views/supervision.php`, `views/usuarios.php`, `views/asignaciones.php`, `views/eliminadas.php`, `views/establecimientos.php`.

## Report/Profile/Version Screens

Detailed implementation is planned in US3. Initial inventory source files: `views/reportes.php`, `views/perfil.php`, `views/versionado.php`.
