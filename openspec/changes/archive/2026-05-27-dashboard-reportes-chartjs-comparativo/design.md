## Context

El Dashboard actual renderiza tres visualizaciones con CSS puro:
- **D1 (Distribución por Estado)**: barras horizontales animadas con porcentajes.
- **D2 (Top Tipos de Error)**: lista numerada con ranking.
- **D3 (Observaciones por Mes)**: barras verticales cuya altura se calcula en PHP.

Esto limita la interactividad (no hay tooltips), obliga a recargar la página para cambiar filtros y no permite comparar períodos. La vista Reportes ya usa Chart.js, por lo que hay una inconsistencia tecnológica.

## Goals / Non-Goals

**Goals:**
- Unificar todas las visualizaciones del Dashboard a Chart.js.
- Permitir comparar dos años en el gráfico de observaciones por mes.
- Mostrar valores numéricos directamente sobre las barras en D1 y D2.
- Cargar datos vía AJAX para no recargar la página al filtrar.
- Agregar botones rápidos de trimestre/semestre en filtros de meses.

**Non-Goals:**
- No modificar los 5 gráficos de la vista Reportes (solo filtros de meses).
- No implementar exportación de gráficos a imagen/PDF.
- No agregar comparativo de años en Reportes.
- No migrar el backend a un framework.

## Decisions

### 1. Chart.js + plugin datalabels vía CDN
**Decision**: Usar Chart.js 4.4 (ya cargado) + `chartjs-plugin-datalabels` 2.2 vía CDN en el layout compartido.
**Rationale**: El plugin es oficial, ligero (~10KB) y resuelve el requerimiento de mostrar valores visibles sin hacks. Se registra globalmente con `Chart.register(ChartDataLabels)`.
**Alternativa considerada**: Renderizar texto manualmente con el API `afterDatasetsDraw` de Chart.js — rechazada por complejidad y fragilidad.

### 2. Carga AJAX con endpoint unificado
**Decision**: Crear un nuevo endpoint `api/reports.php?report=dashboard-stats` que devuelva TODOS los datos del Dashboard en un solo JSON.
**Rationale**: Reduce requests (1 vs 3), mantiene coherencia con el endpoint existente `error-reports`. El frontend inicializa los 3 gráficos con una sola llamada.
**Payload esperado**:
```json
{
  "success": true,
  "data": {
    "por_estado": [{"estado_actual": "pendiente", "total": 12}, ...],
    "por_tipo_error": [{"tipo_error": "Tipo A", "total": 8}, ...],
    "por_mes": {
      "2024": [{"mes": "Enero", "total": 5}, ...],
      "2025": [{"mes": "Enero", "total": 8}, ...]
    }
  }
}
```

### 3. Comparativo de 2 años — barras agrupadas
**Decision**: En D3, usar barras agrupadas (`bar` estándar con múltiples datasets), no barras apiladas.
**Rationale**: Las barras agrupadas permiten comparar cantidades absolutas mes a mes. Las apiladas confunden porque sumarían observaciones de dos años, lo cual no tiene sentido de negocio.
**Colores**: Año actual en `#0ea5e9`, año anterior en `#94a3b8` (gris) para diferenciación clara.

### 4. Filtros de meses — estado centralizado en JS
**Decision**: Guardar el estado de meses seleccionados en un array JS (`selectedMeses`). Los checkboxes individuales y los botones rápidos (Q1, Q2, etc.) modifican ese array y luego disparan `loadDashboardStats()`.
**Rationale**: Los botones rápidos son "macros" que seleccionan/deseleccionan checkboxes. Mantener un estado centralizado evita inconsistencias entre la UI y los parámetros enviados al API.
**Mapeo de botones**:
- Q1: Ene, Feb, Mar
- Q2: Abr, May, Jun
- Q3: Jul, Ago, Sep
- Q4: Oct, Nov, Dic
- H1: Ene-Jun
- H2: Jul-Dic
- Todos: 12 meses

### 5. Adaptación del modelo `getStats()`
**Decision**: Extender `Observation::getStats($years, $meses, $userId, $userRole)` para aceptar arrays de años y meses en lugar de un solo año.
**Rationale**: El método actual solo filtra por `anio = ?`. Para el comparativo se necesita `anio IN (?, ?)` y `mes IN (?, ?, ...)`.
**Query adaptada**:
```php
$yearPlaceholders = implode(',', array_fill(0, count($years), '?'));
$where = "WHERE o.anio IN ($yearPlaceholders)";
if (!empty($meses)) {
    $mesPlaceholders = implode(',', array_fill(0, count($meses), '?'));
    $where .= " AND o.mes IN ($mesPlaceholders)";
}
```

### 6. D2 pasa de lista a barras horizontales
**Decision**: Reemplazar la lista numerada de Top Tipos de Error por un gráfico de barras horizontales de Chart.js, igual que D1.
**Rationale**: Unifica la experiencia visual, aprovecha tooltips y data labels. El ranking visual (orden de mayor a menor) se mantiene por el orden de los datos.

## Risks / Trade-offs

- **[Riesgo] Plugin datalabels puede saturar visualmente** cuando hay muchos tipos de error → **Mitigación**: Mostrar datalabels solo en D1 y D2 (no en D3). Si hay >15 tipos de error, mostrar solo los Top 10.
- **[Riesgo] Consulta con múltiples años + meses puede ser lenta** → **Mitigación**: La tabla `observaciones` tiene índices en `anio`, `mes` y `establecimiento_id`. Si hay problemas de performance, se puede agregar un índice compuesto `(anio, mes, tipo_error)`.
- **[Riesgo] Inconsistencia visual si Chart.js no carga** → **Mitigación**: Mostrar un estado de carga (spinner) y un mensaje de fallback si el JSON falla.
- **[Trade-off] Año comparativo limitado a 2** → Permite una visualización limpia, pero no permite comparar 3+ años. El usuario puede cambiar la selección dinámicamente.

## Migration Plan

1. Agregar CDN de `chartjs-plugin-datalabels` al layout compartido.
2. Crear endpoint `dashboard-stats` en `api/reports.php`.
3. Adaptar `Observation::getStats()` para arrays de años/meses.
4. Reescribir `views/dashboard.php`: reemplazar HTML estático por `<canvas>` y controles de filtros.
5. Extender `assets/js/charts.js` con funciones `createDashboardEstadoChart`, `createDashboardTiposChart`, `createDashboardMesesChart`.
6. Agregar helpers de filtros en `assets/js/app.js`.
7. Agregar botones rápidos de meses en `views/reportes.php`.
8. Probar con datos reales: verificar performance del endpoint y legibilidad de data labels.

## Open Questions

- ¿Se necesita un estado de "cargando" mientras llegan los datos AJAX? (Sí, recomendado — spinner en cada card).
- ¿El orden de los tipos de error en D2 debe ser estrictamente Top 10 o se muestran todos? (Recomendación: Top 10 para no saturar).
