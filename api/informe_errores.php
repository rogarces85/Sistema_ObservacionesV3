<?php
/**
 * API de Informe de Errores REM
 * Genera informe trimestral/anual de errores (tipo_error = 'ERROR')
 * Soporta formato JSON (web) y PDF (descarga)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Observation.php';
require_once __DIR__ . '/../models/Exporter.php';

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

$userRole = $_SESSION['rol'];
$userId = $_SESSION['user_id'];

// Solo supervisores pueden generar informes
if ($userRole !== ROL_SUPERVISOR) {
    jsonResponse(false, null, 'Solo los supervisores pueden generar informes', 403);
}

$tipo = $_GET['tipo'] ?? '';
$trimestre = isset($_GET['trimestre']) ? (int)$_GET['trimestre'] : null;
$anio = (int)($_GET['anio'] ?? $_SESSION['year'] ?? date('Y'));
$format = $_GET['format'] ?? 'json';

// Validar tipo
if (!in_array($tipo, ['trimestral', 'anual'])) {
    jsonResponse(false, null, 'El parámetro tipo debe ser "trimestral" o "anual"', 400);
}

// Validar trimestre
if ($tipo === 'trimestral' && ($trimestre < 1 || $trimestre > 4)) {
    jsonResponse(false, null, 'El trimestre debe estar entre 1 y 4', 400);
}

try {
    $obsModel = new Observation();
    $data = $obsModel->getErroresInforme($anio, $tipo === 'trimestral' ? $trimestre : null, $userId, $userRole);

    // Nombre del período para mostrar
    if ($tipo === 'trimestral') {
        $periodo = match ($trimestre) {
            1 => '1° Trimestre',
            2 => '2° Trimestre',
            3 => '3° Trimestre',
            4 => '4° Trimestre',
        };
    } else {
        $periodo = 'Anual';
    }

    if ($format === 'pdf') {
        // Generar PDF
        $exporter = new Exporter();
        $filename = "Informe_Errores_{$periodo}_{$anio}.pdf";
        $exporter->exportInformeErroresPDF($data, "$periodo $anio", $filename);
    } else {
        // Retornar JSON para vista web
        jsonResponse(true, [
            'datos' => $data,
            'total' => count($data),
            'periodo' => "$periodo $anio",
            'emitido' => date('d/m/Y H:i')
        ], 'Informe generado exitosamente');
    }
} catch (Exception $e) {
    jsonResponse(false, null, 'Error al generar el informe: ' . $e->getMessage(), 500);
}
