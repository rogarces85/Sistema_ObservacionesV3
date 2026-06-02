<?php
/**
 * API de Datos Kanban
 * Retorna observaciones para el tablero kanban
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
$observations = $obsModel->getAll($currentYear, $userRole === 'supervisor' ? null : $userId, $userRole);

$items = array_map(function($obs) {
    return [
        'id' => $obs['id'],
        'estado_actual' => $obs['estado_actual'],
        'nombre_corto' => $obs['nombre_corto'],
        'mes' => $obs['mes'],
        'tipo_error' => $obs['tipo_error'],
        'usuario_registro_id' => $obs['usuario_registro_id']
    ];
}, $observations);

jsonResponse(true, ['items' => $items]);
