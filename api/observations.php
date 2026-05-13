<?php
/**
 * API de Observaciones
 * Endpoints para CRUD de observaciones y historial
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Observation.php';
require_once __DIR__ . '/../models/EstablecimientoAsignacion.php';
require_once __DIR__ . '/../includes/csrf.php';

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
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    jsonResponse(false, null, 'No autorizado', 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;
$year = $_GET['year'] ?? $_SESSION['year'] ?? date('Y');

try {
    $observationModel = new Observation();
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['rol'];

    switch ($method) {
        case 'GET':
            if ($action === 'historial' && $id) {
                // Obtener historial de una observación
                $historial = $observationModel->getHistorial($id);
                jsonResponse(true, $historial, 'Historial obtenido exitosamente');

            } elseif ($action === 'stats') {
                // Obtener estadísticas
                $stats = $observationModel->getStats($year, $userId, $userRole);
                jsonResponse(true, $stats, 'Estadísticas obtenidas exitosamente');

            } elseif ($id) {
                // Obtener una observación específica
                $obs = $observationModel->getById($id);
                if ($obs) {
                    // Verificar permisos
                    if ($userRole === ROL_REGISTRADOR && $obs['usuario_registro_id'] != $userId) {
                        jsonResponse(false, null, 'No tiene permisos para ver esta observación', 403);
                    }
                    jsonResponse(true, $obs, 'Observación obtenida exitosamente');
                } else {
                    jsonResponse(false, null, 'Observación no encontrada', 404);
                }

            } else {
                // Obtener todas las observaciones
                $observations = $observationModel->getAll($year, $userId, $userRole);
                jsonResponse(true, $observations, 'Observaciones obtenidas exitosamente');
            }
            break;

        case 'POST':
            // Validar CSRF
            CSRF::validateRequest();

            // Crear nueva observación - SOLO REGISTRADORES
            if ($userRole !== ROL_REGISTRADOR) {
                jsonResponse(false, null, 'Solo los registradores pueden crear observaciones', 403);
            }

            $input = json_decode(file_get_contents('php://input'), true);

            // Validar datos requeridos
            $required = [
                'mes',
                'establecimiento_id',
                'codigo_serie',
                'codigo_hoja',
                'tipo_error',
                'detalle_observacion',
                'plazo_entrega',
                'usa_validador'
            ];

            foreach ($required as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    jsonResponse(false, null, "El campo {$field} es requerido", 400);
                }
            }

            $data = [
                'anio' => $year,
                'mes' => $input['mes'],
                'establecimiento_id' => $input['establecimiento_id'],
                'codigo_serie' => $input['codigo_serie'],
                'codigo_hoja' => $input['codigo_hoja'],
                'tipo_error' => $input['tipo_error'],
                'detalle_observacion' => $input['detalle_observacion'],
                'plazo_entrega' => $input['plazo_entrega'],
                'usa_validador' => $input['usa_validador'],
                'usuario_registro_id' => $userId,
                'respuesta_establecimiento' => $input['respuesta_establecimiento'] ?? null,
                'clasificacion' => $input['clasificacion'] ?? null,
                'detalle_error' => $input['detalle_error'] ?? null
            ];

            // Validar asignación mensual
            if ($userRole === ROL_REGISTRADOR) {
                $asigModel = new EstablecimientoAsignacion();
                if (!$asigModel->tieneAsignacionParaMes($userId, $data['establecimiento_id'], $year, $data['mes'])) {
                    jsonResponse(false, null, 'El establecimiento no está asignado a su usuario para el mes seleccionado', 403);
                }
            }

            $newId = $observationModel->create($data);

            if ($newId) {
                jsonResponse(true, ['id' => $newId], 'Observación creada exitosamente', 201);
            } else {
                jsonResponse(false, null, 'Error al crear la observación', 500);
            }
            break;

        case 'PUT':
            // Validar CSRF
            CSRF::validateRequest();

            // Actualizar observación
            if (!$id) {
                jsonResponse(false, null, 'ID de observación requerido', 400);
            }

            $input = json_decode(file_get_contents('php://input'), true);

            // Verificar permisos
            $obs = $observationModel->getById($id);
            if (!$obs) {
                jsonResponse(false, null, 'Observación no encontrada', 404);
            }

            // Registradores solo pueden editar sus propias observaciones pendientes
            if ($userRole === ROL_REGISTRADOR) {
                if ($obs['usuario_registro_id'] != $userId) {
                    jsonResponse(false, null, 'No tiene permisos para editar esta observación', 403);
                }
                if ($obs['estado_actual'] !== ESTADO_PENDIENTE) {
                    jsonResponse(false, null, 'Solo puede editar observaciones pendientes', 403);
                }
            }

            // Si es supervisor y está cambiando el estado, agregar su ID
            if ($userRole === ROL_SUPERVISOR && isset($input['estado_actual'])) {
                $input['usuario_supervisor_id'] = $userId;
            }

            // Validar que el registrador solo use establecimientos asignados para el mes
            if ($userRole === ROL_REGISTRADOR) {
                // Si cambia el establecimiento o el mes, validar asignación
                $estIdToCheck = $input['establecimiento_id'] ?? $obs['establecimiento_id'];
                $mesToCheck = $input['mes'] ?? $obs['mes'];
                
                $asigModel = new EstablecimientoAsignacion();
                if (!$asigModel->tieneAsignacionParaMes($userId, $estIdToCheck, $year, $mesToCheck)) {
                    jsonResponse(false, null, 'El establecimiento no está asignado a su usuario para el mes seleccionado', 403);
                }
            }

            $success = $observationModel->update($id, $input, $userId);

            if ($success) {
                jsonResponse(true, null, 'Observación actualizada exitosamente');
            } else {
                jsonResponse(false, null, 'Error al actualizar la observación', 500);
            }
            break;

        case 'DELETE':
            // Validar CSRF
            CSRF::validateRequest();

            // Eliminar observación (solo supervisores)
            if ($userRole !== ROL_SUPERVISOR) {
                jsonResponse(false, null, 'No tiene permisos para eliminar observaciones', 403);
            }

            if (!$id) {
                jsonResponse(false, null, 'ID de observación requerido', 400);
            }

            $success = $observationModel->delete($id);

            if ($success) {
                jsonResponse(true, null, 'Observación eliminada exitosamente');
            } else {
                jsonResponse(false, null, 'Error al eliminar la observación', 500);
            }
            break;

        default:
            jsonResponse(false, null, 'Método no permitido', 405);
    }

} catch (Exception $e) {
    error_log("Error en API observations: " . $e->getMessage());
    jsonResponse(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
