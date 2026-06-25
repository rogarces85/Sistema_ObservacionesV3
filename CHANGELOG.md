# Sistema de Observaciones REM - Changelog

## [2.1.0] - 2026-06-24

### Operacion
- `deploy/` con 18 scripts y plantillas para produccion:
  provisionamiento, HTTPS, MySQL usuario, migraciones ordenadas,
  env file, Apache endurecido, fail2ban, worker systemd timer,
  backup/restore, healthcheck, primer supervisor, limpieza demo.
- `deploy/TRAINING.md`: plan de capacitacion pre-go-live.
- `deploy/CUTOVER.md`: checklist de corte del ambiente dev.

### Seguridad
- CSRF obligatorio en endpoints mutables de `users`, `assignments`,
  `deleted`, `observations`, `import` (confirm), `report_queue` y
  `versioning`.
- Confirmaciones explicitas para acciones irreversibles:
  `confirm_irreversible` en `api/deleted.php`,
  `confirm_delete` y `confirm_reset` en `api/users.php`,
  confirmacion tipeada "ACEPTAR" en `versioning rollback`.
- Validacion de anios, meses, IDs y tipos en backend.
- Guards contra auto-acciones de supervisor:
  no puede cambiarse el propio rol desde admin,
  no puede desactivarse / eliminarse / resetearse a si mismo.

### UX / feedback
- `parseJsonResponse` en todas las vistas para evitar falsos
  positivos en respuestas HTML/500.
- Bloqueo de doble submit en formularios, snapshots, rollbacks,
  encolar reportes e informes.
- Toggle de activo/inactivo revierte visualmente sin recargar.
- Modal de aprobacion no se cierra si la API falla.
- Reemplazo de clases `hidden` por `d-none` (Bootstrap 5).
- Modal de asignacion resetea correctamente radios y meses.
- Detecciones finales del detalle de observacion usan nombres
  reales de la API (`respuesta_establecimiento`, `fecha_revision`).

### Verificacion
- `php -l` en todos los archivos modificados.
- `composer install` documentado.
- Smoke HTTP autenticado como supervisor1 y registrador2
  en las 10 pantallas.
- Mutaciones controladas con CSRF y reversibles (sin borrado
  permanente) ejecutadas y registradas en
  `specs/002-fix-button-actions/verification-evidence.md`.

### Archivos modificados (18)
- `api/assignments.php`
- `api/deleted.php`
- `api/import.php`
- `api/observations.php`
- `api/supervision.php`
- `api/users.php`
- `views/asignaciones.php`
- `views/dashboard.php`
- `views/eliminadas.php`
- `views/establecimientos.php`
- `views/observaciones.php`
- `views/perfil.php`
- `views/reportes.php`
- `views/supervision.php`
- `views/usuarios.php`
- `views/versionado.php`
- `docs/prs/2026-06-24-audit-button-actions.md` (nuevo)
- `specs/002-fix-button-actions/verification-evidence.md`

## [2.0.0] - 2026-06-23

### Caracteristicas
- Sistema de gestion y registro de observaciones REM para
  el Servicio de Salud Osorno.
- Roles: registrador y supervisor.
- Gestion de observaciones, supervision, papelera, asignaciones,
  establecimientos, usuarios, reportes y versionado.
- Reportes sincronos (Excel/PDF) y asincronos con cola.
- Importacion Excel con preview y confirmacion.
- Snapshots de versionado con rollback protegido.
- Tabler 1.4.0 como shell visual.
