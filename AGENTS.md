# AGENTS.md — Sistema de Observaciones REM

> **Proyecto:** Sistema de Observaciones REM — PHP monolith para el Servicio de Salud Osorno, Chile
> **Rama:** `main` (proyecto madre en GitHub)
> **Última actualización:** 2026-11-13
> **Idioma:** español

Este archivo es la guía de referencia para cualquier persona o agente que trabaje sobre este repositorio. Contiene lo esencial para entender el sistema, cómo trabajar y qué reglas no se deben romper.

---

## 1. Qué es este sistema

**Sistema de Observaciones REM** es un monólito PHP tradicional (sin framework, sin router, sin inyección de dependencias) que gestiona el registro, revisión, supervisión, importación y reporte de observaciones asociadas a los reportes **REM** (Resumen Estadístico Mensual) de establecimientos de salud atendidos por el **Servicio de Salud Osorno**, Región de Los Lagos, Chile.

### Roles

| Rol | Qué puede hacer |
|-----|-----------------|
| `registrador` | Crea y edita sus propias observaciones para establecimientos y meses asignados. Puede editar observaciones en cualquier estado; al editar una que no es `pendiente`, esta vuelve a `pendiente` para reingresar a revisión. |
| `supervisor` | Revisa, aprueba o cancela observaciones. Gestiona usuarios, asignaciones, establecimientos y genera reportes consolidados. |

### Estados de observación

`pendiente`, `aprobado`, `rechazado`, `error`, `justificado`.

### Tipos de error (`tipo_error`)

`S/OBSERVACION`, `ERROR`, `REVISAR`, `F/PLAZO`.

---

## 2. Repositorio y versión

| Dato | Valor |
|------|-------|
| Repositorio GitHub | https://github.com/rogarces85/Sistema_ObservacionesV3 |
| Rama por defecto | `main` |
| Último tag publicado | `v2.1.0` (2026-06-24) |
| Último commit en `main` | `7c60567` (17/08/2026) — matriz Plazo/Validador |
| Stack | PHP 8.2, Apache/XAMPP, MySQL/MariaDB, Tabler 1.4, Composer (phpspreadsheet, tcpdf) |

---

## 3. ⚠️ Advertencias de producción (leer antes de tocar cualquier cosa)

El sistema está **en producción con datos reales**.

1. **Nunca operar la base de datos sin respaldo verificado previo.** No hay herramienta de migraciones ni suite de pruebas automatizada.
2. **`config/.env.local.php` nunca se sube a GitHub.** Contiene credenciales de base de datos. Está en `.gitignore`.
3. **`vendor/` no se sube.** Se instala con `composer install` en cada máquina.
4. **`uploads/` no se sube.** Es runtime del sistema.
5. **CSRF es obligatorio** en todo request mutante desde el navegador (`X-CSRF-Token` header o campo POST `csrf_token`).
6. **No hacer commits directos a `main`** salvo que se desbloquee explícitamente. El flujo de trabajo es por ramas y pull request.
7. **`deploy/migrate.sh` está roto** para instalaciones nuevas. No usarlo para fresh installs.
8. **`tools/seed_demo.php` escribe en la BD.** Solo desarrollo. Su ruta `require_once` está rota (falta `..`).

---

## 4. Arquitectura técnica

Monólito PHP clásico, una capa por responsabilidad:

- `index.php` — punto de entrada único. Verifica sesión, lee `?page=`, valida contra whitelist, aplica redirecciones por rol, incluye `views/{page}.php` envuelto por `includes/header.php` y `includes/footer.php`.
- `api/*.php` — un archivo por recurso, endpoint JSON autónomo. Cada uno chequea sesión, lee `$_GET['action']` + `$_SERVER['REQUEST_METHOD']`, despacha via `switch`/`if`, define su propio `jsonResponse()`. Respuestas siempre `{success, data, message}`.
- `models/*.php` — una clase por entidad con SQL embebido vía PDO (`Observation`, `User`, `Location`, `EstablecimientoAsignacion`, `DeletedObservation`, `Notification`, `ReportQueue`, `Exporter`, `UserAudit`, `Database`).
- `models/Database.php` — singleton (`Database::getInstance()`) con una conexión PDO compartida. Nodo de mayor fan-in del código.
- `includes/csrf.php` — clase `CSRF` con token en sesión, validado via header o campo POST.

**Autorización no centralizada:** los controles de rol están duplicados en `index.php`, cada `api/*.php` y a veces en métodos de modelo. No hay middleware. Al agregar una página o endpoint, se debe replicar el rol guard manualmente.

---

## 5. Reglas de negocio críticas (no romperlas)

Estas reglas viven en código PHP, no en restricciones de BD:

1. **Assignment months dual-typed:** `observaciones.mes` almacena el nombre del mes (ej. `"Enero"`); `asignaciones_establecimientos.meses` almacena CSV de números de mes o la literal `ALL` (todos los meses). `EstablecimientoAsignacion::tieneMes()` / `fusionarMeses()` hacen el puente.
2. **Temporal assignments override annual:** `tieneAsignacionParaMes()` revisa asignaciones temporales antes que anuales. Una temporal a otro usuario *bloquea* al titular anual para ese establecimiento/mes. Temporales solapadas para el mismo establecimiento/año/mes se rechazan.
3. **Soft delete con papelera:** `api/observations.php` DELETE y `api/supervision.php?action=delete` mueven a `observaciones_eliminadas` vía `DeletedObservation::moveToTrash()` (recuperable). El **único** delete irreversible es `api/deleted.php?action=permanent_delete` (y su variante `_multiple`), que requiere `confirm_irreversible: true` en el cuerpo. Nunca se saltea ese flag.
4. Desde commit `a9728da`, el único `DELETE FROM observaciones` en el código es `models/DeletedObservation.php:76` (INSERT en trash primero). No hay transacción alrededor del INSERT+DELETE.
5. **Import valida asignación mensual** igual que creación manual (`api/import.php:183` llama a `tieneAsignacionParaMes()`).
6. `codigo_hoja` (hoja REM) es obligatorio a menos que `tipo_error === 'S/OBSERVACION'`.
7. Un registrador solo puede editar sus **propias** observaciones, pero en **cualquier estado**; editar una que no es `pendiente` la resetea a `pendiente` (`api/observations.php:172-175`).
8. **Aprobar requiere elegir un resultado:** `api/supervision.php?action=approve` exige `estado_resultante` (`sin_observacion` → `aprobado` + `S/OBSERVACION`; `error` → `error` + `ERROR`). No hay `reject` directo (devuelve 403 por diseño); `rechazado` se alcanza vía `?action=cancel`.

---

## 6. Cómo trabajar con este repositorio

### Instalación local

```powershell
git clone https://github.com/rogarces85/Sistema_ObservacionesV3.git
cd Sistema_ObservacionesV3
composer install
```

Crear `config/.env.local.php` (gitignorado, nunca subir):

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

Aplicar esquema de BD ejecutando los archivos SQL en orden (ver `README.md` sección "Instalación Local Con XAMPP"). **No usar `deploy/migrate.sh` para fresh installs.**

Verificar sintaxis PHP antes de commitear:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

### Flujo de trabajo (rsc-harness)

El proyecto tiene instalado **rsc-harness** (v4.1.5, target `deepseek`, workflow `branches`).

- La rama `main` está **cerrada** para cambios directos del agente.
- Antes de cada cambio de código, preguntar: ¿esta rama, una nueva, o `rsc main unlock`? Nunca hacer branch solo.
- Tres carriles (lanes):
  1. **Answer** — solo explicar/investigar, no escribir nada.
  2. **FTD** (Fast-Track Development) — cambio ordinario: un feature document en `02-DOCS/`, checklist, evidencia, next step.
  3. **ASDD** — cambios grandes/complejos: cadena specify → clarify → plan → tasks → analyze → implement → review → ship.

### Comandos útiles de rsc

```powershell
npx @ericrisco/rsc doctor          # salud del harness
npx @ericrisco/rsc list            # skills instaladas
npx @ericrisco/rsc add <id>        # instalar una skill adicional
npx @ericrisco/rsc repair          # reparar harness
npx @ericrisco/rsc reassess        # reevaluar componentes diferidos
```

---

## 7. Documentación del proyecto

| Ruta | Contenido |
|------|-----------|
| `README.md` | Documentación general del sistema (instalación, arquitectura, historia) |
| `CLAUDE.md` | Instrucciones técnicas para desarrollo (en inglés) |
| `CHANGELOG.md` | Historial de cambios por versión |
| `SECURITY.md` | Modelo de amenazas y controles de seguridad |
| `DEPLOY.md` | Guía de despliegue a producción |
| `OPERATIONS.md` | Runbook de operaciones y monitoreo |
| `DESIGN.md` | Especificación de diseño visual (paleta DEIS Osorno) |
| `PRODUCT.md` | Especificación de producto (usuarios, alcance, restricciones) |
| `docs/spec/BASELINE.md` | Línea base autoritativa: propósito, diccionario de datos, 17 reglas de oro |
| `docs/spec/VERDAD-ACTUAL.md` | Modelo de datos exacto, 47 reglas de negocio, matriz de autorización |
| `docs/spec/openapi.yaml` | Contrato formal de la API (OpenAPI 3.1.0, 15 endpoints) |
| `docs/spec/AUDITORIA-CODIGO-MUERTO.md` | Inventario de código muerto con evidencia y riesgo |
| `02-DOCS/wiki/index.md` | Tabla de contenidos de la wiki interna |
| `02-DOCS/wiki/sistema/resumen.md` | Resumen completo del sistema |
| `02-DOCS/wiki/sdd/constitution.md` | Constitución del proyecto (reglas no negociables) |
| `02-DOCS/wiki/harness/` | Configuración de rsc-harness (perfil, plan, decisiones) |

> Donde `README.md` o `CLAUDE.md` discrepen con `docs/spec/`, gana `docs/spec/`.

---

## 8. Estructura de directorios (raíz)

```
api/             # Endpoints JSON y descargas (15 archivos)
assets/          # CSS, JS, imágenes, librerías frontend
config/          # Configuración BD, constantes, migraciones SQL
includes/        # Shell compartido (header, sidebar, footer, csrf, mailer)
models/          # Modelos PHP con SQL embebido (Database singleton)
specs/           # Especificaciones de features (001, 002)
tools/           # Scripts manuales (seed_demo, verify_model, etc.)
views/           # Vistas PHP renderizadas en servidor
02-DOCS/         # Conocimiento interno del proyecto (rsc-harness)
01-TOOLS/        # Conexiones a herramientas externas (rsc-harness)
.rsc/            # Motor de rsc-harness (estado de máquina)
.rsc.json        # Configuración de rsc-harness
```

---

## 9. Archivos que NO deben estar en el repositorio

Ya están en `.gitignore`:

- `config/.env.local.php` — credenciales de BD
- `vendor/` — dependencias Composer
- `uploads/` — archivos de runtime
- `*.bak` — respaldos locales
- `logs/`, `*.log` — registros
- `.rsc/` — estado interno de rsc-harness (opcional compartir)

---

## 10. Glosario rápido

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

<!-- rsc-suggest:start -->

# rsc-suggest — la capa siempre activa

Tu cuerpo es inyectado al inicio de **cada** sesión y nuevamente después de cada compactación, así que eres la pieza garantizada de estar presente antes que cualquier otra skill sea igualada. Dos trabajos, en orden:

1. **Clasificar cada turno en uno de tres carriles** antes de que se escriba algo.
2. **Mantener la sesión equipada** — detectar la skill que la tarea necesita pero el usuario no tiene.

## 1. El decisor: clasificar el turno antes de actuar

Cada turno toma uno de tres carriles, y nombras el que tomaste en una línea, en el idioma de la persona (los nombres de carril son etiquetas para traducir).

**Answer** — la solicitud pide información: explicar, comparar, investigar, auditar, revisar, recomendar. Es de solo lectura: no escribir nada, no crear artefactos, no delegar escrituras. Pedirte que *pienses* en construir algo sigue siendo este carril; solo pedir construir autoriza el cambio. Cuando la intención de cambio es ambigua, haz una pregunta y quédate aquí — la ambigüedad ralentiza el carril, nunca lo eleva.

**Tú eliges el carril; nunca entregues esa elección a la persona.** Simple → **FTD**: un documento de característica en `02-DOCS` (intención, alcance, checklist, evidencia, siguiente paso), tareas marcadas como completadas contra prueba observada. Grande, o decisiones que se afectan entre sí → **ASDD**: entrar en la cadena; la persona aprueba el especificado y clarify, luego elige manual o autopiloto. Di cuál y por qué en una línea; cambia si se pide. → `../ftd/SKILL.md` · `../asdd/SKILL.md`.

**Dónde.** Rama principal abierta (elegido al instalar, o nada complejo) → trabajar en ella, sin pregunta de rama. Cerrada («ramas y PR», CI, equipo) → antes de cada cambio de código preguntar: esta rama, una nueva, o `rsc main unlock`? Nunca hacer branch solo. Otra sesión aquí → `.worktrees/<branch>`.

Juzga el **significado**, no la redacción: el disparador es semántico en cualquier idioma. Una corrección de error que restaura el comportamiento previsto es `debug`. El consentimiento de autopiloto cubre toda una ejecución — avanzar sin volver a preguntar.

¿Falta la skill del carril? Ofrecerla (§2) primero. ASDD registrado como diferido en `.rsc.json`? Ejecutar `npx @ericrisco/rsc@latest reassess`; silencioso en `RSC_REASSESSMENT_NO_CHANGE`, y en nueva evidencia mostrar el comando del plan — ASDD necesita un nuevo ID de plan aceptado, nunca agregado silenciosamente.

## 2. Mantener la sesión equipada

Cuando la tarea necesita una capacidad que el usuario **no tiene instalado** — construir, crear, automatizar, analizar, conectar, enviar, asegurar, enseñar, gobernar o documentar algo — nombrarla y ofrecerla. Esto ocurre a mitad de la conversación, no solo al inicio del proyecto.

1. `npx @ericrisco/rsc catalog --available` lista cada skill no instalada como `id  disponible  descripción corta`.
2. Elegir la mejor opción única **por significado**, como lo harías al asociar una solicitud con la experiencia de un compañero de equipo, incluso cuando no haya superposición de palabras clave. Si nada encaja genuinamente, decirlo y seguir: una sugerencia tangencial es peor que ninguna.
3. Preguntar una vez, claramente: "Para esto instalaría `<id>`, que aún no tienes. ¿La instalo? (sí/no)".
4. Al sí, ejecutar `npx @ericrisco/rsc add <id>`, luego continuar con la tarea original.

Una sugerencia a la vez, y nunca para interrumpir un flujo con algo opcional. Nunca recomendar algo ya instalado (`npx @ericrisco/rsc list`).

`npx @ericrisco/rsc consult "<tarea>"` es una **pista léxica** solo: falla en la intención en lenguaje natural y no inglesa. Su silencio nunca significa "no existe una skill"; el catálogo más tu criterio deciden.

### Brecha de automatización — después del trabajo

¿El trabajo entregado es un **procedimiento repetible**? Antes de proponer *construir* algo, ejecutar `npx @ericrisco/rsc capabilities`: ¿cubierto por una skill o agente → usarlo, no decir nada. ¿No cubierto → una línea, skill o agente. Reglas: `skill-scout`.

---

<!-- rsc-suggest:end -->

---

**📍 Dónde estás:** AGENTS.md del Sistema de Observaciones REM — documentación completa y actualizada.

**➡️ Siguiente:** revisar que los archivos borrados (`faltantes.md`, `SUBIR-A-GITHUB.md`, `SUBIR-A-GITHUB.ps1`) no se extrañen, y confirmar si quieres que actualice también `README.md` con el estado actual de rsc-harness y la eliminación del módulo Versionado.
