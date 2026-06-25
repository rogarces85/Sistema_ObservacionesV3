# Plan de Capacitacion - Operacion del Sistema REM (B15)

Material de capacitacion para el equipo de soporte y usuarios clave
antes y durante el go-live.

## Objetivos

- Que el equipo de soporte pueda resolver incidentes nivel 1 y 2
  sin escalamiento externo.
- Que los usuarios clave (supervisores y registradores) operen
  correctamente el sistema desde el primer dia.
- Que exista un runbook de referencia rapida para incidentes
  comunes (vinculado a `OPERATIONS.md`).

## Audiencia y duracion

| Audiencia | Sesion | Duracion | Modalidad |
|---|---|---|---|
| Equipo de operaciones/soporte | Operacion del sistema | 3h | presencial |
| Supervisores | Gestion y supervision | 2h | presencial |
| Registradores | Captura de observaciones | 1.5h | presencial o virtual |
| Administrador de BD | Respaldo y restore | 1h | presencial |

## Sesion 1: Equipo de operaciones y soporte (3h)

### Modulo 1 - Arquitectura y stack (30 min)
- Monolito PHP + MySQL en Apache 2.4.
- Tabler 1.4.0 como shell visual.
- Composer, vendor/, .gitignore.
- Branch `main` y flujo de despliegue.
- Archivos criticos: `config/config.php`, `includes/csrf.php`,
  `includes/Mailer.php`, `worker_reportes.php`.

### Modulo 2 - Servicios y comandos clave (45 min)
- systemctl status apache2 / mysql / fail2ban.
- Logs: `/var/log/apache2/rem-*.log`, `/var/log/rem/*.log`.
- Acceso a BD: `mysql -u rem_app -p observaciones_rem`.
- Ver cola de reportes:
  `SELECT estado, COUNT(*) FROM reportes_pendientes GROUP BY estado;`
- Cron y timer:
  `systemctl list-timers | grep rem-worker`.

### Modulo 3 - Incidentes comunes (60 min, hands-on)
- Error de login: verificar sesion, CSRF, BD.
- Usuario no puede aprobar: ver estado, asignaciones, supervisor.
- Reportes no se generan: revisar `worker.log`, `reportes_pendientes`,
  reiniciar timer.
- Disco lleno: limpiar reportes LISTO antiguos, logs.
- HTTPS cae: `certbot renew`, reiniciar Apache.
- Login brute force: revisar fail2ban, bantime.

### Modulo 4 - Restore y rollback (45 min)
- Demo de `deploy/restore.sh` sobre una copia local.
- Demo de rollback via Versionado (UI) y via git.
- Practica: cambio de password de un usuario via supervisor.

## Sesion 2: Supervisores (2h)

### Bloque A - Gestion de usuarios y asignaciones (45 min)
- Crear usuarios reales con politica de 12+ chars.
- Reset de contrasena: recibir email vs copia manual.
- Asignaciones anuales y temporales; remover y re-asignar.
- Cambiar de registrador sin perder historial.

### Bloque B - Supervision de observaciones (45 min)
- Filtros utiles por mes, estado, comuna, registrador.
- Aprobar, cancelar y mover a papelera.
- Casos de uso: aprobacion masiva, evidencia de cambios.
- Papelera: restaurar vs eliminar permanente (con confirmacion).

### Bloque C - Reportes y dashboard (30 min)
- Encolar Excel/PDF y descarga desde la cola.
- Verificar que el worker procesa (estado LISTO).
- Informe trimestral/anual de errores.

## Sesion 3: Registradores (1.5h)

### Tema 1 - Captura de observaciones (40 min)
- Crear manualmente: mes, establecimiento, tipo, detalle.
- Cuando aplica S/OBSERVACION vs ERROR.
- Plazo dentro/fuera, uso de validador.

### Tema 2 - Importacion Excel (30 min)
- Descargar plantilla.
- Llenar y validar antes de cargar.
- Preview vs confirmacion; manejo de errores fila a fila.

### Tema 3 - Mi perfil y cambio de contrasena (20 min)
- Editar perfil, cambiar contrasena, ver actividad.

## Sesion 4: Administrador de BD (1h)

### Tema 1 - Respaldo y restore (30 min)
- Ubicacion: `/var/backups/rem/YYYY-MM-DD/`.
- Programacion: cron diario en `/etc/cron.daily/rem-backup`.
- Practica: ejecutar backup manual, simular restore, validar.
- Rotacion 30 dias.

### Tema 2 - Consultas de auditoria (30 min)
- `historial_estados`: cambios de estado de observaciones.
- `historial_usuarios`: acciones administrativas.
- `reportes_pendientes`: trazabilidad de cola.
- `versiones_sistema`: snapshots disponibles.

## Materiales de apoyo

- `OPERATIONS.md` (runbook) - lectura previa obligatoria.
- `DEPLOY.md` (arquitectura, configuracion) - referencia.
- `SECURITY.md` (modelo de amenazas) - lectura recomendada.
- `CHANGELOG.md` (cambios por version) - seguimiento de releases.

## Evaluacion (opcional)

Cuestionario corto de 10 preguntas al final de cada sesion,
enfocado en:
- Identificar el log correcto para un incidente dado.
- Saber que un cambio requiere commit y push a `main`.
- Reconocer acciones que requieren confirmacion doble.

## Calendario sugerido (pre-deploy)

| Dia | Sesion | Participantes |
|---|---|---|
| T-7 | Sesion 3 - Registradores (1.5h) | todos los registradores |
| T-5 | Sesion 2 - Supervisores (2h) | todos los supervisores |
| T-3 | Sesion 1 - Operaciones (3h) | equipo de soporte |
| T-2 | Sesion 4 - DB admin (1h) | DBA |
| T-0 | Go-live | acompanamiento en sitio |
| T+7 | Sesion Q&A abierta (1h) | todos |

## Post go-live

- T+1: acompanamiento presencial en horario de mayor uso.
- T+3: revision de logs y reportes de error.
- T+7: encuesta de satisfaccion y ajustes.
- T+30: revision de KPIs y planificacion de mejoras.
