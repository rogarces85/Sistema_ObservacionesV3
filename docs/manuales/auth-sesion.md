# Manual de Usuario - Autenticación y Sesión

## Sistema de Observaciones REM - Servicio de Salud Osorno

---

## 1. Pantalla de Login

### Descripción
La pantalla de login permite al usuario autenticarse en el sistema mediante su nombre de usuario y contraseña.

### Mockup

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│           Sistema de Observaciones REM                  │
│           Servicio de Salud Osorno                      │
│                                                         │
│  ┌───────────────────────────────────────────────────┐  │
│  │                                                   │  │
│  │              Iniciar Sesión                       │  │
│  │                                                   │  │
│  │  Nombre de usuario                                │  │
│  │  ┌───────────────────────────────────────────┐    │  │
│  │  │ Ingrese su usuario                        │    │  │
│  │  └───────────────────────────────────────────┘    │  │
│  │                                                   │  │
│  │  Contraseña                                       │  │
│  │  ┌───────────────────────────────────────┬───┐    │  │
│  │  │ Ingrese su contraseña                 │ 👁 │    │  │
│  │  └───────────────────────────────────────┴───┘    │  │
│  │                                                   │  │
│  │  Año de trabajo                                   │  │
│  │  ┌───────────────────────────────────────────┐    │  │
│  │  │ 2025                              ▼       │    │  │
│  │  └───────────────────────────────────────────┘    │  │
│  │                                                   │  │
│  │  ┌───────────────────────────────────────────┐    │  │
│  │  │         🔐 Ingresar                       │    │  │
│  │  └───────────────────────────────────────────┘    │  │
│  │                                                   │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
│     © Servicio de Salud Osorno - Sistema Observaciones  │
└─────────────────────────────────────────────────────────┘
```

### Campos

| Campo | Tipo | Obligatorio | Descripción |
|-------|------|-------------|-------------|
| Nombre de usuario | Texto | Sí | Nombre de usuario asignado por el administrador |
| Contraseña | Password | Sí | Contraseña con política: mín. 8 caracteres, 1 mayúscula, 1 número |
| Año de trabajo | Select | Sí | Año sobre el cual se trabajará (rango: 2020 - año actual + 1) |

### Acciones

- **Ingresar**: Valida credenciales y redirige al dashboard
- **Mostrar/Ocultar contraseña**: Icono de ojo para visualizar la contraseña ingresada

---

## 2. Bloqueo por Intentos Fallidos

### Descripción
El sistema implementa protección contra fuerza bruta. Después de 5 intentos fallidos de login, la IP del usuario es bloqueada por 30 segundos.

### Mockup - Mensaje de Bloqueo

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│           Sistema de Observaciones REM                  │
│           Servicio de Salud Osorno                      │
│                                                         │
│  ┌───────────────────────────────────────────────────┐  │
│  │                                                   │  │
│  │              Iniciar Sesión                       │  │
│  │                                                   │  │
│  │  ┌─────────────────────────────────────────────┐  │  │
│  │  │ ⚠️ Demasiados intentos. Intente nuevamente  │  │  │
│  │  │    en 18 segundos                           │  │  │
│  │  └─────────────────────────────────────────────┘  │  │
│  │                                                   │  │
│  │  Nombre de usuario                                │  │
│  │  ┌───────────────────────────────────────────┐    │  │
│  │  │ Ingrese su usuario                        │    │  │
│  │  └───────────────────────────────────────────┘    │  │
│  │                                                   │  │
│  │  Contraseña                                       │  │
│  │  ┌───────────────────────────────────────┬───┐    │  │
│  │  │ Ingrese su contraseña                 │ 👁 │    │  │
│  │  └───────────────────────────────────────┴───┘    │  │
│  │                                                   │  │
│  │  Año de trabajo                                   │  │
│  │  ┌───────────────────────────────────────────┐    │  │
│  │  │ 2025                              ▼       │    │  │
│  │  └───────────────────────────────────────────┘    │  │
│  │                                                   │  │
│  │  ┌───────────────────────────────────────────┐    │  │
│  │  │         🔐 Ingresar                       │    │  │
│  │  └───────────────────────────────────────────┘    │  │
│  │                                                   │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Comportamiento

1. Cada intento fallido incrementa el contador de intentos para la IP
2. Al alcanzar 5 intentos fallidos, se bloquea el acceso por 30 segundos
3. El mensaje muestra el tiempo restante para poder intentar nuevamente
4. El contador se reinicia tras un login exitoso
5. El bloqueo se limpia automáticamente al expirar el tiempo

### Respuestas de la API

| Código | Mensaje | Descripción |
|--------|---------|-------------|
| 401 | Credenciales inválidas | Usuario o contraseña incorrectos |
| 429 | Demasiados intentos. Intente nuevamente en X segundos | IP bloqueada temporalmente |
| 403 | Usuario desactivado | La cuenta fue desactivada por un administrador |

---

## 3. Selector de Año de Trabajo

### Descripción
El usuario puede seleccionar el año sobre el cual desea trabajar. El selector permite elegir entre 2020 y el año siguiente al actual.

### Mockup - Selector Desplegado

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  Año de trabajo                                         │
│  ┌───────────────────────────────────────────────────┐  │
│  │ 2025                              ▼               │  │
│  │ ┌───────────────────────────────────────────────┐ │  │
│  │ │ 2020                                          │ │  │
│  │ │ 2021                                          │ │  │
│  │ │ 2022                                          │ │  │
│  │ │ 2023                                          │ │  │
│  │ │ 2024                                          │ │  │
│  │ │ 2025  ← seleccionado (año actual)             │ │  │
│  │ │ 2026  ← año siguiente                         │ │  │
│  │ └───────────────────────────────────────────────┘ │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Validaciones

- Rango válido: 2020 hasta (año actual + 1)
- Si se intenta cambiar a un año fuera de rango, la API retorna error 400
- El año seleccionado se almacena en la sesión del usuario
- Se puede cambiar en cualquier momento desde la interfaz principal

### Cambio de Año vía API

```
POST api/auth.php?action=change_year
Body: { "year": 2026, "csrf_token": "abc123..." }

Respuesta exitosa:
{
    "success": true,
    "data": {
        "anio_trabajo": 2026,
        "csrf_token": "nuevo_token..."
    },
    "code": 200
}
```

---

## 4. Modal de Expiración de Sesión

### Descripción
La sesión del usuario expira automáticamente después de 30 minutos de inactividad. A los 25 minutos se muestra un modal de advertencia que permite mantener la sesión activa.

### Mockup - Modal de Advertencia

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  ┌───────────────────────────────────────────────────┐  │
│  │  ⚠️  Sesión por expirar                       ✕   │  │
│  ├───────────────────────────────────────────────────┤  │
│  │                                                   │  │
│  │  Su sesión expirará en 5 minutos por inactividad. │  │
│  │                                                   │  │
│  │  ¿Desea mantener la sesión activa?                │  │
│  │                                                   │  │
│  │                              ┌─────────────────┐  │  │
│  │                              │ Mantener sesión │  │  │
│  │                              │     activa      │  │  │
│  │                              └─────────────────┘  │  │
│  │                                                   │  │
│  └───────────────────────────────────────────────────┘  │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Comportamiento

| Evento | Tiempo | Acción |
|--------|--------|--------|
| Inicio de sesión | 0 min | Se inician los temporizadores |
| Advertencia | 25 min | Aparece modal con opción de renovar |
| Expiración | 30 min | Sesión cerrada, redirección a login |

### Acciones del Usuario

1. **Mantener sesión activa**: Realiza una petición a la API para verificar y renovar la sesión. Los temporizadores se reinician.
2. **No hacer nada**: Después de 5 minutos adicionales, la sesión expira y se redirige al login.

### Notificación Post-Expiración

Cuando la sesión expira, se muestra una notificación:
```
┌──────────────────────────────────────┐
│ ⚠️ Sesión expirada por inactividad ✕ │
└──────────────────────────────────────┘
```

Luego de 2 segundos, se redirige automáticamente a la página de login.

---

## 5. Política de Contraseñas

### Requisitos

| Requisito | Descripción |
|-----------|-------------|
| Longitud mínima | 8 caracteres |
| Mayúsculas | Al menos 1 letra mayúscula |
| Números | Al menos 1 número |

### Almacenamiento

- Las contraseñas se almacenan usando `bcrypt` (password_hash de PHP)
- Nunca se almacenan en texto plano
- La verificación se realiza con `password_verify()`

---

## 6. Seguridad CSRF

### Implementación

- Token de 32 bytes generado con `bin2hex(random_bytes(32))`
- Almacenado en `$_SESSION['csrf_token']` (PHP) y `localStorage` (JavaScript)
- Se envía en el header `X-CSRF-TOKEN` para peticiones POST/PUT/DELETE
- Se regenera después de cada acción POST
- Validación con `hash_equals()` para evitar ataques de timing

---

## 7. Cierre de Sesión

### Proceso

1. El usuario hace clic en "Cerrar sesión"
2. Se envía petición POST a `api/auth.php?action=logout` con token CSRF
3. Se destruye la sesión PHP completamente
4. Se elimina la cookie de sesión
5. Se elimina el token CSRF del localStorage
6. Se redirige a la página de login
