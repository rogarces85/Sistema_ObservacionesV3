# Línea Base del Sistema — Observaciones REM

**Fase 4 de 4** · Rama `feature/reportes-informe-exportaciones` · HEAD `a3711be`
**Estado del sistema: en producción con datos reales.**

Este documento acompaña a `docs/spec/openapi.yaml` y describe el sistema tal como funciona hoy.
Junto con ese contrato, constituye la fuente de verdad para cualquier cambio futuro.

| Documento | Contiene |
|---|---|
| `docs/spec/openapi.yaml` | Contrato formal de la API (OpenAPI 3.1.0, validado) |
| `docs/spec/VERDAD-ACTUAL.md` | Modelo de datos, 47 reglas de negocio citadas, matriz de autorización |
| `docs/spec/AUDITORIA-CODIGO-MUERTO.md` | Código muerto detectado, con evidencia y nivel de riesgo |
| **`docs/spec/BASELINE.md`** | **Este documento**: propósito, diccionario, flujos y reglas de oro |

---

## 1. Propósito del sistema

El Sistema de Observaciones REM permite al **Servicio de Salud Osorno** llevar el control de las
observaciones detectadas en los **REM** (Resumen Estadístico Mensual) que reportan los
establecimientos de salud de su red.

Cada mes, los establecimientos de las siete comunas del servicio — Osorno, Puerto Octay,
Purranque, Puyehue, Río Negro, San Juan de la Costa y San Pablo — entregan sus planillas REM. Al
revisarlas aparecen errores: totales que no cuadran, hojas incompletas, entregas fuera de plazo,
datos cargados sin pasar por el validador DEIS. El sistema registra cada uno de esos hallazgos,
sigue su corrección y produce los informes y el boletín que se devuelven a los establecimientos.

### Los dos roles

**Registrador.** Revisa los REM de los establecimientos que tiene asignados y registra las
observaciones que encuentra. Solo ve y edita **lo suyo**: la consulta de base de datos le añade
siempre `AND o.usuario_registro_id = ?`, y eso alcanza también a sus reportes y estadísticas.
Solo puede registrar observaciones de establecimientos que tenga asignados **para ese mes
concreto**.

**Supervisor.** Revisa las observaciones pendientes y las resuelve. Ve todo el sistema y
administra usuarios, asignaciones y establecimientos. Genera los informes y el boletín
institucional.

### Qué es el sistema, técnicamente

Monolito PHP sin framework: `index.php` despacha las páginas contra una whitelist, `api/*.php`
son endpoints JSON autónomos, `models/*.php` encapsulan el SQL vía PDO, y todo pasa por un único
`Database` singleton. Base de datos MySQL. Interfaz con Tabler 1.4.

**No hay suite de pruebas automatizadas.** La verificación es el lint de sintaxis
(`php -l`) más la prueba manual. Esto condiciona todo lo que sigue: no hay red de seguridad que
detecte una regresión.

---

## 2. Diccionario de datos

Nueve tablas. El esquema completo, con tipos e índices, está en `VERDAD-ACTUAL.md` §1.

### Observación — `observaciones`

El corazón del sistema. Un hallazgo concreto sobre una hoja REM de un establecimiento en un mes.

Se identifica por **año + mes + establecimiento + serie + hoja**, y describe qué se encontró
(`tipo_error`, `detalle_observacion`), si la entrega llegó a tiempo (`plazo_entrega`), si se usó
el validador DEIS (`usa_validador`), qué respondió el establecimiento
(`respuesta_establecimiento`) y en qué punto del flujo está (`estado_actual`).

Dos particularidades que sorprenden:

- **El mes se guarda como texto en español** — "Enero", no `1`. Las asignaciones, en cambio,
  guardan números. Traducir entre ambos es responsabilidad de `getMesNumero()`.
- **`codigo_hoja` es obligatorio, salvo cuando `tipo_error` es `S/OBSERVACION`.** Tiene sentido:
  "sin observación" significa que no hubo hallazgo en ninguna hoja concreta.

### Establecimiento y Comuna — `establecimientos`, `comunas`

Los centros de salud (hospitales, CESFAM, postas rurales) y las siete comunas del servicio, con
sus códigos oficiales DEIS (10301–10307).

**Los establecimientos nunca se borran.** Se desactivan con la bandera `activo`, para que las
observaciones históricas que los referencian sigan resolviendo su nombre y su comuna. Lo mismo
vale para las comunas: la clave foránea es `ON DELETE RESTRICT`, así que la base impide borrar
una comuna que todavía tenga establecimientos.

### Asignación — `asignaciones_establecimientos`

Define **quién es responsable de qué establecimiento, en qué año y en qué meses**. Es la pieza
más sutil del sistema y la que más fácil se rompe.

Hay dos tipos:

- **Anual** — la responsabilidad base del año. En la práctica siempre cubre `ALL` (todos los
  meses), porque el código fuerza ese valor para las anuales.
- **Temporal** — una reasignación acotada a ciertos meses, para cubrir vacaciones o licencias.
  **Tiene prioridad sobre la anual y bloquea al titular durante esos meses.**

El campo `meses` guarda un CSV de números (`"6,7"`) o el literal `ALL`.

### Usuario — `usuarios`

Cuenta con `username` único, hash bcrypt de contraseña, nombre, rol y bandera `activo`. Los
inactivos no pueden autenticarse: el filtro está dentro del propio `SELECT` de autenticación.

### Papelera — `observaciones_eliminadas`

Observaciones eliminadas de forma suave. **No es una referencia: es una copia desnormalizada.**
Guarda el nombre del establecimiento, la comuna y el nombre de quien registró **como texto**, sin
claves foráneas, para que el registro sobreviva aunque después cambien las tablas maestras.
Conserva el id original en `observacion_id` para poder restaurar.

### Historial de estados — `historial_estados`

Cada transición de estado: de dónde venía, adónde fue, quién la hizo, con qué comentario y
cuándo. Es la trazabilidad de la revisión. **Se borra en cascada con su observación**, así que un
borrado permanente se lleva también su historia.

### Historial de usuarios — `historial_usuarios`

Auditoría de cuentas: creación, actualización, cambio de contraseña, activación, desactivación y
eliminación, con el usuario responsable de cada acción.

### Notificación — `notificaciones`

Avisos internos por usuario, con marca de leída y URL de destino.

### Dos tablas que conviene conocer por lo que *no* hacen

- **`logs`** está definida en el esquema, pero **ningún modelo escribe en ella**. La auditoría
  real vive en las dos tablas de historial.
- **`reportes_pendientes`** es la cola del generador asíncrono de reportes. El subsistema
  funciona, pero **nada en la interfaz lo usa**. Ver §5.

---

## 3. Flujos críticos

### 3.1 Ciclo de vida de una observación

Es el flujo principal del sistema.

```
                 ┌──────────────────────────────────────────────┐
                 │  El registrador crea la observación          │
                 │  (requiere asignación para ESE mes)          │
                 └──────────────────────┬───────────────────────┘
                                        ▼
                                  ┌───────────┐
              ┌──────────────────▶│ pendiente │◀─────────────────┐
              │                   └─────┬─────┘                  │
              │                         │                        │
              │     El supervisor decide (solo sobre pendientes) │
              │      ┌──────────────────┼──────────────────┐     │
              │      ▼                  ▼                  ▼     │
              │  approve            approve             cancel   │
              │  sin_observacion    error                        │
              │      │                  │                  │     │
              │      ▼                  ▼                  ▼     │
              │  ┌──────────┐      ┌───────┐      ┌────────────┐ │
              │  │ aprobado │      │ error │      │ rechazado  │ │
              │  └────┬─────┘      └───┬───┘      └─────┬──────┘ │
              │       │                │                │        │
              └───────┴────────────────┴────────────────┘        │
                 El registrador edita → vuelve a pendiente ──────┘
```

**Paso a paso:**

1. **Creación.** El registrador completa el formulario. El servidor exige `mes`,
   `establecimiento_id`, `tipo_error`, `detalle_observacion`, `plazo_entrega` y `usa_validador`;
   más `codigo_hoja` salvo si el tipo es `S/OBSERVACION`. Antes de guardar comprueba que tenga
   asignación **para ese establecimiento y ese mes**. Nace en estado `pendiente`.

2. **Espera de revisión.** Aparece en la bandeja del supervisor. Un registrador solo ve las
   suyas.

3. **Resolución.** El supervisor actúa **solo sobre observaciones pendientes**. Al aprobar
   **debe decidir el resultado**, no basta con aprobar:

   | Decisión | Estado final | `tipo_error` que se escribe |
   |---|---|---|
   | `sin_observacion` | `aprobado` | `S/OBSERVACION` |
   | `error` | `error` | `ERROR` |

   Cancelar produce el estado `rechazado`. **No existe un rechazo directo**: la acción `reject`
   devuelve 403 a propósito.

4. **Reapertura.** Un registrador puede editar su observación **en cualquier estado**. Si no
   estaba pendiente, la edición **la devuelve a `pendiente`** y reingresa al flujo. Esto es
   deliberado: permite corregir sin intervención del supervisor, a costa de que una observación
   ya cerrada pueda reabrirse.

5. **Rastro.** Cada transición queda en `historial_estados` con autor, comentario y fecha.

**Operaciones masivas.** El supervisor puede enviar un arreglo de ids. Con un solo id, una
observación no pendiente produce error; **en lote, las no pendientes se omiten en silencio** y la
respuesta solo informa cuántas se procesaron. Si el `count` es menor de lo esperado, esa es la
razón.

### 3.2 Asignación de establecimientos y precedencia temporal

Este flujo determina **quién puede registrar qué**. Equivocarse aquí no rompe el sistema: lo que
hace es dejar a alguien sin poder trabajar, o permitir que registre donde no corresponde.

**Asignación anual.** El supervisor asigna un establecimiento a un registrador para el año. El
sistema fuerza `meses = ALL`. Si el establecimiento ya está asignado a otro usuario para meses
que solapen, la operación **se rechaza**.

**Reasignación temporal.** Para cubrir una ausencia, el supervisor crea una asignación temporal
con meses concretos (`"6,7"`). Aquí está lo que hay que entender:

- Una temporal **sí puede** solapar una anual — ese es su propósito.
- Mientras dure, **bloquea al titular anual** para esos meses. Aunque tenga la anual con `ALL`,
  si otro tiene una temporal que cubre junio, en junio **no puede registrar**.
- Dos temporales que solapen meses en el mismo establecimiento y año **se rechazan**.

**Fusión, no duplicación.** Si ya existe una asignación propia del mismo tipo, no se crea otra
fila: los meses se **fusionan** en una unión ordenada. Si cualquiera de los dos conjuntos es
`ALL`, el resultado es `ALL`.

**Cómo se resuelve el acceso** (`tieneAsignacionParaMes()`), en este orden exacto:

1. Traducir el nombre del mes a número. Si no es un mes válido → **denegar**.
2. ¿Tiene el usuario una asignación **temporal** que cubra ese mes? → **permitir**.
3. ¿Tiene una **anual**? Si no → **denegar**.
4. Si la anual cubre el mes: ¿hay una temporal **de otro usuario** para ese mes? → **denegar**.
5. En caso contrario → **permitir**.

**Copiar de un año a otro.** Existe `copiar_anio` para arrastrar las asignaciones del año
anterior. Los años de origen y destino deben diferir.

### 3.3 Reportes, informe y boletín

Tres salidas distintas que se confunden con facilidad:

**Reportes de pantalla** (`api/reports.php`) — alimentan los gráficos y tablas del módulo de
reportes. El parámetro de despacho es `report`, no `action`. Ambos roles acceden, pero **con
alcance distinto**: para un registrador toda agregación se limita a sus propias observaciones.

Los filtros comunes son `year`, `meses[]`, `comuna_ids[]` y `establecimiento_id`. El reporte
`error-reports` agrupa cinco bloques en una sola petición y devuelve además una clave `errors`
con los bloques que fallaron, de modo que un fallo parcial no deje la pantalla en blanco.

> **Defecto conocido.** En `api/reports.php:172`, la rama `default` comparte cuerpo con
> `case 'all'`. Un valor de `report` no reconocido **no devuelve 400**: ejecuta las 17 consultas
> del reporte completo. Es un defecto, no una decisión de diseño.

**Exportación** (`api/export.php`) — genera Excel o PDF y devuelve el **archivo**, no JSON. Ante
un error emite una página HTML de aviso, porque se invoca abriendo una pestaña nueva.

**Informe de errores y Boletín** (`api/informe_errores.php`, `api/boletin.php`) — documentos
institucionales que se devuelven a los establecimientos. **Solo supervisores**, justamente por
ser documentos que salen de la institución.

### 3.4 Importación masiva desde Excel

1. El registrador descarga la plantilla desde `api/import_template.php`.
2. La completa y la sube por POST multipart a `api/import.php`.
3. El servidor valida **fila por fila** y aplica las **mismas reglas que el alta manual**:
   `rem` (la hoja) obligatorio salvo para `S/OBSERVACION`, y validación de la asignación mensual.
4. Los errores se acumulan y se informan todos juntos: **una fila inválida no aborta el lote**.

**Solo registradores importan.** Es el único endpoint del sistema vedado a los supervisores.

### 3.5 Eliminación: dos niveles, uno irreversible

Este flujo merece atención especial porque es el único que puede perder datos.

```
observaciones ──(soft delete)──▶ observaciones_eliminadas ──(restore)──▶ observaciones
                                            │
                                            └──(permanent_delete)──▶ ✖ IRRECUPERABLE
                                                requiere confirm_irreversible: true
```

**Nivel 1 — Papelera (reversible).** Tanto `observations.php` con DELETE como
`supervision.php?action=delete` mueven la observación a `observaciones_eliminadas`. Se puede
restaurar. Ninguna de las dos vías borra de verdad.

**Nivel 2 — Borrado permanente (irreversible).** Solo `deleted.php` con
`action=permanent_delete` o `permanent_delete_multiple`. **Exige `confirm_irreversible: true` en
el cuerpo**; sin ese campo la petición se rechaza con 400 antes de tocar nada.

Esa confirmación es **la única salvaguarda** del único borrado real del sistema. No hay
papelera de segundo nivel ni ventana de gracia.

---

## 4. Reglas de oro

Restricciones que no deben romperse. Cada una con la consecuencia de romperla.

### Datos

**R1 — El borrado permanente es la única operación irrecuperable. Nunca le quites la
confirmación.**
`confirm_irreversible` es lo único que separa un clic de una pérdida definitiva. Tampoco lo
apliques por defecto en el cliente ni lo "simplifiques" para agilizar la interfaz.
*Si se rompe:* pérdida irreversible de observaciones históricas y de todo su historial de
estados, que se borra en cascada.

**R2 — No conviertas la papelera en borrado duro.**
El DELETE de `observations.php` y el `delete` de `supervision.php` **mueven a la papelera**. Si
alguien los "arregla" para que borren de verdad, se pierde la única red de seguridad del sistema.
*Si se rompe:* cada eliminación pasa a ser definitiva, sin aviso.

**R3 — Los establecimientos, comunas y usuarios no se borran: se desactivan.**
Las observaciones históricas los referencian. Las claves foráneas son `RESTRICT` justamente para
impedirlo.
*Si se rompe:* observaciones huérfanas, o fallos de integridad que bloquean operaciones no
relacionadas.

**R4 — La papelera es una copia desnormalizada. No la "normalices".**
Guarda nombres como texto a propósito, para sobrevivir a cambios en las tablas maestras.
Convertirla en claves foráneas parece más limpio y destruye su razón de existir.
*Si se rompe:* los registros eliminados dejan de poder mostrarse si el establecimiento cambia.

**R5 — Toda migración sobre la base en producción necesita respaldo previo verificado.**
No hay herramienta de migraciones: el SQL se aplica a mano. Y hay trampas reales — por ejemplo,
`sprint2_migration.sql` y `add_tipo_asignacion.sql` intentan añadir columnas que
`create_asignaciones_table.sql` ya crea, **sin guarda de existencia**, y fallan con "Duplicate
column name" según el orden.
*Si se rompe:* una migración a medias deja el esquema inconsistente sin forma automática de
volver atrás.

### Autorización

**R6 — Cada endpoint nuevo debe replicar su propia guarda de rol.**
No hay middleware. La autorización está duplicada en `index.php` y en cada `api/*.php`. Si
olvidas la guarda, **el endpoint queda abierto a cualquier sesión**.
*Si se rompe:* un registrador accede a funciones de supervisor.

**R7 — Ocultar un botón no es autorizar.**
El control que cuenta es el del servidor. La interfaz solo decide qué se muestra.
*Si se rompe:* cualquiera con la URL ejecuta la acción.

**R8 — Toda mutación desde el navegador pasa por `CSRF::validateRequest()`.**
*Si se rompe:* un sitio externo puede provocar acciones en nombre del usuario.
*Deuda existente:* `api/auth.php` no lo valida en ninguna operación, incluidas `logout` y
`change_year`, que son POST y modifican la sesión.

**R9 — Un registrador solo ve y toca lo suyo.**
El filtro `AND o.usuario_registro_id = ?` debe estar en toda consulta nueva que devuelva
observaciones a un registrador — listados, estadísticas y reportes.
*Si se rompe:* fuga de datos entre registradores.

### Dominio REM

**R10 — `codigo_hoja` es obligatorio salvo cuando `tipo_error` es `S/OBSERVACION`.**
Vale igual para el alta manual y para la importación.
*Si se rompe:* observaciones sin hoja identificable, inservibles para el informe.

**R11 — Los valores de estado, rol, tipo de error, serie y clasificación salen de
`config/constants.php`. La interfaz no inventa ninguno.**
*Si se rompe:* datos que no encajan en ningún reporte y que la base puede rechazar por el ENUM.

**R12 — Los meses son de doble tipo: nombre en las observaciones, número en las asignaciones.**
`getMesNumero()` es el puente. Al añadir código que cruce ambos mundos, conviértelos
explícitamente.
*Si se rompe:* la validación de asignaciones falla en silencio y deja a un registrador sin poder
trabajar, o le permite registrar donde no debe.

**R13 — Una reasignación temporal tiene prioridad sobre la anual y bloquea al titular.**
No es un caso borde: es el mecanismo de cobertura de ausencias.
*Si se rompe:* dos personas registran sobre el mismo establecimiento y mes, o quien cubre no
puede hacerlo.

**R14 — El supervisor solo actúa sobre observaciones `pendiente`, y al aprobar debe elegir
resultado.**
`estado_resultante` no tiene valor por defecto a propósito.
*Si se rompe:* observaciones aprobadas sin quedar clasificadas como corregidas o con error, lo
que desvirtúa el informe que se envía a los establecimientos.

### Proceso

**R15 — Lint de sintaxis antes de cada commit.** Lo exige `SECURITY.md`:

```bash
find . -name '*.php' -not -path './vendor/*' -exec php -l {} \;
```

*Si se rompe:* un error de sintaxis derriba la página entera; no hay pruebas que lo atrapen.

**R16 — Si cambias el comportamiento, actualiza el contrato en el mismo cambio.**
Lo exige el principio I de `.specify/memory/constitution.md`. Este trabajo encontró **siete**
afirmaciones falsas acumuladas en la documentación maestra: así se llega ahí.

**R17 — No introduzcas frameworks ni capas nuevas sin justificarlo por escrito.**
Es un monolito simple a propósito. La interfaz es Tabler 1.4; los tokens visuales viven en
`assets/css/tokens.css`; `assets/css/styles.css` está deprecado y no debe crecer.

---

## 5. Estado conocido

Deuda real que se arrastra hoy. **Nada de esto es comportamiento correcto**; está aquí para que
nadie lo confunda con una decisión de diseño.

### Corregir cuanto antes

| Asunto | Detalle |
|---|---|
| **Cuentas semilla con `admin123`** | ⚠️ **Pendiente, requiere acción sobre la base.** `config/init_db.sql:154` siembra `supervisor1` y `registrador1..4` con la contraseña `admin123`. Si esas cuentas existen en la base de producción y conservan esa contraseña, hay que rotarlas. La exposición en la pantalla de login ya está cerrada (ver abajo), pero la contraseña débil sigue siendo válida hasta que se cambie. |
| **Sin rotación de identificador de sesión** | No existe `session_regenerate_id()` en todo el repositorio. El id de sesión no cambia al autenticar: fijación de sesión. |
| **`api/auth.php` sin CSRF** | Ninguna de sus tres operaciones valida CSRF, y `logout` y `change_year` son POST que modifican la sesión. |
| **Mensajes de excepción devueltos al cliente** | Varios manejadores responden `'Error en el servidor: ' . $e->getMessage()`, lo que puede filtrar estructura de tablas o rutas del servidor. |

### Deuda estructural

| Asunto | Detalle |
|---|---|
| **Sin pruebas automatizadas** | No hay PHPUnit. La verificación es `php -l` más prueba manual. Los scripts de `tools/` **mutan la base configurada** y no son pruebas aisladas: nunca los ejecutes contra producción. |
| **Sin migrador de esquema** | El SQL se aplica a mano y el orden depende de la documentación. Hay migraciones sin guarda de existencia que fallan según el orden (ver R5). Además, `config/sprint3_migration.sql` y `specs/sprint3_migration.sql` son duplicados byte a byte. |
| **Autorización duplicada** | Repetida en `index.php` y en los quince endpoints, con dos estilos distintos de leer el rol. Ninguna inconsistencia detectada permite escalar privilegios, pero el patrón invita al olvido. |
| **Contrato de API no uniforme** | El parámetro de despacho cambia por endpoint (`action`, `report`, `report_type`, `type`), y en algunos POST viaja en el cuerpo. `api/supervision.php` **no usa el sobre `{success, data, message}`**: emite cuatro formas distintas. |
| **`api/reports.php` sin rama de error** | `default` comparte cuerpo con `case 'all'`: un `report` inválido lanza 17 consultas en lugar de devolver 400. |
| **`assets/css/styles.css` deprecado pero activo** | 1650 líneas prohibidas por la constitución, todavía cargadas desde `includes/header.php:51` y `views/login.php:18`. Eliminarlo sin migrar sus reglas rompería la interfaz. |
| **Cola de reportes desconectada** | `api/report_queue.php`, `models/ReportQueue.php` y `worker_reportes.php` funcionan, pero **nada en la interfaz encola reportes**. `deploy/healthcheck.sh:74` sí monitorea la tabla. Hay que decidir: conectarlo o retirarlo completo, nunca a medias. |
| **Código muerto** | ~890 líneas eliminables con riesgo bajo o medio. Inventario y orden sugerido en `AUDITORIA-CODIGO-MUERTO.md`. |
| **Tabla `logs` sin uso** | Definida en el esquema; ningún modelo escribe en ella. |

### Lo que ya se corrigió y la documentación no reflejaba

Estas afirmaciones circulaban como ciertas y **son falsas**:

| Se decía | Realidad verificada |
|---|---|
| Las credenciales de prueba se muestran en el login sin guarda | **Corregido en este trabajo**: `views/login.php` ahora envuelve el bloque en `if (defined('ENVIRONMENT') && ENVIRONMENT === 'development')`. Verificado renderizando la página: en producción `admin123` y `supervisor1` no aparecen en el HTML |
| El reseteo de contraseña deja `admin123` (`OPERATIONS.md`, `SECURITY.md`, `faltantes.md`) | Falso: `api/users.php:230` usa `Mailer::generateRandomPassword(12)`, con mayúscula, minúscula, dígito y símbolo garantizados, confirmación explícita obligatoria y prohibición de auto-reseteo |
| El worker tiene un bug de `$this->db` fuera de clase | Corregido: usa `Database::getInstance()` en `worker_reportes.php:19` |
| El DELETE de `observations.php` borra en duro | Falso: usa `moveToTrash()` (`observations.php:219-220`) |
| Un registrador solo edita mientras está `pendiente` | Falso: edita en cualquier estado, y la edición la devuelve a `pendiente` |
| La importación no valida la asignación mensual | Falso: la valida en `api/import.php:183` |
| El reseteo de contraseña fija `admin123` | Falso: `api/users.php` genera aleatoria y aplica política de complejidad |
| Existe un modelo `Version` | Falso: el módulo de versionado fue eliminado |
| `A.md` documenta el sistema | El archivo no existe |

---

## 6. Cómo mantener esta línea base

1. **Cambio de comportamiento** → actualiza `openapi.yaml` y este documento **en el mismo
   commit**. Es el principio I de la constitución y la razón de que existan las siete
   afirmaciones falsas de arriba.
2. **Endpoint o acción nueva** → añádela al contrato con su guarda de rol y su requisito de CSRF,
   y revisa si toca alguna regla de oro.
3. **Cambio de esquema** → actualiza §1 de `VERDAD-ACTUAL.md` y §2 de este documento, y deja
   escrito el procedimiento de respaldo y vuelta atrás **antes** de tocar producción.
4. **Verificación mínima antes de cada commit**:

```bash
# Sintaxis de todo el PHP
find . -name '*.php' -not -path './vendor/*' -exec php -l {} \;

# Validez del contrato
python -c "import yaml; yaml.safe_load(open('docs/spec/openapi.yaml', encoding='utf-8'))"
```
