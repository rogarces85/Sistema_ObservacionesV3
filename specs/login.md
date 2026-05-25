# ⚠️ OBSOLETO — Reemplazado por `specs/mod-auth.md`

Esta especificación describía un sistema de autenticación hipotético que **no coincide** con la implementación real del sistema ObservacionesREM_V2.

## Diferencias con la realidad

| Lo que decía este spec | Lo que realmente existe |
|------------------------|------------------------|
| Bloqueo tras 5 intentos fallidos | Sin límite de intentos |
| Recuperación de contraseña por email | No implementado |
| Roles: "Usuario estándar" / "Administrador" | Roles: Registrador / Supervisor |
| Registro público de usuarios | Solo Supervisor crea cuentas |
| Selección de año en login | Selección de año en login (sí existe) |

## Especificación vigente

Para la especificación correcta y actualizada del módulo de autenticación, consultar:

→ **`specs/mod-auth.md`** — Autenticación y Sesión (MOD-AUTH)

La implementación real se encuentra en:
- `api/auth.php` — Endpoints: login, logout, check, change_year
- `models/User.php` — Modelo de usuario con `authenticate()`
- `views/login.php` — Pantalla de inicio de sesión
- `index.php` — Router que verifica sesión

---

*Documento marcado como obsoleto — Mayo 2026 — Auditoría specs vs código*
