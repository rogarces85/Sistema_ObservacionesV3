<?php
/**
 * API de Reportes
 * Devuelve datos agregados para gráficos y tablas de reportes
 * Soporta filtros por año y respeta permisos por rol
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Observation.php';

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

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['rol'];
$year = $_GET['year'] ?? $_SESSION['year'] ?? date('Y');
$report = $_GET['report'] ?? 'all';

try {
    $obsModel = new Observation();

    switch ($report) {
        case 'mes':
            jsonResponse(true, $obsModel->reportePorMes($year, $userId, $userRole));
            break;

        case 'establecimiento':
            jsonResponse(true, $obsModel->reportePorEstablecimiento($year, $userId, $userRole));
            break;

        case 'comuna':
            jsonResponse(true, $obsModel->reportePorComuna($year, $userId, $userRole));
            break;

        case 'serie':
            jsonResponse(true, $obsModel->reportePorSerie($year, $userId, $userRole));
            break;

        case 'plazo':
            jsonResponse(true, $obsModel->reportePorPlazo($year, $userId, $userRole));
            break;

        case 'validador':
            jsonResponse(true, $obsModel->reportePorValidador($year, $userId, $userRole));
            break;

        case 'all':
        default:
            jsonResponse(true, [
                'mes' => $obsModel->reportePorMes($year, $userId, $userRole),
                'establecimiento' => $obsModel->reportePorEstablecimiento($year, $userId, $userRole),
                'comuna' => $obsModel->reportePorComuna($year, $userId, $userRole),
                'serie' => $obsModel->reportePorSerie($year, $userId, $userRole),
                'plazo' => $obsModel->reportePorPlazo($year, $userId, $userRole),
                'validador' => $obsModel->reportePorValidador($year, $userId, $userRole)
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Error en API reports: " . $e->getMessage());
    jsonResponse(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
