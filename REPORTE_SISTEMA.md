# Reporte de Funcionamiento del Sistema REM

Sistema de gestión de observaciones del Resumen Estadístico Mensual (REM) para el Servicio de Salud Osorno.

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
| `api/` | Endpoints REST (controladores) |
| `models/` | Lógica de negocio y acceso a datos |
| `views/` | Plantillas PHP (vistas) |
| `config/` | Configuración y constantes |
| `includes/` | Componentes reutilizables |
| `assets/` | CSS y JavaScript |

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
- **Supervisor**: Acceso completo (supervisión, usuarios, aprobar/rechazar)
- **Registrador**: Solo crear/editar sus propias observaciones

---

## 3. Gestión de Observaciones

### Crear observación

**Paso 1:** Usuario llena formulario en `views/observaciones.php`

**Paso 2:** JavaScript envía POST a `api/observations.php`

**Paso 3:** API valida campos requeridos:
- mes
- establecimiento_id
- codigo_serie
- codigo_hoja
- tipo_error
- detalle_observacion
- plazo_entrega
- usa_validador

**Paso 4:** Modelo `Observation` ejecuta:
```php
INSERT INTO observaciones 
(anio, mes, establecimiento_id, codigo_serie, codigo_hoja, 
 tipo_error, detalle_observacion, plazo_entrega, usa_validador,
 usuario_registro_id, estado_actual)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
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

**Solo supervisores** pueden eliminar. Se registra en historial antes de borrar.

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

---

## 6. Dashboard y Estadísticas

### Cálculo de estadísticas

En `views/dashboard.php`:
```php
$stats = $obsModel->getStats($year, $userId, $userRole);
```

El modelo ejecuta 4 consultas:
1. **Total por estado:**
   ```sql
   SELECT estado_actual, COUNT(*) as total
   FROM observaciones WHERE anio = ?
   GROUP BY estado_actual
   ```

2. **Total por mes:**
   ```sql
   SELECT mes, COUNT(*) as total
   FROM observaciones WHERE anio = ?
   GROUP BY mes
   ```

3. **Tipos de error más comunes:**
   ```sql
   SELECT tipo_error, COUNT(*) as total
   FROM observaciones WHERE anio = ?
   GROUP BY tipo_error
   ORDER BY total DESC LIMIT 10
   ```

4. **Total general:**
   ```sql
   SELECT COUNT(*) as total FROM observaciones WHERE anio = ?
   ```

### Permisos en estadísticas

- **Registrador**: Solo ve sus propias observaciones
- **Supervisor**: Ve todas las observaciones

---

## 7. Estructura de la Base de Datos

### Tablas principales:

**usuarios**
```sql
id, username, password_hash, nombre_completo, rol, activo, fecha_creacion
```

**observaciones**
```sql
id, anio, mes, establecimiento_id, codigo_serie, codigo_hoja,
tipo_error, detalle_observacion, plazo_entrega, usa_validador,
estado_actual, usuario_registro_id, usuario_supervisor_id,
respuesta_establecimiento, clasificacion, detalle_error,
fecha_registro, fecha_revision
```

**historial_estados**
```sql
id, observacion_id, estado_anterior, estado_nuevo,
usuario_id, comentario, fecha_cambio
```

**establecimientos**
```sql
id, nombre, nombre_corto, codigo_establecimiento, comuna_id
```

**comunas**
```sql
id, nombre, codigo_comuna
```

---

## 8. Seguridad Implementada

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

---

## 9. Flujo Completo de Uso

### Escenario: Registrar usuario crea observación

```
1. Login → supervisor1/admin123
2. Dashboard → muestra estadísticas
3. Nueva Observación → formulario
4. Selecciona: Establecimiento, Mes, Serie, REM, Tipo error
5. Envía → API valida → BD almacena
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

---

## 10. Rutas y Endpoints

| Método | Endpoint | Descripción |
|--------|-----------|-------------|
| POST | `api/auth.php?action=login` | Iniciar sesión |
| POST | `api/auth.php?action=logout` | Cerrar sesión |
| GET | `api/observations.php` | Listar observaciones |
| POST | `api/observations.php` | Crear observación |
| PUT | `api/observations.php?id=X` | Actualizar observación |
| DELETE | `api/observations.php?id=X` | Eliminar observación |
| GET | `api/observations.php?action=stats` | Estadísticas |
| POST | `api/supervision.php?action=approve` | Aprobar observación |
| GET | `api/supervision.php?action=get_filtered` | Listar con filtros |
| POST | `api/import.php` | Importar archivo |
| GET | `api/locations.php` | Listar comunas/establecimientos |

---

## 11. Resumen de Funcionalidades

| Módulo | Acciones |
|--------|----------|
| **Login** | Autenticación, selección año |
| **Dashboard** | Estadísticas, gráficos, últimas observaciones |
| **Observaciones** | Crear, editar, listar, filtrar, exportar |
| **Supervisión** | Aprobar, ver historial, filtros avanzados |
| **Usuarios** | Crear, editar, activar/desactivar (solo supervisor) |
| **Perfil** | Editar datos, cambiar contraseña |
| **Reportes** | Exportar a Excel |
| **Importación** | Carga masiva desde Excel/CSV |

---

Sistema desarrollado para el Servicio de Salud Osorno.