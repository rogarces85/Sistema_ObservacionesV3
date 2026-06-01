## Context

Actualmente `api/supervision.php?action=approve` hardcodea `ESTADO_APROBADO` como estado resultante. El modal de confirmación (`views/supervision.php` línea 190-225) muestra campos de clasificación y detalle error, pero no permite elegir el estado final.

Los reportes de errores (`reporteErroresPorMes`, etc.) filtran por `tipo_error = 'ERROR'`. Actualmente el supervisor no puede modificar `tipo_error`, por lo que las observaciones aprobadas nunca aparecen en reportes de error aunque originalmente fueran de tipo ERROR.

## Goals / Non-Goals

**Goals:**
- El supervisor puede elegir entre "Sin Observación" y "Error" al aprobar
- "Sin Observación" → `estado_actual = "aprobado"` + `tipo_error = "S/OBSERVACION"`
- "Error" → `estado_actual = "error"` + `tipo_error = "ERROR"`
- Las observaciones marcadas como "Error" aparecen en los reportes de errores
- Operación individual (no masiva en esta iteración)

**Non-Goals:**
- No se modifica el flujo de cancelación o eliminación
- No se eliminan los campos existentes de clasificación/detalle error (coexistencia)
- No se modifica la operación masiva (bulk approve)
- No se crean nuevos estados ni constantes

## Decisions

### Decisión 1: Usar radio buttons en lugar de dropdown

**Elegido**: Radio buttons (`<input type="radio">`) para "Sin Observación" / "Error".

**Alternativa considerada**: Dropdown `<select>`. Rechazado porque con solo 2 opciones los radio buttons son más visibles y requieren menos interacción (1 clic vs 2 clics).

**Razonamiento**: La decisión es binaria y crítica — radio buttons fuerzan una elección explícita y son más difíciles de ignorar accidentalmente.

### Decisión 2: Ubicación en el modal — primer campo del bloque `#approveExtraFields`

**Elegido**: Insertar los radio buttons como primer elemento dentro del `<div id="approveExtraFields">`, arriba de los campos existentes (Clasificación dropdown y Detalle Error input).

```
<div id="approveExtraFields" class="hidden mt-4 space-y-4">
    <!-- NUEVO: Selector de estado resultante -->
    <div>
        <label class="form-label">Clasificación de Respuesta *</label>
        <div class="...radio-group...">
            <label><input type="radio" name="estadoResultante" value="sin_observacion"> Sin Observación</label>
            <label><input type="radio" name="estadoResultante" value="error"> Error</label>
        </div>
    </div>
    <!-- EXISTENTE -->
    <div>...Clasificación dropdown...</div>
    <div>...Detalle Error input...</div>
</div>
```

### Decisión 3: Mapeo de valores frontend → backend

| Frontend (`estado_resultante`) | `estado_actual` | `tipo_error` |
|-------------------------------|:---------------:|:------------:|
| `sin_observacion` | `ESTADO_APROBADO` (`"aprobado"`) | `"S/OBSERVACION"` |
| `error` | `ESTADO_ERROR` (`"error"`) | `"ERROR"` |

### Decisión 4: Actualizar `tipo_error` junto con `estado_actual`

**Elegido**: El backend actualiza ambas columnas en la misma operación. Usar `update()` de Observation.php que ya soporta actualizar `tipo_error` a través del array `$data` pasado a `$obsModel->update()`.

**Alternativa considerada**: Hacer dos queries separadas. Rechazado por ineficiente.

**Razonamiento**: `Observation::update()` (línea 121) acepta un array de campos actualizables incluyendo `tipo_error`. No se necesita modificar el modelo.

### Decisión 5: Operación masiva sin cambios

**Elegido**: La operación masiva (`bulkUpdateStatus`) mantiene el comportamiento actual (hardcodea `ESTADO_APROBADO`). El selector de estado solo aplica a aprobación individual.

**Razonamiento**: Las operaciones masivas procesan múltiples observaciones que pueden tener diferentes tipos de error. Asignar un solo estado a todas sería incorrecto. En una iteración futura se podría permitir selección masiva por tipo.

## Risks / Trade-offs

- **[Bajo] Observaciones S/OBSERVACION existentes**: Si un registrador creó una observación con `tipo_error = "S/OBSERVACION"` y el supervisor la aprueba como "Error", el `tipo_error` se sobrescribe a `"ERROR"`. Esto es el comportamiento deseado — el supervisor tiene la última palabra.
- **[Bajo] Operación masiva ignorada**: Las aprobaciones masivas no tendrán el selector de estado. Se documenta en el código como comportamiento esperado.
- **[Mínimo] Radio button sin selección**: Si el supervisor no selecciona ninguna opción y confirma, se debe validar que `estado_resultante` tenga valor antes de enviar.

## Open Questions

- ¿Deberían eliminarse los campos de clasificación/detalle error en una iteración futura? (a evaluar tras uso)
- ¿Debería la operación masiva también soportar el selector de estado? (complejidad adicional, postergado)
