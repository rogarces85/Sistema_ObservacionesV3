## ADDED Requirements

### Requirement: Script de seed de usuarios demo

El sistema SHALL incluir un script `seed_demo.php` en la raíz del proyecto que cree o restablezca usuarios de prueba. El script SHALL crear dos usuarios demo: un registrador y un supervisor. El script SHALL usar `password_hash()` con bcrypt para generar los hashes, consistente con el modelo existente `User::create()`.

#### Scenario: Ejecutar seed_demo.php en entorno development

- **WHEN** el script `seed_demo.php` se ejecuta via CLI (`php seed_demo.php`)
- **WHEN** la constante `ENVIRONMENT` está configurada como `'development'`
- **THEN** el script crea (o actualiza) los usuarios demo en la base de datos
- **THEN** el script muestra un mensaje de éxito con los usuarios creados

#### Scenario: Ejecutar seed_demo.php en entorno production

- **WHEN** el script `seed_demo.php` se ejecuta
- **WHEN** la constante `ENVIRONMENT` está configurada como `'production'`
- **THEN** el script NO modifica la base de datos
- **THEN** el script muestra un mensaje de error y termina

### Requirement: Usuarios demo con credenciales conocidas

El sistema SHALL crear los siguientes usuarios demo cuando se ejecuta `seed_demo.php`:

| Username | Rol | Contraseña | Nombre |
|----------|-----|-----------|--------|
| `demo_registrador` | registrador | `Demo1234` | Demo Registrador |
| `demo_supervisor` | supervisor | `Demo1234` | Demo Supervisor |

Si los usuarios ya existen (mismo username), el script SHALL actualizar su contraseña y asegurarse de que estén activos, sin crear duplicados.

#### Scenario: Usuarios demo creados correctamente

- **WHEN** el script `seed_demo.php` se ejecuta en desarrollo
- **THEN** existe un usuario `demo_registrador` con rol `registrador` y activo
- **THEN** existe un usuario `demo_supervisor` con rol `supervisor` y activo
- **THEN** ambos usuarios pueden iniciar sesión con la contraseña `Demo1234`

#### Scenario: Re-ejecutar seed_demo.php no crea duplicados

- **WHEN** el script `seed_demo.php` se ejecuta dos veces en el mismo entorno
- **THEN** no se crean usuarios duplicados
- **THEN** las contraseñas se actualizan a las credenciales demo conocidas
- **THEN** los usuarios permanecen activos

### Requirement: Migración SQL alternativa

El sistema SHALL incluir un archivo `config/demo_users.sql` con los INSERTs necesarios para crear los usuarios demo, como alternativa al script PHP. El hash bcrypt para `Demo1234` SHALL estar pre-generado para que funcione directamente en la base de datos.

#### Scenario: Ejecutar SQL directamente

- **WHEN** el archivo `config/demo_users.sql` se ejecuta en la base de datos
- **THEN** se crean los dos usuarios demo con contraseña `Demo1234`
- **THEN** ambos usuarios están activos
