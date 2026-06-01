## 1. Backend — Modelo de Datos

- [x] 1.1 Crear método `getErroresInforme($anio, $trimestre = null, $userId = null, $userRole = null)` en `models/Observation.php` que consulte observaciones con `tipo_error = 'ERROR'`, ordenando por comuna → CASE establecimiento (HOSPITAL=1..OTRO=5) → establecimiento → mes, retornando comuna, establecimiento, mes, codigo_serie, codigo_hoja, detalle_observacion, clasificacion, detalle_error

## 2. Backend — API Endpoint

- [x] 2.1 Crear `api/informe_errores.php` que reciba `tipo` (trimestral/anual), `trimestre` (1-4), `anio`, `format` (json/pdf) y valide parámetros
- [x] 2.2 En `format=json`, retornar datos + metadatos (total, período, fecha emisión) como JSON
- [x] 2.3 En `format=pdf`, instanciar Exporter y llamar al método de generación PDF

## 3. Backend — PDF con TCPDF

- [x] 3.1 Crear método `exportInformeErroresPDF($data, $periodo)` en `models/Exporter.php` con TCPDF en orientación horizontal
- [x] 3.2 Implementar header personalizado con logo y texto "SERVICIO SALUD OSORNO / DEGI"
- [x] 3.3 Implementar tabla jerárquica con rowspan para comuna/establecimiento/mes
- [x] 3.4 Columna "DETALLE DEL ERROR": concatenar serie (bold) + " | " + hoja (bold) + " | " + detalle_observacion
- [x] 3.5 Colores de fila según estado_actual
- [x] 3.6 Agregar firmas al final: "Cecilia Barría Ojeda - Jefa Subdepto. Producción Estadística"

## 4. Frontend — Dashboard

- [x] 4.1 Agregar botón "📄 Informe de Errores" en "Acciones Rápidas" del dashboard (visible solo para SUPERVISOR)
- [x] 4.2 Crear modal de selección de período (trimestre 1-4 + anual + año) con botones "Ver en Web" y "Descargar PDF"
- [x] 4.3 Implementar fetch a `api/informe_errores.php?format=json` y renderizar tabla paginada (20 filas/página)
- [x] 4.4 Implementar paginación client-side: controles Anterior/Siguiente, indicador de página actual
- [x] 4.5 Botón "Descargar PDF" que abra `api/informe_errores.php?format=pdf&...` en nueva pestaña

## 5. Verificación

- [x] 5.1 Probar consulta trimestral con datos reales
- [x] 5.2 Probar consulta anual
- [x] 5.3 Probar descarga PDF y verificar membrete, logo, tabla jerárquica, firmas
- [x] 5.4 Probar paginación web (cambio de página, controles)
- [x] 5.5 Verificar que registrador NO vea el botón ni la sección
- [x] 5.6 Verificar PHP lint en todos los archivos modificados
