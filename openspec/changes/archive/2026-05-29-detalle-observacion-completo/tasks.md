## 1. Modal de Registrador (views/observaciones.php)

- [x] 1.1 Agregar fila HTML para `codigo_establecimiento` en `modalDetails` junto al nombre del establecimiento
- [x] 1.2 Agregar fila HTML para `fecha_actualizacion` en `modalDetails` en la sección de fechas
- [x] 1.3 Mapear `obs.codigo_establecimiento` y `obs.fecha_actualizacion` en `viewObservation()`

## 2. Modal de Supervisor (views/supervision.php)

- [x] 2.1 Agregar `codigo_establecimiento` en `showDetailModal()` junto al nombre del establecimiento
- [x] 2.2 Agregar `plazo_entrega` y `usa_validador` en `showDetailModal()` entre Tipo de Error y Serie REM
- [x] 2.3 Agregar `fecha_actualizacion` en `showDetailModal()` en la sección de metadatos

## 3. Verificación

- [ ] 3.1 Abrir detalle de observación como registrador — verificar que se vean todos los campos incluyendo código establecimiento y fecha actualización
- [ ] 3.2 Abrir detalle de observación como supervisor — verificar que se vean código establecimiento, plazo entrega, usa validador y fecha actualización
