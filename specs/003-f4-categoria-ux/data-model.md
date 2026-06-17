# Data Model: Cerrar Gap F4 — UX de Carga y Error por Categoría

**Branch**: `[003-f4-categoria-ux]` | **Date**: 2026-06-17
**Spec**: [spec.md](spec.md)

> **Nota importante**: Este feature NO introduce cambios en el modelo de datos (base de datos). El "data model" aquí documenta las **estructuras lógicas en el cliente (JavaScript)** que representan los estados de UI por categoría.

## Entidad 1: Estado de Categoría Analítica (cliente)

Representa el estado UI de una categoría específica en un momento dado.

| Campo | Tipo | Valores posibles | Default | Descripción |
|-------|------|------------------|---------|-------------|
| `categoria` | string (enum) | `errores_establecimiento` \| `plazos_entrega` \| `uso_validador` \| `errores_serie` \| `errores_hoja` | — | Identificador único de la categoría (clave) |
| `estado` | string (enum) | `idle` \| `cargando` \| `listo` \| `vacio` \| `error` | `idle` | Estado actual de la categoría |
| `mensaje` | string \| null | texto en español | `null` | Mensaje a mostrar (solo en estados `vacio` y `error`) |
| `ultimaCarga` | timestamp (ISO 8601) \| null | string | `null` | Momento de la última respuesta exitosa |
| `reintentos` | integer | ≥0 | `0` | Contador de reintentos desde el último éxito |

### Transiciones de estado

```
                    aplicarFiltro() / cambiarCategoria() / recargarCategoria()
        ┌─────────────────────────────────────────────────────────────────────┐
        │                                                                     │
        ▼                                                                     │
     [idle] ──solicitud──▶ [cargando] ──éxito──▶ [listo] ◀──┐
        ▲                     │                            │
        │                     ├──error──▶ [error] ──Reintentar──▶ [cargando]
        │                     │              │                    │
        │                     │              └──mostrar mensaje──┘
        │                     ▼
        │                  [vacio] ◀──sin_datos── [cargando]
        │
        └──navegar_afuera── [idle] (limpieza opcional)
```

**Reglas de transición**:
- `idle → cargando`: al iniciar una consulta (carga inicial, cambio de filtros, reintento).
- `cargando → listo`: respuesta HTTP 2xx con datos (resultados.length > 0).
- `cargando → vacio`: respuesta HTTP 2xx sin datos (`resultados.length === 0`).
- `cargando → error`: respuesta HTTP no-2xx, timeout, error de red, JSON inválido.
- `error → cargando`: click en botón "Reintentar".
- `listo/vacio/error → cargando`: cambio de filtros o cambio de año.
- `reintentos++`: cada transición a `cargando` desde `error`.

## Entidad 2: Filtros Activos (existente, reusado)

No se modifica. Se reutiliza `obtenerFiltrosAnaliticos()` que devuelve:

| Campo | Tipo | Origen |
|-------|------|--------|
| `anio` | integer | `<select id="filtroAnio">` |
| `trimestre` | string | `<select id="filtroTrimestre">` |
| `mes` | string | `<select id="filtroMes">` |
| `comuna_id` | integer \| '' | `<select id="filtroComuna">` |
| `establecimiento_id` | integer \| '' | `<select id="filtroEstablecimiento">` |

Estos filtros se preservan al reintentar (FR-004, SC-004).

## Entidad 3: Configuración de la Categoría (existente, reusado)

`CATEGORIAS_ANALITICAS` en `assets/js/reportes.js:30-36`:

| Clave | Título (mostrado al usuario) | Color (hex) |
|-------|------------------------------|-------------|
| `errores_establecimiento` | Errores por establecimiento | `#dc2626` |
| `plazos_entrega` | Plazos de entrega | `#ca8a04` |
| `uso_validador` | Uso de validador | `#7c3aed` |
| `errores_serie` | Errores por serie | `#0ea5e9` |
| `errores_hoja` | Errores por hoja | `#16a34a` |

El título se usa en el texto "Cargando {titulo}..." (FR-009) y en el mensaje de error cuando aplica.

## Reglas de validación

- **Aislamiento entre categorías** (FR-006, SC-005): el cambio de estado de una categoría no afecta el estado de las otras cuatro. Cada categoría tiene su propio div `[data-estado-categoria]` independiente.
- **No duplicación de solicitudes** (FR-007): el botón "Reintentar" se deshabilita mientras una consulta está en vuelo; no se permite disparar dos fetches simultáneos a la misma categoría.
- **Cancelación en navegación** (edge case): si el usuario navega fuera de la vista de Reportes durante un fetch, la respuesta tardía se descarta sin alterar el DOM.

## Cambios al modelo de datos del backend (NINGUNO)

- ❌ No se agregan tablas.
- ❌ No se modifican columnas.
- ❌ No se crean índices.
- ❌ No se crean stored procedures ni vistas materializadas.
- ✅ Cumplimiento de Constitución VII (BD intocable) y Assumption 4 del spec.
