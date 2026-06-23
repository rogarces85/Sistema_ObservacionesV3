# Funcionalidades y Mockups Pendientes

## Funcionalidades Por Pagina

| Pagina | Funcionalidades implementadas | Funcionalidades no implementadas / parciales | Estado |
|---|---|---|---|
| Login | Inicio de sesion con `api/auth.php?action=login`; seleccion de anio; redireccion al dashboard; mensajes de error/exito; enlace a manual. | No hay recuperacion de contrasena; no hay registro publico; no hay MFA. | Parcial |
| Dashboard | KPIs por anio; graficos de estado y tendencia mensual; top de tipos de error; ultimas observaciones; alertas de asignaciones; accesos rapidos por rol; informe REM web/PDF para supervisor. | Cola asincrona de reportes existe en modelo/worker, pero no esta integrada; el worker tiene riesgo runtime documentado. | Parcial |
| Observaciones | Listado; filtro por texto, estado y mes; crear; editar permitido; ver detalle con historial real; importacion Excel con preview y confirmacion; descarga de plantilla; validacion de asignacion mensual en registro manual e importacion; supervisores pueden enviar a papelera. | No aplica para los elementos graficados en el mockup. | Implementado |
| Supervision | Vista protegida para supervisores; filtros; carga dinamica; detalle con historial; aprobar; cancelar/rechazar; mover a papelera; acciones masivas; clasificacion y detalle de error obligatorio al aprobar individual o masivo. | No aplica para los elementos graficados en el mockup. | Implementado |
| Reportes | Filtros por anio, trimestre, mes, comuna y establecimiento; graficos/tablas de total errores, plazos, uso validador, serie y hoja; exportacion Excel/PDF visible; cola asincrona para generar/listar/descargar reportes. | Requiere ejecutar periodicamente `php worker_reportes.php` para procesar la cola. | Implementado |
| Usuarios | Vista protegida para supervisores; listar; crear; editar; activar/desactivar; eliminar; restablecer contrasena; auditoria por usuario; endpoints en `api/users.php`. | Reset usa contrasena fija `admin123`; es deuda de seguridad, no elemento de mockup. | Implementado |
| Perfil | Visualizacion de datos del usuario; cambio de contrasena propia via `api/users.php`; actividad reciente desde auditoria. | No aplica para los elementos graficados en el mockup. | Implementado |
| Asignaciones | Vista protegida para supervisores; seleccion de registrador; ver asignados; ver referentes/contactos; asignar multiples establecimientos; asignacion anual o temporal; remover; copiar anio anterior; listar reasignaciones temporales. | Referentes/contactos se muestran, pero no hay CRUD de referentes en esta pagina. | Parcial |
| Eliminadas | Vista protegida para supervisores; papelera; filtros; estadisticas; seleccion multiple; restaurar; eliminar permanentemente; endpoints en `api/deleted.php`. | Existe riesgo general por eliminacion hibrida: tambien hay hard delete por `api/observations.php`. | Parcial |
| Establecimientos | Vista protegida para supervisores; listar; estadisticas activos/inactivos; filtro por comuna; mostrar inactivos; crear; editar; activar/desactivar via `api/locations.php`. | No hay eliminacion permanente; no hay gestion de comunas; no hay gestion de referentes/contactos desde esta pagina. | Parcial |
| Header / Navegacion | Selector de anio; cambio de tema light/dark; busqueda global basica de paginas; menu de usuario; logout; notificaciones reales persistidas con badge y marcar como leidas. | Requiere tabla `notificaciones` aplicada en BD. | Implementado |
| Exportacion | Excel/PDF sincronico por `api/export.php`; informe errores JSON/PDF por `api/informe_errores.php`; cola asincrona con worker y descarga controlada. | Requiere ejecutar `php worker_reportes.php` como tarea programada. | Implementado |
| Versionado tecnico | Existe `api/versioning.php`, `models/Version.php` y pagina visible `versionado` para snapshots/rollback. | Requiere tabla `versiones_sistema` aplicada en BD y uso con respaldo previo. | Implementado |

## Mockups Por Pagina

### Login

```text
+--------------------------------------------------------------------------------+
|                              SISTEMA REM - LOGIN                                |
+--------------------------------------+-----------------------------------------+
| Hero institucional                   | Card de inicio de sesion                 |
|                                      | +-------------------------------------+  |
| - Logo / identidad                   | | Usuario                             |  |
| - Descripcion del sistema            | | Contrasena                          |  |
| - Beneficios / seguridad             | | Anio de trabajo                     |  |
| - Manual de usuario                  | | [ Iniciar sesion ]                  |  |
|                                      | | Error / exito                       |  |
|                                      | +-------------------------------------+  |
+--------------------------------------+-----------------------------------------+
```

### Dashboard

```text
+--------------------------------------------------------------------------------+
| Header: busqueda | notificaciones | anio | tema | usuario                      |
+----------------------+---------------------------------------------------------+
| Sidebar              | Hero bienvenida / rol / anio                            |
| - Dashboard          | [Nueva observacion] [Reportes] [Supervisar] [Informe]   |
| - Observaciones      +---------------------------------------------------------+
| - Supervision        | KPI Total | KPI Pendientes | KPI Aprobadas | Problemas  |
| - Reportes           +---------------------------------------------------------+
| - Admin              | Grafico estado | Top errores | Acciones rapidas          |
|                      +---------------------------------------------------------+
|                      | Grafico tendencia mensual                               |
|                      +---------------------------------------------------------+
|                      | Ultimas observaciones                                   |
+----------------------+---------------------------------------------------------+
```

### Observaciones

```text
+--------------------------------------------------------------------------------+
| Header / Sidebar                                                               |
+----------------------+---------------------------------------------------------+
| Navegacion           | Titulo: Listado de Observaciones                         |
|                      | [Importar] [Nueva Observacion]                           |
|                      +---------------------------------------------------------+
|                      | Filtros: Buscar | Estado | Mes                          |
|                      +---------------------------------------------------------+
|                      | Tabla observaciones                                     |
|                      | Establecimiento | Referencia | Tipo | Estado | Acciones  |
|                      +---------------------------------------------------------+
|                      | Modal crear/editar                                      |
|                      | - Informacion general                                   |
|                      | - Detalle observacion                                   |
|                      | - Clasificacion y seguimiento                           |
|                      +---------------------------------------------------------+
|                      | Modal importar: archivo -> preview -> confirmar          |
|                      +---------------------------------------------------------+
|                      | Modal detalle                                           |
+----------------------+---------------------------------------------------------+
```

### Supervision

```text
+--------------------------------------------------------------------------------+
| Header / Sidebar                                                               |
+----------------------+---------------------------------------------------------+
| Navegacion           | Titulo: Supervision de Observaciones                     |
|                      +---------------------------------------------------------+
|                      | Filtros: Estado | Mes | Comuna | Establecimiento | Reg.  |
|                      +---------------------------------------------------------+
|                      | Acciones masivas: [Aprobar] [Cancelar] [Eliminar]        |
|                      +---------------------------------------------------------+
|                      | Tabla seleccionable                                     |
|                      | [] Establecimiento | Mes | Tipo | Estado | Acciones     |
|                      +---------------------------------------------------------+
|                      | Modal detalle                                           |
|                      | - Datos observacion                                     |
|                      | - Historial de estados                                  |
|                      +---------------------------------------------------------+
|                      | Modal confirmar accion                                  |
|                      | - Comentario                                            |
|                      | - Clasificacion / detalle error si aprueba              |
+----------------------+---------------------------------------------------------+
```

### Reportes

```text
+--------------------------------------------------------------------------------+
| Header / Sidebar                                                               |
+----------------------+---------------------------------------------------------+
| Navegacion           | Titulo: Reportes de Errores REM                          |
|                      +---------------------------------------------------------+
|                      | Filtros                                                 |
|                      | Anio | Trimestre | Mes | Comuna | Establecimiento       |
|                      | [Aplicar filtros] [Limpiar]                             |
|                      +---------------------------------------------------------+
|                      | Tabs                                                    |
|                      | [Total Errores] [Plazos] [Validador] [Serie] [Hoja]     |
|                      +---------------------------------------------------------+
|                      | Grafico del reporte activo                              |
|                      +---------------------------------------------------------+
|                      | Tabla resumen del reporte activo                        |
+----------------------+---------------------------------------------------------+
```

### Usuarios

```text
+--------------------------------------------------------------------------------+
| Header / Sidebar                                                               |
+----------------------+---------------------------------------------------------+
| Navegacion           | Titulo: Gestion de Usuarios                              |
|                      | [Nuevo Usuario]                                         |
|                      +---------------------------------------------------------+
|                      | Tabla usuarios                                          |
|                      | Usuario | Nombre | Rol | Estado | Fecha | Acciones       |
|                      | Acciones: editar | reset password | eliminar             |
|                      +---------------------------------------------------------+
|                      | Modal crear/editar usuario                              |
|                      | - Username                                              |
|                      | - Contrasena                                            |
|                      | - Nombre completo                                       |
|                      | - Rol                                                   |
+----------------------+---------------------------------------------------------+
```

### Perfil

```text
+--------------------------------------------------------------------------------+
| Header / Sidebar                                                               |
+----------------------+---------------------------------------------------------+
| Navegacion           | Titulo: Mi Perfil                                       |
|                      +----------------------------+----------------------------+
|                      | Informacion del usuario    | Cambiar contrasena         |
|                      | - Usuario                  | - Actual                   |
|                      | - Nombre completo          | - Nueva                    |
|                      | - Rol                      | - Confirmar                |
|                      | - Miembro desde            | [Cambiar contrasena]       |
|                      +----------------------------+----------------------------+
|                      | Actividad reciente                                      |
|                      | Placeholder: sin actividad reciente                     |
+----------------------+---------------------------------------------------------+
```

### Asignaciones

```text
+--------------------------------------------------------------------------------+
| Header / Sidebar                                                               |
+----------------------+---------------------------------------------------------+
| Navegacion           | Titulo: Asignacion de Establecimientos                   |
|                      | Selector anio | [Copiar anio anterior]                  |
|                      +----------------------------+----------------------------+
|                      | Lista registradores        | Detalle registrador        |
|                      | - Buscar / seleccionar     | - Asignados actuales       |
|                      |                            | - Contactos/referentes     |
|                      |                            | [Asignar / Reasignar]      |
|                      +----------------------------+----------------------------+
|                      | Reasignaciones temporales activas                       |
|                      +---------------------------------------------------------+
|                      | Modal asignar                                           |
|                      | - Tipo anual/temporal                                   |
|                      | - Meses                                                 |
|                      | - Establecimientos disponibles                          |
+----------------------+---------------------------------------------------------+
```

### Eliminadas

```text
+--------------------------------------------------------------------------------+
| Header / Sidebar                                                               |
+----------------------+---------------------------------------------------------+
| Navegacion           | Titulo: Observaciones Eliminadas                         |
|                      | [Restaurar] [Eliminar permanentemente]                   |
|                      +---------------------------------------------------------+
|                      | Estadisticas: Total | Por estado | Mayor eliminador      |
|                      +---------------------------------------------------------+
|                      | Filtros: Mes | Comuna | Establecimiento | Registrador | Buscar |
|                      +---------------------------------------------------------+
|                      | Tabla papelera                                          |
|                      | [] ID | Fecha | Establecimiento | Mes | Estado | Acciones |
|                      +---------------------------------------------------------+
|                      | Modal confirmar                                         |
|                      | - Comentario opcional                                   |
|                      | - Confirmacion irreversible para delete permanente       |
+----------------------+---------------------------------------------------------+
```

### Establecimientos

```text
+--------------------------------------------------------------------------------+
| Header / Sidebar                                                               |
+----------------------+---------------------------------------------------------+
| Navegacion           | Titulo: Gestion de Establecimientos                      |
|                      | [Nuevo Establecimiento]                                 |
|                      +---------------------------------------------------------+
|                      | KPIs: Activos | Inactivos | Total                       |
|                      +---------------------------------------------------------+
|                      | Filtros: Comuna | Mostrar inactivos                     |
|                      +---------------------------------------------------------+
|                      | Tabla establecimientos                                  |
|                      | Codigo | Nombre | Nombre corto | Comuna | Estado | Editar  |
|                      +---------------------------------------------------------+
|                      | Modal crear/editar                                      |
|                      | - Codigo                                                |
|                      | - Nombre completo                                       |
|                      | - Nombre corto                                          |
|                      | - Comuna                                                |
+----------------------+---------------------------------------------------------+
```

### Header / Navegacion Global

```text
+--------------------------------------------------------------------------------+
| Logo / Sidebar                                                                 |
+----------------------+---------------------------------------------------------+
| Menu lateral         | Header superior                                         |
| - Dashboard          | [Buscar] [Notificaciones] [Anio] [Usuario]             |
| - Observaciones      |                                                         |
| - Supervision        | Busqueda global: paginas principales                    |
| - Reportes           | Notificaciones: placeholder                             |
| - Usuarios           | Selector anio: cambia sesion y recarga                  |
| - Asignaciones       | Usuario: perfil, dashboard, tema, logout                |
| - Papelera           |                                                         |
+----------------------+---------------------------------------------------------+
```

### Versionado Tecnico No Expuesto

```text
+--------------------------------------------------------------------------------+
| Pagina propuesta: Versionado / Backups                                         |
+--------------------------------------------------------------------------------+
| Titulo: Versiones del Sistema                                                  |
+--------------------------------------------------------------------------------+
| [Crear snapshot] [Restaurar version seleccionada]                              |
+--------------------------------------------------------------------------------+
| Tabla snapshots                                                                |
| Fecha | Usuario | Descripcion | Hash/Ruta | Acciones                         |
+--------------------------------------------------------------------------------+
| Modal confirmar rollback                                                       |
| - Advertencia                                                                  |
| - Confirmacion explicita                                                       |
+--------------------------------------------------------------------------------+
```
