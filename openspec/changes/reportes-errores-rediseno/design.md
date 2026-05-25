## Context

La vista actual de reportes (`views/reportes.php`, 684 líneas) implementa 6 tabs con 20+ gráficos y tablas. La nueva vista se enfoca exclusivamente en 5 gráficos de errores con filtros multi-select. Se reutiliza la infraestructura existente: Chart.js para gráficos, el endpoint `api/reports.php` para datos, y el modelo `Observation.php` para consultas.

## Goals / Non-Goals

**Goals:**
- 5 gráficos de errores con filtros por año, meses (múltiple) y comunas (múltiple)
- Gráfico ①: Errores por Establecimiento (bar horizontal)
- Gráfico ②: Fuera de Plazo por Establecimiento (bar vertical)
- Gráfico ③: No usa Validador por Establecimiento (bar vertical)
- Gráfico ④: Errores por Serie REM (bar horizontal)
- Gráfico ⑤: Errores por Hoja REM (bar vertical)
- Cada gráfico con su tabla de datos debajo
- Filtros aplicados vía query params al backend

**Non-Goals:**
- No se modifica la exportación (sigue vía `api/export.php`)
- No se agregan reportes de texto o descargables en esta iteración
- No se modifican los KPIs del dashboard principal
- No se toca el resto de tabs (General, Validador, etc.) — se eliminan

## Decisions

### Decisión 1: Layout de 2 columnas para gráficos ①-④, ancho completo para ⑤

```
┌────────────────────┬────────────────────┐
│  ① Errores x Est.  │  ② Fuera Plazo x E.│
├────────────────────┼────────────────────┤
│  ③ No Validador x E│  ④ Errores x Serie │
├────────────────────┴────────────────────┤
│  ⑤ Errores x Hoja REM                   │
└─────────────────────────────────────────┘
```

**Razonamiento**: El gráfico de hojas REM suele tener más elementos (40+ hojas) y necesita más espacio horizontal.

### Decisión 2: Filtros como arrays en query string

**Elegido**: `GET /api/reports.php?report=error-reports&year=2026&meses[]=Enero&meses[]=Febrero&comuna_ids[]=1&comuna_ids[]=3`

**Alternativa**: Enviar como JSON en POST. Rechazado: GET es más cacheable y simple para el frontend.

### Decisión 3: Parámetros opcionales en métodos existentes (backward compatible)

**Elegido**: Agregar `$meses = []` y `$comunaIds = []` al final de la firma.

```php
public function reporteErroresPorEstablecimiento($year, $userId = null, $userRole = null, $meses = [], $comunaIds = [])
```

**Razonamiento**: Los callers existentes no pasan estos parámetros, por lo que el comportamiento por defecto (sin filtros) se mantiene. No hay breaking changes.

### Decisión 4: Endpoint único que devuelve los 5 datasets

**Elegido**: `report=error-reports` devuelve un JSON con las 5 claves. El frontend hace una sola llamada.

**Alternativa**: 5 endpoints separados. Rechazado: 5 requests HTTP vs 1, más lento y complejo en el frontend.

### Decisión 5: Gráficos genéricos reutilizables

**Elegido**: `createBarHorizontal(canvasId, labels, values, color)` y `createBarVertical(canvasId, labels, values, color)`. Ambos aceptan arrays de strings (labels), números (values) y un color base.

**Razonamiento**: Los 5 gráficos son barras con distintos datos pero misma estructura visual. Una función genérica evita duplicar 5 funciones casi idénticas.

## Risks / Trade-offs

- **[Medio] Rendimiento con muchos filtros**: Si se seleccionan 12 meses y 10 comunas, el SQL puede traer muchos datos. → Mitigación: Los métodos usan índices existentes (`anio`, `tipo_error`, `establecimiento_id`). Si es necesario se agregan índices compuestos.
- **[Bajo] Scroll en gráficos con muchos items**: Hoja REM puede tener 40+ barras. → Mitigación: Altura dinámica del canvas (mínimo 300px, +20px por barra extra).
- **[Bajo] Eliminación de tabs**: Los usuarios acostumbrados a los tabs anteriores pierden acceso. → Mitigación: La funcionalidad eliminada (tabs General, Validador, PDF Detallado) se puede recuperar en futuras iteraciones si se solicita.
