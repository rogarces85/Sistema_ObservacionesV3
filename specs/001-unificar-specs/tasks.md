# Tasks: Sistema Observaciones REM

**Creado**: 2026-06-01 | **Plan**: `specs/001-unificar-specs/plan.md`

**Constraints**:
- BD existente y poblada — NO crear migraciones ni modificar esquema
- Sistema desde 0 — ignorar/sobrescribir código existente
- Cada módulo incluye manual de usuario con mockups en `docs/manuales/`

---

## Fase 1: Auth y Sesión (`auth-sesion.md`)

**Dependencias**: Ninguna | **Prioridad**: P1

### T-001: Router principal y estructura base
- [x] Crear `index.php` con router: login check + page routing + permisos
- [x] Crear `config/database.php` con conexión PDO Singleton
- [x] Crear `assets/js/app.js` con `fetchAPI()`, CSRF token handler, utilidades
- [x] Crear `assets/css/tabler-override.css` vacío
- [x] Verificar que `session_start()`, cookie httponly y CSRF token (32 bytes, `bin2hex(random_bytes(32))`) se generan en cada página

### T-002: Login y logout
- [x] Crear `views/auth/login.php` — formulario login con Tabler
- [x] Crear `api/auth.php?action=login` — verificar bcrypt, crear sesión, devolver CSRF
- [x] Crear `api/auth.php?action=logout` — destruir sesión
- [x] Implementar fuerza bruta: 5 intentos → 30s bloqueo por IP
- [x] Mockup: `docs/manuales/auth-login.png` (pantalla login con campos usuario/contraseña)

### T-003: Verificación de sesión y cambio de año
- [x] Crear `api/auth.php?action=check` — verificar sesión activa, devolver usuario + CSRF
- [x] Crear `api/auth.php?action=change_year` — cambiar año de trabajo (rango 2020~currentYear+1)
- [x] Implementar expiración de sesión a los 30 min con modal de advertencia a los 25 min
- [x] CSRF: regenerar token post-POST

### T-004: Manual de usuario
- [x] Crear `docs/manuales/auth-sesion.md` con mockups de: login, bloqueo por intentos, selector de año, modal de expiración

---

## Fase 2: Establecimientos y Referentes (`establecimientos.md`)

**Dependencias**: Ninguna | **Prioridad**: P1

### T-005: Modelo y API de establecimientos
- [x] Crear `models/Establecimiento.php` — listar, crear, actualizar, toggle activo
- [x] Crear `models/Comuna.php` — listar comunas (solo lectura)
- [x] Crear `models/Referente.php` — CRUD referentes por establecimiento
- [x] Crear `api/establecimientos.php` — acciones: listar, crear, actualizar, toggle, estadisticas
- [x] Validar código duplicado en creación y actualización (< 300ms)
- [x] Rechazar registro de observaciones en establecimiento inactivo (backed)

### T-006: Vista de establecimientos
- [x] Crear `views/establecimientos.php` — tabla con código, nombre, comuna, estado, stats
- [x] Crear `assets/js/establecimientos.js` — AJAX, filtros, modal CRUD, toggle
- [x] Implementar filtro por comuna, búsqueda por nombre (backend LIKE)
- [x] Implementar modal de creación/edición con validación frontend
- [x] Orden por código DEIS ascendente, columnas sorteables

### T-007: Gestión de referentes en vista
- [x] Agregar modal de gestión de referentes por establecimiento
- [x] Ordenar referentes: Encargado Estadísticas → Digitador Estadísticas → alfabético
- [x] Validar email y teléfono en frontend y backend
- [x] Mockup: `docs/manuales/establecimientos-lista.png` (tabla con filtros)
- [x] Mockup: `docs/manuales/establecimientos-modal.png` (modal crear/editar)

### T-008: Manual de usuario
- [x] Crear `docs/manuales/establecimientos.md` con mockups y flujo completo

---

## Fase 3: Usuarios (`usuarios.md`)

**Dependencias**: auth-sesion | **Prioridad**: P1

### T-009: Modelo y API de usuarios
- [x] Crear `models/Usuario.php` — CRUD, cambio contraseña, toggle activo
- [x] Crear `models/HistorialUsuario.php` — registrar acciones en `historial_usuarios`
- [x] Crear `api/usuarios.php` — acciones: listar, crear, actualizar, password, reset_password, toggle, eliminar
- [x] Validar username único y formato (solo minúsculas, números, guión bajo)
- [x] Requerir contraseña actual para cambio de contraseña propia
- [x] Bloquear desactivación/eliminación del último Supervisor activo

### T-010: Vista de usuarios
- [x] Crear `views/usuarios.php` — lista de usuarios con tabla
- [x] Crear `assets/js/usuarios.js` — CRUD, cambio contraseña, toggle, eliminación
- [x] Modal de creación con opción "generar contraseña aleatoria" (12 caracteres)
- [x] Modal de cambio de contraseña con validación de política
- [x] No permitir auto-desactivación, auto-eliminación, ni reset password propio
- [x] Mockup: `docs/manuales/usuarios-lista.png`
- [x] Mockup: `docs/manuales/usuarios-crear.png`

### T-011: Contraseña por defecto y reset
- [x] Al crear usuario: hashear bcrypt, marcar `password_reset_required = 1`
- [x] Al reset: asignar "admin123", marcar `password_reset_required = 1`
- [x] En login: si `password_reset_required = 1`, forzar cambio de contraseña

### T-012: Manual de usuario
- [x] Crear `docs/manuales/usuarios.md` con mockups de todas las operaciones

---

## Fase 4: Asignaciones (`asignaciones.md`)

**Dependencias**: establecimientos, usuarios | **Prioridad**: P1

### T-013: Modelo y API de asignaciones
- [x] Crear `models/Asignacion.php` — CRUD, fusión de meses, copia entre años, masiva
- [x] Crear `api/asignaciones.php` — acciones: listar, crear, actualizar, eliminar, copiar, masivo, temporales
- [x] Validar solapamiento de temporales (rechazar si mismo mes ya tiene temporal)
- [x] Fusión de meses: nueva asignación reemplaza si es específica; ALL + lista → nueva lista
- [x] Asignación masiva transaccional (rollback completo si falla)
- [x] Copia entre años: incluye anuales y temporales
- [x] Al remover todos los meses de una asignación, eliminar el registro

### T-014: Vista de asignaciones
- [x] Crear `views/asignaciones.php` — tarjetas por registrador, tabla de establecimientos
- [x] Crear `assets/js/asignaciones.js` — asignación, edición mensual, masiva, copia
- [x] Selector de año, selector de registrador, árbol de establecimientos
- [x] Modal de asignación con checkboxes por mes (1-12) + opción "Todos"
- [x] Indicador visual de temporales activos
- [x] Mockup: `docs/manuales/asignaciones-vista.png`
- [x] Mockup: `docs/manuales/asignaciones-modal.png`

### T-015: Gestión de referentes desde asignaciones
- [x] Integrar modal de referentes (reutilizar T-007) desde vista de asignaciones

### T-016: Manual de usuario
- [x] Crear `docs/manuales/asignaciones.md` con flujo de asignación anual y temporal

---

## Fase 5: Observaciones CRUD (`observaciones.md`)

**Dependencias**: auth-sesion, establecimientos, asignaciones | **Prioridad**: P1

### T-017: Modelo de observaciones
- [x] Crear `models/Observacion.php` — CRUD, listado con filtros, historial, stats, validación de permisos
- [x] Crear `models/HistorialEstado.php` — registrar cambios de estado
- [x] Validar acceso por asignación: registrador solo ve/escribe establecimientos asignados para el mes exacto
- [x] Last-write-wins para edición concurrente
- [x] DELETE físico: eliminar registro sin papelera

### T-018: API de observaciones
- [x] Crear `api/observaciones.php` — acciones: listar, detalle, crear, actualizar, eliminar, historial, stats
- [x] Paginación: 50 registros por página con paginación numerada
- [x] Validar campos obligatorios: mes, establecimiento_id, codigo_serie, tipo_error, etc.
- [x] Formato error: {success:false, error, code}
- [x] 403 si registrador no tiene asignación para establecimiento/mes

### T-019: Vista de observaciones
- [x] Crear `views/observaciones.php` — tabla con filtros (año, mes, estado, establecimiento)
- [x] Crear `assets/js/observaciones.js` — CRUD, filtros, paginación, stats
- [x] Modal de creación/edición con todos los campos del formulario
- [x] Vista de detalle con historial de cambios

### T-020: Manual de usuario
- [x] Crear `docs/manuales/observaciones.md` con mockups de listado, creación, edición, detalle

---

## Fase 6: Supervisión (`supervision.md`)

**Dependencias**: observaciones | **Prioridad**: P1

### T-021: API de supervisión
- [x] Crear `api/supervision.php` — acciones: get_filtered, get_detail, approve, cancel, delete, update_status
- [x] Aprobar con clasificación y estado_resultante
- [x] Cancelar observación con comentario
- [x] Soft delete: mover a `observaciones_eliminadas` (no DELETE físico)
- [x] Operaciones masivas no transaccionales (resumen por ID: procesados/fallos)
- [x] Solo Supervisor (403 para Registrador)

### T-022: Vista de supervisión
- [x] Crear `views/supervision.php` — tabla filtrable con paginación (50/page)
- [x] Crear `assets/js/supervision.js` — acciones individuales/masivas, modales
- [x] Modal de aprobación con clasificación y detalle de error
- [x] Modal de cancelación con comentario
- [x] Mockup: `docs/manuales/supervision-lista.png`
- [x] Mockup: `docs/manuales/supervision-aprobar.png`

### T-023: Manual de usuario
- [x] Crear `docs/manuales/supervision.md` con flujo de supervisión completo

---

## Fase 7: Importación Excel (`importacion.md`)

**Dependencias**: observaciones | **Prioridad**: P1

### T-024: API de importación
- [x] Crear `api/import.php` — acciones: preview, confirm
- [x] Crear `api/import_template.php` — descargar plantilla .xlsx
- [x] PhpSpreadsheet: leer .xlsx/.xls, validar filas, mostrar preview
- [x] Columnas esperadas: mes, codigo_establecimiento, codigo_serie, tipo_error, etc.
- [x] Preview: mostrar filas válidas y errores SIN guardar en BD
- [x] Confirm: insertar solo filas válidas
- [x] Duplicados: detectar y mostrar en preview; usuario decide si importar
- [x] Establecimiento por código DEIS (fallback por nombre)

### T-025: Vista de importación
- [x] Crear `views/importacion.php` — upload + preview + confirm
- [x] Crear `assets/js/importacion.js` — drag & drop file, preview table, confirm
- [x] Mostrar resumen: N filas válidas, M errores
- [x] Botón descargar plantilla
- [x] Mockup: `docs/manuales/importacion-upload.png`
- [x] Mockup: `docs/manuales/importacion-preview.png`

### T-026: Manual de usuario
- [x] Crear `docs/manuales/importacion.md` con flujo de importación paso a paso

---

## Fase 8: Reportes y Exportación (`reportes-exportacion.md`)

**Dependencias**: observaciones, supervision | **Prioridad**: P1

### T-027: API de exportación
- [x] Crear `api/export.php` — formatos: excel, pdf, csv
- [x] Crear `api/informe_errores.php` — Informe Errores REM (trimestral/anual, JSON/PDF)
- [x] Exportación híbrida: ≤1000 registros sync, >1000 queue
- [x] Límite 50,000 registros
- [x] PDF detallado: jerárquico Comuna→Establecimiento→Mes, colores por estado
- [x] CSV con BOM UTF-8 y delimitador punto y coma
- [x] Solo Supervisor para Informe Errores REM

### T-028: Vista de reportes
- [x] Crear `views/reportes.php` — filtros (año, mes, estado, establecimiento) + botones exportación
- [x] Crear `assets/js/reportes.js` — selectores, descarga, vista previa web
- [x] Tabla paginada (20/page) para vista web
- [x] Mockup: `docs/manuales/reportes-vista.png`

### T-029: Manual de usuario
- [x] Crear `docs/manuales/reportes-exportacion.md` con opciones de exportación

---

## Fase 9: Papelera de Eliminadas (`papelera-eliminadas.md`)

**Dependencias**: observaciones, supervision | **Prioridad**: P2

### T-030: API de papelera
- [x] Crear `api/eliminadas.php` — acciones: listar, estadisticas, restaurar, eliminar_permanente, restaurar_masivo, eliminar_permanente_masivo
- [x] Restaurar: MOVE (copiar a observaciones + eliminar de observaciones_eliminadas)
- [x] Eliminar permanente: DELETE directo de observaciones_eliminadas
- [x] Operaciones masivas no transaccionales (reportar fallos por ID)
- [x] Si el registro ya fue restaurado/eliminado por otro → 404

### T-031: Vista de papelera
- [x] Crear `views/papelera.php` — tabla paginada (50/page) con filtros
- [x] Crear `assets/js/papelera.js` — restaurar, eliminar permanente, acciones masivas
- [x] Diálogo de confirmación para eliminación permanente
- [x] Estadísticas: total, por estado original, por mes, por quién eliminó
- [x] Mockup: `docs/manuales/papelera-lista.png`

### T-032: Manual de usuario
- [x] Crear `docs/manuales/papelera-eliminadas.md` con flujo restaurar/eliminar

---

## Fase 10: Dashboard (`dashboard.md`)

**Dependencias**: observaciones, supervision, asignaciones | **Prioridad**: P2

### T-033: API de dashboard
- [x] Crear `api/dashboard/estadisticas.php` — tarjetas: Total, Pendientes, Aprobadas, Problemas
- [x] Crear `api/dashboard/graficos.php` — donut (distribución por estado), barras (tipos de error), líneas (tendencia mensual)
- [x] Crear `api/dashboard/recientes.php` — últimas 5 observaciones
- [x] Crear `api/dashboard/alertas.php` — alertas de asignación (según rol)
- [x] Crear `api/dashboard/sparklines.php` — tendencia 7 días por tarjeta
- [x] Crear `api/dashboard/timeline.php` — actividad reciente
- [x] Crear `api/dashboard/kanban.php` — columnas por estado, drag & drop

### T-034: Vista de dashboard
- [x] Crear `views/dashboard.php` — layout con tarjetas, gráficos ApexCharts, listas, kanban
- [x] Crear `assets/js/dashboard.js` — carga de datos paralela, auto-refresh, pestañas, kanban drag & drop
- [x] Selector de año global que sincroniza todos los componentes
- [x] Auto-refresh configurable (2 min, pausa al interactuar con filtros)
- [x] Skeleton loader mientras cargan datos
- [x] Kanban: pesimista (spinner + esperar confirmación)
- [x] Mockup: `docs/manuales/dashboard-general.png`
- [x] Mockup: `docs/manuales/dashboard-kanban.png`

### T-035: Manual de usuario
- [x] Crear `docs/manuales/dashboard.md` con descripción de cada componente

---

## Fase 11: Versionado y Snapshots (`versionado.md`)

**Dependencias**: Ninguna | **Prioridad**: P2

### T-036: API de versionado
- [ ] Crear `models/VersionSistema.php` — listar, detalle, crear, restaurar
- [ ] Crear `api/versiones.php` — acciones: listar, detalle, crear, restaurar
- [ ] Crear snapshot: copiar archivos (excluyendo node_modules/, .git/, uploads/, vendor/, *.log, *.tmp, assets/cache/, .env)
- [ ] Generar manifiesto MD5 con rutas relativas
- [ ] Versión auto-incremental: v001, v002... v999
- [ ] Restaurar: copiar archivos de vuelta, crear nuevo registro de versión
- [ ] Advertencia en rollback: "Si hay cambios de esquema BD, ejecutar migraciones manualmente"

### T-037: Vista de versionado
- [ ] Crear `views/versionado.php` — lista cronológica de snapshots
- [ ] Crear `assets/js/versionado.js` — crear snapshot, ver detalle, restaurar
- [ ] Modal de creación con campo descripción (obligatorio)
- [ ] Modal de confirmación para restauración
- [ ] Mockup: `docs/manuales/versionado-lista.png`
- [ ] Mockup: `docs/manuales/versionado-crear.png`

### T-038: Manual de usuario
- [ ] Crear `docs/manuales/versionado.md` con flujo de creación y restauración

---

## Tareas Transversales

### T-039: Assets comunes
- [ ] Configurar Tabler Core 1.4, Tabler Icons y ApexCharts 3.45 via CDN en layout principal
- [ ] Crear layout base en `index.php` con sidebar, header, y carga de assets
- [ ] Implementar sistema de toasts/notificaciones global

### T-040: Seguridad global
- [ ] Verificar CSRF en todos los endpoints POST/PUT/DELETE
- [ ] Verificar verificación de rol (supervisor/registrador) en todos los endpoints
- [ ] Verificar consultas preparadas PDO en todos los modelos
- [ ] Verificar rutas dinámicas (sin hardcode)

### T-041: Directorios de manuales
- [ ] Crear carpeta `docs/manuales/`
- [ ] Verificar que todos los mockups sean accesibles desde la UI
