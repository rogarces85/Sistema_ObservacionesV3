#!/usr/bin/env bash
# deploy/backup.sh
# Backup diario de BD y uploads.
# Instalar en /etc/cron.daily/rem-backup con chmod 700.
# Las credenciales de BD se leen de /etc/rem/db.env (chmod 600).

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/rem}"
BACKUP_BASE="${BACKUP_BASE:-/var/backups/rem}"
RETENTION_DAYS="${RETENTION_DAYS:-30}"
DB_ENV_FILE="${DB_ENV_FILE:-/etc/rem/db.env}"

if [ -f "$DB_ENV_FILE" ]; then
    # shellcheck disable=SC1090
    source "$DB_ENV_FILE"
fi

: "${DB_HOST:=localhost}"
: "${DB_NAME:=observaciones_rem}"
: "${DB_USER:=rem_app}"
: "${REM_DB_PASS:?REM_DB_PASS no definido en $DB_ENV_FILE}"

TS="$(date +%Y-%m-%d-%H%M%S)"
DAY_DIR="$BACKUP_BASE/$(date +%Y-%m-%d)"
mkdir -p "$DAY_DIR"

chmod 700 "$BACKUP_BASE" "$DAY_DIR"

echo "[$TS] Backup BD -> $DAY_DIR/db-$TS.sql.gz"
mysqldump \
    --host="$DB_HOST" \
    --user="$DB_USER" \
    --password="$REM_DB_PASS" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    "$DB_NAME" | gzip -9 > "$DAY_DIR/db-$TS.sql.gz"
chmod 600 "$DAY_DIR/db-$TS.sql.gz"

if [ -d "$APP_DIR/uploads" ]; then
    echo "[$TS] Backup uploads -> $DAY_DIR/uploads-$TS.tar.gz"
    tar -czf "$DAY_DIR/uploads-$TS.tar.gz" -C "$APP_DIR" uploads
    chmod 600 "$DAY_DIR/uploads-$TS.tar.gz"
fi

echo "[$TS] Rotacion (> $RETENTION_DAYS dias)"
find "$BACKUP_BASE" -mindepth 1 -type d -mtime +"$RETENTION_DAYS" \
    -exec rm -rf {} + 2>/dev/null || true

# Sincronia off-site (opcional, requiere rclone configurado)
if command -v rclone >/dev/null 2>&1 && [ -n "${BACKUP_REMOTE:-}" ]; then
    echo "[$TS] Sync a $BACKUP_REMOTE"
    rclone sync "$BACKUP_BASE" "$BACKUP_REMOTE" --quiet || true
fi

echo "[$TS] OK"
