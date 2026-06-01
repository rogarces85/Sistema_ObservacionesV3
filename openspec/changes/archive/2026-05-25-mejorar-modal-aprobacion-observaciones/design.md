## Context

Actualmente el modal de detalle de observación en la vista de Supervisión (`views/supervision.php`, función `showDetailModal()`) muestra los siguientes campos: Establecimiento, Estado, Año/Mes, Registrador, Tipo de Error, Detalle de Observación, Clasificación (condicional), Detalle Error (condicional) e Historial de Cambios.

El endpoint `GET /api/supervision.php?action=get_detail&id={id}` retorna `o.*` (todas las columnas de `observaciones`), incluyendo `codigo_serie`, `codigo_hoja` y `respuesta_establecimiento`. Estos tres campos ya existen en la respuesta JSON pero **no se renderizan** en el modal.

El layout del modal usa CSS grid de 2 columnas: `<div class="grid grid-cols-2 gap-4 mb-6">`.

## Goals / Non-Goals

**Goals:**
- Mostrar `codigo_serie` (Serie REM) en el modal de detalle como texto de solo lectura
- Mostrar `codigo_hoja` (Hoja REM) en el modal de detalle como texto de solo lectura
- Mostrar `respuesta_establecimiento` en el modal de detalle como texto de solo lectura
- Mantener consistencia visual con los campos existentes (mismo estilo, grid, tipografía)

**Non-Goals:**
- No se harán editables estos campos en el modal de detalle
- No se modifica el endpoint de la API
- No se modifican modelos ni base de datos
- No se agregan validaciones ni lógica de negocio nueva
- No se modifica el modal de confirmación de acciones (aprobar/cancelar)

## Decisions

### Decisión 1: Ubicación de los nuevos campos en el grid

**Elegido**: Agregar `codigo_serie` y `codigo_hoja` en una fila de 2 columnas (uno en cada columna) inmediatamente después de "Tipo de Error". Agregar `respuesta_establecimiento` en una fila de ancho completo (`col-span-2`) después de "Detalle de Observación".

**Alternativa considerada**: Agregar los tres campos juntos en una sección separada. Rechazado porque rompe la agrupación lógica existente.

**Razonamiento**:
- `codigo_serie` y `codigo_hoja` son atributos del reporte REM — van naturalmente cerca de "Tipo de Error"
- `respuesta_establecimiento` es información del establecimiento — va después del detalle de observación, antes de clasificación/detalle error

```
Antes:
┌─────────────────────┬─────────────────────┐
│ Establecimiento     │ Estado              │
├─────────────────────┼─────────────────────┤
│ Año/Mes             │ Registrador         │
├─────────────────────┴─────────────────────┤
│ Tipo de Error                             │
├───────────────────────────────────────────┤
│ Detalle de Observación                    │
├───────────────────────────────────────────┤
│ Clasificación (condicional)               │
├───────────────────────────────────────────┤
│ Detalle Error (condicional)               │
└───────────────────────────────────────────┘

Después:
┌─────────────────────┬─────────────────────┐
│ Establecimiento     │ Estado              │
├─────────────────────┼─────────────────────┤
│ Año/Mes             │ Registrador         │
├─────────────────────┴─────────────────────┤
│ Tipo de Error                             │
├─────────────────────┬─────────────────────┤  ← NUEVA FILA
│ Serie REM           │ Hoja REM            │  ← NUEVA FILA
├─────────────────────┴─────────────────────┤
│ Detalle de Observación                    │
├───────────────────────────────────────────┤
│ Respuesta del Establecimiento             │  ← NUEVA FILA (condicional si tiene valor)
├───────────────────────────────────────────┤
│ Clasificación (condicional)               │
├───────────────────────────────────────────┤
│ Detalle Error (condicional)               │
└───────────────────────────────────────────┘
```

### Decisión 2: Visibilidad condicional

**Elegido**: `codigo_serie` y `codigo_hoja` se muestran **siempre** (son parte de la información básica del reporte, incluso si están vacíos). `respuesta_establecimiento` se muestra **solo si tiene valor** (siguiendo el patrón existente de `clasificacion` y `detalle_error`).

**Razonamiento**:
- Serie y Hoja REM son datos estructurales del reporte — deben ser visibles aunque estén vacíos para que el supervisor sepa que no se especificaron
- Respuesta del establecimiento es un dato complementario — mostrarlo solo cuando existe evita ruido visual

### Decisión 3: Sin cambios en el backend

**Elegido**: No modificar `api/supervision.php` ni `models/Observation.php`. El endpoint `get_detail` ya retorna todos los campos necesarios (`o.*`).

**Alternativa considerada**: Ninguna. Los datos ya están disponibles.

## Risks / Trade-offs

- **[Mínimo] Campo vacío mostrado**: Si `codigo_serie` o `codigo_hoja` vienen vacíos (ej. observaciones tipo S/OBSERVACION donde `codigo_hoja` es opcional), el modal mostrará texto vacío. → Se mitiga mostrando un placeholder "-" cuando el valor es falsy.
- **[Mínimo] Texto largo en respuesta_establecimiento**: Si la respuesta es muy extensa, puede ocupar mucho espacio vertical. → Se mitiga usando `whitespace-pre-wrap` (mismo patrón que detalle_observacion) y limitando la altura máxima del modal con overflow-y.
