# Especificación del Sistema: Observaciones REM

**Directorio**: `specs/001-unificar-specs/`
**Creado**: 2026-06-01
**Estado**: Completado
**Input**: Migración y unificación de especificaciones existentes en `specs/` y `openspec/changes/` basado en `README.md`

## Descripción General

Sistema de gestión de observaciones del Resumen Estadístico Mensual (REM)
para el Servicio de Salud Osorno (SSO) — Departamento de Estadística (DEGI).
La plataforma permite a registradores crear y gestionar observaciones sobre
reportes REM, y a supervisores validar, aprobar y generar reportes.

## Módulos del Sistema

| ID | Módulo | Archivo | Roles |
|----|--------|---------|-------|
| MOD-AUTH | Autenticación y Sesión | `auth-sesion.md` | Todos |
| MOD-OBS | Observaciones CRUD | `observaciones.md` | Registrador, Supervisor |
| MOD-SUP | Supervisión | `supervision.md` | Supervisor |
| MOD-EXP | Reportes y Exportación | `reportes-exportacion.md` | Todos (datos filtrados por rol) |
| MOD-IMP | Importación Excel | `importacion.md` | Registrador |
| MOD-ASN | Asignaciones | `asignaciones.md` | Supervisor |
| MOD-LOC | Establecimientos y Referentes | `establecimientos.md` | Supervisor |
| MOD-USR | Usuarios | `usuarios.md` | Supervisor |
| MOD-DEL | Papelera (Eliminadas) | `papelera-eliminadas.md` | Supervisor |
| DASH | Dashboard | `dashboard.md` | Todos (datos filtrados por rol) |
| VER | Versionado (Snapshots) | `versionado.md` | Supervisor |

## Stack Tecnológico

| Categoría | Tecnología |
|-----------|------------|
| Backend | PHP 7.4+, PDO MySQL (Singleton) |
| Base de Datos | MySQL 5.7+ (InnoDB, utf8mb4) |
| Frontend | HTML5, CSS3, JavaScript ES6+ |
| UI Framework | Tabler Core 1.4 (Bootstrap 5), Tabler Icons |
| Gráficos | ApexCharts 3.45 |
| Librerías PHP | PhpSpreadsheet 5.4 (Excel), TCPDF 6.10 (PDF) |
| Servidor | Apache (XAMPP) |

## Roles del Sistema

- **Registrador**: Crear/editar observaciones propias, importar Excel, ver
  reportes propios, descargar plantilla, cambiar contraseña propia.
- **Supervisor**: Gestionar usuarios, asignaciones, establecimientos,
  supervisar observaciones (aprobar/cancelar/eliminar), exportar reportes
  globales, generar Informe de Errores REM, acceder a papelera y versionado.

## Convenciones del Sistema

### APIs

- Todos los endpoints API usan español y estructura plana: `api/<modulo>.php?action=<accion>`.
- Excepción: `api/dashboard/` usa subdirectorio con múltiples endpoints.
- Mapeo de nombres normalizados:

| Original | Normalizado |
|----------|-------------|
| `api/observations.php` | `api/observaciones.php` |
| `api/deleted.php` | `api/eliminadas.php` |
| `api/versioning.php` | `api/versiones.php` |
| `api/dashboard/stats.php` | `api/dashboard/estadisticas.php` |
| `api/dashboard/charts.php` | `api/dashboard/graficos.php` |
| `api/dashboard/recent.php` | `api/dashboard/recientes.php` |
| `api/dashboard/alerts.php` | `api/dashboard/alertas.php` |
| `api/dashboard/sparklines.php` | `api/dashboard/sparklines.php` |
| `api/dashboard/timeline.php` | `api/dashboard/timeline.php` |
| `api/dashboard/kanban.php` | `api/dashboard/kanban.php` |

### CSRF

La validación CSRF es obligatoria en todos los endpoints POST/PUT/DELETE del sistema. Definido en la constitución y en `auth-sesion.md`. No se repite en cada spec; se asume como regla global.

### Formato de versión (snapshots)

Etiqueta auto-incremental zero-padded de 3 dígitos: `v001`, `v002`, ..., `v999`. Límite: v999. Directorio: `uploads/versiones/{version_tag}/`.

### Criterios de éxito

Toda afirmación de tiempo de respuesta debe cuantificarse. "Se refleja inmediatamente" se normaliza a "en menos de 500ms en la UI sin recargar la página".

### Campos de auditoría

Todas las tablas del sistema incluyen:
- `fecha_creacion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
- `fecha_actualizacion` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP

### Formato de respuesta JSON

Todas las APIs responden con: `{"success": true|false, "data": ..., "error": "...", "code": 200|400|401|403|404|500}`

### Terminología

- Tablas de auditoría usan prefijo `historial_` (no `historico_`).
- El campo de estado se normaliza a `estado` en todas las tablas.
- Estados canónicos del sistema: `pendiente`, `aprobado`, `error`, `rechazado`.

## Próximos Pasos

1. Revisar cada spec individual para validar que refleje la funcionalidad
   actual del sistema.
2. Usar `/speckit.plan` para planificar la implementación de cada módulo.
3. Usar `/speckit.tasks` para generar tareas a partir de las user stories.
4. Implementar siguiendo las especificaciones como guía técnica.
