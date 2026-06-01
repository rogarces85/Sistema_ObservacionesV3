## Context

El sistema actual usa Tabler (Bootstrap 5) como framework CSS para la mayoría de las vistas nuevas, pero las vistas legacy (`dashboard.php`, `observaciones.php`, `supervision.php`) usan clases CSS personalizadas de `styles.css`. Existe CSS responsivo parcial pero está desorganizado, con estilos legacy que a veces entran en conflicto con Tabler.

No hay meta viewport consistente, los modales no se adaptan en móviles, el sidebar no colapsa correctamente en todas las vistas, y las tablas no hacen scroll horizontal en pantallas pequeñas.

## Goals / Non-Goals

**Goals:**
- Sidebar colapsable con overlay en <768px
- Tablas con scroll horizontal responsivo en todas las vistas
- Modales con ancho adaptable (fullscreen en <576px)
- Formularios apilables en móviles
- Login responsivo (centrado, ancho adaptable)
- Header adaptativo (oculta búsqueda/año en móvil)
- Touch targets ≥44px en elementos interactivos
- Sin scroll horizontal en ninguna página
- Gráficos Chart.js redimensionables

**Non-Goals:**
- No se cambia el backend ni la API
- No se agregan librerías externas
- No se rediseña el layout completo
- No se añade PWA ni soporte offline
- No se cambia el sistema de navegación

## Decisions

### D1: Usar Tabler responsive classes como estándar
Tabler ya incluye clases responsivas (`.container-xl`, `.table-responsive`, `.row-cards`, `.modal-dialog-centered`, etc.). Se prefiere usar estas clases existentes antes que escribir CSS personalizado.
- Alternativa considerada: Escribir todo el CSS responsivo en `styles.css`. Rechazada porque duplicaría funcionalidad de Tabler y aumentaría el mantenimiento.
- Views legacy migrarán sus contenedores a clases Tabler donde sea posible.

### D2: Consolidar media queries en styles.css
Los media queries existentes en `styles.css` se reorganizarán por breakpoint y se eliminarán duplicados. Se usarán los breakpoints estándar de Tabler/Bootstrap:
- `<576px` (xs): Móviles
- `576px-767px` (sm): Móviles grandes
- `768px-991px` (md): Tablets
- `≥992px` (lg+): Escritorio

### D3: Sidebar toggle con Tabler nativo
Tabler ya tiene un sistema de sidebar colapsable con el class `navbar-vertical` y `collapse`. Se aprovechará este mecanismo existente en vez de implementar toggle manual.
- Alternativa considerada: Mantener el toggle manual actual en `app.js`. Rechazada porque es más frágil y no integra bien con Tabler.

### D4: Modales con clases Tabler responsivas
Se agregarán clases `.modal-dialog-centered` y `.modal-dialog-scrollable` a los modales existentes. Para móviles, se usará CSS adicional con media query para fullscreen.
- En las vistas legacy que usan modales manuales, se reemplazarán por modales Tabler.

### D5: Tablas con .table-responsive
Todas las tablas se envolverán en `<div class="table-responsive">` o se les agregará la clase `.table-responsive` directamente.

### D6: Login page con flexbox centrado
Se reemplazará el layout actual del login por un contenedor flexbox que centre vertical y horizontalmente el formulario, con `min-height: 100vh`.

## Risks / Trade-offs

- Views legacy (`dashboard`, `observaciones`, `supervision`) tienen clases CSS que pueden entrar en conflicto con Tabler → Migrar gradualmente, probar cada vista individualmente
- Modales con contenido complejo (charts, tablas anidadas) pueden no adaptarse bien en móvil → Limitar altura máxima y usar scroll interno
- Chart.js necesita `resize: true` y contenedor con ancho definido → Asegurar que los contenedores de gráficos tengan width: 100%
- Cambios en el layout pueden afectar la posición de elementos que dependen de medidas fijas (tooltips, dropdowns) → Probar elementos flotantes en cada breakpoint
