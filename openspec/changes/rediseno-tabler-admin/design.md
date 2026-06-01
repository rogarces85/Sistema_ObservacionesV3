## Context

The app currently loads Tabler v1.4.0 (CSS + JS via CDN) in authenticated views but retains a 1922-line legacy stylesheet (`styles.css`), custom toast notifications, Chart.js charts, emoji icons, and a fully custom login page. The result is a hybrid that neither fully benefits from Tabler nor is cleanly custom. The redesign will make Tabler the single source of truth for all UI.

## Goals / Non-Goals

**Goals:**
- All 10 views + login rendered with Tabler components exclusively
- Remove `styles.css` entirely; only `tabler-override.css` remains for brand colors
- Replace custom toast system with Bootstrap Toast (`notifications.js` deleted)
- Replace Chart.js with ApexCharts using Tabler color tokens
- Replace all emoji icons with Tabler Icons SVG
- Unify page header structure using `.page-header` component
- Standardize loading spinners, empty states, modals, tables, forms, badges, alerts

**Non-Goals:**
- No backend changes (models, API endpoints, database remain identical)
- No behavioral/functional changes to business logic
- No PHP framework migration
- No responsive redesign beyond what Tabler already provides

## Decisions

### Decision 1: CDN delivery (no build step)
- **Choice**: Keep CDN delivery for Tabler CSS/JS, Tabler Icons, and ApexCharts
- **Rationale**: The app has no build pipeline (npm/webpack/vite). Adding one would be scope creep. CDN works reliably for this intranet app.
- **Alternatives considered**: npm + vite build — rejected as unnecessary complexity for a PHP app with no existing toolchain.

### Decision 2: ApexCharts over Chart.js
- **Choice**: Migrate to ApexCharts as documented by Tabler
- **Rationale**: Tabler's chart docs use ApexCharts exclusively. Chart configurations can reference Tabler CSS variables (`var(--tblr-primary)` etc.) for theme consistency. ApexCharts has better built-in features (responsive, export, annotations) that reduce custom JS.
- **Alternatives considered**: Keep Chart.js — simpler but misses theme integration; would remain "the odd one out" in a Tabler app.

### Decision 3: Progressive view migration
- **Choice**: Rewrite views one at a time, starting from the outer shell (layout → login → dashboard → remaining views)
- **Rationale**: Each view is independent PHP. A phased approach lets us deliver value incrementally and catch issues early. The shell (header/footer/sidebar) affects all views, so it goes first.
- **Alternatives considered**: Big-bang rewrite of all files — riskier, harder to debug.

### Decision 4: Build a global Icon helper function
- **Choice**: Create a PHP helper function `tablerIcon(string $name, string $size = 'md'): string` in a shared include, returning the SVG markup for the requested Tabler icon
- **Rationale**: Avoids repeating inline SVGs. The existing sidebar already has a `tablerIcon()` function — we extend this pattern to all views.
- **Alternatives considered**: Tabler Icons webfont — simpler but loses SVG scalability and multi-color support; inline SVGs everywhere — maintainability nightmare.

### Decision 5: JavaScript refactoring strategy
- **Choice**: Rewrite `charts.js` completely for ApexCharts; rewrite `notifications.js` as a thin Bootstrap Toast wrapper; keep `app.js` with minor cleanup
- **Rationale**: Charts JS is 583 lines deeply coupled to Chart.js API — needs full rewrite. Notifications is 113 lines of custom toast — a 30-line Bootstrap wrapper replaces it. App.js (192 lines) mostly handles app logic (fetch, auth, CSRF) — only the `showMessage` function and loading overlay change.

## Migration Plan

```
Phase 1 — Layout Shell (header, footer, sidebar)
  → Rewrite includes/header.php (Tabler page layout, page-header)
  → Rewrite includes/sidebar.php (Tabler dark sidebar, role filter)
  → Rewrite includes/footer.php (Tabler footer, CDN order)
  → Tabler Icons CDN added

Phase 2 — Login Page
  → Full Tabler login: .page-center → .container-tight → .card-md
  → Tabler form classes, Tabler Icons
  → Remove all inline styles and legacy CSS classes

Phase 3 — Dashboard
  → Stats cards with Tabler card-status
  → Page header with .page-header
  → Quick actions with Tabler list-group
  → Empty states with .empty
  → ApexCharts replacing Chart.js

Phase 4 — Remaining Views (observaciones, supervision, reportes, etc.)
  → Each view: Tabler form classes, Tabler tables, Tabler modals, Tabler Icons
  → Remove redundant wrapper divs
  → Replace inline styles with Tabler utilities

Phase 5 — ApexCharts Deep Integration
  → Rewrite charts.js
  → Configure data labels, tooltips, gradient effects in ApexCharts
  → Remove Chart.js CDN

Phase 6 — CSS Cleanup
  → Remove styles.css
  → Keep only tabler-override.css
  → Verify nothing breaks
```

## Risks / Trade-offs

- **[Risk] ApexCharts license**: ApexCharts is free but has a different license (MIT) than Chart.js. No commercial restriction for this internal tool, but worth noting.
- **[Risk] Chart feature parity**: ApexCharts has different APIs for gradients, data labels, and tooltips. The current custom chart effects (gradient plugin, hover dim, emoji tooltips) need re-implementation. → Mitigation: ApexCharts has built-in gradient fill, formatter functions for tooltips, and data labels — no plugin needed.
- **[Risk] Stylesheet removal breaks dynamic content**: Some JS-generated HTML (e.g., informe table rows in dashboard.js, dynamic rows in supervision) may reference legacy CSS classes. → Mitigation: Audit JS-generated HTML templates before removing styles.css.
- **[Trade-off] CDN dependency**: Requires internet access (or local cache). Current deployment is XAMPP intranet — acceptable.
- **[Risk] View-by-view inconsistency during migration**: During Phase 3-4, some views will be Tabler-clean and others legacy-hybrid. → Mitigation: Order migration by page traffic (dashboard → observaciones → reportes → supervision → rest), complete each fully before starting next.

## Open Questions

- Should we add Tabler Icons webfont as a fallback, or strictly use SVG helper? SVG is more maintainable but requires the helper function.
- ApexCharts heatmap — is this needed? The current app has only bar charts. (No — skip heatmap config.)
