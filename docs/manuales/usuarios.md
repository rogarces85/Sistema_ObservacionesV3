# Manual de Usuario - Gestión de Usuarios

**Sistema de Observaciones REM - Servicio de Salud Osorno**

**Módulo:** Gestión de Usuarios  
**Rol requerido:** Supervisor  
**Versión:** 2.0.0

---

## 1. Descripción General

El módulo de Gestión de Usuarios permite al Supervisor administrar las cuentas de usuario del sistema. Solo los usuarios con rol **Supervisor** pueden acceder a esta funcionalidad.

### Funcionalidades disponibles:

- Listar todos los usuarios del sistema
- Crear nuevos usuarios
- Editar datos de usuarios (nombre completo y rol)
- Cambiar contraseña propia
- Restablecer contraseña de otros usuarios
- Activar/Desactivar usuarios
- Eliminar usuarios

---

## 2. Acceso al Módulo

1. Iniciar sesión con credenciales de **Supervisor**
2. En el menú lateral, hacer clic en **"Gestión de Usuarios"**

> **Nota:** Si inicia sesión con rol Registrador, esta opción no estará disponible en el menú.

---

## 3. Lista de Usuarios

Al ingresar al módulo, se muestra una tabla con todos los usuarios del sistema.

### Mockup: Lista de Usuarios

```
┌─────────────────────────────────────────────────────────────────────────┐
│  Administración del system                    [+ Nuevo Usuario]         │
│  Gestión de Usuarios                                                      │
├─────────────────────────────────────────────────────────────────────────┤
│  Lista de Usuarios                                                        │
│  ┌───────────────────────────────────────────────────────────────────┐  │
│  │ Mostrar [10 ▼] usuarios    Buscar: [__________________________]   │  │
│  ├──────────┬──────────────────┬──────────┬────────┬────────┬───────┤  │
│  │ Usuario  │ Nombre Completo  │ Rol      │ Estado │ Reset  │ Acc.  │  │
│  ├──────────┼──────────────────┼──────────┼────────┼────────┼───────┤  │
│  │ admin    │ Admin Sistema    │[Supervis]│[Activo]│[No]    │✏️🔓🗑️│  │
│  │ juan_p   │ Juan Pérez       │[Registr] │[Activo]│[Sí]    │✏️🔓🔑🗑️│ │
│  │ maria_l  │ María López      │[Supervis]│[Activo]│[No]    │✏️🔓🔑🗑️│ │
│  │ carlos_r │ Carlos Rodríguez │[Registr] │[Inact.]│[No]    │✏️🔓🔑🗑️│ │
│  └──────────┴──────────────────┴──────────┴────────┴────────┴───────┘  │
│  Total: 4 usuarios                                                      │
└─────────────────────────────────────────────────────────────────────────┘
```

### Columnas de la tabla:

| Columna | Descripción |
|---------|-------------|
| **Usuario** | Nombre de usuario (username) del sistema |
| **Nombre Completo** | Nombre completo del usuario |
| **Rol** | Rol asignado: Supervisor o Registrador |
| **Estado** | Activo (verde) o Inactivo (gris) |
| **Reset Requerido** | Indica si el usuario debe cambiar contraseña al iniciar sesión |
| **Acciones** | Botones de acción disponibles |

### Acciones disponibles por fila:

| Icono | Acción | Disponible para |
|-------|--------|-----------------|
| ✏️ | Editar datos | Todos los usuarios |
| 🔓/🔒 | Activar/Desactivar | Todos excepto uno mismo |
| 🔑 | Restablecer contraseña | Todos excepto uno mismo |
| 🗑️ | Eliminar | Todos excepto uno mismo |

### Búsqueda

Use el campo de búsqueda para filtrar usuarios por:
- Nombre de usuario
- Nombre completo
- Rol

La búsqueda se realiza en tiempo real mientras escribe.

---

## 4. Crear Nuevo Usuario

### Mockup: Modal de Creación

```
┌─────────────────────────────────────────────────────────┐
│  Crear Nuevo Usuario                               [✕]  │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  Nombre de Usuario *                                      │
│  [________________________________]                       │
│  Solo letras minúsculas, números y guión bajo. 4-50 chars│
│                                                          │
│  Nombre Completo *                                        │
│  [________________________________]                       │
│                                                          │
│  Rol *                                                    │
│  [▼ Registrador              ]                            │
│                                                          │
│  ☑ Generar contraseña aleatoria (12 caracteres)          │
│                                                          │
├─────────────────────────────────────────────────────────┤
│           [Cancelar]              [Crear Usuario]         │
└─────────────────────────────────────────────────────────┘
```

### Pasos para crear un usuario:

1. Hacer clic en el botón **"Nuevo Usuario"** (esquina superior derecha)
2. Completar el formulario:

   | Campo | Descripción |
   |-------|-------------|
   | **Nombre de Usuario** | Entre 4 y 50 caracteres. Solo letras minúsculas (a-z), números (0-9) y guión bajo (_). Ejemplo: `juan_perez` |
   | **Nombre Completo** | Nombre completo del usuario. Ejemplo: `Juan Pérez González` |
   | **Rol** | Seleccionar entre "Registrador" o "Supervisor" |
   | **Generar contraseña** | Opción activada por defecto. Genera una contraseña aleatoria de 12 caracteres |

3. Si desmarca "Generar contraseña aleatoria", debe ingresar una contraseña manualmente que cumpla con:
   - Mínimo 8 caracteres
   - Al menos una letra mayúscula
   - Al menos un número

4. Hacer clic en **"Crear Usuario"**

### Después de crear:

- Si se generó contraseña aleatoria, se mostrará en pantalla con opción de copiar al portapapeles
- El usuario nuevo tendrá `password_reset_required = 1`, lo que obliga a cambiar la contraseña en el próximo inicio de sesión
- La acción queda registrada en el historial de auditoría

---

## 5. Editar Usuario

### Pasos para editar:

1. En la tabla de usuarios, hacer clic en el icono ✏️ (editar) del usuario deseado
2. Se abre el modal de edición con los datos actuales
3. El **nombre de usuario** es de solo lectura (no se puede modificar)
4. Modificar:
   - **Nombre Completo**: actualizar el nombre
   - **Rol**: cambiar entre Registrador y Supervisor
5. Hacer clic en **"Guardar Cambios"**

### Restricciones:

- No se puede modificar el nombre de usuario después de creado
- Los cambios se registran en el historial de auditoría

---

## 6. Cambiar Contraseña Propia

> **Nota:** Esta acción está disponible para que el Supervisor cambie su propia contraseña.

### Pasos:

1. Desde el modal de edición del propio usuario, o desde el menú de usuario en la barra superior → "Mi Perfil"
2. Completar el formulario:

   | Campo | Descripción |
   |-------|-------------|
   | **Contraseña Actual** | Su contraseña actual (requerida) |
   | **Nueva Contraseña** | Nueva contraseña (mínimo 8 caracteres, 1 mayúscula, 1 número) |
   | **Confirmar Nueva Contraseña** | Repetir la nueva contraseña |

3. Hacer clic en **"Cambiar Contraseña"**

### Política de contraseñas:

- Mínimo **8 caracteres**
- Al menos **una letra mayúscula** (A-Z)
- Al menos **un número** (0-9)

### Ejemplos válidos:
- `Admin2024!`
- `Supervisor1`
- `MiClave99`

### Ejemplos inválidos:
- `admin123` (falta mayúscula)
- `ADMIN123` (falta número... wait, has number. falta lowercase... actually policy only requires uppercase and number)
- `Admin` (menos de 8 caracteres)
- `adminpass` (falta mayúscula y número)

---

## 7. Restablecer Contraseña de Otro Usuario

> **Nota:** Esta acción restablece la contraseña de otro usuario a `admin123`. No se puede usar con la propia cuenta.

### Pasos:

1. En la tabla de usuarios, hacer clic en el icono 🔑 (restablecer contraseña) del usuario deseado
2. Se muestra un modal de confirmación:

```
┌─────────────────────────────────────────────────────────┐
│  Restablecer Contraseña                            [✕]  │
├─────────────────────────────────────────────────────────┤
│  ⚠️ ¿Está seguro?                                        │
│                                                          │
│  La contraseña del usuario juan_perez será               │
│  restablecida a admin123. El usuario deberá              │
│  cambiarla en su próximo inicio de sesión.               │
│                                                          │
├─────────────────────────────────────────────────────────┤
│           [Cancelar]        [Restablecer Contraseña]     │
└─────────────────────────────────────────────────────────┘
```

3. Hacer clic en **"Restablecer Contraseña"** para confirmar

### Resultado:

- La contraseña del usuario se establece en `admin123`
- Se marca `password_reset_required = 1`
- El usuario deberá cambiar la contraseña en su próximo inicio de sesión
- La acción queda registrada en el historial

---

## 8. Activar/Desactivar Usuario

### Pasos:

1. En la tabla de usuarios, hacer clic en el icono 🔓/🔒 del usuario deseado
2. El estado cambia inmediatamente:
   - Si estaba **Activo** → pasa a **Inactivo**
   - Si estaba **Inactivo** → pasa a **Activo**

### Restricciones:

- **No se puede desactivar la propia cuenta**
- **No se puede desactivar al último Supervisor activo** del sistema (protección para evitar quedar sin supervisores)
- Los usuarios desactivados no pueden iniciar sesión
- La acción queda registrada en el historial

---

## 9. Eliminar Usuario

### Pasos:

1. En la tabla de usuarios, hacer clic en el icono 🗑️ (eliminar) del usuario deseado
2. Se muestra un modal de confirmación:

```
┌─────────────────────────────────────────────────────────┐
│  Eliminar Usuario                                  [✕]  │
├─────────────────────────────────────────────────────────┤
│  🚨 ¿Está seguro de eliminar este usuario?               │
│                                                          │
│  Se eliminará al usuario juan_perez.                     │
│  Esta acción no se puede deshacer.                       │
│                                                          │
├─────────────────────────────────────────────────────────┤
│           [Cancelar]           [Eliminar Usuario]        │
└─────────────────────────────────────────────────────────┘
```

3. Hacer clic en **"Eliminar Usuario"** para confirmar

### Restricciones:

- **No se puede eliminar la propia cuenta**
- **No se puede eliminar al último Supervisor activo** del sistema
- La eliminación es permanente y no se puede deshacer
- La acción queda registrada en el historial

---

## 10. Historial de Auditoría

Todas las acciones realizadas en el módulo de usuarios quedan registradas en la tabla `historial_usuarios`:

| Acción | Descripción |
|--------|-------------|
| **CREACION** | Se creó un nuevo usuario |
| **ACTUALIZACION** | Se modificaron datos de un usuario |
| **CAMBIO_PASSWORD** | Se cambió o reseteó una contraseña |
| **ACTIVACION** | Se activó un usuario |
| **DESACTIVACION** | Se desactivó un usuario |
| **ELIMINACION** | Se eliminó un usuario |

El historial registra:
- Quién realizó la acción (`usuario_id`)
- A quién afectó (`usuario_afectado_id`)
- Tipo de acción (`accion`)
- Detalles adicionales (`detalles`)
- Fecha y hora (`fecha_creacion`)

---

## 11. Contraseña por Defecto y Reset

### Al crear un usuario:

- La contraseña se hashea con **bcrypt**
- Se marca `password_reset_required = 1`
- El usuario **debe cambiar la contraseña** en su primer inicio de sesión

### Al resetear contraseña:

- Se asigna la contraseña `admin123`
- Se marca `password_reset_required = 1`
- El usuario **debe cambiar la contraseña** en su próximo inicio de sesión

### Flujo de primer inicio de sesión:

1. El usuario ingresa con la contraseña temporal
2. El sistema detecta `password_reset_required = 1`
3. Se redirige al formulario de cambio de contraseña obligatorio
4. El usuario ingresa una nueva contraseña que cumpla la política
5. Se marca `password_reset_required = 0`
6. El usuario accede al sistema normalmente

---

## 12. Preguntas Frecuentes

### ¿Puedo cambiar mi propio nombre de usuario?
No. El nombre de usuario es inmutable después de la creación.

### ¿Qué pasa si desactivo a todos los supervisores?
El sistema no permite desactivar al último supervisor activo. Siempre debe haber al menos uno.

### ¿Puedo eliminar un usuario y luego recuperarlo?
No. La eliminación es permanente. Si necesita restaurar un usuario, debe crearlo nuevamente.

### ¿Qué contraseña usar después de un reset?
La contraseña se establece en `admin123`. El usuario debe cambiarla inmediatamente en su próximo inicio de sesión.

### ¿Por qué no veo la opción de eliminar en mi propio usuario?
Por seguridad, no se permite eliminar la propia cuenta. Otro supervisor debe realizar esta acción si es necesario.

---

## 13. Contacto y Soporte

Para consultas o problemas con el módulo de Gestión de Usuarios, contacte al administrador del sistema.

**Sistema de Observaciones REM v2.0.0**  
**Servicio de Salud Osorno**
