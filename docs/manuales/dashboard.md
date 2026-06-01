# Manual de Usuario - Dashboard

## Descripción General

El **Dashboard** o Panel de Control es la vista principal del Sistema de Observaciones REM. Proporciona una visión general del estado de las observaciones mediante tarjetas estadísticas, gráficos interactivos, listas de observaciones recientes y un tablero Kanban.

## Acceso

El Dashboard es accesible para todos los usuarios autenticados (Registradores y Supervisores). Se carga automáticamente al iniciar sesión o al seleccionar "Panel de Control" en el menú lateral.

## Componentes del Dashboard

### 1. Selector Global de Año

Ubicado en la barra de navegación superior, el selector de año sincroniza todos los componentes del dashboard. Al cambiar el año, la página se recarga con los datos correspondientes al año seleccionado.

### 2. Tarjetas de Estadísticas

Cuatro tarjetas principales muestran los indicadores clave:

| Tarjeta | Descripción | Color |
|---------|-------------|-------|
| **Total Registradas** | Cantidad total de observaciones en el año seleccionado | Azul |
| **Pendientes** | Observaciones que aún no han sido revisadas | Amarillo |
| **Aprobadas** | Observaciones que han sido aprobadas | Verde |
| **Con Problemas** | Observaciones rechazadas o con error | Rojo |

Cada tarjeta incluye un **sparkline** (mini gráfico de tendencia) que muestra la actividad de los últimos 7 días.

### 3. Gráficos Interactivos

#### Distribución por Estado (Donut)
Gráfico circular que muestra la proporción de observaciones según su estado actual. Al pasar el cursor sobre cada segmento se muestra el detalle con cantidad y porcentaje.

#### Top Tipos de Error (Barras Horizontales)
Gráfico de barras que muestra los tipos de error más frecuentes, ordenados de mayor a menor. Permite identificar patrones recurrentes.

#### Observaciones por Mes (Área)
Gráfico de área que muestra la tendencia mensual de observaciones. Incluye un **selector local de mes** que permite filtrar por un mes específico (el año viene del selector global).

### 4. Pestañas del Dashboard

El dashboard organiza la información en tres pestañas:

#### Pestaña: Resumen
Contiene las tarjetas de estadísticas, los tres gráficos y la tabla de últimas observaciones.

#### Pestaña: Kanban
Tablero visual con columnas por estado de observación:

- **Pendiente**: Observaciones sin revisar
- **Aprobado**: Observaciones aceptadas
- **Rechazado**: Observaciones rechazadas
- **Error**: Observaciones con errores identificados
- **Justificado**: Observaciones justificadas

**Drag & Drop (solo Supervisores)**: Los supervisores pueden arrastrar tarjetas entre columnas para cambiar el estado de una observación. El sistema utiliza un enfoque **pesimista**: al soltar una tarjeta se muestra un spinner mientras se espera la confirmación del backend. Si la operación falla, el tablero se recarga automáticamente para restaurar el estado correcto.

#### Pestaña: Actividad
Timeline con los eventos recientes del sistema, mostrando:
- Icono según el estado de la observación
- Descripción del evento
- Usuario que realizó la acción
- Tiempo relativo (hace X minutos/horas/días)

### 5. Alertas

Las alertas aparecen en la parte superior del dashboard según el rol del usuario:

**Para Supervisores:**
- Muestra registradores activos que no tienen establecimientos asignados para el año seleccionado.
- Incluye enlace directo a la página de Asignación de Establecimientos.

**Para Registradores:**
- Muestra una alerta si el registrador no tiene establecimientos asignados para el año seleccionado.
- Indica que debe contactar a su supervisor.

### 6. Últimas Observaciones

Tabla con las 5 observaciones más recientes, mostrando:
- Establecimiento y comuna
- Mes de la observación
- Tipo de error
- Estado actual (con badge de color)
- Fecha de creación

Enlace "Ver todas" que lleva a la página completa de observaciones.

### 7. Auto-Refresh

El dashboard se actualiza automáticamente cada **2 minutos**. Características:

- **Toggle ON/OFF**: Interruptor "Auto" en la barra de acciones para activar/desactivar.
- **Pausa por inactividad**: Se pausa automáticamente al interactuar con filtros, selectores o cualquier elemento del dashboard.
- **Reanudación automática**: Se reanuda tras **10 segundos de inactividad**.
- **Pausa por visibilidad**: Se detiene cuando la pestaña del navegador no está visible y se reanuda al volver.
- **Persistencia**: La preferencia de auto-refresh se guarda en `localStorage`.

## Acciones Rápidas

Según el rol del usuario, se muestran botones de acción:

- **Registrador**: Botón "Nueva Observación" para crear una nueva observación.
- **Supervisor**: Botón "Supervisar" para acceder a la vista de supervisión.

## Comportamiento por Rol

| Característica | Registrador | Supervisor |
|---------------|-------------|------------|
| Ve solo sus establecimientos asignados | Sí | No (ve todos) |
| Puede arrastrar en Kanban | No | Sí |
| Ve alertas de registradores sin asignar | No | Sí |
| Ve alerta si no tiene asignaciones | Sí | No |

## Solución de Problemas

| Problema | Solución |
|----------|----------|
| Las tarjetas muestran 0 | Verificar que tenga establecimientos asignados para el año seleccionado |
| Los gráficos no cargan | Verificar conexión a internet (CDN de ApexCharts) |
| El auto-refresh no funciona | Verificar que el toggle "Auto" esté activado |
| Drag & drop no funciona en Kanban | Solo disponible para supervisores |
| Alertas no aparecen | Normal si no hay condiciones de alerta activas |

## Mockups

### Dashboard General
![Dashboard General](dashboard-general.png)

### Dashboard Kanban
![Dashboard Kanban](dashboard-kanban.png)
