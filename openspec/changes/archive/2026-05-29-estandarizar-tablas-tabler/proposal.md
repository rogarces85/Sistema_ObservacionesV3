## Why

Actualmente las tablas del sistema usan estilos inconsistentes: algunas tienen clases Tabler (`table table-vcenter card-table`), otras usan clases Tailwind-like (`text-slate-800`, `font-bold`), y otras carecen de clases CSS por completo. Esto dificulta el mantenimiento y la experiencia de usuario al navegar entre vistas. Tabler define un modelo de tabla completo con hover, responsive contenedor, badges, alineaciones y empty states que debemos estandarizar.

## What Changes

- Agregar `class="table table-vcenter card-table table-hover"` a todas las tablas del sistema
- Envolver todas las tablas en `<div class="table-responsive">`
- Reemplazar clases Tailwind-like en celdas (`text-slate-*`, `font-bold`, `flex items-center gap-3`) por clases Tabler/Bootstrap equivalentes
- Usar `class="text-end"` en lugar de `text-right`
- Estandarizar empty states con `text-center text-secondary py-4`
- No hay cambios en backend, datos ni lógica de negocio

## Capabilities

### New Capabilities
- `table-model`: Tablas estandarizadas al modelo Tabler con clases consistentes

### Modified Capabilities
- (ninguno — solo refactor de frontend)

## Impact

| Archivo | Tipo de Cambio |
|---------|---------------|
| `views/observaciones.php` | Agregar clases Tabler a tabla principal, reemplazar clases Tailwind |
| `views/usuarios.php` | Agregar `table-hover` |
| `views/supervision.php` | Agregar clases Tabler, wrapper responsive |
| `views/establecimientos.php` | Agregar `table-hover` |
| `views/reportes.php` | Agregar `table-hover` a las 5 tablas |
| `views/eliminadas.php` | Agregar clases Tabler, wrapper responsive |
| `views/asignaciones.php` | Agregar clases Tabler a tablas JS-generadas |
