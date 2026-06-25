#!/usr/bin/env bash
# deploy/setup-https.sh
# Emite y renueva el certificado Let's Encrypt.
# Requiere que el VirtualHost HTTP (puerto 80) responda primero.
# Uso: sudo bash deploy/setup-https.sh rem.example.cl

set -euo pipefail

DOMAIN="${1:-}"
if [ -z "$DOMAIN" ]; then
    echo "Uso: $0 dominio.cl" >&2
    exit 1
fi

if [ "$(id -u)" -ne 0 ]; then
    echo "ERROR: ejecutar como root" >&2
    exit 1
fi

if ! command -v certbot >/dev/null 2>&1; then
    apt-get update
    apt-get install -y certbot python3-certbot-apache
fi

certbot --apache -d "$DOMAIN" --non-interactive --agree-tos \
    --redirect --hsts --staple-ocsp \
    --email "ops@${DOMAIN#*.}" || {
    echo "ERROR: certbot fallo. Revisa DNS y VirtualHost :80." >&2
    exit 1
}

echo "Certificado emitido para $DOMAIN."
echo "Renovacion automatica via certbot.timer (systemd)."

systemctl status certbot.timer | head -5 || true

# Test de renovacion
certbot renew --dry-run
