# Feature Specification: Mejorar Reportes Analiticos

**Feature Branch**: `[002-mejorar-reportes-analiticos]`

**Created**: 2026-06-08

**Status**: Draft

**Input**: User description: "mejorar reportes analíticos"

## User Scenarios & Testing *(mandatory)*

### Historia de Usuario 1 - Explorar reportes analiticos por categoria (Prioridad: P1)

Como supervisor o registrador autenticado, necesito revisar reportes analiticos separados por categoria para identificar rapidamente donde se concentran los errores, incumplimientos de plazo y problemas de uso del validador.

**Por que esta prioridad**: Es el valor principal de la mejora: transformar la pantalla de reportes desde una lista/exportacion general hacia una herramienta de analisis operativo.

**Prueba independiente**: Se puede probar accediendo a reportes, aplicando un año con datos y verificando que cada categoria muestre informacion resumida, grafica y tabular de forma independiente.

**Escenarios de Aceptacion**:

1. **Dado** un usuario autenticado con datos disponibles para el año seleccionado, **Cuando** abre la pantalla de reportes analiticos, **Entonces** ve categorias separadas para errores por establecimiento, plazos de entrega, uso de validador, errores por serie y errores por hoja.
2. **Dado** una categoria de reporte seleccionada, **Cuando** los datos se cargan correctamente, **Entonces** el usuario ve un resumen visual y una tabla con los registros agregados correspondientes a esa categoria.
3. **Dado** un usuario cambia de categoria, **Cuando** selecciona otra pestana o seccion analitica, **Entonces** el contenido visible se actualiza sin perder los filtros aplicados.

---

### Historia de Usuario 2 - Filtrar analisis por periodo y ubicacion (Prioridad: P1)

Como usuario autenticado, necesito filtrar los reportes analiticos por año, trimestre, mes, comuna y establecimiento para enfocar el analisis en un periodo o territorio especifico.

**Por que esta prioridad**: Sin filtros consistentes, los reportes analiticos no permiten responder preguntas operativas concretas ni comparar resultados por periodo o ubicacion.

**Prueba independiente**: Se puede probar aplicando distintas combinaciones de filtros y verificando que los totales, graficos y tablas reflejen solamente el alcance seleccionado.

**Escenarios de Aceptacion**:

1. **Dado** un usuario visualiza reportes analiticos, **Cuando** aplica filtros de periodo y ubicacion, **Entonces** todas las categorias disponibles usan el mismo conjunto de filtros.
2. **Dado** un usuario selecciona una comuna, **Cuando** consulta el filtro de establecimiento, **Entonces** solo se ofrecen establecimientos asociados a esa comuna.
3. **Dado** un usuario limpia los filtros, **Cuando** el sistema vuelve al estado inicial, **Entonces** se muestra el año de trabajo vigente y se eliminan filtros secundarios.

---

### Historia de Usuario 3 - Exportar cada reporte analitico (Prioridad: P2)

Como usuario autenticado, necesito exportar individualmente el reporte analitico que estoy revisando para compartirlo o trabajarlo fuera del sistema sin exportar informacion innecesaria.

**Por que esta prioridad**: La exportacion especifica reduce trabajo manual y permite entregar evidencia enfocada a autoridades, comunas o establecimientos.

**Prueba independiente**: Se puede probar seleccionando una categoria, aplicando filtros y descargando un archivo que contenga solo la informacion agregada correspondiente a esa categoria.

**Escenarios de Aceptacion**:

1. **Dado** una categoria de reporte con datos visibles, **Cuando** el usuario solicita exportarla, **Entonces** se genera un archivo con los mismos filtros y alcance del reporte visible.
2. **Dado** una categoria sin datos para los filtros actuales, **Cuando** el usuario intenta exportar, **Entonces** el sistema informa que no hay datos exportables y evita generar un archivo vacio.
3. **Dado** un registrador exporta un reporte, **Cuando** se genera el resultado, **Entonces** el archivo contiene solo informacion permitida para ese usuario.

---

### Historia de Usuario 4 - Interpretar indicadores clave rapidamente (Prioridad: P3)

Como supervisor, necesito ver indicadores resumidos de errores, fuera de plazo y uso del validador para priorizar acciones sin revisar todas las filas del detalle.

**Por que esta prioridad**: Aumenta la velocidad de lectura gerencial, aunque depende de que los reportes base y filtros ya esten funcionando correctamente.

**Prueba independiente**: Se puede probar cargando un periodo con datos y verificando que los indicadores principales coincidan con las tablas agregadas del mismo filtro.

**Escenarios de Aceptacion**:

1. **Dado** existen datos para los filtros seleccionados, **Cuando** se cargan los reportes analiticos, **Entonces** el usuario ve indicadores resumidos con totales principales.
2. **Dado** los filtros cambian, **Cuando** se actualizan los reportes, **Entonces** los indicadores se recalculan y coinciden con el nuevo alcance.

---

### Casos Borde

- Si no existen datos para los filtros seleccionados, la pantalla debe mostrar estados vacios claros en cada categoria sin presentar errores tecnicos.
- Si el usuario no tiene permiso para ver datos globales, todos los reportes y exportaciones deben limitarse a los datos autorizados para su rol.
- Si una combinacion de filtros produce una lista muy extensa, la tabla debe seguir siendo navegable y el usuario debe poder entender que esta viendo una porcion del resultado.
- Si falla la carga de una categoria, el sistema debe informar el problema en esa seccion sin bloquear la consulta de las demas categorias.
- Si un establecimiento deja de estar disponible para una comuna seleccionada, el filtro debe volver a un estado valido antes de consultar reportes.
- Si la sesion expira durante el cambio de categoria, los filtros se limpian y el usuario es redirigido a login (este caso representa el 5% de excepciones al SC-003, justificado por la politica de seguridad de cierre de sesion).

## Requirements *(mandatory)*

### Requerimientos Funcionales

- **FR-001**: El sistema DEBE presentar cinco categorias independientes de reportes analiticos: errores por establecimiento, plazos de entrega, uso de validador, errores por serie y errores por hoja.
- **FR-002**: El sistema DEBE permitir aplicar un conjunto compartido de filtros a los reportes analiticos, incluyendo año, trimestre, mes, comuna y establecimiento.
- **FR-003**: El sistema DEBE mantener activos los filtros seleccionados cuando el usuario cambie entre categorias de reporte analitico.
- **FR-004**: El sistema DEBE mostrar cada reporte analitico con un resumen visual y una tabla resumen basados en los mismos datos filtrados.
- **FR-005**: El sistema DEBE mostrar totales claros para el conjunto de filtros activo, incluyendo total de observaciones analizadas, total de errores, total fuera de plazo y total sin uso de validador cuando esas medidas esten disponibles.
- **FR-006**: El sistema DEBE restringir los datos de reportes segun el rol y alcance permitido del usuario autenticado.
- **FR-007**: El sistema DEBE permitir exportar cada categoria de reporte analitico de forma independiente usando los filtros seleccionados.
- **FR-008**: El sistema DEBE impedir la exportacion cuando la categoria seleccionada no tenga datos y explicar el motivo al usuario.
- **FR-009**: El sistema DEBE mostrar un estado vacio claro cuando una categoria de reporte no tenga datos coincidentes.
- **FR-010**: El sistema DEBE mostrar un spinner con el texto "Cargando {nombre_categoria}..." (en español) en el area de contenido de cada categoria mientras se obtienen los datos; el spinner debe ocultarse al recibir respuesta o error.
- **FR-011**: El sistema DEBE mostrar, ante error de carga por categoria, un mensaje en español que incluya (a) descripcion legible del fallo, (b) boton "Reintentar" que repite la consulta con los mismos filtros, y (c) sin afectar la carga de las demas categorias.
- **FR-012**: El sistema DEBE asegurar que las opciones del filtro de establecimiento sean consistentes con la comuna seleccionada.
- **FR-013**: El sistema DEBE preservar las capacidades actuales de exportacion general e informe de errores exclusivo para supervisores al agregar las mejoras analiticas.
- **FR-014**: El sistema DEBE presentar etiquetas, mensajes y nombres de reportes en español.

### Entidades Clave *(incluir si la funcionalidad involucra datos)*

- **Reporte Analitico**: Vista analitica para una categoria de reporte, con nombre, filtros activos, totales, resumen visual y tabla resumen.
- **Filtro de Reporte**: Alcance seleccionado para calcular reportes, incluyendo valores de periodo y valores opcionales de ubicacion.
- **Categoria de Reporte**: Agrupacion de negocio usada para organizar el analisis: errores por establecimiento, plazos de entrega, uso de validador, errores por serie o errores por hoja.
- **Resultado Agregado**: Fila o punto de dato resumido para una categoria, como un conteo por establecimiento, mes, serie u hoja.
- **Exportacion Analitica**: Representacion descargable de una categoria de reporte analitico usando los mismos filtros visibles para el usuario.

## Success Criteria *(mandatory)*

### Resultados Medibles

- **SC-001**: Los usuarios pueden identificar los cinco establecimientos con mas errores para un periodo seleccionado en menos de 30 segundos desde que abren la pantalla de reportes.
- **SC-002**: Los usuarios pueden aplicar filtros de periodo y ubicacion, y ver que todas las categorias analiticas reflejan el alcance seleccionado en menos de 5 segundos para volumenes normales de operacion.
- **SC-003**: Al menos el 95% de los cambios de categoria preservan los filtros activos; el 5% restante corresponde a casos documentados en la seccion "Casos Borde" (ej. expiracion de sesion que limpia contexto).
- **SC-004**: Los usuarios pueden exportar una categoria de reporte analitico con filtros activos en menos de 3 clics desde la pantalla de reportes.
- **SC-005**: Los usuarios registradores nunca ven ni exportan observaciones fuera de su alcance permitido durante pruebas de acceso por rol.
- **SC-006**: Los usuarios supervisores pueden comparar errores, plazos y uso del validador del mismo periodo filtrado sin salir de la pantalla de reportes.
- **SC-007**: Los casos sin resultados son comprensibles para los usuarios, sin texto de error tecnico, en el 100% de las pruebas con filtros sin datos.

## Assumptions

- La mejora aplica a la seccion existente de reportes y no reemplaza modulos no relacionados.
- La autenticacion, roles y reglas de propiedad de datos existentes se mantienen sin cambios.
- La exportacion general actual y el informe de errores exclusivo para supervisores siguen disponibles despues de incorporar las mejoras analiticas.
- El periodo inicial por defecto es el año de trabajo vigente configurado para la sesion del usuario.
- Los reportes analiticos usan datos existentes de observaciones, establecimientos, comunas, series, hojas, plazos y uso de validador.
- No se esperan cambios de esquema de base de datos para esta funcionalidad.
