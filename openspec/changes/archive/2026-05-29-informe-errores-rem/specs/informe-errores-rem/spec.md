# Informe de Errores REM

## Descripción
Generación de informes trimestrales y anuales de observaciones con tipo_error = 'ERROR', organizados jerárquicamente por comuna → establecimiento → mes, con salida a PDF formal y visualización web paginada.

## Requerimientos

### R1: Consulta de datos
- Filtrar observaciones por `tipo_error = 'ERROR'`
- Agrupar por trimestre (1-4) o año completo
- Ordenar por: comuna → categoría establecimiento → establecimiento → mes
- Incluir campos: comuna, establecimiento, mes, codigo_serie, codigo_hoja, detalle_observacion, clasificacion, detalle_error

### R2: Clasificación de establecimientos
- Clasificar establecimientos por patrón de nombre en el orden: HOSPITAL (1), CESFAM (2), CECOSF (3), POSTA (4), OTRO (5)
- Implementar via SQL CASE WHEN en la cláusula ORDER BY

### R3: Salida PDF (TCPDF)
- Formato horizontal (Landscape, A4)
- Membrete superior: "SERVICIO SALUD OSORNO / DEGI" con logo
- Título: "INFORME DE ERRORES REM" + período + fecha de emisión
- Tabla jerárquica con columnas: COMUNA, ESTABLECIMIENTO, MES, DETALLE DEL ERROR, CLASIFICACIÓN, DETALLE ERROR
- Columna "DETALLE DEL ERROR" debe concatenar: **SERIE** | **HOJA** | detalle_observacion (serie y hoja en negrita)
- Rowspan para comuna, establecimiento y mes cuando hay múltiples filas
- Color de fila según estado (verde=aprobado, ámbar=pendiente, rojo=rechazado, azul=justificado)
- Firmas al final: "Cecilia Barría Ojeda - Jefa Subdepto. Producción Estadística"

### R4: Visualización web
- Tabla paginada (20 filas/página) con controles Anterior/Siguiente
- Misma estructura que el PDF pero sin rowspan (datos planos repetidos)
- Botón para descargar PDF desde la misma vista

### R5: Acceso
- Visible solo para rol SUPERVISOR
- Botón en "Acciones Rápidas" del dashboard
- Modal de selección: trimestre (1-4) o anual + año
- Acciones: "Ver en Web" (carga tabla) / "Descargar PDF" (descarga directa)
