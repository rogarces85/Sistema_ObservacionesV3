## Why

The app already loads Tabler v1.4.0 but uses it superficially — mixed with 1922 lines of legacy CSS, Chart.js, custom toast system, emoji icons, inline styles, and a completely custom login page. This creates visual inconsistency, maintenance overhead, and prevents leveraging Tabler's built-in components. A full redesign will unify the UI, reduce custom code, and make future development faster.

## What Changes

- **Layout restructuring**: Replace custom wrappers/login with Tabler admin layout (`.page` → `.page-sidebar` → `.page-wrapper`), consistent page headers, proper sidebar/navbar
- **Login page redesign**: Full Tabler login using `.page-center` + `.container-tight` + `.card-md`
- **Toast migration**: Replace custom `notifications.js` with Bootstrap Toast component (Tabler native)
- **Icon migration**: Replace all emoji icons with Tabler Icons SVG set
- **Loading states**: Replace custom overlay with Tabler `.spinner-border` + `.animated-dots`
- **Empty states**: Replace custom markup with Tabler `.empty` component
- **Charts**: Migrate from Chart.js to ApexCharts (Tabler's recommended chart library)
- **CSS cleanup**: Remove `styles.css` (1922 lines) after migration, keep only `tabler-override.css`
- **Inline styles**: Replace scattered `style="..."` attributes with Tabler utility classes
- **Form standardization**: Apply Tabler form classes consistently (`.form-control`, `.form-select`, `.form-label`)

## Capabilities

### New Capabilities
- `tabler-layout`: Complete admin layout with Tabler — login, sidebar, navbar, page headers, wrapper structure
- `tabler-components`: UI component migration to Tabler — toasts, modals, empty states, spinners, badges, alerts, tables, pagination
- `tabler-icons`: Systematic replacement of emoji icons with Tabler Icons SVG across all views
- `apex-charts`: Chart.js to ApexCharts migration — chart components, configurations, and rendering

### Modified Capabilities
<!-- No spec-level behavior changes — all existing business logic remains identical. Only the presentation layer changes. -->

## Impact

- **Views**: All 10 PHP view files + login + header/footer/sidebar templates
- **JS**: `notifications.js` removed, `charts.js` rewritten (583 lines → ApexCharts), `app.js` simplified
- **CSS**: `styles.css` removed (1922 lines gone), only `tabler-override.css` remains (~30 lines)
- **Dependencies**: Add ApexCharts CDN, replace Chart.js CDN; add Tabler Icons CDN
- **No backend changes**: All PHP models, API endpoints, and database remain untouched
