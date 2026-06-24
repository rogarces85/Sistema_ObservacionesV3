<?php
/**
 * API de cola de reportes asíncronos
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/ReportQueue.php';
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

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    jsonResponse(false, null, 'No autorizado', 401);
}

$queue = new ReportQueue();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['rol'] ?? '';

try {
    if ($method === 'GET' && $action === 'list') {
        jsonResponse(true, $queue->getUserReports($userId));
    }

    if ($method === 'GET' && $action === 'download') {
        $id = $_GET['id'] ?? null;
        if (!$id) jsonResponse(false, null, 'ID requerido', 400);

        $report = $queue->getById($id);
        if (!$report) jsonResponse(false, null, 'Reporte no encontrado', 404);
        if ($userRole !== ROL_SUPERVISOR && (int)$report['usuario_id'] !== (int)$userId) {
            jsonResponse(false, null, 'No tiene permisos para descargar este reporte', 403);
        }
        if ($report['estado'] !== 'LISTO' || empty($report['archivo_url'])) {
            jsonResponse(false, null, 'El reporte aún no está listo', 409);
        }

        $relative = ltrim($report['archivo_url'], '/');
        $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
        if (!is_file($path)) jsonResponse(false, null, 'Archivo no encontrado', 404);

        header_remove('Content-Type');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    if ($method === 'POST' && $action === 'enqueue') {
        CSRF::validateRequest();
        $input = json_decode(file_get_contents('php://input'), true) ?: [];
        $tipo = $input['tipo_reporte'] ?? 'general';
        $formato = $input['formato'] ?? 'xlsx';
        $parametros = $input['parametros'] ?? [];

        $allowedTypes = ['general', 'detallado', 'errores_establecimiento', 'fuera_plazo_establecimiento', 'validador_establecimiento', 'serie_detalle', 'hoja_detalle'];
        $allowedFormats = ['xlsx', 'excel', 'pdf'];
        if (!in_array($tipo, $allowedTypes, true)) jsonResponse(false, null, 'Tipo de reporte no permitido', 400);
        if (!in_array($formato, $allowedFormats, true)) jsonResponse(false, null, 'Formato no permitido', 400);
        if ($tipo === 'detallado' && $formato !== 'pdf') jsonResponse(false, null, 'El reporte detallado debe ser PDF', 400);

        $id = $queue->enqueue($userId, $tipo, $formato, $parametros);
        if (!$id) jsonResponse(false, null, 'No se pudo encolar el reporte', 500);

        jsonResponse(true, ['id' => $id], 'Reporte encolado correctamente', 201);
    }

    jsonResponse(false, null, 'Acción no válida', 400);
} catch (Exception $e) {
    error_log('Error en API report_queue: ' . $e->getMessage());
    jsonResponse(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
