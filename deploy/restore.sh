#!/usr/bin/env bash
# deploy/restore.sh
# Restaura BD y uploads desde un backup. USO DE EMERGENCIA.
#
# Uso: sudo bash deploy/restore.sh YYYY-MM-DD [HHMMSS]
#   sudo bash deploy/restore.sh 2026-06-24
#   sudo bash deploy/restore.sh 2026-06-24 164920

set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
    echo "ERROR: ejecutar como root" >&2
    exit 1
fi

APP_DIR="${APP_DIR:-/var/www/rem}"
BACKUP_BASE="${BACKUP_BASE:-/var/backups/rem}"
DB_ENV_FILE="${DB_ENV_FILE:-/etc/rem/db.env}"

DATE="${1:-}"
TIME="${2:-}"
if [ -z "$DATE" ]; then
    echo "Uso: $0 YYYY-MM-DD [HHMMSS]" >&2
    echo "Backups disponibles:" >&2
    ls -1 "$BACKUP_BASE" 2>/dev/null || true
    exit 1
fi

DAY_DIR="$BACKUP_BASE/$DATE"
if [ ! -d "$DAY_DIR" ]; then
    echo "ERROR: no existe $DAY_DIR" >&2
    exit 1
fi

DB_FILE=$(ls -1 "$DAY_DIR"/db-*.sql.gz 2>/dev/null | sort | tail -1)
if [ -z "$DB_FILE" ]; then
    echo "ERROR: no hay dump de BD en $DAY_DIR" >&2
    exit 1
fi

if [ -n "$TIME" ]; then
    CANDIDATE="$DAY_DIR/db-$DATE-$TIME.sql.gz"
    [ -f "$CANDIDATE" ] && DB_FILE="$CANDIDATE"
fi

UPLOADS_FILE=$(ls -1 "$DAY_DIR"/uploads-*.tar.gz 2>/dev/null | sort | tail -1)

echo "=== ADVERTENCIA ==="
echo "Esto sobreescribira:"
echo "  - BD en MySQL"
echo "  - Directorio $APP_DIR/uploads"
echo
echo "  BD dump:   $DB_FILE"
echo "  Uploads:   ${UPLOADS_FILE:-ninguno}"
echo
read -p "Continuar? (escribe SI): " CONFIRM
if [ "$CONFIRM" != "SI" ]; then
    echo "Cancelado."
    exit 1
fi

if [ -f "$DB_ENV_FILE" ]; then
    # shellcheck disable=SC1090
    source "$DB_ENV_FILE"
fi
: "${DB_HOST:=localhost}"
: "${DB_NAME:=observaciones_rem}"
: "${DB_USER:=rem_app}"
: "${REM_DB_PASS:?REM_DB_PASS no definido}"

echo "[1/2] Restaurando BD..."
gunzip -c "$DB_FILE" | mysql \
    --host="$DB_HOST" \
    --user="$DB_USER" \
    --password="$REM_DB_PASS" \
    "$DB_NAME"

if [ -n "$UPLOADS_FILE" ] && [ -f "$UPLOADS_FILE" ]; then
    echo "[2/2] Restaurando uploads..."
    rm -rf "$APP_DIR/uploads"
    mkdir -p "$APP_DIR/uploads"
    tar -xzf "$UPLOADS_FILE" -C "$APP_DIR"
    chown -R www-data:www-data "$APP_DIR/uploads"
else
    echo "[2/2] No hay uploads para restaurar, saltando"
fi

echo
echo "OK. Reiniciar Apache:"
echo "  sudo systemctl reload apache2"
