## Why

El modal de detalle de observaciones no muestra todos los campos que se registran al crear una observación. Falta `codigo_establecimiento` (código interno del establecimiento) en ambos modales (registrador y supervisor), y en el modal de supervisor faltan además `plazo_entrega` y `usa_validador`. Para quienes revisan observaciones, ver la información completa es esencial sin tener que abrir el formulario de edición.

## What Changes

- Modal de detalle (registrador, `views/observaciones.php`): Agregar `codigo_establecimiento`
- Modal de detalle (supervisor, `views/supervision.php`): Agregar `codigo_establecimiento`, `plazo_entrega`, `usa_validador`
- Ambos modales: Agregar `fecha_actualizacion` (última modificación)
- No se modifican APIs ni modelos — los datos ya vienen en `o.*` de las consultas existentes

## Capabilities

### New Capabilities

- `detalle-observacion-completo`: Visualización completa de todos los campos de una observación en los modales de detalle, tanto para registradores como supervisores.

### Modified Capabilities

_(Ninguna — solo cambios de presentación en vistas)_

## Impact

- **views/observaciones.php**: Agregar filas en `modalDetails` para `codigo_establecimiento` y `fecha_actualizacion` + mapeo en `viewObservation()`
- **views/supervision.php**: Agregar filas en `detailModal` para `codigo_establecimiento`, `plazo_entrega`, `usa_validador`, `fecha_actualizacion` + mapeo en `showDetailModal()`
- Sin cambios en backend, APIs, base de datos ni modelos
