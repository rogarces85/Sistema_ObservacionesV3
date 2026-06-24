# Operaciones y Runbook

## Servicios en el servidor

| Servicio | Comando | Puerto | Supervisado por |
|---|---|---|---|
| Apache | `sudo systemctl status apache2` | 80, 443 | systemd |
| MySQL | `sudo systemctl status mysql` | 3306 (interno) | systemd |
| Worker cron | `sudo crontab -u www-data -l` | - | cron |
| Backups | `/etc/cron.daily/rem-backup` | - | cron |

## Logs principales

| Archivo | Contenido | Rotacion |
|---|---|---|
| `/var/log/apache2/rem-access.log` | Accesos HTTP | logrotate semanal |
| `/var/log/apache2/rem-error.log` | Errores Apache | logrotate semanal |
| `/var/log/rem/php-error.log` | Errores PHP | logrotate diario |
| `/var/log/rem/worker.log` | Salida del worker | logrotate diario |

Verificar rapidamente:

```bash
tail -f /var/log/rem/php-error.log
tail -f /var/log/rem/worker.log
sudo tail -f /var/log/apache2/rem-access.log
```

## Monitoreo basico

### Uptime

```bash
# HTTP basico
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

## Procedimientos operativos

### Reiniciar aplicacion

```bash
sudo systemctl reload apache2
# La aplicacion es stateless: cada request crea su propio contexto.
```

### Reiniciar worker

```bash
# El worker se ejecuta en cada minuto via cron. Si esta atascado:
sudo -u www-data pkill -f worker_reportes.php
# El siguiente minuto se reiniciara.
```

### Forzar reprocesamiento de un reporte

```sql
UPDATE reportes_pendientes
SET estado = 'PENDIENTE', mensaje_error = NULL
WHERE id = <ID>;
```

### Backup manual

```bash
sudo -u www-data /etc/cron.daily/rem-backup
```

### Restore desde backup

```bash
# Detener aplicacion
sudo systemctl stop apache2

# Restaurar BD
gunzip -c /var/backups/rem/YYYY-MM-DD/db.sql.gz | \
  mysql -u root -p observaciones_rem

# Restaurar uploads
sudo rm -rf /var/www/rem/uploads
sudo -u www-data tar -xzf /var/backups/rem/YYYY-MM-DD/uploads.tar.gz \
  -C /var/www/rem

# Reiniciar
sudo systemctl start apache2
```

### Rotar password de un usuario

Opcion A: UI de supervisor en `?page=usuarios` -> boton "Restablecer
contrasena". El sistema la deja en `admin123` (deuda tecnica
documentada; en produccion, cambiar inmediatamente despues).

Opcion B: SQL directo (emergencias):

```sql
-- Generar hash fuera del sistema, p.ej. con PHP:
--   php -r "echo password_hash('NuevaPass123', PASSWORD_DEFAULT);"
UPDATE usuarios
SET password_hash = '<HASH_BCRYPT>', fecha_actualizacion = NOW()
WHERE id = <ID>;
```

### Cambiar anio de trabajo

El sistema maneja varios anios en sesion. Para forzar:

1. Login como supervisor.
2. Click en dropdown de anio en header.
3. Seleccionar nuevo anio.

O via API:

```bash
curl -k -b /tmp/c.txt -H "Content-Type: application/json" \
  -d '{"year":2027}' \
  https://rem.example.cl/api/auth.php?action=change_year
```

### Bloquear acceso a un usuario

```sql
UPDATE usuarios SET activo = 0, fecha_actualizacion = NOW() WHERE id = <ID>;
```

### Crear usuario nuevo

1. Login como supervisor.
2. Ir a `?page=usuarios`.
3. Boton "Nuevo Usuario".
4. Definir username, password (8+ chars, mayuscula, numero),
   nombre completo y rol.

## Incidentes comunes

### "No se pudo conectar a la base de datos"

```bash
sudo systemctl status mysql
mysql -u rem_app -p -e "SELECT 1;"
# Si falla: revisar /etc/rem/env.php y permisos
sudo chown www-data:www-data /etc/rem/env.php
sudo chmod 640 /etc/rem/env.php
```

### "Token CSRF invalido"

- Sesion expirada: el usuario debe volver a iniciar sesion.
- Cache de navegador desactualizado: forzar recarga completa.
- Bug: revisar `includes/csrf.php` y que `getCsrfToken()` retorne
  el meta tag correcto.

### Worker atascado

```bash
# Ver que el proceso este corriendo
ps -ef | grep worker_reportes

# Si esta colgado, matarlo
sudo -u www-data pkill -9 -f worker_reportes.php

# Ver siguiente intento
ls -la /var/log/rem/worker.log
tail -50 /var/log/rem/worker.log
```

### Disco lleno

```bash
# Reportes antiguos
mysql -u rem_app -p -e \
  "DELETE FROM reportes_pendientes WHERE estado = 'LISTO' AND fecha_creacion < DATE_SUB(NOW(), INTERVAL 30 DAY);"

# Logs
sudo logrotate -f /etc/logrotate.conf
sudo journalctl --vacuum-size=200M

# Backups antiguos
sudo find /var/backups/rem -type d -mtime +30 -exec rm -rf {} \;
```

### HTTPS caduco

```bash
sudo certbot renew --dry-run
sudo certbot renew
sudo systemctl reload apache2
```

### Rollback a version anterior

Via UI Versionado (preferido):

1. Login como supervisor.
2. `?page=versionado`.
3. Click "Crear snapshot" del estado actual (previo).
4. Click "Rollback" sobre la version estable.
5. Confirmar con "ACEPTAR".

Manual (emergencias):

```bash
cd /var/www/rem
sudo -u www-data git log --oneline -5
sudo -u www-data git checkout <COMMIT_HASH>
sudo systemctl reload apache2
```

## Capacidad y limites

| Parametro | Valor | Notas |
|---|---|---|
| Usuarios concurrentes | ~200 | depende de hardware |
| Observaciones por anio | ~50000 | rendimiento aceptable |
| Cola de reportes | ilimitada | pero revisar `LISTO` antiguos |
| Tamano maximo importacion Excel | 10 MB | validado en `api/import.php` |
| Tamano maximo uploads | 50 MB | configurar en php.ini y Apache |

## Contactos y escalamiento

- **Operador nivel 1**: [insertar]
- **Operador nivel 2 / DBA**: [insertar]
- **Desarrollador**: [insertar]
- **Lider tecnico**: [insertar]
- **Repositorio**: https://github.com/rogarces85/Sistema_ObservacionesV3
- **Wiki interna**: [insertar]

## Auditoria

`historial_estados` guarda cambios de estado de observaciones.
`historial_usuarios` guarda acciones de usuarios.
`reportes_pendientes` guarda el estado de reportes.
`versiones_sistema` guarda snapshots.

Consultas utiles:

```sql
-- Cambios de estado en los ultimos 7 dias
SELECT h.fecha_cambio, o.id, h.estado_anterior, h.estado_nuevo,
       u.username AS autor
FROM historial_estados h
INNER JOIN observaciones o ON o.id = h.observacion_id
INNER JOIN usuarios u ON u.id = h.usuario_id
WHERE h.fecha_cambio >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY h.fecha_cambio DESC
LIMIT 100;

-- Acciones de admin
SELECT accion, detalles, fecha_registro, usuario_id
FROM historial_usuarios
WHERE fecha_registro >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY fecha_registro DESC
LIMIT 200;
```
