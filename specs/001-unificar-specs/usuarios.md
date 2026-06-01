# MOD-USR: Gestión de Usuarios

## Clarifications

### Session 2026-06-01

- Q: ¿Cambio de contraseña propia requiere contraseña actual? → A: Sí. Se debe verificar la contraseña actual antes de permitir el cambio, para validar identidad del usuario.
- Q: ¿historial_usuarios necesita campo usuario_afectado_id? → A: Sí. Agregar columna usuario_afectado_id (INT, FK nullable) para identificar sobre qué usuario se realizó la acción.
- Q: ¿Flag password_reset_required? → A: Sí. Agregar columna TINYINT(1) default 0. Al reset se marca 1. Login redirige forzosamente a cambiar contraseña antes de acceder.
- Q: ¿Proteger último Supervisor? → A: Sí. Bloquear desactivación/eliminación si es el único Supervisor activo en el sistema.
- Q: ¿Formato del username? → A: Solo minúsculas, números y guión bajo. Sin espacios. Mínimo 4, máximo 50 caracteres.

## User Scenarios & Testing

### Historia de Usuario
Como Supervisor, necesito gestionar las cuentas de los usuarios del sistema, para controlar quién tiene acceso y con qué permisos.

### Escenarios de Aceptación

#### HU-USR-001: Listar usuarios
**Prioridad: P1**

```gherkin
Dado un usuario autenticado con rol Supervisor
Cuando accede a la página de gestión de usuarios
Entonces el sistema retorna la lista completa de usuarios vía GET api/users.php
  Y la lista incluye: id, username, nombre_completo, rol, activo
```

#### HU-USR-002: Obtener usuario por ID
**Prioridad: P2**

```gherkin
Dado un usuario autenticado con rol Supervisor
Cuando solicita los datos de un usuario específico vía GET api/users.php?id=N
Entonces el sistema retorna los datos del usuario correspondiente
  O retorna 404 si el ID no existe
```

#### HU-USR-003: Crear usuario
**Prioridad: P1**

```gherkin
Dado un usuario autenticado con rol Supervisor
Cuando crea un nuevo usuario con nombre de usuario, nombre completo, rol y contraseña
  O selecciona la opción de generar contraseña aleatoria (12 caracteres)
Entonces el sistema crea el usuario con contraseña hasheada (bcrypt)
  Y registra la acción como CREACION en historial_usuarios
  Y el nuevo usuario puede iniciar sesión con las credenciales asignadas
```

#### HU-USR-004: Actualizar usuario
**Prioridad: P2**

```gherkin
Dado un usuario autenticado con rol Supervisor
Cuando modifica el nombre o el rol de un usuario existente
Entonces el sistema actualiza los datos vía PUT api/users.php?action=update
  Y registra la acción en historial_usuarios
  Y los cambios se reflejan en la UI en menos de 500ms sin recargar la página
```

#### HU-USR-005: Cambiar contraseña (propia o de otro)
**Prioridad: P1**

```gherkin
Dado un usuario autenticado
Cuando cambia su propia contraseña ingresando la contraseña actual y una nueva que cumple la política de seguridad
  O un Supervisor cambia la contraseña de otro usuario (sin requerir contraseña actual del objetivo)
Entonces el sistema actualiza el hash bcrypt vía PUT api/users.php?action=password
  Y registra la acción como CAMBIO_PASSWORD en historial_usuarios
```

#### HU-USR-006: Restablecer contraseña a valor por defecto
**Prioridad: P2**

```gherkin
Dado un usuario autenticado con rol Supervisor
Cuando restablece la contraseña de otro usuario (no así mismo)
Entonces el sistema asigna la contraseña "admin123" vía PUT api/users.php?action=reset_password
  Y marca password_reset_required = 1
  Y registra la acción en historial_usuarios
```

#### HU-USR-007: Activar/Desactivar usuario
**Prioridad: P1**

```gherkin
Dado un usuario autenticado con rol Supervisor
Cuando cambia el estado activo/inactivo de otro usuario (no así mismo)
Entonces el sistema alterna el campo activo vía PUT api/users.php?action=toggle
  Y registra la acción como ACTIVACION o DESACTIVACION en historial_usuarios
  Y un usuario inactivo no puede iniciar sesión
```

#### HU-USR-008: Eliminar usuario
**Prioridad: P2**

```gherkin
Dado un usuario autenticado con rol Supervisor
Cuando elimina a otro usuario (no así mismo)
Entonces el sistema elimina el registro vía DELETE api/users.php?id=N
  Y registra la acción en historial_usuarios
  O retorna error si el usuario tiene datos asociados (restricción de integridad)
```

### Casos Borde

| Caso | Entrada | Resultado Esperado |
|------|---------|-------------------|
| Supervisor se desactiva a sí mismo | PUT toggle sobre propio ID | Rechazar, no permitir auto-desactivación |
| Supervisor se elimina a sí mismo | DELETE con propio ID | Rechazar, no permitir auto-eliminación |
| Contraseña débil | "abc" sin mayúsculas ni números | Rechazar, debe cumplir política (8+ chars, 1 mayúscula, 1 número) |
| Username duplicado | Crear usuario con username existente | Rechazar, mostrar error de duplicado |
| Username con formato inválido | Crear usuario con username "Nombre Apellido" (espacio) | Rechazar, solo minúsculas, números, guión bajo |
| Registrador accede a gestión | Intentar crear/listar/modificar usuarios | Rechazar, permisos insuficientes (código 403) |
| Reset password sobre sí mismo | Supervisor intenta resetear su propia contraseña | Rechazar, no permitido |
| Eliminar usuario con datos | Usuario tiene registros asociados en el sistema | Rechazar por integridad referencial o solicitar confirmación |
| Desactivar último Supervisor | Intentar desactivar único Supervisor activo | Rechazar, "Debe existir al menos un Supervisor activo en el sistema" |

---

## Requirements

### Requerimientos Funcionales

| ID | Descripción | Método | Endpoint |
|----|------------|--------|----------|
| FR-USR-001 | El sistema debe listar todos los usuarios (solo Supervisor) | GET | api/users.php |
| FR-USR-002 | El sistema debe retornar un usuario por su ID | GET | api/users.php?id=N |
| FR-USR-003 | El sistema debe crear un nuevo usuario con contraseña hasheada (bcrypt) y generar contraseña aleatoria de 12 caracteres si se solicita | POST | api/users.php |
| FR-USR-004 | El sistema debe actualizar nombre y rol de un usuario existente | PUT | api/users.php?action=update |
| FR-USR-005 | El sistema debe permitir cambiar la contraseña: propia requiere contraseña actual + nueva válida; Supervisor a terceros solo requiere nueva válida (sin contraseña actual del objetivo) | PUT | api/users.php?action=password |
| FR-USR-006 | El sistema debe restablecer la contraseña a "admin123" solo si es Supervisor y el objetivo no es él mismo | PUT | api/users.php?action=reset_password |
| FR-USR-007 | El sistema debe alternar el estado activo/inactivo de un usuario, solo Supervisor, no sobre sí mismo | PUT | api/users.php?action=toggle |
| FR-USR-008 | El sistema debe eliminar un usuario, solo Supervisor, no sobre sí mismo | DELETE | api/users.php?id=N |
| FR-USR-009 | El sistema debe registrar todas las acciones de gestión en la tabla historial_usuarios | — | — |
| FR-USR-010 | El sistema debe validar que la contraseña tenga al menos 8 caracteres, 1 mayúscula y 1 número | — | — |
| FR-USR-011 | El sistema debe rechazar operaciones de usuarios con rol Registrador en el módulo de gestión | — | — |

### Entidades Clave

#### usuarios
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT (PK) | Identificador único |
| username | VARCHAR(50) | Nombre de usuario para login (único). Solo minúsculas, números, guión bajo |
| password_hash | VARCHAR(255) | Hash bcrypt de la contraseña |
| nombre_completo | VARCHAR(255) | Nombre real del usuario |
| rol | ENUM('registrador', 'supervisor') | Rol del usuario |
| activo | TINYINT(1) | Estado activo (1) o inactivo (0) |
| password_reset_required | TINYINT(1) | 1=debe cambiar contraseña en próximo login, 0=normal (default 0) |
| fecha_creacion | DATETIME | Fecha de creación (DEFAULT CURRENT_TIMESTAMP) |
| fecha_actualizacion | DATETIME | Fecha de última actualización (ON UPDATE CURRENT_TIMESTAMP) |

#### historial_usuarios
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT (PK) | Identificador único |
| usuario_id | INT (FK) | Referencia al usuario que realizó la acción |
| usuario_afectado_id | INT (FK, nullable) | Referencia al usuario sobre el que se realizó la acción (NULL si es autoacción) |
| accion | VARCHAR(50) | Tipo de acción: CREACION, ACTIVACION, DESACTIVACION, CAMBIO_PASSWORD, etc. |
| detalles | TEXT | Información adicional contextual de la acción |
| fecha_creacion | DATETIME | Fecha de creación (DEFAULT CURRENT_TIMESTAMP) |

### Roles y Permisos

| Acción | Supervisor | Registrador |
|--------|-----------|-------------|
| Listar usuarios | ✓ | ✗ |
| Ver usuario por ID | ✓ | ✗ |
| Crear usuario | ✓ | ✗ |
| Actualizar usuario | ✓ | ✗ |
| Cambiar contraseña (propia) | ✓ | ✓ |
| Cambiar contraseña (terceros) | ✓ | ✗ |
| Resetear contraseña | ✓ (no a sí mismo) | ✗ |
| Activar/Desactivar | ✓ (no a sí mismo) | ✗ |
| Eliminar usuario | ✓ (no a sí mismo) | ✗ |

### Usuarios Iniciales

| Username | Nombre | Rol |
|----------|--------|-----|
| supervisor1 | Cecilia | Supervisor |
| registrador1 | Rodrigo | Registrador |
| registrador2 | Victoria | Registrador |
| registrador3 | Roxana | Registrador |
| registrador4 | Marcelo | Registrador |

---

## Success Criteria

1. Un Supervisor puede listar, crear, editar, activar/desactivar y eliminar usuarios sin errores.
2. Un Registrador no puede acceder a ninguna función de gestión de usuarios (código 403).
3. Las contraseñas se almacenan siempre como hash bcrypt, nunca en texto plano.
4. La política de contraseñas (8+ chars, 1 mayúscula, 1 número) se aplica en creación y cambio de contraseña.
5. La generación de contraseña aleatoria produce strings de 12 caracteres con complejidad suficiente.
6. Un usuario desactivado no puede iniciar sesión en el sistema.
7. Todas las operaciones de gestión quedan registradas en la tabla `historial_usuarios` con la acción y el usuario que la realizó.
8. Un Supervisor no puede desactivarse ni eliminarse a sí mismo.
9. La contraseña "admin123" se asigna correctamente al restablecer, se marca password_reset_required = 1, y el login redirige forzosamente al cambio de contraseña antes de acceder al sistema.
10. El sistema rechaza nombres de usuario duplicados al crear una cuenta.

---

## Assumptions

1. Solo existen dos roles en el sistema: Supervisor (acceso total a gestión) y Registrador (sin acceso a gestión de usuarios).
2. La contraseña por defecto "admin123" es temporal; se espera que el usuario la cambie después del primer inicio de sesión (esto queda fuera del alcance del módulo, como decisión de UI).
3. La tabla `historial_usuarios` es de solo inserción; nunca se modifican ni eliminan registros históricos.
4. La eliminación de un usuario puede fallar por restricciones de integridad referencial si el usuario tiene datos asociados en otras tablas del sistema.
5. Los usuarios iniciales (supervisor1, registrador1-4) se crean durante la instalación o migración inicial del sistema.
6. No hay paginación en la lista de usuarios en la primera versión (se asume una cantidad manejable de cuentas).
7. El username es único e inmutable después de la creación (no se puede cambiar el nombre de usuario).
