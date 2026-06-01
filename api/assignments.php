<?php
/**
 * API de Asignaciones de Establecimientos
 * Gestión de asignaciones de establecimientos a registradores por año (solo supervisores)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/EstablecimientoAsignacion.php';

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
    jsonResponse(false, null, 'No autenticado', 401);
}

$userRole = $_SESSION['rol'];
$userId = $_SESSION['user_id'];

if ($userRole !== ROL_SUPERVISOR) {
    jsonResponse(false, null, 'Acceso denegado', 403);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$currentYear = $_SESSION['year'] ?? date('Y');

try {
    $asignacionModel = new EstablecimientoAsignacion();

    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                $anio = $_GET['anio'] ?? $currentYear;
                $registradores = $asignacionModel->getEstadisticasAsignaciones($anio);
                jsonResponse(true, $registradores);
            } elseif ($action === 'registradores') {
                $registradores = $asignacionModel->getAllRegistradores();
                jsonResponse(true, $registradores);
            } elseif ($action === 'establecimientos') {
                $registradorId = $_GET['registrador_id'] ?? null;
                $anio = $_GET['anio'] ?? $currentYear;
                if ($registradorId) {
                    $establecimientos = $asignacionModel->getEstablecimientosConAsignacion($registradorId, $anio);
                } else {
                    $establecimientos = $asignacionModel->getAllEstablecimientos();
                }
                jsonResponse(true, $establecimientos);
            } elseif ($action === 'asignados') {
                $registradorId = $_GET['registrador_id'] ?? null;
                $anio = $_GET['anio'] ?? $currentYear;
                if (!$registradorId) {
                    jsonResponse(false, null, 'ID de registrador requerido', 400);
                }
                $establecimientos = $asignacionModel->getEstablecimientosByRegistrador($registradorId, $anio);
                jsonResponse(true, $establecimientos);
            } elseif ($action === 'referentes') {
                $establecimientoId = $_GET['establecimiento_id'] ?? null;
                if (!$establecimientoId) {
                    jsonResponse(false, null, 'ID de establecimiento requerido', 400);
                }
                $referentes = $asignacionModel->getReferentes($establecimientoId);
                jsonResponse(true, $referentes);
            } else {
                jsonResponse(false, null, 'Acción no válida', 400);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? '';
            $anio = $input['anio'] ?? $currentYear;

            if ($action === 'asignar') {
                $usuarioId = $input['usuario_id'] ?? null;
                $establecimientoId = $input['establecimiento_id'] ?? null;
                $meses = $input['meses'] ?? 'ALL';
                $tipoAsignacion = $input['tipo_asignacion'] ?? 'anual';

                if (!$usuarioId || !$establecimientoId) {
                    jsonResponse(false, null, 'Usuario y establecimiento son requeridos', 400);
                }

                // Validar tipo de asignación
                if (!in_array($tipoAsignacion, ['anual', 'temporal'])) {
                    jsonResponse(false, null, 'Tipo de asignación debe ser "anual" o "temporal"', 400);
                }

                // Si es temporal, los meses son obligatorios
                if ($tipoAsignacion === 'temporal' && ($meses === 'ALL' || empty($meses))) {
                    jsonResponse(false, null, 'Para asignación temporal debe especificar los meses', 400);
                }

                $success = $asignacionModel->asignar($usuarioId, $establecimientoId, $anio, $meses, $tipoAsignacion);
                if ($success) {
                    $mensaje = $tipoAsignacion === 'temporal' 
                        ? 'Reasignación temporal creada exitosamente' 
                        : 'Establecimiento asignado exitosamente';
                    jsonResponse(true, null, $mensaje);
                } else {
                    $mensaje = $tipoAsignacion === 'temporal'
                        ? 'Ya existe una reasignación temporal para ese periodo'
                        : 'El establecimiento ya está asignado o ocurrió un error';
                    jsonResponse(false, null, $mensaje, 400);
                }
            } elseif ($action === 'asignar_multiple') {
                $usuarioId = $input['usuario_id'] ?? null;
                $establecimientoIds = $input['establecimiento_ids'] ?? [];
                $meses = $input['meses'] ?? 'ALL';
                $tipoAsignacion = $input['tipo_asignacion'] ?? 'anual';

                if (!$usuarioId || empty($establecimientoIds)) {
                    jsonResponse(false, null, 'Usuario y lista de establecimientos son requeridos', 400);
                }

                // Validar tipo de asignación
                if (!in_array($tipoAsignacion, ['anual', 'temporal'])) {
                    jsonResponse(false, null, 'Tipo de asignación debe ser "anual" o "temporal"', 400);
                }

                // Si es temporal, los meses son obligatorios
                if ($tipoAsignacion === 'temporal' && ($meses === 'ALL' || empty($meses))) {
                    jsonResponse(false, null, 'Para asignación temporal debe especificar los meses', 400);
                }

                $success = $asignacionModel->asignarMultiple($usuarioId, $establecimientoIds, $anio, $meses, $tipoAsignacion);
                if ($success) {
                    $mensaje = $tipoAsignacion === 'temporal' 
                        ? 'Reasignaciones temporales creadas exitosamente' 
                        : 'Establecimientos asignados exitosamente';
                    jsonResponse(true, null, $mensaje);
                } else {
                    jsonResponse(false, null, 'Error al asignar establecimientos', 500);
                }
            } elseif ($action === 'remover') {
                $usuarioId = $input['usuario_id'] ?? null;
                $establecimientoId = $input['establecimiento_id'] ?? null;
                $meses = $input['meses'] ?? 'ALL';
                $tipoAsignacion = $input['tipo_asignacion'] ?? 'anual';

                if (!$usuarioId || !$establecimientoId) {
                    jsonResponse(false, null, 'Usuario y establecimiento son requeridos', 400);
                }

                // Validar tipo de asignación
                if (!in_array($tipoAsignacion, ['anual', 'temporal'])) {
                    jsonResponse(false, null, 'Tipo de asignación debe ser "anual" o "temporal"', 400);
                }

                $success = $asignacionModel->remover($usuarioId, $establecimientoId, $anio, $meses, $tipoAsignacion);
                if ($success) {
                    $mensaje = $tipoAsignacion === 'temporal' 
                        ? 'Reasignación temporal removida exitosamente' 
                        : 'Asignación removida exitosamente';
                    jsonResponse(true, null, $mensaje);
                } else {
                    jsonResponse(false, null, 'Error al remover asignación', 500);
                }
            } elseif ($action === 'temporales') {
                // Nuevo endpoint: listar asignaciones temporales activas
                $anio = $_GET['anio'] ?? $currentYear;
                $temporales = $asignacionModel->getAsignacionesTemporalesActivas($anio);
                
                // Agregar información del titular anual para cada temporal
                foreach ($temporales as &$temp) {
                    $titular = $asignacionModel->getTitularAnual($temp['establecimiento_id'], $anio);
                    $temp['titular_anual'] = $titular;
                }
                
                jsonResponse(true, $temporales);
            } elseif ($action === 'copiar_anio') {
                $anioOrigen = $input['anio_origen'] ?? null;
                $anioDestino = $input['anio_destino'] ?? null;

                if (!$anioOrigen || !$anioDestino) {
                    jsonResponse(false, null, 'Año origen y destino son requeridos', 400);
                }

                $success = $asignacionModel->copiarAsignaciones($anioOrigen, $anioDestino);
                if ($success) {
                    jsonResponse(true, null, "Asignaciones copiadas de {$anioOrigen} a {$anioDestino}");
                } else {
                    jsonResponse(false, null, 'Error al copiar asignaciones', 500);
                }
            } else {
                jsonResponse(false, null, 'Acción no válida', 400);
            }
            break;

        default:
            jsonResponse(false, null, 'Método no permitido', 405);
    }

} catch (Exception $e) {
    error_log("Error en API asignaciones: " . $e->getMessage());
    jsonResponse(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
