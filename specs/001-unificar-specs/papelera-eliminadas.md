# Módulo: MOD-DEL — Papelera de Observaciones Eliminadas

## Clarifications

### Session 2026-06-01

- Q: ¿Restaurar es MOVE o COPY? → A: MOVE. Restaurar copia a observaciones y elimina de observaciones_eliminadas. La papelera es temporal, no archivo permanente.
- Q: ¿Restauración masiva con fallos parciales? → A: No transaccional. Restaurar las que se puedan y reportar cuáles fallaron con su error (misma lógica que eliminación permanente masiva).
- Q: ¿Columnas exactas de observaciones_eliminadas? → A: id, observacion_original_id, establecimiento_id, comuna_id, serie, hoja, anio, mes, codigo_prestacion, nombre_prestacion, observado, numerador, denominador, estado_clasificacion, detalle_correccion, motivo_eliminacion, fecha_eliminacion, eliminado_por (FK). Sin FK a establecimiento/comuna (huérfanos permitidos).
- Q: ¿Estructura de historico_observaciones para papelera? → A: Usar misma tabla compartida con columnas: id, observacion_id, accion (RESTAURACION, ELIMINACION_PERMANENTE, RESTAURACION_MASIVA, ELIMINACION_PERMANENTE_MASIVA), usuario_id, fecha, detalles.
- Q: ¿Tamaño de página en listado? → A: 50 registros (consistente con otros módulos).

## 1. User Scenarios & Testing

### HU-DEL-001: Listar observaciones eliminadas
**Prioridad:** P1  
**Rol:** Supervisor

```gherkin
Dado que el usuario Supervisor accede al módulo de eliminadas
Cuando se carga la vista
Entonces se muestra una tabla paginada con las observaciones eliminadas
Y cada fila muestra: ID, establecimiento, comuna, motivo de eliminación, fecha de eliminación, quién eliminó
Y los datos se pueden filtrar por año, mes, comuna, establecimiento, registrador y búsqueda de texto
```

### HU-DEL-002: Ver estadísticas de eliminaciones
**Prioridad:** P2  
**Rol:** Supervisor

```gherkin
Dado que el usuario Supervisor está en el módulo de eliminadas
Cuando solicita ver estadísticas
Entonces se muestran: total de eliminadas, distribución por estado original, distribución por mes, distribución por quién eliminó
```

### HU-DEL-003: Restaurar una observación eliminada
**Prioridad:** P1  
**Rol:** Supervisor

```gherkin
Dado que el usuario Supervisor está en la lista de eliminadas
Cuando selecciona una observación y hace clic en "Restaurar"
Entonces la observación se copia de vuelta a la tabla de observaciones
Y la observación se elimina de la tabla observaciones_eliminadas
Y se registra en el historial la acción de restauración
Y se muestra un mensaje de éxito
```

### HU-DEL-004: Eliminar permanentemente una observación
**Prioridad:** P1  
**Rol:** Supervisor

```gherkin
Dado que el usuario Supervisor está en la lista de eliminadas
Cuando selecciona una observación y hace clic en "Eliminar permanentemente"
Entonces se muestra un diálogo de confirmación con advertencia de que la acción es irreversible

Dado que el usuario confirma la eliminación permanente
Entonces el registro se elimina definitivamente de observaciones_eliminadas
Y se muestra un mensaje de éxito
```

### HU-DEL-005: Restauración masiva de observaciones
**Prioridad:** P2  
**Rol:** Supervisor

```gherkin
Dado que el usuario Supervisor está en la lista de eliminadas
Cuando selecciona múltiples observaciones y hace clic en "Restaurar seleccionadas"
Entonces todas las observaciones seleccionadas se restauran a la tabla de observaciones
Y se muestra un mensaje con la cantidad de observaciones restauradas exitosamente
```

### HU-DEL-006: Eliminación permanente masiva
**Prioridad:** P2  
**Rol:** Supervisor

```gherkin
Dado que el usuario Supervisor está en la lista de eliminadas
Cuando selecciona múltiples observaciones y hace clic en "Eliminar permanentemente seleccionadas"
Entonces se muestra un diálogo de confirmación con el número de registros afectados

Dado que el usuario confirma la eliminación masiva
Entonces todos los registros seleccionados se eliminan definitivamente
Y se muestra un mensaje con la cantidad de observaciones eliminadas
```

### Edge Cases

| Caso | Descripción |
|------|-------------|
| EC-DEL-01 | No hay observaciones eliminadas: mostrar mensaje "No hay observaciones en la papelera" y ocultar acciones masivas |
| EC-DEL-02 | Restaurar una observación cuyo establecimiento o comuna ya no existe en el sistema: mostrar advertencia y permitir restauración con datos huérfanos |
| EC-DEL-03 | Intento de restaurar un registro que ya fue restaurado (o eliminado permanentemente) por otro usuario: mostrar 404 con mensaje "El registro ya no existe en la papelera" |
| EC-DEL-04 | Filtros sin resultados: mostrar mensaje "No se encontraron observaciones con los filtros aplicados" |
| EC-DEL-05 | Eliminación permanente en medio de una operación masiva falla parcialmente: mostrar cuáles fallaron y cuáles se eliminaron correctamente |
| EC-DEL-06 | Seleccionar todas las observaciones con el checkbox de cabecera: seleccionar/deseleccionar todas las filas visibles |
| EC-DEL-07 | Restauración masiva con fallos parciales: restaurar las exitosas y reportar IDs fallidos con su error (no transaccional) |

---

## 2. Requirements

### Functional Requirements

| ID | Descripción | Prioridad |
|----|-------------|-----------|
| FR-DEL-001 | El sistema debe listar observaciones eliminadas con paginación y filtros por año, mes, comuna, establecimiento, registrador y texto libre (DEL-001) | P1 |
| FR-DEL-002 | El sistema debe mostrar estadísticas de eliminaciones: total, por estado original, por mes, por quién eliminó (DEL-002) | P2 |
| FR-DEL-003 | El sistema debe permitir restaurar una observación eliminada individualmente, copiándola de vuelta a la tabla observaciones y registrando en historial (DEL-003) | P1 |
| FR-DEL-004 | El sistema debe permitir eliminar permanentemente una observación con confirmación previa (DEL-004) | P1 |
| FR-DEL-005 | El sistema debe permitir restaurar múltiples observaciones seleccionadas en una sola operación (DEL-005) | P2 |
| FR-DEL-006 | El sistema debe permitir eliminar permanentemente múltiples observaciones seleccionadas con confirmación previa (DEL-006) | P2 |
| FR-DEL-007 | El sistema debe mostrar el motivo de eliminación en cada fila de la tabla | P1 |
| FR-DEL-008 | El sistema debe registrar en el historial las acciones de restauración realizadas | P2 |

### Key Entities

**observaciones_eliminadas**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT (PK) | Identificador único |
| observacion_original_id | INT | ID original en tabla observaciones (antes de eliminar) |
| establecimiento_id | INT | Código establecimiento (sin FK, permite huérfanos) |
| comuna_id | INT | ID comuna (sin FK, permite huérfanos) |
| serie | VARCHAR | Serie REM |
| hoja | VARCHAR | Hoja REM |
| anio | INT | Año |
| mes | INT | Mes (1-12) |
| codigo_prestacion | VARCHAR | Código de prestación |
| nombre_prestacion | VARCHAR | Nombre de prestación |
| observado | INT | Valor observado |
| numerador | INT | Numerador |
| denominador | INT | Denominador |
| estado_clasificacion | VARCHAR | Estado de clasificación al momento de eliminar |
| detalle_correccion | TEXT | Detalle de corrección al momento de eliminar |
| motivo_eliminacion | VARCHAR | Motivo por el que se eliminó (obligatorio) |
| fecha_eliminacion | DATETIME | Fecha y hora de eliminación |
| eliminado_por | INT (FK) | ID del usuario que eliminó |
| fecha_creacion | DATETIME | Fecha de creación (DEFAULT CURRENT_TIMESTAMP) |
| fecha_actualizacion | DATETIME | Fecha de última actualización (ON UPDATE CURRENT_TIMESTAMP) |

**observaciones**: Tabla principal a la que se restauran los registros eliminados.
**historico_observaciones**: Registro de auditoría compartido. Columnas: id, observacion_id, accion (RESTAURACION, ELIMINACION_PERMANENTE, RESTAURACION_MASIVA, ELIMINACION_PERMANENTE_MASIVA), usuario_id, fecha, detalles.
**usuarios**: Tabla de usuarios; identifica quién eliminó y quién restaura.

---

## 3. Success Criteria

| Criterio | Métrica |
|----------|---------|
| CR-DEL-01 | La lista paginada se carga en menos de 3 segundos con cualquier combinación de filtros |
| CR-DEL-02 | La restauración individual completa la operación en menos de 2 segundos y el registro aparece en observaciones |
| CR-DEL-03 | La eliminación permanente muestra confirmación y elimina el registro en menos de 1 segundo |
| CR-DEL-04 | Las operaciones masivas procesan correctamente todos los registros seleccionados sin excepción |
| CR-DEL-05 | Los filtros combinados devuelven resultados correctos y consistentes con los datos almacenados |
| CR-DEL-06 | El diálogo de confirmación se muestra antes de cualquier eliminación permanente (individual o masiva) |

---

## 4. Assumptions

| ID | Supuesto |
|----|----------|
| ASM-DEL-01 | El mecanismo de soft-delete actual (función `moveToTrash`) copia correctamente todos los campos necesarios a `observaciones_eliminadas` y elimina el registro original |
| ASM-DEL-02 | Solo los usuarios con rol Supervisor tienen acceso al módulo de eliminadas |
| ASM-DEL-03 | El campo `motivo_eliminacion` es obligatorio al momento de eliminar una observación |
| ASM-DEL-04 | La API REST está implementada en `api/eliminadas.php` con las acciones: listar, estadisticas, restaurar, eliminar_permanente, restaurar_masivo, eliminar_permanente_masivo |
| ASM-DEL-05 | La vista se sirve desde `views/eliminadas.php` |
| ASM-DEL-06 | La tabla `observaciones_eliminadas` tiene la misma estructura que `observaciones` más los campos adicionales de eliminación |
| ASM-DEL-07 | La restauración de una observación no valida la existencia de datos referenciales (establecimiento, comuna) para permitir recuperación completa |
