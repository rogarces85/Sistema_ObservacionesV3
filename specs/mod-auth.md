# Especificación: MOD-AUTH — Autenticación y Sesión

## Historia de Usuario

> **Como** usuario del sistema,
> **necesito** acceder a la plataforma mediante credenciales seguras y definir mi año de trabajo,
> **para** operar dentro del contexto temporal correcto y mantener mi sesión segura.

---

## Descripción General

El módulo de Autenticación gestiona el acceso al sistema. Permite a los usuarios iniciar sesión con nombre de usuario y contraseña, seleccionar el año de trabajo (que filtra los datos visibles) y cerrar sesión de forma segura.

El sistema utiliza **hashing** para proteger las contraseñas y permite sesiones concurrentes sin bloqueo por intentos fallidos.

---

## Funciones del Módulo

### AUTH-001: Iniciar Sesión (Login)

**Descripción**: Valida las credenciales del usuario y establece la sesión.

**Reglas de Negocio**:
- **Credenciales**: `username` y `password`.
- **Año**: El usuario debe seleccionar el año de trabajo (por defecto el año actual).
- **Seguridad**: Las contraseñas se validan comparando el hash almacenado.
- **Bloqueo**: No hay límite de intentos fallidos.

### AUTH-002: Cerrar Sesión (Logout)

**Descripción**: Destruye la sesión activa del usuario.

**Reglas de Negocio**:
- **Impacto**: El usuario es redirigido a la pantalla de login.
- **Datos**: No se pierden datos del sistema, solo la sesión local.

### AUTH-003: Verificar Sesión (Check Session)

**Descripción**: Valida si hay una sesión activa y retorna los datos del usuario.

**Reglas de Negocio**:
- **Uso**: Utilizado por la interfaz para mantener la navegación o redirigir al login si la sesión expiró.

### AUTH-004: Cambiar Año de Trabajo

**Descripción**: Permite cambiar el contexto de año sin cerrar sesión.

**Reglas de Negocio**:
- **Rango**: Años válidos (ej. 2020 hasta año actual + 1).
- **Impacto**: Refresca los datos mostrados en el dashboard y listados para el nuevo año.

---

## Gestión de Sesiones y Cuentas

### Matriz de Permisos

| Función | Todos los Roles |
|---------|:---------------:|
| Iniciar Sesión | ✅ |
| Cerrar Sesión | ✅ |
| Cambiar Año | ✅ |

---

## Mensajes del Sistema

### Mensajes de Éxito

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-AUTH-001 | Login exitoso | `Bienvenido, [Nombre].` |
| MSG-AUTH-002 | Logout exitoso | `Sesión cerrada correctamente.` |
| MSG-AUTH-003 | Año cambiado | `Año de trabajo actualizado a [Año].` |

### Mensajes de Error

| ID | Contexto | Mensaje |
|----|----------|---------|
| MSG-AUTH-101 | Credenciales inválidas | `Error: Usuario o contraseña incorrectos.` |
| MSG-AUTH-102 | Campos vacíos | `Error: Debe ingresar usuario y contraseña.` |
| MSG-AUTH-103 | Año inválido | `Error: El año seleccionado no es válido.` |

---

## Escenarios BDD (Gherkin)

### Escenario: Inicio de sesión exitoso
```gherkin
Dado que soy un usuario registrado
Y estoy en la pantalla de login
Cuando ingreso mi usuario y contraseña correctos
Y selecciono el año "2026"
Entonces el sistema me redirige al Dashboard
Y veo mis datos filtrados para el año 2026
```

### Escenario: Intento de login fallido
```gherkin
Dado que estoy en la pantalla de login
Cuando ingreso una contraseña incorrecta
Entonces el sistema muestra el error "Usuario o contraseña incorrectos"
Y permanezco en la pantalla de login
```

### Escenario: Cambio de año en sesión
```gherkin
Dado que estoy autenticado y trabajando en el año "2026"
Cuando cambio el año a "2025" desde el menú
Entonces el sistema actualiza la interfaz
Y ahora veo los datos correspondientes al año 2025
```

---

## Mockup ASCII

### Pantalla de Login

```
+==============================================================================+
|                                                                              |
|                        SISTEMA OBSERVACIONES REM                             |
|                                                                              |
|  +------------------------------------------------------------------------+  |
|  |  Usuario: [________________]                                           |  |
|  |  Contraseña: [________________]                                        |  |
|  |                                                                        |  |
|  |  Año de trabajo: [ 2026 ▼ ]                                            |  |
|  |                                                                        |  |
|  |                      [ Iniciar Sesión ]                                |  |
|  +------------------------------------------------------------------------+  |
|                                                                              |
+==============================================================================+
```

---

## Resumen de Asunciones Validadas

| # | Asunción | Estado Final |
|---|----------|-------------|
| 1 | Credenciales | ✅ Aceptada → Usuario + Contraseña |
| 2 | Selección de Año | ✅ Aceptada → Se elige al entrar |
| 3 | Cambio de Año | ✅ Aceptada → Dinámico sin re-login |
| 4 | Seguridad | ✅ Aceptada → **Hashing (irreversible)** |
| 5 | Bloqueo por Intentos | ✅ Aceptada → **Sin bloqueo (intentos infinitos)** |
| 6 | Sesiones Concurrentes | ✅ Aceptada → Permitidas |
| 7 | Expiración | ✅ Aceptada → Manual o por servidor |
