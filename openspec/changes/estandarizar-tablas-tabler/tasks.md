## 1. Migrar observaciones.php (más cambios)

- [x] 1.1 Reemplazar `<table id="observationsTable">` por `<table class="table table-vcenter card-table table-hover" id="observationsTable">`
- [x] 1.2 Reemplazar `<div class="overflow-x-auto">` por `<div class="table-responsive">`
- [x] 1.3 Reemplazar clases Tailwind en celdas: `text-slate-800` → `fw-semibold`, `text-slate-400/500/600` → `text-secondary`, `font-bold` → `fw-semibold`, `text-xs` → `small`
- [x] 1.4 Reemplazar `class="text-right"` por `class="text-end"` en th de Acciones
- [x] 1.5 Estandarizar empty state a `text-center text-secondary py-4`
- [x] 1.6 Reemplazar botones de acción (👁️, ✏️) con iconos SVG Tabler `btn-ghost-secondary btn-icon`

## 2. Agregar table-hover a vistas con Tabler parcial

- [x] 2.1 Agregar `table-hover` a `views/usuarios.php` en `<table class="table table-vcenter card-table">`
- [x] 2.2 Agregar `table-hover` a `views/establecimientos.php` en `<table class="table table-vcenter card-table">`
- [x] 2.3 Agregar `table-hover` a las 5 tablas de `views/reportes.php`

## 3. Migrar tablas JS-generadas y resto de vistas

- [x] 3.1 Migrar tabla de `views/supervision.php`: agregar clases Tabler + wrapper responsive + `text-end` en acciones
- [x] 3.2 Migrar tabla de `views/eliminadas.php`: agregar `table-hover`
- [x] 3.3 Migrar tablas JS-generadas en `views/asignaciones.php`: actualizar templates JS con clases Tabler + `table-hover`

## 4. Verificación

- [x] 4.1 Verificar que todas las tablas tengan `table table-vcenter card-table table-hover` (12 tablas en 8 vistas confirmadas)
- [x] 4.2 Verificar que no queden `text-right` en tablas (solo quedan 2 en divs no-tabla, fuera de scope)
- [x] 4.3 Verificar que no queden clases `text-slate-*` en tablas de observaciones (limpiado)
- [x] 4.4 Verificar responsive en móvil (todas las tablas envueltas en `table-responsive`)
