## Context

README.md está en v2.2.0 y le faltan los cambios implementados en la sesión actual:
- `mejorar-modal-aprobacion-observaciones`: Campos codigo_serie, codigo_hoja, respuesta_establecimiento en modal de detalle
- `selector-estado-aprobacion`: Radio buttons "Sin Observación"/"Error" en modal de aprobación con actualización de tipo_error

`openspec/config.yaml` está vacío de contexto. Necesita el contexto del proyecto para que futuras propuestas generadas por IA tengan mejor calidad.

## Goals / Non-Goals

**Goals:**
- README refleje la versión 2.3.0 con todas las funcionalidades actuales
- openspec/config.yaml tenga contexto completo del proyecto
- Estructura del proyecto en README incluya archivos nuevos (Version.php, ReportQueue.php, UserAudit.php, versioning.php)

**Non-Goals:**
- No se modifica código de la aplicación
- No se crean nuevos specs

## Decisions

### Decisión 1: Versión 2.3.0

Los dos cambios implementados (modal detalle + selector estado) constituyen una versión menor. Se etiqueta como v2.3.0.

### Decisión 2: Estructura de config.yaml

Se agrega `context` con: nombre del sistema, propósito, tech stack, roles, patrones de arquitectura. Se agregan `rules` básicos para propuestas y tareas.

## Risks / Trade-offs

- Ninguno — cambios puramente documentales y de configuración
