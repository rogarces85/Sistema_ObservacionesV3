<?php
/**
 * API de Autenticación
 * Endpoints: login, logout, check, change_year
 * Incluye protección contra fuerza bruta y gestión CSRF
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Función para responder en JSON
function responderJson($exito, $datos = null, $error = '', $codigo = 200)
{
    http_response_code($codigo);
    $respuesta = ['success' => $exito, 'data' => $datos];
    if ($error !== '') {
        $respuesta['error'] = $error;
    }
    $respuesta['code'] = $codigo;
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

// Generar o regenerar token CSRF
function generarCsrfToken()
{
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

// Validar token CSRF
function validarCsrfToken()
{
    $tokenEntrante = null;

    if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $tokenEntrante = $_SERVER['HTTP_X_CSRF_TOKEN'];
    } elseif (isset($_POST['csrf_token'])) {
        $tokenEntrante = $_POST['csrf_token'];
    } else {
        $cuerpoEntrada = json_decode(file_get_contents('php://input'), true);
        $tokenEntrante = $cuerpoEntrada['csrf_token'] ?? null;
    }

    if (!$tokenEntrante || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $tokenEntrante)) {
        responderJson(false, null, 'Token CSRF inválido o expirado', 403);
    }
}

// Protección contra fuerza bruta: 5 intentos → bloqueo 30s por IP
function obtenerClaveIntentos()
{
    return 'login_intentos_' . ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

function registrarIntentoFallido()
{
    $clave = obtenerClaveIntentos();
    if (!isset($_SESSION[$clave])) {
        $_SESSION[$clave] = ['intentos' => 0, 'bloqueado_hasta' => 0];
    }
    $_SESSION[$clave]['intentos']++;
    if ($_SESSION[$clave]['intentos'] >= 5) {
        $_SESSION[$clave]['bloqueado_hasta'] = time() + 30;
    }
}

function verificarBloqueoFuerzaBruta()
{
    $clave = obtenerClaveIntentos();
    if (!isset($_SESSION[$clave])) return;

    $registro = $_SESSION[$clave];
    if ($registro['bloqueado_hasta'] > time()) {
        $tiempoRestante = $registro['bloqueado_hasta'] - time();
        responderJson(false, null, "Demasiados intentos. Intente nuevamente en {$tiempoRestante} segundos", 429);
    }

    if ($registro['bloqueado_hasta'] > 0 && $registro['bloqueado_hasta'] <= time()) {
        $_SESSION[$clave] = ['intentos' => 0, 'bloqueado_hasta' => 0];
    }
}

function limpiarIntentosLogin()
{
    $clave = obtenerClaveIntentos();
    unset($_SESSION[$clave]);
}

// Obtener método HTTP y acción
$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['action'] ?? '';

try {
    $db = Database::obtenerInstancia();

    switch ($accion) {
        case 'login':
            if ($metodo !== 'POST') {
                responderJson(false, null, 'Método no permitido', 405);
            }

            verificarBloqueoFuerzaBruta();

            $cuerpo = json_decode(file_get_contents('php://input'), true);
            $nombreUsuario = trim($cuerpo['username'] ?? '');
            $password = $cuerpo['password'] ?? '';

            if ($nombreUsuario === '' || $password === '') {
                responderJson(false, null, 'Usuario y contraseña son requeridos', 400);
            }

            // Buscar usuario en la base de datos
            $sql = "SELECT id, username, password_hash, nombre_completo, rol, activo
                    FROM usuarios WHERE username = :username LIMIT 1";
            $usuario = $db->consultarUno($sql, ['username' => $nombreUsuario]);

            if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
                registrarIntentoFallido();
                responderJson(false, null, 'Credenciales inválidas', 401);
            }

            if ((int)$usuario['activo'] !== 1) {
                responderJson(false, null, 'Usuario desactivado. Contacte al administrador', 403);
            }

            // Login exitoso - limpiar intentos y crear sesión
            limpiarIntentosLogin();

            $_SESSION['usuario_id'] = (int)$usuario['id'];
            $_SESSION['nombre_usuario'] = $usuario['username'];
            $_SESSION['nombre_completo'] = $usuario['nombre_completo'];
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['anio_trabajo'] = (int)($cuerpo['year'] ?? date('Y'));
            $_SESSION['autenticado'] = true;
            $_SESSION['ultimo_acceso'] = time();

            $tokenCsrf = generarCsrfToken();

            responderJson(true, [
                'usuario' => [
                    'id' => (int)$usuario['id'],
                    'username' => $usuario['username'],
                    'nombre_completo' => $usuario['nombre_completo'],
                    'rol' => $usuario['rol']
                ],
                'anio_trabajo' => $_SESSION['anio_trabajo'],
                'csrf_token' => $tokenCsrf
            ], '', 200);
            break;

        case 'logout':
            if ($metodo !== 'POST') {
                responderJson(false, null, 'Método no permitido', 405);
            }

            $_SESSION = [];

            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params['path'], $params['domain'],
                    $params['secure'], $params['httponly']
                );
            }

            session_destroy();
            responderJson(true, null, 'Sesión cerrada exitosamente', 200);
            break;

        case 'check':
            if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
                responderJson(false, null, 'No hay sesión activa', 401);
            }

            // Verificar expiración de sesión (30 minutos)
            $tiempoInactividad = time() - ($_SESSION['ultimo_acceso'] ?? 0);
            if ($tiempoInactividad > 1800) {
                $_SESSION = [];
                session_destroy();
                responderJson(false, null, 'Sesión expirada por inactividad', 401);
            }

            // Actualizar último acceso
            $_SESSION['ultimo_acceso'] = time();

            // Regenerar CSRF token
            $tokenCsrf = generarCsrfToken();

            responderJson(true, [
                'usuario' => [
                    'id' => $_SESSION['usuario_id'],
                    'username' => $_SESSION['nombre_usuario'],
                    'nombre_completo' => $_SESSION['nombre_completo'],
                    'rol' => $_SESSION['rol']
                ],
                'anio_trabajo' => $_SESSION['anio_trabajo'] ?? date('Y'),
                'csrf_token' => $tokenCsrf,
                'expira_en' => 1800 - $tiempoInactividad
            ], '', 200);
            break;

        case 'change_year':
            if ($metodo !== 'POST') {
                responderJson(false, null, 'Método no permitido', 405);
            }

            validarCsrfToken();

            if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
                responderJson(false, null, 'No hay sesión activa', 401);
            }

            $cuerpo = json_decode(file_get_contents('php://input'), true);
            $anio = (int)($cuerpo['year'] ?? date('Y'));
            $anioActual = (int)date('Y');

            if ($anio < 2020 || $anio > ($anioActual + 1)) {
                responderJson(false, null, 'Año inválido. Rango permitido: 2020-' . ($anioActual + 1), 400);
            }

            $_SESSION['anio_trabajo'] = $anio;
            $_SESSION['ultimo_acceso'] = time();

            // Regenerar CSRF token post-POST
            $tokenCsrf = generarCsrfToken();

            responderJson(true, [
                'anio_trabajo' => $anio,
                'csrf_token' => $tokenCsrf
            ], '', 200);
            break;

        default:
            responderJson(false, null, 'Acción no válida', 400);
    }

} catch (Exception $e) {
    error_log("Error en API auth: " . $e->getMessage());
    responderJson(false, null, 'Error en el servidor', 500);
}
