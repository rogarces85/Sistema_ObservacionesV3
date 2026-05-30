## Why

El modal de creación/edición de observaciones fue migrado a Bootstrap 5 pero mantiene una estructura plana sin validación visual cohesiva ni estado de carga/bloqueo. Los campos condicionales (S/OBSERVACION, hojas REM) se manejan con lógica JS dispersa, y el formulario carece de indicaciones de validación al usuario. El objetivo es modernizar el modal con un formulario estructurado, feedback visual inmediato y mejor experiencia de edición.

## What Changes

- Reestructurar el formulario del modal con secciones agrupadas (Información general, Detalle, Clasificación)
- Agregar validación visual con clases `is-invalid`/`is-valid` de Bootstrap y mensajes de error inline
- Agregar estado de carga (spinner + disable) en botón de guardar mientras se procesa
- Mejorar la experiencia de edición: feedback visual al cambiar tipo de error (S/OBSERVACION toggle)
- Agregar tecla Escape para cerrar (ya incluida por Bootstrap)
- Centralizar la lógica de visibilidad condicional (hojas REM, respuesta) en funciones reutilizables

### Breaking Changes
- Ninguno. Es refactor puramente frontend, no cambia API ni base de datos.

## Capabilities

### New Capabilities
- `observation-form`: Formulario estructurado de observaciones con validación visual, estados de carga, secciones agrupadas y comportamiento condicional predecible.

### Modified Capabilities
- Ninguna (no hay specs existentes).

## Impact

- `views/observaciones.php`: reemplazo completo del modal `modalObservation` y su JS asociado (saveObservation, editObservation, handleTipoChange, loadHojasREM)
- No afecta API (`api/observations.php`, `api/import.php`)
- No afecta base de datos
- No afecta otros modales (import, details)
