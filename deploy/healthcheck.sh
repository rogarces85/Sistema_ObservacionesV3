#!/usr/bin/env bash
# deploy/healthcheck.sh
# Healthcheck basico del sistema en produccion.
# Pensado para correr via cron cada 5 minutos o desde un monitor externo.
#
# Configurar como cron en /etc/cron.d/rem-healthcheck:
#   */5 * * * * root /var/www/rem/deploy/healthcheck.sh >> /var/log/rem/healthcheck.log 2>&1
#
# Tambien enviar correo si falla (configurar MAILTO_TO).

set -u

APP_DIR="${APP_DIR:-/var/www/rem}"
BASE_URL="${BASE_URL:-https://rem.example.cl}"
LOG_DIR="${LOG_DIR:-/var/log/rem}"
MAILTO_TO="${MAILTO_TO:-ops@rem.example.cl}"

mkdir -p "$LOG_DIR"
TS="$(date '+%Y-%m-%d %H:%M:%S')"
ERRORS=0
ALERTS=()

check() {
    local label="$1"
    local cmd="$2"
    local pattern="$3"
    if output=$(eval "$cmd" 2>&1); then
        if echo "$output" | grep -qE "$pattern"; then
            echo "[$TS] OK   $label"
        else
            echo "[$TS] FAIL $label (no match: $pattern)"
            ALERTS+=("$label fallo: patron no encontrado")
            ERRORS=$((ERRORS+1))
        fi
    else
        echo "[$TS] FAIL $label (comando retorno no-cero)"
        ALERTS+=("$label fallo: $output")
        ERRORS=$((ERRORS+1))
    fi
}

# 1. Home responde
check "HTTP home" \
    "curl -ks -o /dev/null -w '%{http_code}' $BASE_URL/" \
    "^200$"

# 2. API session check responde
check "API session check" \
    "curl -ks $BASE_URL/api/auth.php?action=check" \
    "No hay sesion activa|success.*false"

# 3. Disco
DISK_USED=$(df /var/www /var/lib/mysql | tail -1 | awk '{print $5}' | tr -d '%')
if [ "$DISK_USED" -lt 85 ]; then
    echo "[$TS] OK   Disco ${DISK_USED}%"
else
    echo "[$TS] FAIL Disco ${DISK_USED}% (>85%)"
    ALERTS+=("Disco al ${DISK_USED}%")
    ERRORS=$((ERRORS+1))
fi

# 4. Worker log reciente
if [ -f "$LOG_DIR/worker.log" ]; then
    if find "$LOG_DIR/worker.log" -mmin -10 >/dev/null 2>&1; then
        echo "[$TS] OK   Worker log actualizado en ultimos 10 min"
    else
        echo "[$TS] WARN Worker log sin actividad en 10+ min"
        ALERTS+=("Worker sin actividad en 10+ minutos")
        ERRORS=$((ERRORS+1))
    fi
fi

# 5. Reportes pendientes
PEND=$(mysql -N -e "SELECT COUNT(*) FROM observaciones_rem.reportes_pendientes WHERE estado = 'PENDIENTE';" 2>/dev/null || echo "?")
if [ "$PEND" != "?" ] && [ "$PEND" -gt 100 ]; then
    echo "[$TS] WARN Cola de reportes con $PEND pendientes"
    ALERTS+=("Cola de reportes con $PEND pendientes")
    ERRORS=$((ERRORS+1))
fi

# Alertas
if [ "$ERRORS" -gt 0 ]; then
    {
        echo "Subject: [REM] Healthcheck FALLO ($ERRORS alertas) - $TS"
        echo
        echo "Alertas:"
        printf '  - %s\n' "${ALERTS[@]}"
    } | sendmail -t 2>/dev/null || \
    echo "[$TS] (no se pudo enviar correo de alerta a $MAILTO_TO)"
fi

exit "$ERRORS"
