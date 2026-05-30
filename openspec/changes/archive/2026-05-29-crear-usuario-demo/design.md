## Context

El sistema actual solo permite crear usuarios mediante la UI de administración (rol supervisor) o directamente en la base de datos. No hay un mecanismo de seed o demo. Para probar el sistema en desarrollo, QA o demostraciones, se necesita crear usuarios manualmente cada vez.

La tabla `usuarios` usa `password_hash` con bcrypt. No hay una función de seed existente.

## Goals / Non-Goals

**Goals:**
- Crear un script PHP `seed_demo.php` autocontenido que inserte/restablezca usuarios demo
- Crear un archivo SQL de migración como alternativa
- Usuarios demo: un registrador y un supervisor, con credenciales documentadas
- El script debe ser seguro: no comprometer producción si se ejecuta accidentalmente
- El script debe detectar si los usuarios ya existen y actualizarlos (no duplicar)

**Non-Goals:**
- No modificar la UI de administración de usuarios
- No agregar endpoints API nuevos
- No modificar el modelo User
- No crear un instalador o asistente de setup

## Decisions

| Decisión | Opción elegida | Alternativas | Razón |
|----------|---------------|--------------|-------|
| Enfoque principal | Script PHP `seed_demo.php` | Solo SQL | PHP permite usar `password_hash()` de bcrypt, mismo que usa el sistema; evita tener que generar hash manualmente |
| Seguridad | Guard constante `DEMO_PASSWORD` + check de entorno | Sin protección | El script verificará que no se está en producción antes de ejecutar (por constante ENVIRONMENT) |
| Detección de existencia | Buscar por username demo (`demo_registrador`, `demo_supervisor`) | Buscar por flag especial | Username único es suficiente; si existen, se actualizan (password + activo) |
| Hash de contraseña | `password_hash()` con PASSWORD_DEFAULT | Hash fijo | Consistente con el modelo User::create() existente |

## Risks / Trade-offs

- **Riesgo:** Ejecutar seed_demo.php en producción crearía usuarios no autorizados.  
  **Mitigación:** El script verifica `ENVIRONMENT !== 'production'` antes de ejecutar. Solo correrá en desarrollo.
- **Riesgo:** Las credenciales demo son conocidas y predecibles.  
  **Mitigación:** Documentar que es solo para desarrollo/demo. El script solo funciona en entorno development.
- **Trade-off:** Usar PHP en lugar de SQL puro significa que el script requiere el entorno PHP para ejecutarse (no se puede copiar-pegar en phpMyAdmin). Se incluye el SQL como alternativa.
