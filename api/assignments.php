<?php
/**
 * API de Asignaciones de Establecimientos
 * Gestión de asignaciones de establecimientos a registradores por año (solo supervisores)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/EstablecimientoAsignacion.php';
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

function normalizeAssignmentYear($year)
{
    $year = (int) $year;
    if ($year < 2020 || $year > (int) date('Y') + 1) {
        jsonResponse(false, null, 'Año fuera de rango permitido', 400);
    }
    return $year;
}

function normalizeMeses($meses)
{
    if ($meses === null || $meses === '' || $meses === 'ALL') {
        return 'ALL';
    }

    $mesesArray = is_array($meses) ? $meses : explode(',', (string) $meses);
    $mesesLimpios = array_values(array_unique(array_filter(array_map('intval', $mesesArray), function ($m) {
        return $m >= 1 && $m <= 12;
    })));
    sort($mesesLimpios);

    if (count($mesesLimpios) === 12) {
        return 'ALL';
    }

    if (empty($mesesLimpios)) {
        jsonResponse(false, null, 'Debe seleccionar al menos un mes válido', 400);
    }

    return implode(',', $mesesLimpios);
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
            } elseif ($action === 'temporales') {
                $anio = $_GET['anio'] ?? $currentYear;
                $temporales = $asignacionModel->getAsignacionesTemporalesActivas($anio);

                foreach ($temporales as &$temp) {
                    $temp['titular_anual'] = $asignacionModel->getTitularAnual($temp['establecimiento_id'], $anio);
                }

                jsonResponse(true, $temporales);
            } else {
                jsonResponse(false, null, 'Acción no válida', 400);
            }
            break;

        case 'POST':
            CSRF::validateRequest();
            $input = json_decode(file_get_contents('php://input'), true) ?: [];
            $action = $input['action'] ?? '';
            $anio = normalizeAssignmentYear($input['anio'] ?? $currentYear);

            if ($action === 'asignar') {
                $usuarioId = (int) ($input['usuario_id'] ?? 0);
                $establecimientoId = (int) ($input['establecimiento_id'] ?? 0);
                $tipoAsignacion = $input['tipo_asignacion'] ?? 'anual';

                if ($usuarioId <= 0 || $establecimientoId <= 0) {
                    jsonResponse(false, null, 'Usuario y establecimiento son requeridos', 400);
                }

                if (!in_array($tipoAsignacion, ['anual', 'temporal'], true)) {
                    jsonResponse(false, null, 'Tipo de asignación debe ser "anual" o "temporal"', 400);
                }

                $mesesRaw = $tipoAsignacion === 'temporal' ? ($input['meses'] ?? '') : 'ALL';
                $meses = normalizeMeses($mesesRaw);

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
                $usuarioId = (int) ($input['usuario_id'] ?? 0);
                $establecimientoIds = $input['establecimiento_ids'] ?? [];
                $tipoAsignacion = $input['tipo_asignacion'] ?? 'anual';

                if ($usuarioId <= 0 || empty($establecimientoIds) || !is_array($establecimientoIds)) {
                    jsonResponse(false, null, 'Usuario y lista de establecimientos son requeridos', 400);
                }

                $establecimientoIds = array_values(array_unique(array_filter(array_map('intval', $establecimientoIds), function ($id) {
                    return $id > 0;
                })));

                if (empty($establecimientoIds)) {
                    jsonResponse(false, null, 'Debe seleccionar al menos un establecimiento válido', 400);
                }

                if (!in_array($tipoAsignacion, ['anual', 'temporal'], true)) {
                    jsonResponse(false, null, 'Tipo de asignación debe ser "anual" o "temporal"', 400);
                }

                $mesesRaw = $tipoAsignacion === 'temporal' ? ($input['meses'] ?? '') : 'ALL';
                $meses = normalizeMeses($mesesRaw);

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
                $usuarioId = (int) ($input['usuario_id'] ?? 0);
                $establecimientoId = (int) ($input['establecimiento_id'] ?? 0);
                $tipoAsignacion = $input['tipo_asignacion'] ?? 'anual';

                if ($usuarioId <= 0 || $establecimientoId <= 0) {
                    jsonResponse(false, null, 'Usuario y establecimiento son requeridos', 400);
                }

                if (!in_array($tipoAsignacion, ['anual', 'temporal'], true)) {
                    jsonResponse(false, null, 'Tipo de asignación debe ser "anual" o "temporal"', 400);
                }

                $meses = normalizeMeses($input['meses'] ?? 'ALL');

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
                $anioGet = normalizeAssignmentYear($_GET['anio'] ?? $currentYear);
                $temporales = $asignacionModel->getAsignacionesTemporalesActivas($anioGet);

                foreach ($temporales as &$temp) {
                    $titular = $asignacionModel->getTitularAnual($temp['establecimiento_id'], $anioGet);
                    $temp['titular_anual'] = $titular;
                }
                unset($temp);

                jsonResponse(true, $temporales);
            } elseif ($action === 'copiar_anio') {
                $anioOrigen = normalizeAssignmentYear($input['anio_origen'] ?? 0);
                $anioDestino = normalizeAssignmentYear($input['anio_destino'] ?? 0);

                if ($anioOrigen === $anioDestino) {
                    jsonResponse(false, null, 'El año origen y destino deben ser distintos', 400);
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
