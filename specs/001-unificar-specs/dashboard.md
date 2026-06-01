# Módulo: Dashboard — Panel de Resumen

## Clarifications

### Session 2026-06-01

- Q: ¿Auto-refresh vs filtro manual (race condition)? → A: Auto-refresh se pausa automáticamente al interactuar con filtros y se reanuda tras 10s de inactividad.
- Q: ¿API del dashboard: endpoint único o múltiple? → A: Múltiples endpoints paralelos (stats, charts, recent, alerts, sparklines, timeline, kanban). Cada componente carga independientemente.
- Q: ¿Kanban drag & drop optimista o pesimista? → A: Pesimista. Mostrar spinner en la tarjeta mientras se espera confirmación del backend. Solo se mueve si el servidor confirma.
- Q: Alerta "sin asignaciones" para registrador: ¿sin establecimientos asignados o sin observaciones registradas? → A: Sin asignaciones de establecimiento-mes. El registrador no tiene ningún establecimiento asignado por el supervisor para el período actual.
- Q: Selector global de año vs selector local de "Observaciones por Mes" — ¿cómo se combinan? → A: El selector global de año sincroniza todos los componentes. El selector local de "Observaciones por Mes" solo cambia el mes (el año viene del global).

## 1. User Scenarios & Testing

### HU-DASH-001: Visualizar panel resumen al iniciar sesión
**Prioridad:** P1  
**Rol:** Todos los roles

```gherkin
Dado que el usuario ha iniciado sesión en el sistema
Cuando se cargue la vista dashboard
Entonces debe mostrar las tarjetas de estadísticas: Total, Pendientes, Aprobadas, Problemas
Y debe mostrar un gráfico de donut con la distribución por estado
Y debe mostrar un gráfico de barras con los principales tipos de error
Y debe mostrar un gráfico de líneas con la tendencia mensual
Y debe mostrar una lista con las 5 observaciones más recientes
Y debe mostrar un esqueleto (skeleton) de carga mientras se obtienen los datos
```

### HU-DASH-002: Selector de año filtra todos los datos
**Prioridad:** P1  
**Rol:** Todos los roles

```gherkin
Dado que el usuario está en el dashboard
Cuando selecciona un año distinto en el selector de año del encabezado
Entonces todos los gráficos, tarjetas y listas se actualizan con datos filtrados por ese año
```

### HU-DASH-003: Auto-refresh periódico
**Prioridad:** P2  
**Rol:** Todos los roles

```gherkin
Dado que el usuario está en el dashboard
Cuando el toggle de auto-refresh está activado
Entonces los datos se recargan automáticamente cada 2 minutos
Y la preferencia se persiste en localStorage

Dado que el usuario desactiva el toggle de auto-refresh
Entonces los datos no se recargan automáticamente
Y la preferencia se guarda en localStorage
```

### HU-DASH-004: Tarjetas con pestañas intercambiables
**Prioridad:** P2  
**Rol:** Todos los roles

```gherkin
Dado que el usuario está en el dashboard
Cuando hace clic en la pestaña "Pendientes" dentro de la tarjeta Observaciones
Entonces la lista muestra solo observaciones pendientes

Dado que el usuario hace clic en la pestaña "Tabla" dentro de la tarjeta de gráfico
Entonces el gráfico de donut se reemplaza por una tabla de datos numéricos

Dado que el usuario hace clic en la pestaña "Notificaciones" dentro de la tarjeta Acciones
Entonces se muestran las notificaciones recientes en lugar de las acciones rápidas
```

### HU-DASH-005: Alertas de asignación
**Prioridad:** P2  
**Rol:** Supervisor, Registrador

```gherkin
Dado que el usuario autenticado tiene rol Supervisor
Cuando existen registradores sin asignaciones
Entonces se muestra una alerta con la lista de registradores sin asignar

Dado que el usuario autenticado tiene rol Registrador
Cuando el usuario no tiene observaciones asignadas
Entonces se muestra una alerta indicando que no tiene asignaciones
```

### HU-DASH-006: Sparklines en tarjetas de estadísticas
**Prioridad:** P3  
**Rol:** Todos los roles

```gherkin
Dado que el usuario está en el dashboard
Entonces cada tarjeta de estadísticas muestra un minigráfico (sparkline) con la tendencia de los últimos 7 días
```

### HU-DASH-007: Tablero Kanban de observaciones
**Prioridad:** P3  
**Rol:** Supervisor

```gherkin
Dado que el usuario Supervisor está en el dashboard
Cuando accede a la vista Kanban
Entonces ve columnas por estado del flujo de trabajo (Registrada, En Revisión, Aprobada, Rechazada, Resuelta)
Y puede arrastrar y soltar observaciones entre columnas para cambiar su estado
Y cada observación tiene botones alternativos para cambiar de estado sin arrastrar
```

### HU-DASH-008: Timeline de actividad reciente
**Prioridad:** P3  
**Rol:** Todos los roles

```gherkin
Dado que el usuario está en el dashboard
Entonces debe mostrar una línea de tiempo (timeline) con la actividad reciente del sistema
```

### HU-DASH-009: Pasos de progreso del flujo de trabajo
**Prioridad:** P3  
**Rol:** Todos los roles

```gherkin
Dado que el usuario está viendo una observación en el dashboard
Entonces debe ver los 4 pasos del progreso: Registrada → En Revisión → Aprobada/Rechazada → Resuelta
```

### HU-DASH-010: Acceso rápido a Informe de Errores REM
**Prioridad:** P2  
**Rol:** Todos los roles

```gherkin
Dado que el usuario está en el dashboard
Cuando hace clic en el acceso rápido a Informe de Errores REM
Entonces se abre la vista del informe con los períodos trimestral y anual disponibles
```

### HU-DASH-011: Filtros desplegables en "Observaciones por Mes"
**Prioridad:** P2  
**Rol:** Todos los roles

```gherkin
Dado que el usuario está en el dashboard
Cuando interactúa con la tarjeta "Observaciones por Mes"
Entonces puede seleccionar mes mediante selector desplegable para filtrar los datos mostrados (el año viene del selector global del encabezado)
```

### Edge Cases

| Caso | Descripción |
|------|-------------|
| EC-DASH-01 | No hay observaciones registradas: todas las tarjetas muestran 0, gráficos vacíos con mensaje "Sin datos" |
| EC-DASH-02 | Error de conexión al cargar datos: mostrar mensaje de error descriptivo y botón de reintentar |
| EC-DASH-03 | Año sin datos: mostrar mensaje "No hay datos para el año seleccionado" |
| EC-DASH-04 | Sesión expirada durante auto-refresh: redirigir al login |
| EC-DASH-05 | Usuario sin permisos: redirigir a página de acceso denegado |
| EC-DASH-06 | Sparkline con menos de 7 días de datos: mostrar con los puntos disponibles sin errores |
| EC-DASH-07 | Auto-refresh durante interacción con filtros: el refresh se pausa automáticamente y se reanuda tras 10s de inactividad |

---

## 2. Requirements

### Functional Requirements

| ID | Descripción | Prioridad |
|----|-------------|-----------|
| FR-DASH-001 | El sistema debe mostrar 4 tarjetas de estadísticas (Total, Pendientes, Aprobadas, Problemas) al cargar el dashboard | P1 |
| FR-DASH-002 | El sistema debe renderizar un gráfico de donut (ApexCharts) con la distribución de observaciones por estado | P1 |
| FR-DASH-003 | El sistema debe renderizar un gráfico de barras (ApexCharts) con los principales tipos de error | P1 |
| FR-DASH-004 | El sistema debe renderizar un gráfico de líneas (ApexCharts) con la tendencia mensual de observaciones | P1 |
| FR-DASH-005 | El sistema debe listar las 5 observaciones más recientes con enlace a detalle | P1 |
| FR-DASH-006 | El sistema debe incluir un selector de año en el encabezado que filtre todos los componentes del dashboard | P1 |
| FR-DASH-007 | El sistema debe mostrar un skeleton loader mientras se cargan los datos | P1 |
| FR-DASH-008 | El sistema debe implementar auto-refresh configurable cada 2 minutos con toggle switch y persistencia en localStorage | P2 |
| FR-DASH-009 | El sistema debe implementar pestañas intercambiables en las tarjetas: Observaciones (Recientes/Pendientes/Problemas), Gráfico (Gráfico/Tabla), Acciones (Acciones/Notificaciones) | P2 |
| FR-DASH-010 | El sistema debe mostrar alertas de asignación: supervisores ven registradores sin asignar, registradores ven si no tienen asignaciones | P2 |
| FR-DASH-011 | El sistema debe proporcionar acceso rápido al Informe de Errores REM con períodos trimestral y anual | P2 |
| FR-DASH-012 | El sistema debe incluir filtros desplegables (año/mes) en la tarjeta "Observaciones por Mes" | P2 |
| FR-DASH-013 | El sistema debe mostrar sparklines (ApexCharts modo minigráfico) con tendencia de 7 días en cada tarjeta de estadísticas | P3 |
| FR-DASH-014 | El sistema debe incluir un timeline de actividad reciente | P3 |
| FR-DASH-015 | El sistema debe mostrar los pasos de progreso del flujo de trabajo (Registrada → En Revisión → Aprobada/Rechazada → Resuelta) | P3 |
| FR-DASH-016 | El sistema debe incluir un tablero Kanban con columnas por estado, drag & drop para supervisores y botones de estado alternativos | P3 |

### Key Entities

| Entidad | Descripción |
|---------|-------------|
| observaciones | Tabla principal de observaciones; fuente de datos para estadísticas y listados |
| usuarios | Tabla de usuarios del sistema; determinan el rol y filtros de datos |
| estados_observacion | Catálogo de estados posibles para las observaciones |

---

## 3. Success Criteria

| Criterio | Métrica |
|----------|---------|
| CR-DASH-01 | Todas las tarjetas, gráficos y listas se renderizan en menos de 3 segundos desde la carga de la página |
| CR-DASH-02 | El selector de año actualiza todos los componentes del dashboard sin recargar la página |
| CR-DASH-03 | El auto-refresh se activa/desactiva correctamente y persiste la preferencia entre sesiones |
| CR-DASH-04 | Las alertas de asignación se muestran correctamente según el rol del usuario |
| CR-DASH-05 | Los sparklines muestran datos de 7 días sin errores de renderizado |
| CR-DASH-06 | El Kanban permite arrastrar y soltar observaciones entre columnas y persiste el cambio de estado |
| CR-DASH-07 | Las pestañas cambian el contenido de cada tarjeta sin recargar la página |
| CR-DASH-08 | Los filtros de "Observaciones por Mes" actualizan los datos correctamente |

---

## 4. Assumptions

| ID | Supuesto |
|----|----------|
| ASM-DASH-01 | Los datos de observaciones están disponibles en la base de datos y las APIs responden correctamente |
| ASM-DASH-02 | ApexCharts está correctamente instalado y configurado en el proyecto |
| ASM-DASH-03 | Tabler Core 1.4 está disponible y los componentes (cards, tabs, dropdowns, timeline, steps, kanban) están importados |
| ASM-DASH-04 | El usuario tiene una sesión activa y válida al acceder al dashboard |
| ASM-DASH-05 | El navegador del usuario soporta localStorage para la persistencia del auto-refresh |
| ASM-DASH-06 | Los roles de usuario (Supervisor, Registrador, etc.) están correctamente definidos en el sistema |
| ASM-DASH-07 | La vista se sirve desde `views/dashboard.php`. APIs paralelas: `api/dashboard/estadisticas.php`, `api/dashboard/graficos.php`, `api/dashboard/recientes.php`, `api/dashboard/alertas.php`, `api/dashboard/sparklines.php`, `api/dashboard/timeline.php`, `api/dashboard/kanban.php` |
