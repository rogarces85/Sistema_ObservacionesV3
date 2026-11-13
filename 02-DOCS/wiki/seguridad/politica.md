# Política de Seguridad

> **Ubicación:** `02-DOCS/wiki/seguridad/politica.md`
> **Fuente original:** `SECURITY.md` (raíz)
> **Última actualización:** 2026-11-13

---

## 1. Modelo de amenazas

### Activos a proteger
- Datos de observaciones REM (salud pública)
- Credenciales de usuarios
- Historial de cambios (auditoría)
- Snapshots del sistema (rollback)
- Archivos generados (reportes Excel/PDF)

### Amenazas consideradas
- Inyección SQL via formularios / API
- CSRF en acciones mutables
- XSS via campos de texto libre
- Escalada de privilegios (registrador → supervisor)
- Robo de sesión por cookie insegura
- Acceso físico al servidor
- Backup sin cifrado
- Eliminación o alteración maliciosa de datos
- Phishing a supervisores para reset de password

### Amenazas fuera de alcance (versión 2.1.0)
- DDoS a nivel red
- Compromiso del servidor host
- Ingeniería social contra usuarios finales

---

## 2. Controles implementados

### Autenticación
- Passwords con `password_hash` (bcrypt cost 10)
- Política: 8+ caracteres, al menos una mayúscula y un número
- Bloqueo de sesión por cookie httponly
- Cookie same-site Lax

### Autorización
- Roles separados: `registrador` y `supervisor`
- Guards en backend: `api/users.php`, `api/supervision.php`, `api/assignments.php` validan rol antes de mutar
- Auto-acciones prohibidas: supervisor no puede cambiar su propio rol, desactivarse, eliminarse o resetear su password desde admin

### CSRF
- Token por sesión, almacenado en `$_SESSION['csrf_token']`
- Header `X-CSRF-TOKEN` o `csrf_token` en body POST/PUT/DELETE
- Validación estricta en `includes/csrf.php::validateRequest()`

### Acciones destructivas
- `api/deleted.php` exige `confirm_irreversible: true` para eliminación permanente
- `api/users.php` exige `confirm_delete: true` y `confirm_reset: true`
- Eliminación permanente **no ejecutada** durante la auditoría de junio 2026; la BD oficial quedó solo con mutaciones reversibles

### Logs
- `logs` (genérico), `historial_estados` (observaciones), `historial_usuarios` (acciones admin)
- Errores PHP a `/var/log/rem/php-error.log`

---

## 3. Controles recomendados post-lanzamiento

### Corto plazo
- [x] Reemplazar reset de password literal `admin123` por generación aleatoria y envío por email. **Hecho**: `api/users.php:230` usa `Mailer::generateRandomPassword(12)` y envía por correo si está configurado.
- [ ] Rotar las cuentas semilla de `config/init_db.sql` (`supervisor1`, `registrador1..4`) si siguen existiendo en producción con la password `admin123`.
- [ ] Implementar recuperación de password via email.
- [ ] Implementar bloqueo tras N intentos fallidos.
- [ ] Forzar cambio de password en primer login.
- [ ] Cambiar contraseña del usuario `root` de MySQL y crear `rem_app` con password aleatorio.
- [ ] Mover `demo_users.sql` fuera del paquete de despliegue.
- [ ] Cifrar backups.

### Mediano plazo
- [ ] Implementar MFA (TOTP).
- [ ] Auditar SQL embebido fuera de `models/`.
- [ ] Penetration test externo anual.
- [ ] WAF (ModSecurity) en Apache.
- [ ] IDS/IPS en host (OSSEC, fail2ban).

### Largo plazo
- [ ] Migrar autenticación a OAuth2 / OpenID Connect.
- [ ] Cifrado en reposo para campos sensibles.
- [ ] Auditoría SOC 2 / ISO 27001 si aplica.

---

## 4. Gestión de parches

| Severidad | SLA de parcheo |
|-----------|----------------|
| Crítica (RCE, auth bypass) | < 24 horas |
| Alta (escalada de privilegios) | < 7 días |
| Media (defectos con impacto) | < 30 días |
| Baja (cosmético) | siguiente release |

---

## 5. Gestión de secretos

- **Nunca** commitear passwords, tokens o llaves privadas al repo.
- Variables sensibles en `/etc/rem/env.php` con permisos `640`.
- `.gitignore` excluye: `vendor/`, `node_modules/`, `uploads/*`, `assets/libs/*/coverage/`.
- Secrets de despliegue guardados en bóvedas del equipo (1Password, Bitwarden, etc.).

---

## 6. Reporte de vulnerabilidades

- Email: [insertar]
- Tiempo de respuesta: < 48 horas.
- Coordinar divulgación responsable.
- Premiar con reconocimiento (no monetario por ahora).

---

## 7. Auditoría de código

- `php -l` antes de cada commit (CI o local).
- Revisión de pares obligatoria para PR a `main`.
- No merge con `php -l` fallando.
- Evidencia de pruebas guardada en `specs/002-fix-button-actions/verification-evidence.md`.

---

## 8. Política de retención

| Dato | Retención | Justificación |
|------|-----------|---------------|
| `observaciones` | indefinido | dato de salud pública |
| `observaciones_eliminadas` | 90 días desde eliminación | recuperación ante error |
| `historial_estados` | indefinido | auditoría |
| `historial_usuarios` | indefinido | auditoría |
| `reportes_pendientes` | 30 días después de `LISTO` | limpieza |
| `logs` | 90 días | operación |
| Backups | 30 días | recuperación |

---

## 9. Compliance

- Ley 19.628 (Chile) sobre protección de datos personales.
- Si se procesan datos sensibles (salud), considerar:
  - Encriptación en reposo y en tránsito.
  - Logs de acceso a datos personales.
  - Contratos de encargo de tratamiento con el Servicio de Salud.
- Escenario futuro: integración con plataformas nacionales (DEIS, MINSAL) requiere cumplir con sus estándares.

---

## 10. Contacto

- Reportes de seguridad: [insertar]
- CISO o responsable: [insertar]
- Repositorio: https://github.com/rogarces85/Sistema_ObservacionesV3