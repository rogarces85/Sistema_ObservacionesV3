## Why

No existe un mecanismo rápido para crear usuarios de prueba o demostración al instalar/configurar el sistema. Cada vez que se necesita probar funcionalidades en un entorno nuevo (desarrollo, QA, demo) hay que crear usuarios manualmente vía la interfaz, lo que requiere tener un supervisor registrado primero. Esto ralentiza las pruebas y demostraciones.

## What Changes

- Crear un script PHP `seed_demo.php` que inserte o restablezca usuarios demo con credenciales conocidas
- El script creará 2 usuarios demo: un registrador y un supervisor
- Se agregará un archivo SQL de migración con los INSERTs por si se prefiere vía base de datos
- El script será autocontenido: verificará si los usuarios ya existen y los recreará
- Los usuarios demo tendrán credenciales documentadas para facilitar pruebas
- **BREAKING**: Ninguno. Solo agrega datos, no modifica esquemas.

## Capabilities

### New Capabilities
- `demo-user`: Mecanismo para crear y resetear usuarios de prueba con credenciales conocidas, facilitando la revisión de funcionalidades y demostraciones del sistema.

### Modified Capabilities
- Ninguna.

## Impact

- `seed_demo.php` — Nuevo script en la raíz del proyecto
- `config/demo_users.sql` — Nuevo archivo SQL de migración (opcional, como alternativa)
- No afecta API, vistas, modelos, ni base de datos existente
