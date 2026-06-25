# Despliegue del Sistema REM

Scripts y plantillas para llevar el sistema a produccion.

## Orden de ejecucion recomendado

1. `bash setup-server.sh` (B1: instala PHP 8.2, MySQL 8, Apache 2.4, fail2ban, ufw).
2. Clonar el codigo:
   ```
   sudo -u www-data git clone <repo> /var/www/rem
   cd /var/www/rem
   sudo -u www-data composer install --no-dev --optimize-autoloader
   ```
3. Crear usuario MySQL dedicado:
   ```
   sudo mysql < sql/create-db-user.sql
   ```
4. Configurar `/etc/rem/env.php` (copiar `env.production.example` y editar).
5. Configurar `/etc/rem/db.env` (usar `db.env.example` como base).
6. Correr migraciones:
   ```
   sudo bash migrate.sh
   ```
7. Crear primer supervisor:
   ```
   sudo -u www-data php deploy/create-first-supervisor.php --username=admin --nombre="Administrador" --email=admin@rem.example.cl
   ```
8. Limpiar usuarios demo:
   ```
   sudo -u www-data php deploy/purge-demo-users.php --keep=admin
   ```
9. Activar VirtualHost:
   ```
   sudo cp deploy/apache-rem.conf /etc/apache2/sites-available/rem.conf
   sudo a2ensite rem.conf
   ```
10. Activar HTTPS:
    ```
    sudo bash setup-https.sh rem.example.cl
    ```
11. Activar worker:
    ```
    sudo cp deploy/rem-worker.service /etc/systemd/system/
    sudo cp deploy/rem-worker.timer /etc/systemd/system/
    sudo systemctl daemon-reload
    sudo systemctl enable --now rem-worker.timer
    ```
12. Configurar fail2ban:
    ```
    sudo cp deploy/fail2ban-rem-auth.conf /etc/fail2ban/filter.d/
    sudo cp deploy/fail2ban-rem-jail.conf /etc/fail2ban/jail.d/
    sudo systemctl restart fail2ban
    ```
13. Configurar backup:
    ```
    sudo cp deploy/backup.sh /etc/cron.daily/rem-backup
    sudo chmod 700 /etc/cron.daily/rem-backup
    sudo cp deploy/db.env.example /etc/rem/db.env
    sudo chmod 600 /etc/rem/db.env
    ```
14. Configurar healthcheck:
    ```
    echo '*/5 * * * * root /var/www/rem/deploy/healthcheck.sh >> /var/log/rem/healthcheck.log 2>&1' | sudo tee /etc/cron.d/rem-healthcheck
    ```
15. Smoke test:
    ```
    curl -I https://rem.example.cl/
    curl https://rem.example.cl/api/auth.php?action=check
    ```

## Archivos

| Archivo | Proposito |
|---|---|
| `setup-server.sh` | B1: paquetes, firewall, fail2ban, carpetas |
| `setup-https.sh` | B3: Let's Encrypt via certbot |
| `sql/create-db-user.sql` | B4: usuario MySQL con privilegios minimos |
| `migrate.sh` | B5: corre 14 migraciones en orden |
| `env.production.example` | B8: plantilla para /etc/rem/env.php |
| `db.env.example` | Plantilla para /etc/rem/db.env (usado por backup.sh) |
| `apache-rem.conf` | B9: VirtualHost HTTPS endurecido |
| `fail2ban-rem-auth.conf` | B12: filtro fail2ban |
| `fail2ban-rem-jail.conf` | B12: jail fail2ban |
| `rem-worker.service` | B10: unidad systemd del worker |
| `rem-worker.timer` | B10: timer systemd (cada minuto) |
| `rem-worker.cron` | B10: alternativa cron |
| `create-first-supervisor.php` | B6: crea supervisor inicial via CLI |
| `purge-demo-users.php` | B7: desactiva/elimina usuarios demo |
| `backup.sh` | B11: backup diario BD + uploads |
| `restore.sh` | B13: restore desde backup (emergencias) |
| `healthcheck.sh` | B14: chequeo periodico |
| `TRAINING.md` | B15: plan de capacitacion para go-live |
| `CUTOVER.md` | B16: checklist de corte del ambiente dev |

## Post-deploy

- B15: Capacitar al equipo de soporte siguiendo `TRAINING.md`.
- B16: Cortar acceso al ambiente de desarrollo siguiendo `CUTOVER.md`.

## Hardening post-lanzamiento (no bloqueante)

- C1: MFA
- C2: Politica de primer-login
- C4: Penetration test externo
- C5: WAF ModSecurity
- C6: Cifrado en reposo
