# Changelog — Sistema de Observaciones REM

> **Ubicación:** `02-DOCS/wiki/sistema/changelog.md`
> **Fuente original:** `CHANGELOG.md` (raíz)
> **Última actualización:** 2026-11-13

---

## [No publicado] — 2026-08 (en desarrollo)

### Eliminado
- **Módulo Versionado retirado por completo**: `views/versionado.php`, `api/versioning.php`, `models/Version.php`, `config/migrations/sprint5_migration.sql`, la tabla `versiones_sistema` y la carpeta `uploads/versiones/`.
  - Motivos: su `rollback()` no restauraba ningún archivo (bug de rutas en el manifiesto) pero respondía "Rollback ejecutado exitosamente", dando falsa capacidad de recuperación.
  - Los snapshots quedaban bajo `uploads/`, servida por el navegador: `uploads/versiones/v001/config/config.php` era descargable con las credenciales de BD en texto plano, y `views/login.php` era ejecutable.
  - Git ya cubre el versionado y el rollback de código de forma fiable.
- El rollback de código pasa a documentarse solo con `git checkout` (ver `operaciones/runbook.md`, `operaciones/deploy.md`).

### Seguridad
- `.htaccess` de protección en la raíz y en `config/`, `includes/`, `models/` y `uploads/`: se niega el acceso directo a la lógica interna, a la documentación y a los `.sql`, y se desactiva la ejecución de PHP dentro de `uploads/`. Antes la protección dependía por completo del VirtualHost de `deploy/apache-rem.conf`.

### Exportaciones
- **Archivos corruptos**: `Exporter::prepararSalidaBinaria()` vacía el búfer de salida antes de emitir cada XLSX/PDF/CSV. Con `output_buffering` activo en `php.ini`, cualquier aviso previo se pegaba delante del binario y el archivo "no abría".
- **Pestañas en blanco**: `api/export.php` envuelve todo en `try/catch` y muestra una página de aviso con el motivo (sin datos, librería ausente, error interno) en lugar de morir en silencio.
- **PDF sin visor instalado**: los PDF se emiten `inline`, de modo que se abren en el visor integrado del navegador; antes se forzaba la descarga.
- **Errores invisibles**: `config/config.php` cae a `logs/php-error.log` dentro del proyecto cuando la ruta configurada no es escribible (caso XAMPP/Windows).
- **Descargas verificadas**: nuevo `descargarArchivo()` en `assets/js/app.js`; reemplaza los `window.open()` a ciegas de Reportes, Dashboard y Boletín. Comprueba la respuesta, avisa con un toast si falla, muestra "Generando…" en el botón y detecta el bloqueo de ventanas emergentes.
- **Dependencias**: `Exporter::requerirLibreria()` avisa que hay que ejecutar `composer install` en vez de lanzar "Class not found".
- Corregido `models/Exporter.php`: en el PDF detallado, `$estado` se leía antes de asignarse, por lo que la etiqueta de clasificación podía tomar el estado de la fila anterior (y generaba un warning en la primera fila).

### Reportes
- Pestañas "Plazos Entrega" y "Uso Validador" rediseñadas como informe: matriz de doble entrada (establecimiento × mes), encabezado institucional, resumen "cumple" por establecimiento, totales por mes, botón Imprimir con estilos `@media print` (A4 horizontal) y exportación a Excel y PDF del mismo contenido.
- Las matrices respetan el filtro de mes/trimestre; sin filtro muestran solo los meses con datos, en vez de los 12 siempre.
- El botón "Exportar Excel" de esas pestañas ya no descarga un reporte distinto del que se ve en pantalla (`plazo_matriz` / `validador_matriz`).
- Tab "Plazos Entrega" reparado (llamaba a una función inexistente).
- Matrices Plazo/Validador unificadas, sin estilos inline, con leyenda y los 12 meses por establecimiento.
- Filtros de comuna/establecimiento y alcance por rol aplicados a `plazo-agregado` y `validador-agregado`.
- Gráfico "Total Errores": nombres de establecimiento completos y alto proporcional a la cantidad de barras.

---

## [2.1.0] — 2026-06-24

### Operación
- `deploy/` con 18 scripts y plantillas para producción: provisionamiento, HTTPS, MySQL usuario, migraciones ordenadas, env file, Apache endurecido, fail2ban, worker systemd timer, backup/restore, healthcheck, primer supervisor, limpieza demo.
- `deploy/TRAINING.md`: plan de capacitación para go-live.
- `deploy/CUTOVER.md`: checklist de corte del ambiente dev.
- `docs/releases/2.1.0.md`: notas orientadas a usuarios finales.
- `docs/releases/2.1.0-internal.md`: notas técnicas para desarrollo/soporte.
- `docs/releases/2.1.0-GO-LIVE.md`: acta de go-live.
- Tag `v2.1.0` firmado y pusheado a `origin`.

### Seguridad
- CSRF obligatorio en endpoints mutables de `users`, `assignments`, `deleted`, `observations`, `import` (confirm), `report_queue` y `versioning`.
- Confirmaciones explícitas para acciones irreversibles: `confirm_irreversible` en `api/deleted.php`, `confirm_delete` y `confirm_reset` en `api/users.php`, confirmación tipeada "ACEPTAR" en `versioning rollback`.
- Validación de años, meses, IDs y tipos en backend.
- Guards contra auto-acciones de supervisor: no puede cambiarse el propio rol desde admin, no puede desactivarse / eliminarse / resetearse a sí mismo.

### UX / feedback
- `parseJsonResponse` en todas las vistas para evitar falsos positivos en respuestas HTML/500.
- Bloqueo de doble submit en formularios, snapshots, rollbacks, encolar reportes e informes.
- Toggle de activo/inactivo revierte visualmente sin recargar.
- Modal de aprobación no se cierra si la API falla.
- Reemplazo de clases `hidden` por `d-none` (Bootstrap 5).
- Modal de asignación resetea correctamente radios y meses.
- Detecciones finales del detalle de observación usan nombres reales de la API (`respuesta_establecimiento`, `fecha_revision`).

### Verificación
- `php -l` en todos los archivos modificados.
- `composer install` documentado.
- Smoke HTTP autenticado como `supervisor1` y `registrador2` en las 10 pantallas.
- Mutaciones controladas con CSRF y reversibles (sin borrado permanente) ejecutadas y registradas en `specs/002-fix-button-actions/verification-evidence.md`.

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

---

## [2.0.0] — 2026-06-23

### Características
- Sistema de gestión y registro de observaciones REM para el Servicio de Salud Osorno.
- Roles: registrador y supervisor.
- Gestión de observaciones, supervisión, papelera, asignaciones, establecimientos, usuarios, reportes y versionado.
- Reportes síncronos (Excel/PDF) y asíncronos con cola.
- Importación Excel con preview y confirmación.
- Snapshots de versionado con rollback protegido.