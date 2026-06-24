# Guia de Despliegue a Produccion

## Requisitos del servidor

| Componente | Version | Notas |
|---|---|---|
| OS | Ubuntu 22.04 LTS | o 20.04 LTS |
| PHP | 8.2.x | con extensiones: pdo_mysql, mbstring, gd, intl, zip, xml, openssl, curl, opcache |
| MySQL | 8.0.x | o MariaDB 10.11.x |
| Web server | Apache 2.4 | mod_rewrite, mod_headers, mod_ssl |
| Memoria | 2 GB minimo | 4 GB recomendado para reportes concurrentes |
| Disco | 20 GB minimo | backups aparte |

## Preparacion del servidor

```bash
# Paquetes
sudo apt update
sudo apt install -y php8.2 php8.2-mysql php8.2-mbstring php8.2-gd \
  php8.2-intl php8.2-zip php8.2-xml php8.2-curl php8.2-opcache \
  mysql-server apache2 libapache2-mod-php8.2 \
  certbot python3-certbot-apache

# Habilitar modulos Apache
sudo a2enmod rewrite headers ssl

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

## Despliegue de la aplicacion

```bash
# 1. Clonar repositorio
sudo mkdir -p /var/www
sudo chown -R www-data:www-data /var/www
cd /var/www
sudo -u www-data git clone https://github.com/rogarces85/Sistema_ObservacionesV3.git rem
cd rem
sudo git checkout main

# 2. Instalar dependencias
sudo -u www-data composer install --no-dev --optimize-autoloader

# 3. Permisos
sudo chown -R www-data:www-data /var/www/rem
sudo find /var/www/rem -type d -exec chmod 750 {} \;
sudo find /var/www/rem -type f -exec chmod 640 {} \;
# Exceptions: ejecutables y uploads
sudo chmod 755 /var/www/rem/worker_reportes.php
sudo chmod -R 770 /var/www/rem/uploads
```

## Configuracion endurecida

### `config/config.php`

`config/config.php` ya viene endurecido en `main` y soporta dos formas
de cargar credenciales:

1. **Archivo env via `REM_ENV_FILE`** (recomendado en produccion):
   - Copiar `config/config.production.example` a `/etc/rem/env.php`
   - Editar host, user, password
   - En el VirtualHost de Apache agregar `SetEnv REM_ENV_FILE /etc/rem/env.php`

2. **Variables de entorno del sistema** (alternativa o complemento):
   - `REM_ENVIRONMENT` (production | development)
   - `REM_DB_USER`, `REM_DB_PASS`
   - `REM_PHP_ERROR_LOG`
   - `REM_COOKIE_SECURE` (0 | 1)

3. **Archivo local para desarrollo** (no usar en produccion):
   - `config/.env.local.php` (gitignored) se carga automaticamente
     solo si no hay env file. Pensado para que el equipo local no
     tenga que configurar nada.

Comportamiento automatico en produccion:
- `display_errors = 0`, `log_errors = 1`, `error_log` configurable.
- `session.cookie_secure = 1`, `session.cookie_httponly = 1`,
  `session.cookie_samesite = Lax`, `session.use_strict_mode = 1`.
- Credenciales de BD nunca quedan en el codigo del repo.
- Si no hay env file ni env vars, devuelve error 500 con
  instruccion clara (no cae en defaults inseguros).

### SMTP para reset de contrasena

`includes/Mailer.php` envia el password temporal al usuario cuando
se restablece via `api/users.php?action=reset_password`. Si SMTP
no esta configurado, la API devuelve el password al supervisor
para comunicacion manual. Configurar en `/etc/rem/env.php`:

```php
return [
    // ...
    'smtp' => [
        'host' => 'smtp.example.cl',
        'port' => 587,
        'user' => 'rem@example.cl',
        'pass' => '...',
        'from' => 'no-reply@rem.example.cl',
        'from_name' => 'Sistema de Observaciones REM',
        'secure' => 'tls',
    ],
];
```

O via env vars: `REM_SMTP_HOST`, `REM_SMTP_PORT`, `REM_SMTP_USER`,
`REM_SMTP_PASS`, `REM_SMTP_FROM`, `REM_SMTP_FROM_NAME`, `REM_SMTP_SECURE`.

### Apache VirtualHost (`/etc/apache2/sites-available/rem.conf`)

```apache
<VirtualHost *:443>
    ServerName rem.example.cl
    DocumentRoot /var/www/rem

    <Directory /var/www/rem>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    <FilesMatch "config/">
        Require all denied
    </FilesMatch>

    <Directory /var/www/rem/uploads>
        php_flag engine off
        AddType text/plain .php .phtml .php3 .php4 .php5 .php7 .phps
    </Directory>

    SSLEngine on
    SSLCertificateFile      /etc/letsencrypt/live/rem.example.cl/fullchain.pem
    SSLCertificateKeyFile   /etc/letsencrypt/live/rem.example.cl/privkey.pem

    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "DENY"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' cdn.jsdelivr.net; style-src 'self' cdn.jsdelivr.net 'unsafe-inline'; font-src 'self' cdn.jsdelivr.net data:; img-src 'self' data:; connect-src 'self'; frame-ancestors 'none'"

    ErrorLog /var/log/apache2/rem-error.log
    CustomLog /var/log/apache2/rem-access.log combined
</VirtualHost>

<VirtualHost *:80>
    ServerName rem.example.cl
    Redirect permanent / https://rem.example.cl/
</VirtualHost>
```

## Base de datos

### Crear usuario dedicado

```sql
CREATE USER 'rem_app'@'localhost' IDENTIFIED BY 'CAMBIAR';
GRANT SELECT, INSERT, UPDATE, DELETE ON observaciones_rem.* TO 'rem_app'@'localhost';
FLUSH PRIVILEGES;
```

### Orden de migraciones

```bash
mysql -u rem_app -p observaciones_rem < config/init_db.sql
mysql -u rem_app -p observaciones_rem < config/migration_2026_02_06.sql
mysql -u rem_app -p observaciones_rem < config/migrations/add_tipo_asignacion.sql
mysql -u rem_app -p observaciones_rem < config/migrations/create_notificaciones.sql
mysql -u rem_app -p observaciones_rem < config/migration_2026_05_08_limpieza_comunas.sql
mysql -u rem_app -p observaciones_rem < config/migration_2026_05_08_reportes.sql
mysql -u rem_app -p observaciones_rem < specs/sprint1_migration.sql
mysql -u rem_app -p observaciones_rem < specs/sprint2_migration.sql
mysql -u rem_app -p observaciones_rem < specs/sprint3_migration.sql
mysql -u rem_app -p observaciones_rem < specs/sprint4_migration.sql
mysql -u rem_app -p observaciones_rem < specs/sprint5_migration.sql
mysql -u rem_app -p observaciones_rem < config/sprint3_migration.sql
mysql -u rem_app -p observaciones_rem < config/update_establecimientos.sql
mysql -u rem_app -p observaciones_rem < config/create_asignaciones_table.sql
```

**No ejecutar** `config/demo_users.sql` en produccion.

### Crear usuarios reales

Insertar manualmente los primeros usuarios via `api/users.php?action=create`
o mediante la UI de Usuarios tras primer login de un supervisor inicial
generado por el equipo de operaciones.

## Cron del worker de reportes

```bash
sudo mkdir -p /var/log/rem
sudo chown www-data:www-data /var/log/rem
sudo -u www-data crontab -e
```

Agregar:

```
* * * * * cd /var/www/rem && /usr/bin/php8.2 worker_reportes.php >> /var/log/rem/worker.log 2>&1
```

## Backups

```bash
# /etc/cron.daily/rem-backup
#!/bin/bash
BACKUP_DIR="/var/backups/rem/$(date +%Y-%m-%d)"
mkdir -p "$BACKUP_DIR"
mysqldump -u rem_app -p$REM_DB_PASS observaciones_rem | gzip > "$BACKUP_DIR/db.sql.gz"
tar -czf "$BACKUP_DIR/uploads.tar.gz" -C /var/www/rem uploads/

# Rotacion: conservar 30 dias
find /var/backups/rem -type d -mtime +30 -exec rm -rf {} \;
```

Hacer chmod 700 al script, guardar credenciales en `/etc/rem/db.env`.

## Verificacion post-despliegue

```bash
# Lint del codigo desplegado
sudo -u www-data find /var/www/rem -name "*.php" -not -path "*/vendor/*" -exec php -l {} \;

# Smoke HTTP
curl -k https://rem.example.cl/?page=dashboard
curl -k -I https://rem.example.cl/

# Login de prueba
curl -k -c /tmp/c.txt -H "Content-Type: application/json" \
  -d '{"username":"supervisor_inicial","password":"..."}' \
  https://rem.example.cl/api/auth.php?action=login
```

## Rollback a version anterior

```bash
# Ver snapshots
sudo -u www-data php /var/www/rem/api/versioning.php?action=list
# (gestionado desde la UI Versionado con doble confirmacion)

# O manual via git
cd /var/www/rem
sudo -u www-data git log --oneline -5
sudo -u www-data git checkout afb31f5  # commit anterior deseado
```

## Cambiar credenciales por primera vez

1. Login como supervisor inicial.
2. Ir a `?page=usuarios`.
3. Para cada usuario demo, restablecer password con politica
   de 8+ chars, mayuscula y numero.
4. Eliminar usuarios que no se usen.
5. Cambiar password del supervisor inicial desde Perfil.

## Limpieza de delta de auditoria

Si la BD de produccion se hidrata desde un dump que incluye
los IDs 506 y 507 con estados `rechazado` y `aprobado` del
pase de auditoria, revertir manualmente:

```sql
UPDATE observaciones SET estado_actual = 'pendiente' WHERE id IN (506, 507);
DELETE FROM historial_estados WHERE observacion_id IN (506, 507)
  AND estado_nuevo IN ('rechazado','aprobado','aprobado')
  AND fecha_cambio > '2026-06-24';
```

## Checklist de salida a produccion

- [ ] Servidor con PHP 8.2, MySQL 8, Apache 2.4
- [ ] HTTPS con cert valido y HSTS
- [ ] `config/config.php` apunta a BD con usuario dedicado
- [ ] `cookie_secure = 1`, `display_errors = 0`
- [ ] Cabeceras HTTP endurecidas configuradas
- [ ] Migraciones aplicadas en orden, sin `demo_users.sql`
- [ ] Usuarios demo cambiados / eliminados
- [ ] Cron del worker activo
- [ ] Backup diario configurado y probado
- [ ] Permisos de archivos correctos
- [ ] `uploads/` no ejecutable
- [ ] Logs accesibles y rotando
- [ ] Smoke test verde
- [ ] Equipo de operaciones capacitado

## Contacto

- Repositorio: https://github.com/rogarces85/Sistema_ObservacionesV3
- Branch de despliegue: `main`
- Ultima version estable: 2.1.0 (commit `afb31f5`)
