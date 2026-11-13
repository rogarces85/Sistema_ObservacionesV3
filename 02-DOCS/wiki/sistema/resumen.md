# Sistema de Observaciones REM — Resumen completo del sistema

> **Ubicación:** `02-DOCS/wiki/sistema/resumen.md`
> **Estado:** vigente
> **Última actualización:** 2026-11-13

Este documento consolida todo lo necesario para entender, instalar, operar y modificar el Sistema de Observaciones REM. Es la guía de referencia para trabajar en este proyecto desde cualquier máquina (incluida tu casa).

---

## 1. Qué es este sistema

**Sistema de Observaciones REM** es un monólito PHP (sin framework) que gestiona el registro, revisión, supervisión, importación y reporte de observaciones asociadas a los reportes **REM** (Resumen Estadístico Mensual) de establecimientos de salud atendidos por el **Servicio de Salud Osorno**, Chile.

### Roles

| Rol | Qué puede hacer |
|-----|-----------------|
| `registrador` | Crea y edita sus propias observaciones para los establecimientos y meses que le están asignados. Puede editar observaciones en cualquier estado; al editar una que no es `pendiente`, esta vuelve a `pendiente` para reingresar a revisión. |
| `supervisor` | Revisa, aprueba o cancela observaciones. Gestiona usuarios, asignaciones, establecimientos y genera reportes consolidados. |

### Estados de observación

`pendiente`, `aprobado`, `rechazado`, `error`, `justificado`.

### Tipos de error (`tipo_error`)

`S/OBSERVACION`, `ERROR`, `REVISAR`, `F/PLAZO`.

---

## 2. Repositorio GitHub y versión

| Dato | Valor |
|------|-------|
| Repositorio | `https://github.com/rogarces85/Sistema_ObservacionesV3` |
| Rama por defecto | `main` |
| Último tag publicado | `v2.1.0` (commit `425a453`, 2026-06-24) |
| Último commit en `main` | `7c60567` (17/08/2026) — matriz Plazo/Validador |
| Último push | 19/08/2026 |

### Estado de sincronización: copia local vs GitHub

La carpeta local `C:\xampp\htdocs\www\respaldo_observaciones` **no es un clone de git** (no tiene `.git`). Es una copia de respaldo ("respaldo"). Hay diferencias conocidas:

| Elemento | Local (`respaldo_observaciones`) | GitHub (`main`) |
|----------|----------------------------------|-----------------|
| Módulo Versionado | **Eliminado**: no existen `api/versioning.php`, `views/versionado.php`, `models/Version.php` | **Presente**: `api/versioning.php` existe en `main` |
| `views/reportes.php.bak` | Existe (respaldo local) | No existe (404 en GitHub) |
| `config/.env.local.php` | Existe (credenciales locales, gitignorado) | No sube (esperado) |
| `vendor/` | Existe (`composer install`) | No sube (dependencias) |
| Rediseño reportes (matrices Plazo/Validador) | Presente en CHANGELOG como "[No publicado]" | Coincide con commit `7c60567` de GitHub |

**Conclusión:** la copia local tiene trabajo posterior al tag `v2.1.0` (rediseño de reportes) pero **no está sincronizada** con `main` de GitHub. El módulo Versionado fue borrado localmente y eso no se refleja en el remoto.

---

## 3. Arquitectura técnica

Monólito PHP tradicional, sin router ni framework, sin inyección de dependencias.

### Flujo de request

1. `index.php` — punto de entrada único. Verifica `$_SESSION['logged_in']`, lee `?page=`, valida contra una whitelist hardcodeada, aplica redirecciones por rol y `include`s `views/{page}.php` envuelto por `includes/header.php` y `includes/footer.php`.
2. `api/*.php` — un archivo por recurso, endpoint JSON autónomo. Cada uno: chequea sesión, lee `$_GET['action']` + `$_SERVER['REQUEST_METHOD']`, despacha via `switch`/`if`, define su propio `jsonResponse()`. Respuestas siempre `{success, data, message}`.
3. `models/*.php` — una clase por entidad con SQL embebido vía PDO (`Observation`, `User`, `Location`, `EstablecimientoAsignacion`, `DeletedObservation`, `Notification`, `ReportQueue`, `Exporter`, `UserAudit`, `Database`).
4. `models/Database.php` — singleton (`Database::getInstance()`) con una conexión PDO compartida. Es el nodo de mayor fan-in del sistema.
5. `includes/csrf.php` — clase `CSRF` con token almacenado en sesión, validado vía header `X-CSRF-Token` o campo POST `csrf_token`. Todo request mutante desde el navegador debe pasar CSRF.
6. `worker_reportes.php` — worker cron que drena `reportes_pendientes`. No tiene caller en la UI (subsistema huérfano).

### Autorización

No está centralizada. Los controles de rol están duplicados en `index.php`, en cada `api/*.php` y a veces en métodos de modelo. Al agregar una página o endpoint, se debe replicar el rol guard manualmente. No hay middleware.

---

## 4. Stack tecnológico

| Capa | Tecnología |
|------|------------|
| Backend | PHP 7.4+ declarado; PHP 8.2.12 observado |
| Servidor web | Apache / XAMPP |
| Base de datos | MySQL 5.7+ / MariaDB compatible (`observaciones_rem`) |
| Acceso a datos | PDO MySQL (prepared statements) |
| Frontend | HTML5, CSS3, JavaScript ES6+ |
| UI framework | Tabler 1.4 (`@tabler/core`, `@tabler/icons-webfont`) — **único framework CSS permitido** |
| Tokens de diseño | `assets/css/tokens.css` (CSS custom properties) |
| Overrides | `assets/css/tabler-override.css` |
| CSS deprecated | `assets/css/styles.css` (1650 líneas, aún cargado pero no extender) |
| Gráficos | Chart.js |
| Excel | `phpoffice/phpspreadsheet:^5.4` |
| PDF | `tecnickcom/tcpdf:^6.10` |
| Gestión de dependencias PHP | Composer |
| rsc-harness | `@ericrisco/rsc@4.1.5` (target `deepseek`, workflow `branches`) |

---

## 5. Estructura de directorios (raíz del proyecto)

```
Sistema_ObservacionesV3/
├── api/                        # Endpoints JSON y descargas
│   ├── auth.php                # Login, logout, check, cambio de año
│   ├── observations.php        # CRUD de observaciones e historial
│   ├── supervision.php         # Aprobación, cancelación, detalle, papelera
│   ├── reports.php             # Datos agregados para gráficos/reportes
│   ├── export.php              # Exportación Excel/PDF síncrona
│   ├── import.php              # Importación Excel preview/confirmar
│   ├── import_template.php     # Plantilla Excel
│   ├── locations.php           # Comunas y establecimientos
│   ├── assignments.php         # Asignaciones anuales/temporales
│   ├── users.php               # Gestión de usuarios
│   ├── deleted.php             # Papelera de observaciones eliminadas
│   ├── informe_errores.php     # Informe REM de errores trimestral/anual
│   ├── notifications.php       # Notificaciones
│   ├── report_queue.php        # Cola de reportes (sin caller en UI)
│   └── boletin.php             # Boletín
├── assets/
│   ├── css/                    # tokens.css, tabler-override.css, styles.css (legacy)
│   ├── js/                     # app.js, theme.js, charts.js, confirm-modal.js, notifications.js
│   ├── images/                 # Logos e imágenes institucionales
│   └── libs/                   # Librerías frontend (countup.js, litepicker, list.js, etc.)
├── config/
│   ├── config.php              # BD, rutas, sesión, entorno (falla cerrado en prod)
│   ├── constants.php           # Estados, roles, meses, series y hojas REM
│   ├── init_db.sql             # Esquema inicial y datos base
│   ├── migration_*.sql         # Migraciones sueltas
│   ├── migrations/             # Migraciones por sprint (sprint1, sprint2, sprint4)
│   └── .htaccess               # Deniega acceso directo
├── includes/
│   ├── header.php              # Shell superior (búsqueda, notificaciones, año, tema)
│   ├── sidebar.php             # Sidebar vertical
│   ├── footer.php              # Footer, toast container, FAB, carga de assets
│   ├── breadcrumbs.php         # Helper de migas de pan
│   ├── csrf.php                # Generación/validación de token CSRF
│   └── Mailer.php              # Envío de correos (contraseñas aleatorias)
├── models/
│   ├── Database.php            # PDO Singleton
│   ├── Observation.php         # Observaciones, estados, reportes e informes
│   ├── User.php                # Usuarios y autenticación
│   ├── UserAudit.php           # Auditoría de usuarios
│   ├── Location.php            # Comunas y establecimientos
│   ├── EstablecimientoAsignacion.php # Asignaciones anuales/temporales
│   ├── DeletedObservation.php  # Papelera y restauración
│   ├── Exporter.php            # Excel/PDF
│   ├── ReportQueue.php         # Cola de reportes asíncronos
│   └── Notification.php        # Notificaciones
├── views/
│   ├── login.php               # Inicio de sesión
│   ├── dashboard.php           # Panel principal
│   ├── observaciones.php       # Gestión de observaciones
│   ├── supervision.php         # Revisión de supervisor
│   ├── reportes.php            # Reportes y gráficos
│   ├── usuarios.php            # Administración de usuarios
│   ├── perfil.php              # Perfil y cambio de contraseña
│   ├── asignaciones.php        # Gestión de asignaciones
│   ├── eliminadas.php          # Papelera de observaciones
│   ├── establecimientos.php    # Gestión de establecimientos
│   ├── boletin.php             # Boletín
│   └── reportes.php.bak        # Respaldo local (no subir a GitHub)
├── specs/                      # Especificaciones por módulo y migraciones por sprint
│   └── 002-fix-button-actions/ # Plan, spec, evidencia de botones/actions
├── docs/
│   ├── spec/                   # LÍNEA BASE AUTORITATIVA (openapi.yaml, BASELINE.md, VERDAD-ACTUAL.md, AUDITORIA-CODIGO-MUERTO.md)
│   └── releases/               # Notas de versión 2.1.0
├── deploy/                     # Scripts de despliegue producción (backup, migrate, healthcheck, etc.)
├── tools/                      # Scripts manuales (seed_demo.php, verify_model.php, etc.)
├── uploads/                    # Archivos de runtime (no subir a GitHub)
├── 02-DOCS/                    # Conocimiento del proyecto (rsc-harness)
├── 01-TOOLS/                   # Conexiones a herramientas externas (rsc-harness)
├── .rsc/                       # Motor de rsc-harness
├── .rsc.json                   # Configuración de rsc-harness
├── AGENTS.md                   # Instrucciones para agentes (rsc + CLAUDE original)
├── CLAUDE.md                   # Documentación original del proyecto (autoridad para el código)
├── README.md                   # Documentación antigua del sistema (donde discrepe con docs/spec/, gana docs/spec/)
├── CHANGELOG.md                # Historial de cambios por versión
├── composer.json               # phpspreadsheet, tcpdf
├── .gitignore                  # Excluye vendor, .env.local.php, uploads, *.bak, logs, etc.
├── SECURITY.md                 # Modelo de amenazas
├── DEPLOY.md                   # Despliegue producción
├── OPERATIONS.md               # Runbook de operaciones
└── SUBIR-A-GITHUB.ps1          # Script local para subir todo a GitHub (no subir al repo)
```

---

## 6. Reglas de negocio críticas (no romperlas)

Estas reglas viven en código PHP, no en restricciones de BD:

1. **Assignment months dual-typed**: `observaciones.mes` almacena el nombre del mes (ej. `"Enero"`); `asignaciones_establecimientos.meses` almacena CSV de números de mes o la literal `ALL` (todos los meses). `EstablecimientoAsignacion::tieneMes()` / `fusionarMeses()` hacen el puente.
2. **Temporal assignments override annual**: `tieneAsignacionParaMes()` revisa asignaciones temporales antes que anuales. Una temporal a otro usuario *bloquea* al titular anual para ese establecimiento/mes. Temporales solapadas para el mismo establecimiento/año/mes se rechazan.
3. **Soft delete con papelera**: `api/observations.php` DELETE y `api/supervision.php?action=delete` mueven a `observaciones_eliminadas` vía `DeletedObservation::moveToTrash()` (recuperable). El **único** delete irreversible es `api/deleted.php?action=permanent_delete` (y su variante `_multiple`), que requiere `confirm_irreversible: true` en el cuerpo. Nunca se saltea ese flag.
4. Desde commit `a9728da`, el único `DELETE FROM observaciones` en el código es `models/DeletedObservation.php:76` (INSERT en trash primero). No hay transacción alrededor del INSERT+DELETE.
5. **Import valida asignación mensual** igual que creación manual (`api/import.php:183` llama a `tieneAsignacionParaMes()`).
6. `codigo_hoja` (hoja REM) es obligatorio a menos que `tipo_error === 'S/OBSERVACION'`.
7. Un registrador solo puede editar sus **propias** observaciones, pero en **cualquier estado**; editar una que no es `pendiente` la resetea a `pendiente` (`api/observations.php:172-175`).
8. **Aprobar requiere elegir un resultado**: `api/supervision.php?action=approve` exige `estado_resultante` (`sin_observacion` → `aprobado` + `S/OBSERVACION`; `error` → `error` + `ERROR`). No hay `reject` directo (devuelve 403 por diseño); `rechazado` se alcanza vía `?action=cancel`.
9. **CSRF obligatorio** en todo request mutante desde el navegador.
10. `ENVIRONMENT` default es `'production'` (fail-closed). UI que expone secretos debe usar `=== 'development'`, nunca `!== 'production'`.

> La fuente autoritativa completa es `docs/spec/` (BASELINE.md, VERDAD-ACTUAL.md, openapi.yaml). Donde `README.md` discrepe con `docs/spec/`, gana `docs/spec/`.

---

## 7. Base de datos

- Nombre: `observaciones_rem`
- Motor: MySQL / MariaDB
- No hay herramienta de migraciones automatizada. El esquema se aplica ejecutando archivos SQL directamente (ver `README.md` "Instalación Local Con XAMPP" para el orden de `config/*.sql` y `config/migrations/`).
- `deploy/migrate.sh` está roto para instalaciones nuevas (referencia archivos inexistentes y aplica migraciones en orden incorrecto). No confiar en él para fresh installs.
- Tablas clave: `usuarios`, `establecimientos`, `comunas`, `observaciones`, `asignaciones_establecimientos`, `observaciones_eliminadas`, `historial_usuarios`, `notificaciones`, `reportes_pendientes`, `tipo_asignacion`, etc.

### ⚠️ Seguridad de producción

- El sistema está **en producción con datos reales**.
- Cualquier operación sobre la base de datos requiere **respaldo verificado previo**.
- No hay suite de pruebas automatizada (no PHPUnit).
- `config/config.php` resuelve credenciales en este orden: `REM_ENV_FILE` → `config/.env.local.php` → `REM_DB_*` env vars → defaults hardcodeados solo si `REM_ENVIRONMENT=development`. En producción sin configuración, falla con 500 (fail-closed).
- Nunca agregar credenciales o secretos a archivos versionados. Ver `DEPLOY.md` para el patrón `/etc/rem/env.php`.

---

## 8. Endpoints API (15 principales)

| Archivo | Acciones principales |
|---------|---------------------|
| `api/auth.php` | login, logout, check, cambio de año |
| `api/observations.php` | CRUD observaciones, historial |
| `api/supervision.php` | approve, cancel, delete, detalle |
| `api/reports.php` | datos agregados para gráficos/reportes |
| `api/export.php` | exportación Excel/PDF |
| `api/import.php` | preview y confirmación de importación Excel |
| `api/import_template.php` | plantilla Excel |
| `api/locations.php` | comunas y establecimientos |
| `api/assignments.php` | asignaciones anuales/temporales |
| `api/users.php` | gestión de usuarios |
| `api/deleted.php` | papelera, restore, permanent_delete |
| `api/informe_errores.php` | informe REM de errores |
| `api/notifications.php` | notificaciones |
| `api/report_queue.php` | cola de reportes (sin caller UI) |
| `api/boletin.php` | boletín |

---

## 9. Cómo instalar en tu casa (paso a paso)

### Requisitos

- PHP 8.2+ (CLI y Apache/XAMPP)
- Composer
- MySQL / MariaDB
- Acceso al repositorio GitHub `rogarces85/Sistema_ObservacionesV3`

### Paso 1: Clonar el repositorio

```powershell
git clone https://github.com/rogarces85/Sistema_ObservacionesV3.git
cd Sistema_ObservacionesV3
```

> **Nota:** Si usas la copia local de trabajo (`respaldo_observaciones`), primero debe subirse a GitHub con el script `SUBIR-A-GITHUB.ps1` (ver sección 11). La copia local no tiene `.git`, así que no se puede clonar directamente desde ella.

### Paso 2: Instalar dependencias PHP

```powershell
composer install
# O en producción:
composer install --no-dev --optimize-autoloader
```

### Paso 3: Configurar credenciales de base de datos

Crear `config/.env.local.php` (gitignorado, nunca subir a GitHub):

```php
<?php
return [
    'db_host' => 'localhost',
    'db_name' => 'observaciones_rem',
    'db_user' => 'tu_usuario',
    'db_pass' => 'tu_contraseña',
    'REM_ENVIRONMENT' => 'development',
];
```

### Paso 4: Aplicar esquema de base de datos

Ejecutar los archivos SQL en orden (ver `README.md` sección "Instalación Local Con XAMPP"). No usar `deploy/migrate.sh` para fresh install (está roto).

### Paso 5: Configurar Apache/XAMPP

Apuntar el virtual host o carpeta de XAMPP a la raíz del proyecto. El archivo `index.php` es el punto de entrada.

### Paso 6: Verificar sintaxis PHP

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

### Paso 7: Semillas de prueba (opcional, solo desarrollo)

`tools/seed_demo.php` escribe en la BD. Tiene guardia `ENVIRONMENT !== 'development'` en la línea 18, pero el fatal por `require_once` ocurre antes de alcanzar esa guardia. Corregir la ruta (`__DIR__ . '/../config/config.php`) antes de usarlo. **No ejecutar en producción.**

---

## 10. Reglas de trabajo con rsc-harness

Este proyecto tiene instalado `rsc-harness` (v4.1.5, target `deepseek`). Véase `.rsc.json` para la configuración completa.

### Carril de trabajo (workflow): `branches`

La rama principal (`main`) está **cerrada** para el agente. Antes de cada cambio de código, se pregunta: ¿esta rama, una nueva, o `rsc main unlock`? Nunca hacer branch solo.

### Líneas (lanes)

1. **Answer** — solo explicar/investigar, no escribir nada.
2. **FTD** (Fast-Track Development) — cambio ordinario: un feature document en `02-DOCS/`, checklist, evidencia, next step.
3. **ASDD** — cambios grandes/complejos: cadena specify → clarify → plan → tasks → analyze → implement → review → ship.

### Comandos útiles de rsc

```powershell
# Salud del harness
npx @ericrisco/rsc doctor

# Reinstalar/reparar harness
npx @ericrisco/rsc repair

# Ver skills instaladas
npx @ericrisco/rsc list

# Instalar una skill adicional
npx @ericrisco/rsc add <skill-id>

# Reevaluar componentes diferidos
npx @ericrisco/rsc reassess

# Sincronizar todo (tidy)
npx @ericrisco/rsc tidy
```

### Conocimiento del proyecto

- `02-DOCS/wiki/` — wiki del proyecto (decisiones, instalación, perfil, sistema)
- `01-TOOLS/` — conexiones a herramientas externas
- `.rsc/` — motor del harness (no editar manualmente)

---

## 11. Cómo subir todo este proyecto a GitHub (rama main)

La copia local en `C:\xampp\htdocs\www\respaldo_observaciones` no tiene `.git`. Para convertirla en la fuente de verdad de `main` en GitHub:

1. Abrir PowerShell en `C:\xampp\htdocs\www\respaldo_observaciones`
2. Ejecutar: `.\SUBIR-A-GITHUB.ps1`
3. El script inicializa git, hace commit de todo lo local, agrega el remote `origin`, renombra a `main` y hace `push --force` a GitHub.
4. Se pedirá usuario de GitHub y un **Personal Access Token (PAT)** con scope `repo`.
5. **⚠️ Advertencia:** esto sobrescribe `main` en GitHub con el contenido local. El módulo Versionado que existe en GitHub se eliminará del remoto (ya fue borrado localmente).

Después, en cualquier otra máquina (tu casa):

```powershell
git clone https://github.com/rogarces85/Sistema_ObservacionesV3.git
cd Sistema_ObservacionesV3
composer install
```

### Qué NO se sube a GitHub (ya en `.gitignore`)

- `config/.env.local.php` — credenciales de BD
- `vendor/` — dependencias Composer
- `uploads/` — archivos de runtime
- `*.bak` — respaldos locales (ej. `views/reportes.php.bak`)
- `logs/`, `*.log`
- `SUBIR-A-GITHUB.ps1` — script de utilidad local
- `.rsc/` — estado de máquina de rsc (opcional; si se quiere compartir el harness, subir `.rsc.json`, `02-DOCS/`, `01-TOOLS/`, `AGENTS.md`)

---

## 12. Archivos clave para referencia rápida

| Propósito | Archivo |
|-----------|---------|
| Punto de entrada web | `index.php` |
| Configuración BD y entorno | `config/config.php` |
| Constantes de negocio | `config/constants.php` |
| Modelo de datos autoritativo | `docs/spec/VERDAD-ACTUAL.md` |
| Contrato de API | `docs/spec/openapi.yaml` |
| 17 reglas de oro | `docs/spec/BASELINE.md` |
| Matriz de autorización | `docs/spec/VERDAD-ACTUAL.md` |
| Código muerto y riesgos | `docs/spec/AUDITORIA-CODIGO-MUERTO.md` |
| Changelog | `CHANGELOG.md` |
| Despliegue producción | `DEPLOY.md` |
| Operaciones y runbook | `OPERATIONS.md` |
| Seguridad | `SECURITY.md` |
| rsc config | `.rsc.json` |
| rsc AGENTS | `AGENTS.md` |

---

## 13. Advertencias importantes

1. **Datos reales en producción.** No modificar la BD sin respaldo verificado.
2. **No hay pruebas automatizadas.** La verificación es manual (php -l, walkthroughs de páginas, pruebas controladas con registros de prueba).
3. **El módulo Versionado fue eliminado localmente pero existe en GitHub.** Al subir a GitHub se perderá del remoto.
4. **`deploy/migrate.sh` está roto.** No usarlo para instalaciones nuevas.
5. **`tools/seed_demo.php` escribe en la BD.** Solo desarrollo, y su ruta de `require_once` está rota (falta `..`).
6. **CSRF es obligatorio** en todo request mutante desde el navegador.
7. **rsc-harness usa workflow `branches`.** No hacer commits directos a `main` sin desbloquear primero.

---

## 14. Glosario

| Término | Significado |
|---------|-------------|
| REM | Resumen Estadístico Mensual (reporte de salud) |
| Registrador | Usuario que crea/edita observaciones |
| Supervisor | Usuario que aprueba/cancela y gestiona usuarios |
| Papelera | `observaciones_eliminadas` (soft delete recuperable) |
| Delete irreversible | `permanent_delete` con `confirm_irreversible: true` |
| Asignación temporal | Reasignación que anula la anual para un mes |
| Asignación anual | Asignación base por establecimiento y año |
| `codigo_hoja` | Hoja del formulario REM, obligatoria salvo `S/OBSERVACION` |

---

*Fin del resumen. Para detalles técnicos completos, consultar `docs/spec/` y `CLAUDE.md`.*
