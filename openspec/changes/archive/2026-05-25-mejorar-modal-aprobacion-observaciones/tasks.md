## 1. Modificar modal de detalle en supervision.php

- [x] 1.1 Agregar fila `codigo_serie` y `codigo_hoja` en grid de 2 columnas después de "Tipo de Error" en `showDetailModal()` (línea ~417)
- [x] 1.2 Mostrar placeholder "-" cuando `codigo_serie` o `codigo_hoja` estén vacíos
- [x] 1.3 Agregar fila `respuesta_establecimiento` en ancho completo (`col-span-2`) después de "Detalle de Observación", solo si el campo tiene valor

## 2. Verificación

- [x] 2.1 Probar modal con observación que tenga `codigo_serie`, `codigo_hoja` y `respuesta_establecimiento` con valores — verificar que se muestran los tres campos
- [x] 2.2 Probar modal con observación tipo S/OBSERVACION (`codigo_hoja` vacío) — verificar placeholder "-"
- [x] 2.3 Probar modal con observación sin `respuesta_establecimiento` — verificar que la fila no aparece
- [x] 2.4 Verificar que los campos existentes (Establecimiento, Estado, Año/Mes, Registrador, Tipo Error, Detalle, Clasificación, Detalle Error, Historial) siguen mostrándose correctamente
- [x] 2.5 Verificar que el modal de confirmación (aprobar/cancelar) no se ve afectado
