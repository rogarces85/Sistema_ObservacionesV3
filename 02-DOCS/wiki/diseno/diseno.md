# Diseño Visual — Sistema de Observaciones REM

> **Ubicación:** `02-DOCS/wiki/diseno/diseno.md`
> **Fuente original:** `DESIGN.md` (raíz)
> **Última actualización:** 2026-11-13

---

## 1. Identidad institucional

- **Nombre:** Sistema de Observaciones REM
- **Institución:** Servicio de Salud Osorno, Región de Los Lagos, Chile
- **Paleta derivada del logo oficial DEIS Osorno:**

| Token | Hex | Uso | Contraste sobre blanco |
|-------|-----|-----|------------------------|
| `deis-navy-800` | `#003366` | Texto, titulares, encabezados | 12.6:1 |
| `deis-blue-600` | `#0B71B9` | Texto, enlaces, botones primarios | 5.13:1 |
| `deis-cyan-500` | `#00AEEF` | Relleno y acentos únicamente | Insuficiente para texto |
| `deis-cyan-300` | `#76C7ED` | Bandas y acentos decorativos | Relleno |

- **Tema light/dark persistido:** en modo oscuro, primary remapea a `#4FB3E8`, enlaces a `#76C7ED`.
- **Voice:** Formal, basado en procesos regulatorios; sin idioma coloquial.
- **Tokens:** CSS custom properties en `assets/css/tokens.css`; overrides componentes en `assets/css/tabler-override.css`. `assets/css/styles.css` es **deprecated** (no extender).

---

## 2. Tipografía

| Estilo | Fuente | Tamaño | Peso | Line-height / Espaciado |
|--------|--------|--------|------|-------------------------|
| Body | Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto | 0.9375rem | 400 | 1.5 |
| Label | Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto | 0.75rem | 600 | letter-spacing: 0.05em, text-transform: uppercase |

---

## 3. Espaciado y bordes

| Token | Valor |
|-------|-------|
| `rounded.sm` | 0.375rem |
| `rounded.md` | 0.5rem |
| `rounded.lg` | 0.75rem |
| `rounded.pill` | 9999px |
| `spacing.xs` | 0.25rem |
| `spacing.sm` | 0.5rem |
| `spacing.md` | 1rem |
| `spacing.lg` | 1.5rem |
| `spacing.xl` | 2rem |

---

## 4. Componentes base

### Botón primario
```css
background-color: {colors.deis-blue-600};
color: #ffffff;
padding: 0.5rem 1rem;
border-radius: {rounded.md};
```

### Botón ghost primario
```css
background-color: transparent;
color: {colors.deis-blue-600};
padding: 0.5rem 1rem;
```

---

## 5. Estados de observación (tokens de color)

| Estado | Token | Hex | Uso |
|--------|-------|-----|-----|
| Pendiente | `status-pending` | `#B45309` | Badge, fila, icono |
| Aprobado | `status-approved` | `#15803D` | Badge, fila, icono |
| Rechazado | `status-rejected` | `#475569` | Badge, fila, icono |
| Error | `status-error` | `#B91C1C` | Badge, fila, icono |
| Justificado | `status-justified` | `#0077A3` | Badge, fila, icono |

> **Regla:** El frontend **no inventa** estados/roles/etiquetas. Todo lo que se muestra debe mapear a `config/constants.php`.

---

## 6. Reglas de UI (binding del proyecto)

1. **Solo Tabler 1.4** (`@tabler/core` + `@tabler/icons-webfont`). No introducir Tailwind, standalone Bootstrap, ni frameworks CSS compitiendo.
2. **Tokens en `assets/css/tokens.css`**; overrides semánticos/componentes en `assets/css/tabler-override.css`. `assets/css/styles.css` es legacy (todavía cargado en `includes/header.php:51` y `views/login.php:18`, pero **no extender**).
3. **Tema light/dark** via `data-bs-theme` en `<html>`, persistido en cookie `rem.theme` (no solo localStorage). Chart.js debe re-leer tokens y refrescar en evento `rem:theme-changed`.
4. **Sin `style="..."` inline** en markup nuevo o modificado.
5. **Cada `views/*.php` independiente** y reutiliza shell compartido (`includes/header.php`, `sidebar.php`, `footer.php`, `breadcrumbs.php`) — no duplicar layout/scripts.
6. **Accesibilidad (WCAG AA):**
   - Contraste mínimo 4.5:1 texto normal, 3:1 texto grande
   - Navegación por teclado: todos los elementos interactivos focusables
   - Screen readers: labels descriptivos en inputs; `aria-label` o `aria-labelledby` cuando aplique
   - Focus indicator visible (personalizado en `tabler-override.css`)
   - Dark mode via tokens CSS (no valores fijos light-only)
   - Responsive mínimo 320px; componentes reflow graceful antes de breakpoints

---

## 7. Gráficos (Chart.js)

- Colores de series toman de tokens CSS (`--color-primary`, `--color-success`, `--color-warning`, `--color-danger`, `--color-info`).
- Al cambiar tema, escuchar `rem:theme-changed` y llamar `.update()` en cada instancia Chart.js.
- Altura de contenedores: `.report-chart-frame` (estándar), `.report-chart-frame--xlong` (900px para >40 items).

---

## 8. Recursos

- `assets/css/tokens.css` — Design tokens (CSS custom properties)
- `assets/css/tabler-override.css` — Overrides Tabler (sidebar, header, cards, a11y)
- `assets/css/styles.css` — **Deprecated** (1650 líneas, no extender)
- `assets/js/theme.js` — Toggle tema, sidebar, búsqueda, FAB
- `assets/js/charts.js` — Helpers Chart.js con tokens
- `config/constants.php` — Estados, roles, meses, series, hojas REM (fuente de verdad para UI)