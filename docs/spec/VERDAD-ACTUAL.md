# Verdad Actual del Sistema — Observaciones REM

**Fase 2 de 4** · Rama `feature/reportes-informe-exportaciones` · HEAD `a3711be`

> Todo lo que sigue está extraído del código que se ejecuta hoy. Cada afirmación lleva su cita
> `archivo:línea`. **No hay nada inferido ni deseado**: donde el código hace algo distinto de lo
> que dice la documentación, gana el código y la discrepancia queda anotada.

## Índice

1. [Modelo de datos](#1-modelo-de-datos)
2. [Enumeraciones y constantes](#2-enumeraciones-y-constantes)
3. [Reglas de negocio](#3-reglas-de-negocio)
4. [Matriz de autorización](#4-matriz-de-autorización)
5. [Inventario de endpoints](#5-inventario-de-endpoints)
6. [Discrepancias entre documentación y código](#6-discrepancias-entre-documentación-y-código)

### Símbolos excluidos por la Fase 1

Este documento **no describe** lo marcado como muerto en `AUDITORIA-CODIGO-MUERTO.md`:
`includes/icons.php`, `includes/validators.php` (clase `Validators` completa), los 13 métodos PHP
sin llamador, las 19 funciones JS y las 6 acciones de API inalcanzables. La acción
`supervision.php?action=reject` **sí** se documenta: es un stub 403 deliberado, no código muerto.

---

## 1. Modelo de datos

Nueve tablas. Reconstruido desde `config/init_db.sql` más las migraciones, contrastado con el SQL
que los modelos ejecutan realmente.

### 1.1 `usuarios` — `config/init_db.sql:11-24`

| Columna | Tipo | Nulo | Detalle |
|---|---|---|---|
| `id` | INT AUTO_INCREMENT | no | PK |
| `username` | VARCHAR(50) | no | **UNIQUE**, índice `idx_username` |
| `password_hash` | VARCHAR(255) | no | `password_hash()` de PHP, bcrypt |
| `nombre_completo` | VARCHAR(100) | no | |
| `rol` | ENUM('registrador','supervisor') | no | Default `registrador`, índice `idx_rol` |
| `activo` | BOOLEAN | no | Default TRUE |
| `fecha_creacion` | TIMESTAMP | sí | Default CURRENT_TIMESTAMP |
| `fecha_actualizacion` | TIMESTAMP | sí | ON UPDATE CURRENT_TIMESTAMP |

### 1.2 `comunas` — `config/init_db.sql:27-33`

| Columna | Tipo | Nulo | Detalle |
|---|---|---|---|
| `id` | INT AUTO_INCREMENT | no | PK |
| `codigo_comuna` | INT | no | **UNIQUE**. Códigos oficiales DEIS 10301–10307 |
| `nombre` | VARCHAR(100) | no | |

Siete comunas del Servicio de Salud Osorno, sembradas en `config/init_db.sql:104-112`: OSORNO
(10301), PUERTO OCTAY (10302), PURRANQUE (10303), PUYEHUE (10304), RIO NEGRO (10305), SAN JUAN DE
LA COSTA (10306), SAN PABLO (10307). La migración `config/migration_2026_05_08_limpieza_comunas.sql`
unificó una numeración antigua (10001–10007) hacia estos códigos y borró la vieja.

### 1.3 `establecimientos` — `config/init_db.sql:36-46`

| Columna | Tipo | Nulo | Detalle |
|---|---|---|---|
| `id` | INT AUTO_INCREMENT | no | PK |
| `codigo_establecimiento` | INT | no | **UNIQUE** |
| `nombre` | VARCHAR(200) | no | |
| `nombre_corto` | VARCHAR(100) | **sí** | Ampliado de 50 y hecho nullable por `config/migrations/sprint1_migration.sql:20` |
| `comuna_id` | INT | no | FK → `comunas(id)` ON DELETE RESTRICT |
| `activo` | BOOLEAN | no | Default TRUE. Baja lógica, nunca se borra la fila |

### 1.4 `observaciones` — `config/init_db.sql:49-79` — tabla central

| Columna | Tipo | Nulo | Detalle |
|---|---|---|---|
| `id` | INT AUTO_INCREMENT | no | PK |
| `anio` | INT | no | Índice `idx_anio` |
| `mes` | VARCHAR(20) | no | **Nombre en español** ("Enero"…"Diciembre"), no número |
| `establecimiento_id` | INT | no | FK → `establecimientos(id)` RESTRICT |
| `codigo_serie` | VARCHAR(50) | no | La SERIE REM ("SERIE A", "SERIE BM"…) |
| `codigo_hoja` | VARCHAR(50) | no | La hoja REM ("A01", "Hoja Control"…) |
| `tipo_error` | VARCHAR(100) | no | `S/OBSERVACION`, `ERROR`, `REVISAR`, `F/PLAZO` |
| `detalle_observacion` | TEXT | no | |
| `respuesta_establecimiento` | TEXT | sí | |
| `plazo_entrega` | ENUM('dentro_plazo','fuera_plazo') | no | |
| `usa_validador` | ENUM('si','no') | no | |
| `estado_actual` | ENUM(5 valores) | no | Default `pendiente` |
| `clasificacion` | VARCHAR(200) | sí | Clasificación del supervisor |
| `detalle_error` | TEXT | sí | Anotación del supervisor |
| `usuario_registro_id` | INT | no | FK → `usuarios(id)` RESTRICT |
| `usuario_supervisor_id` | INT | sí | FK → `usuarios(id)` **SET NULL** |
| `fecha_registro` | TIMESTAMP | sí | Default CURRENT_TIMESTAMP |
| `fecha_revision` | TIMESTAMP | sí | |
| `fecha_actualizacion` | TIMESTAMP | sí | ON UPDATE CURRENT_TIMESTAMP |

**Índices de reportes** (`config/migration_2026_05_08_reportes.sql`): `idx_anio_tipo_error`,
`idx_anio_plazo`, `idx_anio_validador`, `idx_anio_serie_error`, `idx_anio_hoja`, `idx_anio_estado`.
Más `idx_boletin (anio, tipo_error, establecimiento_id, mes)`.

> **Divergencia real:** `respuesta_establecimiento`, `detalle_error` e `idx_boletin` **ya están**
> en `config/init_db.sql`, pero también los añade `config/migration_2026_08_12_boletin.sql`. Esa
> migración es idempotente a propósito (consulta `information_schema` antes de alterar) porque
> existían bases desplegadas sin esas columnas mientras el PHP ya las usaba. Una instalación nueva
> desde `init_db.sql` no la necesita; una base antigua sí.

### 1.5 `asignaciones_establecimientos` — `config/create_asignaciones_table.sql:5-19`

| Columna | Tipo | Nulo | Detalle |
|---|---|---|---|
| `id` | INT AUTO_INCREMENT | no | PK |
| `usuario_id` | INT | no | FK → `usuarios(id)` **CASCADE** |
| `establecimiento_id` | INT | no | FK → `establecimientos(id)` **CASCADE** |
| `anio` | INT | no | |
| `meses` | VARCHAR(50) | sí | Default `'ALL'`. **CSV de números de mes** (`"1,2,3"`) o el literal `ALL` |
| `tipo_asignacion` | ENUM('anual','temporal') | sí | Default `anual` |
| `fecha_asignacion` | TIMESTAMP | sí | Default CURRENT_TIMESTAMP |

Índice compuesto `idx_establecimiento_anio_tipo (establecimiento_id, anio, tipo_asignacion)`.

> **Trampa de orden de migraciones:** `create_asignaciones_table.sql` ya crea `meses` y
> `tipo_asignacion`. Pero `config/migrations/sprint2_migration.sql:4` intenta
> `ADD COLUMN meses` y `config/migrations/add_tipo_asignacion.sql:9` intenta
> `ADD COLUMN tipo_asignacion`, **ambos sin guarda de existencia**. Aplicarlos después del
> `CREATE TABLE` falla con "Duplicate column name". No hay herramienta de migraciones que lo
> resuelva: el orden depende de la documentación y del estado real de cada base.

### 1.6 `observaciones_eliminadas` — `config/sprint3_migration.sql:5-34`

Papelera de reciclaje. **Es una copia desnormalizada**, no una FK: guarda
`establecimiento_nombre`, `comuna` y `nombre_registro` como texto para que el registro sobreviva
aunque cambien las tablas maestras. Campos propios: `observacion_id` (el id original),
`motivo_eliminacion`, `fecha_eliminacion`, `fecha_registro_original`, `usuario_supervisor_id`
(quién eliminó). No define claves foráneas.

### 1.7 `historial_estados` — `config/init_db.sql:82-94`

Auditoría de cada transición de estado: `observacion_id` (FK **CASCADE**), `estado_anterior`,
`estado_nuevo`, `usuario_id`, `comentario`, `fecha_cambio`. Al borrar la observación en duro, su
historial se va con ella.

### 1.8 `historial_usuarios` — `config/migrations/sprint1_migration.sql:5-14`

Auditoría de usuarios (modelo `UserAudit`): `usuario_id` (CASCADE),
`usuario_responsable_id` (SET NULL), `accion` (`CREACION`, `ACTUALIZACION`, `CAMBIO_PASSWORD`,
`ACTIVADO`, `DESACTIVADO`, `ELIMINACION`), `detalles`, `fecha_registro`.

### 1.9 `notificaciones` — `config/migrations/create_notificaciones.sql:1-13`

`usuario_id` (CASCADE), `tipo`, `titulo`, `mensaje`, `url`, `leida` (TINYINT default 0),
`fecha_creacion`, `fecha_lectura`. Índice `(usuario_id, leida, fecha_creacion)`.

### 1.10 `logs` y `reportes_pendientes`

- `logs` (`config/init_db.sql:97-110`) está **definida pero ningún modelo escribe en ella**. La
  auditoría real vive en `historial_estados` e `historial_usuarios`.
- `reportes_pendientes` (`config/migrations/sprint4_migration.sql:4-16`) pertenece al subsistema
  de cola desconectado de la UI (hallazgo E de la Fase 1). Estados
  `PENDIENTE`/`PROCESANDO`/`LISTO`/`ERROR`.

### 1.11 Relaciones

```
comunas 1──N establecimientos 1──N observaciones N──1 usuarios (registro)
                     │                  │            └──1 usuarios (supervisor, SET NULL)
                     │                  └──N historial_estados
                     └──N asignaciones_establecimientos N──1 usuarios

observaciones ──(copia desnormalizada, sin FK)──> observaciones_eliminadas
usuarios 1──N historial_usuarios · notificaciones · reportes_pendientes
```

---

## 2. Enumeraciones y constantes

Fuente única: `config/constants.php`. La constitución prohíbe que la interfaz invente valores
fuera de este archivo.

| Grupo | Valores | Línea |
|---|---|---|
| **Estados** | `pendiente`, `aprobado`, `rechazado`, `error`, `justificado` | `8-12` |
| **Roles** | `registrador`, `supervisor` | `15-16` |
| **Plazo** | `dentro_plazo`, `fuera_plazo` | `19-20` |
| **Validador** | `si`, `no` | `23-24` |
| **Tipos de error** | `S/OBSERVACION`, `ERROR`, `REVISAR`, `F/PLAZO` | `27-32` |
| **Series REM** | `SERIE A`, `SERIE BS`, `SERIE BM`, `SERIE P`, `SERIE ANEXO`, `SERIE D` | `35-42` |
| **Meses** | `Enero` … `Diciembre` (nombres en español) | `131-144` |
| **Clasificaciones** | `corregido`, `error`, `sin_respuesta`, `respuesta_incorrecta` | `162-171` |
| **Colores de estado** | ámbar/esmeralda/pizarra/rosa/cielo | `147-153` |

**`$HOJAS_POR_SERIE`** (`config/constants.php:45-129`) mapea cada serie a sus hojas válidas. Toda
serie incluye `Hoja Nombre`, `Hoja Control` y `Renombre archivo`. SERIE A tiene 30 hojas; SERIE
ANEXO, 13; el resto entre 5 y 14.

**Dos funciones de presentación** que forman parte del contrato:

- `clasificacionLabel($valor, $estadoActual)` (`constants.php:178-200`): devuelve la etiqueta
  legible. Si no hay clasificación, **la deriva del estado**: aprobado→"Corregido",
  justificado→"Justificado", rechazado→"No corregido", pendiente→"Pendiente de respuesta",
  error→"Error", y "Sin clasificar" en cualquier otro caso.
- `esCorregido($clasificacion, $estadoActual)` (`constants.php:206-216`): la clasificación manda;
  si está vacía, se infiere `estado === aprobado`.

---

## 3. Reglas de negocio

### 3.1 Observaciones

| # | Regla | Cita |
|---|---|---|
| O-1 | Solo **registradores** crean observaciones. Un supervisor recibe 403. | `api/observations.php:85-87` |
| O-2 | Campos obligatorios al crear: `mes`, `establecimiento_id`, `tipo_error`, `detalle_observacion`, `plazo_entrega`, `usa_validador`. | `api/observations.php:90-98` |
| O-3 | **`codigo_hoja` es obligatorio salvo si `tipo_error === 'S/OBSERVACION'`.** | `api/observations.php:100-103` |
| O-4 | `usa_validador` con valor `'n/a'` se normaliza a `'no'` antes de guardar. | `api/observations.php:112-114` |
| O-5 | Al crear, un registrador debe tener asignación válida para ese establecimiento **y ese mes**. Si no, 403. | `api/observations.php:134-138` |
| O-6 | Un registrador solo edita observaciones **propias**; ajena → 403. | `api/observations.php:168-171` |
| O-7 | **Un registrador puede editar en cualquier estado**, pero si la observación no está `pendiente`, la edición **la devuelve a `pendiente`** y reingresa al flujo de revisión. | `api/observations.php:172-175` |
| O-8 | Al editar, se revalida la asignación con el establecimiento y mes resultantes (los nuevos si cambian, los actuales si no). | `api/observations.php:184-192` |
| O-9 | Si un supervisor cambia el estado al editar, se estampa su id en `usuario_supervisor_id`. | `api/observations.php:179-181` |
| O-10 | El DELETE de `observations.php` **mueve a la papelera**, no borra en duro. Solo supervisores. | `api/observations.php:206-220` |
| O-11 | Un registrador solo ve sus propias observaciones: el `SELECT` añade `AND o.usuario_registro_id = ?`. Aplica a listados, estadísticas y a los reportes. | `models/Observation.php:43-44, 233-234, 455-544` |

### 3.2 Supervisión

| # | Regla | Cita |
|---|---|---|
| S-1 | Todo `api/supervision.php` es **solo supervisor**; cualquier otro rol recibe 403 antes del despacho. | `api/supervision.php:26-27` |
| S-2 | Solo se gestionan observaciones en estado `pendiente`. En lote, las que no lo estén se **omiten en silencio**; con un único id, se lanza error. | `api/supervision.php:49-76` |
| S-3 | **Aprobar exige elegir el resultado.** `estado_resultante` debe ser `sin_observacion` o `error`; si falta o es otro valor, se lanza excepción. | `api/supervision.php:98-101` |
| S-4 | `estado_resultante = 'sin_observacion'` → estado `aprobado` y `tipo_error = 'S/OBSERVACION'`. `'error'` → estado `error` y `tipo_error = 'ERROR'`. | `api/supervision.php:102-109` |
| S-5 | **No existe rechazo directo.** `?action=reject` devuelve 403 con "Los supervisores solo pueden aprobar observaciones". | `api/supervision.php:139-146` |
| S-6 | "Cancelar" es lo que produce el estado `rechazado`. | `api/supervision.php:160, 171` |
| S-7 | El borrado del supervisor es **suave**: `moveToTrash()` a `observaciones_eliminadas`. | `api/supervision.php:181-206` |
| S-8 | Los ids se normalizan a enteros positivos únicos; lista vacía → error. Acepta un id o un arreglo. | `api/supervision.php:34-47` |

### 3.3 Asignaciones — la parte más sutil del sistema

| # | Regla | Cita |
|---|---|---|
| A-1 | **Los meses son de doble tipo**: `observaciones.mes` guarda el *nombre* ("Enero"); `asignaciones_establecimientos.meses` guarda un *CSV de números* o `ALL`. `getMesNumero()` es el puente. | `models/EstablecimientoAsignacion.php:133-141` |
| A-2 | `meses = 'ALL'` o vacío significa **todos los meses**. | `EstablecimientoAsignacion.php:121-128` |
| A-3 | **Precedencia**: al comprobar acceso se busca primero la asignación *temporal* del usuario; solo si no hay, se mira la *anual*. | `EstablecimientoAsignacion.php:422-441` |
| A-4 | **Una temporal ajena bloquea al titular anual.** Aunque el usuario tenga la anual (incluso `ALL`), si otro tiene una temporal que cubre ese mes, el acceso se **niega**. | `EstablecimientoAsignacion.php:445-461` |
| A-5 | Dos temporales del mismo establecimiento/año que solapen meses se rechazan; una temporal **sí** puede solapar una anual (ese es su propósito). | `EstablecimientoAsignacion.php:167-194` |
| A-6 | Una asignación **anual** entra en conflicto con cualquier tipo de otro usuario que solape meses. | `EstablecimientoAsignacion.php:186-193` |
| A-7 | Si ya existe asignación propia **del mismo tipo**, no se duplica: se **fusionan** los meses (unión ordenada; si cualquiera es `ALL`, el resultado es `ALL`). | `EstablecimientoAsignacion.php:211-221, 267-280` |
| A-8 | `mesesSolapan()` considera que `ALL` solapa con todo. | `EstablecimientoAsignacion.php:107-116` |
| A-9 | Un `tipo_asignacion` distinto de `temporal` se normaliza a `anual`. | `EstablecimientoAsignacion.php:256-259` |

### 3.4 Eliminadas (papelera)

| # | Regla | Cita |
|---|---|---|
| E-1 | Todo `api/deleted.php` es solo supervisor. | `api/deleted.php:28-29` |
| E-2 | **La eliminación permanente exige confirmación explícita**: sin `confirm_irreversible` en el cuerpo, 400. Aplica a la individual y a la masiva. | `api/deleted.php:49-54, 98, 119` |
| E-3 | Cuatro operaciones: `restore`, `permanent_delete`, `restore_multiple`, `permanent_delete_multiple`. | `api/deleted.php:88-131` |

### 3.5 Usuarios

| # | Regla | Cita |
|---|---|---|
| U-1 | Un usuario puede leer su **propia** ficha; leer otra exige ser supervisor. | `api/users.php:79-81` |
| U-2 | Crear, listar todos, actualizar ajenos, activar/desactivar y eliminar: solo supervisor. | `api/users.php:94-95, 107-108, 214-215, 264` |
| U-3 | **Política de contraseña**: mínimo 8 caracteres, al menos una mayúscula y un número. | `api/users.php:129-131` |
| U-4 | Al crear se exige contraseña **o** `generate_password: true`, que produce una aleatoria. | `api/users.php:126-127` |
| U-5 | `rol` debe ser `registrador` o `supervisor`; otro valor → 400. | `api/users.php:134-135` |
| U-6 | Cambiar la contraseña **propia** exige `current_password` y que coincida. Un supervisor puede cambiar la de otro sin la actual. | `api/users.php:171-197` |
| U-7 | Las acciones destructivas exigen confirmación explícita en el cuerpo (`requireExplicitConfirmation`). | `api/users.php:44-48` |
| U-8 | Toda mutación queda registrada en `historial_usuarios` vía `UserAudit::logAction()`. | `models/UserAudit.php` |

### 3.6 Importación

| # | Regla | Cita |
|---|---|---|
| I-1 | **Solo registradores importan.** Un supervisor recibe 403. | `api/import.php:28-29` |
| I-2 | Solo POST. | `api/import.php:255` |
| I-3 | Si falta `rem` pero viene `codigo_hoja`, se usa ese. | `api/import.php:124-125` |
| I-4 | Igual que en el alta manual: `rem` (hoja) es obligatorio salvo para `S/OBSERVACION`. | `api/import.php:137-138` |
| I-5 | **La importación SÍ valida la asignación mensual** con `tieneAsignacionParaMes()`. | `api/import.php:183` |
| I-6 | Los errores se acumulan por fila y se informan; no abortan el lote entero. | `api/import.php:148` |

### 3.7 Establecimientos

| # | Regla | Cita |
|---|---|---|
| L-1 | Leer comunas y establecimientos: cualquier sesión. Crear, actualizar y activar/desactivar: solo supervisor. | `api/locations.php:24-25, 71-72` |
| L-2 | `codigo_establecimiento` es único; duplicado → **409 Conflict**, tanto al crear como al actualizar. | `api/locations.php:94, 119` |
| L-3 | Los establecimientos no se borran: `toggle` cambia `activo`. | `api/locations.php:128-137` |

### 3.8 Sesión y año de trabajo

| # | Regla | Cita |
|---|---|---|
| C-1 | El login guarda `user_id`, `username`, `nombre_completo`, `rol`, `year`, `logged_in` en sesión. | `api/auth.php:48-54` |
| C-2 | El **año de trabajo** vive en `$_SESSION['year']` y es el default de casi toda consulta. | `api/observations.php:33` |
| C-3 | Cambiar de año se acepta solo entre 2020 y el año siguiente al actual. | `api/auth.php:104-110` |
| C-4 | El logout vacía `$_SESSION`, expira la cookie y destruye la sesión. | `api/auth.php:65-75` |

---

## 4. Matriz de autorización

La autorización **no está centralizada**: cada `api/*.php` repite su propia guarda, e `index.php`
repite las suyas para las páginas. No hay middleware.

### 4.1 Páginas (`index.php`)

Whitelist en `index.php:29`; cualquier valor fuera de ella cae a `dashboard` (`index.php:32-33`).

| Página | Registrador | Supervisor | Guarda |
|---|:---:|:---:|---|
| `dashboard`, `observaciones`, `reportes`, `perfil` | ✅ | ✅ | solo sesión |
| `supervision` | ❌ | ✅ | `index.php:38-40` |
| `usuarios` | ❌ | ✅ | `index.php:41-43` |
| `asignaciones` | ❌ | ✅ | `index.php:44-46` |
| `eliminadas` | ❌ | ✅ | `index.php:47-49` |
| `establecimientos` | ❌ | ✅ | `index.php:50-52` |
| `boletin` | ❌ | ✅ | `index.php:53-55` |

El acceso denegado **no da error**: redirige silenciosamente a `dashboard`.

### 4.2 Endpoints

| Endpoint | Sesión | Registrador | Supervisor | CSRF | Guarda |
|---|:---:|:---:|:---:|:---:|---|
| `auth.php` (`login`) | — | ✅ | ✅ | ❌ | pública |
| `auth.php` (`logout`, `check`, `change_year`) | ✅ | ✅ | ✅ | **❌** | `auth.php:81, 98` |
| `observations.php` GET | ✅ | propias | todas | — | `observations.php:48, 65` |
| `observations.php` POST | ✅ | ✅ | ❌ | ✅ | `observations.php:85` |
| `observations.php` PUT | ✅ | propias | ✅ | ✅ | `observations.php:168` |
| `observations.php` DELETE | ✅ | ❌ | ✅ | ✅ | `observations.php:206` |
| `supervision.php` (todo) | ✅ | ❌ | ✅ | ✅ en mutaciones | `supervision.php:26` |
| `assignments.php` (todo) | ✅ | ❌ | ✅ | ✅ en POST | `assignments.php:63` |
| `deleted.php` (todo) | ✅ | ❌ | ✅ | ✅ en POST | `deleted.php:28` |
| `users.php` GET id propio | ✅ | ✅ | ✅ | — | `users.php:79` |
| `users.php` GET otros / listado / POST / DELETE | ✅ | ❌ | ✅ | ✅ | `users.php:94, 107, 264` |
| `users.php` PUT contraseña propia | ✅ | ✅ | ✅ | ✅ | `users.php:171` |
| `locations.php` GET | ✅ | ✅ | ✅ | — | `locations.php:24` |
| `locations.php` POST | ✅ | ❌ | ✅ | ✅ | `locations.php:71-75` |
| `notifications.php` | ✅ | ✅ | ✅ | ✅ en POST | `notifications.php:19, 50` |
| `reports.php` | ✅ | ✅ (datos propios) | ✅ | — | `reports.php:35` |
| `informe_errores.php` | ✅ | ❌ | ✅ | — | `informe_errores.php:36` |
| `boletin.php` | ✅ | ❌ | ✅ | — | `boletin.php:46` |
| `import.php` | ✅ | ✅ | **❌** | ✅ | `import.php:28` |
| `import_template.php` | ✅ | ✅ | ✅ | — | `import_template.php:18` |
| `export.php` | ⚠️ | ✅ | ✅ | — | `export.php:61` |
| `report_queue.php` | ✅ | propios | todos | ✅ en POST | `report_queue.php:44` |

### 4.3 Inconsistencias encontradas

Ninguna permite escalar privilegios, pero todas son deuda real:

1. **`api/export.php:61` valida `$_SESSION['user_id']`** en lugar de `$_SESSION['logged_in']`,
   que es lo que usan los otros catorce endpoints. Distinto criterio para la misma decisión.
2. **`api/auth.php` no llama a `CSRF::validateRequest()` ni una vez**, pese a que `logout` y
   `change_year` son POST que mutan estado de sesión. La constitución exige CSRF en toda mutación
   desde el navegador. `change_year` es el más relevante: un tercero podría cambiarle el año de
   trabajo al usuario.
3. **No existe `session_regenerate_id()` en todo el repositorio.** El identificador de sesión no
   rota al autenticar: fijación de sesión.
4. **La guarda de rol se lee de dos formas distintas**: unos endpoints usan la variable
   `$userRole` (`assignments.php:60`), otros leen `$_SESSION['rol']` directo
   (`deleted.php:28`, `supervision.php:26`, `locations.php:58`). Mismo resultado, dos estilos.
5. **`import.php` excluye a los supervisores.** Puede ser deliberado, pero no está documentado en
   ninguna parte.
6. **`supervision.php?action=update_status` acepta cualquier cadena en `estado`** sin validarla
   contra el ENUM (`supervision.php:216-226`). Hoy es inalcanzable desde la UI; si se reconecta
   sin validación, permite escribir estados inválidos.

---

## 5. Inventario de endpoints

### 5.1 Dos formas de despacho, y no son uniformes

| Endpoint | Parámetro | Origen |
|---|---|---|
| `reports.php` | **`report`** | query |
| `export.php` | **`report_type`** | query |
| `locations.php` | **`type`**, con `action` como alias | query (`locations.php:29`) |
| `observations.php` | método HTTP + `action` | query |
| `assignments.php`, `deleted.php`, `users.php` | `action` en GET; **`action` en el cuerpo JSON** en POST/PUT | mixto |
| resto | `action` | query |
| `boletin.php`, `informe_errores.php`, `import.php`, `import_template.php` | sin parámetro | endpoint de propósito único |

### 5.2 Dos formas de respuesta

La mayoría usa un `jsonResponse()` **local a cada archivo** (no compartido) con el sobre:

```json
{ "success": true, "data": {}, "message": "texto" }
```

Pero **`api/supervision.php` no lo usa**: hace `echo json_encode(...)` directo con formas
distintas — `{success, message}`, `{success, message, count}`, `{success, data, count}` y
`{success, data, historial}` (`supervision.php:112-116, 240-244, 253-257, 273-277`). Cualquier
cliente que asuma un sobre uniforme se rompe contra este endpoint.

### 5.3 Endpoints y acciones vigentes

**`api/auth.php`** — `login` (POST: `username`, `password`, `year`), `logout` (POST),
`change_year` (POST: `year` 2020–actual+1). *`check` está inalcanzable desde la UI (Fase 1).*

**`api/observations.php`** — despacha por método:
- `GET` sin parámetros → listado del año; `GET ?id=` → una observación; `GET ?action=stats` →
  estadísticas; `GET ?action=historial&id=` → historial de estados.
- `POST` → crea (201 con `{id}`).
- `PUT ?id=` → actualiza.
- `DELETE ?id=` → a papelera, cuerpo opcional `{reason}`.

**`api/supervision.php`** — `approve` (POST: `id` escalar o arreglo, `comment`, `clasificacion`,
`detalle_error`, **`estado_resultante` obligatorio**), `cancel` (POST), `delete` (POST: `reason`),
`get_filtered` (GET: `anio`, `mes`, `estado`, `establecimiento_id`, `usuario_registro_id`,
`busqueda`, `limit`=100, `offset`=0), `get_detail` (GET: `id`), `reject` (403 deliberado).

**`api/assignments.php`** — GET: `list`, `registradores`, `establecimientos`, `asignados`,
`referentes`, `temporales`. POST (acción en el cuerpo): `asignar`, `asignar_multiple`, `remover`,
`copiar_anio` (`anio_origen` ≠ `anio_destino`), `temporales`.

**`api/deleted.php`** — GET: `list` (filtros `anio`, `mes`, `comuna_nombre`,
`establecimiento_id`, `usuario_registro_id`, `busqueda`), `stats`. POST: `restore`,
`permanent_delete`, `restore_multiple`, `permanent_delete_multiple` — los permanentes exigen
`confirm_irreversible`.

**`api/users.php`** — GET: listado, `?id=`, `?action=history&id=`. POST: crea (`username`,
`password` o `generate_password`, `nombre_completo`, `rol`). PUT `?id=` con `action` en el cuerpo:
`update`, `password`, `toggle`. DELETE `?id=` con confirmación explícita.

**`api/locations.php`** — GET: `comunas`, `establecimientos` / `get_establecimientos` (filtra por
`comunaId`/`comuna_id` o `comuna_nombre`), `establecimientos_all` (supervisor). POST: `create`,
`update`, `toggle` — 409 ante código duplicado.

**`api/notifications.php`** — GET `list`; POST `read` (`id`), `read_all`.

**`api/reports.php`** — `?report=` con: `mes`, `establecimiento`, `comuna`, `serie`, `plazo`,
`validador`, `plazo-agregado`, `validador-agregado`, `errores_mes`, `errores_establecimiento`,
`errores_comuna`, `fuera_plazo_mes`, `fuera_plazo_establecimiento`, `fuera_plazo_comuna`,
`validador_mes`, `validador_establecimiento`, `validador_comuna`, `serie_detalle`, `hoja_detalle`,
`filtros`, `error-reports`, `all`. Filtros comunes: `year`, `meses[]`, `comuna_ids[]`,
`establecimiento_id`.

> **Defecto de contrato:** `default` comparte cuerpo con `case 'all'` (`reports.php:172-173`).
> Un `report` inválido no da 400: ejecuta las 17 consultas. Ver hallazgo D-2 de la Fase 1.

**`api/export.php`** — GET `?report_type=` con `format` (`excel`/`xlsx`/`pdf`), `year`, `month` o
`months`, `estado`, `establecimiento_id`, `comuna_id`. Devuelve el archivo, o una página HTML de
aviso ante error (no JSON).

**`api/informe_errores.php`** — GET: `tipo`, `trimestre`, `anio`, `format` (default `json`).

**`api/boletin.php`** — GET: `anio`, `periodo` (default `anual`), `trimestre`, `mes`, `comuna_id`,
`establecimiento_id`.

**`api/import.php`** — POST multipart, solo registradores. Valida por fila y devuelve el detalle
de errores.

**`api/import_template.php`** — GET, descarga la plantilla Excel. Sin guarda de rol; si no hay
sesión **redirige** a `index.php` en vez de responder JSON.

**`api/report_queue.php`** — GET `list`, GET `download` (`id`; 409 si no está listo; solo el dueño
o un supervisor), POST `enqueue` (`tipo_reporte`, `formato`, `parametros`; el tipo `detallado`
obliga formato `pdf`). *Sin ningún llamador en la UI — hallazgo E de la Fase 1.*

### 5.4 Códigos de estado en uso

`200` correcto · `201` observación creada (`observations.php:141`) · `400` validación ·
`401` sin sesión o credenciales inválidas · `403` rol o propiedad insuficiente ·
`404` no encontrado · `405` método no permitido · `409` conflicto (código duplicado en
`locations.php`, reporte no listo en `report_queue.php`) · `500` error del servidor.

> **Fuga de información:** varios manejadores devuelven el mensaje crudo de la excepción al
> cliente — `'Error en el servidor: ' . $e->getMessage()` (`observations.php:234`,
> `reports.php:198`, `assignments.php:248`, `locations.php:151`, `deleted.php:141`). Eso puede
> exponer estructura de tablas o rutas del sistema de archivos.

---

## 6. Discrepancias entre documentación y código

Además de las de la Fase 1, la lectura línea a línea desmintió **tres afirmaciones** más:

| Afirmación documentada | Realidad verificada |
|---|---|
| "`api/observations.php` DELETE hace un borrado duro directo de `observaciones`" (CLAUDE.md) | **Falso.** `api/observations.php:219-220` usa `DeletedObservation::moveToTrash()`. Ambas vías de borrado van hoy a la papelera; el borrado duro solo ocurre en `deleted.php?action=permanent_delete`. |
| "Un registrador solo puede editar sus observaciones mientras `estado_actual === 'pendiente'`" (CLAUDE.md) | **Falso.** `api/observations.php:172-175`: puede editar en **cualquier** estado; el efecto es que la observación vuelve a `pendiente`. |
| "La importación no valida la asignación mensual como sí hace el alta manual" (CLAUDE.md y README) | **Falso.** `api/import.php:183` llama a `tieneAsignacionParaMes()`. La brecha está cerrada. |

Sumadas a las de la Fase 1 (módulo de versionado inexistente, bug del worker ya corregido, `A.md`
ausente, `admin123` como contraseña de reseteo), son **siete** afirmaciones falsas en la
documentación maestra. Se corrigen en la Fase 4.
