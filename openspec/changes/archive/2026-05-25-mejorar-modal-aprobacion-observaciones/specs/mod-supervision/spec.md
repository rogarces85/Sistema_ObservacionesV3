## MODIFIED Requirements

### Requirement: SUP-005 — Ver Detalle Completo

**Descripción**: Visualización exhaustiva de una observación y su historial.

**Endpoint**: `GET /api/supervision.php?action=get_detail&id={id}`

**Reglas de Negocio**:
- Muestra todos los campos de la observación, datos del establecimiento, datos del registrador y supervisor (si aplica).
- **Campos visibles en el modal de detalle**: Establecimiento, Estado, Año/Mes, Registrador, Tipo de Error, Serie REM (`codigo_serie`), Hoja REM (`codigo_hoja`), Detalle de Observación, Respuesta del Establecimiento (`respuesta_establecimiento`, condicional), Clasificación (condicional), Detalle Error (condicional).
- `codigo_serie` y `codigo_hoja` se muestran SIEMPRE en una fila de 2 columnas (grid). Si el valor es vacío, se muestra un placeholder "-".
- `respuesta_establecimiento` se muestra en una fila de ancho completo SOLO si tiene valor (mismo patrón que `clasificacion` y `detalle_error`).
- Muestra la línea de tiempo del historial de cambios de estado.
- Retorna tanto `data` (observación) como `historial` (array de cambios).

#### Scenario: Supervisor ve detalle con todos los campos
- **WHEN** un Supervisor autenticado solicita el detalle de una observación que tiene `codigo_serie`, `codigo_hoja` y `respuesta_establecimiento` con valores
- **THEN** el modal muestra Serie REM y Hoja REM en una fila de 2 columnas después de Tipo de Error
- **AND** el modal muestra Respuesta del Establecimiento en una fila de ancho completo después del Detalle de Observación
- **AND** todos los campos existentes se siguen mostrando (Establecimiento, Estado, Año/Mes, Registrador, Tipo de Error, Detalle, Historial)

#### Scenario: Supervisor ve detalle con campos vacíos
- **WHEN** un Supervisor autenticado solicita el detalle de una observación tipo S/OBSERVACION donde `codigo_hoja` está vacío y `respuesta_establecimiento` está vacío
- **THEN** el modal muestra Serie REM con su valor y Hoja REM con placeholder "-"
- **AND** el modal NO muestra la fila de Respuesta del Establecimiento

#### Scenario: Supervisor ve detalle sin respuesta del establecimiento
- **WHEN** un Supervisor autenticado solicita el detalle de una observación que no tiene `respuesta_establecimiento`
- **THEN** el modal NO muestra la fila de Respuesta del Establecimiento
- **AND** los demás campos se muestran normalmente
