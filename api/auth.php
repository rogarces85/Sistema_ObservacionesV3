<?php
/**
 * API de Autenticación
 * Endpoints para login y logout
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

// Función para responder en JSON
function jsonResponse($success, $data = null, $message = '', $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Obtener el método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $userModel = new User();

    switch ($action) {
        case 'login':
            if ($method !== 'POST') {
                jsonResponse(false, null, 'Método no permitido', 405);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $username = $input['username'] ?? '';
            $password = $input['password'] ?? '';
            $year = $input['year'] ?? date('Y');

            if (empty($username) || empty($password)) {
                jsonResponse(false, null, 'Usuario y contraseña son requeridos', 400);
            }

            $user = $userModel->authenticate($username, $password);

            if ($user) {
                // Iniciar sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nombre_completo'] = $user['nombre_completo'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['year'] = $year;
                $_SESSION['logged_in'] = true;

                jsonResponse(true, [
                    'user' => $user,
                    'year' => $year
                ], 'Login exitoso');
            } else {
                jsonResponse(false, null, 'Credenciales inválidas', 401);
            }
            break;

        case 'logout':
            if ($method !== 'POST') {
                jsonResponse(false, null, 'Método no permitido', 405);
            }

            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();
            jsonResponse(true, null, 'Sesión cerrada exitosamente');
            break;

        case 'check':
            // Verificar si hay sesión activa
            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
                $user = $userModel->getById($_SESSION['user_id']);
                jsonResponse(true, [
                    'user' => $user,
                    'year' => $_SESSION['year'] ?? date('Y')
                ], 'Sesión activa');
            } else {
                jsonResponse(false, null, 'No hay sesión activa', 401);
            }
            break;

        case 'change_year':
            if ($method !== 'POST') {
                jsonResponse(false, null, 'Método no permitido', 405);
            }

            // Verificar sesión activa
            if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
                jsonResponse(false, null, 'No hay sesión activa', 401);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $year = $input['year'] ?? date('Y');

            // Validar que sea un año válido
            if ($year >= 2020 && $year <= (date('Y') + 1)) {
                $_SESSION['year'] = $year;
                jsonResponse(true, ['year' => $year], 'Año actualizado correctamente');
            } else {
                jsonResponse(false, null, 'Año inválido', 400);
            }
            break;

        default:
            jsonResponse(false, null, 'Acción no válida', 400);
    }

} catch (Exception $e) {
    error_log("Error en API auth: " . $e->getMessage());
    jsonResponse(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
