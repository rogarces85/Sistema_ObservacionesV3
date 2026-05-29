## Context

Actualmente los reportes de Plazo de Entrega (tab 2) y Uso Validador (tab 3) en `views/reportes.php` ejecutan consultas SQL que solo cuentan observaciones de una categoría (`plazo_entrega = 'fuera_plazo'` o `usa_validador = 'no'`), mostrando solo el lado negativo. El resultado es una barra por establecimiento sin el contrapeso del lado positivo, lo que da una visión incompleta del cumplimiento.

**Dato relevante:** Una observación pertenece a una serie REM. Un establecimiento puede reportar múltiples series en un mismo mes. La regla de negocio es: si en un mes, para un establecimiento, **alguna** serie cumple una condición, ese mes cuenta como 1 para esa categoría.

## Goals / Non-Goals

**Goals:**
- Mostrar ambos lados en cada reporte (dentro/fuera plazo, usa/no usa validador)
- Agregación correcta por establecimiento+mes (indicador binario mensual)
- Gráfico de barras apiladas (stacked horizontal) por establecimiento
- Tabla mensual detallada color-coded por establecimiento × mes
- Reutilizar endpoint único `api/reports.php?report=...` con nuevos tipos

**Non-Goals:**
- No modificar el modelo de datos (no hay cambios en tablas ni columnas)
- No modificar el reporte general (tab 1, reporte por servicio)
- No modificar el reporte de tipo observación (tab 4)
- No agregar librerías externas nuevas (Chart.js 4.4 ya incluye soporte para stacked bars)
- No modificar lógica de creación/edición de observaciones

## Decisions

### 1. Agregación por establecimiento+mes con subquery o CTE

- **Decisión:** Usar dos niveles de agregación: (1) subquery/CTE por establecimiento+mes con `MAX(CASE...)` para obtener indicadores binarios, (2) `SUM()` sobre esos indicadores agrupado por establecimiento.
- **Alternativa:** Agregar directo sobre observaciones sin pasar por mes. Se descarta porque no reflejaría la regla de negocio (si un establecimiento tiene múltiples series en un mes, la agregación plana inflaría el conteo).
- **Ejemplo SQL (Plazo):**
```sql
WITH per_mes AS (
    SELECT e.id, e.nombre_corto, o.mes,
           MAX(CASE WHEN o.plazo_entrega = 'dentro_plazo' THEN 1 ELSE 0 END) as dentro,
           MAX(CASE WHEN o.plazo_entrega = 'fuera_plazo'  THEN 1 ELSE 0 END) as fuera
    FROM observaciones o
    JOIN establecimientos e ON o.establecimiento_id = e.id
    WHERE o.anio = :anio AND o.plazo_entrega IS NOT NULL AND o.plazo_entrega != ''
    GROUP BY e.id, e.nombre_corto, o.mes
)
SELECT id, nombre_corto,
       SUM(dentro) as meses_dentro, SUM(fuera) as meses_fuera, COUNT(*) as meses_con_datos
FROM per_mes
GROUP BY id, nombre_corto ORDER BY meses_fuera DESC
```

### 2. Endpoint de API único vs separado

- **Decisión:** Un endpoint único `api/reports.php?report=plazo-agregado` y `api/reports.php?report=validador-agregado` que devuelvan JSON con ambos lados (`[{id, nombre_corto, meses_dentro, meses_fuera, meses_con_datos}]`).
- **Alternativa:** Endpoint combinado `?report=plazo-validador` que devuelva ambos reportes juntos. Se descarta porque cada reporte es independiente y se carga por tab distinto.
- **Alternativa:** Modificar los endpoints existentes `?report=plazo` y `?report=validador`. Se descarta para mantener retrocompatibilidad.

### 3. Visualización principal: Barras apiladas horizontales (stacked horizontal bar)

- **Decisión:** Chart.js BarChart con `indexAxis: 'y'` y `scales.x.stacked: true`. Cada establecimiento es una fila, la barra se compone de dos segmentos: verde (dentro plazo / usa validador) y rojo/gris (fuera plazo / no usa validador).
- **Alternativa:** Grouped bar (barras lado a lado). Se descarta porque dificulta la comparación de proporciones por establecimiento.
- **Alternativa:** Pie/donut por establecimiento. Se descarta porque no escala bien con muchos establecimientos.

**Gráfico recomendado:**
```
Estab A  ██████████████████████░░░░░░░  10 dentro | 2 fuera
Estab B  ████████████████████████████░  11 dentro | 1 fuera
Estab C  ██████████░░░░░░░░░░░░░░░░░░  4  dentro | 8 fuera ← problemático
```

### 4. Visualización secundaria: Tabla mensual color-coded

- **Decisión:** Tabla HTML con establecimientos como filas y 12 meses como columnas. Cada celda muestra un indicador visual (círculo verde = dentro, rojo = fuera, ámbar = ambos, gris = sin datos).
- **Alternativa:** Heatmap con Chart.js matrix plugin. Se descarta porque requiere dependencia adicional y la tabla HTML es más accesible e imprimible.

### 5. Nuevos métodos en Observation.php

```php
public function reportePlazoAgregado(int $anio): array
public function reportePlazoMensual(int $anio): array     // detalle mes a mes
public function reporteValidadorAgregado(int $anio): array
public function reporteValidadorMensual(int $anio): array  // detalle mes a mes
```

## Risks / Trade-offs

- **Establecimientos sin datos en ciertos meses**: La tabla mensual mostrará celdas grises (sin datos). El stacked bar solo contará los meses que tengan al menos una observación.
- **Meses con ambas categorías**: Es posible que un mismo establecimiento en un mismo mes tenga series dentro y fuera de plazo. El stacked bar lo reflejará contando ese mes en ambos segmentos — es correcto según la regla de negocio, pero puede confundir visualmente si es muy frecuente. La tabla mensual mostrará un indicador ámbar.
- **Rendimiento**: Las consultas con CTE y subqueries son livianas (filtro por año, joins simples). Sin riesgo para el volumen de datos actual (~miles de observaciones/año).
- **Retrocompatibilidad**: Los endpoints existentes `?report=plazo` y `?report=validador` se mantienen intactos. Cero riesgo de breaking changes.

## Open Questions

- ¿Se deben conservar los gráficos actuales como "vista simple" y agregar la nueva visualización como "vista detallada" (pestañas anidadas dentro del tab), o reemplazar completamente?
- ¿El reporte de Plazo necesita filtro por mes (selector de rango) además del año?
