<?php
/**
 * API de Observaciones Eliminadas
 * Gestión de papelera de reciclaje (solo supervisores)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/DeletedObservation.php';
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

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    jsonResponse(false, null, 'No autenticado', 401);
}

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    jsonResponse(false, null, 'Acceso denegado', 403);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $deletedModel = new DeletedObservation();

    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                $filters = [
                    'anio' => $_GET['anio'] ?? null,
                    'mes' => $_GET['mes'] ?? null,
                    'comuna_nombre' => $_GET['comuna_nombre'] ?? null,
                    'establecimiento_id' => $_GET['establecimiento_id'] ?? null,
                    'usuario_registro_id' => $_GET['usuario_registro_id'] ?? null,
                    'busqueda' => $_GET['busqueda'] ?? null
                ];
                
                $deleted = $deletedModel->getAll($filters);
                jsonResponse(true, $deleted);
            } elseif ($action === 'stats') {
                $year = $_GET['anio'] ?? null;
                $stats = $deletedModel->getStats($year);
                jsonResponse(true, $stats);
            } else {
                jsonResponse(false, null, 'Acción no válida', 400);
            }
            break;

        case 'POST':
            CSRF::validateRequest();
            
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';

            if ($action === 'restore') {
                $deletedId = $input['deleted_id'] ?? null;
                if (!$deletedId) {
                    jsonResponse(false, null, 'ID requerido', 400);
                }

                $success = $deletedModel->restore($deletedId, $_SESSION['user_id']);
                if ($success) {
                    jsonResponse(true, null, 'Observación restaurada exitosamente');
                } else {
                    jsonResponse(false, null, 'Error al restaurar observación', 500);
                }
            } elseif ($action === 'permanent_delete') {
                $deletedId = $input['deleted_id'] ?? null;
                if (!$deletedId) {
                    jsonResponse(false, null, 'ID requerido', 400);
                }

                $success = $deletedModel->permanentDelete($deletedId);
                if ($success) {
                    jsonResponse(true, null, 'Observación eliminada permanentemente');
                } else {
                    jsonResponse(false, null, 'Error al eliminar permanentemente', 500);
                }
            } elseif ($action === 'restore_multiple') {
                $deletedIds = $input['deleted_ids'] ?? [];
                if (empty($deletedIds)) {
                    jsonResponse(false, null, 'Lista de IDs requerida', 400);
                }

                $successCount = 0;
                foreach ($deletedIds as $deletedId) {
                    if ($deletedModel->restore($deletedId, $_SESSION['user_id'])) {
                        $successCount++;
                    }
                }

                jsonResponse(true, ['restored' => $successCount], "{$successCount} observación(es) restaurada(s)");
            } elseif ($action === 'permanent_delete_multiple') {
                $deletedIds = $input['deleted_ids'] ?? [];
                if (empty($deletedIds)) {
                    jsonResponse(false, null, 'Lista de IDs requerida', 400);
                }

                $successCount = 0;
                foreach ($deletedIds as $deletedId) {
                    if ($deletedModel->permanentDelete($deletedId)) {
                        $successCount++;
                    }
                }

                jsonResponse(true, ['deleted' => $successCount], "{$successCount} observación(es) eliminada(s) permanentemente");
            } else {
                jsonResponse(false, null, 'Acción no válida', 400);
            }
            break;

        default:
            jsonResponse(false, null, 'Método no permitido', 405);
    }

} catch (Exception $e) {
    error_log("Error en API eliminadas: " . $e->getMessage());
    jsonResponse(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
