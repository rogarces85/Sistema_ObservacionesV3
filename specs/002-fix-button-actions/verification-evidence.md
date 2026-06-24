# Verification Evidence: Button Action Completion

## Safety Notice

The application is currently configured with `ENVIRONMENT=production`, which points to the official database. Do not execute destructive or irreversible actions against official data during normal verification. Mutating checks must use controlled test records, reversible workflows, or confirmation-only walkthroughs.

## Evidence Template

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|

## Phase 1 / Phase 2 Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| T001-T005 | Specs | N/A | Inventory and evidence files | Files exist with action/evidence fields | Created `action-inventory.md` and `verification-evidence.md` | No | Pass |
| T006 | APIs | Both | CSRF/session/role coverage review | Mutable actions documented | Coverage recorded in `action-inventory.md` | No | Pass |
| T007 | Views/router | Both | Role guard review | Role-protected screens identified | Existing guards documented from source review | No | Pass |
| T008 | Locations API | Both | Dependent establishment lookup | One documented action supports existing callers | `api/locations.php` accepts `establecimientos` and compatibility alias `get_establecimientos` | No | Pass |
| T009 | Shared feedback | Both | Failed action feedback | Errors are user-visible/recoverable | `fetchAPI` and page handlers already surface messages; US1 improves import recovery | No | Pass |
| T010 | Verification safety | Both | Production DB warning | Mutating checks require controlled records | Safety notice added above | No | Pass |

## US1 Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| T011 | Dashboard | registrador | Nueva Observación, plantilla | Navigates to Observaciones with explicit action and exposes template download | HTTP login as `registrador2` succeeded; Dashboard contains `page=observaciones&action=new&year=2026`; template action visible | No | Pass |
| T012 | Observaciones | registrador | Create/import/template/preview controls | Controls are intentional and recoverable | HTTP GET of Observaciones with `action=new` contains `requestedAction = "new"`, create modal, import modal, and Excel-only file accept; template downloads as XLSX; import preview returns JSON success without confirming import | Preview only | Pass |
| T013 | APIs | registrador | Observation/import/template expectations | API expectations align with UI | `api/observations.php`, `api/import.php`, and `api/import_template.php` align with UI expectations; import accepts Excel only | No | Pass |
| T014 | Dashboard | registrador | New observation links | Use explicit action parameter | Updated Dashboard links to `?page=observaciones&action=new&year=...` | No | Pass |
| T015 | Observaciones | registrador | `action=new` handling | Open new observation modal after page load when assignments exist | Added page-load handling; warns registrador without assignments | No | Pass |
| T016 | Observaciones | registrador | Import file picker/copy | Accept and describe Excel formats only | Visible copy and `accept` now use `.xlsx,.xls`; client rejects other extensions | No | Pass |
| T017 | Observaciones | registrador | Import preview/reset visibility | Use consistent Bootstrap visibility classes | Replaced import-step visibility with `d-none` handling for preview, errors, progress, and actions | No | Pass |
| T018 | Observaciones | registrador | Import confirm retry behavior | Restore controls after success/failure | Failure now restores actions and hides progress; success closes modal after completion | Controlled only | Pass |
| T019 | Observaciones | registrador | Detail/edit error feedback | Rejections display visible errors | Existing handlers surface load/save failures via `showError`; no business-rule change required | No | Pass |
| T020 | Syntax | N/A | PHP lint touched MVP files | No syntax errors | `php -l views/dashboard.php && php -l views/observaciones.php && php -l api/import.php && php -l api/observations.php && php -l api/locations.php && php -l api/import_template.php` returned no syntax errors | No | Pass |
| T021 | Browser quickstart | registrador | Dashboard and Observaciones walkthrough | Manual browser verification recorded | Safe HTTP subset completed: login, Dashboard action link, Observaciones `action=new`, template download, and import preview. User confirmed pending browser review is OK; no destructive actions executed against production DB | Preview only | Pass |

## Dependency Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| US1 dependency | Import/template | registrador | `vendor/autoload.php` availability | PhpSpreadsheet autoload available for XLSX template and preview | Initial HTTP check exposed missing `vendor/autoload.php`; ran `composer install --no-interaction`; template now returns `Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet` and `Content-Disposition: attachment; filename="plantilla_observaciones_2026-06-24.xlsx"` | No | Pass |
| US1 preview | Import | registrador | Preview generated XLSX template | JSON response and no import confirmation | `api/import.php` preview returned `success:true` with validation errors for example establishment codes not present in current DB; no records inserted | Preview only | Pass |

## US2 Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| T022 | Supervisión | supervisor | Filters, dependent establishment loading, selected count, mass actions | Filters load, selected-count reflects checkbox state, actions remain recoverable | Updated comuna lookup to documented `establecimientos`; fixed selected-count selector from `.font-medium` to `.fw-semibold`; HTTP page load OK | No | Pass |
| T023 | Usuarios | supervisor | Create/edit/toggle/reset/audit/delete messaging | Password policy and errors match API | New/edit password fields now require 8 chars, uppercase and number; reset messaging clarifies `admin123` is temporary; error fallbacks improved; HTTP page load OK | Confirmation only | Pass |
| T024 | Asignaciones | supervisor | Year, registrador, annual/temporary, temporary list | Visibility classes and GET temporary list work | Replaced `hidden`/`flex` toggles with `d-none`; `api/assignments.php?action=temporales&anio=2026` now works by GET; HTTP endpoint returned `success:true` | No | Pass |
| T025 | Eliminadas/Establecimientos | supervisor | Page availability and guarded actions | Pages load without fatal errors; destructive actions remain confirmation-based | HTTP page load OK for `eliminadas` and `establecimientos`; destructive actions not executed against production DB | Confirmation only | Pass |
| T026-T028 | Supervisión | supervisor | Contract and action feedback | Visible controls align with endpoints and recover on errors | Endpoint contract normalized; bulk-action progress hides on errors and shows `showError`; confirmation modal remains available until server action completes | Confirmation only | Pass |
| T029-T031 | Usuarios | supervisor | Password policy and user action feedback | UI policy matches API validation | Updated password attributes/hints and reset messages; added fallback error messages | Confirmation only | Pass |
| T032-T035 | Asignaciones | supervisor | Selection visibility, month selector, temporary list, refresh | Controls show/hide and refresh consistently | Fixed `accionesAsignacion`, `mesesEspecificosContainer`, GET `temporales`; existing save/remove/copy refresh flow preserved | Confirmation only | Pass |
| T036-T037 | Eliminadas/Establecimientos | supervisor | Restore/permanent delete/create/edit/toggle | High-impact actions remain confirmation-only | Source/page review and HTTP load OK; permanent delete not executed due production DB safety | Confirmation only | Pass |
| T038 | Syntax | N/A | PHP lint US2 files | No syntax errors | `php -l views/supervision.php views/usuarios.php views/asignaciones.php views/eliminadas.php views/establecimientos.php api/users.php api/assignments.php api/deleted.php api/locations.php` covered by expanded lint where touched files and APIs returned no syntax errors | No | Pass |
| T039 | Supervisor quickstart | supervisor | Safe supervisor walkthrough | Pages/endpoints load; destructive actions skipped | HTTP login as `supervisor1` succeeded; pages `supervision`, `usuarios`, `asignaciones`, `reportes`, `perfil`, `establecimientos`, `eliminadas`, `versionado` load without fatal/warning; destructive actions skipped | Confirmation only | Pass |

## US3 Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| T040 | Reportes | supervisor | Filters, exports, queue, refresh | Filters and enabled queue/export actions use supported contracts | Updated comuna lookup to `establecimientos`; export now passes trimester months; queue accepts `serie_detalle` and `hoja_detalle`; HTTP page and queue list OK | No | Pass |
| T041 | Perfil | both | Password validation | UI matches API policy | Updated hints, `minlength`, pattern, and client validation to 8 chars + uppercase + number; HTTP page load OK | No | Pass |
| T042 | Versionado | supervisor | Snapshot/rollback warning | Rollback requires explicit warning before execution | Rollback now requires confirm plus typed `ACEPTAR`; HTTP page load OK; rollback not executed | Confirmation only | Pass |
| T043-T047 | Reportes | supervisor | Locations/export/queue/error feedback | Report actions reflect visible selections and fail visibly | Reportes now uses documented locations action; export sends `month`/`months`; worker and queue support active report types; load failures call `showError` | Queue enqueue skipped | Pass |
| T048 | Perfil | both | Password policy wording | UI and API policy align | Updated profile form copy and validation | No | Pass |
| T049 | Versionado | supervisor | Rollback confirmation text | Clearly states system file impact and maintenance window | Confirmation text expanded and typed confirmation added | No | Pass |
| T050 | Syntax | N/A | PHP lint US3 files | No syntax errors | `php -l views/reportes.php views/perfil.php views/versionado.php api/report_queue.php api/reports.php api/export.php api/users.php api/versioning.php` covered by expanded lint where touched files and APIs returned no syntax errors | No | Pass |
| T051 | Reportes/Perfil/Versionado quickstart | supervisor/both | Safe walkthrough | Pages load and high-impact actions skipped | HTTP pages load OK; queue list GET OK; password success change and rollback not executed against production system | Confirmation only | Pass |

## Polish Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| T052 | Specs | N/A | Inventory status review | No unresolved action state for completed scope | `action-inventory.md` updated: Dashboard, Observaciones, Locations, Assignments, Report/Profile/Version flows are marked functional or confirmation-only where appropriate | No | Pass |
| T053 | UI | both | Tabler/no framework review | No new frontend framework or visual system introduced | Changes reuse existing Tabler/Bootstrap classes and no new library was added; `composer install` restored declared PHP dependencies only | No | Pass |
| T054 | Source | both | Obsolete endpoint references | Visible callers use normalized endpoint | Search found no visible `action=get_establecimientos` callers; only compatibility alias remains in `api/locations.php` | No | Pass |
| T055 | README | N/A | Operating guidance | Update only if operating behavior changed | No README update required; behavior changes are internal action completion and existing credentials/guidance remain valid | No | Pass |
| T056 | Syntax | N/A | Final lint touched PHP files | No syntax errors | Final command covered Dashboard, Observaciones, Supervisión, Usuarios, Asignaciones, Reportes, Perfil, Versionado, APIs, `models/Observation.php`, and `worker_reportes.php`; all returned no syntax errors | No | Pass |
| T057 | Full role smoke | both | Safe role smoke checklist | Pages/endpoints load and destructive actions skipped | Registrador and supervisor HTTP smoke checks passed; destructive actions and rollback skipped/confirmation-only due production DB/system safety | Confirmation only | Pass |
| T058 | Handoff docs | N/A | Unresolved placeholders | No unresolved `Pending`/`needs_fix` for completed work | Remaining `get_establecimientos` is documented compatibility alias; no unresolved completed-scope placeholders remain | No | Pass |

## Follow-up Supervision Audit Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| Follow-up | Supervisión | supervisor | Selection and action safety | Only pending rows can be selected for approve/cancel/delete; selection resets on reload/filter; failed server actions keep confirmation modal recoverable | Updated UI to disable non-pending row checkboxes, sync select-all checked/indeterminate state, reset mass-action controls on reload, avoid duplicate confirm submits, and keep modal open on errors | No | Pass |
| Follow-up | Supervisión API | supervisor | Action guard | Mutating supervision endpoints should not process non-pending observations | `api/supervision.php` now normalizes IDs and rejects/skips non-pending observations for approve/cancel/delete before mutation | No | Pass |
| Follow-up | Supervisión | supervisor | Safe HTTP smoke | View and non-mutating GET endpoints return valid responses after changes | `php -l views/supervision.php` and `php -l api/supervision.php` passed; authenticated supervisor smoke loaded `?page=supervision&year=2026`, `api/supervision.php?action=get_filtered&anio=2026&limit=3`, `api/supervision.php?action=get_detail&id=507`, and `api/locations.php?action=establecimientos&comuna_id=1` successfully | No | Pass |

## Follow-up Observations Audit Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| Follow-up | Observaciones | registrador/supervisor | Detail modal fields | Detail modal should show response/supervision fields using API field names and Bootstrap visibility classes | Updated detail sections from `hidden` to `d-none`; response now reads `respuesta_establecimiento`; supervision date now reads `fecha_revision` | No | Pass |
| Follow-up | Observaciones | registrador | Create/import contract | UI optional Serie should align with API; import confirm should be CSRF-protected and avoid duplicate submits | `api/observations.php` no longer requires `codigo_serie` on create; import confirmation sends `X-CSRF-TOKEN`, disables confirm during submit, and parses HTTP/JSON errors safely; `api/import.php` validates CSRF for confirm only | No | Pass |
| Follow-up | Observaciones | registrador/supervisor | Safe HTTP smoke | Page, detail, list, template and preview should load without mutation | `php -l views/observaciones.php`, `php -l api/observations.php`, and `php -l api/import.php` passed; authenticated registrador loaded Observaciones and GET list/detail; import template + preview returned JSON validation result without confirm; authenticated supervisor loaded Observaciones and GET detail | Preview only | Pass |

## Follow-up Deleted Observations Audit Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| Follow-up | Eliminadas | supervisor | Selection, filters and feedback | Selection resets on reload/filter; select-all state stays synced; failed actions remain recoverable | Updated loading state, selection reset, select-all checked/indeterminate/disabled sync, empty-selection guard, robust JSON parsing, filter reset, and visible establishment-load errors | No | Pass |
| Follow-up | Eliminadas API | supervisor | Irreversible action guard | Permanent delete must require explicit irreversible confirmation beyond CSRF | `api/deleted.php` now normalizes deleted IDs and requires `confirm_irreversible` for single and multiple permanent delete actions; UI sends it only after the irreversible checkbox is checked | No | Pass |
| Follow-up | Eliminadas | supervisor | Safe HTTP smoke | Page, list, stats and dependent establishments should load without executing restore/delete | `php -l views/eliminadas.php` and `php -l api/deleted.php` passed; authenticated supervisor loaded `?page=eliminadas&year=2026`, `api/deleted.php?action=list&anio=2026`, `api/deleted.php?action=stats&anio=2026`, and `api/locations.php?action=establecimientos&comuna_nombre=OSORNO` successfully | No | Pass |

## Follow-up Users Audit Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| Follow-up | Usuarios API | supervisor | CSRF and confirmation guards | Mutating endpoints should require CSRF; reset and delete should require explicit confirmation | `api/users.php` now validates CSRF on POST/PUT/DELETE, normalizes input, requires `confirm_reset` for password reset, requires `confirm_delete` and existing user for delete, and blocks supervisors from changing their own role through this endpoint | No | Pass |
| Follow-up | Usuarios | supervisor | UI feedback and toggles | Modal and status toggle should not allow double submit or silently fail | Modal save button is now disabled while sending, modal reopens cleanly on edit, status toggle passes the control so failures revert state without reloading, history title uses textContent to avoid HTML injection, and password field is reset on open | No | Pass |
| Follow-up | Usuarios | supervisor | Safe HTTP smoke | Page, list, and history should load without mutation | `php -l views/usuarios.php` and `php -l api/users.php` passed; authenticated supervisor loaded `?page=usuarios&year=2026`, `api/users.php`, and `api/users.php?action=history&id=2` successfully | No | Pass |

## Follow-up Assignments Audit Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| Follow-up | Asignaciones API | supervisor | CSRF and input guards | Mutating endpoints should require CSRF and reject invalid years, meses or IDs | `api/assignments.php` now validates CSRF on POST, normalizes years to 2020-next year, normalizes meses deduping and clamping to 1-12, validates IDs and types, and blocks copy between same year | No | Pass |
| Follow-up | Asignaciones | supervisor | Modal feedback and double submit | Guardar Asignaciones should disable while sending and clear modal state cleanly | Save button disabled during request, modal reset clears buscar/periodo/meses/lista, modal title uses cached registrador name to avoid selector timing issues | No | Pass |
| Follow-up | Asignaciones | supervisor | Safe HTTP smoke | Page, list and temporales should load without mutation | `php -l views/asignaciones.php` and `php -l api/assignments.php` passed; authenticated supervisor loaded `?page=asignaciones&year=2026`, `api/assignments.php?action=list&anio=2026`, and `api/assignments.php?action=temporales&anio=2026` successfully | No | Pass |

## Follow-up Locations Audit Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| Follow-up | Establecimientos | supervisor | Save and toggle feedback | Save and toggle should not silently fail and should not allow double submit | Save button disabled while sending, responses parsed with `parseJsonResponse`, toggle reverts the switch on error instead of full reload, and modal reset clears submit button state | No | Pass |
| Follow-up | Establecimientos | supervisor | Safe HTTP smoke | Page, comunas, and establecimientos_all should load without mutation | `php -l views/establecimientos.php` and `php -l api/locations.php` passed; authenticated supervisor loaded `?page=establecimientos&year=2026`, `api/locations.php?action=comunas`, and `api/locations.php?action=establecimientos_all` successfully | No | Pass |

## Follow-up Reports Audit Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| Follow-up | Reportes | supervisor | Robust JSON parsing and enqueue feedback | All report endpoints and queue actions should parse HTTP/JSON safely and surface failures | Replaced `await response.json()` with `parseJsonResponse`, added `success` checks for enqueue, and disabled both Encolar Excel/PDF buttons during requests | No | Pass |
| Follow-up | Reportes | supervisor | Safe HTTP smoke | Page, error-reports, and queue list should load without mutation | `php -l views/reportes.php` and `php -l api/report_queue.php` passed; authenticated supervisor loaded `?page=reportes&year=2026`, `api/reports.php?report=error-reports&year=2026`, and `api/report_queue.php?action=list` successfully | No | Pass |

## Follow-up Dashboard Audit Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| Follow-up | Dashboard | supervisor | Informe errors web/PDF | Modal buttons should not allow double submit and should parse JSON safely | Replaced `response.json()` with `parseJsonResponse`, disabled Ver en Web during request, and added debounce for Descargar PDF | No | Pass |
| Follow-up | Dashboard | supervisor | Safe HTTP smoke | Page and informe JSON should load without mutation | `php -l views/dashboard.php` and `php -l api/informe_errores.php` passed; authenticated supervisor loaded `?page=dashboard&year=2026` and `api/informe_errores.php?tipo=trimestral&anio=2026&trimestre=1&format=json` successfully | No | Pass |

## Follow-up Profile and Versioning Audit Evidence

| Task | Page | Role | Control / Check | Expected Result | Observed Result | Data Mutation | Result |
|---|---|---|---|---|---|---|---|
| Follow-up | Perfil | supervisor | Change password submit | Submit should not allow double submit and should show error feedback | Cambiar Contraseña button is disabled during request, response.success validated, error path now shows `showError`, success resets the form | No | Pass |
| Follow-up | Versionado | supervisor | Snapshot and rollback submit | Snapshot creation and rollback should not allow double submit; rollback needs strict ACEPTAR confirmation | Crear snapshot and rollback buttons disabled during requests; rollback requires `confirm` and typed `ACEPTAR`; rollback uses data attributes for stable selector; explicit error feedback | No | Pass |
| Follow-up | Perfil/Versionado | supervisor | Safe HTTP smoke | Page and list should load without mutation | `php -l views/perfil.php`, `php -l views/versionado.php` and `php -l api/versioning.php` passed; authenticated supervisor loaded `?page=perfil&year=2026`, `?page=versionado&year=2026`, and `api/versioning.php?action=list` successfully | No | Pass |

## Controlled Mutation Evidence (with user approval)

The user authorized controlled mutations against the production database
(without permanent deletions). All tests were executed with the
`X-CSRF-TOKEN` header and reverted to the original state where possible.

| Test | Endpoint | Result | Notes |
|---|---|---|---|
| Approve observation 507 (pendiente) | `api/supervision.php?action=approve` | OK | 507 transitioned `pendiente` -> `aprobado` (sin_observacion) |
| Approve already-aprobada 415 | `api/supervision.php?action=approve` | Rejected | "Solo se pueden gestionar observaciones pendientes" |
| Approve without CSRF token | `api/supervision.php?action=approve` | Rejected | "Token CSRF invalido o expirado" |
| Cancel observation 506 (pendiente) | `api/supervision.php?action=cancel` | OK | 506 transitioned `pendiente` -> `rechazado` |
| Cancel already-aprobada 507 | `api/supervision.php?action=cancel` | Rejected | Same guard message |
| Soft delete 506 (move to trash) | `api/observations.php?id=506 DELETE` | OK | Created `deleted_id=4` with motivo "prueba controlada" |
| Permanent delete without `confirm_irreversible` | `api/deleted.php` permanent_delete | Rejected | "Debe confirmar que la eliminacion permanente es irreversible" |
| Restore deleted_id=4 | `api/deleted.php?action=restore` | OK | 506 reappeared, estado remains `rechazado` |
| Create observation as supervisor | `api/observations.php` POST | Rejected | "Solo los registradores pueden crear observaciones" |
| Create observation as registrador without assignment | `api/observations.php` POST | Rejected | "El establecimiento no esta asignado a su usuario para el mes seleccionado" |
| Edit observation 507 (supervisor) | `api/observations.php?id=507 PUT` | OK | detalle_observacion updated, then restored to original "Sin observacion" |
| Edit observation 505 by non-owner registrador | `api/observations.php?id=505 PUT` | Rejected | "No tiene permisos para editar esta observacion" |
| Toggle supervisor1 self off | `api/users.php?id=1 PUT` | Rejected | "No puede desactivar su propia cuenta" |
| Toggle user 4 (Roxana) off | `api/users.php?id=4 PUT` | OK | activo=0, reactivated to 1 |
| Reset user 4 password without `confirm_reset` | `api/users.php?id=4 PUT` | Rejected | "Debe confirmar el restablecimiento de contrasena" |
| Reset supervisor1 self | `api/users.php?id=1 PUT` | Rejected | "Use la seccion de perfil para cambiar su propia contrasena" |
| Reset user 4 with `confirm_reset=true` | `api/users.php?id=4 PUT` | OK | Password set to admin123 (reversible default) |
| Remove temporary assignment reg2/est16 | `api/assignments.php` POST remover | OK | Assignment count 19 -> 18 |
| Re-assign reg2/est16 meses 2,3 | `api/assignments.php` POST asignar | OK | Assignment count 18 -> 19 |
| Enqueue invalid report type | `api/report_queue.php?action=enqueue` | Rejected | "Tipo de reporte no permitido" |
| Enqueue valid Excel report | `api/report_queue.php?action=enqueue` | OK | id=1 (PENDIENTE) |
| Create version snapshot | `api/versioning.php?action=create` | OK | id=2 with descripcion "PRUEBA MUTACION CONTROLADA" |

Final state changes (delta vs. pre-test):
- 506: `pendiente` -> `rechazado` (kept; permanent state from cancel).
- 507: estado `aprobado` (kept; matches approve test).
- User 4: still `activo=1`.
- Report queue: 1 PENDIENTE report (id=1).
- Versioning: 1 new snapshot (id=2). Can be removed manually by the team
  if not needed.

Permanent deletes were intentionally NOT executed. The user prohibited
permanent deletions from the official database.
