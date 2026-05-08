# Reporte de Funcionamiento del Sistema REM

Sistema de gestión de observaciones del Resumen Estadístico Mensual (REM) para el Servicio de Salud Osorno.

**Versión:** 2.1.0 — **Actualizado:** Mayo 2026

---

## 1. Arquitectura General

El sistema utiliza una arquitectura MVC (Modelo-Vista-Controlador) simplificada:

```
Usuario → index.php → Vistas/Modelos → Base de Datos
              ↓
         API REST (api/*.php)
```

### Componentes principales:

| Carpeta | Función |
|---------|---------|
| `api/` | Endpoints REST (11 archivos) |
| `models/` | Lógica de negocio y acceso a datos (7 clases) |
| `views/` | Plantillas PHP (9 vistas) |
| `config/` | Configuración, constantes y migraciones SQL |
| `includes/` | Componentes reutilizables (header, footer, sidebar, CSRF) |
| `assets/` | CSS (BEM) y JavaScript (fetchAPI, Chart.js, notificaciones) |

---

## 2. Flujo de Autenticación

### Paso 1: Acceso al sistema
```
1. Usuario accede a index.php
2. El sistema verifica $_SESSION['logged_in']
3. Si no está autenticado → muestra views/login.php
```

### Paso 2: Inicio de sesión
```
1. Usuario completa formulario (username, password, year)
2. JavaScript envía POST a api/auth.php?action=login
3. API valida credenciales contra tabla 'usuarios'
4. Si es válido → crea variables de sesión:
   - $_SESSION['user_id']
   - $_SESSION['username']
   - $_SESSION['nombre_completo']
   - $_SESSION['rol']
   - $_SESSION['year']
   - $_SESSION['logged_in'] = true
5. Redirige a index.php (dashboard)
```

### Paso 3: Validación de permisos
```php
// En index.php se verifican los roles
if ($page === 'supervision' && $_SESSION['rol'] !== ROL_SUPERVISOR) {
    $page = 'dashboard'; // Redirigir
}
```

### Roles del sistema:
- **Supervisor**: Acceso completo (supervisión, usuarios, asignaciones, aprobaciones, eliminadas)
- **Registrador**: Solo crear/editar sus propias observaciones, restringido a establecimientos asignados

### Páginas protegidas por rol (index.php):
| Página | Supervisor | Registrador |
|--------|:----------:|:-----------:|
| dashboard | ✅ | ✅ |
| observaciones | ✅ | ✅ |
| supervision | ✅ | ❌ → redirect |
| reportes | ✅ | ✅ |
| usuarios | ✅ | ❌ → redirect |
| asignaciones | ✅ | ❌ → redirect |
| eliminadas | ✅ | ❌ → redirect |
| perfil | ✅ | ✅ |

---

## 3. Gestión de Observaciones

### Crear observación

**Paso 1:** Usuario llena formulario en `views/observaciones.php`

**Paso 2:** JavaScript envía POST a `api/observations.php`

**Paso 3:** API valida campos requeridos:
- mes, establecimiento_id, codigo_serie, codigo_hoja
- tipo_error, detalle_observacion, plazo_entrega, usa_validador

**Paso 4:** Modelo `Observation` ejecuta:
```php
INSERT INTO observaciones 
(anio, mes, establecimiento_id, codigo_serie, codigo_hoja, 
 tipo_error, detalle_observacion, plazo_entrega, usa_validador,
 usuario_registro_id, estado_actual, clasificacion, detalle_error)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', ?, ?)
```

**Paso 5:** Se registra automáticamente en `historial_estados`:
```php
INSERT INTO historial_estados 
(observacion_id, estado_anterior, estado_nuevo, usuario_id, comentario)
VALUES (?, '', 'pendiente', ?, 'Registro inicial')
```

### Editar observación

**Reglas de permiso:**
- **Registrador**: Solo puede editar sus propias observaciones en estado "pendiente"
- **Supervisor**: Puede editar cualquier observación

**Proceso:**
1. API recibe PUT con ID
2. Verifica permisos según rol
3. Si cambia de estado, registra en historial
4. Actualiza `fecha_revision = NOW()`

### Eliminar observación

**Solo supervisores** pueden eliminar. Se registra en historial antes de borrar. Las observaciones eliminadas van a la papelera (`observaciones_eliminadas`) y pueden restaurarse desde `?page=eliminadas`.

---

## 4. Sistema de Supervisión

### Flujo de aprobación

**Paso 1:** Supervisor accede a `?page=supervision`

**Paso 2:** Selecciona observaciones pendientes

**Paso 3:** Ejecuta acción "Aprobar":
```
POST api/supervision.php?action=approve
Body: { "id": [1,2,3], "comment": "Revisión completada" }
```

**Paso 4:** Modelo ejecuta:
```php
UPDATE observaciones 
SET estado_actual = 'aprobado', 
    usuario_supervisor_id = ?,
    fecha_revision = NOW()
WHERE id = ?
```

**Paso 5:** Se registra en historial:
```php
INSERT INTO historial_estados 
(observacion_id, estado_anterior, estado_nuevo, usuario_id, comentario)
VALUES (?, 'pendiente', 'aprobado', ?, 'Revisión completada')
```

### Estados de observación

| Estado | Descripción |
|--------|-------------|
| pendiente | aguardando revisión del supervisor |
| aprobado | revisado y aprobado |
| rechazado | no aprobado (requiere justificación) |
| justificado | rechazo justificado por registrador |
| error | requiere corrección |

---

## 5. Importación Masiva

### Proceso de dos pasos

**Paso 1: Preview (previsualización)**
```
Usuario sube archivo Excel/CSV
→ POST api/import.php (preview=1)
→ Sistema valida cada fila:
   - Verifica código establecimiento existe
   - Verifica campos requeridos
→ Retorna resumen:
   {
     "success": true,
     "data": {
       "total": 100,
       "valid": 95,
       "errors": [
         {"row": 5, "message": "Código no encontrado"}
       ]
     }
   }
```

**Paso 2: Confirmación (importación real)**
```
Usuario confirma importación
→ POST api/import.php (confirm=1)
→ Sistema inserta registros válidos
→ Retorna cantidad importada:
   {
     "success": true,
     "imported": 95,
     "message": "Se importaron 95 observaciones correctamente"
   }
```

### Validaciones en importación

- **Establecimiento**: Busca por código primero, luego por nombre
- **Campos requeridos**: mes, tipo (tipo_error)
- **Campos opcionales**: serie, rem, detalle_observacion, plazo_entrega, usa_validador, clasificacion, detalle_error
- **Asignaciones**: Para registradores, valida que el establecimiento esté asignado

---

## 6. Dashboard y Estadísticas

### Cálculo de estadísticas

En `views/dashboard.php`:
```php
$stats = $obsModel->getStats($year, $userId, $userRole);
```

El modelo ejecuta 4 consultas:
1. **Total por estado:** `SELECT estado_actual, COUNT(*) FROM observaciones WHERE anio = ? GROUP BY estado_actual`
2. **Total por mes:** `SELECT mes, COUNT(*) FROM observaciones WHERE anio = ? GROUP BY mes`
3. **Tipos de error más comunes:** `SELECT tipo_error, COUNT(*) FROM observaciones WHERE anio = ? GROUP BY tipo_error ORDER BY total DESC LIMIT 10`
4. **Total general:** `SELECT COUNT(*) FROM observaciones WHERE anio = ?`

### Permisos en estadísticas

- **Registrador**: Solo ve sus propias observaciones (`usuario_registro_id = ?`)
- **Supervisor**: Ve todas las observaciones

---

## 7. Sistema de Reportes (v2.1)

### 7.1 API de Reportes

**Endpoint:** `api/reports.php?report={dimension}&year={YYYY}`

**Dimensiones soportadas (20):**

| Dimensión | Método del Modelo | Filtro SQL |
|-----------|-------------------|------------|
| `mes` | `reportePorMes()` | `GROUP BY mes` |
| `establecimiento` | `reportePorEstablecimiento()` | `GROUP BY establecimiento` |
| `comuna` | `reportePorComuna()` | `GROUP BY comuna` |
| `serie` | `reportePorSerie()` | `GROUP BY codigo_serie` |
| `plazo` | `reportePorPlazo()` | `GROUP BY plazo_entrega` |
| `validador` | `reportePorValidador()` | `GROUP BY usa_validador` |
| `errores_mes` | `reporteErroresPorMes()` | `WHERE tipo_error = 'ERROR' GROUP BY mes` |
| `errores_establecimiento` | `reporteErroresPorEstablecimiento()` | `WHERE tipo_error = 'ERROR' GROUP BY establecimiento` |
| `errores_comuna` | `reporteErroresPorComuna()` | `WHERE tipo_error = 'ERROR' GROUP BY comuna` |
| `fuera_plazo_mes` | `reporteFueraPlazoPorMes()` | `WHERE plazo_entrega = 'fuera_plazo' GROUP BY mes` |
| `fuera_plazo_establecimiento` | `reporteFueraPlazoPorEstablecimiento()` | `WHERE plazo_entrega = 'fuera_plazo' GROUP BY establecimiento` |
| `fuera_plazo_comuna` | `reporteFueraPlazoPorComuna()` | `WHERE plazo_entrega = 'fuera_plazo' GROUP BY comuna` |
| `validador_mes` | `reporteValidadorPorMes()` | `WHERE usa_validador = 'si' GROUP BY mes` |
| `validador_establecimiento` | `reporteValidadorPorEstablecimiento()` | `WHERE usa_validador = 'si' GROUP BY establecimiento` |
| `validador_comuna` | `reporteValidadorPorComuna()` | `WHERE usa_validador = 'si' GROUP BY comuna` |
| `serie_detalle` | `reportePorSerieDetalle()` | `GROUP BY codigo_serie, tipo_error` |
| `hoja_detalle` | `reportePorHojaDetalle()` | `GROUP BY codigo_hoja, tipo_error` |
| `filtros` | `getComunasConDatos()`, `getEstablecimientosConDatos()` | Listas dinámicas para filtros |
| `all` | — | Todas las dimensiones en una respuesta |

**Endpoint especial:** `api/reports.php?report=filtros&year={YYYY}` devuelve comunas y establecimientos con datos para los filtros del PDF detallado.

### 7.2 API de Exportación

**Endpoint:** `api/export.php?format={excel|pdf|csv}&year={YYYY}[&report_type={tipo}]`

**Modos de exportación:**

| report_type | format | Descripción |
|-------------|--------|-------------|
| (ninguno) | excel/pdf/csv | Exportación general de observaciones |
| `detallado` | pdf | PDF jerárquico con filtros (comuna, establecimiento, mes, estado) |
| `errores_mes` | excel | Reporte de errores por mes |
| `errores_establecimiento` | excel | Reporte de errores por establecimiento |
| `errores_comuna` | excel | Reporte de errores por comuna |
| `fuera_plazo_mes` | excel | Reporte fuera de plazo por mes |
| `fuera_plazo_establecimiento` | excel | Reporte fuera de plazo por establecimiento |
| `fuera_plazo_comuna` | excel | Reporte fuera de plazo por comuna |
| `validador_mes` | excel | Reporte uso validador por mes |
| `validador_establecimiento` | excel | Reporte uso validador por establecimiento |
| `validador_comuna` | excel | Reporte uso validador por comuna |
| `serie_detalle` | excel | Reporte serie × tipo error |
| `hoja_detalle` | excel | Reporte por hoja REM |

### 7.3 PDF Detallado Jerárquico

**Método:** `Exporter::exportDetalladoPDF($data, $filename, $filters)`

**Características:**
- Formato horizontal (landscape) A4
- 6 columnas: COMUNAS, ESTABLECIMIENTOS, MES, DETALLE, DETALLE ERROR, ERRORES
- Header en rojo oscuro (#8B1A1A)
- Agrupamiento con `rowspan`:
  - Comuna: rowspan total de sus registros, fondo azul oscuro
  - Establecimiento: rowspan dentro de comuna, fondo azul medio
  - Mes: rowspan dentro de establecimiento, fondo azul claro
- Código de colores por estado:
  - Verde claro (#E8F5E9): aprobado / corregido
  - Naranja claro (#FFF3E0): pendiente / sin respuesta
  - Rojo claro (#FFEBEE): rechazado
  - Azul claro (#E3F2FD): justificado
- Paginación automática cada ~35 filas
- Columna DETALLE: texto completo de `detalle_observacion`
- Columna DETALLE ERROR: valor de `clasificacion` ("Corregido", "Sin respuesta del Establecimiento", etc.)

### 7.4 Optimización de Índices

Migration `config/migration_2026_05_08_reportes.sql`:

```sql
ALTER TABLE observaciones ADD INDEX idx_anio_tipo_error (anio, tipo_error);
ALTER TABLE observaciones ADD INDEX idx_anio_plazo (anio, plazo_entrega);
ALTER TABLE observaciones ADD INDEX idx_anio_validador (anio, usa_validador);
ALTER TABLE observaciones ADD INDEX idx_anio_serie_error (anio, codigo_serie, tipo_error);
ALTER TABLE observaciones ADD INDEX idx_anio_hoja (anio, codigo_hoja);
ALTER TABLE observaciones ADD INDEX idx_anio_estado (anio, estado_actual);
```

---

## 8. Estructura de la Base de Datos

### Tablas principales:

**usuarios**
```sql
id, username, password_hash, nombre_completo, rol, activo, fecha_creacion, fecha_actualizacion
```

**comunas**
```sql
id, codigo_comuna, nombre
```

**establecimientos**
```sql
id, codigo_establecimiento, nombre, nombre_corto, comuna_id, activo
```

**observaciones**
```sql
id, anio, mes, establecimiento_id, codigo_serie, codigo_hoja,
tipo_error, detalle_observacion, plazo_entrega, usa_validador,
estado_actual, usuario_registro_id, usuario_supervisor_id,
respuesta_establecimiento, clasificacion, detalle_error,
fecha_registro, fecha_revision, fecha_actualizacion
```

**historial_estados**
```sql
id, observacion_id, estado_anterior, estado_nuevo,
usuario_id, comentario, fecha_cambio
```

**logs**
```sql
id, usuario_id, accion, detalle, ip_address, user_agent, fecha
```

**asignaciones_establecimientos**
```sql
id, usuario_id, establecimiento_id, anio, fecha_asignacion
UNIQUE KEY (usuario_id, establecimiento_id)
```

**observaciones_eliminadas** (papelera soft-delete)
```sql
Snapshot de observaciones + metadatos de eliminación
```

---

## 9. Seguridad Implementada

### Protección CSRF
```php
// En cada formulario se incluye token
CSRF::generateToken();

// En cada POST/PUT/DELETE se valida
CSRF::validateRequest();
```

### Contraseñas
```php
// Hash con PHP default (bcrypt)
password_hash($password, PASSWORD_DEFAULT);

// Verificación
password_verify($password, $hash);
```

### Consultas preparadas (PDO)
```php
// Previene SQL Injection
$stmt = $this->connection->prepare($sql);
$stmt->execute($params);
```

### Validación de sesión
```php
// Cada API verifica autenticación
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    jsonResponse(false, null, 'No autorizado', 401);
}
```

### Validación de asignaciones
```php
// En api/observations.php (crear/editar)
// Verifica que el establecimiento esté asignado al registrador
if ($userRole === ROL_REGISTRADOR) {
    $asignacion = $asignacionModel->getByUsuarioEstablecimiento($userId, $establecimientoId, $year);
    if (!$asignacion) {
        jsonResponse(false, null, 'El establecimiento no está asignado a su usuario', 403);
    }
}
```

### Gestión de sesión
- `session_start()` se ejecuta **solo** en `config/config.php`
- Las APIs **no** deben llamar `session_start()` directamente
- Configuración: `session.cookie_httponly=1`, `session.use_only_cookies=1`, `session.cookie_secure=0` (HTTP local)

---

## 10. Flujo Completo de Uso

### Escenario: Registrador crea observación
```
1. Login → registrador1/admin123
2. Dashboard → muestra estadísticas personales
3. Nueva Observación → formulario (solo establecimientos asignados)
4. Selecciona: Establecimiento (filtrado), Mes, Serie, Hoja, Tipo error
5. Envía → API valida asignación → BD almacena
6. Lista observaciones → muestra nueva fila
```

### Escenario: Supervisor revisa observaciones
```
1. Login → supervisor1/admin123
2. Supervisión → lista todas pendientes
3. Selecciona observaciones a aprobar
4. Click "Aprobar" → API actualiza estado
5. historial registra cambio
6. Dashboard → actualizado (menos pendientes)
```

### Escenario: Importación masiva
```
1. Descargar plantilla (api/import_template.php)
2. Llenar Excel con datos
3. Subir archivo → Preview
4. Revisar errores reportados
5. Corregir archivo si es necesario
6. Confirmar importación
7. Ver observaciones importadas
```

### Escenario: Generar PDF Detallado
```
1. Ir a Reportes → Tab "PDF Detallado"
2. Seleccionar filtros: Comuna, Establecimiento, Mes, Estado
3. Click "Generar PDF Detallado"
4. GET api/export.php?format=pdf&report_type=detallado&comuna_id=X&...
5. PDF se descarga con agrupamiento jerárquico
```

### Escenario: Exportar sub-reporte
```
1. Ir a Reportes → Tab "Errores"
2. Click "Exportar Excel ↓" en el gráfico deseado
3. GET api/export.php?format=excel&year=2026&report_type=errores_mes
4. Excel se descarga con datos filtrados
```

---

## 11. Rutas y Endpoints

| Método | Endpoint | Descripción |
|--------|-----------|-------------|
| POST | `api/auth.php?action=login` | Iniciar sesión |
| POST | `api/auth.php?action=logout` | Cerrar sesión |
| POST | `api/auth.php?action=change_year` | Cambiar año de sesión |
| GET | `api/observations.php` | Listar observaciones |
| POST | `api/observations.php` | Crear observación |
| PUT | `api/observations.php?id=X` | Actualizar observación |
| DELETE | `api/observations.php?id=X` | Eliminar observación |
| GET | `api/observations.php?action=stats` | Estadísticas dashboard |
| GET | `api/observations.php?action=historial&id=X` | Historial de estados |
| POST | `api/supervision.php?action=approve` | Aprobar observación |
| POST | `api/supervision.php?action=reject` | Rechazar observación |
| POST | `api/supervision.php?action=cancel` | Cancelar aprobación |
| POST | `api/supervision.php?action=delete` | Eliminar observación |
| POST | `api/supervision.php?action=update_status` | Cambiar estado |
| GET | `api/supervision.php?action=get_filtered` | Listar con filtros |
| GET | `api/supervision.php?action=get_detail` | Detalle de observación |
| GET | `api/reports.php?report={dim}&year={Y}` | Datos de reportes (20 dimensiones) |
| GET | `api/reports.php?report=filtros&year={Y}` | Filtros dinámicos (comunas/establecimientos) |
| GET | `api/export.php?format={fmt}&year={Y}` | Exportación general |
| GET | `api/export.php?report_type={tipo}&...` | Exportación específica |
| POST | `api/import.php` | Importar archivo (preview/confirm) |
| GET | `api/import_template.php` | Descargar plantilla CSV |
| GET | `api/locations.php?type=comunas` | Listar comunas |
| GET | `api/locations.php?type=establecimientos` | Listar establecimientos |
| GET | `api/assignments.php` | Listar asignaciones |
| POST | `api/assignments.php?action=asignar` | Asignar establecimiento |
| POST | `api/assignments.php?action=asignar_multiple` | Asignar múltiples |
| POST | `api/assignments.php?action=remover` | Remover asignación |
| POST | `api/assignments.php?action=copiar_anio` | Copiar del año anterior |
| GET | `api/users.php` | Listar usuarios |
| POST | `api/users.php` | Crear usuario |
| PUT | `api/users.php` | Actualizar usuario |
| DELETE | `api/users.php` | Eliminar usuario |
| GET | `api/deleted.php` | Listar eliminadas |
| POST | `api/deleted.php?action=restore` | Restaurar eliminada |
| POST | `api/deleted.php?action=permanent_delete` | Eliminar permanente |

---

## 12. Resumen de Funcionalidades

| Módulo | Acciones |
|--------|----------|
| **Login** | Autenticación, selección año |
| **Dashboard** | Estadísticas, gráficos, últimas observaciones, alertas de asignación |
| **Observaciones** | Crear, editar, listar, filtrar, exportar, historial |
| **Supervisión** | Aprobar, rechazar, cancelar, eliminar, cambiar estado, filtros avanzados |
| **Usuarios** | Crear, editar, activar/desactivar, cambiar contraseña (solo supervisor) |
| **Asignaciones** | Asignar/remover establecimientos por año, copiar año anterior (solo supervisor) |
| **Eliminadas** | Ver papelera, restaurar, eliminar permanente (solo supervisor) |
| **Perfil** | Editar datos, cambiar contraseña |
| **Reportes** | 6 tabs, 15+ gráficos, PDF detallado jerárquico, exportación por sub-reporte |
| **Importación** | Carga masiva desde Excel/CSV con preview |

---

## 13. Historial de Cambios Técnicos

### Mayo 2026 — v2.1
- **api/reports.php**: Extendido de 6 a 20 dimensiones de reporte
- **models/Observation.php**: +14 métodos nuevos de reporte + 2 métodos de filtros
- **models/Exporter.php**: `exportDetalladoPDF()` (jerárquico con rowspan) + `exportErroresExcel()`
- **api/export.php**: Soporte para `report_type` (13 tipos específicos) + fix de sesión
- **views/reportes.php**: Reescrito completamente con 6 tabs, 15+ gráficos, filtros dinámicos
- **assets/css/styles.css**: +45 líneas para tabs de reportes
- **config/migration_2026_05_08_reportes.sql**: 6 índices compuestos nuevos

---

Sistema desarrollado para el Servicio de Salud Osorno.
