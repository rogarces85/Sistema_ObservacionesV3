## ADDED Requirements

### Requirement: Formulario estructurado con secciones agrupadas

El formulario dentro del modal SHALL estar dividido en 3 secciones visuales:
1. **Información General**: Mes, Establecimiento, Código Establecimiento
2. **Detalle de la Observación**: Tipo de Error, Serie, REM (Hoja), Detalle
3. **Clasificación**: Plazo de Entrega, Usa Validador, Respuesta del Establecimiento

Cada sección SHALL usar una card interna (`.card` anidada con `.card-header` y `.card-body`) o un separator visual con texto (`.form-section-label`) para mantener jerarquía visual.

#### Scenario: Las tres secciones se renderizan al abrir el modal
- **WHEN** el modal `modalObservation` se abre (crear o editar)
- **THEN** el usuario ve tres bloques visuales: Información General, Detalle, Clasificación

### Requirement: Validación visual con Bootstrap

El formulario SHALL usar clases `is-invalid` en campos inválidos y mostrar mensaje de error con `.invalid-feedback` al hacer submit. NO se SHALL usar la clase `was-validated` del form para evitar conflictos con pre-poblado. La validación SHALL ser manual: al hacer submit, se recorren los campos `[required]` y se agrega `is-invalid` al que no tenga valor.

#### Scenario: Submit con campos requeridos vacíos
- **WHEN** el usuario hace clic en "Guardar" sin completar campos requeridos
- **THEN** los campos requeridos vacíos muestran borde rojo (`is-invalid`) y un mensaje de feedback
- **THEN** el formulario NO se envía al servidor

#### Scenario: Submit con todos los campos completos
- **WHEN** el usuario completa todos los campos requeridos y hace clic en "Guardar"
- **THEN** todos los campos tienen clase `is-valid` (opcional)
- **THEN** el formulario se envía al servidor

### Requirement: Estado de carga en botón de guardar

El botón de guardar SHALL mostrar un spinner (`.spinner-border.spinner-border-sm`) y deshabilitarse (`disabled`) mientras la petición al servidor está en curso. El texto del botón SHALL cambiar a "Guardando...". Al finalizar (éxito o error), el botón SHALL restaurar su estado original.

#### Scenario: Guardar con éxito
- **WHEN** el usuario hace clic en "Guardar" y la petición se completa con éxito
- **THEN** el botón vuelve a estado "Guardar" habilitado
- **THEN** el modal se cierra y la página se recarga

#### Scenario: Guardar con error
- **WHEN** el usuario hace clic en "Guardar" y la petición falla
- **THEN** el botón vuelve a estado "Guardar" habilitado
- **THEN** el modal permanece abierto y se muestra mensaje de error

### Requirement: Comportamiento condicional de campos

La visibilidad de los campos "REM (Hoja)" y "Respuesta del Establecimiento" SHALL depender del valor del campo "Tipo de Error" (`tipo_error`). Cuando se selecciona "S/OBSERVACION", el campo "Hoja REM" y "Respuesta" se ocultan (`d-none`). Para cualquier otro tipo, se muestran. El campo "Serie" y "Hoja" solo se habilitan si el tipo de error no es "S/OBSERVACION".

#### Scenario: Seleccionar tipo S/OBSERVACION
- **WHEN** el usuario selecciona "S/OBSERVACION" en Tipo de Error
- **THEN** los campos Serie, REM (Hoja) y Respuesta se ocultan
- **THEN** los valores enviados para esos campos son vacíos

#### Scenario: Seleccionar tipo que no es S/OBSERVACION
- **WHEN** el usuario selecciona un tipo diferente a "S/OBSERVACION"
- **THEN** los campos Serie, REM (Hoja) y Respuesta se muestran
- **THEN** el campo Serie se habilita para selección

### Requirement: Uso de Bootstrap Modal API

El modal SHALL usar Bootstrap 5 Modal API (`bootstrap.Modal`). El cierre con tecla Escape SHALL estar habilitado (por defecto en Bootstrap). No se SHALL usar la función `openModal`/`closeModal` legacy.

#### Scenario: Cerrar con Escape
- **WHEN** el modal está abierto y el usuario presiona Escape
- **THEN** el modal se cierra sin guardar cambios

#### Scenario: Cerrar clic fuera
- **WHEN** el modal está abierto y el usuario hace clic fuera del contenido
- **THEN** el modal se cierra (comportamiento por defecto de `modal-dialog-centered`)

### Requirement: Objeto ObservationForm para lógica JS

La lógica del modal SHALL estar encapsulada en un objeto `ObservationForm` con métodos: `init()`, `open(data?)`, `close()`, `save()`, `handleTipoChange()`, `loadHojasREM()`, `loadEstablecimientoCodigo()`, `validate()`. Los IDs y nombres de campos SHALL mantenerse igual que en la implementación actual.

#### Scenario: Editar observación existente
- **WHEN** `ObservationForm.open(obsData)` se llama con datos de una observación existente
- **THEN** el modal se abre con los campos pre-poblados según `obsData`
- **THEN** el título del modal cambia a "Editar Observación"
- **THEN** el `obsId` oculto contiene el ID de la observación
