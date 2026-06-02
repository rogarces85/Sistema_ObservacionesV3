<?php
/**
 * API de Versionado del Sistema
 * Gestión de snapshots y rollbacks
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Version.php';

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

// Verificar autenticación
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    jsonResponse(false, null, 'No autenticado', 401);
}

// Verificar rol (Solo Admin/Supervisor)
if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    jsonResponse(false, null, 'Acceso denegado', 403);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

try {
    $versionModel = new Version();

    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                $versions = $versionModel->getAllVersions();
                jsonResponse(true, $versions);
            } elseif ($action === 'detail' && isset($_GET['id'])) {
                $version = $versionModel->getVersionDetails($_GET['id']);
                if ($version) {
                    jsonResponse(true, $version);
                } else {
                    jsonResponse(false, null, 'Versión no encontrada', 404);
                }
            } else {
                jsonResponse(false, null, 'Acción no válida', 400);
            }
            break;

        case 'POST':
            if ($action === 'create') {
                $input = json_decode(file_get_contents('php://input'), true);
                $descripcion = $input['descripcion'] ?? '';
                
                if (empty($descripcion)) {
                    jsonResponse(false, null, 'La descripción es requerida', 400);
                }

                $newId = $versionModel->createVersion($descripcion, $userId);
                if ($newId) {
                    jsonResponse(true, ['id' => $newId], 'Versión creada exitosamente', 201);
                } else {
                    jsonResponse(false, null, 'Error al crear la versión', 500);
                }
            } elseif ($action === 'rollback' && isset($_GET['id'])) {
                $versionId = $_GET['id'];
                $newId = $versionModel->rollback($versionId, $userId);
                if ($newId) {
                    jsonResponse(true, ['id' => $newId], 'Rollback ejecutado exitosamente');
                } else {
                    jsonResponse(false, null, 'Error al ejecutar el rollback', 500);
                }
            } else {
                jsonResponse(false, null, 'Acción no válida', 400);
            }
            break;

        default:
            jsonResponse(false, null, 'Método no permitido', 405);
    }
} catch (Exception $e) {
    error_log("Error en API versioning: " . $e->getMessage());
    jsonResponse(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
