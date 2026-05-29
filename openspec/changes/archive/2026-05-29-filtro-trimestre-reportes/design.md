## Context

El filtro actual en reportes.php permite seleccionar año, mes (individual), comuna y establecimiento. El backend ya soporta filtrar por múltiples meses mediante el parámetro `meses[]` que se usa en consultas SQL con `o.mes IN (...)`. Los métodos `reportePlazoAgregado` y `reporteValidadorAgregado` (creados en el cambio anterior) son los únicos que aún no aceptan este filtro.

El mes se almacena como string en español (`'Enero'`, `'Febrero'`, etc.) en la columna `o.mes`.

## Goals / Non-Goals

**Goals:**
- Agregar selector de trimestre (Q1-Q4) en el panel de filtros
- Al seleccionar un trimestre, convertir a los 3 meses correspondientes y pasarlos como `meses[]`
- El trimestre tiene prioridad sobre el mes individual (si se selecciona trimestre, se ignora el mes)
- Extender `reportePlazoAgregado` y `reporteValidadorAgregado` para aceptar `$meses[]`
- Todos los tabs de reportes se benefician del filtro trimestre

**Non-Goals:**
- No modificar la lógica de agrupación SQL (solo filtrado WHERE)
- No crear una nueva capability spec (el cambio es puramente UI/filtrado)
- No modificar el almacenamiento de datos ni la lógica de negocios
- No agregar dependencias externas

## Decisions

### 1. Conversión trimestre → meses en frontend vs backend

- **Decisión:** La conversión se hace en el frontend (JS). Un objeto constante mapea `Q1` → `['Enero','Febrero','Marzo']`, etc. Los meses resultantes se envían como `meses[]` al backend, que ya sabe filtrar por array de meses.
- **Alternativa:** Enviar el número de trimestre al backend y que este haga la conversión. Se descarta porque requeriría modificar más endpoints y agregar lógica de mapeo en PHP duplicando la del frontend.
- **Ventaja:** El backend ya soporta `meses[]` — cero cambios en la lógica SQL de los 5 métodos del reporte error-reports.

### 2. Prioridad trimestre vs mes individual

- **Decisión:** Si el usuario selecciona un trimestre, el mes individual se ignora. Si no selecciona trimestre, funciona el mes individual como antes. Lógica: "trimestre anula mes".
- **Alternativa:** Enviar ambos y combinarlos con OR. Se descarta porque complica la UX y no tiene caso práctico (un trimestre ya contiene los meses).

### 3. Mapeo trimestre-meses

```js
const TRIMESTRES = {
    '1': ['Enero','Febrero','Marzo'],
    '2': ['Abril','Mayo','Junio'],
    '3': ['Julio','Agosto','Septiembre'],
    '4': ['Octubre','Noviembre','Diciembre']
};
```

### 4. Extensión de métodos backend

`reportePlazoAgregado` y `reporteValidadorAgregado` reciben un nuevo parámetro `$meses = []`. Dentro del CTE `per_mes`, se agrega `AND o.mes IN (...)` condicional, siguiendo exactamente el mismo patrón usado en los otros 5 métodos de reporte.

## Risks / Trade-offs

- **Compatibilidad:** Los endpoints `plazo-agregado` y `validador-agregado` actualmente no aceptan `meses[]`. Al agregarlo, el parámetro es opcional (`?? []`), así que las llamadas existentes (sin el parámetro) siguen funcionando sin cambios.
- **Rendimiento:** El filtro `o.mes IN (...)` con hasta 3 valores es marginal — no hay impacto.
- **UX:** Si el usuario selecciona trimestre y también un mes, el mes se ignora silenciosamente. Podría ser confuso si no es evidente. Se podría agregar un indicador visual o deshabilitar el mes cuando se selecciona trimestre, pero se considera sobreingeniería para este cambio.
