# Feature Specification: Button Action Completion

**Feature Branch**: `005-fix-button-actions`

**Created**: 2026-06-23

**Status**: Draft

**Input**: User description: "Implementar y corregir la funcionalidad de acciones por botón en el sistema REM, página por página, manteniendo Tabler como estándar visual oficial y sin cambiar reglas de negocio REM innecesariamente."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Registrar y revisar observaciones sin acciones rotas (Priority: P1)

Como registrador, necesito que las acciones de observaciones, importación y acceso desde el panel principal funcionen de forma predecible, para registrar, editar, consultar e importar observaciones REM sin depender de soporte técnico ni rutas alternativas.

**Why this priority**: La creación e importación de observaciones es el flujo operativo principal del sistema. Si los botones de nuevo registro, importación o detalle fallan, el sistema no permite cumplir el proceso base de registro REM.

**Independent Test**: Se puede probar iniciando sesión como registrador, entrando al panel principal y a Observaciones, ejecutando solo acciones no destructivas o con datos de prueba controlados: abrir nuevo registro, abrir importación, previsualizar archivo, ver detalle y editar una observación permitida.

**Acceptance Scenarios**:

1. **Given** un registrador autenticado con establecimientos asignados, **When** selecciona "Nueva Observación" desde el panel principal, **Then** llega al flujo de creación y el formulario de nuevo registro queda disponible sin pasos ambiguos.
2. **Given** un registrador en la página de Observaciones, **When** selecciona "Nueva Observación" o "Crear primera observación", **Then** se abre el formulario de creación con campos limpios, validación visible y opción de cancelar.
3. **Given** un registrador con permiso sobre una observación pendiente propia, **When** selecciona "Editar", **Then** el formulario muestra los datos actuales y permite guardar cambios válidos.
4. **Given** cualquier observación visible para el usuario, **When** selecciona "Ver detalle", **Then** se muestra una vista completa con información del registro, estado e historial disponible.
5. **Given** un registrador con un archivo de importación válido, **When** selecciona "Importar", elige el archivo y solicita vista previa, **Then** el sistema muestra resumen, errores por fila si existen y permite confirmar solo registros válidos.
6. **Given** un usuario que intenta importar un formato no permitido, **When** selecciona el archivo, **Then** recibe un mensaje claro y no se registra información parcial.

---

### User Story 2 - Gestionar acciones supervisoras críticas (Priority: P2)

Como supervisor, necesito que los botones de supervisión, usuarios, asignaciones, establecimientos y papelera funcionen de forma consistente, para aprobar, cancelar, asignar, restaurar, administrar cuentas y mantener catálogos sin acciones silenciosamente fallidas.

**Why this priority**: Los supervisores mantienen la integridad operativa del sistema. Fallos en selección masiva, filtros, asignaciones o administración de usuarios impiden revisar datos, asignar trabajo y corregir errores.

**Independent Test**: Se puede probar con sesión de supervisor recorriendo cada módulo administrativo, verificando apertura de modales, filtros dependientes, acciones masivas y mensajes de resultado. Las acciones destructivas deben probarse solo en datos de prueba o mediante simulación controlada.

**Acceptance Scenarios**:

1. **Given** un supervisor en Supervisión, **When** aplica filtros por estado, mes, comuna, establecimiento, registrador o búsqueda, **Then** la tabla se actualiza con resultados coherentes y permite limpiar los filtros.
2. **Given** un supervisor con observaciones seleccionadas, **When** usa acciones masivas de aprobar, cancelar o eliminar, **Then** el sistema solicita confirmación, exige los datos requeridos para aprobación y muestra resultado claro.
3. **Given** un supervisor en Usuarios, **When** crea, edita, activa, desactiva, restablece contraseña, consulta auditoría o elimina una cuenta permitida, **Then** la acción se completa o muestra un error accionable sin dejar la pantalla en estado inconsistente.
4. **Given** un supervisor en Asignaciones, **When** selecciona un registrador, cambia año, asigna, reasigna, remueve o copia asignaciones, **Then** la lista, los contactos y las reasignaciones temporales se actualizan correctamente.
5. **Given** un supervisor en Establecimientos, **When** crea, edita o cambia estado de un establecimiento, **Then** el sistema valida duplicados, muestra errores comprensibles y refresca estadísticas/listado.
6. **Given** un supervisor en Observaciones Eliminadas, **When** restaura o elimina permanentemente registros, **Then** las acciones requieren confirmación apropiada y actualizan conteos/listado.

---

### User Story 3 - Obtener reportes, perfil y operaciones auxiliares confiables (Priority: P3)

Como usuario autorizado, necesito que los botones de reportes, exportación, perfil y versionado respondan correctamente, para consultar información, generar entregables, cambiar mi contraseña y usar herramientas auxiliares sin errores de interfaz.

**Why this priority**: Estos flujos no siempre crean observaciones, pero son necesarios para análisis, administración y continuidad operativa. Deben quedar coherentes después de corregir los flujos principales.

**Independent Test**: Se puede probar usando filtros y acciones no destructivas: generar reportes en pantalla, solicitar descargas, encolar reportes, cambiar contraseña con una cuenta de prueba y validar confirmaciones de versionado sin ejecutar rollback real.

**Acceptance Scenarios**:

1. **Given** un usuario en Reportes, **When** aplica filtros, cambia pestañas, exporta o encola un reporte permitido, **Then** el resultado corresponde a los filtros visibles y el usuario recibe confirmación o descarga.
2. **Given** un usuario en Reportes con un trimestre seleccionado, **When** solicita una salida o cola de reporte, **Then** el periodo usado corresponde al trimestre completo y no solo a un mes vacío.
3. **Given** un usuario en Perfil, **When** intenta cambiar contraseña, **Then** la validación visible coincide con la política real y se informa cualquier error antes o después del envío.
4. **Given** un supervisor en Versionado, **When** crea un snapshot, **Then** se solicita descripción y se actualiza el listado; **When** intenta rollback, **Then** recibe advertencia explícita de impacto antes de continuar.

---

### Edge Cases

- Usuario sin permisos intenta acceder a una acción administrativa por navegación directa o acción oculta.
- Acción visible no aplica por falta de asignaciones, registros seleccionados o datos mínimos.
- Filtro dependiente no tiene valores disponibles para la selección anterior.
- Acción masiva se ejecuta con cero elementos seleccionados o con elementos que cambiaron de estado desde la carga de pantalla.
- Archivo de importación contiene filas válidas e inválidas mezcladas.
- Exportación o reporte no tiene datos para los filtros seleccionados.
- Contraseña no cumple la política visible o no coincide con la confirmación.
- Acción destructiva irreversible se intenta sin confirmación explícita.
- Sesión expirada durante una acción de guardado, importación, exportación o administración.
- Error de comunicación o validación deja un modal abierto: el usuario debe poder corregir, cancelar o reintentar.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The system MUST ensure every visible action control in the dashboard, observation, supervision, report, user, assignment, deleted-observation, establishment, profile, and versioning screens has an intentional outcome: perform action, navigate, open a form/dialog, download/export, or remain disabled with an explanation.
- **FR-002**: The system MUST allow users to start a new observation from the dashboard and reach the creation flow without ambiguity.
- **FR-003**: The system MUST allow registradores with valid assignments to create, edit permitted pending observations, view details, import valid observation files, preview import results, confirm valid imports, and download the import template.
- **FR-004**: The system MUST prevent unsupported import formats from being accepted and MUST explain the accepted file format before processing.
- **FR-005**: The system MUST show import preview sections, error sections, progress states, and confirmation controls consistently without hidden-state conflicts.
- **FR-006**: The system MUST allow supervisors to filter supervision records by all visible filters and load dependent establishment options from the selected comuna.
- **FR-007**: The system MUST keep selection counters and mass-action buttons synchronized with selected supervision records.
- **FR-008**: The system MUST require all mandatory approval information before a supervisor can approve one or more observations.
- **FR-009**: The system MUST allow supervisors to cancel and move observations to the deleted-observations area with confirmation and visible result feedback.
- **FR-010**: The system MUST allow report users to apply filters, clear filters, switch report categories, export allowed report outputs, queue allowed report outputs, refresh queued status, and download completed queued reports.
- **FR-011**: The system MUST apply trimester selections consistently wherever report output or queued report generation uses the visible period filter.
- **FR-012**: The system MUST only offer, send, or accept report queue types that are supported by the report queue workflow; unsupported categories MUST be disabled or made supported before use.
- **FR-013**: The system MUST align user creation and password-change validation messages with the real password policy: at least 8 characters, at least one uppercase letter, and at least one number.
- **FR-014**: The system MUST allow supervisors to create, edit, activate/deactivate, reset passwords, view audit history, and delete permitted users, with clear confirmation and error feedback.
- **FR-015**: The system MUST clearly communicate that password reset uses the current default reset password and MUST not present that password as valid for user-created passwords if it does not meet policy.
- **FR-016**: The system MUST allow supervisors to select registradores, change assignment year, assign/reassign establishments, choose annual or temporary periods, remove assignments, copy prior-year assignments, view active temporary reassigments, and remove temporary reassigments.
- **FR-017**: The system MUST ensure assignment controls and month selectors become visible when their selected assignment type requires them.
- **FR-018**: The system MUST allow supervisors to filter deleted observations, restore individual or multiple deleted observations, and permanently delete individual or multiple deleted observations only after irreversible-action confirmation.
- **FR-019**: The system MUST allow supervisors to create, edit, activate, and deactivate establishments while validating required fields and duplicate establishment codes.
- **FR-020**: The system MUST allow users to change their own password only when current password, new password, confirmation, and policy checks pass.
- **FR-021**: The system MUST allow supervisors to create version snapshots and MUST warn clearly before any rollback action continues.
- **FR-022**: The system MUST preserve role-based access rules: registradores cannot perform supervisor-only administration, and supervisors cannot bypass required confirmation for high-impact actions.
- **FR-023**: The system MUST preserve the current REM domain meanings for roles, observation states, months, establishment assignments, deleted records, reports, and password policy.
- **FR-024**: The system MUST preserve the official visual standard and existing accessibility expectations for action controls, dialogs, status messages, and disabled states.
- **FR-025**: The system MUST provide user-visible success and error feedback for every action that changes state, requests a file, queues work, or depends on user input.

### Key Entities *(include if feature involves data)*

- **Action Control**: A visible button, link, dropdown item, switch, file picker, or form submit control that initiates a user task.
- **Observation**: A REM observation record with year, month, establishment, type, status, details, owner, and history.
- **Import Batch**: A user-selected file and its preview results, including valid rows, invalid rows, and confirmation state.
- **Supervision Selection**: One or more observations selected for supervisor review actions.
- **User Account**: A system account with username, full name, role, status, password state, and audit history.
- **Assignment**: A relationship between a registrador, establishment, year, and annual or temporary month coverage.
- **Report Request**: A visible report selection with filters, period, output type, and optional queued status.
- **Deleted Observation**: A soft-deleted observation eligible for restore or permanent deletion.
- **Establishment**: A health facility catalog item with code, name, short name, comuna, and active state.
- **Version Snapshot**: A named operational snapshot available to supervisors for controlled version history.

### Roles & Permissions *(mandatory for user-facing/API changes)*

- **Affected roles**: registrador and supervisor.
- **Backend authorization rules**: Registradores may only create, edit, import, view, and report within their permitted observations and assignments. Supervisors may use administrative and review actions, but high-impact actions require confirmation and must remain server-authorized.
- **Data visibility rules**: All observation, report, assignment, deleted-record, and dashboard results remain scoped by year context, user role, establishment assignment, month selection, and existing report filters.

### Security & Data Integrity *(mandatory)*

- **Security considerations**: All action-changing requests must preserve existing session validation, request-forgery protection, role checks, upload restrictions, password policy checks, and confirmation for destructive or administrative actions.
- **Data integrity considerations**: Observation state transitions, import confirmation, assignment periods, soft delete, permanent delete, user status changes, establishment status changes, and report periods must preserve existing REM business meanings and auditability.
- **Audit/observability expectations**: User changes, password reset/change, supervision actions, deleted-observation actions, assignment changes, queued reports, and version snapshots must show user-visible result feedback and preserve existing audit/history/status evidence where already supported.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of visible action controls across the ten covered screens are classified as functional, intentionally disabled with explanation, or removed because they do not apply.
- **SC-002**: A registrador can complete the new-observation flow from dashboard entry to opened creation form in no more than 2 user actions.
- **SC-003**: A registrador can complete an import preview for a valid file and see the preview result in under 30 seconds for a typical operational file.
- **SC-004**: A supervisor can filter supervision records by comuna and establishment with no broken dependent dropdown behavior in 100% of tested attempts.
- **SC-005**: A supervisor can select and deselect records for mass action with the selected count and button states updating correctly in 100% of tested attempts.
- **SC-006**: User creation and profile password change reject non-compliant passwords before completion and display the same password policy in 100% of tested attempts.
- **SC-007**: Report exports and queued reports use the visible period/filter selections correctly for all supported report categories tested.
- **SC-008**: Assignment annual and temporary workflows display required controls and refresh assigned/temporary lists correctly in 100% of tested non-destructive or controlled data scenarios.
- **SC-009**: Destructive actions require explicit confirmation in 100% of tested delete, permanent delete, and rollback entry points.
- **SC-010**: Manual smoke testing as one registrador and one supervisor completes all non-destructive primary actions on each covered screen without JavaScript errors or ambiguous no-op clicks.

## Assumptions

- Existing role definitions, observation states, assignment rules, report categories, and password policy remain authoritative.
- The official visual standard is already accepted and this feature only corrects behavior and consistency of existing controls.
- Data-destructive actions will be verified with test records, reversible paths, or confirmation-only walkthroughs unless explicit approval is given.
- Existing session and request protection mechanisms remain in place and will be reused.
- If an action category is not currently supported by the background report workflow, the user experience should either disable it with explanation or bring support into alignment without changing report meaning.
- Password reset continues to use the current reset behavior unless a separate security feature changes that policy.

## Verification Plan *(mandatory)*

- **Safe checks**: Review each covered screen, validate action inventory, run syntax checks on modified server-rendered files, confirm no unsupported action labels remain, and perform non-mutating navigation/modal/filter/export-availability checks.
- **Data-mutating checks**: Required for create/update/import/assignment/supervision/delete/user/profile/establishment workflows. They must use test accounts or controlled records, document what data is changed, avoid irreversible production data deletion unless explicitly approved, and prefer restore/reset steps where available.
- **Acceptance evidence**: A page-by-page checklist showing each action control, tested role, expected result, observed result, and any skipped destructive action with reason; plus confirmation that registrador and supervisor smoke paths pass.
