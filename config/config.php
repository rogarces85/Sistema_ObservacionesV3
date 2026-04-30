<?php
/**
 * Configuración de Base de Datos
 * Sistema de Observaciones REM - Servicio de Salud Osorno
 */

// Configuración de la base de datos
define('DB_HOST', '10.8.152.199');
define('DB_PORT', '3306');
define('DB_NAME', 'observaciones_rem');
define('DB_USER', 'root');
define('DB_PASS', 'estadi2021');
define('DB_CHARSET', 'utf8mb4');

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
