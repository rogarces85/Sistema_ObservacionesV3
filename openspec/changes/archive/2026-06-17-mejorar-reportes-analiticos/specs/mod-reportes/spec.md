# Spec delta: mod-reportes

> **Source change**: `archive/2026-06-17-mejorar-reportes-analiticos`
> **Created**: 2026-06-17
> **Status**: Archived (with known gap F4 pendiente en T055/T056)

## MODIFIED Requirements

### Requirement: REP-001 — Reportes Analíticos por Categoría

**Descripción**: La vista de Reportes ofrece cinco categorías analíticas navegables, cada una con resumen visual, tabla sincronizada, indicadores destacados, filtros compartidos y exportación individual.

**Vista**: `views/reportes.php` (sección "Reportes Analíticos")
**Endpoint de carga**: `GET /api/reports.php?report=reportes-analiticos&anio=YYYY&trimestre=N&mes=N&comuna_id=N&establecimiento_id=N`
**Endpoint de exportación**: `POST /api/export.php` con `{tipo_reporte: <categoría>, formato: 'excel', ...filtros}`

**Categorías disponibles** (ordenadas por defecto):
1. **Errores por establecimiento** — Top establecimientos con más observaciones de tipo `ERROR`.
2. **Plazos de entrega** — Distribución dentro/fuera de plazo por establecimiento/mes.
3. **Uso de validador** — Distribución usa/no-usa validador por establecimiento/mes.
4. **Errores por serie** — Distribución de errores por serie REM (A, BS, BM, P, ANEXO, D).
5. **Errores por hoja** — Top hojas REM con más errores.

**Reglas de Negocio**:
- **Filtros compartidos** (FR-002): año, trimestre, mes, comuna, establecimiento. Las cinco categorías usan el mismo conjunto de filtros.
- **Preservación de filtros** (FR-003): cambiar de categoría conserva los filtros activos.
- **Alcance por rol** (FR-006): un registrador solo ve datos de sus establecimientos asignados; el filtro se aplica en backend vía `aplicarAlcancePorRol()`.
- **Consistencia comuna↔establecimiento** (FR-012): al elegir una comuna, el filtro de establecimiento solo muestra los de esa comuna.
- **Idioma** (FR-014): todas las etiquetas, mensajes y nombres en español.
- **Bloqueo de exportación sin datos** (FR-008): el botón "Exportar categoría" se deshabilita cuando la categoría no tiene datos, con mensaje en español.
- **Preservación de flujos previos** (FR-013): la exportación general clásica y el Informe de Errores del supervisor siguen disponibles idénticos a v2.3.0.

**Estructura de respuesta** (`api/reports.php?report=reportes-analiticos`):
```json
{
  "success": true,
  "data": {
    "anio": 2026,
    "filtros": {"anio": 2026, "trimestre": "", "mes": "", "comuna_id": "", "establecimiento_id": ""},
    "categorias": {
      "errores_establecimiento": {"titulo": "Errores por establecimiento", "resultados": [...], "totales": {...}, "estado": "success|empty|error", "mensaje": "..."},
      "plazos_entrega": {...},
      "uso_validador": {...},
      "errores_serie": {...},
      "errores_hoja": {...}
    },
    "indicadores": {"total_observaciones": N, "total_errores": N, "total_fuera_plazo": N, "total_sin_validador": N}
  },
  "message": "Reportes analíticos cargados"
}
```

#### Scenario: Supervisor abre Reportes con datos disponibles
- **WHEN** un supervisor o registrador autenticado abre `views/reportes.php` con datos en el año de trabajo
- **THEN** la vista muestra 5 tabs de categorías analíticas
- **AND** la primera tab (errores por establecimiento) se carga con su gráfico, tabla e indicador

#### Scenario: Cambio de categoría preserva filtros
- **WHEN** un usuario con filtros aplicados cambia a otra categoría
- **THEN** la nueva categoría carga con los mismos filtros activos
- **AND** los controles de filtro (selectores) reflejan los valores previamente elegidos

#### Scenario: Filtro de comuna sincroniza establecimientos
- **WHEN** un usuario selecciona una comuna
- **THEN** el selector de establecimiento solo muestra los establecimientos de esa comuna
- **AND** las consultas al backend rechazan combinaciones inconsistentes (establecimiento de otra comuna) con código 400

#### Scenario: Registrador solo ve su alcance
- **WHEN** un registrador con asignaciones anuales carga los reportes analíticos
- **THEN** todas las categorías muestran solo datos de sus establecimientos asignados
- **AND** el backend aplica `aplicarAlcancePorRol()` antes de ejecutar las agregaciones

#### Scenario: Exportación de categoría con datos
- **WHEN** un usuario hace click en "Exportar categoría" de una categoría con datos
- **THEN** se genera un archivo Excel con los mismos filtros y resultados visibles
- **AND** la exportación valida CSRF y permisos antes de generar el archivo
- **AND** un registrador solo exporta su propio alcance

#### Scenario: Bloqueo de exportación sin datos
- **WHEN** un usuario hace click en "Exportar categoría" de una categoría sin datos
- **THEN** el botón está deshabilitado y, si se fuerza, el sistema muestra mensaje en español "No hay datos para exportar en esta categoría"

#### Scenario: Cambio de año recarga automáticamente
- **WHEN** un usuario cambia el año de trabajo desde el selector global
- **THEN** los reportes analíticos se recargan con el nuevo año
- **AND** los totales y gráficos reflejan el nuevo alcance

#### Scenario: Expiración de sesión (95% SC-003 / 5% excepciones)
- **WHEN** la sesión expira durante un cambio de categoría
- **THEN** los filtros se limpian y el usuario es redirigido a login
- **AND** este caso representa el 5% de excepciones al SC-003 (preservación de filtros en 95% de los casos)

#### Scenario: Estado de carga claro
- **WHEN** se está cargando una categoría
- **THEN** la vista muestra un spinner con el texto "Cargando {titulo_categoria}..." en español
- **AND** el spinner se oculta al recibir respuesta o error

#### Scenario: Error recuperable por categoría
- **WHEN** una categoría falla al cargar
- **THEN** la vista muestra mensaje en español con (a) descripción legible del fallo, (b) botón "Reintentar" que repite la consulta con los mismos filtros
- **AND** las demás categorías no se ven afectadas

---

## ADDED Requirements

### Requirement: REP-002 — Indicadores Resumidos (KPI Cards)

**Descripción**: Tarjetas con totales principales (observaciones, errores, fuera de plazo, sin validador) que se recalculan al cambiar filtros.

**Reglas de Negocio**:
- Los totales se calculan en `api/reports.php?report=reportes-analiticos` junto con el resto de las agregaciones.
- Las tarjetas se renderizan con `data-destacados-categoria` por categoría y como bloque independiente en la parte superior.
- Si no hay datos, las tarjetas muestran estado "no disponible" en español.

#### Scenario: Tarjetas con totales consistentes
- **WHEN** un usuario carga un periodo con datos
- **THEN** las tarjetas de indicadores muestran los mismos totales que la suma de la tabla de la categoría activa
- **AND** al cambiar filtros, las tarjetas se recalculan dentro de los 5 segundos (SC-002)

---

## Known Gaps (no cubiertos por este delta)

- **F4 spinner (FR-010 refinado)**: La implementación actual muestra texto plano "Cargando reportes analíticos...". El refinamiento F4 exige un spinner HTML visible y texto dinámico por categoría `Cargando {titulo_categoria}...`. Cubierto por tarea T055 (pendiente externa).
- **F4 Reintentar (FR-011 refinado)**: La implementación actual muestra solo texto "No fue posible cargar esta categoría.". El refinamiento F4 exige un botón "Reintentar" que repita la consulta con los filtros activos. Cubierto por tarea T056 (pendiente externa).

Las tareas T025, T033, T040, T046, T052 están marcadas [X] en `tasks.md` con la nota "validación parcial, gap F4 pendiente en T055/T056". Re-validación humana completa requerida tras cierre de T055 y T056.
