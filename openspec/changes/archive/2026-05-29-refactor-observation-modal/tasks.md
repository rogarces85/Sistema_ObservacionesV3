## 1. Reestructurar HTML del modal

- [x] 1.1 Reemplazar el contenido de `modal-body` con 3 cards internas (Información General, Detalle, Clasificación) manteniendo `id` y `name` de campos
- [x] 1.2 Agregar `.invalid-feedback` después de cada campo requerido para mensajes de validación
- [x] 1.3 Agregar spinner al botón de guardar con estructura: `<span class="spinner-border spinner-border-sm me-2 d-none" id="btnSaveSpinner"></span>` + texto dinámico

## 2. Refactorizar JavaScript a objeto ObservationForm

- [x] 2.1 Crear objeto `ObservationForm` con propiedades: `modal`, `form`, `btnSave`, `spinner`, `btnText`, `isEditing`, `obsId`
- [x] 2.2 Migrar `openCreateModal` → `ObservationForm.openCreate()`: resetear formulario, cambiar título, mostrar modal
- [x] 2.3 Migrar `editObservation` → `ObservationForm.openEdit(data)`: pre-poblar campos, cambiar título a "Editar Observación", mostrar modal
- [x] 2.4 Migrar `saveObservation` → `ObservationForm.save()`: validación visual con `is-invalid`, spinner en botón, fetch API
- [x] 2.5 Migrar `handleTipoChange` → `ObservationForm.handleTipoChange()`: toggle visibility con `d-none`/`d-block`
- [x] 2.6 Migrar `loadHojasREM` y `loadEstablecimientoCodigo` como métodos internos

## 3. Implementar validación visual

- [x] 3.1 Implementar `ObservationForm.validate()`: recorrer `[required]`, aplicar `is-invalid`/`.invalid-feedback`, retornar booleano
- [x] 3.2 Limpiar validación al abrir modal (eliminar `is-invalid` de todos los campos)
- [x] 3.3 Reemplazar llamada a `validateForm()` global por `ObservationForm.validate()`

## 4. Verificar regresiones

- [x] 4.1 Probar creación de observación: llenar form, guardar, verificar que se recarga la tabla
- [x] 4.2 Probar edición de observación: abrir modal con datos pre-poblados, modificar, guardar
- [x] 4.3 Probar tipo S/OBSERVACION: seleccionar, verificar que campos serie/hoja/respuesta se ocultan
- [x] 4.4 Probar validación: submit con campos vacíos, verificar mensajes de error visuales
- [x] 4.5 Probar estado de carga: verificar spinner y botón deshabilitado durante fetch
- [x] 4.6 Probar cierre con Escape y clic fuera del modal
