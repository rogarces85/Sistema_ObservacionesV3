#!/usr/bin/env bash
# deploy/migrate.sh
# Aplica las migraciones SQL en orden estricto.
# Ejecutar como root despues de clonar el codigo y antes de configurar
# el VirtualHost de Apache.
#
# Uso: sudo bash deploy/migrate.sh

set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
    echo "ERROR: ejecutar como root" >&2
    exit 1
fi

APP_DIR="${APP_DIR:-/var/www/rem}"
DB_NAME="${DB_NAME:-observaciones_rem}"
DB_USER="${DB_USER:-root}"
MYSQL_CMD="mysql -u${DB_USER} -p ${DB_NAME}"

if [ ! -d "$APP_DIR" ]; then
    echo "ERROR: $APP_DIR no existe. Clonar el repo primero." >&2
    exit 1
fi

cd "$APP_DIR"

# Orden estricto de migraciones. NO demo_users.sql.
MIGRATIONS=(
    "config/init_db.sql"
    "config/migration_2026_02_06.sql"
    "config/migrations/add_tipo_asignacion.sql"
    "config/migrations/create_notificaciones.sql"
    "config/migration_2026_05_08_limpieza_comunas.sql"
    "config/migration_2026_05_08_reportes.sql"
    "specs/sprint1_migration.sql"
    "specs/sprint2_migration.sql"
    "specs/sprint3_migration.sql"
    "specs/sprint4_migration.sql"
    "specs/sprint5_migration.sql"
    "config/sprint3_migration.sql"
    "config/update_establecimientos.sql"
    "config/create_asignaciones_table.sql"
)

echo "=== Aplicando ${#MIGRATIONS[@]} migraciones a '$DB_NAME' ==="
for rel in "${MIGRATIONS[@]}"; do
    path="$APP_DIR/$rel"
    if [ ! -f "$path" ]; then
        echo "ERROR: no existe $rel" >&2
        exit 1
    fi
    echo "--> $rel"
    $MYSQL_CMD < "$path"
done

echo
echo "=== Tablas creadas ==="
mysql -u${DB_USER} -p -e "USE $DB_NAME; SHOW TABLES;"

echo
echo "OK. No se aplico demo_users.sql (no usar en produccion)."
echo "Siguiente paso:"
echo "  sudo -u www-data php deploy/create-first-supervisor.php"
