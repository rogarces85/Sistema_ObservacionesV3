## Why

Los supervisores del Servicio de Salud Osorno necesitan generar informes trimestrales (y anual) de errores REM con formato profesional imprimible, que muestre el detalle de cada error organizado jerárquicamente por comuna → establecimiento → mes, incluyendo la clasificación y detalle registrados por el supervisor. Actualmente solo existen reportes agregados (charts) y una exportación PDF genérica, pero no un informe formal con membrete, logo y firmas.

## What Changes

- **Nuevo endpoint** `api/informe_errores.php` que entrega datos de errores (tipo_error = 'ERROR') en formato JSON (para tabla web) o PDF (descarga)
- **Nuevo método** en `models/Observation.php` para consultar errores por trimestre/año ordenados por comuna → categoría establecimiento → establecimiento → mes
- **Nueva sección** en `views/dashboard.php` con botón "Generar Informe Trimestral/Anual" y tabla web paginada con los resultados
- **Nuevo método** en `models/Exporter.php` que genera PDF con TCPDF incluyendo: logo, membrete "SERVICIO SALUD OSORNO / DEGI", tabla jerárquica con rowspan, y firmas
- **Clasificación de establecimientos** por patrón de nombre (HOSPITAL, CESFAM, CECOSF, POSTA, OTRO) vía SQL CASE para ordenamiento jerárquico

## Capabilities

### New Capabilities
- `informe-errores-rem`: Generación de informes de errores REM trimestrales/anuales con salida PDF formal y visualización web paginada, con datos ordenados jerárquicamente.

### Modified Capabilities
- *(Ninguna — es una nueva funcionalidad)*

## Impact

- **models/Observation.php**: Nuevo método `getErroresInforme($anio, $trimestre)` que filtra `tipo_error = 'ERROR'` y ordena por comuna, categoría de establecimiento, establecimiento, mes
- **models/Exporter.php**: Nuevo método `exportInformeErroresPDF()` con TCPDF, membrete, logo, tabla jerárquica con rowspan, firmas
- **api/informe_errores.php**: Nuevo archivo API que acepta parámetros `tipo` (trimestral/anual), `trimestre` (1-4), `anio`, `format` (json/pdf)
- **views/dashboard.php**: Nuevo botón de acción "Informe Trimestral/Anual" visible para supervisores + sección de tabla paginada de resultados
- **Sin cambios en BD**: La clasificación de establecimientos se hará por patrón de nombre en SQL, no requiere migración
