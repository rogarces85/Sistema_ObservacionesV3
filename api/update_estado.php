<?php
/**
 * API de Actualización de Estado
 * Permite cambiar el estado de una observación
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Observation.php';
require_once __DIR__ . '/../includes/csrf.php';

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

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    jsonResponse(false, null, 'No autorizado', 401);
}

if ($_SESSION['rol'] !== 'supervisor') {
    jsonResponse(false, null, 'Solo supervisores pueden cambiar estados', 403);
}

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'POST') {
    jsonResponse(false, null, 'Método no permitido', 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(false, null, 'Datos inválidos', 400);
}

$obsId = isset($input['id']) ? (int)$input['id'] : 0;
$nuevoEstado = isset($input['estado_actual']) ? $input['estado_actual'] : '';

if (!$obsId || !$nuevoEstado) {
    jsonResponse(false, null, 'ID y estado requeridos', 400);
}

$estadosPermitidos = ['pendiente', 'aprobado', 'rechazado', 'error', 'justificado'];
if (!in_array($nuevoEstado, $estadosPermitidos)) {
    jsonResponse(false, null, 'Estado no válido', 400);
}

try {
    $db = Database::getInstance();
    $db->execute(
        "UPDATE observaciones SET estado_actual = ?, fecha_actualizacion = NOW() WHERE id = ?",
        [$nuevoEstado, $obsId]
    );
    jsonResponse(true, ['id' => $obsId, 'estado_actual' => $nuevoEstado], 'Estado actualizado correctamente');
} catch (Exception $e) {
    jsonResponse(false, null, 'Error al actualizar estado: ' . $e->getMessage(), 500);
}
