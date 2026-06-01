# Sistema de Observaciones REM

Sistema de gestión de observaciones del Resumen Estadístico Mensual (REM) para el **Servicio de Salud Osorno (SSO)** — Departamento de Estadística (DEGI).

**Versión:** 2.3.0 — **Última actualización:** Junio 2026

---

## Tabla de Contenidos

1. [Tecnologías](#tecnologías)
2. [Estructura del Proyecto](#estructura-del-proyecto)
3. [Base de Datos](#base-de-datos)
4. [APIs del Sistema](#apis-del-sistema)
5. [Modelos (Lógica de Negocio)](#modelos-lógica-de-negocio)
6. [Vistas](#vistas)
7. [Funcionalidades por Módulo](#funcionalidades-por-módulo)
8. [Series REM Soportadas](#series-rem-soportadas)
9. [Tipos de Error](#tipos-de-error)
10. [Roles y Permisos](#roles-y-permisos)
11. [Seguridad](#seguridad)
12. [Instalación](#instalación)
13. [Migraciones](#migraciones)
14. [Usuarios del Sistema](#usuarios-del-sistema)
15. [Manejo de Versiones](#manejo-de-versiones)
16. [Historial de Versiones](#historial-de-versiones)

---

## Tecnologías

| Categoría | Tecnologías |
|-----------|-------------|
| **Backend** | PHP 7.4+, PDO MySQL (Singleton) |
| **Base de Datos** | MySQL 5.7+ (InnoDB, utf8mb4) |
| **Frontend** | HTML5, CSS3, JavaScript ES6+ |
| **UI Framework** | Tabler Core 1.4 (Bootstrap 5), Tabler Icons |
| **Gráficos** | ApexCharts 3.45 |
| **Librerías PHP** | PhpSpreadsheet 5.4 (Excel), TCPDF 6.10 (PDF) |
| **Servidor** | Apache (XAMPP) |

---

## Estructura del Proyecto

```
ObservacionesREM_V2/
├── api/                              # Endpoints REST (13 archivos)
│   ├── auth.php                      # Autenticación (login, logout, check, change_year)
│   ├── observations.php              # CRUD observaciones (GET, POST, PUT, DELETE)
│   ├── supervision.php               # Operaciones supervisoras (approve, cancel, delete, update_status, get_filtered, get_detail)
│   ├── reports.php                   # Datos agregados para reportes (20+ dimensiones)
│   ├── export.php                    # Exportación Excel/PDF/CSV + reportes específicos
│   ├── informe_errores.php           # Informe trimestral/anual de errores (JSON + PDF)
│   ├── locations.php                 # CRUD comunas y establecimientos (GET, POST)
│   ├── import.php                    # Importación masiva Excel con preview/confirm
│   ├── import_template.php           # Generación plantilla Excel con instrucciones
│   ├── users.php                     # CRUD usuarios + cambio/reset contraseña
│   ├── assignments.php               # Asignación anual/temporal de establecimientos
│   ├── deleted.php                   # Papelera (list, restore, permanent_delete)
│   └── versioning.php                # Snapshots y rollback del sistema
│
├── assets/
│   ├── css/
│   │   └── tabler-override.css       # Override de Tabler (paleta SSO, BEM, responsive)
│   └── js/
│       ├── app.js                    # Lógica principal (fetchAPI, modals, CSRF, logout)
│       ├── charts-apex.js            # Gráficos ApexCharts (dashboard + reportes)
│       ├── toasts.js                 # Sistema de notificaciones toast (Bootstrap)
│       └── notifications.js          # Sistema legacy de notificaciones (reemplazado por toasts.js)
│
├── config/                           # Configuración y SQL
│   ├── config.php                    # Conexión BD, rutas, sesión, zona horaria (America/Santiago)
│   ├── constants.php                 # Constantes: estados, roles, series, hojas, meses, colores
│   ├── init_db.sql                   # Script inicial: tablas + datos semilla (5 usuarios, 7 comunas, 23+ establecimientos)
│   ├── migration_2026_02_06.sql      # Documentación de cambio semántico (SERIE/TIPO/REM)
│   ├── migration_2026_05_08_reportes.sql  # 6 índices compuestos para optimización
│   ├── migration_2026_05_08_limpieza_comunas.sql  # Unificación comunas duplicadas
│   ├── sprint3_migration.sql         # Creación tabla observaciones_eliminadas + índices
│   ├── create_asignaciones_table.sql # Creación tabla asignaciones_establecimientos
│   ├── update_establecimientos.sql   # Reemplazo completo del listado de establecimientos (93 registros)
│   └── migrations/
│       └── add_tipo_asignacion.sql   # Agrega columna tipo_asignacion a asignaciones (anual/temporal)
│
├── includes/                         # Componentes reutilizables
│   ├── header.php                    # Header con meta CSRF, navegación, selector de año
│   ├── footer.php                    # Footer, scripts JS (Tabler, ApexCharts, toasts, app)
│   ├── sidebar.php                   # Menú lateral con grupos por rol
│   ├── csrf.php                      # Clase CSRF (generación, validación, regeneración)
│   └── icons.php                     # Función tablerIcon() con SVGs inline
│
├── models/                           # Capa de datos (9 modelos)
│   ├── Database.php                  # Conexión PDO Singleton (query, queryOne, execute, transacciones)
│   ├── User.php                      # Usuarios (create, authenticate, update, delete, password management)
│   ├── Observation.php               # Observaciones (CRUD, 20+ reportes, historial, filtros)
│   ├── Location.php                  # Comunas y establecimientos (CRUD, búsqueda, toggle activo)
│   ├── EstablecimientoAsignacion.php # Asignaciones (anual/temporal, conflictos, copia anual)
│   ├── Exporter.php                  # Exportación Excel/PDF/CSV/PDF Detallado/PDF Informe Errores
│   ├── DeletedObservation.php        # Papelera (moveToTrash, restore, permanentDelete, stats)
│   ├── Version.php                   # Snapshots (directorio uploads/versiones/, rollback)
│   ├── ReportQueue.php               # Cola de reportes asíncronos (enqueue, worker, estados)
│   └── UserAudit.php                 # Auditoría de cambios en usuarios (logAction, getHistory)
│
├── views/                            # Vistas del sistema (10 vistas)
│   ├── login.php                     # Página de inicio de sesión
│   ├── dashboard.php                 # Panel de control con stats, gráficos, alertas, informe errores
│   ├── observaciones.php             # CRUD observaciones (1,102 líneas con filtros, modal, importación)
│   ├── supervision.php               # Panel supervisión (filtros, selección masiva, modal detalle con historial)
│   ├── reportes.php                  # Reportes con 5 tabs (Errores, Plazos, Validador, Serie, Hoja)
│   ├── usuarios.php                  # Gestión usuarios CRUD (solo supervisor)
│   ├── perfil.php                    # Perfil y cambio de contraseña
│   ├── asignaciones.php              # Asignar establecimientos (anual/temporal/referentes)
│   ├── eliminadas.php                # Papelera con restauración/eliminación permanente
│   └── establecimientos.php          # Gestión establecimientos y referentes
│
├── uploads/                          # Archivos importados/subidos (gitignored)
│   └── versiones/                    # Snapshots del sistema (creado automáticamente)
│
├── vendor/                           # Dependencias PHP (Composer)
├── index.php                         # Router principal (login check + page routing + permisos)
├── composer.json                     # phpoffice/phpspreadsheet ^5.4, tecnickcom/tcpdf ^6.10
├── .gitignore                        # node_modules, uploads, .env, IDE, logs, etc.
└── README.md                         # Este archivo
```

---

## Base de Datos

Base de datos `observaciones_rem` con charset `utf8mb4_unicode_ci`.

### Tablas

| Tabla | Propósito | Columnas clave |
|-------|-----------|----------------|
| **usuarios** | Usuarios del sistema | id, username, password_hash (bcrypt), nombre_completo, rol (registrador/supervisor), activo |
| **comunas** | Comunas del SSO (7 registros oficiales) | id, codigo_comuna (DEIS), nombre |
| **establecimientos** | Establecimientos de salud (93 registros) | id, codigo_establecimiento, nombre, nombre_corto, comuna_id (FK), activo |
| **observaciones** | Observaciones REM | id, anio, mes, establecimiento_id (FK), codigo_serie, codigo_hoja, tipo_error, detalle_observacion, plazo_entrega, usa_validador, estado_actual, clasificacion, detalle_error, respuesta_establecimiento, usuario_registro_id (FK), usuario_supervisor_id (FK) |
| **historial_estados** | Historial de cambios de estado | id, observacion_id (FK), estado_anterior, estado_nuevo, usuario_id (FK), comentario |
| **asignaciones_establecimientos** | Asignación establecimientos a registradores | id, usuario_id (FK), establecimiento_id (FK), anio, meses (ALL o lista 1-12), tipo_asignacion (anual/temporal) |
| **observaciones_eliminadas** | Papelera de reciclaje (soft-delete) | id, observacion_id, +copia completa de datos + motivo_eliminacion, fecha_eliminacion |
| **versiones_sistema** | Snapshots del sistema | id, version_tag, descripcion, snapshot_path, archivos_json (manifest), usuario_id |
| **reportes_pendientes** | Cola de reportes asíncronos | id, usuario_id, tipo_reporte, formato, parametros (JSON), estado (PENDIENTE/PROCESANDO/LISTO/ERROR) |
| **historial_usuarios** | Auditoría de cambios en usuarios | id, usuario_id, accion (CREACION/ACTIVACION/DESACTIVACION/CAMBIO_PASSWORD/etc), detalles |
| **referentes_establecimientos** | Contactos de establecimientos | id, establecimiento_id, cargo, nombre, telefono, email, activo |
| **logs** | Logs del sistema | id, usuario_id, accion, detalle, ip_address, user_agent |

### Índices de Optimización (migration_2026_05_08)

- `idx_anio_tipo_error` — (anio, tipo_error)
- `idx_anio_plazo` — (anio, plazo_entrega)
- `idx_anio_validador` — (anio, usa_validador)
- `idx_anio_serie_error` — (anio, codigo_serie, tipo_error)
- `idx_anio_hoja` — (anio, codigo_hoja)
- `idx_anio_estado` — (anio, estado_actual)

---

## APIs del Sistema

### `api/auth.php` — Autenticación
| Acción | Método | Descripción |
|--------|--------|-------------|
| `login` | POST | Autenticar usuario (username + password + year). Inicia sesión PHP |
| `logout` | POST | Destruir sesión |
| `check` | GET | Verificar sesión activa y retornar datos del usuario |
| `change_year` | POST | Cambiar año de trabajo en sesión (válida 2020~año_siguiente) |

### `api/observations.php` — Observaciones CRUD
| Acción | Método | Descripción |
|--------|--------|-------------|
| — | GET | Listar observaciones del año (filtro por rol: registrador ve solo las suyas) |
| — | GET + `?id=N` | Obtener observación por ID (con permisos) |
| `historial` | GET + `?id=N` | Historial de cambios de una observación |
| `stats` | GET | Estadísticas agregadas (por estado, mes, tipo de error) |
| — | POST | Crear observación (solo registradores, valida asignación mensual) |
| — | PUT + `?id=N` | Actualizar observación (registrador: solo propias pendientes; supervisor: todo) |
| — | DELETE + `?id=N` | Eliminar observación (solo supervisores) |

**Campos requeridos al crear:** mes, establecimiento_id, codigo_serie, tipo_error, detalle_observacion, plazo_entrega, usa_validador. codigo_hoja requerido excepto para S/OBSERVACION.

**Validación backend:** Registradores solo pueden crear/editar observaciones en establecimientos asignados para el mes específico (validación anual + temporal).

### `api/supervision.php` — Supervisión (solo supervisores)
| Acción | Método | Descripción |
|--------|--------|-------------|
| `approve` | POST | Aprobar observación(es). Acepta `estado_resultante` (`sin_observacion` → estado=aprobado/tipo_error=S/OBSERVACION, `error` → estado=error/tipo_error=ERROR). Incluye clasificación y detalle_error |
| `cancel` | POST | Cancelar observación(es) (estado=rechazado) |
| `delete` | POST | Mover a papelera (soft-delete) |
| `update_status` | POST | Cambio de estado genérico |
| `get_filtered` | GET | Observaciones con filtros (año, mes, estado, establecimiento, registrador, búsqueda texto, paginación) |
| `get_detail` | GET | Detalle completo + historial de una observación |

### `api/reports.php` — Reportes
| Reporte | Descripción |
|---------|-------------|
| `mes` | Observaciones por mes |
| `establecimiento` | Por establecimiento |
| `comuna` | Por comuna |
| `serie` | Por serie REM |
| `plazo` | Por plazo de entrega |
| `validador` | Por uso de validador |
| `errores_mes/establecimiento/comuna` | Solo tipo_error = 'ERROR' |
| `fuera_plazo_mes/establecimiento/comuna` | Solo plazo_entrega = 'fuera_plazo' |
| `validador_mes/establecimiento/comuna` | Solo usa_validador = 'si' |
| `serie_detalle` | Matriz Serie × Tipo Error |
| `hoja_detalle` | Top hojas REM más frecuentes |
| `plazo-agregado` | Plazo agregado por establecimiento+mes (agregación binaria) |
| `validador-agregado` | Validador agregado por establecimiento+mes |
| `error-reports` | Reporte unificado con filtros (meses, comuna, establecimiento) — 5 sub-reportes |

### `api/export.php` — Exportación
| Parámetro `report_type` | Formatos | Descripción |
|-------------------------|----------|-------------|
| `general` (default) | Excel, PDF, CSV | Exportación general con filtros |
| `detallado` | PDF | Reporte jerárquico Comuna→Establecimiento→Mes con rowspan, colores por estado |
| `errores_*`, `fuera_plazo_*`, `validador_*`, `serie_detalle`, `hoja_detalle` | Excel | Exportación individual por sub-reporte |

### `api/informe_errores.php` — Informe de Errores REM (solo supervisores)
| Parámetro | Descripción |
|-----------|-------------|
| `tipo=trimestral` + `trimestre=1-4` | Informe trimestral filtrado |
| `tipo=anual` | Informe anual completo |
| `format=json` | Vista web con paginación (20 por página) |
| `format=pdf` | PDF profesional con logo, diseño institucional, firma |

### `api/locations.php` — Ubicaciones
| Acción | Método | Descripción |
|--------|--------|-------------|
| `comunas` | GET | Listar todas las comunas |
| `establecimientos` | GET | Listar establecimientos (filtro por comuna_id o por comuna_nombre) |
| `establecimientos_all` | GET | Todos los establecimientos (incluye inactivos, solo supervisor) |
| `create` | POST | Crear establecimiento (solo supervisor) |
| `update` | POST | Actualizar establecimiento (solo supervisor) |
| `toggle` | POST | Activar/desactivar establecimiento (solo supervisor) |

### `api/users.php` — Usuarios (solo supervisores, excepto cambio de contraseña propia)
| Acción | Método | Descripción |
|--------|--------|-------------|
| — | GET | Listar usuarios (solo supervisor) |
| — | GET + `?id=N` | Obtener usuario por ID |
| — | POST | Crear usuario (solo supervisor, con generación de contraseña aleatoria opcional) |
| `action=update` | PUT | Actualizar datos (solo supervisor) |
| `action=password` | PUT | Cambiar contraseña (propia o de otros si es supervisor) |
| `action=reset_password` | PUT | Reset a `admin123` (solo supervisor, no a sí mismo) |
| `action=toggle` | PUT | Activar/desactivar (solo supervisor, no a sí mismo) |
| — | DELETE | Eliminar usuario (solo supervisor, no a sí mismo) |

**Política de contraseñas:** mínimo 8 caracteres, al menos 1 mayúscula, al menos 1 número.

### `api/assignments.php` — Asignaciones (solo supervisores)
| Acción | Método | Descripción |
|--------|--------|-------------|
| `list` | GET | Estadísticas de asignaciones por registrador |
| `registradores` | GET | Listar registradores activos |
| `establecimientos` | GET | Establecimientos con info de asignación |
| `asignados` | GET | Establecimientos asignados a un registrador |
| `asignar` | POST | Asignar establecimiento (anual o temporal con meses) |
| `asignar_multiple` | POST | Asignación masiva |
| `remover` | POST | Remover asignación (completa o parcial por meses) |
| `copiar_anio` | POST | Copiar asignaciones de un año a otro |
| `temporales` | POST | Listar asignaciones temporales activas con titular anual |

### `api/deleted.php` — Papelera (solo supervisores)
| Acción | Método | Descripción |
|--------|--------|-------------|
| `list` | GET | Listar eliminadas con filtros (año, mes, comuna, establecimiento, registrador, búsqueda) |
| `stats` | GET | Estadísticas de eliminadas (total, por estado, por mes, por eliminador) |
| `restore` | POST | Restaurar observación (vuelve a tabla original + historial) |
| `permanent_delete` | POST | Eliminación permanente |
| `restore_multiple` | POST | Restauración masiva |
| `permanent_delete_multiple` | POST | Eliminación permanente masiva |

### `api/versioning.php` — Versionado (solo supervisores)
| Acción | Método | Descripción |
|--------|--------|-------------|
| `list` | GET | Listar versiones disponibles |
| `detail` | GET + `?id=N` | Detalle de una versión (manifest de archivos) |
| `create` | POST | Crear snapshot (copia archivos a uploads/versiones/vXXX/) |
| `rollback` | POST + `?id=N` | Restaurar archivos desde snapshot + crea nueva versión |

---

## Modelos (Lógica de Negocio)

### `Database.php` — Conexión PDO Singleton
- `getInstance()` — Obtener instancia única
- `query(sql, params)` — SELECT múltiple
- `queryOne(sql, params)` — SELECT único
- `execute(sql, params)` — INSERT/UPDATE/DELETE
- `lastInsertId()` — Último ID insertado
- `beginTransaction()`, `commit()`, `rollback()` — Transacciones

### `User.php` — Usuarios
- `authenticate(username, password)` — Login con bcrypt
- `getById(id)`, `getAll()`, `getByRole(rol)` — Consultas
- `isActive(id)` — Verificar si está activo
- `create(username, password, nombreCompleto, rol)` — Crear con hash bcrypt
- `update(id, nombreCompleto, rol)` — Actualizar datos
- `updatePassword(id, newPassword)` — Cambiar contraseña
- `setActive(id, activo)` — Activar/desactivar
- `delete(id)` — Eliminar usuario
- `getByIdWithPassword(id)` — Para verificación de contraseña actual
- `usernameExists(username, excludeId)` — Verificar duplicado

### `Observation.php` — Observaciones (20+ reportes)
**CRUD:** `getAll`, `getById`, `create`, `update`, `delete`, `deleteWithAudit`, `getHistorial`
**Supervisión:** `updateStatus` (con extraData: clasificación, detalle_error, tipo_error), `bulkUpdateStatus`, `getWithFilters`, `getStats`
**Reportes generales:** `reportePorMes`, `reportePorEstablecimiento`, `reportePorComuna`, `reportePorSerie`, `reportePorPlazo`, `reportePorValidador`
**Reportes errores:** `reporteErroresPorMes/Establecimiento/Comuna`, `reporteErroresPorSerie`, `reporteErroresPorHoja`
**Reportes fuera de plazo:** `reporteFueraPlazoPorMes/Establecimiento/Comuna`
**Reportes validador:** `reporteValidadorPorMes/Establecimiento/Comuna`
**Reportes detalle:** `reportePorSerieDetalle`, `reportePorHojaDetalle`
**Reportes agregados:** `reportePlazoAgregado/Mensual`, `reporteValidadorAgregado/Mensual`, `reporteNoValidadorPorEstablecimiento`
**PDF:** `reporteDetalladoPDF`, `getErroresInforme`
**Utilidades:** `getComunasConDatos`, `getEstablecimientosConDatos`

### `Location.php` — Ubicaciones
- `getAllComunas()`, `getComunaById(id)`, `getComunaByNombre(nombre)`
- `getAllEstablecimientos()`, `getEstablecimientosByComuna(comunaId)`, `getEstablecimientoById(id)`
- `getAllEstablecimientosConInactivos()` — Incluye desactivados
- `searchEstablecimientos(searchTerm)` — Búsqueda por nombre
- `createComuna(codigo, nombre)`, `createEstablecimiento(codigo, nombre, nombreCorto, comunaId)`
- `updateEstablecimiento(id, data)`, `toggleEstablecimiento(id, activo)`
- `codigoEstablecimientoExiste(codigo, excludeId)` — Validar unicidad

### `EstablecimientoAsignacion.php` — Asignaciones (585 líneas)

**Lógica core:**
- Asignación **anual**: base para todo el año, puede ser ALL o meses específicos
- Asignación **temporal**: reasignación por meses con prioridad sobre la anual
- Validación de **solapamiento**: temporales no pueden solaparse entre sí; temporales pueden solapares con anuales
- Fusión de meses si ya existe asignación del mismo tipo
- Resta de meses para remoción parcial

**Métodos:**
- `getAllRegistradores()`, `getAllEstablecimientos()`
- `getEstablecimientosByRegistrador(registradorId, anio)` — Asignaciones de un registrador
- `getEstablecimientosConAsignacion(registradorId, anio)` — Todos los establecimientos con info de quién está asignado
- `asignar(usuarioId, establecimientoId, anio, meses, tipo)` — Asignar (anual o temporal)
- `asignarMultiple(usuarioId, establecimientoIds, anio, meses, tipo)` — Asignación masiva
- `remover(usuarioId, establecimientoId, anio, meses, tipo)` — Remover completa o parcial
- `removerTodas(usuarioId, anio)` — Remover todas las de un registrador
- `tieneAsignacionParaMes(usuarioId, establecimientoId, anio, mesNombre)` — Validación mensual (prioridad temporal sobre anual)
- `tieneAsignaciones(usuarioId, anio)` — Verificar si tiene al menos una
- `getRegistradoresSinAsignaciones(anio)` — Alertas de dashboard
- `getEstadisticasAsignaciones(anio)` — Resumen por registrador
- `copiarAsignaciones(anioOrigen, anioDestino)` — Copia anual (incluye temporales)
- `getAsignacionesTemporalesActivas(anio)` — Listar temporales con titular anual
- `getTitularAnual(establecimientoId, anio)` — Obtener dueño anual
- `getReferentes(establecimientoId)`, `getReferentesMultiple(ids)` — Referentes de contacto

### `Exporter.php` — Exportación (739 líneas)

**Formatos:**
- `exportToExcel(data, filename, headers)` — Excel general con título, fecha, auto-width
- `exportToPDF(data, filename, headers, title)` — PDF horizontal con tabla HTML
- `exportToCSV(data, filename, headers)` — CSV con BOM UTF-8 y separador `;`
- `exportDetalladoPDF(data, filename, filters)` — PDF jerárquico Comuna→Establecimiento→Mes con rowspan, códigos de color por estado, header rojo oscuro, paginación cada 35 filas
- `exportErroresExcel(data, filename, reportType)` — Excel para reportes específicos con títulos descriptivos
- `exportInformeErroresPDF(data, periodo, filename)` — PDF vertical institucional con logo SSO, diseño profesional, tabla jerárquica, sección de firma

**Helpers:** `prepareObservationsData`, `getObservationsHeaders`

### `DeletedObservation.php` — Papelera
- `moveToTrash(observacionId, supervisorId, reason)` — Soft-delete (copia a tabla eliminadas + elimina original)
- `getAll(filters)` — Listar con filtros (año, mes, comuna, establecimiento, registrador, búsqueda)
- `restore(deletedId, supervisorId)` — Restaurar (reinserta en observaciones + historial + elimina de papelera)
- `permanentDelete(deletedId)` — Eliminación definitiva
- `getStats(year)` — Estadísticas (total, por estado original, por mes, por eliminador)

### `Version.php` — Snapshots
- `createVersion(descripcion, userId)` — Crea tag vXXX, copia archivos a `uploads/versiones/vXXX/`, genera manifiesto con hashes MD5
- `getAllVersions()` — Listar con autor
- `getVersionDetails(id)` — Detalle con manifiesto decodificado
- `rollback(versionId, userId)` — Restaura archivos desde snapshot + crea nueva versión de registro

### `ReportQueue.php` — Cola de Reportes
- `enqueue(userId, tipoReporte, formato, parametros)` — Encolar nuevo reporte
- `getUserReports(userId)` — Reportes de un usuario
- `getNextPending()` — Siguiente pendiente (SELECT FOR UPDATE)
- `updateStatus`, `markProcessing`, `markReady`, `markError` — Ciclo de vida

### `UserAudit.php` — Auditoría de Usuarios
- `logAction(userId, action, details)` — Registrar acción
- `getHistory(userId)` — Obtener historial de un usuario

---

## Vistas

| Vista | Archivo | Rol | Descripción |
|-------|---------|-----|-------------|
| login | `views/login.php` | Público | Formulario de inicio de sesión |
| dashboard | `views/dashboard.php` | Todos | Panel con stats cards (total, pendientes, aprobados, problemas), gráficos ApexCharts (distribución por estado, top tipos de error, tendencia por mes), últimas 5 observaciones, acciones rápidas, alertas de asignación, modal Informe de Errores |
| observaciones | `views/observaciones.php` | Todos | CRUD completo con tabla responsive, filtros, modal de creación/edición, importación Excel con preview, selector de series/hojas, validación de asignación |
| supervision | `views/supervision.php` | Supervisor | Panel con filtros (estado, mes, comuna, establecimiento, registrador, búsqueda), selección masiva con checkboxes, botones de acción masiva (aprobar/cancelar/eliminar), modal de detalle con historial, selector `estado_resultante` (Sin Observación / Error) con campos de clasificación |
| reportes | `views/reportes.php` | Todos | 5 tabs (Errores por Establecimiento, Plazos Entrega, Uso Validador, Errores por Serie, Errores por Hoja) con gráficos ApexCharts y tabla de datos, filtros por año/trimestre/mes/comuna/establecimiento |
| usuarios | `views/usuarios.php` | Supervisor | CRUD con tabla de usuarios, modal creación con generación de contraseña aleatoria, edición, activación/desactivación, reseteo de contraseña |
| perfil | `views/perfil.php` | Todos | Información del usuario, cambio de contraseña (con validación de política) |
| asignaciones | `views/asignaciones.php` | Supervisor | Panel por año con selector, cards por registrador, árbol de establecimientos con checkboxes, asignación anual/temporal por meses, copia de año anterior, gestión de referentes por establecimiento |
| eliminadas | `views/eliminadas.php` | Supervisor | Papelera con filtros, tabla de eliminadas con motivo, botones de restauración individual/masiva, eliminación permanente, estadísticas |
| establecimientos | `views/establecimientos.php` | Supervisor | Listado completo con activos/inactivos, stats, CRUD con modal, toggle activo/inactivo, código de establecimiento |

---

## Funcionalidades por Módulo

### Gestión de Observaciones
- CRUD completo con campos: clasificación, detalle error, establecimiento, mes, serie, hoja, tipo de error, plazo de entrega, uso de validador, respuesta del establecimiento
- Importación masiva desde Excel (.xlsx/.xls) con preview (validación previa) y confirmación
- Mapeo inteligente de establecimientos: prioridad por código, fallback por nombre
- Compatibilidad hacia atrás: acepta nombres de columna antiguos (tipo_error, codigo_serie, codigo_hoja)
- Plantilla descargable con hoja de instrucciones y ejemplos
- Filtros por estado, mes, establecimiento y búsqueda de texto
- Exportación a Excel, PDF y CSV
- Historial de cambios de estado por observación
- Papelera de eliminadas con restauración y eliminación permanente

### Supervisión (Selector de Estado)
- Al aprobar, el supervisor elige entre **"Sin Observación"** (→ estado=aprobado, tipo_error=S/OBSERVACION) o **"Error"** (→ estado=error, tipo_error=ERROR)
- Campos adicionales: clasificación (Corregido, Error, Sin respuesta del Establecimiento, Respuesta incorrecta de Establecimiento), Detalle Error
- Operaciones masivas: aprobar/cancelar/eliminar múltiples observaciones
- Modal de detalle con: Serie REM, Hoja REM, Respuesta del Establecimiento, historial completo de cambios

### Dashboard
- Estadísticas en tiempo real (total, pendientes, aprobados, con problemas)
- Gráficos interactivos ApexCharts: distribución por estado (donut), top tipos de error (barra), tendencia por mes (línea)
- Lista de últimas 5 observaciones
- Alertas de asignación: supervisor ve registradores sin establecimientos; registrador ve si no tiene asignaciones
- Acceso rápido a Informe de Errores REM (trimestral/anual)

### Reportes (5 Tabs)
1. **Errores por Establecimiento** — Gráfico horizontal + tabla
2. **Plazos Entrega** — Gráfico de fuera de plazo por establecimiento + tabla con dentro/fuera/total meses
3. **Uso Validador** — Gráfico de no usa validador + tabla con usa/no-usa/total meses
4. **Errores por Serie** — Gráfico horizontal + tabla
5. **Errores por Hoja** — Gráfico vertical + tabla

### Informe de Errores REM
- Generación de informes trimestrales o anuales
- Vista web con paginación (20 registros por página)
- PDF profesional en formato vertical (portrait) con:
  - Logo del Servicio de Salud Osorno
  - Tabla jerárquica Comuna→Establecimiento con rowspan
  - Código de colores por estado (verde/aprobado, amarillo/pendiente, rojo/rechazado)
  - Serie y Hoja REM destacadas en azul institucional (#005288)
  - Sección de firma de la Jefa de Subdepto.

### Asignación de Establecimientos
- Asignación **anual** (titular): establecimientos base para el año completo
- **Reasignación temporal** por meses: vacaciones, licencias, con prioridad sobre anual
- Sin solapamiento: validación de conflictos entre temporales del mismo período
- Vista por año con selector
- Asignación individual y múltiple (checkboxes)
- Fusión de meses si ya existe asignación
- Remoción individual, parcial (por meses) y masiva
- Copiar asignaciones del año anterior (incluye anuales y temporales)
- Gestión de **referentes** por establecimiento: cargo, nombre, teléfono, email

### Gestión de Establecimientos y Referentes
- CRUD completo de establecimientos (nombre, código, comuna)
- Toggle activo/inactivo (soft-delete)
- Listado completo (incluye inactivos para administración)
- Validación de código único
- CRUD de referentes (cargo, nombre, teléfono, email)
- Ordenamiento por cargo (Encargado Estadísticas → Digitador Estadísticas)

### Gestión de Usuarios
- CRUD completo con validación de username único
- Creación con generación de contraseña aleatoria (12 caracteres)
- Política de contraseñas: 8+ caracteres, 1 mayúscula, 1 número
- Cambio de contraseña propia (requiere contraseña actual)
- Reset de contraseña por supervisor (a `admin123`)
- Activar/desactivar usuarios (no a sí mismo)
- Eliminación de usuarios (no a sí mismo)
- Auditoría completa: todas las acciones quedan registradas en `historial_usuarios`

### Versionado (Snapshots)
- Creación de snapshots del código fuente
- Almacenamiento en `uploads/versiones/vXXX/` con manifiesto MD5
- Rollback a cualquier versión anterior
- Listado cronológico con autor

### Seguridad
- Autenticación con sesiones PHP (httponly, secure configurable)
- Permisos basados en roles (supervisor/registrador) verificados en backend
- Protección CSRF: tokens generados por `random_bytes(32)`, validados en todos los endpoints POST/PUT/DELETE
- Contraseñas hasheadas con `password_hash` (bcrypt)
- Consultas preparadas PDO (sin inyección SQL)
- Validación backend de asignaciones (403 si registrador usa establecimiento no asignado para el mes)
- API_BASE dinámica desde `window.location.pathname` (sin rutas hardcodeadas)
- Selector de año en sesión con validación (2020 ~ año_siguiente)
- `error_reporting(E_ERROR | E_PARSE)` en APIs de producción

---

## Series REM Soportadas

| Serie | Hojas |
|-------|-------|
| **SERIE A** | Hoja Nombre, A01–A09, A11, A11a, A19a, A19b, A21, A23–A34, A30ar, Hoja Control, Renombre archivo |
| **SERIE BS** | Hoja Nombre, B, B17, Hoja Control, Renombre archivo |
| **SERIE BM** | Hoja Nombre, BM18, BM18a, Hoja Control, Renombre archivo |
| **SERIE P** | Hoja Nombre, P01–P07, P09, P11–P13, Hoja Control, Renombre archivo |
| **SERIE ANEXO** | Hoja Nombre, Hoja Parto_RN, Hoja S_Infancia, Hoja I.T.S, Hoja Rechazos, Hoja Farmacia, Hoja S_Mental, Hoja S_Adolescencia, Hoja Laboratorio, Hoja Intercultural, Hoja S_Familiar, Hoja Control, Renombre archivo |
| **SERIE D** | Hoja Nombre, D15, D16, Hoja Control, Renombre archivo |

---

## Tipos de Error

| Valor | Significado |
|-------|-------------|
| `S/OBSERVACION` | Sin observación (aprobado) |
| `ERROR` | Error detectado |
| `REVISAR` | Requiere revisión |
| `F/PLAZO` | Fuera de plazo |

---

## Roles y Permisos

### Supervisor
- Panel de supervisión exclusivo con filtros y acciones masivas
- Aprobar observaciones con selector "Sin Observación" / "Error"
- Cancelar y eliminar observaciones (a papelera)
- Ver y restaurar observaciones eliminadas
- Gestionar usuarios (CRUD completo)
- Asignar establecimientos (anual y temporal)
- Gestionar referentes por establecimiento
- Gestionar establecimientos (CRUD + toggle activo)
- Generar Informe de Errores REM (trimestral/anual en PDF)
- Ver todas las observaciones del sistema
- Acceso a versionado (snapshots y rollback)
- Exportar en todos los formatos

### Registrador
- Crear y editar observaciones propias (solo pendientes)
- Importación masiva desde Excel
- Ver solo sus propias observaciones
- Restringido a establecimientos asignados (validación mensual)
- Reportes (solo datos propios)
- Descargar plantilla de importación
- Cambiar su propia contraseña

---

## Seguridad

- **Autenticación:** Sesiones PHP con cookie httponly
- **CSRF:** Token de 32 bytes (bin2hex(random_bytes)) en meta tag + header X-CSRF-TOKEN
- **Contraseñas:** password_hash con bcrypt (PASSWORD_DEFAULT)
- **SQL Injection:** Consultas preparadas PDO (emulate_prepares = false)
- **Validación de asignación:** Backend verifica que registrador tenga el establecimiento asignado para el mes exacto
- **Ruta dinámica:** API_BASE calculada desde `window.location.pathname` (sin hardcodeo)
- **Roles:** Verificación en cada endpoint y vista; redirección a dashboard si no tiene permiso

---

## Instalación

### 1. Requisitos
- PHP >= 7.4
- MySQL >= 5.7
- Apache con mod_rewrite
- XAMPP o similar

### 2. Configurar base de datos
Editar `config/config.php` con credenciales MySQL. Soporta entornos `production` y `development`.

```php
define('ENVIRONMENT', 'production'); // o 'development'
```

### 3. Ejecutar scripts de inicialización
```bash
mysql -h localhost -u root -p < config/init_db.sql
```

### 4. Ejecutar migraciones en orden
```bash
mysql -h localhost -u root -p observaciones_rem < config/create_asignaciones_table.sql
mysql -h localhost -u root -p observaciones_rem < config/sprint3_migration.sql
mysql -h localhost -u root -p observaciones_rem < config/update_establecimientos.sql
mysql -h localhost -u root -p observaciones_rem < config/migration_2026_05_08_limpieza_comunas.sql
mysql -h localhost -u root -p observaciones_rem < config/migration_2026_05_08_reportes.sql
mysql -h localhost -u root -p observaciones_rem < config/migrations/add_tipo_asignacion.sql
```

### 5. Instalar dependencias PHP
```bash
composer install
```

### 6. Acceder al sistema
`http://localhost/ObservacionesREM_V2/`

---

## Migraciones

| Archivo | Fecha | Descripción |
|---------|-------|-------------|
| `init_db.sql` | — | Creación de BD, tablas (usuarios, comunas, establecimientos, observaciones, historial_estados, logs) y datos semilla |
| `create_asignaciones_table.sql` | — | Creación de tabla `asignaciones_establecimientos` |
| `sprint3_migration.sql` | — | Creación de tabla `observaciones_eliminadas` con índices |
| `update_establecimientos.sql` | — | Reemplazo completo de establecimientos (93 registros oficiales DEIS) |
| `migration_2026_02_06.sql` | 2026-02-06 | Documentación de cambio semántico de columnas (serie/tipo/hoja) |
| `migration_2026_05_08_limpieza_comunas.sql` | 2026-05-08 | Unificación de comunas duplicadas (códigos 10001→10301, etc.) |
| `migration_2026_05_08_reportes.sql` | 2026-05-08 | 6 índices compuestos para optimización de reportes |
| `migrations/add_tipo_asignacion.sql` | 2026-05-20 | Agregar columna `tipo_asignacion` (anual/temporal) a asignaciones |

---

## Usuarios del Sistema

### Supervisor
| Usuario | Nombre | Contraseña |
|---------|--------|------------|
| `supervisor1` | Cecilia (Supervisor) | admin123 |

### Registradores
| Usuario | Nombre | Contraseña |
|---------|--------|------------|
| `registrador1` | Rodrigo Garcés | admin123 |
| `registrador2` | Victoria Martínez | admin123 |
| `registrador3` | Roxana Mancilla | admin123 |
| `registrador4` | Marcelo Horstmeier | admin123 |

---

## Manejo de Versiones

### Convención de commits
| Prefijo | Uso |
|---------|-----|
| `feat:` | Nueva funcionalidad |
| `fix:` | Corrección de bug |
| `refactor:` | Refactorización |
| `style:` | Cambios visuales |
| `docs:` | Documentación |
| `chore:` | Mantenimiento |
| `perf:` | Mejora de rendimiento |

### Etiquetado
```bash
git tag -a v2.3.0 -m "Versión 2.3.0"
git push origin v2.3.0
```

### Despliegue
```bash
git archive --format=zip --output=release.zip main
# o git pull en servidor
```

---

## Historial de Versiones

### v2.3.0 — Junio 2026
- **Selector de estado en supervisión:** Al aprobar, supervisor elige "Sin Observación" (estado=aprobado) o "Error" (estado=error, tipo_error=ERROR)
- **Modal detalle:** Nuevos campos visibles — Serie REM, Hoja REM y Respuesta del Establecimiento
- **Informe de Errores REM:** Nuevo endpoint `api/informe_errores.php`, PDF profesional con logo SSO, tabla jerárquica, firma institucional
- **Reportes renovados:** Nueva vista con 5 tabs (Errores, Plazos, Validador, Serie, Hoja) usando ApexCharts
- **Reportes agregados:** Plazo y validador agregado por establecimiento+mes (agregación binaria)
- **Migración `add_tipo_asignacion`:** Columna `tipo_asignacion` (anual/temporal) para asignaciones
- **Gestión de referentes:** CRUD de contactos por establecimiento (cargo, nombre, teléfono, email)
- **Copia de asignaciones:** Copia anual + temporal de un año a otro
- **Asignaciones temporales:** Vista de temporales activas con titular anual
- **Cola de reportes:** Modelo `ReportQueue` para procesamiento asíncrono
- **Auditoría de usuarios:** Modelo `UserAudit` que registra todas las acciones sobre usuarios
- **Refactor gráficos:** Migración de Chart.js a ApexCharts (`charts-apex.js`)
- **Refactor toasts:** Nuevo sistema de notificaciones (`toasts.js`)
- **Override CSS:** Archivo `tabler-override.css` con paleta SSO personalizada

### v2.2.0 — Mayo 2026
- **Limpieza:** Eliminación de 24 archivos de desarrollo/testing/one-time scripts
- **Bugfix logout:** Corrección de ruta API dinámica (evita 404)
- **Nueva vista:** Gestión de establecimientos y referentes (supervisor)
- **Mejora:** API_BASE calculada dinámicamente desde `window.location.pathname`
- **Migración limpieza comunas:** Unificación de comunas duplicadas
- **Migración reportes:** 6 índices compuestos nuevos

### v2.1.0 — Mayo 2026
- **Reportes:** Interfaz tabbed con 6 vistas (General, Errores, Fuera de Plazo, Validador, Serie/Hoja, PDF Detallado)
- **PDF Detallado:** Reporte jerárquico Comuna→Establecimiento→Mes con rowspan, colores, header rojo
- **15 nuevas dimensiones de reportes:** errores/fuera_plazo/validador × mes/comuna/establecimiento
- **Exportación individual:** Botones por sub-reporte
- **Bugfix sesión:** Corrección session_start() en api/export.php

### v2.0.0 — Versión base
- CRUD completo de observaciones
- Supervisión básica
- Asignación de establecimientos
- Importación masiva Excel
- Dashboard con Chart.js
- Exportación Excel/PDF
- Autenticación con roles
- Protección CSRF

---

## Solución de Problemas

### Error de conexión
1. Verificar MySQL ejecutándose
2. Revisar credenciales en `config/config.php`
3. Verificar que existe la base de datos `observaciones_rem`

### Página en blanco
```php
// Temporal en config/config.php
define('ENVIRONMENT', 'development');
```

### "No autenticado" al exportar
Causa: `session_start()` antes de incluir `config.php`. Corregido en v2.1.

### Error 404 en APIs
Causa: Ruta hardcodeada. Corregido en v2.2 con cálculo dinámico.

---

## Licencia

Sistema desarrollado para el **Servicio de Salud Osorno** — Departamento de Estadística (DEGI).
