# Especificación: Módulo de Observaciones

## Historia de Usuario

> **Como** usuario del sistema Observaciones (Registrador o Supervisor),
> **necesito** gestionar el ciclo de vida completo de las observaciones REM (crear, listar, ver, editar, eliminar y analizar),
> **para** documentar incidencias, supervisar su resolución y obtener estadísticas sobre la calidad de los reportes.

---

## Descripción General

El módulo de Observaciones es el núcleo del sistema. Permite registrar incidencias encontradas en los reportes REM, gestionar su estado (Pendiente, Aprobado, Rechazado), y generar reportes estadísticos.

El sistema opera con dos roles principales con permisos diferenciados:
- **Registrador**: Crea y gestiona sus propias observaciones.
- **Supervisor**: Supervisa, aprueba/rechaza, elimina y ve todas las observaciones del sistema.

---

## Funciones del Módulo

### OBS-001: Listar Observaciones

**Descripción**: Consulta la lista de observaciones con filtros implícitos según el rol del usuario.

**Reglas de Negocio**:
- **Filtro por rol**: El Registrador solo ve sus propias observaciones. El Supervisor ve todas las del año activo.
- **Ordenamiento**: Fecha de registro descendente (más recientes primero).
- **Paginación**: La lista es paginada para optimizar el rendimiento.
- **Columnas visibles**: ID, Mes, Establecimiento, Tipo de Error, Estado Actual, Fecha de Registro.

**Flujo de Trabajo**:
```
1. Usuario accede a la lista.
2. Sistema verifica el rol.
3. Sistema consulta la BD aplicando filtro (usuario_id si es Registrador).
4. Sistema ordena por fecha DESC y aplica paginación.
5. Sistema retorna la lista de observaciones.
```

---

### OBS-002: Ver Detalle de Observación

**Descripción**: Muestra toda la información de una observación específica y sus datos relacionados.

**Reglas de Negocio**:
- **Vista completa**: Muestra todos los campos + datos de Comuna, Establecimiento, Registrador y Supervisor (si aplica).
- **Restricción de acceso**: El Registrador solo puede ver el detalle de sus propias observaciones.

**Flujo de Trabajo**:
```
1. Usuario selecciona una observación.
2. Sistema verifica permisos de acceso.
3. Sistema obtiene el detalle completo y datos relacionados.
4. Sistema muestra la información en pantalla.
```

---

### OBS-003: Crear Observación

*(Especificación detallada disponible en `obs-crear-observacion.md`)*

**Resumen**:
- **Quién**: Solo Registradores.
- **Validación**: Campos obligatorios + Establecimiento asignado al usuario.
- **Duplicados**: Permitidos sin validación (criterio del usuario).
- **Estado Inicial**: "Pendiente".
- **Historial**: Se genera entrada automática "Registro inicial".

---

### OBS-004: Actualizar Observación

**Descripción**: Permite modificar los datos de una observación existente o cambiar su estado.

**Reglas de Negocio**:
- **Permisos de edición**:
  - **Registrador**: Solo puede editar sus propias observaciones y **únicamente** si están en estado "Pendiente".
  - **Supervisor**: Puede editar cualquier observación y cambiar su estado.
- **Historial**: Si se cambia el estado, se registra automáticamente en el historial con el comentario del cambio. La edición de otros campos no genera entrada en el historial.

**Flujo de Trabajo**:
```
1. Usuario envía datos modificados.
2. Sistema verifica permisos (¿Es dueño? ¿Está pendiente? O ¿Es Supervisor?).
3. Sistema actualiza los campos en la BD.
4. Si el estado cambió, el sistema registra entrada en historial.
5. Sistema confirma actualización.
```

---

### OBS-005: Eliminar Observación

**Descripción**: Elimina una observación del sistema.

**Reglas de Negocio**:
- **Permisos**: Solo Supervisores.
- **Tipo de eliminación (Híbrido)**:
  - **Vía API Principal**: Borrado físico (Hard Delete).
  - **Vía Supervisión**: Borrado lógico (Soft Delete) -> Mueve a la papelera de reciclaje.

**Flujo de Trabajo**:
```
1. Supervisor solicita eliminar observación.
2. Sistema verifica rol (debe ser Supervisor).
3. Sistema determina el origen de la solicitud:
   - Si es API -> Ejecuta DELETE físico.
   - Si es Supervisión -> Inserta en tabla de eliminadas y borra de tabla principal.
4. Sistema confirma eliminación.
```

---

### OBS-006: Ver Historial

**Descripción**: Muestra la bitácora de cambios de estado de una observación.

**Reglas de Negocio**:
- **Contenido**: Fecha, Usuario, Estado Anterior, Estado Nuevo, Comentario.
- **Orden**: Cronológico (más recientes primero).
- **Acceso**: Mismas reglas que OBS-002 (Registrador ve historial de las suyas, Supervisor ve todo).

---

### OBS-007: Estadísticas (Dashboard)

**Descripción**: Genera métricas agregadas para el dashboard principal.

**Reglas de Negocio**:
- **Métricas incluidas**:
  1. Total general de observaciones.
  2. Conteo por Estado.
  3. Conteo por Mes.
  4. Top 10 Tipos de Error.
- **Filtro por rol**: Las estadísticas del Registrador reflejan solo sus propios datos.

---

## Gestión de Sesiones y Cuentas

### Matriz de Permisos

| Función | Registrador | Supervisor |
|---------|:-----------:|:----------:|
| OBS-001 Listar | ✅ Solo propias | ✅ Todas |
| OBS-002 Ver Detalle | ✅ Solo propias | ✅ Todas |
| OBS-003 Crear | ✅ Solo est. asignados | ❌ No permitido |
| OBS-004 Actualizar | ✅ Solo propias y Pendientes | ✅ Todas + Cambio Estado |
| OBS-005 Eliminar | ❌ No permitido | ✅ (Hard o Soft según origen) |
| OBS-006 Historial | ✅ Solo propias | ✅ Todas |
| OBS-007 Estadísticas | ✅ Solo propias | ✅ Todas |

---

## Mensajes del Sistema

### Mensajes de Éxito

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-OBS-001 | Listar | `Se encontraron [N] observaciones.` |
| MSG-OBS-003 | Crear | `Observación registrada exitosamente. ID: [ID]` |
| MSG-OBS-004 | Actualizar | `Observación actualizada exitosamente.` |
| MSG-OBS-005 | Eliminar | `Observación eliminada exitosamente.` |

### Mensajes de Error

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-OBS-101 | Campos vacíos | `Error: El campo [nombre] es requerido.` |
| MSG-OBS-102 | Est. no asignado | `Error: Establecimiento no asignado a su usuario.` |
| MSG-OBS-103 | Permiso denegado | `Error: No tiene permisos para realizar esta acción.` |
| MSG-OBS-104 | Edición bloqueada | `Error: Solo puede editar observaciones pendientes.` |
| MSG-OBS-105 | No encontrada | `Error: La observación solicitada no existe.` |

---

## Escenarios BDD (Gherkin)

### Escenario: Registrador crea observación válida
```gherkin
Dado que soy un Registrador autenticado
Y tengo asignado el establecimiento "Hospital San José"
Cuando creo una observación para "Hospital San José" con datos válidos
Entonces la observación se crea con estado "Pendiente"
Y se registra en el historial "Registro inicial"
```

### Escenario: Registrador intenta editar observación aprobada
```gherkin
Dado que soy un Registrador autenticado
Y tengo una observación en estado "Aprobado"
Cuando intento modificar el detalle de esa observación
Entonces el sistema muestra el error "Error: Solo puede editar observaciones pendientes."
Y no se realizan cambios
```

### Escenario: Supervisor elimina observación desde Supervisión
```gherkin
Dado que soy un Supervisor autenticado
Y estoy en la pantalla de Supervisión
Cuando elimino una observación
Entonces la observación se mueve a la papelera de reciclaje
Y ya no aparece en la lista principal
```

### Escenario: Dashboard muestra estadísticas filtradas
```gherkin
Dado que soy un Registrador autenticado
Y existen observaciones de otros usuarios en el sistema
Cuando accedo al Dashboard
Entonces las estadísticas muestran solo mis observaciones
Y el total no incluye datos de otros usuarios
```

---

## Mockup ASCII

### Listado de Observaciones

```
+==============================================================================+
|  LISTADO DE OBSERVACIONES - Año 2026                                         |
+==============================================================================+
|  [ + Nueva Observación ]      [ Filtros: Mes ▼ | Estado ▼ | Buscar... ]      |
+------+-------+------------------+-------------+-----------+------------------+
|  ID  |  Mes  | Establecimiento  | Tipo Error  | Estado    | Fecha Registro   |
+------+-------+------------------+-------------+-----------+------------------+
| 1045 | Marzo | Hospital San Jos | ERROR       | Pendiente | 2026-05-13 10:00 |
| 1044 | Marzo | CESFAM Norte     | OBSERVACIÓN | Aprobado  | 2026-05-12 09:15 |
| 1043 | Feb   | Hospital San Jos | ERROR       | Rechazado | 2026-05-10 16:48 |
| ...  | ...   | ...              | ...         | ...       | ...              |
+------+-------+------------------+-------------+-----------+------------------+
|  Mostrando 1-10 de 45 registros        [ < Anterior ] [ 1 ] [ 2 ] [ 3 ]     |
+==============================================================================+
```

### Detalle de Observación

```
+==============================================================================+
|  DETALLE DE OBSERVACIÓN #1045                                                |
+==============================================================================+
|                                                                              |
|  ESTADO ACTUAL: [ PENDIENTE ]                                                |
|                                                                              |
|  INFORMACIÓN BÁSICA                                                          |
|  +-----------------------------+------------------------------------------+  |
|  | Año:          2026          | Mes:               Marzo                 |  |
|  | Establecimiento:            | Hospital San José                        |  |
|  | Comuna:                     | Santiago                                 |  |
|  | Registrador:                | Juan Pérez                               |  |
|  +-----------------------------+------------------------------------------+  |
|                                                                              |
|  DETALLE DEL REPORTE                                                         |
|  +-----------------------------+------------------------------------------+  |
|  | Serie REM:        REM-12    | Hoja REM:            HOJA-01             |  |
|  | Tipo:             ERROR     | Plazo Entrega:       A tiempo            |  |
|  | Usa Validador:    Si        |                                          |  |
|  +-----------------------------+------------------------------------------+  |
|                                                                              |
|  OBSERVACIÓN                                                                 |
|  +------------------------------------------------------------------------+  |
|  | Falta información en la columna de egresos del mes de marzo.           |  |
|  +------------------------------------------------------------------------+  |
|                                                                              |
|  HISTORIAL DE CAMBIOS                                                        |
|  +------------------------------------------------------------------------+  |
|  | 2026-05-13 10:00 | Juan Pérez | - -> Pendiente | Registro inicial      |  |
|  +------------------------------------------------------------------------+  |
|                                                                              |
|                          [ Volver ]    [ Editar ]    [ Eliminar ]           |
|                                                                              |
+==============================================================================+
```

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Filtro por rol en lista | ✅ Aceptada (Registrador: propias, Supervisor: todas) |
| 2 | Ordenamiento | ✅ Aceptada (Fecha DESC) |
| 3 | Paginación | ✅ Aceptada |
| 4 | Columnas del listado | ✅ Aceptada (Resumen) |
| 5 | Vista completa detalle | ✅ Aceptada |
| 6 | Restricción acceso detalle | ✅ Aceptada |
| 7 | Permisos edición | ✅ Aceptada (Registrador: propio/pendiente, Supervisor: todo) |
| 8 | Historial solo en estado | ✅ Aceptada |
| 9 | Tipo de eliminación | ✅ Aceptada → **Híbrido** (API: Hard, Supervisión: Soft/Papelera) |
| 10 | Solo supervisores eliminan | ✅ Aceptada |
| 11 | Contenido historial | ✅ Aceptada (Cronológico, usuario, estados, comentario) |
| 12 | Acceso historial | ✅ Aceptada |
| 13 | Métricas Dashboard | ✅ Aceptada → **Completas** (Total, Estado, Mes, Top 10 Errores) |
| 14 | Filtro rol en stats | ✅ Aceptada |
