<?php
/**
 * API de Exportación
 * Endpoint para generar reportes en diferentes formatos
 */

session_start();
require_once '../config/config.php';
require_once '../config/constants.php';
require_once '../models/Observation.php';
require_once '../models/Exporter.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('No autenticado');
}

$format = $_GET['format'] ?? 'excel';
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? null;
$estado = $_GET['estado'] ?? null;
$establecimiento_id = $_GET['establecimiento_id'] ?? null;

$obsModel = new Observation();
$exporter = new Exporter();

// Preparar filtros
$filters = ['anio' => $year];

if ($month) {
    $filters['mes'] = $month;
}

if ($estado) {
    $filters['estado'] = $estado;
}

if ($establecimiento_id) {
    $filters['establecimiento_id'] = $establecimiento_id;
}

// Obtener observaciones
$observations = $obsModel->getWithFilters($filters);

if (empty($observations)) {
    http_response_code(404);
    die('No se encontraron observaciones para exportar');
}

// Preparar datos y headers
$data = $exporter->prepareObservationsData($observations);
$headers = $exporter->getObservationsHeaders();

// Generar nombre de archivo
$timestamp = date('Y-m-d_His');
$filterDesc = $month ? "_{$month}" : '';
$filterDesc .= $estado ? "_{$estado}" : '';

switch ($format) {
    case 'excel':
    case 'xlsx':
        $filename = "Observaciones_REM_{$year}{$filterDesc}_{$timestamp}.xlsx";
        $exporter->exportToExcel($data, $filename, $headers);
        break;

    case 'pdf':
        $filename = "Observaciones_REM_{$year}{$filterDesc}_{$timestamp}.pdf";
        $title = "Reporte de Observaciones REM - Año {$year}";
        if ($month) {
            $title .= " - {$month}";
        }
        $exporter->exportToPDF($data, $filename, $headers, $title);
        break;

    case 'csv':
        $filename = "Observaciones_REM_{$year}{$filterDesc}_{$timestamp}.csv";
        $exporter->exportToCSV($data, $filename, $headers);
        break;

    default:
        http_response_code(400);
        die('Formato no válido. Use: excel, pdf o csv');
}
