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
        // Reportes existentes
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

        // GRUPO A: Reportes de Errores
        case 'errores_mes':
            jsonResponse(true, $obsModel->reporteErroresPorMes($year, $userId, $userRole));
            break;

        case 'errores_establecimiento':
            jsonResponse(true, $obsModel->reporteErroresPorEstablecimiento($year, $userId, $userRole));
            break;

        case 'errores_comuna':
            jsonResponse(true, $obsModel->reporteErroresPorComuna($year, $userId, $userRole));
            break;

        // GRUPO B: Reportes Fuera de Plazo
        case 'fuera_plazo_mes':
            jsonResponse(true, $obsModel->reporteFueraPlazoPorMes($year, $userId, $userRole));
            break;

        case 'fuera_plazo_establecimiento':
            jsonResponse(true, $obsModel->reporteFueraPlazoPorEstablecimiento($year, $userId, $userRole));
            break;

        case 'fuera_plazo_comuna':
            jsonResponse(true, $obsModel->reporteFueraPlazoPorComuna($year, $userId, $userRole));
            break;

        // GRUPO C: Reportes de Uso del Validador
        case 'validador_mes':
            jsonResponse(true, $obsModel->reporteValidadorPorMes($year, $userId, $userRole));
            break;

        case 'validador_establecimiento':
            jsonResponse(true, $obsModel->reporteValidadorPorEstablecimiento($year, $userId, $userRole));
            break;

        case 'validador_comuna':
            jsonResponse(true, $obsModel->reporteValidadorPorComuna($year, $userId, $userRole));
            break;

        // GRUPO D: Reportes Específicos
        case 'serie_detalle':
            jsonResponse(true, $obsModel->reportePorSerieDetalle($year, $userId, $userRole));
            break;

        case 'hoja_detalle':
            jsonResponse(true, $obsModel->reportePorHojaDetalle($year, $userId, $userRole));
            break;

        // Filtros para reportes
        case 'filtros':
            jsonResponse(true, [
                'comunas' => $obsModel->getComunasConDatos($year, $userId, $userRole),
                'establecimientos' => $obsModel->getEstablecimientosConDatos($year, null, $userId, $userRole)
            ]);
            break;

        case 'all':
        default:
            jsonResponse(true, [
                'mes' => $obsModel->reportePorMes($year, $userId, $userRole),
                'establecimiento' => $obsModel->reportePorEstablecimiento($year, $userId, $userRole),
                'comuna' => $obsModel->reportePorComuna($year, $userId, $userRole),
                'serie' => $obsModel->reportePorSerie($year, $userId, $userRole),
                'plazo' => $obsModel->reportePorPlazo($year, $userId, $userRole),
                'validador' => $obsModel->reportePorValidador($year, $userId, $userRole),
                'errores_mes' => $obsModel->reporteErroresPorMes($year, $userId, $userRole),
                'errores_establecimiento' => $obsModel->reporteErroresPorEstablecimiento($year, $userId, $userRole),
                'errores_comuna' => $obsModel->reporteErroresPorComuna($year, $userId, $userRole),
                'fuera_plazo_mes' => $obsModel->reporteFueraPlazoPorMes($year, $userId, $userRole),
                'fuera_plazo_establecimiento' => $obsModel->reporteFueraPlazoPorEstablecimiento($year, $userId, $userRole),
                'fuera_plazo_comuna' => $obsModel->reporteFueraPlazoPorComuna($year, $userId, $userRole),
                'validador_mes' => $obsModel->reporteValidadorPorMes($year, $userId, $userRole),
                'validador_establecimiento' => $obsModel->reporteValidadorPorEstablecimiento($year, $userId, $userRole),
                'validador_comuna' => $obsModel->reporteValidadorPorComuna($year, $userId, $userRole),
                'serie_detalle' => $obsModel->reportePorSerieDetalle($year, $userId, $userRole),
                'hoja_detalle' => $obsModel->reportePorHojaDetalle($year, $userId, $userRole)
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Error en API reports: " . $e->getMessage());
    jsonResponse(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
