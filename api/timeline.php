<?php
/**
 * API de Timeline
 * Retorna eventos recientes del sistema
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

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    jsonResponse(false, null, 'No autorizado', 401);
}

$userId = $_SESSION['usuario_id'];
$userRole = $_SESSION['rol'];
$currentYear = $_SESSION['anio_trabajo'] ?? date('Y');

$obsModel = new Observation();
$recentObs = $obsModel->getAll($currentYear, $userId, $userRole);

$events = [];
foreach (array_slice($recentObs, 0, 20) as $obs) {
    $iconMap = [
        'pendiente' => 'clock',
        'aprobado' => 'check',
        'rechazado' => 'x',
        'error' => 'alert-triangle',
        'justificado' => 'info-circle'
    ];
    $colorMap = [
        'pendiente' => 'yellow',
        'aprobado' => 'green',
        'rechazado' => 'red',
        'error' => 'red',
        'justificado' => 'blue'
    ];

    $events[] = [
        'id' => $obs['id'],
        'icon' => $iconMap[$obs['estado_actual']] ?? 'circle',
        'color' => $colorMap[$obs['estado_actual']] ?? 'secondary',
        'descripcion' => sprintf('Observación #%d - %s - %s', $obs['id'], $obs['nombre_corto'], $obs['tipo_error']),
        'usuario' => $obs['nombre_registro'] ?? 'Sistema',
        'fecha' => $obs['fecha_registro'] ?? date('Y-m-d H:i:s'),
        'estado' => $obs['estado_actual']
    ];
}

jsonResponse(true, ['events' => $events]);
