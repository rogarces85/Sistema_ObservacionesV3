# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Sistema de Observaciones REM — PHP monolith (no framework) that tracks REM (Resumen Estadístico Mensual) observations for health establishments served by Servicio de Salud Osorno, Chile. Two roles: `registrador` (creates/edits own observations for assigned establishments/months) and `supervisor` (reviews, approves, manages users/assignments/establishments, generates reports).

**The system is live in production with real data.** Anything touching the database needs a verified backup first — there is no migration tool and no automated test suite.

`docs/spec/` is the **authoritative baseline**, extracted from the running code and verified line by line:

- `docs/spec/openapi.yaml` — formal API contract (OpenAPI 3.1.0, validated). Covers all 15 `api/*.php` endpoints.
- `docs/spec/BASELINE.md` — purpose, data dictionary, critical flows, and the **17 golden rules** that must not be broken.
- `docs/spec/VERDAD-ACTUAL.md` — exact data model, 47 cited business rules, full authorization matrix.
- `docs/spec/AUDITORIA-CODIGO-MUERTO.md` — dead-code inventory with evidence and risk level.

`README.md` is the older, exhaustively detailed system doc generated via Spec Kit discovery; it remains useful for installation and history, but **where it disagrees with `docs/spec/`, `docs/spec/` is correct**. `.specify/memory/constitution.md` defines binding project rules (see below); `SECURITY.md`, `DEPLOY.md`, and `OPERATIONS.md` cover threat model, production deployment, and runbook respectively.

## Commands

```bash
composer install                 # install PHP deps (phpoffice/phpspreadsheet, tecnickcom/tcpdf)
composer install --no-dev --optimize-autoloader   # production install
```

There is no automated test suite (no PHPUnit). Verification is:

```powershell
# Syntax lint — run before every commit, required by SECURITY.md
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

```bash
php -l path/to/file.php          # lint a single file
```

`test_reasignacion_temporal.php`, `test_api_asignaciones.php`, and `verify_model.php` at the repo root are **manual scripts that mutate the configured database** — never run them against production or real data without a backup; they are not isolated/transactional tests.

Database schema is applied by running SQL files directly (no migration tool); see README.md "Instalacion Local Con XAMPP" for the exact file order across `config/*.sql`, `config/migrations/`, and `specs/*_migration.sql` — order must be validated against the target environment's actual schema state.

## Architecture

Classic PHP monolith, no router/framework, no dependency injection. Request flow:

- `index.php` is the sole page entry point: checks `$_SESSION['logged_in']`, reads `?page=`, validates against a hardcoded whitelist, applies inline per-role redirects (several pages require `ROL_SUPERVISOR`), then `include`s `views/{page}.php` wrapped by `includes/header.php`/`footer.php`.
- `api/*.php` — one file per resource, each a self-contained JSON endpoint (no shared bootstrap/router). Each file: checks session, reads `$_GET['action']` + `$_SERVER['REQUEST_METHOD']`, dispatches via `switch`/`if` chains to inline logic, defines its own local `jsonResponse()` helper. Responses always `{success, data, message}`. Frontend calls these via `fetch` from `assets/js/*.js` and inline view scripts.
- `models/*.php` — one class per domain entity (`Observation`, `User`, `Location`, `EstablecimientoAsignacion`, `DeletedObservation`, `Notification`, `ReportQueue`, `Exporter`, `UserAudit`), each embedding its own SQL via PDO prepared statements. No repository/query-builder abstraction.
- `models/Database.php` — singleton (`Database::getInstance()`) wrapping one shared PDO connection; `query()`/`queryOne()`/`execute()`/`lastInsertId()`/transactions. This is the highest fan-in node in the codebase — virtually every model call flows through it.
- `includes/csrf.php` (`CSRF` class) — session-stored token, validated via `X-CSRF-Token` header or `csrf_token` POST field. Every mutating API request must call `CSRF::validateRequest()`.
- `worker_reportes.php` — cron-driven async worker draining `reportes_pendientes` (report generation queue). The `$this->db`-outside-a-class bug that older docs describe **is fixed** (it uses `Database::getInstance()` at line 19) and `php -l` is clean. The real issue is different: the whole queue subsystem (`api/report_queue.php` + `models/ReportQueue.php` + this worker) has **no caller anywhere in the UI**, while `deploy/healthcheck.sh:74` still monitors the table. Connect it or retire it whole — never partially.
- Authorization is **not centralized**: role checks are duplicated across `index.php`, each `api/*.php` file, and sometimes model methods. When adding a page or endpoint, you must replicate the role guard yourself — there is no middleware to hook into.

### Config and secrets

`config/config.php` resolves DB credentials in priority order: `REM_ENV_FILE` env var (path to a PHP file returning a config array) → `config/.env.local.php` (gitignored, dev-only) → `REM_DB_*` env vars → hardcoded localhost defaults (only if `REM_ENVIRONMENT=development`). In production with nothing configured, it fails hard with a 500 rather than falling back to insecure defaults. Never add credentials or environment-specific secrets to tracked files — see `DEPLOY.md` for the `/etc/rem/env.php` production pattern.

### Frontend / theming (binding rules from constitution.md)

- UI framework is Tabler 1.4 only (`@tabler/core`, `@tabler/icons-webfont`). Do not introduce Tailwind, standalone Bootstrap, or any competing CSS framework.
- All visual tokens (color, shadow, radius, chart colors) live in `assets/css/tokens.css`; semantic/component overrides go in `assets/css/tabler-override.css`. `assets/css/styles.css` is deprecated — never add to it.
- Theme is `light`/`dark` via `data-bs-theme`, persisted in cookie `rem.theme` (not just localStorage); Chart.js instances must re-read tokens and refresh on the `rem:theme-changed` event.
- No inline `style="..."` on new or modified markup.
- Each `views/*.php` must stay independent and reuse the shared shell (`includes/header.php`, `sidebar.php`, `footer.php`, `breadcrumbs.php`) rather than duplicating layout/scripts.
- Frontend must not invent states/roles/labels — everything shown must map back to `config/constants.php` (states: `pendiente`, `aprobado`, `rechazado`, `error`, `justificado`; roles: `registrador`, `supervisor`; tipo_error: `S/OBSERVACION`, `ERROR`, `REVISAR`, `F/PLAZO`).

## Non-obvious business rules

These are easy to get wrong because they aren't enforced by DB constraints — they live only in PHP model logic (mainly `EstablecimientoAsignacion` and `Observation`):

- **Assignment months are dual-typed**: `observaciones.mes` stores a month *name* (e.g. `"Enero"`); `asignaciones_establecimientos.meses` stores a CSV of month numbers or the literal string `ALL` (meaning every month). `EstablecimientoAsignacion::tieneMes()` / `fusionarMeses()` bridge this.
- **Temporal assignments override annual ones**: `tieneAsignacionParaMes()` checks temporary (`tipo_asignacion` temporal) reassignments before annual ones, and a temporal assignment to another user *blocks* the original annual holder for that establishment/month. Overlapping temporals for the same establishment/year/month are rejected.
- **Deletion has two levels, and only one is destructive**: both `api/observations.php` DELETE and `api/supervision.php?action=delete` soft-delete into `observaciones_eliminadas` via `DeletedObservation::moveToTrash()` (recoverable trash). The **only** irreversible delete in the system is `api/deleted.php?action=permanent_delete` (and its `_multiple` variant), which requires `confirm_irreversible: true` in the body — that flag is the sole safeguard. Never bypass it.
- **Import validates monthly assignment** just like manual creation does (`api/import.php:183` calls `tieneAsignacionParaMes()`). Older docs describe this as an open gap; that gap is closed.
- **`codigo_hoja` (REM sheet) is required unless `tipo_error === 'S/OBSERVACION'`**. A registrador can only edit their **own** observations, but in **any** state — editing one that is not `pendiente` resets it to `pendiente` so it re-enters review (`api/observations.php:172-175`).
- **Approving requires choosing an outcome**: `api/supervision.php?action=approve` demands `estado_resultante` (`sin_observacion` → state `aprobado` + `tipo_error` `S/OBSERVACION`; `error` → state `error` + `tipo_error` `ERROR`). There is no direct reject: `?action=reject` returns 403 by design, and the `rechazado` state is reached via `?action=cancel`.

## Constitution highlights (`.specify/memory/constitution.md`)

Binding for any new work, beyond what's already covered above:
- README.md and the relevant `specs/`/`openspec/` artifact must stay in sync with actual behavior — if you change behavior, update the doc in the same change.
- Destructive/mutating changes need a stated verification plan (safe for non-prod data) and, where relevant, a rollback/backup path.
- Don't add new architectural layers, services, or frameworks without an explicit tradeoff write-up — this is intentionally a "simple monolith."
- Every mutating API call from the browser must go through CSRF (`includes/csrf.php`); don't rely on hidden UI controls as an authorization mechanism — the backend check is what counts.
