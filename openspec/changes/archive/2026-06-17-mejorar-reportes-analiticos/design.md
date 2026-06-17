## Context

`views/reportes.php` actualmente presenta una vista general con tabla de observaciones filtrada y un botón de "Exportación General" que produce un Excel/PDF/CSV consolidado. Existe también un Informe de Errores trimestral/anual accesible desde el dashboard, exclusivo para supervisores, que produce un PDF con tabla jerárquica Comuna→Establecimiento→Mes.

Los usuarios necesitan:
1. Desglosar los datos por categoría (errores, plazos, validador, serie, hoja) sin generar exports masivos.
2. Aplicar el mismo conjunto de filtros a todas las categorías y ver resultados consistentes.
3. Exportar solo la categoría visible con sus filtros aplicados.
4. Ver indicadores resumidos (KPI cards) consistentes con los gráficos y tablas.

## Goals / Non-Goals

**Goals:**
- Cinco categorías analíticas navegables con resumen visual + tabla + indicador.
- Filtros compartidos (año, trimestre, mes, comuna, establecimiento) que se preservan al cambiar de categoría.
- Exportación individual por categoría con CSRF, permisos por rol y bloqueo sin datos.
- Preservar la exportación general y el Informe de Errores del supervisor (no-regresión FR-013).
- Sin cambios de esquema BD; sin nuevas dependencias; UI en español.

**Non-Goals:**
- No se introducen nuevos reportes más allá de las cinco categorías definidas.
- No se modifica la exportación general ni el Informe de Errores en su comportamiento clásico.
- No se cambian los métodos de `models/Observation.php` que no sean los nuevos métodos agregados.
- No se introduce i18n (todo el sistema permanece en español).
- No se introducen tests automatizados (validación manual conforme a `quickstart.md`).

## Decisions

### Decisión 1: 5 categorías con payload unificado en una sola llamada

**Elegido**: Un endpoint `reportes-analiticos` en `api/reports.php` que ejecuta 5 sub-consultas y devuelve un payload único con totales, resultados y mensaje por categoría.

**Alternativa considerada**: 5 endpoints separados. Rechazado por latencia (5 round-trips) y complejidad de orquestación de filtros compartidos.

**Razonamiento**: El sistema ya usa respuestas con estructura `{success, data, message}` (T010). Mantener el contrato simplifica el cliente JS y reduce puntos de fallo. La carga de las 5 categorías se hace en una sola llamada inicial; las llamadas siguientes (cambio de filtros) recargan las 5.

### Decisión 2: Filtros compartidos con estado en el cliente

**Elegido**: Los filtros viven en `obtenerFiltrosAnaliticos()` y se envían en cada request. El JS no guarda los filtros en estado interno; siempre se leen del DOM.

**Razonamiento**: Si el usuario navega a otra vista y vuelve, los filtros quedan como los dejó en los controles (inputs/selects). Coherente con la constitución III (idioma) y con la persistencia natural del DOM.

### Decisión 3: ApexCharts con instanciación por categoría

**Elegido**: `graficos[categoria] = new ApexCharts(contenedor, config)` con un mapa por nombre de categoría. `destruirGrafico(categoria)` se llama antes de cada re-render.

**Alternativa considerada**: Un solo chart conmutando series. Rechazado porque la constitución IV exige ApexCharts y porque la limpieza de series es más frágil entre cambios de categoría.

### Decisión 4: Helper de alcance por rol en `Observation.php`

**Elegido**: `aplicarAlcancePorRol($sql, $params, $usuario)` agrega `WHERE` con restricción por establecimiento asignado para registradores, transparente para supervisores.

**Razonamiento**: Centraliza la regla de alcance (matriz de permisos §4) y evita replicarla en cada método agregado. Cubierto por la constitución I (seguridad) y FR-006.

### Decisión 5: Exportación por categoría con bloqueo explícito

**Elegido**: `setBotonExportarAnalitico(categoria, deshabilitado)` deshabilita el botón cuando no hay datos; al hacer click, `api/export.php` re-valida que existan datos y responde con mensaje en español si no.

**Razonamiento**: Doble validación (frontend deshabilita, backend rechaza) evita descargas vacías y Cumple FR-008 + FR-014 (idioma).

### Decisión 6: Preservación de la exportación general y del Informe de Errores (FR-013)

**Elegido**: El cambio agrega capacidad `mod-reportes` sin tocar `mod-exportacion` ni `mod-informe-errores`. La tarea T054 verifica manualmente que ambos flujos siguen funcionando idéntico al baseline v2.3.0.

**Razonamiento**: Alineado con la constitución VII (sistema desde 0) — el cambio no refactoriza capacidades existentes, solo agrega.

### Decisión 7: Refinamiento F4 (T055/T056) diferido

**Elegido**: Los refinamientos de FR-010 (spinner por categoría) y FR-011 (botón "Reintentar") aplicados a `spec.md` durante la auditoría de hoy se implementan como tareas separadas (T055, T056) y se difieren al cambio posterior.

**Razonamiento**: El feature 002 queda archivado con la implementación base (texto "Cargando..." y "No fue posible cargar esta categoría." en español, aislamiento entre categorías). El gap es de UX incremental, no bloqueante, y se documenta explícitamente en `proposal.md` (sección "Known Gaps").

## Risks / Trade-offs

- **[Bajo] Rendimiento de carga agregada**: 5 sub-consultas en una sola llamada. Con ~245 observaciones/año (volumen típico) las consultas demoran <500ms. Si el volumen crece a >5K/año, considerar caché APCu o vista materializada.
- **[Bajo] Filtros trimestre/mes incompatibles**: JS valida la combinación (T031) y muestra mensaje en español. El backend también rechaza para evitar bypass.
- **[Medio] Gap F4 conocido**: Las 5 tareas de validación manual están marcadas [X] con nota "validación parcial". El feature se archiva técnicamente completo pero con gap de UX documentado.
- **[Bajo] Cambio de año sin recargar**: Si el usuario cambia de año en el selector global, los reportes analíticos deben recargarse automáticamente. Verificar que `cargarReportesAnaliticos()` escuche el evento de cambio de año.

## Open Questions

- ¿Se debe mantener `mod-exportacion` como capability separado o fusionarse con `mod-reportes`? (Decisión: mantener separados para preservar la no-regresión de FR-013.)
- ¿Las cinco categorías deberían ser configurables (activar/desactivar por supervisor) en una iteración futura? (No-goal actual.)
- ¿La exportación por categoría debería soportar además el formato PDF (no solo Excel)? (Postergado; hoy solo Excel para sub-reportes; PDF solo en el Informe de Errores.)
