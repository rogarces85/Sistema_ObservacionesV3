# Feature Specification: Cerrar Gap F4 — UX de Carga y Error por Categoría

**Feature Branch**: `[003-f4-categoria-ux]`
**Created**: 2026-06-17
**Status**: Draft
**Input**: User description: "cerrar-gap-f4" — implementar el refinamiento de UX pendiente del feature 002-mejorar-reportes-analiticos (T055 spinner, T056 botón Reintentar) en las cinco categorías analíticas.

**Source**: Este feature cierra los pendientes T055 y T056 documentados en `openspec/changes/archive/2026-06-17-mejorar-reportes-analiticos/proposal.md` (sección "Known Gaps") y `tasks.md` (fase 8).

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Ver indicador de carga por categoría (Priority: P1)

Como usuario autenticado (supervisor o registrador) que acaba de aplicar filtros o cambiar de categoría, necesito ver un indicador visual claro de que la categoría está cargando para saber que el sistema está trabajando y no está congelado.

**Por que esta prioridad**: Sin un indicador de carga explícito, el usuario no distingue entre "cargando" y "no responde", lo que genera clics duplicados, ansiedad y percepción de lentitud. Es la primera impresión de "feedback inmediato" en cada interacción con la vista analítica.

**Prueba independiente**: Se puede probar abriendo Reportes, aplicando un filtro con datos, observando el comportamiento de cada una de las cinco pestañas al cambiar entre ellas, y verificando que cada una muestra un indicador de carga mientras se obtienen sus datos.

**Escenarios de Aceptacion**:

1. **Dado** un usuario autenticado con un año de trabajo con datos, **Cuando** aplica un filtro o cambia de categoría analítica, **Entonces** cada categoría en proceso de carga muestra un indicador visual de carga (spinner) con el texto "Cargando {nombre_categoria}..." en español.
2. **Dado** una categoría está cargando, **Cuando** se recibe la respuesta exitosa, **Entonces** el indicador de carga desaparece y se muestra el contenido de la categoría (gráfico, tabla, indicador).
3. **Dado** una categoría está cargando, **Cuando** se recibe un error, **Entonces** el indicador de carga desaparece y se muestra el mensaje de error recuperable (ver User Story 2).

---

### User Story 2 — Reintentar carga fallida por categoría (Priority: P1)

Como usuario autenticado que ve un mensaje de error en una categoría específica (por timeout, caída de red, o error del servidor), necesito una forma de reintentar la carga de esa categoría sin tener que recargar toda la página ni perder los filtros activos ni afectar la carga de las otras categorías.

**Por que esta prioridad**: Un error en una categoría no debe bloquear la experiencia del usuario en las demás. Sin un mecanismo de reintento explícito, el usuario se ve forzado a recargar toda la página (perdiendo contexto) o a abandonar el análisis. Esta es la base de la resiliencia de UX.

**Prueba independiente**: Se puede probar forzando un error de carga (ej. cortando temporalmente la conexión a la API), observando que solo la categoría afectada muestra error, las demás cargan normalmente, y que el botón "Reintentar" permite recuperar la categoría fallida.

**Escenarios de Aceptacion**:

1. **Dado** una categoría muestra un mensaje de error, **Cuando** el usuario hace click en el botón "Reintentar", **Entonces** el sistema ejecuta nuevamente la consulta de esa categoría con los mismos filtros activos.
2. **Dado** el usuario hace click en "Reintentar", **Cuando** la consulta tiene éxito, **Entonces** el mensaje de error se reemplaza por el contenido de la categoría (gráfico, tabla, indicador).
3. **Dado** el usuario hace click en "Reintentar", **Cuando** la consulta vuelve a fallar, **Entonces** el sistema muestra nuevamente el mensaje de error con el botón "Reintentar" disponible para otro intento.
4. **Dado** una categoría falla, **Cuando** las demás categorías cargan exitosamente, **Entonces** las categorías exitosas mantienen su contenido visible mientras la categoría fallida muestra su mensaje de error.

---

### User Story 3 — Mensaje de error claro y recuperable (Priority: P2)

Como usuario que experimenta un error de carga, necesito entender qué falló y cómo recuperarme sin necesidad de contactar a soporte técnico.

**Por que esta prioridad**: Un mensaje de error técnico (ej. "Error 500", "JSON parse error") genera desconfianza y obliga al usuario a escalar el problema. Un mensaje en lenguaje natural permite auto-recuperación.

**Prueba independiente**: Se puede probar generando diferentes tipos de error (timeout, datos malformados, endpoint caído) y verificando que el mensaje mostrado es claro, en español, y permite la auto-recuperación.

**Escenarios de Aceptacion**:

1. **Dado** una categoría falla al cargar, **Cuando** se muestra el mensaje de error, **Entonces** el mensaje está en español, describe el problema en lenguaje natural (no técnico), e incluye un botón "Reintentar".
2. **Dado** una categoría falla por timeout, **Cuando** se muestra el mensaje, **Entonces** el texto indica que la carga tomó más tiempo del esperado y sugiere reintentar.
3. **Dado** una categoría falla por error del servidor, **Cuando** se muestra el mensaje, **Entonces** el texto indica que hubo un problema al obtener los datos y ofrece reintentar.

---

### Edge Cases

- Si el usuario hace click en "Reintentar" múltiples veces rápidamente, el sistema debe evitar enviar más de una solicitud en paralelo (debounce o deshabilitar botón durante la nueva carga).
- Si los filtros activos se modifican durante la carga, al recibir la respuesta debe descartarse si los filtros cambiaron (evitar mostrar datos obsoletos).
- Si el usuario navega fuera de la vista de Reportes mientras una categoría está cargando, la solicitud en vuelo debe cancelarse o completarse silenciosamente sin afectar la nueva vista.
- Si la categoría vuelve a fallar después de N reintentos, el botón "Reintentar" debe seguir disponible sin un límite visible, pero podría mostrarse un mensaje adicional tras N intentos sugiriendo contactar a soporte.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El sistema DEBE mostrar un indicador visual de carga (spinner) por categoría analítica con el texto "Cargando {nombre_categoria}..." en español mientras se obtienen los datos.
- **FR-002**: El sistema DEBE ocultar el indicador de carga y mostrar el contenido de la categoría (gráfico, tabla, indicador) cuando la consulta se completa exitosamente.
- **FR-003**: El sistema DEBE ocultar el indicador de carga y mostrar un mensaje de error con botón "Reintentar" cuando la consulta falla.
- **FR-004**: El sistema DEBE mostrar un mensaje de error en español, en lenguaje natural (no técnico), cuando una categoría falla al cargar.
- **FR-005**: El sistema DEBE incluir un botón "Reintentar" en el mensaje de error de cada categoría que, al ser clickeado, ejecuta nuevamente la consulta de esa categoría con los mismos filtros activos.
- **FR-006**: El sistema DEBE mantener el contenido de las categorías exitosamente cargadas cuando una o más categorías fallan (aislamiento de errores entre categorías).
- **FR-007**: El sistema DEBE deshabilitar el botón "Reintentar" mientras la nueva consulta está en curso, para evitar solicitudes duplicadas.
- **FR-008**: El sistema DEBE permitir múltiples reintentos consecutivos de una misma categoría sin límite visible para el usuario.
- **FR-009**: El sistema DEBE usar el nombre de la categoría (en español) en el texto del indicador de carga, no un identificador técnico.

### Key Entities *(incluir si la funcionalidad involucra datos)*

- **Estado de carga por categoría**: Cada categoría analítica puede estar en uno de cuatro estados: `cargando` (spinner visible), `listo` (contenido visible), `vacio` (mensaje de "sin datos"), `error` (mensaje de error + botón reintentar).
- **Filtros activos**: Conjunto de valores (año, trimestre, mes, comuna, establecimiento) que se preservan al reintentar.
- **Categoría analítica**: Cada una de las cinco categorías (errores_establecimiento, plazos_entrega, uso_validador, errores_serie, errores_hoja) con su título en español.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Los usuarios ven un indicador visual de carga en cada categoría dentro de 200ms después de aplicar un filtro o cambiar de categoría.
- **SC-002**: El indicador de carga desaparece en menos de 100ms después de que la respuesta del servidor es recibida (exitosa o con error).
- **SC-003**: El 100% de los errores de carga de una categoría muestran un mensaje en español con un botón "Reintentar" funcional.
- **SC-004**: Al hacer click en "Reintentar", la nueva consulta se ejecuta con exactamente los mismos filtros activos que la consulta original.
- **SC-005**: El fallo de una categoría no impide ni afecta la carga o visualización de las otras cuatro categorías.
- **SC-006**: El texto del indicador de carga incluye el nombre de la categoría en español (ej. "Cargando Errores por establecimiento..."), no el identificador técnico.
- **SC-007**: El sistema permite reintentar una categoría fallida al menos 5 veces consecutivas sin perder la disponibilidad del botón.

## Assumptions

- Los endpoints de la API de reportes analíticos (`api/reports.php?report=reportes-analiticos` y sub-endpoints de exportación) ya existen y son estables. Este feature no agrega ni modifica endpoints.
- Las cinco categorías (errores_establecimiento, plazos_entrega, uso_validador, errores_serie, errores_hoja) y sus títulos en español ya están definidos en `assets/js/reportes.js` (`CATEGORIAS_ANALITICAS`).
- La estructura HTML de cada categoría (panel con `data-panel-categoria`, estado con `data-estado-categoria`, botón de exportar con `data-exportar-analitico`) ya existe en `views/reportes.php`.
- El feature anterior 002-mejorar-reportes-analiticos está archivado y las modificaciones se realizan sobre los archivos existentes.
- La constitución v1.1.0 sigue vigente; este feature es un refinamiento incremental de UX que no altera arquitectura, esquema BD, ni dependencias.
- La convención de la casa (Tabler + ApexCharts + BEM) se mantiene.
- La validación humana se realiza con `quickstart.md` del feature 002 (mismos pasos 1-10) re-ejecutando específicamente los criterios de carga y error.
