# UI Contract: Reportes Analiticos

## Pantalla

La seccion Reportes debe incluir un bloque analitico con filtros compartidos, indicadores principales y cinco categorias seleccionables.

## Filtros Compartidos

- Año: obligatorio, inicia con el año de trabajo vigente.
- Trimestre: opcional, limita los meses disponibles o el alcance del reporte.
- Mes: opcional, debe ser compatible con el trimestre seleccionado cuando exista.
- Comuna: opcional.
- Establecimiento: opcional, deshabilitado o vacio hasta seleccionar comuna cuando corresponda.

## Categorias

- Errores por establecimiento.
- Plazos de entrega.
- Uso de validador.
- Errores por serie.
- Errores por hoja.

## Comportamiento por Categoria

Cada categoria debe mostrar:

- Titulo y descripcion breve.
- Estado de carga mientras consulta datos.
- Resumen visual.
- Tabla con los mismos datos del resumen visual.
- Boton para exportar la categoria con filtros activos.
- Estado vacio cuando no haya resultados.
- Mensaje de error recuperable si la categoria falla.

## Reglas de Interaccion

- Cambiar de categoria no debe limpiar filtros.
- Aplicar filtros debe actualizar indicadores, resumen visual y tabla.
- Limpiar filtros debe restaurar el año de trabajo y quitar filtros secundarios.
- Exportar sin datos debe mostrar mensaje en español y no generar archivo.
- Los usuarios registradores solo deben ver datos permitidos por su alcance.
- El informe de errores exclusivo para supervisores debe permanecer visible solo para supervisores.

## Estados Visuales

- `cargando`: indicador visible y acciones temporalmente protegidas contra doble ejecucion.
- `listo`: grafico, tabla, totales y exportacion disponibles.
- `vacio`: mensaje claro sin texto tecnico y exportacion deshabilitada.
- `error`: mensaje recuperable con opcion de reintentar al reaplicar filtros o recargar categoria.

## Accesibilidad y Localizacion

- Todos los textos visibles deben estar en español.
- Los botones deben tener texto comprensible ademas de icono.
- Las tablas deben conservar encabezados descriptivos.
- Los graficos deben estar respaldados por tabla para lectura exacta.
