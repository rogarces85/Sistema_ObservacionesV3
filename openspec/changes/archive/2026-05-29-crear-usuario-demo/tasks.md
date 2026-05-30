## 1. Script PHP de seed

- [x] 1.1 Crear `seed_demo.php` en la raíz del proyecto con configuración de conexión a DB y verificación de entorno (solo development)
- [x] 1.2 Implementar lógica de creación/actualización de usuarios demo usando `password_hash()` con bcrypt y PDO directo
- [x] 1.3 Agregar mensajes de salida CLI (success/error) y código de retorno

## 2. Migración SQL alternativa

- [x] 2.1 Generar hash bcrypt para contraseña `Demo1234` y crear `config/demo_users.sql` con INSERTs
- [x] 2.2 Verificar que el SQL con hash pre-generado funciona con `password_verify()`

## 3. Verificación

- [x] 3.1 Ejecutar script en entorno development y verificar que los usuarios se crean
- [x] 3.2 Verificar login con credenciales demo (`demo_registrador` / `Demo1234`)
- [x] 3.3 Verificar que re-ejecutar el script no crea duplicados
