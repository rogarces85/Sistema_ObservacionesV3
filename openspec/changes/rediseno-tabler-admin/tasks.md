## 1. Layout Shell (header, sidebar, footer)

- [x] 1.1 Add Tabler Icons CDN stylesheet to `includes/header.php`
- [x] 1.2 Rewrite `includes/header.php`: Tabler DOCTYPE, head with Tabler meta, `.page` container, `.page-wrapper` wrapper, ensure container-xl is opened once
- [x] 1.3 Rewrite `includes/sidebar.php`: clean `.navbar-vertical` with `data-bs-theme="dark"`, unified `tablerIcon()` helper, role-based active state, nav-subtitle groups
- [x] 1.4 Move/centralize the `tablerIcon()` PHP helper from sidebar into `includes/icons.php` so all views can use it
- [x] 1.5 Rewrite `includes/footer.php`: Tabler `.footer`, remove legacy loading overlay, reorder CDN scripts (Tabler JS → ApexCharts → app.js), close `.page-wrapper` + `.page`
- [x] 1.6 Add Tabler loading overlay with `.spinner-border` + `.animated-dots` to footer
- [x] 1.7 Remove redundant `.page-wrapper > .page-body > .container-xl` wrappers from all views that have them (reportes, usuarios, establecimientos, perfil, eliminadas, asignaciones)

## 2. Login Page Redesign

- [x] 2.1 Rewrite `views/login.php` with Tabler layout: `.page.page-center` → `.container-tight` → `.card.card-md`
- [x] 2.2 Replace all custom inline styles with Tabler form classes (`.form-control`, `.form-select`, `.form-label`)
- [x] 2.3 Replace logo/header section with Tabler `.card-body` structure
- [x] 2.4 Add Tabler icons for user/password fields via `.input-icon` + `.input-icon-addon`
- [x] 2.5 Remove all inline JS handlers (onfocus/onblur) — use `.form-control` CSS instead
- [x] 2.6 Remove leftover legacy CSS classes (`.flex`, `.items-center`, `.text-slate-*`, `.bg-rose-*`, etc.)
- [x] 2.7 Style login button with `.btn.btn-primary.w-100` and remove gradient inline styles
- [x] 2.8 Replace test credentials section with Tabler styling

## 3. Dashboard Redesign

- [x] 3.1 Add `.page-header` with `.page-pretitle` + `.page-title` replacing custom `<h2>` + `<p>`
- [x] 3.2 Replace stat cards inline gradients with Tabler cards + `.card-status-top.bg-{color}`
- [x] 3.3 Replace card icon containers (div with p-3 rounded-3 gradient) with `.card-status-start` or simple icon helper
- [x] 3.4 Replace emoji icons in stat cards with `tablerIcon()` SVG calls
- [x] 3.5 Replace chart card headers text (📈, 🔍, ⚡, 📅) with `tablerIcon()` + clean title
- [x] 3.6 Replace Acciones Rápidas list items inline styles with Tabler `.list-group-item` + `.list-group-item-action`
- [x] 3.7 Replace Acciones Rápidas emojis with `tablerIcon()`
- [x] 3.8 Replace empty state in recent observations with Tabler `.empty` component
- [x] 3.9 Replace emoji icon in empty state with `tablerIcon()`
- [x] 3.10 Replace "Últimas Observaciones" table's inline `style="color:#..."` with Tabler text utilities (`.text-body`, `.text-secondary`)
- [x] 3.11 Migrate informe modal: ensure `.modal-dialog.modal-sm.modal-dialog-centered` uses proper Bootstrap modal structure
- [x] 3.12 Replace modal emoji icons with `tablerIcon()`
- [x] 3.13 Replace inline style `style="display:none"` with `.d-none` class in informe resultados
- [x] 3.14 Replace pagination buttons inline styles with Tabler `.btn.btn-outline-secondary.btn-sm`

## 4. Observaciones View Redesign

- [x] 4.1 Add `.page-header` for the view title
- [x] 4.2 Replace filter forms inline styles with Tabler form classes
- [x] 4.3 Replace table with `.table.table-vcenter.card-table` ensuring proper Tabler classes
- [x] 4.4 Replace status badges with `.badge.bg-{color}.text-{color}-fg`
- [x] 4.5 Replace action buttons emojis with `tablerIcon()`
- [x] 4.6 Replace empty state with Tabler `.empty` component
- [x] 4.7 Replace modals with proper Bootstrap modal structure, remove emoji from titles
- [x] 4.8 Replace inline style color references with `.text-body`, `.text-secondary`, `.fw-semibold`
- [x] 4.9 Replace import modal drag-and-drop zone with Tabler `.dropzone` if applicable

## 5. Supervisión View Redesign

- [x] 5.1 Add `.page-header` for the view title
- [x] 5.2 Replace filter/search inline styles with Tabler form classes
- [x] 5.3 Replace table classes with `.table.table-vcenter.card-table`
- [x] 5.4 Replace status badges with `.badge.bg-{color}.text-{color}-fg`
- [x] 5.5 Replace any custom CSS modals with Bootstrap `.modal` component
- [x] 5.6 Replace emoji icons with `tablerIcon()`
- [x] 5.7 Replace empty state with Tabler `.empty`
- [x] 5.8 Replace inline color/styles with Tabler utility classes

## 6. Reportes View Redesign

- [x] 6.1 Add `.page-header` for the view title
- [x] 6.2 Ensure tabs use Tabler `.nav-tabs.card-header-tabs` with `data-bs-toggle="tabs"`
- [x] 6.3 Replace chart canvases rendering with proper ApexCharts containers (Phase 2)
- [x] 6.4 Replace form filters with Tabler form classes
- [x] 6.5 Replace emoji icons in tab titles with `tablerIcon()`
- [x] 6.6 Replace tables with `.table.table-vcenter.card-table`
- [x] 6.7 Replace inline styles with Tabler utility classes (chart container heights, Phase 2)

## 7. Remaining Views Redesign (usuarios, perfil, establecimientos, asignaciones, eliminadas)

- [x] 7.1 `views/usuarios.php`: Add `.page-header`, Tabler table, Tabler modal, Tabler form classes, replace emojis with icons, remove inline styles
- [x] 7.2 `views/perfil.php`: Add `.page-header`, Tabler form classes, replace inline styles
- [x] 7.3 `views/establecimientos.php`: Add `.page-header`, Tabler table, Tabler modal, Tabler form, replace emojis, remove inline styles
- [x] 7.4 `views/asignaciones.php`: Add `.page-header`, Tabler table, Tabler modals, Tabler form classes, replace emojis, remove inline styles
- [x] 7.5 `views/eliminadas.php`: Add `.page-header`, Tabler table, Tabler modal, replace emojis, remove inline styles

## 8. JavaScript Refactoring — Toast System

- [x] 8.1 Create `assets/js/toasts.js`: Bootstrap Toast wrapper with `showSuccess()`, `showError()`, `showWarning()`, `showInfo()` — thin wrapper over `bootstrap.Toast`
- [x] 8.2 Add toast container HTML to `includes/footer.php`: `<div class="toast-container position-fixed bottom-0 end-0 p-3">`
- [x] 8.3 Remove `assets/js/notifications.js` from header
- [x] 8.4 Update `assets/js/app.js`: replace `showMessage()` to delegate to new toast system, remove loading overlay references
- [ ] 8.5 Test all views: success/error toasts still appear after async operations

## 9. JavaScript Refactoring — Loading States

- [x] 9.1 Update `assets/js/app.js`: replace `showLoading()`/`hideLoading()` to toggle `.spinner-border` visibility instead of custom overlay
- [x] 9.2 Add loading spinner HTML to footer: `<div id="loading-spinner" class="spinner-border text-primary d-none" role="status"><span class="visually-hidden">Cargando<span class="animated-dots"></span></span></div>`
- [x] 9.3 Remove old loading overlay CSS from `tabler-override.css` (if any) — no loading overlay CSS existed, added `.hidden` utility class
- [ ] 9.4 Test all async operations (fetchAPI calls) show/hide spinner correctly

## 10. ApexCharts Migration — Setup & Core

- [x] 10.1 Replace Chart.js CDN with ApexCharts CDN in `includes/footer.php`
- [x] 10.2 Create `assets/js/charts-apex.js` (new file) with ApexCharts initialization
- [x] 10.3 Implement ApexCharts bar chart utility function: `createBarHorizontal(canvasId, config)` with Tabler CSS variable colors
- [x] 10.4 Implement ApexCharts bar chart utility function: `createBarVertical(canvasId, config)` with Tabler CSS variable colors
- [x] 10.5 Implement ApexCharts gradient fill configuration using Tabler color tokens (`var(--tblr-primary)`, `var(--tblr-success)`, etc.)
- [x] 10.6 Implement ApexCharts data labels formatter with smart display (hide if bar too small)
- [x] 10.7 Implement ApexCharts tooltip formatter with emoji indicators and progress bar styling
- [x] 10.8 Implement chart export-to-PNG button injection — built-in via ApexCharts toolbar

## 11. ApexCharts Migration — Dashboard Charts

- [x] 11.1 Rewrite `createEstadoChart()` for ApexCharts: horizontal bar, status colors, data labels
- [x] 11.2 Rewrite `createTipoErrorChart()` for ApexCharts: horizontal bar, top 10, gradient colors
- [x] 11.3 Rewrite `createTendenciaChart()` for ApexCharts: vertical bar, monthly x-axis, blue gradient
- [x] 11.4 Update `initializeCharts()` in dashboard to call new ApexCharts functions
- [ ] 11.5 Test all 3 dashboard charts render correctly with real data

## 12. ApexCharts Migration — Report Charts

- [x] 12.1 Rewrite `createBarHorizontal()` report utility for ApexCharts
- [x] 12.2 Rewrite `createBarVertical()` report utility for ApexCharts
- [x] 12.3 Implement `renderStackedBarChart()` for ApexCharts (if used in reports)
- [ ] 12.4 Test all 5 report charts render correctly in each tab
- [ ] 12.5 Verify chart export-to-PNG works for all report charts

## 13. ApexCharts Migration — Cleanup

- [x] 13.1 Remove `assets/js/charts.js` old file
- [x] 13.2 Remove `chartjs-plugin-datalabels` CDN from footer
- [x] 13.3 Remove Chart.js CDN from footer (verify no remaining references)
- [ ] 13.4 Test all charts across dashboard and reports for regression
- [ ] 13.5 Verify chart tooltips work correctly
- [ ] 13.6 Verify data labels display correctly on all bar charts

## 14. Icon Migration — All Views

- [x] 14.1 Create `includes/icons.php` with comprehensive `tablerIcon()` function (home, file-text, eye, chart-bar, users, package, building, trash, user, plus, edit, check, alert-triangle, clock, download, search, filter, upload, x, arrow-left, arrow-right, settings, info-circle, etc.)
- [x] 14.2 Replace emoji in sidebar navigation → `tablerIcon()`
- [x] 14.3 Replace all emoji in dashboard view
- [x] 14.4 Replace all emoji in observaciones view
- [x] 14.5 Replace all emoji in supervisión view
- [x] 14.6 Replace all emoji in reportes view (none found — already clean)
- [x] 14.7 Replace all emoji in usuarios view (none found — already clean)
- [x] 14.8 Replace all emoji in perfil view (none found — already clean)
- [x] 14.9 Replace all emoji in establecimientos view (none found — already clean)
- [x] 14.10 Replace all emoji in asignaciones view
- [x] 14.11 Replace all emoji in eliminadas view
- [x] 14.12 Replace emoji in login view

## 15. CSS Cleanup

- [x] 15.1 Audit `assets/css/styles.css`: identify classes still referenced by JS-generated HTML
- [x] 15.2 Create a compatibility CSS snippet for JS-generated classes (added to tabler-override.css)
- [x] 15.3 Delete `assets/css/styles.css` (1922 lines removed)
- [x] 15.4 Review `tabler-override.css`: kept brand overrides, added compatibility utilities (hidden, colors, spacing, layout, flex)

## 16. Final Verification

- [ ] 16.1 Verify login page renders correctly with Tabler styling
- [ ] 16.2 Verify all authenticated views render without visual breakage
- [ ] 16.3 Verify all charts render with correct data (dashboard + reports) — ApexCharts
- [ ] 16.4 Verify all modals open/close correctly across all views
- [ ] 16.5 Verify all toasts appear correctly (success, error, warning, info)
- [ ] 16.6 Verify loading spinner appears during AJAX calls
- [ ] 16.7 Verify all Tabler Icons render correctly across all views
- [ ] 16.8 Verify responsive layout on mobile viewport
- [ ] 16.9 Verify sidebar navigation works correctly for both roles
- [ ] 16.10 Verify no console errors in browser developer tools
