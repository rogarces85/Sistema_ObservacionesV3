<?php
/**
 * API de Datos del Dashboard
 * Retorna estadísticas y datos actualizados para auto-refresh
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Observation.php';
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

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    jsonResponse(false, null, 'No autorizado', 401);
}

$userId = $_SESSION['usuario_id'];
$userRole = $_SESSION['rol'];
$currentYear = $_SESSION['anio_trabajo'] ?? date('Y');

$obsModel = new Observation();
$stats = $obsModel->getStats($currentYear, $userId, $userRole);

jsonResponse(true, [
    'stats' => $stats,
    'year' => $currentYear,
    'timestamp' => date('Y-m-d H:i:s')
]);
