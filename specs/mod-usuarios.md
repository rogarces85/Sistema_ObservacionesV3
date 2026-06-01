# Especificación: MOD-USR — Gestión de Usuarios

## Historia de Usuario

> **Como** Supervisor del sistema,
> **necesito** gestionar las cuentas de usuario (crear, editar, activar, desactivar, eliminar y resetear contraseñas),
> **para** controlar el acceso al sistema y asegurar que cada persona tenga los permisos adecuados.

---

## Descripción General

Este módulo permite a los Supervisores administrar el ciclo de vida de las cuentas de usuario. Incluye la creación de nuevos usuarios, la gestión de sus roles, el control de acceso (activación/desactivación) y la administración de contraseñas.

El sistema implementa una **política de contraseñas estándar** y ofrece la opción de **generación automática de contraseñas seguras**. Además, se mantiene un **historial completo de auditoría** de todos los cambios realizados sobre las cuentas.

---

## Funciones del Módulo

### USR-001: Listar Usuarios

**Descripción**: Muestra la lista de todos los usuarios registrados en el sistema.

**Reglas de Negocio**:
- **Datos visibles**: Username, Nombre Completo, Rol, Estado (Activo/Inactivo), Fecha de Creación.
- **Filtros**: Por rol y por estado.

### USR-002: Crear Usuario

**Descripción**: Registra una nueva cuenta de usuario en el sistema.

**Reglas de Negocio**:
- **Campos obligatorios**: `username`, `nombre_completo`, `rol`.
- **Contraseña**:
  - El supervisor puede ingresar una contraseña manualmente (debe cumplir política estándar).
  - El sistema puede **generar una contraseña aleatoria** automáticamente.
- **Política de Contraseña Estándar**: Mínimo 8 caracteres, al menos una mayúscula y un número.
- **Unicidad**: El `username` debe ser único.
- **Estado inicial**: Activo.

### USR-003: Editar Usuario

**Descripción**: Modifica los datos generales de un usuario existente.

**Reglas de Negocio**:
- **Campos editables**: `nombre_completo`, `rol`.
- **Auditoría**: Se registra en el historial quién realizó el cambio, qué se modificó y cuándo.
- **Restricción**: El `username` no se puede modificar una vez creado.

### USR-004: Cambiar/Resetear Contraseña

**Descripción**: Permite actualizar la contraseña de un usuario.

**Reglas de Negocio**:
- **Cambio por Supervisor**: Puede cambiar la contraseña de cualquier usuario sin conocer la actual.
- **Reset a Default**: Opción para restablecer a `admin123` (se recomienda forzar cambio en próximo login).
- **Generación Aleatoria**: El sistema puede generar una nueva contraseña segura y mostrarla al supervisor para que la entregue al usuario.
- **Cambio por Usuario**: Desde su perfil, el usuario puede cambiar su propia contraseña ingresando la actual.

### USR-005: Activar/Desactivar Usuario

**Descripción**: Cambia el estado de acceso de un usuario.

**Reglas de Regla**:
- **Impacto**: Un usuario desactivado no puede iniciar sesión.
- **Restricción**: Un usuario **NO** puede desactivarse a sí mismo.
- **Auditoría**: Se registra en el historial.

### USR-006: Eliminar Usuario

**Descripción**: Borra permanentemente una cuenta de usuario.

**Reglas de Negocio**:
- **Tipo**: Borrado físico (Hard Delete).
- **Restricción**: Un usuario **NO** puede eliminarse a sí mismo.
- **Advertencia**: Se recomienda desactivar antes de eliminar para mantener trazabilidad.

### USR-007: Historial de Auditoría de Usuario

**Descripción**: Bitácora de cambios realizados sobre las cuentas.

**Reglas de Negocio**:
- **Registra**: Cambios de datos, rol, contraseña, estado (activo/inactivo).
- **Datos**: Fecha, Usuario Responsable, Tipo de Cambio, Detalle (antes/después).
- **Acceso**: Solo Supervisores.

---

## Gestión de Sesiones y Cuentas

### Matriz de Permisos

| Función | Registrador | Supervisor |
|---------|:-----------:|:----------:|
| Listar Usuarios | ❌ No permitido | ✅ |
| Crear Usuario | ❌ No permitido | ✅ |
| Editar Usuario | ❌ No permitido | ✅ |
| Cambiar Contraseña | ✅ Solo propia | ✅ Cualquiera |
| Activar/Desactivar | ❌ No permitido | ✅ (No propio) |
| Eliminar | ❌ No permitido | ✅ (No propio) |
| Ver Historial | ❌ No permitido | ✅ |

---

## Mensajes del Sistema

### Mensajes de Éxito

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-USR-001 | Usuario creado | `Usuario creado exitosamente.` |
| MSG-USR-002 | Usuario editado | `Usuario actualizado exitosamente.` |
| MSG-USR-003 | Contraseña cambiada | `Contraseña actualizada exitosamente.` |
| MSG-USR-004 | Estado cambiado | `Estado del usuario actualizado.` |
| MSG-USR-005 | Usuario eliminado | `Usuario eliminado permanentemente.` |

### Mensajes de Error

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-USR-101 | Campos vacíos | `Error: Todos los campos son requeridos.` |
| MSG-USR-102 | Username duplicado | `Error: El nombre de usuario ya está en uso.` |
| MSG-USR-103 | Política contraseña | `Error: La contraseña debe tener al menos 8 caracteres, una mayúscula y un número.` |
| MSG-USR-104 | Auto-eliminación | `Error: No puede eliminar su propia cuenta.` |
| MSG-USR-105 | Auto-desactivación | `Error: No puede desactivar su propia cuenta.` |
| MSG-USR-106 | Permiso denegado | `Error: No tiene permisos para gestionar usuarios.` |

---

## Escenarios BDD (Gherkin)

### Escenario: Crear usuario con contraseña generada
```gherkin
Dado que soy un Supervisor autenticado
Y estoy en la pantalla de creación de usuarios
Cuando ingreso el username "jperez" y nombre "Juan Pérez"
Y selecciono el rol "Registrador"
Y elijo la opción "Generar contraseña aleatoria"
Y presiono "Crear"
Entonces el usuario se crea exitosamente
Y el sistema muestra la contraseña generada para entregar al usuario
Y se registra en el historial "Usuario creado"
```

### Escenario: Editar usuario y registrar auditoría
```gherkin
Dado que soy un Supervisor autenticado
Y el usuario "mlopez" tiene rol "Registrador"
Cuando edito el usuario y cambio el rol a "Supervisor"
Y guardo los cambios
Entonces el rol de "mlopez" se actualiza a "Supervisor"
Y el historial registra: "Rol cambiado de Registrador a Supervisor por [Supervisor Actual]"
```

### Escenario: Intentar cambiar contraseña que no cumple política
```gherkin
Dado que soy un Supervisor autenticado
Y estoy cambiando la contraseña del usuario "jperez"
Cuando ingreso la contraseña "12345" (muy corta y sin mayúsculas)
Entonces el sistema muestra el error "Error: La contraseña debe tener al menos 8 caracteres, una mayúscula y un número."
Y la contraseña no se actualiza
```

### Escenario: Supervisor intenta desactivarse a sí mismo
```gherkin
Dado que soy un Supervisor autenticado con username "admin"
Y estoy en la lista de usuarios
Cuando intento desactivar mi propia cuenta "admin"
Entonces el sistema muestra el error "Error: No puede desactivar su propia cuenta."
Y mi cuenta permanece activa
```

---

## Mockup ASCII

### Listado de Usuarios

```
+==============================================================================+
|  GESTIÓN DE USUARIOS                                                         |
+==============================================================================+
|  [ + Nuevo Usuario ]                                                         |
|                                                                              |
|  Filtros: [ Todos los Roles ▼ ]  [ Todos los Estados ▼ ]  [ Buscar... ]      |
|                                                                              |
|  +---------+----------------+-------------+----------+-------------------+  |
|  | Username| Nombre Completo| Rol         | Estado   | Acciones          |  |
|  +---------+----------------+-------------+----------+-------------------+  |
|  | admin   | Admin Sistema  | Supervisor  | Activo   | [Editar] [🔑] [🗑️] |  |
|  | jperez  | Juan Pérez     | Registrador | Activo   | [Editar] [🔑] [⏸️] |  |
|  | mlopez  | María López    | Registrador | Inactivo | [Editar] [🔑] [▶️] |  |
|  +---------+----------------+-------------+----------+-------------------+  |
|                                                                              |
+==============================================================================+
```

### Formulario de Nuevo Usuario

```
+==============================================================================+
|  NUEVO USUARIO                                                               |
+==============================================================================+
|                                                                              |
|  Username:           [________________]                                      |
|  Nombre Completo:    [________________]                                      |
|  Rol:                [ Registrador ▼ ]                                       |
|                                                                              |
|  Contraseña:                                                                 |
|  ( ) Ingresar manualmente: [________________]                                |
|  (x) Generar contraseña aleatoria                                            |
|                                                                              |
|  [ Vista previa de contraseña: X7#mP9qL2 (copiar) ]                          |
|                                                                              |
|                          [ Cancelar ]    [ Crear Usuario ]                  |
|                                                                              |
+==============================================================================+
```

### Historial de Auditoría (Usuario)

```
+==============================================================================+
|  HISTORIAL DE AUDITORÍA - Usuario: jperez                                    |
+==============================================================================+
|                                                                              |
|  +---------------------+----------------+----------------------------------+  |
|  | Fecha               | Responsable    | Cambio                           |  |
|  +---------------------+----------------+----------------------------------+  |
|  | 2026-05-13 10:00    | admin          | Contraseña actualizada           |  |
|  | 2026-05-10 09:15    | admin          | Rol: Registrador -> Supervisor   |  |
|  | 2026-05-01 08:00    | admin          | Usuario creado                   |  |
|  +---------------------+----------------+----------------------------------+  |
|                                                                              |
+==============================================================================+
```

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Rol exclusivo | ✅ Aceptada → Solo Supervisores |
| 2 | Política de contraseñas | ✅ Modificada → **Estándar (8+, mayúscula, número) + Generación aleatoria** |
| 3 | Contraseña por defecto | ✅ Aceptada (con nota de forzar cambio si no cumple política) |
| 4 | Eliminación vs Desactivación | ✅ Aceptada |
| 5 | Auto-gestión | ✅ Aceptada (No auto-eliminación/desactivación) |
| 6 | Unicidad de username | ✅ Aceptada |
| 7 | Datos del usuario | ✅ Aceptada |
| 8 | Roles disponibles | ✅ Aceptada |
| 9 | Auditoría | ✅ Modificada → **Historial completo de cambios** |
| 10 | Generación de contraseña | ✅ Nueva → `generateRandomPassword()` genera contraseña aleatoria de 12 caracteres con mayúsculas, minúsculas, números y símbolos |
| 11 | Validación de política | ✅ Nueva → `validatePasswordPolicy()` exige: mínimo 8 caracteres, al menos 1 mayúscula, al menos 1 número |
