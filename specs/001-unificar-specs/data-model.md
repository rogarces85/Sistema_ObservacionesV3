# Data Model: Sistema Observaciones REM

**Propósito**: Documentar el esquema existente de la base de datos. La BD ya está creada y poblada — NO generar migraciones ni modificar el esquema.

**Charset**: utf8mb4_unicode_ci | **Motor**: InnoDB

---

## Tablas

### `usuarios`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK) | Identificador único |
| `username` | VARCHAR(50) | Nombre de usuario (único, minúsculas/números/guion_bajo) |
| `password_hash` | VARCHAR(255) | Hash bcrypt |
| `nombre_completo` | VARCHAR(255) | Nombre real |
| `rol` | ENUM('registrador','supervisor') | Rol del usuario |
| `activo` | TINYINT(1) | 1=activo, 0=inactivo |
| `password_reset_required` | TINYINT(1) | 1=debe cambiar contraseña en próximo login |
| `fecha_creacion` | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| `fecha_actualizacion` | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

### `comunas`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK) | Identificador único |
| `codigo_comuna` | VARCHAR | Código DEIS |
| `nombre` | VARCHAR | Nombre de comuna |
| `fecha_creacion` | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| `fecha_actualizacion` | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

### `establecimientos`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK) | Identificador único |
| `codigo_establecimiento` | VARCHAR (UNIQUE) | Código DEIS |
| `nombre` | VARCHAR | Nombre completo |
| `nombre_corto` | VARCHAR | Abreviación (opcional) |
| `comuna_id` | INT (FK) | → comunas.id |
| `activo` | TINYINT(1) | 1=activo, 0=inactivo |
| `fecha_creacion` | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| `fecha_actualizacion` | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

### `referentes_establecimientos`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK) | Identificador único |
| `establecimiento_id` | INT (FK) | → establecimientos.id |
| `cargo` | VARCHAR | Cargo del referente |
| `nombre` | VARCHAR | Nombre completo |
| `telefono` | VARCHAR | Teléfono |
| `email` | VARCHAR | Email (opcional) |
| `activo` | TINYINT(1) | 1=activo, 0=inactivo |
| `fecha_creacion` | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| `fecha_actualizacion` | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

### `observaciones`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK) | Identificador único |
| `usuario_registro_id` | INT (FK) | → usuarios.id |
| `establecimiento_id` | INT | Código establecimiento |
| `comuna_id` | INT | ID comuna |
| `anio` | INT | Año |
| `mes` | INT | Mes (1-12) |
| `codigo_serie` | VARCHAR | Serie REM |
| `codigo_hoja` | VARCHAR | Hoja REM (nullable si S/OBSERVACION) |
| `tipo_error` | VARCHAR | ERROR/REVISAR/F/PLAZO/S/OBSERVACION |
| `detalle_observacion` | TEXT | Descripción |
| `plazo_entrega` | DATE | Fecha límite |
| `anio_rem` | INT | Año REM |
| `mes_rem` | INT | Mes REM |
| `estado_actual` | VARCHAR | pendiente/aprobado/error/rechazado |
| `clasificacion` | VARCHAR | Clasificación asignada |
| `fecha_creacion` | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| `fecha_actualizacion` | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

### `historial_estados`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK) | Identificador único |
| `observacion_id` | INT (FK) | → observaciones.id |
| `usuario_id` | INT (FK) | → usuarios.id (supervisor que actuó) |
| `estado_anterior` | VARCHAR | Estado previo |
| `estado_nuevo` | VARCHAR | Estado resultante |
| `comentario` | TEXT | Comentario del supervisor |
| `fecha_creacion` | DATETIME | DEFAULT CURRENT_TIMESTAMP |

### `historial_usuarios`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK) | Identificador único |
| `usuario_id` | INT (FK) | → usuarios.id (quien realizó la acción) |
| `usuario_afectado_id` | INT (FK) | → usuarios.id (target de la acción, nullable) |
| `accion` | VARCHAR(50) | CREACION/ACTIVACION/DESACTIVACION/CAMBIO_PASSWORD/etc. |
| `detalles` | TEXT | Contexto adicional |
| `fecha_creacion` | DATETIME | DEFAULT CURRENT_TIMESTAMP |

### `observaciones_eliminadas`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK) | Identificador único |
| `observacion_original_id` | INT | ID original en observaciones |
| `establecimiento_id` | INT | Código establecimiento (sin FK) |
| `comuna_id` | INT | ID comuna (sin FK) |
| `serie` | VARCHAR | Serie REM |
| `hoja` | VARCHAR | Hoja REM |
| `anio` | INT | Año |
| `mes` | INT | Mes |
| `codigo_prestacion` | VARCHAR | Código prestación |
| `nombre_prestacion` | VARCHAR | Nombre prestación |
| `observado` | INT | Valor observado |
| `numerador` | INT | Numerador |
| `denominador` | INT | Denominador |
| `estado_clasificacion` | VARCHAR | Estado al eliminar |
| `detalle_correccion` | TEXT | Detalle al eliminar |
| `motivo_eliminacion` | VARCHAR | Motivo (obligatorio) |
| `fecha_eliminacion` | DATETIME | Fecha de eliminación |
| `eliminado_por` | INT (FK) | → usuarios.id |
| `fecha_creacion` | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| `fecha_actualizacion` | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

### `asignaciones_establecimientos`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK) | Identificador único |
| `usuario_id` | INT (FK) | → usuarios.id (registrador) |
| `establecimiento_id` | INT (FK) | → establecimientos.id |
| `anio` | INT | Año |
| `meses` | VARCHAR | "ALL" o "1,2,3...12" |
| `tipo_asignacion` | ENUM('anual','temporal') | Tipo |
| `fecha_creacion` | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| `fecha_actualizacion` | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

### `versiones_sistema`

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | INT (PK) | Identificador único |
| `version_tag` | VARCHAR | v001, v002... |
| `descripcion` | TEXT | Descripción del snapshot |
| `snapshot_path` | VARCHAR | Ruta al directorio del snapshot |
| `archivos_json` | TEXT | Manifiesto JSON (ruta relativa → hash MD5) |
| `usuario_id` | INT (FK) | → usuarios.id (creador) |
| `fecha_creacion` | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| `fecha_actualizacion` | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

---

## Relaciones

```text
usuarios (1) ──< observaciones (usuario_registro_id)
usuarios (1) ──< historial_estados (usuario_id)
usuarios (1) ──< historial_usuarios (usuario_id)
usuarios (1) ──< historial_usuarios (usuario_afectado_id)
usuarios (1) ──< observaciones_eliminadas (eliminado_por)
usuarios (1) ──< asignaciones_establecimientos (usuario_id)
usuarios (1) ──< versiones_sistema (usuario_id)
comunas (1) ──< establecimientos (comuna_id)
establecimientos (1) ──< observaciones (establecimiento_id)
establecimientos (1) ──< referentes_establecimientos (establecimiento_id)
establecimientos (1) ──< asignaciones_establecimientos (establecimiento_id)
observaciones (1) ──< historial_estados (observacion_id)
```

## Notas

- `observaciones_eliminadas` NO tiene FK a `establecimientos` ni `comunas` (permite huérfanos para restauración).
- `observaciones.establecimiento_id` y `observaciones.comuna_id` NO tienen FK (permite que el establecimiento se desactive sin perder la integridad de la observación).
- La tabla `usuarios` contiene los 5 seed users: supervisor1 + registrador1-4.
