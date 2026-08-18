# Sistema de Observaciones REM - Changelog

## [No publicado]

### Eliminado
- **Modulo Versionado retirado por completo**: `views/versionado.php`,
  `api/versioning.php`, `models/Version.php`,
  `config/migrations/sprint5_migration.sql`, la tabla `versiones_sistema`
  y la carpeta `uploads/versiones/`. Motivos:
  - Su `rollback()` no restauraba ningun archivo (bug de rutas en el
    manifiesto, `models/Version.php:167`) pero respondia "Rollback ejecutado
    exitosamente", dando una falsa capacidad de recuperacion.
  - Los snapshots quedaban bajo `uploads/`, servida por el navegador:
    `uploads/versiones/v001/config/config.php` era descargable con las
    credenciales de BD en texto plano, y `views/login.php` era ejecutable.
  - Git ya cubre el versionado y el rollback de codigo de forma fiable.
- El rollback de codigo pasa a documentarse solo con `git checkout`
  (OPERATIONS.md, DEPLOY.md).

### Seguridad
- `.htaccess` de proteccion en la raiz y en `config/`, `includes/`,
  `models/` y `uploads/`: se niega el acceso directo a la logica interna,
  a la documentacion y a los `.sql`, y se desactiva la ejecucion de PHP
  dentro de `uploads/`. Antes la proteccion dependia por completo del
  VirtualHost de `deploy/apache-rem.conf`.

### Exportaciones
- **Archivos corruptos**: `Exporter::prepararSalidaBinaria()` vacia el bufer de
  salida antes de emitir cada XLSX/PDF/CSV. Con `output_buffering` activo en
  php.ini, cualquier aviso previo se pegaba delante del binario y el archivo
  "no abria".
- **Pestañas en blanco**: `api/export.php` envuelve todo en `try/catch` y muestra
  una pagina de aviso con el motivo (sin datos, libreria ausente, error interno)
  en lugar de morir en silencio.
- **PDF sin visor instalado**: los PDF se emiten `inline`, de modo que se abren
  en el visor integrado del navegador; antes se forzaba la descarga.
- **Errores invisibles**: `config/config.php` cae a `logs/php-error.log` dentro
  del proyecto cuando la ruta configurada no es escribible (caso XAMPP/Windows).
- **Descargas verificadas**: nuevo `descargarArchivo()` en `assets/js/app.js`;
  reemplaza los `window.open()` a ciegas de Reportes, Dashboard y Boletin.
  Comprueba la respuesta, avisa con un toast si falla, muestra "Generando…" en
  el boton y detecta el bloqueo de ventanas emergentes.
- **Dependencias**: `Exporter::requerirLibreria()` avisa que hay que ejecutar
  `composer install` en vez de lanzar "Class not found".
- Corregido `models/Exporter.php`: en el PDF detallado, `$estado` se leia antes
  de asignarse, por lo que la etiqueta de clasificacion podia tomar el estado de
  la fila anterior (y generaba un warning en la primera fila).

### Reportes
- Pestañas "Plazos Entrega" y "Uso Validador" rediseñadas como informe: matriz de
  doble entrada (establecimiento x mes), encabezado institucional, resumen
  "cumple" por establecimiento, totales por mes, boton Imprimir con estilos
  `@media print` (A4 horizontal) y exportacion a Excel y PDF del mismo contenido.
- Las matrices respetan el filtro de mes/trimestre; sin filtro muestran solo los
  meses con datos, en vez de los 12 siempre.
- El boton "Exportar Excel" de esas pestañas ya no descarga un reporte distinto
  del que se ve en pantalla (`plazo_matriz` / `validador_matriz`).
- Tab "Plazos Entrega" reparado (llamaba a una funcion inexistente).
- Matrices Plazo/Validador unificadas, sin estilos inline, con leyenda y
  los 12 meses por establecimiento.
- Filtros de comuna/establecimiento y alcance por rol aplicados a
  `plazo-agregado` y `validador-agregado`.
- Grafico "Total Errores": nombres de establecimiento completos y alto
  proporcional a la cantidad de barras.

## [2.1.0] - 2026-06-24

### Operacion
- `deploy/` con 18 scripts y plantillas para produccion:
  provisionamiento, HTTPS, MySQL usuario, migraciones ordenadas,
  env file, Apache endurecido, fail2ban, worker systemd timer,
  backup/restore, healthcheck, primer supervisor, limpieza demo.
- `deploy/TRAINING.md`: plan de capacitacion para go-live.
- `deploy/CUTOVER.md`: checklist de corte del ambiente dev.
- `docs/releases/2.1.0.md`: notas orientadas a usuarios finales.
- `docs/releases/2.1.0-internal.md`: notas tecnicas para desarrollo/soporte.
- `docs/releases/2.1.0-GO-LIVE.md`: acta de go-live.
- Tag `v2.1.0` firmado y pusheado a `origin`.

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
