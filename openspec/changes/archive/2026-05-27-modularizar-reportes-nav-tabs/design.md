## Context

La vista `views/reportes.php` actual muestra 5 gráficos en una sola página larga: dos en una grilla de 2 columnas (Total Errores, Plazos, Validador, Serie) y uno de ancho completo (Hojas). Cada gráfico tiene su propio canvas, tabla y contenedor. La página se vuelve extensa y difícil de navegar.

## Goals / Non-Goals

**Goals:**
- Modularizar la vista en 5 pestañas (nav-tabs).
- Mostrar solo el gráfico de la pestaña activa.
- Mantener filtros globales siempre visibles.
- Cargar datos del gráfico al activar la pestaña (lazy loading).

**Non-Goals:**
- No modificar filtros ni backend.
- No agregar nuevos reportes.

## Decisions

### 1. Pestañas con HTML/CSS puro (sin librerías)
**Decision**: Implementar nav-tabs con HTML semántico (`<nav>`, `<button>`, `[role="tablist"]`) y CSS para estados activo/inactivo.
**Rationale**: Evita dependencias externas. El proyecto ya usa CSS custom/Tailwind-like.

### 2. Lazy loading por pestaña
**Decision**: Al hacer clic en una pestaña, se carga el gráfico correspondiente vía AJAX si no está cargado aún. Se mantiene un estado (`tabDataLoaded`) para evitar recargas innecesarias.
**Rationale**: Reduce carga inicial y mejora performance.

### 3. Filtros globales persistentes
**Decision**: Los selects de filtros (Año, Mes, Comuna, Establecimiento) permanecen arriba de las pestañas y al aplicarlos se recarga el gráfico activo.
**Rationale**: Coherencia con el diseño actual y facilidad de uso.

### 4. Estado de pestaña activa en URL (hash)
**Decision**: Usar `location.hash` para recordar la pestaña activa al recargar la página.
**Rationale**: Mejora UX al compartir URLs o refrescar.

## Risks / Trade-offs

- **[Riesgo] Usuarios acostumbrados a ver todo junto** → **Mitigación**: Las pestañas son intuitivas y el contenido está organizado por categoría.
- **[Trade-off] Lazy loading vs carga inicial** → Se usa lazy loading para mejorar tiempo de carga inicial; si el usuario cambia rápido de pestañas, puede haber un breve delay.

## Migration Plan

1. Crear estructura HTML de nav-tabs en `views/reportes.php`.
2. Mover cada gráfico a su contenedor de pestaña.
3. Implementar lógica JS para cambio de pestañas y lazy loading.
4. Ajustar estilos CSS para tabs responsive.
5. Probar navegación entre pestañas y aplicación de filtros.
