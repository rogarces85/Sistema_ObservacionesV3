## Context

El modal `modalObservation` en `views/observaciones.php` usa Bootstrap 5 (`modal fade`, `modal-dialog modal-lg`) y contiene un formulario con campos para mes, establecimiento, tipo de error, serie REM, hoja REM, detalle, plazo, validador y respuesta. La validación es manual (función `validateForm`), el estado de carga se maneja con `showLoading()`/`hideLoading()` global, y la visibilidad de hojas REM y respuesta depende de `handleTipoChange()` con manipulación directa de `style.display`.

El JS asociado incluye `saveObservation()`, `editObservation()`, `handleTipoChange()`, `loadHojasREM()` y `loadEstablecimientoCodigo()` — todas funciones sueltas sin agrupación lógica.

## Goals / Non-Goals

**Goals:**
- Reestructurar el formulario en secciones visuales con cards internas o separadores
- Implementar validación visual Bootstrap (`is-invalid`/`is-valid` + feedback messages)
- Agregar estado de carga en el botón submit (spinner + disabled)
- Consolidar la lógica condicional en un par de funciones manejables
- Unificar el aspecto visual con el resto del sistema Tabler

**Non-Goals:**
- No cambiar la API REST ni el modelo de datos
- No migrar a un framework JS (sigue siendo vanilla ES6+)
- No modificar los otros modales (import, details)
- No agregar nuevas validaciones de negocio (solo visuales)

## Decisions

| Decisión | Opción elegida | Alternativas | Razón |
|----------|---------------|--------------|-------|
| Agrupación visual | Cards internas dentro del modal-body | Tabs, acordeón | El formulario es compacto; cards internas son más simples y consistentes con Tabler |
| Validación | Bootstrap `is-invalid` + `.invalid-feedback` | JS nativo, HTML5 constraint API | Consistente con el ecosistema Bootstrap, mensajes inline, no requiere librería extra |
| Estado de carga | `btn-primary` con spinner + `disabled` | overlay global | Más directo, menos intrusivo, el botón comunica visualmente que algo ocurre |
| Manejo de estado | Clases `d-none`/`d-block` en lugar de `style.display` | `hidden` attribute | Consistente con Bootstrap, aprovecha clases utilitarias ya cargadas |
| Organización JS | Objeto `ObservationForm` con métodos | Funciones globales sueltas | Encapsula estado y lógica, evita contaminación del scope global |

## Risks / Trade-offs

- **Riesgo:** La validación Bootstrap `was-validated` puede interferir con la lógica de edición (pre-poblado de datos).  
  **Mitigación:** Usar validación manual con `is-invalid` en lugar de `was-validated` en el form.
- **Riesgo:** El refactor JS puede romper la funcionalidad de edición si no se respetan los IDs de campos.  
  **Mitigación:** Mantener todos los `id` y `name` actuales, solo cambiar lógica de presentación.
- **Trade-off:** Usar objeto `ObservationForm` añade complejidad a cambio de organización. Justificado porque hay 5+ funciones relacionadas con el mismo modal.
