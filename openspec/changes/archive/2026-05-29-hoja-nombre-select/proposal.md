## Why

Actualmente el select de "Hoja REM" en el formulario de nueva observación muestra solo el código de la hoja (ej. `A01`, `BM18`), sin ningún nombre descriptivo. Para el usuario es difícil identificar qué significa cada código. Se necesita agregar un "Hoja Nombre" que acompañe al código en la visualización del select, facilitando la selección correcta.

## What Changes

- Cambiar `$HOJAS_POR_SERIE` de `string[]` a `array[]` con estructura `{codigo: string, nombre: string}`
- Actualizar `loadHojasREM()` para mostrar en el select `codigo - nombre` (ej. `A01 - Atenciones Profesionales`)
- El valor enviado al backend (`value` del option) sigue siendo el `codigo` — no cambia la BD
- Aplicar el cambio a todas las series (SERIE A, BS, BM, P, ANEXO, D)

## Capabilities

### New Capabilities

- `hoja-rem-nombre`: Nombre descriptivo para cada hoja REM en los formularios de observación. Mejora la usabilidad del selector de hoja mostrando código + nombre descriptivo.

### Modified Capabilities

_(Ninguna — solo cambio de presentación en el select)_

## Impact

- **config/constants.php**: Reestructurar `$HOJAS_POR_SERIE` de array plano a array asociativo con `codigo` y `nombre`
- **views/observaciones.php**: Actualizar `loadHojasREM()` para mostrar `codigo - nombre` en el option text, manteniendo `codigo` como value
- **Sin cambios en BD, APIs, modelos ni vistas de detalle/reportes**
