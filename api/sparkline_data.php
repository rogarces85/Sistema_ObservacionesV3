<?php
/**
 * API de Sparkline Data
 * Retorna datos de tendencia para sparklines
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
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
$currentYear = $_SESSION['year'] ?? date('Y');

$obsModel = new Observation();
$stats = $obsModel->getStats($currentYear, $userId, $userRole);

// Generate mock trend data (last 7 days) since we don't have daily granularity
// In production, this would query a daily aggregation table
function generateTrend($baseValue, $variance = 5) {
    $data = [];
    for ($i = 6; $i >= 0; $i--) {
        $dayValue = max(0, $baseValue + rand(-$variance, $variance));
        $data[] = $dayValue;
    }
    return $data;
}

$total = $stats['total'] ?? 0;
$porEstado = $stats['por_estado'] ?? [];
$pendientes = 0;
$aprobados = 0;
$problemas = 0;

foreach ($porEstado as $estado) {
    switch ($estado['estado_actual']) {
        case 'pendiente': $pendientes = (int)$estado['total']; break;
        case 'aprobado': $aprobados = (int)$estado['total']; break;
        case 'rechazado':
        case 'error': $problemas += (int)$estado['total']; break;
    }
}

jsonResponse(true, [
    'total' => generateTrend($total, ceil($total / 10)),
    'pendientes' => generateTrend($pendientes, ceil($pendientes / 5)),
    'aprobados' => generateTrend($aprobados, ceil($aprobados / 5)),
    'problemas' => generateTrend($problemas, ceil($problemas / 5))
]);
