## Why

Actualmente el supervisor solo puede "Aprobar" una observación, seteando rígidamente `estado_actual = "aprobado"`. No puede distinguir si la observación era válida (sin errores) o si confirmó un error real que debe aparecer en los reportes de errores. Se necesita que el supervisor pueda clasificar la respuesta en dos categorías al aprobar: "Sin Observación" (el reporte está bien) o "Error" (se confirma el error, debe contabilizarse en reportes).

## What Changes

- Agregar selector "Clasificación de Respuesta" con radio buttons "Sin Observación" y "Error" en el modal de confirmación de aprobación (`#confirmModal`)
- Enviar `estado_resultante` en el payload del POST a `api/supervision.php?action=approve`
- Backend: mapear "Sin Observación" → `estado_actual = "aprobado"` + `tipo_error = "S/OBSERVACION"`, "Error" → `estado_actual = "error"` + `tipo_error = "ERROR"`
- Los campos existentes "Clasificación" (dropdown) y "Detalle Error" se mantienen coexistiendo temporalmente

## Capabilities

### New Capabilities
<!-- No new capabilities — this is a modification of SUP-002 -->

### Modified Capabilities
- `mod-supervision`: La acción de aprobar (SUP-002) ahora incluye un selector obligatorio de estado resultante ("Sin Observación" / "Error") que determina el `estado_actual` y `tipo_error` final de la observación, en lugar del hardcodeo actual a `ESTADO_APROBADO`.

## Impact

- `views/supervision.php`: HTML del modal de confirmación + lógica JS de `performAction()` y `executeAction()`
- `api/supervision.php`: `case 'approve'` — dejar de hardcodear `ESTADO_APROBADO`, usar el valor recibido del frontend
- Reportes de errores (`api/reports.php`, `models/Observation.php`): sin cambios — ya filtran por `tipo_error = 'ERROR'`, que ahora se setea correctamente
- Sin nuevos estados ni constantes — `ESTADO_APROBADO` y `ESTADO_ERROR` ya existen
