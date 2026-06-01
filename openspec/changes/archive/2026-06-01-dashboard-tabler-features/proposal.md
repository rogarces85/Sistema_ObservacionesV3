## Why

El dashboard actual del sistema de observaciones REM ya utiliza el framework Tabler CSS v1.4.0 con componentes básicos (cards, badges, tables, charts), pero no aprovecha componentes avanzados que podrían mejorar significativamente la experiencia de usuario: timeline visual, estados de carga profesionales, mini-gráficos inline, flujos de trabajo visuales, tableros kanban, actualización en tiempo real, filtros inline y organización por pestañas. Estos componentes son nativos de Tabler y se alinean perfectamente con la arquitectura visual ya establecida.

## What Changes

- **Timeline de actividad**: Reemplazar/complementar la tabla de "Últimas Observaciones" con un componente visual de timeline que muestre el flujo cronológico de acciones (creación, cambios de estado, aprobaciones).
- **Skeleton loading**: Agregar pantallas de carga animadas en stat cards, tablas y gráficos durante la carga inicial de datos.
- **Sparklines**: Integrar mini-gráficos de tendencia dentro de cada stat card para mostrar evolución de métricas en tiempo real.
- **Progress steps**: Visualizar el ciclo de vida de observaciones (Registrada → Revisión → Aprobada/Rechazada → Resuelta) con componente de pasos de Tabler.
- **Kanban board**: Implementar tablero visual por estado con cards arrastrables entre columnas (drag & drop) para gestión visual de observaciones.
- **Auto-refresh**: Actualización automática de datos cada 2 minutos vía AJAX sin recargar la página, con animaciones sutiles en los cambios.
- **Dropdown filters**: Filtros rápidos de año/mes/comuna dentro de los headers de las cards de gráficos.
- **Card tabs**: Pestañas dentro de cards para alternar entre vistas (ej: "Recientes" | "Pendientes" | "Con Problemas" en la tabla de observaciones).

## Capabilities

### New Capabilities
- `dashboard-timeline`: Timeline visual de actividad reciente con eventos cronológicos
- `dashboard-skeleton-loading`: Pantallas de carga animadas durante fetch de datos
- `dashboard-sparklines`: Mini-gráficos de tendencia inline en stat cards
- `dashboard-progress-steps`: Visualización del flujo de trabajo de observaciones
- `dashboard-kanban-board`: Tablero kanban con drag & drop por estado
- `dashboard-auto-refresh`: Actualización automática periódica de datos vía AJAX
- `dashboard-dropdown-filters`: Filtros inline dentro de headers de cards
- `dashboard-card-tabs`: Pestañas dentro de cards para múltiples vistas

### Modified Capabilities
- No se modifican capabilities existentes. Solo se agregan nuevos componentes visuales sobre el dashboard actual.

## Impact

- **Views**: `views/dashboard.php` (modificaciones significativas)
- **JS**: Nuevos módulos en `assets/js/dashboard-features.js` para timeline, kanban, auto-refresh, sparklines
- **CSS**: Se aprovechan clases nativas de Tabler, sin custom CSS adicional requerido
- **API**: Posibles nuevos endpoints en `api/` para datos de timeline y kanban (si no existen)
- **No breaking changes**: Todas las funciones son aditivas, el dashboard actual sigue funcionando igual
