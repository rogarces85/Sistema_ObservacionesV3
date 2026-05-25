## 1. Frontend — Modal de confirmación (views/supervision.php)

- [x] 1.1 Agregar radio buttons "Sin Observación" / "Error" dentro de `#approveExtraFields`, arriba de los campos existentes de Clasificación y Detalle Error
- [x] 1.2 Leer valor del radio button seleccionado en `performAction()` y pasarlo a `executeAction()`
- [x] 1.3 Enviar `estado_resultante` en el payload del POST en `executeAction()`
- [x] 1.4 Validar en `performAction()` que se haya seleccionado una opción antes de llamar a `executeAction()` — mostrar alert si no se seleccionó
- [x] 2.1 Leer `estado_resultante` del request en `case 'approve'`
- [x] 2.2 Mapear `"sin_observacion"` → `ESTADO_APROBADO` + `tipo_error = "S/OBSERVACION"` y `"error"` → `ESTADO_ERROR` + `tipo_error = "ERROR"`
- [x] 2.3 Actualizar `tipo_error` junto con `estado_actual` usando `Observation::update()` en la aprobación individual
- [x] 2.4 Mantener comportamiento legacy en aprobación masiva (sin cambios)
- [x] 3.1 Probar aprobación individual con "Sin Observación" — verificar `estado_actual = "aprobado"` y `tipo_error = "S/OBSERVACION"`
- [x] 3.2 Probar aprobación individual con "Error" — verificar `estado_actual = "error"` y `tipo_error = "ERROR"`
- [x] 3.3 Verificar que observación marcada como "Error" aparece en reporte de errores
- [x] 3.4 Verificar que observación marcada como "Sin Observación" NO aparece en reporte de errores
- [x] 3.5 Verificar que aprobación masiva sigue funcionando (sin selector de estado)
- [x] 3.6 Verificar validación: intentar aprobar sin seleccionar radio button muestra mensaje de error
