#!/usr/bin/env bash
# deploy/setup-server.sh
# Provisiona Ubuntu 22.04 con PHP 8.2, MySQL 8, Apache 2.4,
# firewall basico y carpetas de logs para el Sistema REM.
# Ejecutar como root en servidor limpio.

set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
    echo "ERROR: ejecutar como root (sudo $0)" >&2
    exit 1
fi

APP_USER="www-data"
APP_DIR="/var/www/rem"
LOG_DIR="/var/log/rem"
BACKUP_DIR="/var/backups/rem"
ENV_DIR="/etc/rem"

echo "=== Actualizando sistema ==="
apt-get update
apt-get -y upgrade

echo "=== Instalando paquetes ==="
apt-get install -y \
    software-properties-common \
    ca-certificates \
    apt-transport-https \
    curl \
    git \
    unzip \
    ufw \
    fail2ban \
    logrotate \
    apache2 \
    libapache2-mod-php8.2 \
    php8.2 \
    php8.2-cli \
    php8.2-mysql \
    php8.2-mbstring \
    php8.2-gd \
    php8.2-intl \
    php8.2-zip \
    php8.2-xml \
    php8.2-curl \
    php8.2-opcache \
    mysql-server \
    composer

echo "=== Habilitando modulos Apache ==="
a2enmod rewrite headers ssl
a2dissite 000-default

echo "=== Creando estructura ==="
mkdir -p "$LOG_DIR" "$BACKUP_DIR" "$ENV_DIR" "$APP_DIR"
chown -R "$APP_USER:$APP_USER" "$LOG_DIR" "$BACKUP_DIR"
chown root:"$APP_USER" "$ENV_DIR"
chmod 750 "$ENV_DIR"

echo "=== Firewall ==="
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow OpenSSH
ufw allow "Apache Full"
ufw --force enable

echo "=== Fail2ban ==="
systemctl enable fail2ban
systemctl restart fail2ban

echo "=== Listo. Siguiente paso: copiar codigo a $APP_DIR ==="
echo "  sudo -u $APP_USER git clone <repo> $APP_DIR"
echo "  sudo -u $APP_USER bash $APP_DIR/deploy/migrate.sh"
echo "  sudo cp $APP_DIR/deploy/apache-rem.conf /etc/apache2/sites-available/"
echo "  sudo a2ensite rem.conf"
echo "  sudo systemctl reload apache2"
