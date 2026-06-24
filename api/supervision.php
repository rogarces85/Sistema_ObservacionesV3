<?php
/**
 * API de Supervisión
 * Endpoint para operaciones de supervisión sobre observaciones
 * Solo accesible para usuarios con rol de supervisor
 */

// Suppress notices/warnings in production to avoid breaking JSON
error_reporting(E_ERROR | E_PARSE);

require_once '../config/config.php';
require_once '../config/constants.php';
require_once '../models/Observation.php';
require_once '../includes/csrf.php';

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['rol'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

// Verificar que el usuario sea supervisor
if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Se requiere rol de supervisor']);
    exit;
}

$action = $_GET['action'] ?? '';
$obsModel = new Observation();

try {
    switch ($action) {
        case 'approve':
            // Validar CSRF
            CSRF::validateRequest();

            // Aprobar observación(es)
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                throw new Exception('ID de observación requerido');
            }

            $ids = is_array($data['id']) ? $data['id'] : [$data['id']];
            $comment = $data['comment'] ?? 'Observación aprobada por supervisor';
            $extraData = [];
            if (!empty($data['clasificacion'])) {
                $extraData['clasificacion'] = $data['clasificacion'];
            }
            if (isset($data['detalle_error'])) {
                $extraData['detalle_error'] = $data['detalle_error'];
            }

            // Determinar estado resultante según selección del supervisor
            $estadoResultante = $data['estado_resultante'] ?? '';
            if (!in_array($estadoResultante, ['sin_observacion', 'error'], true)) {
                throw new Exception('Debe seleccionar si la aprobación queda como Sin Observación o Error');
            }
            if ($estadoResultante === 'error') {
                $nuevoEstado = ESTADO_ERROR;
                $extraData['tipo_error'] = 'ERROR';
            } else {
                $nuevoEstado = ESTADO_APROBADO;
                $extraData['tipo_error'] = 'S/OBSERVACION';
            }

            if (count($ids) === 1) {
                $result = $obsModel->updateStatus($ids[0], $nuevoEstado, $_SESSION['user_id'], $comment, $extraData);

                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Observación aprobada correctamente'
                    ]);
                } else {
                    throw new Exception('Error al aprobar la observación');
                }
            } else {
                $count = 0;
                foreach ($ids as $obsId) {
                    if ($obsModel->updateStatus($obsId, $nuevoEstado, $_SESSION['user_id'], $comment, $extraData)) {
                        $count++;
                    }
                }

                echo json_encode([
                    'success' => true,
                    'message' => "Se aprobaron {$count} observaciones correctamente",
                    'count' => $count
                ]);
            }
            break;

        case 'reject':
            // Acción no permitida para supervisores
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Acción no permitida. Los supervisores solo pueden aprobar observaciones.'
            ]);
            break;

        case 'cancel':
            // Validar CSRF
            CSRF::validateRequest();

            // Cancelar observación(es)
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                throw new Exception('ID de observación requerido');
            }

            $ids = is_array($data['id']) ? $data['id'] : [$data['id']];
            $comment = $data['comment'] ?? 'Observación cancelada por supervisor';

            if (count($ids) === 1) {
                $result = $obsModel->updateStatus($ids[0], ESTADO_RECHAZADO, $_SESSION['user_id'], $comment);

                if ($result) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Observación cancelada correctamente'
                    ]);
                } else {
                    throw new Exception('Error al cancelar la observación');
                }
            } else {
                // Operación masiva
                $count = $obsModel->bulkUpdateStatus($ids, ESTADO_RECHAZADO, $_SESSION['user_id'], $comment);

                echo json_encode([
                    'success' => true,
                    'message' => "Se cancelaron {$count} observaciones correctamente",
                    'count' => $count
                ]);
            }
            break;

        case 'delete':
            // Validar CSRF
            CSRF::validateRequest();

            // Eliminar observación(es) - mover a papelera
            require_once '../models/DeletedObservation.php';
            $deletedModel = new DeletedObservation();
            
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                throw new Exception('ID de observación requerido');
            }

            $ids = is_array($data['id']) ? $data['id'] : [$data['id']];
            $reason = $data['reason'] ?? 'Eliminado por supervisor';

            $successCount = 0;
            foreach ($ids as $id) {
                if ($deletedModel->moveToTrash($id, $_SESSION['user_id'], $reason)) {
                    $successCount++;
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "{$successCount} observación(es) eliminada(s) correctamente",
                'count' => $successCount
            ]);
            break;

        case 'update_status':
            // Validar CSRF
            CSRF::validateRequest();

            // Cambiar estado genérico
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id']) || empty($data['estado'])) {
                throw new Exception('ID y estado son requeridos');
            }

            $comment = $data['comment'] ?? "Cambio de estado a: {$data['estado']}";
            $extraData = [];
            if (!empty($data['clasificacion'])) {
                $extraData['clasificacion'] = $data['clasificacion'];
            }
            if (isset($data['detalle_error'])) {
                $extraData['detalle_error'] = $data['detalle_error'];
            }
            $result = $obsModel->updateStatus($data['id'], $data['estado'], $_SESSION['user_id'], $comment, $extraData);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Estado actualizado correctamente'
                ]);
            } else {
                throw new Exception('Error al actualizar el estado');
            }
            break;

        case 'get_filtered':
            // Obtener observaciones con filtros
            $filters = [
                'anio' => $_GET['anio'] ?? null,
                'mes' => $_GET['mes'] ?? null,
                'estado' => $_GET['estado'] ?? null,
                'establecimiento_id' => $_GET['establecimiento_id'] ?? null,
                'usuario_registro_id' => $_GET['usuario_registro_id'] ?? null,
                'busqueda' => $_GET['busqueda'] ?? null,
                'limit' => $_GET['limit'] ?? 100,
                'offset' => $_GET['offset'] ?? 0
            ];

            $observations = $obsModel->getWithFilters($filters);

            echo json_encode([
                'success' => true,
                'data' => $observations,
                'count' => count($observations)
            ]);
            break;

        case 'get_detail':
            // Obtener detalle completo con historial
            $id = $_GET['id'] ?? null;

            if (!$id) {
                throw new Exception('ID de observación requerido');
            }

            $observation = $obsModel->getById($id);
            $historial = $obsModel->getHistorial($id);

            if (!$observation) {
                throw new Exception('Observación no encontrada');
            }

            echo json_encode([
                'success' => true,
                'data' => $observation,
                'historial' => $historial
            ]);
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Acción no válida'
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
