# Operaciones y Runbook

> **Ubicación:** `02-DOCS/wiki/operaciones/runbook.md`
> **Fuente original:** `OPERATIONS.md` (raíz)
> **Última actualización:** 2026-11-13

---

## 1. Servicios en el servidor

| Servicio | Comando | Puerto | Supervisado por |
|----------|---------|--------|-----------------|
| Apache | `sudo systemctl status apache2` | 80, 443 | systemd |
| MySQL | `sudo systemctl status mysql` | 3306 (interno) | systemd |
| Worker cron | `sudo crontab -u www-data -l` | - | cron |
| Backups | `/etc/cron.daily/rem-backup` | - | cron |

---

## 2. Logs principales

| Archivo | Contenido | Rotación |
|---------|-----------|----------|
| `/var/log/apache2/rem-access.log` | Accesos HTTP | logrotate semanal |
| `/var/log/apache2/rem-error.log` | Errores Apache | logrotate semanal |
| `/var/log/rem/php-error.log` | Errores PHP | logrotate diario |
| `logs/php-error.log` (dentro del proyecto) | Respaldo automático: se usa cuando `/var/log/rem` no existe o no es escribible (ej. XAMPP Windows) | manual |
| `/var/log/rem/worker.log` | Salida del worker | logrotate diario |

Verificar rápidamente:
```bash
tail -f /var/log/rem/php-error.log
tail -f /var/log/rem/worker.log
sudo tail -f /var/log/apache2/rem-access.log
```

---

## 3. Monitoreo básico

### Uptime

```bash
# HTTP básico
curl -k -I https://rem.example.cl/ | head -1
# Esperado: HTTP/1.1 200 OK

# Healthcheck API
curl -k -I https://rem.example.cl/api/auth.php?action=check
```

### Disco y memoria

```bash
df -h /var/www /var/lib/mysql /var/log /var/backups
free -m
```

### Cola de reportes

```bash
mysql -u rem_app -p observaciones_rem -e \
  "SELECT estado, COUNT(*) FROM reportes_pendientes GROUP BY estado;"
```

Esperado: reportes `LISTO` menores a 100 y `PENDIENTE` que decrece cada minuto.

---

## 4. Procedimientos operativos

### Reiniciar servicios

```bash
sudo systemctl restart apache2
sudo systemctl restart mysql
sudo systemctl restart rem-worker.timer
```

### Verificar worker

```bash
# Estado del timer
systemctl status rem-worker.timer

# Logs del worker
tail -f /var/log/rem/worker.log

# Forzar ejecución manual
sudo -u www-data php /var/www/rem/worker_reportes.php
```

### Backup manual

```bash
sudo /var/www/rem/deploy/backup.sh
```

### Restaurar backup

Ver `deploy/restore.sh`.

---

## 5. Gestión de usuarios (desde la UI)

Acceder como supervisor → **Usuarios**:

- Crear usuario: rol `registrador` o `supervisor`, asignar establecimientos/meses.
- Editar: cambiar rol, activar/desactivar.
- Restablecer contraseña: genera password aleatorio de 12 chars, envía email si Mailer está configurado, log en `historial_usuarios`.
- Eliminar: requiere confirmación explícita `confirm_delete: true`.
- Auditoría: botón "Historial" por usuario.

**Guards:** un supervisor no puede cambiar su propio rol, desactivarse, eliminarse ni resetear su propia contraseña desde la UI.

---

## 6. Rotación de cuentas semilla

Las cuentas creadas por `config/init_db.sql` (`supervisor1`, `registrador1..4`) pueden seguir con password `admin123` en producción.

**Procedimiento:**
1. Entrar como supervisor → Usuarios
2. Para cada cuenta semilla: botón "Restablecer contraseña"
3. Se genera password aleatorio de 12 caracteres
4. Se envía por email (si Mailer configurado)
5. Se registra en `historial_usuarios`

---

## 7. Limpieza de reportes antiguos

Los reportes en estado `LISTO` mayores a 30 días se pueden limpiar:

```sql
DELETE FROM reportes_pendientes
WHERE estado = 'LISTO' AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

Ejecutar via cron mensual si se desea.

---

## 8. Papelera de observaciones (`observaciones_eliminadas`)

- Retención: 90 días desde eliminación
- Limpieza manual o cron:
```sql
DELETE FROM observaciones_eliminadas
WHERE fecha_eliminacion < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

---

## 9. Troubleshooting rápido

| Síntoma | Qué revisar |
|---------|-------------|
| Página en blanco / 500 | `/var/log/rem/php-error.log` o `logs/php-error.log` |
| Reportes no generan | Worker activo? `systemctl status rem-worker.timer`; logs en `/var/log/rem/worker.log` |
| Login falla | BD accesible? Usuario `rem_app` existe? |
| CSRF error | Header `X-CSRF-Token` presente? Sesión activa? |
| Permisos archivos | `www-data` owner en `/var/www/rem`? `uploads/` 770? |

---

## 10. Contactos

- Responsable técnico: [insertar]
- CISO / Seguridad: [insertar]
- Repositorio: https://github.com/rogarces85/Sistema_ObservacionesV3