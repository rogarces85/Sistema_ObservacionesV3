# Implementation Plan: Cerrar Gap F4 — UX de Carga y Error por Categoría

**Branch**: `[003-f4-categoria-ux]` | **Date**: 2026-06-17 | **Last re-validated**: 2026-06-17 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/003-f4-categoria-ux/spec.md`

## Summary

Refinar la UX de las cinco categorías analíticas en `views/reportes.php` para que cada una muestre un indicador de carga explícito (spinner + texto "Cargando {nombre_categoria}...") mientras se obtienen los datos, y un mensaje de error recuperable con botón "Reintentar" cuando la consulta falla. El refinamiento se aplica puramente en la capa de vista (HTML + CSS + JS) sin modificar la API ni el modelo. Se reutilizan los endpoints, contratos y estructura HTML existentes del feature 002 archivado. Cierra los pendientes T055 y T056.

## Technical Context

**Language/Version**: PHP 7.4+ (vistas server-rendered), JavaScript ES6+ (cliente), HTML5, CSS3
**Primary Dependencies**: Tabler Core 1.4 (clases `.spinner-border`, `.btn`), ApexCharts 3.45 (sin cambios)
**Storage**: N/A (sin cambios de BD)
**Testing**: Validación manual contra `quickstart.md` (pasos 1–10 del feature 002 + 3 pasos nuevos para UX de carga/error), lint PHP, lint JS
**Target Platform**: Apache en XAMPP, navegadores web de escritorio y móvil
**Project Type**: Aplicación web PHP monolítica (extensión/refinamiento del feature existente 002)
**Performance Goals**: Spinner aparece en <200ms tras aplicar filtro; spinner desaparece en <100ms tras respuesta; cero overhead perceptible
**Constraints**: Sin nuevas dependencias; sin cambios de esquema BD; sin nuevos endpoints; UI en español; convención BEM; consistencia con `assets/css/tabler-override.css`; sin estilos inline (T050 vigente)
**Scale/Scope**: 5 categorías × 1 panel cada una = 5 paneles con estado UI explícito

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

**Gates derivados de la Constitución v1.1.0 (2026-06-17) del proyecto:**

1. **I. Seguridad**: PASS. No introduce nuevos endpoints ni nuevas superficies de ataque. Reusa `api/reports.php?report=reportes-analiticos` y `api/export.php` ya validados con CSRF y permisos por rol. El refinamiento es puramente cliente.
2. **II. Arquitectura**: PASS. Cambios localizados en `views/reportes.php` (HTML) y `assets/js/reportes.js` (lógica de UI). La API y el modelo no se tocan. Sin acceso a superglobals en cliente.
3. **III. Idioma**: PASS. Todos los textos visibles en español: "Cargando {nombre_categoria}...", "No fue posible cargar esta categoría. Reintentar". Cumple FR-014 implícito del feature 002.
4. **IV. Stack**: PASS. Usa clases de Tabler/Bootstrap (`.spinner-border`, `.btn`, `.btn-primary`, `.btn-sm`) ya disponibles. Sin nuevos íconos (reusa `tablerIcon('refresh')` para "Reintentar"). Sin nuevas dependencias.
5. **V. Mantenibilidad**: PASS. Convenciones BEM (`reportes-analytics__*`) en CSS, camelCase en JS, sin comentarios innecesarios. Refactor de funciones pequeñas (`setEstadoCargando`, `setEstadoError`, `recargarCategoria`).
6. **VI. Trazabilidad documental**: PASS. Spec en `specs/003-f4-categoria-ux/spec.md`; manual `docs/manuales/reportes-exportacion.md` será actualizado con mockups de los nuevos estados de carga y error.
7. **VII. Sistema desde 0 (BD intocable)**: PASS. Sin cambios de esquema; sin migraciones; sin nuevas tablas ni columnas.
8. **Stack Tecnológico**: PASS. Sin nuevas dependencias Composer ni npm. Reusa `composer.json` y `package.json` existentes.
9. **Gobernanza (ciclo SpecKit)**: PASS. Producido en el orden correcto: `discover` (README v2.3.0) → `constitution` (v1.1.0) → `specify` (este feature) → `plan` (este documento).

## Project Structure

### Documentation (this feature)

```text
specs/003-f4-categoria-ux/
├── plan.md              # Este documento
├── research.md          # Phase 0
├── data-model.md        # Phase 1
├── quickstart.md        # Phase 1
├── contracts/
│   └── ui-estados-carga-error.md   # Phase 1 (contrato de UI)
├── tasks.md             # Phase 2 (generado por /speckit.tasks, no por este comando)
└── checklists/
    └── requirements.md
```

### Source Code (repository root)

Archivos modificados (todos del feature 002 existente, sin archivos nuevos):

```text
views/
└── reportes.php                            # Agregar spinner HTML + botón "Reintentar" en cada panel

assets/
├── js/
│   └── reportes.js                         # Refactor: setEstadoCargando, setEstadoError, recargarCategoria
└── css/
    └── tabler-override.css                 # Clases BEM: reportes-analytics__estado--loading, --error, __retry-button

docs/
└── manuales/
    └── reportes-exportacion.md             # Actualizar mockups con estados de carga y error
```

**Structure Decision**: El feature es un refinamiento incremental del feature 002 archivado. No introduce nueva estructura de directorios ni nuevos archivos de código. Modifica in-place 3 archivos existentes (`views/reportes.php`, `assets/js/reportes.js`, `assets/css/tabler-override.css`) y 1 archivo de documentación (`docs/manuales/reportes-exportacion.md`).

## Complexity Tracking

No hay violaciones constitucionales ni complejidad excepcional que justificar. El feature es un refinamiento UX acotado en una sola vista + JS + CSS.

## Phase 0: Research

Ver [research.md](research.md). Decisiones clave resueltas:
- Reutilizar `.spinner-border` de Bootstrap/Tabler (no ApexCharts loader) para consistencia con el resto del sistema.
- Reutilizar `tablerIcon('refresh')` de Tabler Icons para el icono del botón "Reintentar".
- Función `recargarCategoria(cat)` que extrae los filtros activos y ejecuta el fetch por-categoría, no el bundle completo.
- Aislamiento de errores se preserva con el loop existente en `setEstadoAnalitico(categoria, mensaje, tipo)`.

## Phase 1: Design & Contracts

Ver [data-model.md](data-model.md), [contracts/ui-estados-carga-error.md](contracts/ui-estados-carga-error.md) y [quickstart.md](quickstart.md).

## Constitution Check Post-Design

1. **I. Seguridad**: PASS. Sin cambios en API; CSRF y permisos siguen aplicándose en el endpoint subyacente.
2. **II. Arquitectura**: PASS. Cambios limitados a vista + cliente.
3. **III. Idioma**: PASS. Strings en español validados contra Constitución y spec.
4. **IV. Stack**: PASS. Reuso de utilidades Tabler ya declaradas en `includes/icons.php`.
5. **V. Mantenibilidad**: PASS. Funciones pequeñas y nombradas semánticamente; sin duplicación con helpers existentes.
6. **VI. Trazabilidad documental**: PASS. Manual a actualizar en implementación (tarea T006 Polish).
7. **VII. Sistema desde 0 (BD intocable)**: PASS. Sin migraciones.
8. **Stack Tecnológico**: PASS. Sin nuevas dependencias.
9. **Gobernanza (ciclo SpecKit)**: PASS. Artefactos completos: `plan.md`, `research.md`, `data-model.md`, `quickstart.md`, `contracts/`. `tasks.md` se generará en `/speckit.tasks` posterior.
