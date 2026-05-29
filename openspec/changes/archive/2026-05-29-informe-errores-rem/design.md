## Context

El dashboard actual (`views/dashboard.php`) muestra indicadores generales y charts, pero carece de un informe detallado de errores. El modelo `Observation` ya tiene `reporteDetalladoPDF()` que retorna observaciones ordenadas por comuna → establecimiento → mes. El `Exporter` ya genera PDF con TCPDF y tabla jerárquica con rowspan. Existe `assets/images/logo.png` para el membrete.

La tabla `establecimientos` no tiene columna de categoría; se usará lógica por nombre (SQL CASE) para clasificar: HOSPITAL > CESFAM > CECOSF > POSTA > OTRO.

## Goals / Non-Goals

**Goals:**
- Nuevo endpoint `api/informe_errores.php` con dos formatos: JSON (web) y PDF (descarga)
- Nuevo modelo de datos: errores filtrados por `tipo_error = 'ERROR'`, agrupables por trimestre o año
- Tabla web paginada en dashboard (20 rows/página, JS client-side)
- PDF formal con TCPDF: logo, membrete, tabla jerárquica con rowspan, firmas
- Orden jerárquico: comuna → categoría establecimiento → establecimiento → mes

**Non-Goals:**
- No modificar la tabla `establecimientos` (la categorización por nombre es provisional)
- No modificar reportes existentes ni exportaciones
- No incluir otros tipos de error (`S/OBSERVACION`, `REVISAR`, `F/PLAZO`)
- No incluir filtros por comuna/establecimiento específicos (el informe es global)

## Decisions

### 1. Clasificación de establecimientos por patrón de nombre

Se usará SQL `CASE WHEN` en el ORDER BY:
```sql
ORDER BY c.nombre,
  CASE
    WHEN e.nombre LIKE '%HOSPITAL%' THEN 1
    WHEN e.nombre LIKE '%CESFAM%' THEN 2
    WHEN e.nombre LIKE '%CECOSF%' THEN 3
    WHEN e.nombre LIKE '%POSTA%' THEN 4
    ELSE 5
  END,
  e.nombre,
  FIELD(o.mes, 'Enero','Febrero',...,'Diciembre')
```

**Alternativa considerada:** Agregar columna `categoria` a la BD. Se descarta temporalmente para evitar migración; se implementará en el futuro.

### 2. Endpoint unificado JSON + PDF

Un solo archivo `api/informe_errores.php` con parámetro `format`:
- `format=json` → devuelve JSON con datos + metadatos (total, páginas)
- `format=pdf` → genera y descarga PDF (no requiere JSON intermedio)

### 3. Paginación web client-side

Los datos se cargan completos vía fetch, la paginación se hace con JS vanilla:
- 20 filas por página
- Controles: Anterior / Siguiente / página actual
- No se requiere paginación server-side (los informes son acotados: máximo ~400 errores/año)

### 4. PDF con TCPDF

Se usará el mismo patrón que `exportDetalladoPDF()`:
- Orientación horizontal (Landscape)
- Tabla HTML con rowspan calculado en PHP
- Header personalizado con logo y membrete (SetHeaderData)
- Firmas al final como tabla sin bordes

### 5. Ubicación en dashboard

Dos elementos nuevos en `views/dashboard.php`:
- Botón en "Acciones Rápidas" (solo supervisor): abre modal de selección de período
- Modal con opciones: trimestre (1-4) o anual, año, botones "Ver en Web" / "Descargar PDF"
- Sección de tabla paginada debajo de "Últimas Observaciones" (se oculta si no hay datos)

## Risks / Trade-offs

- **[Clasificación por nombre]** → Puede fallar si un establecimiento tiene nombre atípico. Mitigación: provisional, se migrará a columna `categoria` después.
- **[Datos completos en JSON]** → Si hay muchos errores (>500), la respuesta puede ser grande. Mitigación: los informes trimestrales son naturalmente acotados.
- **[Logo no encontrado]** → Si `assets/images/logo.png` no existe, el PDF se genera sin logo. Mitigación: validar existencia del archivo antes de insertarlo.
