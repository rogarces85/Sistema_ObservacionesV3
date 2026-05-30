## 1. Tabler Icon Helpers

- [x] 1.1 Crear partial `includes/icons.php` con funciones PHP para los 12 iconos Tabler SVG usados en el dashboard (chart-bar, clock-hour-4, circle-check, alert-triangle, chart-pie, search, zap, calendar, clipboard-list, edit, download, eye, file-text)

## 2. Migrar Cards de Estadísticas

- [x] 2.1 Reemplazar inline styles de fondo en las 4 cards (gradients) por clases `.bg-primary-lt`, `.bg-warning-lt`, `.bg-success-lt`, `.bg-danger-lt`
- [x] 2.2 Reemplazar inline styles de fondo en los contenedores de icono (gradients) por clases `.bg-primary`, `.bg-warning`, `.bg-success`, `.bg-danger`
- [x] 2.3 Reemplazar emojis (📊, ⏳, ✅, ⚠️) en las cards por iconos SVG Tabler vía `icon_chart_bar()`, `icon_clock()`, `icon_circle_check()`, `icon_alert_triangle()`
- [x] 2.4 Reemplazar inline styles de color en textos de las cards por clases `.text-primary`, `.text-warning`, `.text-success`, `.text-danger`, `.text-secondary`

## 3. Migrar Alertas

- [x] 3.1 Migrar alerta de "sin establecimientos asignados" a `<div class="alert alert-warning alert-icon">` con icono SVG `alert-triangle`
- [x] 3.2 Migrar alerta de "registradores sin asignaciones" a `<div class="alert alert-danger alert-icon">` con icono SVG `alert-triangle`

## 4. Migrar Títulos de Sección

- [x] 4.1 Reemplazar emoji en título "Distribución por Estado" por icono `chart-pie`
- [x] 4.2 Reemplazar emoji en título "Top Tipos de Error" por icono `search`
- [x] 4.3 Reemplazar emoji en título "Acciones Rápidas" por icono `zap`
- [x] 4.4 Reemplazar emoji en título "Observaciones por Mes" por icono `calendar`
- [x] 4.5 Reemplazar emoji en título "Últimas Observaciones" por icono `clipboard-list`

## 5. Migrar Acciones Rápidas

- [x] 5.1 Reemplazar inline backgrounds (colores hexadecimales) en items del list-group por clases `.bg-primary-lt`, `.bg-success-lt`, etc.
- [x] 5.2 Reemplazar emojis (📝, 📥, 📊, 👁️, 📄) por iconos SVG Tabler (`edit`, `download`, `chart-bar`, `eye`, `file-text`)
- [x] 5.3 Reemplazar inline styles de color en los spans de flecha (→) por clase `.text-primary`/`.text-success`/etc.

## 6. Limpieza General

- [x] 6.1 Reemplazar clase Tailwind `space-y-6` por contenedor con clases Bootstrap de spacing
- [x] 6.2 Reemplazar `text-2xl` inline en título por clase Bootstrap `.h2`
- [x] 6.3 Reemplazar inline styles de color en textos del header del dashboard (`#1e293b`, `#64748b`) por clases `.text-primary`/`.text-secondary`
- [x] 6.4 Reemplazar inline styles de color en celdas de tabla (`#1e293b`, `#64748b`) por clases Tabler
- [x] 6.5 Reemplazar inline `fw-semibold` duplicados por clases utilitarias Bootstrap consistentes

## 7. Verificación

- [x] 7.1 Verificar que no haya regresiones visuales en las 4 cards de estadísticas
- [x] 7.2 Verificar que los iconos SVG se rendericen correctamente en todos los navegadores objetivo
- [x] 7.3 Verificar que las alertas mantengan su funcionalidad y diseño responsive
- [x] 7.4 Confirmar cero `style="` attributes inline restantes en `views/dashboard.php` (solo quedan 5 estructurales: 3 heights de Chart.js + 2 display:none JS)
