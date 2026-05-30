## Context

El dashboard (`views/dashboard.php`) es la página principal del sistema. Actualmente usa componentes Tabler/Bootstrap en estructura (cards, grid, tablas, modales) pero mantiene ~40 inline styles con colores hardcodeados (gradientes, backgrounds, borders, textos), emojis como iconos, y clases Tailwind sueltas (`space-y-6`, `text-2xl`). El archivo `assets/css/tabler-override.css` ya define `--tblr-primary: #0ea5e9` y colores slate, pero el dashboard no los aprovecha consistentemente.

## Goals / Non-Goals

**Goals:**
- Reemplazar todos los inline styles de color por clases utilitarias de Tabler (`.bg-primary-lt`, `.text-primary`, `.text-secondary`, etc.)
- Reemplazar emojis (📊, ⏳, ✅, ⚠️, 📝, 📥, 👁️, ⚡, 📈, etc.) por iconos SVG inline de Tabler
- Reemplazar clases Tailwind (`space-y-6`, `text-2xl`) por equivalentes Bootstrap/Tabler (`mb-4`, `h2`, etc.)
- Eliminar todos los inline styles de tipografía (color, font-size, font-weight) usando clases utilitarias
- Usar el color system existente de `tabler-override.css` (variables `--tblr-*`)

**Non-Goals:**
- No se modifican los gráficos Chart.js ni su lógica
- No se cambia la estructura HTML del modal de informe
- No se toca backend, PHP ni JavaScript funcional
- No se modifican otros archivos (solo dashboard.php y posiblemente tabler-override.css)

## Decisions

### Iconos SVG inline en lugar de Tabler Icons CDN
Tabler ofrece un set de iconos vía `@tabler/icons`, pero no está incluido en el proyecto. Se usará SVG inline (como ya hace `header.php`) para los ~8 iconos necesarios. Esto evita añadir una dependencia CDN y mantiene coherencia con el header existente.

### Mapeo de emoji a icono Tabler
| Emoji Actual | Icono Tabler SVG |
|-------------|------------------|
| 📊 | `chart-bar` |
| ⏳ | `clock-hour-4` |
| ✅ | `circle-check` |
| ⚠️ | `alert-triangle` |
| 📝 | `edit` |
| 📥 | `download` |
| 👁️ | `eye` |
| ⚡ | `zap` |
| 📈 | `chart-pie` |
| 🔍 | `search` |
| 📅 | `calendar` |
| 📋 | `clipboard-list` |
| 📄 | `file-text` |

### Cards sin gradientes inline
Las 4 cards de estadísticas actualmente usan `background: linear-gradient(...) + border-color` inline. Se reemplazarán por:
- `.card` con clase de fondo `.bg-primary-lt`, `.bg-warning-lt`, `.bg-success-lt`, `.bg-danger-lt` (Tabler light backgrounds)
- Icono sobre fondo sólido con `.bg-primary`, `.bg-warning`, etc.
- Textos con `.text-primary`, `.text-warning`, etc.

### Alert banners → Tabler `.alert-icon`
Las alertas de "sin asignaciones" y "registradores sin asignaciones" usan `d-flex align-items-center gap-3` con emoji en un div. Se reemplazarán por `.alert.alert-icon` de Tabler con icono SVG.

### Acciones Rápidas → List group Tabler
Los `<a>` del list-group actual tienen inline backgrounds (`#f0f9ff`, `#ecfdf5`, etc.) y emojis. Se reemplazarán por iconos SVG y se usará el hover state default de Bootstrap list-group.

## Risks / Trade-offs

| Riesgo | Mitigación |
|--------|-----------|
| Iconos SVG aumentan tamaño HTML | Solo ~8 iconos, SVG inline es marginal. Si se desea optimizar después, se puede migrar a sprite sheet. |
| Variables de color Tabler no cubren los tonos exactos actuales (sky blue, amber, emerald, rose) | `tabler-override.css` permite añadir clases utilitarias custom si Tabler no ofrece el tono. Revisar Tabler 1.4.0 tiene `.bg-{color}-lt` para primary, warning, success, danger — cubren los 4 estados. |
| Clases `space-y-6` reemplazadas por `mb-*` pueden alterar espaciado en vistas responsivas | Bootstrap `g-3` y `mb-4` son equivalentes probados. Verificar en mobile después del cambio. |
