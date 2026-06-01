# Módulo: VER — Versionado y Snapshots del Sistema

## Clarifications

### Session 2026-06-01

- Q: ¿Archivos excluidos del snapshot? → A: Excluir node_modules/, .git/, uploads/, vendor/, *.log, *.tmp, assets/cache/, .env. Solo código fuente activo (.php, .js, .css, .sql, .json, .md).
- Q: ¿Rollback incluye BD o solo archivos? → A: Solo archivos. El snapshot no incluye BD. El versionado de BD se maneja aparte con migraciones SQL secuenciales. El registro de rollback incluye advertencia sobre posibles cambios de esquema.
- Q: ¿Rollback falla a medio camino? → A: No atómico. Copia directa con overwrite. Si falla, mostrar error con lista de archivos no restaurados. El supervisor decide si reintentar o restaurar manualmente. Los archivos ya copiados se dejan como están (el supervisor debe crear un snapshot antes del rollback como respaldo).
- Q: ¿Manifiesto con estructura de directorios o plana? → A: Con directorios. Rutas relativas completas (`models/Observacion.php`). Al restaurar se recrea misma estructura.
- Q: ¿Rollback genera copia completa o referencia? → A: Copia completa. Cada versión es un snapshot físico independiente en uploads/versiones/{version_tag}/. Más disco pero más robusto.

## 1. User Scenarios & Testing

### HU-VER-001: Listar versiones del sistema
**Prioridad:** P1  
**Rol:** Supervisor

```gherkin
Dado que el usuario Supervisor accede al módulo de versionado
Cuando se carga la vista
Entonces se muestra una lista cronológica de todas las versiones/snapshots del sistema
Y cada versión muestra: etiqueta (v001, v002, ...), descripción, autor, fecha de creación
```

### HU-VER-002: Ver detalle de una versión
**Prioridad:** P2  
**Rol:** Supervisor

```gherkin
Dado que el usuario Supervisor está en la lista de versiones
Cuando selecciona una versión para ver detalle
Entonces se muestra el manifiesto de archivos incluidos en el snapshot
Y se muestra la ruta del snapshot y metadatos asociados
```

### HU-VER-003: Crear un snapshot del sistema
**Prioridad:** P1  
**Rol:** Supervisor

```gherkin
Dado que el usuario Supervisor desea crear un snapshot
Cuando completa la descripción del snapshot y confirma la creación
Entonces el sistema copia los archivos del código fuente a `uploads/versiones/{version_tag}/`
Y se genera un manifiesto MD5 de todos los archivos copiados
Y se crea un registro en `versiones_sistema` con los metadatos
Y se muestra un mensaje de éxito con la etiqueta de la versión creada
```

### HU-VER-004: Restaurar una versión anterior (rollback)
**Prioridad:** P1  
**Rol:** Supervisor

```gherkin
Dado que el usuario Supervisor selecciona una versión para restaurar
Cuando confirma la operación de rollback
Entonces el sistema copia los archivos del snapshot de vuelta al código fuente activo
Y se crea un nuevo registro en `versiones_sistema` documentando el rollback
Y se incluye advertencia: "Si hay cambios de esquema BD desde esta versión, ejecutar migraciones manualmente"
Y se muestra un mensaje de éxito indicando la versión restaurada

Dado que ocurre un error durante el rollback
Entonces el sistema muestra un mensaje de error detallado
Y no se crea un nuevo registro de versión
```

### Edge Cases

| Caso | Descripción |
|------|-------------|
| EC-VER-01 | No hay versiones creadas: mostrar mensaje "No hay snapshots disponibles" con botón para crear el primero |
| EC-VER-02 | Directorio `uploads/versiones/` no existe o no tiene permisos de escritura: mostrar error descriptivo al crear snapshot |
| EC-VER-03 | Snapshot con archivos faltantes o corruptos: validar integridad con MD5 antes del rollback y mostrar advertencia si hay discrepancias |
| EC-VER-04 | Rollback durante una sesión activa de otro usuario: mostrar advertencia de que otros usuarios pueden verse afectados |
| EC-VER-05 | Espacio en disco insuficiente para crear snapshot: mostrar mensaje de error y sugerir liberar espacio |
| EC-VER-06 | El manifiesto MD5 no coincide al restaurar: abortar rollback y notificar al usuario sobre la inconsistencia |
| EC-VER-07 | Intentar crear un snapshot sin descripción: validar que el campo descripción no esté vacío |

---

## 2. Requirements

### Functional Requirements

| ID | Descripción | Prioridad |
|----|-------------|-----------|
| FR-VER-001 | El sistema debe listar todos los snapshots disponibles en orden cronológico descendente mostrando etiqueta, descripción, autor y fecha (VER-001) | P1 |
| FR-VER-002 | El sistema debe mostrar el detalle de un snapshot incluyendo el manifiesto de archivos y metadatos (VER-002) | P2 |
| FR-VER-003 | El sistema debe permitir crear un snapshot copiando los archivos del código fuente a `uploads/versiones/{version_tag}/` y generando un manifiesto MD5 (VER-003) | P1 |
| FR-VER-004 | El sistema debe incrementar automáticamente el número de versión (v001, v002, ..., v999), con 3 dígitos zero-padded. Al alcanzar v999, rechazar nuevos snapshots con mensaje "Límite de versiones alcanzado" | P1 |
| FR-VER-005 | El sistema debe permitir restaurar el código fuente a partir de un snapshot, copiando los archivos de vuelta y creando un nuevo registro de versión (VER-004) | P1 |
| FR-VER-006 | El sistema debe almacenar en `versiones_sistema` los campos: id, version_tag, descripcion, snapshot_path, archivos_json (manifiesto con rutas relativas y hash MD5), usuario_id, fecha_creacion, fecha_actualizacion | P1 |
| FR-VER-007 | El sistema debe validar la integridad del manifiesto MD5 antes de ejecutar un rollback | P2 |
| FR-VER-008 | El sistema debe registrar qué usuario creó cada snapshot y qué usuario ejecutó cada rollback | P2 |
| FR-VER-009 | El sistema debe impedir la creación de snapshots con descripción vacía | P2 |

### Key Entities

| Entidad | Descripción |
|---------|-------------|
| versiones_sistema | Registro de snapshots: id, version_tag, descripcion, snapshot_path, archivos_json (JSON con nombres de archivo y hash MD5), usuario_id, fecha_creacion, fecha_actualizacion |
| uploads/versiones/{version_tag}/ | Directorio físico que almacena los archivos del snapshot. Ej: uploads/versiones/v001/ |
| usuarios | Tabla de usuarios; identifica quién creó o restauró un snapshot |

---

## 3. Success Criteria

| Criterio | Métrica |
|----------|---------|
| CR-VER-01 | La lista de versiones se carga en menos de 2 segundos |
| CR-VER-02 | La creación de un snapshot completa la copia de archivos y genera el manifiesto MD5 en menos de 30 segundos para un proyecto de tamaño medio |
| CR-VER-03 | El rollback restaura correctamente todos los archivos del snapshot y crea el registro de versión en menos de 30 segundos |
| CR-VER-04 | La validación MD5 previa al rollback detecta cualquier discrepancia en los archivos |
| CR-VER-05 | El número de versión se incrementa automática y correctamente en cada snapshot |
| CR-VER-06 | El manifiesto `archivos_json` contiene la lista completa de archivos con sus hashes MD5 correspondientes |
| CR-VER-07 | Al crear un snapshot, los archivos en `uploads/versiones/{version_tag}/` son idénticos a los del código fuente activo y el tag sigue el formato v001, v002... |

---

## 4. Assumptions

| ID | Supuesto |
|----|----------|
| ASM-VER-01 | El servidor tiene permisos de lectura sobre el código fuente del sistema y permisos de escritura sobre `uploads/versiones/` |
| ASM-VER-02 | Hay suficiente espacio en disco para almacenar los snapshots (se asume un mínimo de 2 GB disponibles) |
| ASM-VER-03 | Solo los usuarios con rol Supervisor tienen acceso al módulo de versionado |
| ASM-VER-04 | La función hash MD5 está disponible en el servidor para generar el manifiesto |
| ASM-VER-05 | La API REST está implementada en `api/versiones.php` con las acciones: listar, detalle, crear, restaurar |
| ASM-VER-06 | La base de datos tiene la tabla `versiones_sistema` creada con los campos especificados |
| ASM-VER-07 | El rollback sobrescribe los archivos actuales del código fuente; se recomienda crear un snapshot antes de rollback como medida de seguridad |
| ASM-VER-08 | Archivos excluidos del snapshot: node_modules/, .git/, uploads/, vendor/, *.log, *.tmp, assets/cache/, .env. Solo se incluye código fuente activo (.php, .js, .css, .sql, .json, .md) |
