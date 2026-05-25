## Why

Los supervisores necesitan ver información completa de la observación al momento de aprobarla o rechazarla. Actualmente el modal de detalle en la vista de Supervisión omite tres campos clave — `codigo_serie` (Serie REM), `codigo_hoja` (Hoja REM) y `respuesta_establecimiento` — que ya están disponibles en la respuesta del backend pero no se muestran en la interfaz. Esto obliga al supervisor a navegar a otra vista o adivinar datos que deberían estar visibles durante la revisión.

## What Changes

- **Agregar `codigo_serie` (Serie REM)** al modal de detalle de observación como campo de solo lectura
- **Agregar `codigo_hoja` (Hoja REM)** al modal de detalle de observación como campo de solo lectura
- **Agregar `respuesta_establecimiento`** al modal de detalle de observación como campo de solo lectura

Los datos ya son retornados por `GET /api/supervision.php?action=get_detail` (el SELECT usa `o.*`). El cambio es exclusivamente en la función JavaScript `showDetailModal()` dentro de `views/supervision.php` para renderizar estos tres campos adicionales.

## Capabilities

### New Capabilities
<!-- No new capabilities introduced - this is a UI enhancement to an existing feature -->

### Modified Capabilities
- `mod-supervision`: El modal de detalle (SUP-005) ahora muestra `codigo_serie`, `codigo_hoja` y `respuesta_establecimiento` además de los campos ya existentes.

## Impact

- **Archivo modificado**: `views/supervision.php` — función `showDetailModal()` (líneas ~394–455)
- **Sin cambios en**: API, modelos, base de datos, CSS, constantes
- **Sin dependencias nuevas**: los datos ya existen en la respuesta del endpoint `get_detail`
- **Riesgo**: Mínimo. Es una adición de elementos HTML de solo lectura. No afecta flujos de creación/edición/aprobación.
