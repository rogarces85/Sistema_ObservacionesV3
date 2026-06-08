# Data Model: Mejorar Reportes Analiticos

## Reporte Analitico

Representa una vista analitica visible para el usuario.

**Campos**:
- `categoria`: Identificador canonico de la categoria.
- `titulo`: Nombre visible en español.
- `descripcion`: Texto breve de orientacion al usuario.
- `filtros`: Filtro de Reporte aplicado.
- `totales`: Totales principales del reporte.
- `resultado_agregado`: Lista de resultados agregados.
- `estado_carga`: Estado de presentacion: cargando, listo, vacio o error.

**Reglas**:
- Debe usar los mismos filtros activos que las demas categorias.
- Debe respetar el alcance del usuario autenticado.
- Debe mostrar estado vacio cuando no existan resultados.

## Filtro de Reporte

Define el alcance temporal y territorial de los reportes.

**Campos**:
- `anio`: Año de trabajo obligatorio.
- `trimestre`: Trimestre opcional, valores 1 a 4.
- `mes`: Mes opcional en español.
- `comuna_id`: Comuna opcional.
- `establecimiento_id`: Establecimiento opcional.

**Reglas**:
- `anio` siempre debe estar presente.
- `mes` y `trimestre` pueden usarse para acotar el periodo; si ambos estan presentes, el mes debe pertenecer al trimestre seleccionado.
- `establecimiento_id` debe pertenecer a la comuna seleccionada cuando `comuna_id` este presente.
- Limpiar filtros restaura el año de trabajo y elimina filtros secundarios.

## Categoria de Reporte

Agrupacion de negocio para organizar el analisis.

**Valores**:
- `errores_establecimiento`: Errores agrupados por establecimiento.
- `plazos_entrega`: Cumplimiento o incumplimiento de plazos de entrega.
- `uso_validador`: Uso o no uso del validador.
- `errores_serie`: Errores agrupados por serie.
- `errores_hoja`: Errores agrupados por hoja.

**Reglas**:
- Cada categoria debe tener resumen visual, tabla y accion de exportacion.
- Cambiar de categoria no debe cambiar filtros.

## Resultado Agregado

Fila o punto de datos resumido de una categoria.

**Campos comunes**:
- `clave`: Identificador del grupo analizado.
- `nombre`: Nombre visible del grupo.
- `total`: Cantidad total para el grupo.
- `porcentaje`: Participacion del grupo dentro del total filtrado, cuando aplique.

**Campos opcionales segun categoria**:
- `comuna`: Nombre de comuna.
- `establecimiento`: Nombre de establecimiento.
- `serie`: Codigo o nombre de serie.
- `hoja`: Codigo o nombre de hoja.
- `mes`: Mes asociado cuando el resultado sea mensual.
- `estado`: Estado de observacion cuando aplique.

**Reglas**:
- Los totales deben coincidir entre grafico, tabla y exportacion del mismo filtro.
- Los valores deben presentarse en español y con formato numerico local.

## Exportacion Analitica

Representa la descarga de una categoria analitica.

**Campos**:
- `categoria`: Categoria exportada.
- `filtros`: Filtros usados para generar la descarga.
- `formato`: Formato de salida permitido.
- `nombre_archivo`: Nombre descriptivo con categoria, año y marca temporal.
- `total_registros`: Cantidad de filas exportadas.

**Reglas**:
- No debe generarse si no hay datos.
- Debe respetar permisos del usuario.
- Debe usar el mismo alcance que el reporte visible.

## Relaciones

- Un Filtro de Reporte se aplica a varias Categorias de Reporte.
- Una Categoria de Reporte produce cero o muchos Resultados Agregados.
- Un Reporte Analitico pertenece a una Categoria de Reporte.
- Una Exportacion Analitica se genera desde un Reporte Analitico con filtros activos.

## Transiciones de Estado

```text
sin_cargar -> cargando -> listo
sin_cargar -> cargando -> vacio
sin_cargar -> cargando -> error
error -> cargando -> listo
vacio -> cargando -> listo
listo -> cargando -> listo
```
