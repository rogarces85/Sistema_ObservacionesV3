## Context

La vista `views/reportes.php` actual utiliza checkboxes para filtrar meses y comunas, lo que genera problemas de visualización cuando hay muchas opciones. La UI no sigue el mismo patrón que otras vistas del sistema como `supervision.php`, que usa selects con layout de grilla y botones de acción claros.

## Goals / Non-Goals

**Goals:**
- Reemplazar filtros checkbox por selects estilo `supervision.php`.
- Mantener el sistema de 5 pestañas de reportes existente.
- Implementar lógica de filtrado estricta por pestaña.
- Cargar datos vía AJAX al aplicar filtros.

**Non-Goals:**
- No modificar `supervision.php` ni el Dashboard.
- No agregar nuevos endpoints PHP (se reutiliza `api/reports.php`).
- No implementar exportación.

## Decisions

### 1. Selects en lugar de checkboxes
**Decision**: Usar `<select>` para Mes, Comuna y Establecimiento, replicando el diseño de `supervision.php`.
**Rationale**: Los selects ocupan menos espacio visual, son más accesibles en móviles y permiten una jerarquía clara de filtros.

### 2. Carga AJAX con parámetros unificados
**Decision**: Un único endpoint `api/reports.php?report=error-reports` recibe todos los filtros y devuelve datos para las 5 pestañas.
**Rationale**: Reduce requests y mantiene coherencia con la arquitectura actual.

### 3. Filtrado estricto en el backend
**Decision**: La lógica de "solo errores", "solo fuera de plazo", etc. se aplica en el modelo PHP, no en el frontend.
**Rationale**: Evita enviar datos innecesarios al cliente y garantiza consistencia.

### 4. Destrucción y recreación de gráficos
**Decision**: Al cambiar de pestaña o aplicar filtros, se destruyen los charts existentes y se crean nuevos.
**Rationale**: Chart.js no soporta cambios de tipo de gráfico dinámicos; recrear es más limpio.

## Risks / Trade-offs

- **[Riesgo] Usuarios acostumbrados a checkboxes** → **Mitigación**: Los selects son más intuitivos; se mantiene opción "Todos" por defecto.
- **[Riesgo] Performance con muchos datos** → **Mitigación**: Los métodos del modelo ya usan índices; se puede agregar paginación si es necesario.
- **[Trade-off] Select individual vs múltiple para meses** → Se usará select individual por simplicidad; si el usuario necesita múltiples meses, se puede cambiar a `<select multiple>` en iteración futura.

## Migration Plan

1. Crear backup de `views/reportes.php`.
2. Reescribir sección de filtros con nueva estructura.
3. Adaptar `loadErrorReports()` en JavaScript.
4. Ajustar endpoint `api/reports.php` para nuevos parámetros.
5. Verificar que cada pestaña muestra datos correctos según reglas de negocio.
6. Probar responsive en móvil y desktop.
