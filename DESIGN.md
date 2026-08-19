---
name: Sistema de Observaciones REM
description: Interfaz institucional para gestión de observaciones sanitarias REM con identidad DEIS Osorno.
colors:
  deis-navy-800: "#003366"
  deis-blue-600: "#0B71B9"
  deis-cyan-500: "#00AEEF"
  deis-cyan-300: "#76C7ED"
  neutral-dark: "#0f172a"
  neutral-medium: "#1e293b"
  neutral-light: "#f6f8fb"
  neutral-surface: "#ffffff"
  status-pending: "#B45309"
  status-approved: "#15803D"
  status-rejected: "#475569"
  status-error: "#B91C1C"
  status-justified: "#0077A3"
typography:
  body:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto"
    fontSize: "0.9375rem"
    fontWeight: 400
    lineHeight: 1.5
  label:
    fontFamily: "Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto"
    fontSize: "0.75rem"
    fontWeight: 600
    letterSpacing: "0.05em"
    textTransform: "uppercase"
rounded:
  sm: "0.375rem"
  md: "0.5rem"
  lg: "0.75rem"
  pill: "9999px"
spacing:
  xs: "0.25rem"
  sm: "0.5rem"
  md: "1rem"
  lg: "1.5rem"
  xl: "2rem"
components:
  button-primary:
    backgroundColor: "{colors.deis-blue-600}"
    textColor: "#ffffff"
    padding: "0.5rem 1rem"
    rounded: "{rounded.md}"
  button-ghost-primary:
    backgroundColor: "transparent"
    textColor: "{colors.deis-blue-600}"
    padding: "0.5rem 1rem"
    rounded: "{rounded.md}"
  button-icon:
    width: "2.25rem"
    height: "2.25rem"
    rounded: "{rounded.md}"
  card:
    backgroundColor: "{colors.neutral-surface}"
    rounded: "{rounded.md}"
    padding: "1rem"
  input:
    backgroundColor: "{colors.neutral-surface}"
    textColor: "{colors.neutral-medium}"
    rounded: "{rounded.md}"
    padding: "0.75rem 1rem"
  badge-status:
    rounded: "{rounded.pill}"
    padding: "0.25rem 0.75rem"
---

# Design System: Sistema de Observaciones REM

## Overview

**Creative North Star: "La Ventana Institucional"**

Este sistema es la interfaz frontal de un servicio de salud pública; transmite autoridad institucional sin sacrificar accesibilidad. La experiencia es **dinámica y confiable**: responde ágilmente a las acciones del usuario con transiciones suaves y feedback visual inmediato, reflejando que el sistema está vivo y funciona. Mantiene densidad **moderada**: espaciado generoso pero sin desperdiciar espacio, priorizando la lectura y comprensión sobre la mera compacidad. La paleta surge del logo DEIS Osorno (navy profundo, azul medio, cian, celeste), pero se aplica con rigor: navy y azul son texto/acción, cian y celeste son acentos/relleno, evitando equívocos en contraste WCAG. El sistema respeta tanto el contexto de usuarios bajo presión (registradores con plazos) como supervisores con múltiples flujos de información.

**Key Characteristics:**
- Identidad institucional clara: paleta DEIS como verdad de marca.
- Contraste perfeccionado: navy/azul ≥ 5.1:1 sobre fondo claro; remapeos automáticos en dark mode.
- Transiciones suaves y retroalimentación táctil: botones que se elevan, cards que se deslizan, un sistema que *responde*.
- Accesibilidad integrada: focus rings, tema oscuro, reducción de movimiento respetada.
- Flexibilidad temática: light/dark automático según preferencia del usuario, colores recalculados sin recompilar.

## Colors

La paleta respeta la autoridad visual del servicio de salud, derivada del logo oficial DEIS, con énfasis en accesibilidad (WCAG 2.1 AA mínimo para contrastes críticos).

### Primary

- **Deep Navy** (#003366 / DEIS navy-800): Texto principal, encabezados de nivel 1-2, estructura profunda. Es el color de mayor contraste (12.6:1 sobre blanco) y define la seriedad institucional.
- **Medium Blue** (#0B71B9 / DEIS blue-600): Acción, enlaces, botones primarios. Contraste 5.13:1 sobre blanco; se transforma a #4FB3E8 en dark mode para mantener legibilidad.
- **Cyan** (#00AEEF / DEIS cyan-500): Acentos, bandas, rellenos únicamente. Nunca se usa como texto en claro. En contextos de relleno profundo (badges, alertas) se acompaña de navy para asegurar contraste.
- **Light Cyan** (#76C7ED / DEIS cyan-300): Decoración, bandas secundarias, acentos sutiles. Relleno exclusivamente.

### Secondary (Status Palette)

Dominio de cinco estados observables, cada uno con variantes para relleno y texto:
- **Pending** (#B45309 texto, #FEF3C7 relleno): Observación esperando acción.
- **Approved** (#15803D texto, #DCFCE7 relleno): Supervisión completada, aceptada.
- **Rejected** (#475569 texto, #E2E8F0 relleno): Rechazada, requiere reenvío.
- **Error** (#B91C1C texto, #FEE2E2 relleno): REM contiene error estructural.
- **Justified** (#0077A3 texto, #D7EEFA relleno): Observación con justificación documentada.

### Neutral

- **Surface** (#ffffff claro, #0f172a oscuro): Fondo de tarjetas y contenedores principales.
- **Surface-2** (#f6f8fb claro, #111c33 oscuro): Fondo de tablas, secciones secundarias.
- **Body** (#1e293b claro, #e2e8f0 oscuro): Texto corporal predeterminado.
- **Muted** (#64748b claro, #94a3b8 oscuro): Labels, captions, navegación secundaria.
- **Border** (#e2e8f0 claro, #1e293b oscuro): Líneas divisoras, bordes sutiles.

### Named Rules

**The Logo Fidelity Rule.** Los cuatro colores del logo DEIS son la fuente de verdad visual; no se añaden azules secundarios ni paletas rivales. El sistema crece *dentro* de esta cuarteta, no fuera de ella.

**The Contrast-First Rule.** Navy y azul son las únicas opciones para texto sobre fondo claro (≥5:1). Cian y celeste son decoración pura. En dark mode, se remapean automáticamente: navy → #4FB3E8, azul → #76C7ED. Nunca se fuerza un color claro sobre fondo oscuro.

## Typography

**Display Font:** Inter (primaria), fallback a -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto.
**System Stack:** Humanista, modern-neutral, sin serifas. Inter es la elección de Tabler 1.4; se mantiene sin cambios.

**Character:** Legible, accesible, modernamente formal. Inter transmite profesionalismo sin frialdad. Funciona en rangos de tamaño (1rem a 2rem), pesos (400 a 700), y en ambos temas.

### Hierarchy

- **Display** (bold, 2rem, 1.1 lineheight): Títulos de página, secciones de panel. Raro; normalmente se prefiere size-1.5rem (title).
- **Title** (semibold, 1.5rem, 1.2 lineheight): Encabezados de página principal (`h1.page-title`). Mantiene eje visual.
- **Headline** (semibold, 1.125rem, 1.3 lineheight): Subencabezados, títulos de tarjeta, headers de tabla. Menos prominente que title; estructura la lectura.
- **Body** (400, 0.9375rem, 1.5 lineheight): Párrafos, copias largas, descripciones. Optimizado para legibilidad en pantalla; max-width ~65–75ch en textos ricos.
- **Label** (semibold, 0.75rem, 1.2 lineheight, letter-spacing 0.05em, uppercase): Etiquetas de formulario, badges, captions. Pequeño pero visible; la mayúscula ayuda a separación visual.

### Named Rules

**The Mono-Family Rule.** Inter es la única fuente de pantalla. No se añaden serifs, monospace, o fuentes de script. Las excepciones (código, números tabulares) se manejan con pesos o tamaños de Inter, no con fuentes nuevas.

## Layout

El sistema usa grid Bootstrap implícito (12 columnas, breakpoint a 992px para mobile-first), con sidebar de ancho fijo (260px desktop, colapsable a 72px mini, off-canvas en mobile < 992px).

- **Spacing Rhythm:** Todos los espacios (`gap`, `padding`, `margin`) son múltiplos de `--tblr-spacer` (1rem). Así: 0.25rem, 0.5rem, 0.75rem, 1rem, 1.25rem, 1.5rem, 2rem (xs a xl).
- **Container Behavior:** Ancho máximo en páginas es el viewport menos sidebar y padding marginal. Tablas y dashboards responden a contenedor; cards en grid de 2–3 columnas en desktop, 1 en mobile.
- **Density:** Balanceada. Headers de tabla usan 0.75rem top/bottom padding; cuerpo de tabla 0.5–0.75rem vertical. Cards tienen 1rem interior padding, 1.5rem margin entre items. No es apretado; es legible y escaneable.
- **Responsive:** Sidebar oculto en mobile (trigger en hamburger menu); tablas scrollean horizontalmente si son muy anchas; modals se adaptan al viewport. Breakpoints: desktop (≥992px), tablet (576–991px), mobile (<576px).

## Elevation & Depth

El sistema **no usa sombras volumétricas** excepto en casos contados. En su lugar, confía en **tonal layering** y **sutiles sombras ambientes**.

- **Ambient shadows** (sombras suaves para profundidad visual): `box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04)` (card en reposo). Da profundidad sin dramatismo.
- **Hover elevation** (card-link): `0 10px 24px rgba(15, 23, 42, 0.08)` + `transform: translateY(-2px)`. Cards se sienten "levantables".
- **FAB shadow** (botón flotante): `0 16px 30px rgba(11, 113, 185, 0.26)`. Más audaz porque es un elemento focal.
- **Dark mode remapeo:** Las sombras oscuras usan `rgba(0, 0, 0, 0.28)` a `rgba(0, 0, 0, 0.45)` en lugar de neutral, reflejando materiales más profundos.

**Named Rule: The Layering Rule.** Las superficies en reposo se separan por **color de fondo** (surface > surface-2) y **sombra ambiental leve**, no por sombra volumétrica. El hover/activo añade sombra más fuerte e ítem se desplaza ligeramente (2px arriba). Esto es "lifting without drama".

## Shapes

Todas las esquinas usan `border-radius` CSS, no clipping. Paleta de radio:
- **Small (0.375rem):** Inputs, pequeños chips, focus rings.
- **Medium (0.5rem):** Botones, cards, la mayoría de contenedores.
- **Large (0.75rem):** Stat cards, modals, contenedores más grandes.
- **Pill (9999px):** Avatars, badges, botones icon circulares.

No hay bordes agudos (0px radius) en la interfaz principal. Todos los contactos son curvas suaves, transmitiendo accesibilidad y modernidad.

## Components

### Buttons

**Character:** Firmes y responsivos.

- **Primary button** (#0B71B9, 0.5rem padding, radius 0.5rem): Estado base es el color medium-blue. Hover añade transform `translateY(-1px)` y sombra `0 6px 14px rgba(11, 113, 185, 0.28)`. Font-weight es medium (500). Transición 150ms cubic-bezier(0.4, 0, 0.2, 1).
- **Ghost primary** (texto en azul, fondo transparente): Hover aplica `rgba(11, 113, 185, 0.12)` de fondo. Active: `rgba(11, 113, 185, 0.18)`. Mismo radio y transición.
- **Icon button** (2.25rem × 2.25rem, centro flex): Sin padding, solo display flex center. Útil para acciones como toggling, agregar, editar.

### Cards

**Character:** Contenedores lijeros que cobran peso al interactuar.

- **Reposo:** Fondo blanco (o surface en dark), radius 0.5rem, sombra ambiental 0 2px 6px rgba(15,23,42,0.04), sin borde.
- **Card-link (hoverable):** Adquiere cursor pointer, transición de sombra y transform. Hover: sombra 0 10px 24px rgba(15,23,42,0.08), transform translateY(-2px).
- **Stat card:** Card especial con icono, valor (2rem, bold), label (uppercase, muted, 0.75rem). Icono dentro de un fondo leve-primary (rgba de primary 0.08 a 0.12).
- **Card header:** Sin fondo distintivo (transparent), borde inferior en border-color suave.

### Inputs / Fields

**Character:** Accesibles, enfocados en legibilidad.

- **Style:** Border 1px solid #e2e8f0, radius 0.5rem, padding 0.75rem 1rem, background white (o surface en dark). Placeholder color muted.
- **Focus:** Border cambia a primary (#0B71B9), añade `box-shadow: 0 0 0 0.2rem rgba(11, 113, 185, 0.18)`. El glow es suave, no invasivo.
- **Error state:** Border en danger (#ef4444), background leve danger si es necesario. Label sobre el input cambia a danger al focusarse.
- **Disabled:** Opacidad 0.5, cursor not-allowed, sin cambios de hover.

### Navigation

- **Sidebar (navbar-vertical):** Fondo #002347 (navy-900, más profundo que navy-800), texto #C7DDEE (light cyan, 55%+). Nav-links usan radius 0.5rem, padding 0.5rem 0.75rem, display flex gap 0.625rem. Hover: fondo rgba(255,255,255,0.06), texto white. Active: fondo rgba(0,174,239,0.20), inset border-left 3px primary, font-weight semibold.
- **Mini variant:** Ancho 72px, hide text labels, nav-links center justificado. Triggered by `.page[data-sidebar="mini"]`.
- **Mobile:** Sidebar fixed off-canvas izquierda, ancho 260px, overlay oscuro detrás. Animación translateX, ẓ-index 1045.
- **Topbar (header):** Fondo white (o surface en dark), border-bottom 1px suave, sombra suave. Nav-links heredan body color, hover primary. Logo brand en topbar usa primary.

### Tables

- **Header:** Background surface-2, text uppercase muted, font-weight semibold, 0.75rem font-size, 0.05em letter-spacing. Padding top/bottom 0.75rem.
- **Body rows:** Striped optional (rgba primary 0.035 cada otra fila), hover (rgba primary 0.06). Bordes 1px en border-color suave.
- **Cell padding:** Estándar 0.5rem left/right, 0.75rem top/bottom. Verticalmente centered por defecto.
- **Fixed layout:** Boletin table usa `table-layout: fixed` para columnas predefinidas.

### Status Badges

- **Inline badge:** Radius pill, font-weight semibold, letter-spacing 0.02em, padding 0.25rem 0.75rem. Color y fondo por estado (pending, approved, rejected, error, justified). Usa variables predefinidas `--rem-status-*-bg` y `--rem-status-*-fg`.
- **Soft variant:** Solo fondo, texto heredado o muted. Útil para badges menos críticos.

## Do's and Don'ts

### Do:

- **Do** respetar la paleta DEIS: navy y azul solo para texto/acción sobre fondo claro; cian y celeste para relleno.
- **Do** usar transiciones 150ms (fast) para hover/focus, 250ms (base) para state changes más grandes.
- **Do** mantener espaciado en múltiplos de 0.25rem (xs) o 0.5rem (sm), nunca valores arbitrarios.
- **Do** aplicar focus ring 2px solid primary, offset 2px, en todos los elementos focusables.
- **Do** remapear colores automáticamente en dark mode (`[data-bs-theme="dark"]`). Navy → #4FB3E8, azul → #76C7ED.
- **Do** usar `prefers-reduced-motion` para respetar preferencia de accesibilidad (animation-duration 0.001ms en modo reduced).

### Don't:

- **Don't** introducir nuevas fuentes, frameworks CSS (Tailwind, Pico, etc.), o colores fuera de DEIS.
- **Don't** usar sombras volumétricas (>0.3 blur) en reposo. Reserve sombras fuertes para hover/activo solamente.
- **Don't** incluir `style="..."` inline en nuevas vistas. Todo en CSS; usa variables.
- **Don't** crear estados de color que no mapeen a `config/constants.php` (estados pendiente/aprobado/rechazado/error/justificado).
- **Don't** forzar colores claros (blanco, cyan) como texto sobre fondos oscuros. Remapea a colores más claros (p.ej. navy → #4FB3E8 en dark mode).
- **Don't** cambiar radio de border-radius sin justificación. Usa escalas existentes (sm/md/lg/pill).
