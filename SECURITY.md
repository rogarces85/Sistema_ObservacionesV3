# Politica de Seguridad

## Modelo de amenazas

### Activos a proteger
- Datos de observaciones REM (salud publica).
- Credenciales de usuarios.
- Historial de cambios (auditoria).
- Snapshots del sistema (rollback).
- Archivos generados (reportes Excel/PDF).

### Amenazas consideradas
- Inyeccion SQL via formularios / API.
- CSRF en acciones mutables.
- XSS via campos de texto libre.
- Escalada de privilegios (registrador -> supervisor).
- Robo de sesion por cookie insegura.
- Acceso fisico al servidor.
- Backup sin cifrado.
- Eliminacion o alteracion maliciosa de datos.
- Phishing a supervisores para reset de password.

### Amenazas fuera de alcance (version 2.1.0)
- DDoS a nivel red.
- Compromiso del servidor host.
- Ingenieria social contra usuarios finales.

## Controles implementados

### Autenticacion
- Passwords con `password_hash` (bcrypt cost 10).
- Politica: 8+ caracteres, al menos una mayuscula y un numero.
- Bloqueo de sesion por cookie httponly.
- Cookie same-site Lax.

### Autorizacion
- Roles separados: `registrador` y `supervisor`.
- Guards en backend: `api/users.php`, `api/supervision.php`,
  `api/assignments.php` validan rol antes de mutar.
- Auto-acciones prohibidas: supervisor no puede cambiar su
  propio rol, desactivarse, eliminarse o resetear su password
  desde admin.

### CSRF
- Token por sesion, almacenado en `$_SESSION['csrf_token']`.
- Header `X-CSRF-TOKEN` o `csrf_token` en body POST/PUT/DELETE.
- Validacion estricta en `includes/csrf.php::validateRequest()`.

### Acciones destructivas
- `api/deleted.php` exige `confirm_irreversible: true` para
  eliminacion permanente.
- `api/users.php` exige `confirm_delete: true` y
  `confirm_reset: true`.
- `api/versioning.php` rollback requiere confirm en UI
  con tipeo de "ACEPTAR".
- Eliminacion permanente **no ejecutada** durante la auditoria
  de junio 2026; la BD oficial quedo solo con mutaciones
  reversibles.

### Logs
- `logs` (generico), `historial_estados` (observaciones),
  `historial_usuarios` (acciones admin).
- Errores PHP a `/var/log/rem/php-error.log`.

## Controles recomendados post-lanzamiento

### Corto plazo
- [ ] Reemplazar reset de password literal `admin123` por
      generacion aleatoria y envio por email.
- [ ] Implementar recuperacion de password via email.
- [ ] Implementar bloqueo tras N intentos fallidos.
- [ ] Forzar cambio de password en primer login.
- [ ] Cambiar contrasena del usuario `root` de MySQL y crear
      `rem_app` con password aleatorio.
- [ ] Mover `demo_users.sql` fuera del paquete de despliegue.
- [ ] Cifrar backups.

### Mediano plazo
- [ ] Implementar MFA (TOTP).
- [ ] Auditar SQL embebido fuera de `models/`.
- [ ] Penetration test externo anual.
- [ ] WAF (ModSecurity) en Apache.
- [ ] IDS/IPS en host (OSSEC, fail2ban).

### Largo plazo
- [ ] Migrar autenticacion a OAuth2 / OpenID Connect.
- [ ] Cifrado en reposo para campos sensibles.
- [ ] Auditoria SOC 2 / ISO 27001 si aplica.

## Gestion de parches

| Severidad | SLA de parcheo |
|---|---|
| Critica (RCE, auth bypass) | < 24 horas |
| Alta (escalada de privilegios) | < 7 dias |
| Media (defectos con impacto) | < 30 dias |
| Baja (cosmetic) | siguiente release |

## Gestion de secretos

- **Nunca** commitear passwords, tokens o llaves privadas al repo.
- Variables sensibles en `/etc/rem/env.php` con permisos `640`.
- `.gitignore` excluye: `vendor/`, `node_modules/`, `uploads/*`,
  `.opencode/`, `assets/libs/*/coverage/`.
- Secrets de despliegue guardados en bovedas del equipo
  (1Password, Bitwarden, etc.).

## Reporte de vulnerabilidades

- Email: [insertar]
- Tiempo de respuesta: < 48 horas.
- Coordinar divulgacion responsable.
- Premiar con reconocimiento (no monetario por ahora).

## Auditoria de codigo

- `php -l` antes de cada commit (CI o local).
- Revision de pares obligatoria para PR a `main`.
- No merge con `php -l` fallando.
- Evidencia de pruebas guardada en
  `specs/002-fix-button-actions/verification-evidence.md`.

## Politica de retencion

| Dato | Retencion | Justificacion |
|---|---|---|
| `observaciones` | indefinido | dato de salud publica |
| `observaciones_eliminadas` | 90 dias desde eliminacion | recuperacion ante error |
| `historial_estados` | indefinido | auditoria |
| `historial_usuarios` | indefinido | auditoria |
| `reportes_pendientes` | 30 dias despues de `LISTO` | limpieza |
| `versiones_sistema` | 5 snapshots mas recientes | historial |
| `logs` | 90 dias | operacion |
| Backups | 30 dias | recuperacion |

## Compliance

- Ley 19.628 (Chile) sobre proteccion de datos personales.
- Si se procesan datos sensibles (salud), considerar:
  - Encriptacion en reposo y en transito.
  - Logs de acceso a datos personales.
  - Contratos de encargo de tratamiento con el Servicio de Salud.
- Escenario futuro: integracion con plataformas nacionales
  (DEIS, MINSAL) requiere cumplir con sus estandares.

## Contacto

- Reportes de seguridad: [insertar]
- CISO o responsable: [insertar]
- Repositorio: https://github.com/rogarces85/Sistema_ObservacionesV3
