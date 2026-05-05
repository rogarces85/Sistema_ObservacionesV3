<?php
/**
 * Configuración de Base de Datos
 * Sistema de Observaciones REM - Servicio de Salud Osorno
 */

define('ENVIRONMENT', 'production'); // 'production' o 'development'

$dbConfig = [
    'production' => [
        'host' => '10.8.152.199',
        'port' => '3306',
        'name' => 'observaciones_rem',
        'user' => 'root',
        'pass' => 'estadi2021',
        'charset' => 'utf8mb4'
    ],
    'development' => [
        'host' => 'localhost',
        'port' => '3306',
        'name' => 'observaciones_rem',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4'
    ]
];

$config = $dbConfig[ENVIRONMENT];

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

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Observaciones REM');
define('APP_VERSION', '2.0.0');
define('SESSION_NAME', 'rem_session');

// Zona horaria
date_default_timezone_set('America/Santiago');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 si se usa HTTPS

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}
