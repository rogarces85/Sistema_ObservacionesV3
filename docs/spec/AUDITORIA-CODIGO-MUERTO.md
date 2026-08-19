# Auditoría de Código Muerto — Sistema Observaciones REM

**Fase 1 de 4** · Rama `feature/reportes-informe-exportaciones` · HEAD `a3711be`

> **Este documento no elimina nada.** Es un inventario de candidatos con la evidencia que
> respalda cada uno. La decisión de borrar es tuya, y el orden sugerido está al final.

## Método

Tres pasadas independientes, cruzadas entre sí:

1. **Grafo de código** (`codebase-memory-mcp`, re-indexado al inicio) para obtener el conjunto
   inicial de símbolos sin llamadores.
2. **Verificación por `grep` sobre todo el repositorio** — autoritativa. El grafo resultó
   incompleto: omitió `api/boletin.php`, `views/boletin.php`, `includes/validators.php` y
   `tools/*.php` aunque están versionados en git. Ningún hallazgo de este informe depende
   únicamente del grafo.
3. **Resolución manual de llamadas dinámicas**: las plantillas del frontend construyen URLs con
   interpolación (`api/supervision.php?action=${action}`), invisibles a la búsqueda literal. Cada
   una se resolvió leyendo qué valores recibe la variable en tiempo de ejecución.

**Alcance:** ~15.800 líneas PHP (`api/`, `models/`, `includes/`, `views/`, `index.php`,
`worker_reportes.php`, `config/`), más `assets/js/` y los `.sql`.

## Resumen ejecutivo

| Categoría | Elementos | Líneas | Riesgo |
|---|---:|---:|---|
| A. Archivos completos muertos | 2 | 319 | Bajo |
| B. Métodos PHP sin ningún llamador | 13 | ~283 | Bajo–Medio |
| C. Funciones JS sin ningún llamador | 19 | 154 | Bajo |
| D. Acciones de API inalcanzables desde la UI | 7 | ~90 | Medio |
| E. Subsistema huérfano (cola de reportes) | 3 archivos | ~377 | Alto (decisión de producto) |
| F. Documentación que contradice al código | 6 afirmaciones | — | Bajo |
| G. SQL duplicado / mal ubicado | 2 archivos | 38 | Bajo |
| H. Fuga de credenciales en la UI de login | 1 bloque | 17 | **Corregir ya** |
| I. CSS deprecado aún cargado | 1 archivo | 1650 | Alto (requiere migración) |

**Total eliminable con riesgo bajo o medio: ~890 líneas.**

---

## A. Archivos completos muertos

Nadie hace `require`/`include` de ellos, y ninguno de sus símbolos aparece en el resto del repo.
No pueden ejecutarse nunca en el estado actual.

| Ruta | Líneas | Evidencia | Riesgo | Acción |
|---|---:|---|---|---|
| `includes/icons.php` | 113 | `grep -rn "icons.php"` → 0 resultados fuera del propio archivo. `grep -rn "icon_"` en `api models views includes assets` → 0 resultados. Sus 14 funciones (`icon_svg`, `icon_chart_bar`, …) no se invocan. La UI usa el webfont `@tabler/icons-webfont` (`<i class="ti ti-…">`) en su lugar. | Bajo | Eliminar el archivo |
| `includes/validators.php` | 206 | `grep -rn "validators.php"` → **nadie lo requiere**. `grep -rn "Validators::"` → 0 resultados fuera del archivo. Clase `Validators` con 10 métodos estáticos. Añadido en el commit `4b417c1` y nunca conectado. | Bajo | Eliminar, **o** cablearlo en `api/*.php` si la intención era centralizar validación |

> **Nota sobre `validators.php`:** antes de borrarlo, decide si el objetivo original sigue vigente.
> Hoy la validación está duplicada a mano en cada endpoint; este archivo era el intento de
> centralizarla. Borrarlo es correcto si se descarta ese camino; conectarlo es la alternativa.

---

## B. Métodos PHP sin ningún llamador

Verificados uno por uno: cada nombre aparece **exactamente una vez** en todo el repositorio —
su propia definición.

| Ruta:línea | Símbolo | Líneas | Evidencia | Riesgo |
|---|---|---:|---|---|
| `includes/csrf.php:44` | `CSRF::getTokenField()` | 9 | Sin llamadores. La UI inyecta el token vía `<meta name="csrf-token">`, no por campo oculto. | Bajo |
| `includes/csrf.php:65` | `CSRF::regenerateToken()` | 9 | Sin llamadores. | Bajo |
| `includes/csrf.php:53` | `CSRF::clearToken()` | 12 | **Muerto por transitividad**: su único llamador es `regenerateToken()` (línea 67), que también está muerto. Solo eliminable junto con él. | Bajo |
| `models/Location.php:38` | `getComunaById()` | 9 | Sin llamadores. | Bajo |
| `models/Location.php:82` | `getEstablecimientoById()` | 12 | Sin llamadores. | Bajo |
| `models/Location.php:94` | `searchEstablecimientos()` | 15 | Sin llamadores. La búsqueda de establecimientos se hace en el cliente. | Bajo |
| `models/Location.php:109` | `createComuna()` | 16 | Sin llamadores. Las comunas se cargan por SQL (`config/migration_2026_05_08_limpieza_comunas.sql`), no por la app. | Bajo |
| `models/User.php:73` | `isActive()` | 10 | Sin llamadores. El estado activo se filtra dentro del `SELECT` de `authenticate()`. | Bajo |
| `models/EstablecimientoAsignacion.php:410` | `getIdsAsignados()` | 12 | Sin llamadores. | Bajo |
| `models/EstablecimientoAsignacion.php:542` | `getReferentesMultiple()` | 15 | Sin llamadores. La UI usa `getReferentes()` (singular), vía `assignments.php?action=referentes`. | Bajo |
| `models/Exporter.php:240` | `exportToCSV()` | 27 | Sin llamadores. `api/export.php` solo despacha `excel`/`xlsx`/`pdf`; no existe una rama CSV. | Bajo |
| `models/Observation.php:334` | `deleteWithAudit()` | 22 | Sin llamadores. El borrado del supervisor va por `DeletedObservation::moveToTrash()` (papelera); el borrado duro va por `Observation::delete()`. Esta tercera vía quedó huérfana. | Medio |
| `models/Observation.php:950` | `reporteErroresPorSerie()` | 32 | Sin llamadores. Reemplazado por `reportePorSerieDetalle()`, que es lo que sirve `?report=serie_detalle` y la clave `errores_serie` de `?report=error-reports`. | Medio |
| `models/Observation.php:982` | `reporteErroresPorHoja()` | 34 | Sin llamadores. Reemplazado por `reportePorHojaDetalle()`. | Medio |

### No incluidos deliberadamente

- **`Database::__clone()` y `Database::__wakeup()`** (`models/Database.php:138,145`) aparecen sin
  llamadores en el análisis estático, pero **no son código muerto**: son métodos mágicos que
  invoca el runtime de PHP y constituyen la guarda del Singleton (impedir clonado y
  deserialización). Eliminarlos abriría un agujero real. **No tocar.**
- **`CLASIF_CORREGIDO`** figura sin uso externo, pero se consume dentro de `config/constants.php`
  (líneas 168 y 210) como clave del mapa `$CLASIFICACIONES` y en `esCorregido()`. Está vivo.
- **Los 17 métodos `reporte*` de `Observation.php`** parecen no tener llamadores por acción, pero
  todos se ejecutan en la rama `case 'all': default:` de `api/reports.php:172`. Están vivos
  (ver hallazgo D-2).

---

## C. Funciones JavaScript sin ningún llamador

`assets/js/app.js` arrastra una capa de utilidades que nunca se conectó. Verificado con `grep`
sobre `assets/js`, `views` e `includes` — incluye los `onclick=` en HTML inline.

| Ruta:líneas | Símbolo | Líneas |
|---|---|---:|
| `assets/js/app.js:173-178` | `formatNumber` | 6 |
| `assets/js/app.js:181-188` | `formatCurrency` | 8 |
| `assets/js/app.js:191-197` | `formatPercent` | 7 |
| `assets/js/app.js:200-202` | `pluralize` | 3 |
| `assets/js/app.js:234-236` | `preventDoubleSubmit` | 3 |
| `assets/js/app.js:238-240` | `markFormSubmitting` | 3 |
| `assets/js/app.js:242-244` | `unmarkFormSubmitting` | 3 |
| `assets/js/app.js:247-254` | `disableSubmitButton` | 8 |
| `assets/js/app.js:256-261` | `enableSubmitButton` | 6 |
| `assets/js/app.js:264-271` | `validateTextLength` | 8 |
| `assets/js/app.js:282-295` | `fetchAPIWithRetry` | 14 |
| `assets/js/app.js:298-305` | `validateCheckboxSelection` | 8 |
| `assets/js/app.js:308-315` | `withTimeout` | 8 |
| `assets/js/app.js:320-330` | `debounce` | 11 |
| `assets/js/app.js:333-342` | `throttle` | 10 |
| `assets/js/app.js:345-347` | `onAnimationFrame` | 3 |
| `assets/js/app.js:350-369` | `observeElements` | 20 |
| `assets/js/app.js:387-408` | `validateForm` | 22 |
| `assets/js/charts.js:44-46` | `estadoToken` | 3 |

**Total: 154 líneas.** Riesgo **bajo**: son funciones puras, sin efectos secundarios ni registro
en `window` por otra vía.

> `debounce` y `throttle` merecen una pausa: son utilidades genuinamente útiles y su ausencia de
> uso puede indicar un problema (filtros que disparan una petición por tecla) más que código
> sobrante. Recomiendo revisar los manejadores de filtro en `views/reportes.php` y
> `views/supervision.php` antes de borrarlas.

---

## D. Acciones de API inalcanzables desde la interfaz

**Advertencia de riesgo:** "inalcanzable desde la UI" no equivale a "sin uso". Estos endpoints
son HTTP y podrían estar siendo consumidos por un script externo, una integración o un marcador
guardado. Antes de eliminar, revisa los logs de acceso de Apache en producción.

### D-1 · Acciones sin ningún llamador

| Endpoint | Acción | Evidencia | Riesgo |
|---|---|---|---|
| `api/auth.php:79` | `check` | 0 referencias reales en `views/` y `assets/js`. | Medio |
| `api/locations.php:39` | `comunas` | 0 referencias. Las vistas llaman a `Location::getComunas()` en el servidor. | Medio |
| `api/locations.php:44` | `get_establecimientos` | 0 referencias. La UI usa `?action=establecimientos` (10 usos). | Medio |
| `api/locations.php:56` | `establecimientos_all` | 0 referencias. | Medio |
| `api/reports.php:145` | `filtros` | 0 referencias. Las coincidencias de `grep` eran la palabra "filtros" en textos de UI. | Medio |
| `api/supervision.php:210` | `update_status` | 0 referencias. **Resuelto manualmente**: `views/supervision.php:684` construye `?action=${action}`, y `performAction` solo se invoca con `'approve'`, `'cancel'` y `'delete'` (líneas 585, 590, 594, 599, 603, 608). Legado del flujo anterior; además acepta cualquier cadena en `estado` sin validarla contra el enum. | Medio |

> **Corregido durante la Fase 2:** en la primera pasada marqué `api/supervision.php:139`
> (`reject`) como acción muerta. **Es incorrecto.** No es código sobrante, es un *stub
> deliberado* que devuelve `403` con el mensaje "Acción no permitida. Los supervisores solo
> pueden aprobar observaciones." Está ahí a propósito para cerrar la vía de rechazo directo.
> **No eliminar.**

### D-2 · `case 'all': default:` en `api/reports.php:172` — no es código muerto, es un defecto

La rama `default` comparte cuerpo con `case 'all'` y ejecuta **17 consultas de reporte** en una
sola petición. Consecuencias:

- Cualquier valor de `?report=` no reconocido — un error de tipeo, un enlace viejo, una petición
  malformada — dispara los 17 reportes contra la base de datos en lugar de devolver un 400.
- Es la razón por la que ningún método `reporte*` aparece como muerto: todos son alcanzables por
  esta vía aunque la UI no los pida nunca.

**Acción sugerida:** separar `default` de `case 'all'` y devolver `400 Bad Request` para acciones
desconocidas. Esto es un cambio de comportamiento, no una limpieza — pertenece a un trabajo
aparte, pero debe quedar registrado en la línea base (Fase 4).

---

## E. Subsistema huérfano: cola asíncrona de reportes

| Componente | Líneas | Estado |
|---|---:|---|
| `api/report_queue.php` | 86 | **Ninguna referencia** en `views/` ni `assets/js`. |
| `models/ReportQueue.php` | 134 | Solo lo usan `api/report_queue.php` y `worker_reportes.php`. |
| `worker_reportes.php` | 157 | Script de cron. `php -l` limpio. |
| Tabla `reportes_pendientes` | — | Creada en `config/migrations/sprint4_migration.sql`. |

El subsistema está **completo y sintácticamente sano**, pero desconectado de la interfaz: nada
en la UI encola un reporte. `deploy/healthcheck.sh:74` sí monitorea la tabla, así que la
operación lo da por vivo.

**Riesgo alto — es una decisión de producto, no de limpieza.** O se conecta a la UI, o se retira
completo (los cuatro componentes más la entrada de cron y la línea del healthcheck). Retirar
solo una parte deja el monitoreo apuntando a una tabla fantasma.

### Corrección importante a la documentación

`README.md` y `CLAUDE.md` afirman que `worker_reportes.php` tiene un bug de `$this->db` usado
fuera de contexto de clase. **Es falso hoy:** `grep -n 'this->' worker_reportes.php` no devuelve
nada y el archivo usa `$db = Database::getInstance()` en la línea 19. El bug ya fue corregido y
la documentación quedó atrás.

---

## F. Documentación que contradice al código

| Ubicación | Afirmación | Realidad | Acción |
|---|---|---|---|
| `README.md` (líneas 26, 173, 828) | `A.md` es la documentación histórica del sistema | El archivo **no existe** en el repositorio | Eliminar las 3 referencias |
| `CLAUDE.md:39` | `Version` es un modelo de dominio vigente | No existe: `models/Version.php`, `api/versioning.php` y `views/versionado.php` fueron eliminados, e `index.php:29` no incluye `versionado` en la whitelist | Quitar de la lista |
| `CLAUDE.md:42` y `README.md` (2 menciones) | El worker tiene el bug `$this->db` | Ya corregido (ver E) | Corregir |
| `README.md` (5 menciones) | El reseteo de contraseña fija `admin123` | `api/users.php` usa `generateRandomPassword()` y `validatePasswordPolicy()`. `admin123` solo sobrevive en `config/init_db.sql:154` (datos semilla) y en `views/login.php` (ver H) | Corregir |
| Documentación nueva ausente | — | `api/boletin.php`, `views/boletin.php`, `includes/validators.php` y `config/migration_2026_08_12_boletin.sql` no están documentados en ninguna parte | Documentar en Fase 4 |

Los mismos módulos muertos se citan en `CHANGELOG.md`, `docs/prs/`, `docs/releases/` y
`specs/002-fix-button-actions/`. **No los toques**: son registros históricos y deben reflejar lo
que era cierto cuando se escribieron.

> **Corregido durante la Fase 4:** en la primera pasada afirmé que `README.md` describía en
> cuatro lugares un módulo de *versionado* inexistente. **Es incorrecto.** Esas cuatro
> apariciones (`README.md:599, 868, 891, 895`) hablan del *versionado de esquema de base de
> datos* — la propuesta de crear un migrador — que es un tema distinto y sigue vigente. El
> README **no** menciona el módulo eliminado. La afirmación falsa estaba solo en `CLAUDE.md:39`.

---

## G. SQL duplicado y mal ubicado

| Hallazgo | Evidencia | Acción |
|---|---|---|
| ~~`config/sprint3_migration.sql` y `specs/sprint3_migration.sql` idénticos byte a byte~~ **Corregido** | Se conservó `config/sprint3_migration.sql` y se eliminó la copia de `specs/`, junto con sus referencias en `deploy/migrate.sh` y `DEPLOY.md` | Hecho |
| `sprint3` está fuera de sitio | `sprint1`, `sprint2` y `sprint4` viven en `config/migrations/`; `sprint3` quedó en `config/` | Sin mover: se dejó en `config/` para no romper más referencias mientras `deploy/migrate.sh` siga sin validar (ver abajo) |

### Hallazgo grave descubierto al desduplicar: `deploy/migrate.sh` está roto

El script aborta en una instalación nueva. Referencia **cuatro archivos que no
existen** y omite uno que sí hace falta:

| Entrada en `migrate.sh` | Realidad |
|---|---|
| `specs/sprint1_migration.sql` | Movido a `config/migrations/` en el commit `d514e8a` |
| `specs/sprint2_migration.sql` | Movido a `config/migrations/` |
| `specs/sprint4_migration.sql` | Movido a `config/migrations/` |
| `specs/sprint5_migration.sql` | **Eliminado**: creaba `versiones_sistema`, la tabla del módulo Versionado que ya no existe |
| `config/migration_2026_08_12_boletin.sql` | **Ausente de la lista**, pese a ser necesaria |

Como el bucle hace `if [ ! -f "$path" ]; then exit 1; fi`, el script muere en la
primera entrada ausente.

Y hay un **segundo problema, de orden**: `config/migrations/add_tipo_asignacion.sql`
se aplica en la posición 3, pero hace `ALTER TABLE asignaciones_establecimientos`,
y esa tabla la crea `config/create_asignaciones_table.sql`, que está en la
posición **última**. Además `create_asignaciones_table.sql` ya define las columnas
`meses` y `tipo_asignacion`, así que `sprint2_migration.sql` y
`add_tipo_asignacion.sql` fallarían con "Duplicate column name" si se ejecutaran
después de él.

**Por qué no lo arreglé aquí:** corregir el orden exige validarlo contra una base
desechable, y los archivos contienen `USE observaciones_rem;` — uno de ellos hace
`DELETE FROM comunas`. Ejecutarlos en esta máquina habría operado sobre la base
real. Reordenar sin validar sería peor que dejarlo documentado. **Requiere trabajo
aparte con una base de pruebas aislada.**

**Impacto real acotado:** esto solo afecta a instalaciones **nuevas**. La base de
producción actual ya tiene el esquema aplicado y no corre riesgo por este defecto.

Sin herramienta de migraciones, el orden de aplicación depende de la documentación. Un duplicado
en dos rutas distintas es exactamente el tipo de ambigüedad que provoca aplicar dos veces la
misma migración.

---

## H. Credenciales de prueba expuestas en el login — ✅ CORREGIDO

> **Estado: resuelto.** El bloque ahora está condicionado a entorno de desarrollo:
> `views/login.php` lo envuelve en `if (defined('ENVIRONMENT') && ENVIRONMENT === 'development')`.
> Verificado renderizando la página completa: con `ENVIRONMENT=production` las cadenas
> `admin123` y `supervisor1` **no aparecen en el HTML**. La guarda falla cerrado en los tres
> modos de fallo (constante en `production`, vacía, o no definida — este último caso cubre el
> acceso directo a `views/login.php`, donde `config/config.php` no llega a cargarse).
>
> **Queda pendiente y depende de ti:** rotar las contraseñas de las cuentas semilla
> (`supervisor1`, `registrador1..4`) si siguen existiendo en producción con `admin123`. Eso
> requiere tocar la base de datos y no se hizo aquí.

El texto original del hallazgo, para referencia:

`views/login.php:126-141` renderizaba un bloque `<details>` con credenciales reales:

```
supervisor1 / admin123
registrador2 / admin123
```

Se mostraba **sin ninguna guarda de entorno**: cualquier visitante de la pantalla de login en
producción las veía. Y `config/init_db.sql:154` confirma que son las contraseñas semilla reales.

Esto excedía el alcance de una auditoría de código muerto, pero fue lo más grave que apareció.

### Hallazgo derivado, también corregido

Al cerrar este punto salió a la luz que **tres documentos afirmaban que el reseteo de contraseña
deja `admin123`** — `OPERATIONS.md:112`, `SECURITY.md:66` y `faltantes.md:12`. Es falso:
`api/users.php:230` usa `Mailer::generateRandomPassword(12)`, que garantiza mayúscula,
minúscula, dígito y símbolo, exige `confirm_reset` y prohíbe que un supervisor se restablezca a
sí mismo. Los tres documentos quedaron corregidos, junto con un comentario obsoleto en
`api/users.php:213` que decía "restablecer a credencial por defecto".

Esos tres archivos no estaban en el alcance de la Fase 1, que solo revisó `README.md` y
`CLAUDE.md`. Eleva a **diez** el total de afirmaciones falsas encontradas en la documentación.

---

## I. CSS deprecado aún en carga

`assets/css/styles.css` (1650 líneas) está marcado como deprecado por
`.specify/memory/constitution.md`, que prohíbe añadirle nada. Sin embargo **se sigue cargando**:

- `includes/header.php:51`
- `views/login.php:18`

No es código muerto: sus reglas están activas y probablemente sostienen parte del diseño actual.
Es deuda declarada. **Riesgo alto:** eliminarlo sin migrar sus reglas a `tokens.css` /
`tabler-override.css` rompería la interfaz. Requiere un trabajo de migración propio, no un
borrado.

---

## Orden de limpieza sugerido

Cada paso es un commit independiente y verificable por separado.

1. **Seguridad, primero y solo** — quitar el bloque de credenciales de `views/login.php` (H) y
   rotar las contraseñas si esas cuentas viven en producción.
2. **Archivos completos muertos** — `includes/icons.php` y `includes/validators.php` (A, 319
   líneas). Riesgo mínimo: nadie los incluye, no pueden ejecutarse.
3. **Funciones JS sin uso** (C, 154 líneas), decidiendo antes qué hacer con `debounce`/`throttle`.
4. **Métodos PHP sin llamador** (B, ~283 líneas), recordando que `clearToken` solo se va junto con
   `regenerateToken`, y sin tocar `__clone`/`__wakeup`.
5. **Sincronizar la documentación** (F) — se resuelve en la Fase 4 de este mismo trabajo.
6. **Desduplicar el SQL** (G).
7. **Acciones de API inalcanzables** (D-1) — solo después de revisar los logs de acceso.
8. **Decidir sobre la cola de reportes** (E) — conectar o retirar por completo, nunca a medias.
9. **Migrar `styles.css`** (I) — proyecto aparte.

## Verificación después de cada borrado

```bash
# Lint de sintaxis de todo el PHP (exigido por SECURITY.md)
find . -name '*.php' -not -path './vendor/*' -exec php -l {} \;

# Confirmar que el símbolo eliminado no dejó referencias colgando
grep -rn "NOMBRE_DEL_SIMBOLO" --include='*.php' --include='*.js' --include='*.html' .
```

Y una pasada manual por las pantallas afectadas: login, dashboard, observaciones, supervisión,
reportes (las cinco pestañas), asignaciones, eliminadas y boletín.
