# Especificación: Inicio de Sesión en el Sistema

## Historia de Usuario
**Como usuario quiero iniciar sesión en el sistema** para acceder a mis funcionalidades autorizadas.

---

## 1. Descripción General

El sistema debe proporcionar un mecanismo de autenticación seguro que permita a los usuarios registrados acceder al sistema mediante credenciales válidas (usuario y contraseña).

---

## 2. Flujo de Autenticación

### 2.1 Pantalla de Inicio de Sesión
- Existe una página o pantalla dedicada para el inicio de sesión
- El formulario solicita:
  - Nombre de usuario o correo electrónico
  - Contraseña
- Botón para enviar las credenciales

### 2.2 Validación de Credenciales
- El sistema valida que las credenciales sean correctas antes de permitir el acceso
- Si las credenciales son correctas:
  - El usuario es redirigido al panel principal o dashboard
  - Se inicia la sesión del usuario
- Si las credenciales son incorrectas:
  - Se muestra un mensaje de error claro indicando el fallo
  - Se muestra el conteo de intentos restantes (máximo 5 intentos)

### 2.3 Gestión de Intentos Fallidos
- El sistema permite un máximo de **5 intentos fallidos**
- Tras cada intento fallido, se muestra al usuario cuántos intentos le restan
- Tras el **quinto intento fallido**, la cuenta se **bloquea permanentemente**
- Una cuenta bloqueada solo puede ser desbloqueada por un **administrador**

---

## 3. Gestión de Sesión

- La sesión permanece activa hasta que:
  - El usuario cierre sesión manualmente, o
  - El usuario cierre el navegador
- No existe expiración automática por inactividad

---

## 4. Gestión de Cuentas

### 4.1 Creación de Cuentas
- **No existe registro público de usuarios**
- Un **administrador** crea las cuentas manualmente y asigna las credenciales iniciales

### 4.2 Recuperación de Contraseña
- Los usuarios pueden solicitar recuperación de contraseña
- El sistema envía un enlace o código para restablecer la contraseña

### 4.3 Roles de Usuario
- **Usuario estándar:** Acceso a funcionalidades básicas del sistema
- **Administrador:** Acceso a funcionalidades de gestión, incluyendo desbloqueo de cuentas

---

## 5. Cierre de Sesión

- Los usuarios pueden cerrar sesión manualmente en cualquier momento
- Al cerrar sesión, se invalida la sesión activa y se redirige a la pantalla de login

---

## 6. Mensajes del Sistema

| Escenario | Mensaje |
|-----------|---------|
| Credenciales incorrectas | "Usuario o contraseña incorrectos. Intentos restantes: X" |
| Cuenta bloqueada | "Su cuenta ha sido bloqueada. Contacte al administrador." |
| Inicio de sesión exitoso | Redirección al dashboard sin mensaje |
| Contraseña recuperada | "Su contraseña ha sido actualizada exitosamente" |

---

## 7. Escenarios BDD (Gherkin)

### Escenario 1: Inicio de sesión exitoso
```gherkin
Dado que el usuario tiene una cuenta activa con credenciales válidas
Y el usuario se encuentra en la pantalla de inicio de sesión
Cuando ingresa su usuario y contraseña correctos
Y presiona el botón "Iniciar Sesión"
Entonces el sistema valida las credenciales
Y redirige al usuario al dashboard
Y se inicia una sesión activa
```

### Escenario 2: Inicio de sesión con credenciales incorrectas
```gherkin
Dado que el usuario se encuentra en la pantalla de inicio de sesión
Cuando ingresa un usuario o contraseña incorrectos
Y presiona el botón "Iniciar Sesión"
Entonces el sistema muestra un mensaje de error "Usuario o contraseña incorrectos. Intentos restantes: X"
Y el contador de intentos fallidos se incrementa
Y el usuario permanece en la pantalla de inicio de sesión
```

### Escenario 3: Bloqueo de cuenta tras 5 intentos fallidos
```gherkin
Dado que el usuario ha fallado 4 intentos consecutivos de inicio de sesión
Cuando ingresa credenciales incorrectas por quinta vez
Y presiona el botón "Iniciar Sesión"
Entonces el sistema bloquea permanentemente la cuenta
Y muestra el mensaje "Su cuenta ha sido bloqueada. Contacte al administrador."
Y el usuario no puede intentar iniciar sesión nuevamente
```

### Escenario 4: Cierre de sesión manual
```gherkin
Dado que el usuario tiene una sesión activa en el sistema
Cuando selecciona la opción "Cerrar Sesión"
Entonces el sistema invalida la sesión activa
Y redirige al usuario a la pantalla de inicio de sesión
```

### Escenario 5: Sesión activa tras cerrar navegador
```gherkin
Dado que el usuario tiene una sesión activa en el sistema
Cuando cierra el navegador
Y vuelve a abrir el navegador y accede al sistema
Entonces el sistema le solicita iniciar sesión nuevamente
```

### Escenario 6: Recuperación de contraseña
```gherkin
Dado que el usuario se encuentra en la pantalla de inicio de sesión
Cuando selecciona la opción "¿Olvidaste tu contraseña?"
Y proporciona su correo electrónico registrado
Entonces el sistema envía un enlace de recuperación al correo proporcionado
```

### Escenario 7: Creación de cuenta por administrador
```gherkin
Dado que un administrador ha iniciado sesión en el sistema
Cuando accede a la sección de gestión de usuarios
Y crea un nuevo usuario con credenciales iniciales
Entonces el nuevo usuario puede iniciar sesión con las credenciales asignadas
```

### Escenario 8: Desbloqueo de cuenta por administrador
```gherkin
Dado que un administrador ha iniciado sesión en el sistema
Y existe un usuario con la cuenta bloqueada
Cuando el administrador selecciona la opción de desbloquear cuenta
Entonces la cuenta del usuario es desbloqueada
Y el usuario puede intentar iniciar sesión nuevamente
```

---

## 8. Mockup ASCII - Pantalla de Inicio de Sesión

### Estado Inicial

```
+--------------------------------------------------+
|                                                  |
|                  [ LOGO ]                        |
|                                                  |
|           Bienvenido al Sistema                  |
|                                                  |
|  +--------------------------------------------+  |
|  |                                            |  |
|  |  Usuario o Correo Electrónico              |  |
|  |  [____________________________________]    |  |
|  |                                            |  |
|  |  Contraseña                                |  |
|  |  [____________________________________]    |  |
|  |                                            |  |
|  |         [  Iniciar Sesión  ]               |  |
|  |                                            |  |
|  |      ¿Olvidaste tu contraseña?             |  |
|  |                                            |  |
|  +--------------------------------------------+  |
|                                                  |
+--------------------------------------------------+
```

### Estado con Error (Intento Fallido)

```
+--------------------------------------------------+
|                                                  |
|                  [ LOGO ]                        |
|                                                  |
|           Bienvenido al Sistema                  |
|                                                  |
|  +--------------------------------------------+  |
|  |                                            |  |
|  |  Usuario o Correo Electrónico              |  |
|  |  [juan.perez                           ]    |  |
|  |                                            |  |
|  |  Contraseña                                |  |
|  |  [************                        ]    |  |
|  |                                            |  |
|  |  [!] Usuario o contraseña incorrectos.     |  |
|  |      Intentos restantes: 3                 |  |
|  |                                            |  |
|  |         [  Iniciar Sesión  ]               |  |
|  |                                            |  |
|  |      ¿Olvidaste tu contraseña?             |  |
|  |                                            |  |
|  +--------------------------------------------+  |
|                                                  |
+--------------------------------------------------+
```

### Estado con Cuenta Bloqueada

```
+--------------------------------------------------+
|                                                  |
|                  [ LOGO ]                        |
|                                                  |
|           Bienvenido al Sistema                  |
|                                                  |
|  +--------------------------------------------+  |
|  |                                            |  |
|  |  Usuario o Correo Electrónico              |  |
|  |  [juan.perez                           ]    |  |
|  |                                            |  |
|  |  Contraseña                                |  |
|  |  [************                        ]    |  |
|  |                                            |  |
|  |  [X] Su cuenta ha sido bloqueada.          |  |
|  |      Contacte al administrador.            |  |
|  |                                            |  |
|  |         [  Iniciar Sesión  ] (Deshabilitado)|  |
|  |                                            |  |
|  |      ¿Olvidaste tu contraseña?             |  |
|  |                                            |  |
|  +--------------------------------------------+  |
|                                                  |
+--------------------------------------------------+
```
