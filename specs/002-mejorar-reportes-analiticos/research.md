# Research: Mejorar Reportes Analiticos

## Decision: Mantener arquitectura Vista -> API -> Modelo

**Rationale**: La constitucion exige separacion estricta de presentacion, endpoints y datos. La pantalla de reportes debe renderizar estructura y filtros, el JavaScript debe solicitar datos, la API debe validar permisos y el modelo debe ejecutar consultas preparadas.

**Alternatives considered**: Colocar calculos directamente en la vista fue descartado por mezclar presentacion con negocio. Duplicar logica en JavaScript fue descartado porque expondria reglas de permisos y calculos sensibles al cliente.

## Decision: Reutilizar datos existentes sin cambios de esquema

**Rationale**: La especificacion y restricciones del proyecto indican que no se debe modificar el esquema. Las categorias analiticas pueden derivarse de observaciones, establecimientos, comunas, series, hojas, plazos y uso de validador ya existentes.

**Alternatives considered**: Crear tablas cache o materializadas fue descartado para esta fase porque aumenta complejidad, requiere migraciones y no es necesario para el alcance definido.

## Decision: Cinco categorias analiticas con filtros compartidos

**Rationale**: La necesidad de usuario se centra en comparar errores, plazos y uso del validador desde el mismo contexto. Mantener filtros compartidos evita inconsistencias y reduce re-trabajo del usuario.

**Alternatives considered**: Filtros independientes por categoria fueron descartados porque facilitan comparaciones erroneas entre categorias con alcances distintos.

## Decision: Resumen visual y tabla por categoria

**Rationale**: Los graficos ayudan a detectar concentraciones rapidamente, mientras la tabla entrega trazabilidad y valores exactos. La combinacion cumple los criterios de identificacion rapida y validacion de totales.

**Alternatives considered**: Solo tabla fue descartado por menor valor analitico. Solo grafico fue descartado porque no entrega suficiente detalle operativo.

## Decision: Exportacion individual por categoria usando filtros activos

**Rationale**: El usuario necesita compartir evidencia enfocada. La exportacion debe reflejar exactamente el reporte visible para evitar trabajo manual y reducir errores.

**Alternatives considered**: Exportar siempre todos los reportes fue descartado porque genera archivos innecesarios y dificulta compartir hallazgos especificos.

## Decision: Carga tolerante por categoria

**Rationale**: Si una categoria falla, las demas deben seguir siendo consultables. Esto mejora la disponibilidad percibida y facilita diagnosticar errores parciales.

**Alternatives considered**: Bloquear toda la pantalla ante cualquier fallo fue descartado por mala experiencia y baja resiliencia.

## Decision: Mantener exportacion general e informe de supervisor

**Rationale**: La mejora es incremental y no debe romper flujos actuales. La especificacion exige preservar capacidades existentes.

**Alternatives considered**: Reemplazar la pantalla completa fue descartado por riesgo de regresion y perdida de funciones actuales.
