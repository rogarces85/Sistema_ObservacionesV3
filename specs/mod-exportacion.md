# Especificación: MOD-EXP — Exportación y Reportes

## Historia de Usuario

> **Como** usuario del sistema (Supervisor o Registrador),
> **necesito** generar y descargar reportes de observaciones en formatos Excel, PDF y CSV,
> **para** analizar tendencias de errores, presentar informes formales y realizar auditorías de calidad.

---

## Descripción General

El módulo de Exportación permite generar archivos descargables basados en los datos de observaciones. Soporta tres categorías de reportes: **General** (listado plano), **Específicos** (agrupados por métricas) y **Detallado** (PDF jerárquico para impresión).

La generación de reportes es **síncrona**: el usuario selecciona filtros y formato, y el archivo se descarga directamente en el navegador.

### Formatos soportados

| Formato | General | Específicos | Detallado |
|---------|:-------:|:-----------:|:---------:|
| Excel (.xlsx) | ✅ | ✅ | ❌ |
| PDF | ✅ | ❌ | ✅ |
| CSV | ✅ | ❌ | ❌ |

### Visibilidad por rol

- **Registrador**: Solo ve datos de sus propias observaciones.
- **Supervisor**: Ve todas las observaciones del sistema.

---

## Funciones del Módulo

### EXP-001: Exportar Reporte General

**Descripción**: Genera un listado plano de todas las observaciones que coinciden con los filtros activos.

**Reglas de Negocio**:
- **Formatos**: Excel (.xlsx), PDF y CSV.
- **Contenido**: Una fila por observación con 16 columnas: ID, Año, Mes, Establecimiento, Comuna, Código Serie, Código Hoja, Tipo Error, Detalle, Plazo Entrega, Usa Validador, Estado, Clasificación, Registrador, Supervisor, Fecha Registro.
- **Filtros**: Año (obligatorio), Mes, Estado, Establecimiento.
- **Volumen**: Exporta todos los registros coincidentes, sin límite de paginación.
- **Archivo**: Nombre automático con timestamp (`Observaciones_REM_2026_2026-05-25_143022.xlsx`).

**Endpoint**: `GET /api/export.php?format=excel|pdf|csv&year=2026&month=&estado=&establecimiento_id=`

---

### EXP-002: Exportar Reportes Específicos (Analíticos)

**Descripción**: Genera reportes agrupados por métricas de negocio (errores, fuera de plazo, uso de validador, series, hojas).

**Reglas de Negocio**:
- **Formato**: Solo Excel (.xlsx).
- **Tipos disponibles** (11 reportes):

| Grupo | Reporte | Descripción |
|-------|---------|-------------|
| **General** | `errores_mes` | Errores agrupados por mes |
| | `errores_establecimiento` | Errores agrupados por establecimiento |
| | `errores_comuna` | Errores agrupados por comuna |
| **Fuera de Plazo** | `fuera_plazo_mes` | Fuera de plazo por mes |
| | `fuera_plazo_establecimiento` | Fuera de plazo por establecimiento |
| | `fuera_plazo_comuna` | Fuera de plazo por comuna |
| **Validador** | `validador_mes` | Uso de validador por mes |
| | `validador_establecimiento` | Uso de validador por establecimiento |
| | `validador_comuna` | Uso de validador por comuna |
| **Series/Hojas** | `serie_detalle` | Serie REM × Tipo Error |
| | `hoja_detalle` | Hoja REM × Tipo Error × Detalle |

- **Contenido**: Tablas resumen con dimensión, sub-dimensión y cantidad.
- **Filtro**: Por año. Respeta visibilidad del rol.
- **Archivo**: Nombre automático por tipo (`Errores_por_Mes_2026_2026-05-25_143022.xlsx`).

**Endpoint**: `GET /api/export.php?format=excel&report_type=errores_mes&year=2026`

---

### EXP-003: Exportar Reporte Detallado (PDF Jerárquico)

**Descripción**: Genera un documento PDF estructurado jerárquicamente para presentación formal e impresión.

**Reglas de Negocio**:
- **Formato**: Exclusivamente PDF (orientación horizontal, tamaño A4).
- **Estructura jerárquica**: Agrupa datos por **Comuna → Establecimiento → Mes** usando rowspan en celdas fusionadas.
- **Columnas del PDF**: COMUNAS, ESTABLECIMIENTOS, MES, DETALLE, DETALLE ERROR, ERRORES.
- **Código de colores por estado**:

| Estado | Color de fondo |
|--------|---------------|
| `aprobado` / Corregido | Verde claro (#E8F5E9) |
| `pendiente` / Sin respuesta | Naranja claro (#FFF3E0) |
| `rechazado` | Rojo claro (#FFEBEE) |
| `justificado` | Azul claro (#E3F2FD) |

- **Paginación**: Nueva página cada ~35 filas. Encabezado de tabla se repite en cada página.
- **Encabezado**: Fondo rojo oscuro (#8B1A1A) con texto blanco.
- **Filtros**: Año, Comuna, Establecimiento, Mes, Estado, Tipo Error.
- **Resumen**: Total de registros al final del documento.

**Endpoint**: `GET /api/export.php?report_type=detallado&year=2026&comuna_id=&establecimiento_id=&month=&estado=`

---

## Arquitectura Técnica

```
┌─────────────────────────────────────────────────────────────────┐
│                     Flujo de Exportación                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Usuario ──GET──▶ api/export.php                                │
│                      │                                          │
│                      ├── report_type=general                    │
│                      │   ├── format=excel → exportToExcel()     │
│                      │   ├── format=pdf   → exportToPDF()      │
│                      │   └── format=csv   → exportToCSV()      │
│                      │                                          │
│                      ├── report_type=detallado                  │
│                      │   └── → reporteDetalladoPDF()            │
│                      │       → exportDetalladoPDF()             │
│                      │                                          │
│                      └── report_type={especifico}               │
│                          └── format=excel → exportErroresExcel()│
│                                                                 │
│  Clases involucradas:                                           │
│  • models/Observation.php — Métodos de consulta (20+ reportes)  │
│  • models/Exporter.php — Generación de archivos                 │
│  • PhpSpreadsheet — Excel (.xlsx)                               │
│  • TCPDF — PDF                                                  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

### Métodos de Observation.php usados en exportación

| Método | Uso |
|--------|-----|
| `getWithFilters()` | Reporte general con filtros |
| `reportePorMes()` | General: agrupación por mes |
| `reportePorEstablecimiento()` | General: agrupación por establecimiento |
| `reportePorComuna()` | General: agrupación por comuna |
| `reportePorSerie()` | General: agrupación por serie |
| `reportePorPlazo()` | General: agrupación por plazo |
| `reportePorValidador()` | General: agrupación por validador |
| `reporteErroresPorMes()` | Errores × Mes |
| `reporteErroresPorEstablecimiento()` | Errores × Establecimiento |
| `reporteErroresPorComuna()` | Errores × Comuna |
| `reporteFueraPlazoPorMes()` | Fuera plazo × Mes |
| `reporteFueraPlazoPorEstablecimiento()` | Fuera plazo × Establecimiento |
| `reporteFueraPlazoPorComuna()` | Fuera plazo × Comuna |
| `reporteValidadorPorMes()` | Validador × Mes |
| `reporteValidadorPorEstablecimiento()` | Validador × Establecimiento |
| `reporteValidadorPorComuna()` | Validador × Comuna |
| `reportePorSerieDetalle()` | Serie × Tipo Error |
| `reportePorHojaDetalle()` | Hoja × Tipo Error × Detalle |
| `reporteDetalladoPDF()` | Datos para PDF jerárquico |

### Métodos de Exporter.php

| Método | Formato | Descripción |
|--------|---------|-------------|
| `exportToExcel()` | .xlsx | Listado general con encabezados y estilos |
| `exportToPDF()` | .pdf | Listado general en tabla HTML |
| `exportToCSV()` | .csv | Listado general con BOM UTF-8, delimitador `;` |
| `prepareObservationsData()` | — | Convierte resultados a array para exportación |
| `getObservationsHeaders()` | — | Retorna los 16 encabezados estándar |
| `exportDetalladoPDF()` | .pdf | PDF jerárquico con rowspan y colores |
| `exportErroresExcel()` | .xlsx | Reportes específicos con título dinámico |

---

## Gestión de Sesiones y Cuentas

### Matriz de Permisos

| Función | Registrador | Supervisor |
|---------|:-----------:|:----------:|
| Exportar General | ✅ Solo propios | ✅ Todos |
| Exportar Específicos | ✅ Solo propios | ✅ Todos |
| Exportar Detallado PDF | ✅ Solo propios | ✅ Todos |

---

## Mensajes del Sistema

### Mensajes de Éxito

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-EXP-001 | Descarga iniciada | El navegador inicia la descarga del archivo |

### Mensajes de Error

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-EXP-101 | Sin datos | `No se encontraron observaciones para exportar` (HTTP 404) |
| MSG-EXP-102 | Sin datos específicos | `No se encontraron datos para este reporte` (HTTP 404) |
| MSG-EXP-103 | Formato inválido | `Formato no válido. Use: excel, pdf o csv` (HTTP 400) |
| MSG-EXP-104 | No autenticado | `No autenticado` (HTTP 401) |

---

## Escenarios BDD (Gherkin)

### Escenario: Exportar reporte general en Excel
```gherkin
Dado que soy un Supervisor autenticado
Y estoy en la vista de reportes
Cuando selecciono año "2026", formato "Excel" y presiono "Exportar"
Entonces el sistema genera un archivo .xlsx con todas las observaciones de 2026
Y el archivo incluye 16 columnas con encabezados estilizados
Y la descarga se inicia automáticamente
```

### Escenario: Exportar reporte específico (Errores por Mes)
```gherkin
Dado que soy un Registrador autenticado
Y tengo observaciones con tipo_error='ERROR' registradas
Cuando selecciono el reporte "Errores por Mes" en formato Excel
Entonces el sistema genera un archivo con los errores agrupados por mes
Y el archivo muestra solo mis observaciones
Y la tabla incluye columnas: mes, total
```

### Escenario: Exportar PDF detallado jerárquico
```gherkin
Dado que soy un Supervisor autenticado
Y existen observaciones de múltiples comunas
Cuando solicito el "PDF Detallado" para el año 2026
Entonces el sistema genera un PDF con:
  | Columnas: COMUNAS | ESTABLECIMIENTOS | MES | DETALLE | DETALLE ERROR | ERRORES
Y las filas están agrupadas jerárquicamente con rowspan
Y cada fila tiene color de fondo según el estado
Y el encabezado de tabla es rojo oscuro con texto blanco
```

### Escenario: Exportar sin datos
```gherkin
Dado que soy un Supervisor autenticado
Y no existen observaciones para los filtros seleccionados
Cuando intento exportar un reporte
Entonces el sistema responde con HTTP 404
Y muestra el mensaje "No se encontraron observaciones para exportar"
```

### Escenario: Registrador solo ve sus datos
```gherkin
Dado que soy un Registrador autenticado
Y existen observaciones de otros registradores en el sistema
Cuando exporto un reporte general
Entonces el archivo contiene solo mis observaciones
Y no incluye datos de otros registradores
```

---

## Mockup ASCII

### Panel de Exportación en Vista de Reportes

```
+==============================================================================+
|  REPORTES - Año: [ 2026 ▼ ]                                                  |
+==============================================================================+
|  [General] [Errores] [Fuera de Plazo] [Validador] [Serie/Hoja] [PDF Detall.]|
+==============================================================================+
|                                                                              |
|  DISTRIBUCIÓN POR MES                                                        |
|  ┌──────────────────────────────────────────────────────────────────┐        |
|  │  ████████████████████████  Enero    45                           │        |
|  │  ██████████████            Febrero  32                           │        |
|  │  ███████████████████       Marzo    38                           │        |
|  └──────────────────────────────────────────────────────────────────┘        |
|  [ 📥 Exportar Excel ]                                                       |
|                                                                              |
|  DISTRIBUCIÓN POR COMUNA                                                     |
|  ┌──────────────────────────────────────────────────────────────────┐        |
|  │  ██████████████████████████████  Osorno       102                │        |
|  │  ██████████                      Purranque     35                │        |
|  └──────────────────────────────────────────────────────────────────┘        |
|  [ 📥 Exportar Excel ]                                                       |
|                                                                              |
|  ... (más gráficos con sus botones de exportación)                           |
|                                                                              |
|  [ 📥 Excel General ]    [ 📄 PDF Detallado ]                                |
+==============================================================================+
```

### PDF Detallado (vista previa conceptual)

```
+==============================================================================+
|  REPORTE DETALLADO DE VALIDACIONES REM                                       |
|  Año: 2026 | Comuna: Osorno | Generado: 25/05/2026 14:30                     |
+==============================================================================+
|  COMUNAS    | ESTABLECIMIENTOS          | MES    | DETALLE        | DET.ERR  |
+=============+===========================+========+================+==========+
|             |                           |        | Faltan datos   | Pendiente|
|             |                           | Enero  | en columna     |          |
|             |                           |        | egresos        |          |
|  OSORNO     | Hospital Base San José    |--------+----------------+----------|
|             |                           | Febrero| Error en       | Corregido|
|             |                           |        | total REM      |          |
|             |---------------------------+--------+----------------+----------|
|             | CESFAM Dr. Lopetegui      | Marzo  | Sin observ.    | Aprobado |
|=============+===========================+========+================+==========|
|             | Hospital de Purranque     | Enero  | Fuera de plazo | Rechazado|
|  PURRANQUE  |---------------------------+--------+----------------+----------|
|             | CESFAM Quinta Centenario  | Abril  | Valores a      | Pendiente|
|             |                           |        | verificar      |          |
+=============+===========================+========+================+==========|
|  Total registros: 5                                                          |
+==============================================================================+
```

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Formatos soportados | **Excel (.xlsx), PDF y CSV** |
| 2 | Tipo de generación | **Síncrona** (descarga directa, sin cola) |
| 3 | Tipos de Reporte | **General, 11 Específicos, PDF Jerárquico** |
| 4 | Permisos y Datos | Registrador: propios, Supervisor: todos |
| 5 | Filtros | Año, Mes, Estado, Establecimiento, Comuna |
| 6 | Volumen | Sin límite de paginación |
| 7 | PDF Detallado | Jerárquico Comuna→Establecimiento→Mes con rowspan |
| 8 | Código de colores | 4 colores por estado (verde, naranja, rojo, azul) |
| 9 | Nombre de archivo | Automático con timestamp |
| 10 | Columnas exportación | 16 columnas estándar |
| 11 | CSV | BOM UTF-8, delimitador `;` |

---

## Requisitos de Infraestructura de Exportación

- `vendor/` debe estar instalado (`composer install`)
- Extensiones PHP: `zip` (para PhpSpreadsheet), `gd` o `imagick` (para TCPDF)
- Memoria suficiente para datasets grandes (PhpSpreadsheet carga en memoria)
- Tiempo de ejecución adecuado (`max_execution_time`) para reportes con >10K registros
