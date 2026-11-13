# Despliegue a Producción

> **Ubicación:** `02-DOCS/wiki/operaciones/deploy.md`
> **Fuente original:** `DEPLOY.md` (raíz)
> **Última actualización:** 2026-11-13

---

## 1. Requisitos del servidor

| Componente | Versión | Notas |
|------------|---------|-------|
| OS | Ubuntu 22.04 LTS | o 20.04 LTS |
| PHP | 8.2.x | extensiones: pdo_mysql, mbstring, gd, intl, zip, xml, openssl, curl, opcache |
| MySQL | 8.0.x | o MariaDB 10.11.x |
| Web server | Apache 2.4 | mod_rewrite, mod_headers, mod_ssl |
| Memoria | 2 GB mínimo | 4 GB recomendado para reportes concurrentes |
| Disco | 20 GB mínimo | backups aparte |

---

## 2. Preparación del servidor

```bash
# Paquetes
sudo apt update
sudo apt install -y php8.2 php8.2-mysql php8.2-mbstring php8.2-gd \
  php8.2-intl php8.2-zip php8.2-xml php8.2-curl php8.2-opcache \
  mysql-server apache2 libapache2-mod-php8.2 \
  certbot python3-certbot-apache

# Habilitar módulos Apache
sudo a2enmod rewrite headers ssl

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

---

## 3. Despliegue de la aplicación

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

---

## 4. Configuración endurecida

### `config/config.php`

`config/config.php` ya viene endurecido en `main` y soporta dos formas de cargar credenciales:

1. **Archivo PHP externo** (recomendado para producción): variable de entorno `REM_ENV_FILE` apunta a `/etc/rem/env.php` (fuera del docroot, permisos 640).
2. **Variables de entorno**: `REM_DB_HOST`, `REM_DB_NAME`, `REM_DB_USER`, `REM_DB_PASS`.

Ejemplo `/etc/rem/env.php`:
```php
<?php
return [
    'db_host' => 'localhost',
    'db_name' => 'observaciones_rem',
    'db_user' => 'rem_app',
    'db_pass' => 'password_aleatorio_largo',
];
```

En producción **nunca** usar `config/.env.local.php` (es solo para desarrollo y está en `.gitignore`).

---

## 5. Apache VirtualHost (HTTPS)

```apache
<VirtualHost *:443>
    ServerName rem.example.cl
    DocumentRoot /var/www/rem

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/rem.example.cl/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/rem.example.cl/privkey.pem

    # Seguridad headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "DENY"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data:; connect-src 'self'"

    <Directory /var/www/rem>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Bloquear acceso a sensibles
    <DirectoryMatch "^/var/www/rem/(config|includes|models|specs|docs|tools|01-TOOLS|02-DOCS|\.rsc)">
        Require all denied
    </DirectoryMatch>

    <Directory /var/www/rem/uploads>
        php_admin_flag engine off
        Options -Indexes
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/rem-error.log
    CustomLog ${APACHE_LOG_DIR}/rem-access.log combined
</VirtualHost>
```

Obtener certificado:
```bash
sudo certbot --apache -d rem.example.cl
```

---

## 6. Base de datos

### Usuario de aplicación
```sql
CREATE USER 'rem_app'@'localhost' IDENTIFIED BY 'password_aleatorio_largo';
GRANT SELECT, INSERT, UPDATE, DELETE ON observaciones_rem.* TO 'rem_app'@'localhost';
FLUSH PRIVILEGES;
```

### Esquema
Ejecutar los archivos SQL en orden (ver `README.md` sección "Instalación Local Con XAMPP" para el orden exacto de `config/*.sql` y `config/migrations/`).

**⚠️ No usar `deploy/migrate.sh` para fresh installs** — está roto (referencia archivos inexistentes y aplica migraciones en orden incorrecto).

---

## 7. Worker de reportes (systemd)

```ini
# /etc/systemd/system/rem-worker.service
[Unit]
Description=REM Report Queue Worker
After=network.target mysql.service

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/rem
ExecStart=/usr/bin/php /var/www/rem/worker_reportes.php
Restart=on-failure
RestartSec=10
StandardOutput=append:/var/log/rem/worker.log
StandardError=append:/var/log/rem/worker.log

[Install]
WantedBy=multi-user.target
```

```ini
# /etc/systemd/system/rem-worker.timer
[Unit]
Description=Run REM worker every minute

[Timer]
OnBootSec=1min
OnUnitActiveSec=1min
Persistent=true

[Install]
WantedBy=timers.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now rem-worker.timer
```

---

## 8. Backups

Script: `deploy/backup.sh` (ejecutar via cron diario).

```bash
# /etc/cron.daily/rem-backup
#!/bin/bash
/var/www/rem/deploy/backup.sh >> /var/log/rem/backup.log 2>&1
```

El script hace:
- `mysqldump` de `observaciones_rem` (comprimido)
- Copia de `config/.env.local.php` (si existe) o `/etc/rem/env.php`
- Retención 30 días en `/var/backups/rem/`

---

## 9. Healthcheck

Script: `deploy/healthcheck.sh` (ejecutar via cron cada 5 min o systemd timer).

Verifica:
- HTTP 200 en `/`
- API `auth.php?action=check` responde
- BD accesible
- Espacio en disco > 10%
- Worker de reportes procesando (cola `PENDIENTE` decrece)

---

## 10. Checklist de Go-Live

Ver `deploy/CUTOVER.md` y `deploy/TRAINING.md`.

Puntos clave:
- [ ] Certificado TLS válido
- [ ] Headers de seguridad presentes
- [ ] `config/config.php` leyendo de `/etc/rem/env.php`
- [ ] Usuario BD `rem_app` con password fuerte
- [ ] `.htaccess` en raíces sensibles
- [ ] Worker timer activo
- [ ] Backup cron activo
- [ ] Healthcheck cron activo
- [ ] Cuentas semilla (`supervisor1`, `registrador1..4`) rotadas si siguen con `admin123`
- [ ] `php -l` sin errores en todo el código