## Decisions

### Bug 1: Icono `menu` duplicado
- **Problema**: `includes/icons.php` define `menu` en línea 48 y línea 57 (idéntico)
- **Solución**: Eliminar la segunda definición (línea 57), mantener la primera

### Testing: Verificación funcional integral
Dado que es una app PHP/browser, el testing es **manual**. Cada tarea genera una checklist verificable por el desarrollador en el navegador.

## Plan de Verificación

### Login
1. Abrir `index.php` → verificar página de login con estilos Tabler
2. Probar login con credenciales válidas
3. Probar login con credenciales inválidas → verificar mensaje de error
4. Verificar que no hay emojis, solo iconos SVG

### Vistas autenticadas (dashboard, observaciones, supervisión, reportes, usuarios, establecimientos, asignaciones, papelera)
1. Navegar a cada vista → verificar que renderiza sin errores visuales
2. Verificar que sidebar muestra los ítems correctos según rol
3. Verificar que header tiene: search, selector de año, avatar con dropdown
4. Verificar que `.page-header` está presente en cada vista
5. Verificar que todos los iconos son SVG (no emojis)
6. Verificar que las tablas usan `.table.table-vcenter.card-table`
7. Verificar que los badges de estado usan `.badge.bg-{color}`
8. Verificar que los modales usan estructura Bootstrap (`.modal-dialog` → `.modal-content` → `.modal-header/.modal-body/.modal-footer`)
9. Verificar que el dropdown de usuario abre/cierra correctamente
10. Probar logout desde el dropdown

### Charts (dashboard + reportes)
1. Dashboard: verificar 3 gráficos cargan con datos reales (donut estados, barras tipos error, línea tendencia)
2. Reportes: verificar gráficos en cada tab
3. Verificar tooltips funcionan al hacer hover
4. Verificar data labels visibles en barras
5. Verificar exportación PNG funciona

### Toasts y Notificaciones
1. Crear una observación → verificar toast de éxito aparece
2. Intentar acción inválida → verificar toast de error aparece
3. Verificar animación de entrada/salida
4. Verificar botón de cerrar funciona

### Loading States
1. Filtrar observaciones → verificar spinner aparece durante carga
2. Verificar spinner desaparece al completar

### Responsive
1. Reducir viewport a 375px (mobile)
2. Verificar sidebar se colapsa correctamente
3. Verificar tablas son scrollables horizontalmente
4. Verificar que no hay overflow horizontal

### Console
1. Abrir DevTools (F12) → Console tab
2. Navegar por 3-4 vistas
3. Verificar que no hay errores en rojo
4. Verificar que no hay warnings críticos