# MOD-AUTH: Autenticación y Sesión

## User Scenarios & Testing

### Historia de Usuario
Como usuario del sistema, necesito acceder mediante credenciales seguras y definir mi año de trabajo, para operar dentro del contexto temporal correcto.

### Escenarios de Aceptación

#### HU-AUTH-001: Inicio de sesión exitoso
**Prioridad: P1**

```gherkin
Dado un usuario registrado con credenciales válidas
  Y el año seleccionado está dentro del rango permitido (2020 ~ {currentYear + 1})
Cuando el usuario ingresa su nombre de usuario y contraseña correctos
  Y presiona "Iniciar Sesión"
Entonces el sistema verifica la contraseña con bcrypt
  Y crea una sesión PHP con los datos del usuario
  Y genera un token CSRF de 32 bytes
  Y redirige al dashboard
  Y muestra el mensaje "Bienvenido, [Nombre]"
```

#### HU-AUTH-002: Inicio de sesión con credenciales incorrectas
**Prioridad: P1**

```gherkin
Dado un usuario registrado
Cuando el usuario ingresa un nombre de usuario o contraseña incorrectos
  Y presiona "Iniciar Sesión"
Entonces el sistema muestra el mensaje "Error: Usuario o contraseña incorrectos"
  Y no se crea ninguna sesión
  Y el usuario permanece en la página de login
```

#### HU-AUTH-003: Cierre de sesión
**Prioridad: P1**

```gherkin
Dado un usuario con sesión activa
Cuando el usuario solicita cerrar sesión
Entonces el sistema destruye la sesión PHP
  Y redirige a la página de login
  Y muestra el mensaje "Sesión cerrada correctamente"
```

#### HU-AUTH-004: Verificación de sesión activa
**Prioridad: P1**

```gherkin
Dado un usuario con sesión activa
Cuando el usuario navega a una página protegida
Entonces el sistema verifica que la sesión esté activa vía GET api/auth.php?action=check
  Y retorna los datos del usuario (nombre, rol, año de trabajo)
  Y permite el acceso al recurso
```

#### HU-AUTH-005: Cambio de año de trabajo
**Prioridad: P2**

```gherkin
Dado un usuario con sesión activa
Cuando el usuario selecciona un nuevo año dentro del rango 2020 ~ {currentYear + 1}
Entonces el sistema actualiza el año de trabajo en la sesión
  Y las operaciones posteriores usan el nuevo contexto temporal
```

#### HU-AUTH-006: Acceso sin sesión activa
**Prioridad: P1**

```gherkin
Dado un usuario sin sesión activa
Cuando intenta acceder a una página protegida
Entonces el sistema redirige a la página de login
  Y no permite el acceso al recurso
```

### Casos Borde

| Caso | Entrada | Resultado Esperado |
|------|---------|-------------------|
| Año fuera de rango | Año < 2020 o año > {currentYear + 1} | Rechazar, mantener año anterior |
| Usuario inactivo | Credenciales válidas, cuenta desactivada | Mostrar mismo error genérico "Error: Usuario o contraseña incorrectos". No revelar que la cuenta está desactivada |
| Fuerza bruta | Múltiples intentos fallidos consecutivos | Bloquear temporalmente 30s después de 5 intentos fallidos. Contador se resetea tras bloqueo o login exitoso |
| Sesión expirada | Sesión PHP expirada por inactividad | A los 25 min mostrar modal: "Su sesión está por expirar. ¿Desea mantenerla abierta?" con botones "Cerrar sesión" / "Mantener abierta". A los 30 min redirigir a login |
| CSRF inválido | Token CSRF incorrecto o ausente | Rechazar petición POST |
| Inyección SQL | Password con caracteres especiales o SQL | Sanitizar entrada, no alterar consulta |

---

## Requirements

### Requerimientos Funcionales

| ID | Descripción | Método | Endpoint |
|----|------------|--------|----------|
| FR-AUTH-001 | El sistema debe autenticar usuarios mediante verificación bcrypt de contraseña | POST | api/auth.php?action=login |
| FR-AUTH-002 | El sistema debe destruir la sesión al cerrar sesión | POST | api/auth.php?action=logout |
| FR-AUTH-003 | El sistema debe verificar el estado de la sesión activa | GET | api/auth.php?action=check |
| FR-AUTH-004 | El sistema debe permitir cambiar el año de trabajo dentro del rango 2020~{currentYear+1} | POST | api/auth.php?action=change_year |
| FR-AUTH-005 | El sistema debe generar un token CSRF de 32 bytes por sesión | — | — |
| FR-AUTH-006 | El sistema debe usar cookies httponly para la sesión | — | — |
| FR-AUTH-007 | El sistema debe rechazar peticiones POST sin token CSRF válido | — | — |
| FR-AUTH-008 | El sistema debe redirigir a login cuando no hay sesión activa | — | — |

### Entidades Clave

No aplica directamente (sesión es volátil, no persiste en BD). Depende de la tabla `usuarios` para verificar credenciales.

---

## Success Criteria

1. Un usuario con credenciales válidas puede iniciar sesión, seleccionar su año de trabajo y acceder al sistema en < 2 segundos.
2. Un intento de inicio de sesión con credenciales inválidas muestra el mensaje de error correspondiente sin exponer si el usuario existe o no.
3. El cierre de sesión destruye la sesión y redirige al login en < 1 segundo.
4. Peticiones POST sin token CSRF válido son rechazadas con código de error 403.
5. El selector de año solo muestra valores en el rango 2020 ~ {currentYear + 1}.
6. Un usuario sin sesión activa no puede acceder a ningún recurso protegido del sistema.
7. Las contraseñas se almacenan exclusivamente usando bcrypt, nunca en texto plano.

---

## Clarifications

### Session 2026-06-01

- Q: ¿Debe haber límite de intentos de login (fuerza bruta)? → A: Implementar bloqueo temporal de 30 segundos después de 5 intentos fallidos. Contador se resetea tras bloqueo o login exitoso.
- Q: ¿El token CSRF se regenera o es estático por sesión? → A: Regenerar después de cada POST exitoso (OWASP best practice).
- Q: ¿Qué mensaje mostrar para usuario inactivo? → A: Mismo error genérico "Error: Usuario o contraseña incorrectos". No revelar que la cuenta está desactivada.
- Q: ¿El rango del selector de año es dinámico o fijo? → A: Dinámico: desde 2020 hasta (año_actual + 1). Se actualiza automáticamente.
- Q: ¿Tiempo de expiración de sesión por inactividad? → A: 30 minutos. A los 25 min mostrar modal "Su sesión está por expirar. ¿Desea mantenerla abierta?" con opciones "Cerrar sesión" / "Mantener abierta".

---

## Assumptions

1. El rango de años permitidos es dinámico: desde 2020 hasta (año_actual + 1). Se actualiza automáticamente cada año sin intervención manual.
2. La sesión PHP expira por inactividad a los 30 minutos. A los 25 minutos se muestra un modal de advertencia con opciones "Cerrar sesión" / "Mantener abierta".
3. El token CSRF se regenera después de cada POST exitoso. Se valida en toda petición POST.
4. El sistema asume que el reloj del servidor está sincronizado con la hora real.
5. Límite de intentos: bloqueo de 30 segundos tras 5 intentos fallidos. El contador se resetea al pasar el bloqueo o al realizar un login exitoso.
6. Todos los roles del sistema (Supervisor, Registrador) pueden autenticarse; no hay restricciones por rol en el módulo de autenticación.
7. La UI del login es responsiva e incluye: campo de usuario, campo de contraseña, selector de año y botón "Iniciar Sesión".
