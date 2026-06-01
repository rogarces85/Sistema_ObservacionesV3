## 1. Fundación — Skeleton Loading

- [x] 1.1 Crear `assets/js/dashboard-features.js` con módulo base y sistema de feature flags
- [x] 1.2 Agregar `.skeleton` HTML placeholders a las 4 stat cards del dashboard
- [x] 1.3 Implementar `showSkeleton()` / `hideSkeleton()` con transición fade para stat cards
- [x] 1.4 Agregar skeleton rows (5 filas) a la tabla de últimas observaciones durante carga
- [x] 1.5 Agregar skeleton container (proporción 16:9) a los 3 gráficos ApexCharts durante inicialización
- [x] 1.6 Implementar transición suave de skeleton a contenido real en todos los componentes
- [x] 1.7 Verificar que skeletons no aparecen cuando los datos ya están cacheados

## 2. Fundación — Auto-Refresh

- [x] 2.1 Agregar toggle de auto-refresh con switch en el header del dashboard (`.form-check-input`)
- [x] 2.2 Implementar `DashboardAutoRefresh` clase con intervalo de 2 minutos
- [x] 2.3 Agregar persistencia del toggle en localStorage
- [x] 2.4 Implementar pausa/resume del intervalo usando Page Visibility API
- [x] 2.5 Agregar badge "Actualizado hace X seg" con contador en el header
- [x] 2.6 Implementar función `fetchDashboardData()` que recarga stats, charts y tabla vía AJAX
- [x] 2.7 Agregar animación de contador en stat cards cuando cambian los números
- [x] 2.8 Verificar que auto-refresh no causa memory leaks en ApexCharts

## 3. Organización — Card Tabs

- [x] 3.1 Agregar tabs "Recientes" | "Pendientes" | "Con Problemas" a la card de observaciones
- [x] 3.2 Implementar filtrado de tabla según tab activo sin recargar la página
- [x] 3.3 Agregar tabs "Gráfico" | "Tabla de Datos" a la card de distribución por estado
- [x] 3.4 Crear tabla de datos numéricos para el tab "Tabla de Datos" del gráfico
- [x] 3.5 Agregar tabs "Acciones" | "Notificaciones" a la card de acciones rápidas
- [x] 3.6 Implementar persistencia del tab activo en localStorage por card
- [x] 3.7 Verificar que tabs usan `.nav-tabs.card-header-tabs` de Bootstrap/Tabler

## 4. Organización — Dropdown Filters

- [x] 4.1 Agregar dropdown de año/mes al header de la card "Observaciones por Mes"
- [x] 4.2 Implementar endpoint `api/dashboard_filter.php` para datos filtrados
- [x] 4.3 Agregar dropdown de comuna al header de la card "Distribución por Estado"
- [x] 4.4 Implementar sincronización de filtros con sessionStorage
- [x] 4.5 Agregar botón "Limpiar filtros" que resetea todos los dropdowns
- [x] 4.6 Verificar que filtros se pre-aplican al navegar desde dashboard a reportes

## 5. Visualización de Flujo — Timeline

- [x] 5.1 Crear endpoint `api/timeline.php` que retorna eventos recientes ordenados
- [x] 5.2 Agregar card de timeline al dashboard con componente `.timeline` de Tabler
- [x] 5.3 Implementar renderizado de eventos con icono, descripción, usuario y timestamp
- [x] 5.4 Filtrar eventos según rol (registrador = solo sus observaciones)
- [x] 5.5 Agregar estado `.empty` cuando no hay eventos recientes
- [x] 5.6 Limitar timeline a los últimos 20 eventos con scroll si es necesario
- [x] 5.7 Verificar que timestamps relativos se actualizan ("hace 5 min", "hace 2 horas")

## 6. Visualización de Flujo — Progress Steps

- [x] 6.1 Agregar sección de progress steps al dashboard (componente `.steps` de Tabler)
- [x] 6.2 Implementar 4 pasos: Registrada → En Revisión → Aprobada/Rechazada → Resuelta
- [x] 6.3 Calcular conteo de observaciones por paso del flujo
- [x] 6.4 Hacer cada paso clickeable para filtrar la tabla/timeline a ese estado
- [x] 6.5 Filtrar steps según rol (registrador = solo sus observaciones)
- [x] 6.6 Verificar que steps inactivos se visualizan correctamente sin observaciones

## 7. Enriquecimiento Visual — Sparklines

- [x] 7.1 Agregar contenedor de sparkline a cada stat card (mini div debajo del número)
- [x] 7.2 Crear endpoint `api/sparkline_data.php` para datos de tendencia de 7 días
- [x] 7.3 Implementar `renderSparkline()` usando ApexCharts en modo sparkline
- [x] 7.4 Configurar colores de sparklines según la stat card (primary, warning, success, danger)
- [x] 7.5 Ocultar sparkline gracefulmente si no hay datos históricos suficientes
- [x] 7.6 Verificar que sparklines no afectan el layout de las stat cards en mobile

## 8. Enriquecimiento Visual — Kanban Board

- [x] 8.1 Agregar card de kanban board al dashboard con columnas por estado
- [x] 8.2 Implementar renderizado de cards dentro de cada columna del kanban
- [x] 8.3 Agregar drag & drop nativo (HTML5) para supervisores
- [x] 8.4 Implementar endpoint `api/update_estado.php` para cambio de estado vía drag & drop
- [x] 8.5 Agregar botones de cambio de estado como alternativa al drag & drop
- [x] 8.6 Filtrar kanban según rol (registrador = solo sus observaciones, sin drag)
- [x] 8.7 Agregar estado `.empty` en columnas sin observaciones
- [x] 8.8 Verificar que el kanban es responsive (scroll horizontal en mobile)

## 9. Integración y Verificación

- [ ] 9.1 Verificar que todas las funciones coexisten sin conflictos en el dashboard
- [ ] 9.2 Probar dashboard con rol de supervisor (acceso total)
- [ ] 9.3 Probar dashboard con rol de registrador (vistas limitadas)
- [ ] 9.4 Verificar que no hay errores de consola en Chrome DevTools
- [ ] 9.5 Probar responsive en viewport mobile (375px) y tablet (768px)
- [ ] 9.6 Verificar que ApexCharts se destruyen correctamente al desmontar/re-renderizar
- [ ] 9.7 Probar auto-refresh durante 10 minutos sin memory leaks
- [ ] 9.8 Verificar accesibilidad básica (ARIA labels, focus states, keyboard navigation)
