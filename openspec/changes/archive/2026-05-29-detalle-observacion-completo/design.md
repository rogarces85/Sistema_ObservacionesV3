## Context

Actualmente existen dos modales de detalle de observación:

- **Modal A** (`views/observaciones.php`, registrador): Muestra ~16 campos. Le falta `codigo_establecimiento` (se muestra readonly en el formulario de creación) y `fecha_actualizacion`.
- **Modal B** (`views/supervision.php`, supervisor): Muestra ~12 campos. Le falta `codigo_establecimiento`, `plazo_entrega`, `usa_validador` y `fecha_actualizacion`.

Ambos modales obtienen los datos desde endpoints que devuelven `o.*` (todas las columnas), incluyendo `codigo_establecimiento`, `plazo_entrega`, `usa_validador` y `fecha_actualizacion`. Los datos ya están disponibles en la respuesta JSON — solo falta mostrarlos en el HTML.

## Goals / Non-Goals

**Goals:**
- Modal A: agregar `codigo_establecimiento` y `fecha_actualizacion`
- Modal B: agregar `codigo_establecimiento`, `plazo_entrega`, `usa_validador` y `fecha_actualizacion`
- Mantener el diseño visual consistente con los campos existentes

**Non-Goals:**
- No modificar APIs, modelos ni base de datos
- No modificar el formulario de creación/edición
- No modificar la lógica de negocio

## Decisions

### 1. Datos ya disponibles, solo falta renderizarlos

Ambos endpoints (`api/observations.php?id=X` y `api/supervision.php?action=get_detail&id=X`) ejecutan `Observation::getById()` que hace `SELECT o.*, ... JOIN ...`. La columna `codigo_establecimiento` viene del JOIN con `e.codigo_establecimiento`, y `plazo_entrega`, `usa_validador`, `fecha_actualizacion` vienen de `o.*`. No hay que tocar backend.

### 2. Ubicación de los nuevos campos en cada modal

**Modal A** (registrador):
- `codigo_establecimiento`: junto al nombre del establecimiento
- `fecha_actualizacion`: en la sección de fechas, junto a fecha_registro

**Modal B** (supervisor):
- `codigo_establecimiento`: junto al nombre del establecimiento
- `plazo_entrega` y `usa_validador`: en una nueva fila, entre "Tipo de Error" y "Serie REM"
- `fecha_actualizacion`: en la sección de metadatos

### 3. Formato de fecha_actualizacion

Usar el mismo formato que `fecha_registro` (ej. `date('d/m/Y H:i')`). Si es NULL, mostrar `-`.

## Risks / Trade-offs

- **Riesgo mínimo:** Solo se agregan elementos HTML y asignaciones JS. No hay cambios en flujo de datos. Si un campo es NULL, se muestra `-`.
- **Consistencia:** El modal B (supervisor) usa innerHTML dinámico. Se debe mantener el mismo patrón de template strings para los nuevos campos.
