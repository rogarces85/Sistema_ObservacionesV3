<?php
/**
 * Configuracion del Sistema de Observaciones REM
 *
 * En produccion se recomienda cargar credenciales desde un archivo
 * fuera del web root. Definir REM_ENV_FILE apuntando a un PHP que
 * retorne el array de configuracion (ver config/config.production.example).
 *
 * Orden de resolucion:
 *   1. REM_ENV_FILE (si existe y retorna array)
 *   2. Variables de entorno del sistema (REM_DB_HOST, etc.)
 *   3. Valores por defecto del archivo (solo development)
 */

define('ENVIRONMENT', getenv('REM_ENVIRONMENT') ?: 'production');
define('APP_VERSION', '2.1.0');

/**
 * Cargar configuracion de BD.
 *
 * Fuentes aceptadas, en orden de prioridad:
 *   1. Archivo env via REM_ENV_FILE (recomendado en produccion).
 *   2. config/.env.local.php (solo para desarrollo local; gitignored).
 *   3. Variables de entorno del sistema (REM_DB_HOST, REM_DB_PORT, etc.).
 *   4. Defaults localhost solo si ENVIRONMENT=development.
 *
 * En produccion sin env file ni variables: error 500 con instruccion.
 */
function rem_load_db_config() {
    $candidates = [];
    $envFile = getenv('REM_ENV_FILE');
    if ($envFile) {
        $candidates[] = $envFile;
    }
    $candidates[] = __DIR__ . '/.env.local.php';

    foreach ($candidates as $file) {
        if (is_file($file)) {
            $loaded = require $file;
            if (is_array($loaded) && !empty($loaded['host'])) {
                if (empty($loaded['port'])) $loaded['port'] = 3306;
                if (empty($loaded['charset'])) $loaded['charset'] = 'utf8mb4';
                return $loaded;
            }
        }
    }

    $envConfig = [
        'host'    => getenv('REM_DB_HOST') ?: null,
        'port'    => getenv('REM_DB_PORT') ?: null,
        'name'    => getenv('REM_DB_NAME') ?: null,
        'user'    => getenv('REM_DB_USER') ?: null,
        'pass'    => getenv('REM_DB_PASS') ?: null,
        'charset' => getenv('REM_DB_CHARSET') ?: 'utf8mb4',
    ];

    if (!empty($envConfig['host']) && !empty($envConfig['user'])) {
        $envConfig['port'] = $envConfig['port'] ? (int) $envConfig['port'] : 3306;
        return $envConfig;
    }

    if (ENVIRONMENT === 'development') {
        return [
            'host'    => 'localhost',
            'port'    => 3306,
            'name'    => 'observaciones_rem',
            'user'    => 'root',
            'pass'    => '',
            'charset' => 'utf8mb4',
        ];
    }

    http_response_code(500);
    die('Configuracion de base de datos faltante. ' .
        'Defina REM_ENV_FILE apuntando a archivo env, o las variables ' .
        'REM_DB_HOST / REM_DB_USER / REM_DB_PASS. ' .
        'Ver DEPLOY.md seccion "Configuracion endurecida".');
}

$config = rem_load_db_config();

define('DB_HOST', $config['host']);
define('DB_PORT', $config['port']);
define('DB_NAME', $config['name']);
define('DB_USER', $config['user']);
define('DB_PASS', $config['pass']);
define('DB_CHARSET', $config['charset']);

// Rutas del sistema
define('BASE_PATH', dirname(__DIR__));
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('ASSETS_PATH', BASE_PATH . '/assets');

// Configuracion de la aplicacion
define('APP_NAME', 'Sistema de Observaciones REM');

// Zona horaria
date_default_timezone_set('America/Santiago');

// Errores segun entorno
if (ENVIRONMENT === 'production') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', getenv('REM_PHP_ERROR_LOG') ?: '/var/log/rem/php-error.log');
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    ini_set('log_errors', 1);
}

// Configuracion de sesion
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Lax');

// cookie_secure: 1 por defecto en produccion con HTTPS, override por env
$cookieSecure = getenv('REM_COOKIE_SECURE');
if ($cookieSecure === '0') {
    ini_set('session.cookie_secure', 0);
} else {
    ini_set('session.cookie_secure', ENVIRONMENT === 'production' ? 1 : 0);
}

// Iniciar sesion si no esta iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_name('rem_session');
    session_start();
}
