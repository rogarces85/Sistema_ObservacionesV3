## Why

El dashboard actual ya usa componentes Tabler/Bootstrap en estructura, pero mantiene ~40 inline styles con colores hardcodeados y emojis como iconos. Esto dificulta la consistencia visual, el mantenimiento y el theming del sistema. Se propone limpiar y estandarizar el dashboard usando las utilidades nativas de Tabler.

## What Changes

- Reemplazar todos los inline styles de colores en las 4 cards de estadísticas por clases utilitarias de Tabler (`.bg-primary-lt`, `.text-primary`, etc.)
- Reemplazar emojis en cards, headers y acciones rápidas por iconos SVG de Tabler
- Estandarizar alertas de advertencia con clases Tabler (`.alert-warning`, `.alert-icon`)
- Migrar sección de "Acciones Rápidas" a componentes `list-group` de Tabler con iconos SVG
- Limpiar clases Tailwind sueltas (`space-y-6`, `text-2xl`) por equivalentes Bootstrap
- Eliminar inline styles tipográficos (color, font-size) usando clases de Tabler
- No hay cambios en backend, lógica de negocio, Chart.js ni funcionalidad

## Capabilities

### New Capabilities
- `dashboard-cards`: Cards de estadísticas con iconos Tabler SVG, colores desde variables CSS, sin inline styles

### Modified Capabilities
- (ninguno — solo refactor de frontend)

## Impact

| Archivo | Tipo de Cambio |
|---------|---------------|
| `views/dashboard.php` | Refactor: inline styles → clases utilitarias, emojis → iconos SVG, clases Tailwind → Bootstrap |
| `assets/css/tabler-override.css` | Posible extensión menor si faltan variables de color |
