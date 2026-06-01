## Context

Actualmente hay 15 tablas en 8 vistas del sistema. Cada una tiene un nivel diferente de adopción de Tabler:
- `usuarios.php` y `dashboard.php`: usan `table table-vcenter card-table` correctamente pero sin `table-hover`
- `observaciones.php`: NO usa clases Tabler en su tabla principal — usa clases Tailwind-like (`text-slate-*`, `font-bold`)
- `supervision.php`, `reportes.php`, `eliminadas.php`, `asignaciones.php`: mezclan Tabler con clases legacy o JS inline
- La mayoría usa `text-right` en lugar de `text-end` de Bootstrap/Tabler

## Goals / Non-Goals

**Goals:**
- `table table-vcenter card-table table-hover` en TODAS las tablas HTML
- `<div class="table-responsive">` envolviendo cada tabla
- Reemplazar clases Tailwind en celdas por clases Bootstrap/Tabler (`fw-semibold`, `text-secondary`, `text-muted`)
- `text-end` en lugar de `text-right`
- Empty states consistentes con `text-center text-secondary py-4`
- Tablas JS-generadas en asignaciones.php deben usar las mismas clases

**Non-Goals:**
- No cambiar estructura ni lógica de las tablas
- No migrar a DataTables ni otro plugin
- No modificar backend, modelos, ni controladores
- No cambiar el modal de informe de dashboard

## Decisions

### table-hover en todas las tablas
Tabler define `table-hover` para resaltar filas al pasar el mouse. Se agrega a todas las tablas del sistema para mejorar la experiencia de usuario al navegar listas de datos.

### text-end en lugar de text-right
Bootstrap 5 usa `text-end` para alineación derecha. `text-right` es de Bootstrap 4 y no funciona en Tabler 1.4.0 (Bootstrap 5). Se reemplaza en todas las tablas.

### Clases de texto consistentes
| Clase Tailwind Actual | Clase Tabler/Bootstrap |
|----------------------|----------------------|
| `text-slate-800` o `#1e293b` | `fw-semibold` o `text-primary` |
| `text-slate-600/700` | `text-secondary` o `text-muted` |
| `text-slate-400/500` | `text-secondary` o `text-muted small` |
| `font-bold` o `fw-bold` | `fw-semibold` (más sutil que bold) |
| `font-semibold` | `fw-semibold` |
| `text-xs` | `small` |

### Tablas JS-generadas
Las tablas en `asignaciones.php` generadas vía JavaScript inline y las de `supervision.php`, `eliminadas.php` vía fetch + innerHTML deben usar las mismas clases Tabler. Se actualizan los templates JS (strings HTML) para incluir `table table-vcenter card-table table-hover` y `text-end`.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|-----------|
| table-hover puede no funcionar en dispositivos touch | Bootstrap lo maneja con media queries; en touch no hay hover, no hay cambio visual (no es un problema) |
| Cambiar text-right a text-end rompe alineación en vistas no migradas | text-end es el estándar Bootstrap 5, compatible con Tabler 1.4.0 |
| Clases Tailwind en observaciones.php son muchas (10+) | Reemplazo progresivo: primero clases de tabla y celdas, luego contenido anidado |
