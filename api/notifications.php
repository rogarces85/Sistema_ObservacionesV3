<?php
/**
 * API de notificaciones reales.
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../includes/csrf.php';

function jsonResponse($success, $data = null, $message = '', $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode(['success' => $success, 'data' => $data, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    jsonResponse(false, null, 'No autorizado', 401);
}

$notificationModel = new Notification();
$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

try {
    if ($method === 'GET' && $action === 'list') {
        jsonResponse(true, [
            'unread' => $notificationModel->countUnread($userId),
            'items' => $notificationModel->getForUser($userId, 10)
        ]);
    }

    if ($method === 'POST') {
        CSRF::validateRequest();
        $input = json_decode(file_get_contents('php://input'), true) ?: [];

        if ($action === 'read') {
            $id = $input['id'] ?? null;
            if (!$id) jsonResponse(false, null, 'ID requerido', 400);
            $notificationModel->markRead($id, $userId);
            jsonResponse(true, null, 'Notificación marcada como leída');
        }

        if ($action === 'read_all') {
            $notificationModel->markAllRead($userId);
            jsonResponse(true, null, 'Notificaciones marcadas como leídas');
        }
    }

    jsonResponse(false, null, 'Acción no válida', 400);
} catch (Exception $e) {
    error_log('Error en API notifications: ' . $e->getMessage());
    jsonResponse(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
