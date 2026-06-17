# Research: Cerrar Gap F4 — UX de Carga y Error por Categoría

**Branch**: `[003-f4-categoria-ux]` | **Date**: 2026-06-17
**Spec**: [spec.md](spec.md)

## Preguntas de investigación resueltas

### 1. ¿Qué componente spinner usar para el indicador de carga?

**Decisión**: Usar `.spinner-border` de Bootstrap 5 (ya incluido vía Tabler Core 1.4).

**Rationale**:
- Tabler Core 1.4 incluye Bootstrap 5 como dependencia transitiva; `.spinner-border` es nativo.
- Es consistente con el resto de la UI del sistema (otros spinners en `assets/js/importacion.js:285` y `assets/js/reportes.js:494` usan `mostrarCargando(true)` con clases Tabler).
- No requiere nuevas dependencias ni assets.
- Es accesible (animación CSS pura, sin JS adicional).

**Alternativas consideradas**:
- **ApexCharts loader**: Rechazado porque solo aplica al chart, no al estado de la categoría completa (tabla + indicador + estado).
- **Skeleton screens** (placeholders animados): Descartado por costo de implementación vs. valor: el usuario solo espera 1-2s; un spinner es suficiente.
- **Texto plano "Cargando..."** (implementación actual): Es lo que existe y se va a reemplazar; insuficiente como affordance visual.

### 2. ¿Cómo estructurar el estado de error con botón "Reintentar"?

**Decisión**: HTML inline dentro del div `[data-estado-categoria]`, generado por una nueva función `setEstadoError(categoria, mensaje)` que inyecta texto + botón.

```html
<div class="reportes-analytics__estado reportes-analytics__estado--error" data-estado-categoria="errores_establecimiento">
    <p class="mb-2">No fue posible cargar esta categoría.</p>
    <button type="button" class="btn btn-sm btn-primary" onclick="recargarCategoria('errores_establecimiento')">
        <i class="ti ti-refresh"></i> Reintentar
    </button>
</div>
```

**Rationale**:
- Inline porque el estado es dinámico y específico por categoría; no vale la pena un template HTML aparte.
- `onclick` directo para simplicidad; consistente con handlers existentes en el archivo (línea 695 usa `tab.dataset.categoria`).
- Icono `ti-refresh` de Tabler Icons (sin nuevas dependencias; ya en `assets/libs/@tabler/core`).
- Texto del mensaje en español, descriptivo y en lenguaje natural.

**Alternativas consideradas**:
- **Event delegation en contenedor padre**: Descartado por complejidad innecesaria para un solo botón por categoría.
- **Modal de error genérico**: Sobredimensionado para este caso; el error es por-categoría, no global.
- **Toast notification**: Descartado porque desvía la atención del usuario fuera del panel afectado; el usuario debe ver el error y el reintento en contexto.

### 3. ¿Cómo evitar solicitudes duplicadas al hacer click rápido en "Reintentar"?

**Decisión**: Deshabilitar el botón mientras la nueva consulta está en curso, vía `setBotonReintentarHabilitado(categoria, habilitado)`.

**Rationale**:
- Es el patrón estándar de UX (botón deshabilitado indica "acción en curso").
- Cumple FR-007 explícitamente.
- Implementación trivial: una línea que setea `boton.disabled = !habilitado` en el inicio de `recargarCategoria()` y al recibir respuesta.

**Alternativas consideradas**:
- **Debounce con setTimeout**: Más complejo, no aporta valor real sobre el disabled simple.
- **Lock con variable booleana por categoría**: Ya existe `datosAnaliticos[categoria]`; se puede usar un nuevo mapa `reintentosEnCurso = {}` con la misma forma. Igual de válido pero más verboso.

### 4. ¿La función `recargarCategoria` debe recargar solo la categoría o las 5?

**Decisión**: Solo la categoría específica. Implementación:

```javascript
async function recargarCategoria(categoria) {
    setEstadoCargando(categoria);
    setBotonReintentarHabilitado(categoria, false);
    try {
        const response = await fetchAPI('api/reports.php', {
            method: 'GET',
            params: construirParams(obtenerFiltrosAnaliticos(), { report: 'reportes-analiticos', categoria })
        });
        // procesar respuesta y renderizar
    } catch (error) {
        setEstadoError(categoria, 'No fue posible cargar esta categoría.');
    } finally {
        setBotonReintentarHabilitado(categoria, true);
    }
}
```

**Rationale**:
- Cumple FR-005 (reintento solo de la categoría fallida) y SC-005 (aislamiento entre categorías).
- Eficiente: una sola consulta en lugar de 5.
- Consistente con el patrón existente de `cargarReportesAnaliticos` que ya hace el fetch de las 5.

**Alternativas consideradas**:
- **Recargar las 5 categorías**: Innecesario; las demás ya cargaron exitosamente y están renderizadas.
- **Refactor completo del fetch**: Riesgo de regresión; mejor extraer solo lo necesario.

### 5. ¿El mensaje de error debe variar según el tipo de error (timeout, 500, JSON)?

**Decisión**: Mensaje genérico en español "No fue posible cargar esta categoría." + sugerencia de reintentar. Sin distinción visible del tipo de error técnico.

**Rationale**:
- Cumple FR-004 (mensaje claro en lenguaje natural, no técnico) y US3.
- Distinguir el tipo de error requeriría parsear el `error` y traducir; agrega complejidad sin valor para el usuario final.
- Si el usuario reporta "no carga X", el log del servidor (con stack trace) es la fuente de debug, no la UI.

**Alternativas consideradas**:
- **Mensajes específicos por tipo**: "Tiempo de espera agotado" vs "Error del servidor": marginal improvement; no justificado.
- **Mostrar stack trace parcial**: Anti-patrón; expone detalles internos.

### 6. ¿Cómo afecta esto al contrato de UI del feature 002?

**Decisión**: El contrato `contracts/ui-reportes-analiticos.md` del feature 002 se mantiene; este feature agrega un addendum `contracts/ui-estados-carga-error.md` que documenta los 3 estados explícitos (cargando, error-con-reintento, sin-cambios). No se modifica el contrato previo para preservar trazabilidad.

**Rationale**:
- Cumplimiento de la Constitución VI (trazabilidad documental): cada cambio tiene su propio contrato.
- Los tests/validaciones del feature 002 siguen siendo válidos; este feature es aditivo.

## Resumen de hallazgos

| # | Tema | Decisión |
|---|------|----------|
| 1 | Spinner | `.spinner-border` de Bootstrap (Tabler) |
| 2 | Botón Reintentar | HTML inline con `tablerIcon('refresh')` y `onclick` directo |
| 3 | Anti-duplicado | Disabled del botón mientras consulta en curso |
| 4 | Alcance del reintento | Solo la categoría fallida (1 fetch) |
| 5 | Mensaje de error | Genérico en español, sin distinción de tipo técnico |
| 6 | Contrato UI | Addendum `ui-estados-carga-error.md`, no se modifica el previo |

Todas las decisiones son consistentes con Constitución v1.1.0, el spec, y la arquitectura existente. No hay [NEEDS CLARIFICATION] pendientes.
