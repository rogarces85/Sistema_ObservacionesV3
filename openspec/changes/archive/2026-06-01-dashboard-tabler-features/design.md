## Context

El dashboard actual (`views/dashboard.php`) ya está construido con componentes Tabler básicos: cards, badges, tablas, ApexCharts y el layout shell. Sin embargo, carece de componentes avanzados que Tabler ofrece nativamente y que mejorarían significativamente la UX: timeline, skeleton screens, sparklines, progress steps, kanban, auto-refresh, dropdown filters y card tabs. Estas funciones son aditivas y no requieren cambios en la arquitectura backend ni en el modelo de datos existente.

## Goals / Non-Goals

**Goals:**
- Integrar 8 componentes avanzados de Tabler en el dashboard existente
- Mejorar la percepción de velocidad con skeleton loading
- Proporcionar contexto visual adicional con sparklines y timeline
- Permitir gestión visual de observaciones con kanban board
- Automatizar la actualización de datos con auto-refresh
- Reducir clicks con filtros inline y tabs dentro de cards

**Non-Goals:**
- No se modifica el backend (models, API endpoints, database)
- No se cambian las capacidades existentes del dashboard
- No se agregan nuevas dependencias externas (todo es nativo de Tabler/Bootstrap)
- No se modifica la arquitectura MVC actual

## Decisions

### Decisión 1: Todo nativo de Tabler
- **Elección**: Usar únicamente componentes CSS/JS nativos de Tabler v1.4.0 y Bootstrap 5
- **Razonamiento**: El sistema ya carga Tabler vía CDN. No se requieren librerías adicionales como Sortable.js para kanban (usar native HTML5 drag & drop)
- **Alternativas consideradas**: Sortable.js para kanban — rechazado para evitar dependencia extra; native DnD es suficiente para este caso

### Decisión 2: Un solo archivo JS para todas las funciones
- **Elección**: Crear `assets/js/dashboard-features.js` con módulos para cada función
- **Razonamiento**: Mantiene el dashboard.php limpio y permite lazy-loading de funciones según necesidad
- **Alternativas consideradas**: Un archivo por función — rechazado para reducir requests HTTP

### Decisión 3: Kanban como vista alternativa, no reemplazo
- **Elección**: El kanban board se mostrará como una card adicional en el dashboard, no como reemplazo de la tabla
- **Razonamiento**: La tabla sigue siendo útil para búsquedas y filtrado; el kanban complementa la visualización por estado

### Decisión 4: Auto-refresh con debounce y toggle
- **Elección**: Intervalo de 2 minutos con toggle en el header del dashboard para activar/desactivar
- **Razonamiento**: Balance entre frescura de datos y carga del servidor. Toggle permite al usuario controlar el comportamiento

### Decisión 5: Sparklines con ApexCharts existente
- **Elección**: Reutilizar ApexCharts ya cargado para renderizar sparklines en lugar de agregar una librería específica
- **Razonamiento**: ApexCharts puede renderizar gráficos muy pequeños (sparkline mode). Evita carga de librería adicional

## Risks / Trade-offs

- **[Risk]** Kanban drag & drop nativo puede no funcionar perfectamente en todos los navegadores móviles → Mitigation: proporcionar botones de cambio de estado como alternativa
- **[Risk]** Auto-refresh puede generar carga innecesaria en el servidor si el usuario deja la pestaña abierta → Mitigation: usar Page Visibility API para pausar refresh cuando la pestaña está oculta
- **[Trade-off]** Sparklines en stat cards requieren datos históricos adicionales → Se puede usar datos de los últimos 7 días desde el modelo existente o simular con tendencia mensual
- **[Risk]** Timeline requiere datos de auditoría que podrían no existir → Mitigation: mostrar solo eventos disponibles (creación, cambios de estado recientes) y degradar gracefulmente

## Migration Plan

Fase 1: Skeleton Loading + Auto-refresh (fundación)
Fase 2: Card Tabs + Dropdown Filters (organización)
Fase 3: Timeline + Progress Steps (visualización flujo)
Fase 4: Sparklines + Kanban Board (enriquecimiento visual)

## Open Questions

- ¿Los datos históricos de observaciones por día existen para sparklines, o se necesita agregar un campo `fecha_creacion` más granular?
- ¿El kanban debe permitir cambiar estado directamente o solo es visual?
- ¿Se requiere persistir las preferencias de auto-refresh del usuario (localStorage vs sesión)?
