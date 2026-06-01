## MODIFIED Requirements

### Requirement: SUP-002 — Aprobar Observación

**Descripción**: Valida una observación permitiendo al supervisor elegir el estado resultante entre "Sin Observación" y "Error".

**Endpoint**: `POST /api/supervision.php?action=approve`

**Reglas de Negocio**:
- **Selector de estado obligatorio**: El supervisor DEBE elegir entre dos opciones mediante radio buttons:
  - **"Sin Observación"** → `estado_actual = ESTADO_APROBADO` (`"aprobado"`) y `tipo_error = "S/OBSERVACION"`. La observación NO aparece en reportes de errores.
  - **"Error"** → `estado_actual = ESTADO_ERROR` (`"error"`) y `tipo_error = "ERROR"`. La observación SÍ aparece en reportes de errores.
- **Datos adicionales (coexistentes)**: El Supervisor puede opcionalmente ingresar `clasificación` (dropdown: corregido, error, sin_respuesta, respuesta_incorrecta) y `detalle_error`.
- **Historial**: Se registra automáticamente la acción con el comentario del supervisor.
- **Operación individual**: El selector de estado solo aplica a aprobación individual. La operación masiva mantiene el comportamiento anterior (estado "aprobado").
- **Payload**: El frontend envía `estado_resultante` con valor `"sin_observacion"` o `"error"`.

#### Scenario: Supervisor aprueba como Sin Observación
- **WHEN** un Supervisor selecciona radio "Sin Observación" y confirma la aprobación
- **THEN** el sistema actualiza `estado_actual` a `"aprobado"` y `tipo_error` a `"S/OBSERVACION"`
- **AND** la observación NO aparece en los reportes de errores (filtrados por `tipo_error = 'ERROR'`)

#### Scenario: Supervisor aprueba como Error
- **WHEN** un Supervisor selecciona radio "Error" y confirma la aprobación
- **THEN** el sistema actualiza `estado_actual` a `"error"` y `tipo_error` a `"ERROR"`
- **AND** la observación SÍ aparece en los reportes de errores (filtrados por `tipo_error = 'ERROR'`)

#### Scenario: Supervisor intenta aprobar sin seleccionar estado
- **WHEN** un Supervisor confirma la aprobación sin haber seleccionado "Sin Observación" ni "Error"
- **THEN** el sistema muestra un mensaje de validación indicando que debe seleccionar una opción
- **AND** no se envía la petición al servidor

#### Scenario: Aprobación masiva sin selector de estado
- **WHEN** un Supervisor aprueba múltiples observaciones en lote
- **THEN** el sistema aplica `estado_actual = "aprobado"` a todas (comportamiento legacy)
- **AND** no se modifica el `tipo_error` de las observaciones
