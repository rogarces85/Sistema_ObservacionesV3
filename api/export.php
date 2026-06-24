<?php
/**
 * API de Exportación
 * Endpoint para generar reportes en diferentes formatos
 */

require_once '../config/config.php';
require_once '../config/constants.php';
require_once '../models/Observation.php';
require_once '../models/Exporter.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die('No autenticado');
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['rol'];
$format = $_GET['format'] ?? 'excel';
$year = $_GET['year'] ?? date('Y');
$reportType = $_GET['report_type'] ?? 'general';
$month = $_GET['month'] ?? null;
$months = $_GET['months'] ?? null;
$estado = $_GET['estado'] ?? null;
$establecimiento_id = $_GET['establecimiento_id'] ?? null;
$comuna_id = $_GET['comuna_id'] ?? null;
$mesesFiltro = [];
if ($month) {
    $mesesFiltro = [$month];
} elseif ($months) {
    $mesesFiltro = array_filter(array_map('trim', explode(',', $months)));
}
$comunaIdsFiltro = $comuna_id ? [(int)$comuna_id] : [];

$obsModel = new Observation();
$exporter = new Exporter();
$timestamp = date('Y-m-d_His');

// Reporte detallado jerárquico (PDF)
if ($reportType === 'detallado') {
    $filters = ['anio' => $year];
    if ($comuna_id) $filters['comuna_id'] = $comuna_id;
    if ($establecimiento_id) $filters['establecimiento_id'] = $establecimiento_id;
    if ($month) $filters['mes'] = $month;
    if ($months) $filters['meses'] = $mesesFiltro;
    if ($estado) $filters['estado'] = $estado;

    $data = $obsModel->reporteDetalladoPDF($filters, $userId, $userRole);

    if (empty($data)) {
        http_response_code(404);
        die('No se encontraron datos para el reporte detallado');
    }

    $filterDesc = $year;
    if ($comuna_id) $filterDesc .= '_comuna';
    if ($establecimiento_id) $filterDesc .= '_est';
    $filename = "Reporte_Detallado_REM_{$filterDesc}_{$timestamp}.pdf";

    $pdfFilters = [
        'anio' => $year,
        'comuna' => '',
        'establecimiento' => '',
        'mes' => $month,
        'estado' => $estado
    ];

    $exporter->exportDetalladoPDF($data, $filename, $pdfFilters);
    exit;
}

// Reportes específicos (errores, fuera de plazo, validador, serie, hoja)
$specificReports = [
    'errores_mes', 'errores_establecimiento', 'errores_comuna',
    'fuera_plazo_mes', 'fuera_plazo_establecimiento', 'fuera_plazo_comuna',
    'validador_mes', 'validador_establecimiento', 'validador_comuna',
    'serie_detalle', 'hoja_detalle'
];

if (in_array($reportType, $specificReports)) {
    $reportMethods = [
        'errores_mes' => 'reporteErroresPorMes',
        'errores_establecimiento' => 'reporteErroresPorEstablecimiento',
        'errores_comuna' => 'reporteErroresPorComuna',
        'fuera_plazo_mes' => 'reporteFueraPlazoPorMes',
        'fuera_plazo_establecimiento' => 'reporteFueraPlazoPorEstablecimiento',
        'fuera_plazo_comuna' => 'reporteFueraPlazoPorComuna',
        'validador_mes' => 'reporteValidadorPorMes',
        'validador_establecimiento' => 'reporteValidadorPorEstablecimiento',
        'validador_comuna' => 'reporteValidadorPorComuna',
        'serie_detalle' => 'reportePorSerieDetalle',
        'hoja_detalle' => 'reportePorHojaDetalle'
    ];

    $method = $reportMethods[$reportType];
    $filteredReports = ['errores_establecimiento', 'fuera_plazo_establecimiento', 'validador_establecimiento', 'serie_detalle', 'hoja_detalle'];
    if (in_array($reportType, $filteredReports, true)) {
        $data = $obsModel->$method($year, $userId, $userRole, $mesesFiltro, $comunaIdsFiltro, $establecimiento_id);
    } else {
        $data = $obsModel->$method($year, $userId, $userRole);
    }

    if (empty($data)) {
        http_response_code(404);
        die('No se encontraron datos para este reporte');
    }

    $reportNames = [
        'errores_mes' => 'Errores_por_Mes',
        'errores_establecimiento' => 'Errores_por_Establecimiento',
        'errores_comuna' => 'Errores_por_Comuna',
        'fuera_plazo_mes' => 'Fuera_Plazo_por_Mes',
        'fuera_plazo_establecimiento' => 'Fuera_Plazo_por_Establecimiento',
        'fuera_plazo_comuna' => 'Fuera_Plazo_por_Comuna',
        'validador_mes' => 'Validador_por_Mes',
        'validador_establecimiento' => 'Validador_por_Establecimiento',
        'validador_comuna' => 'Validador_por_Comuna',
        'serie_detalle' => 'Por_Serie_REM',
        'hoja_detalle' => 'Por_Hoja_REM'
    ];

    $baseName = $reportNames[$reportType];

    if ($format === 'excel' || $format === 'xlsx') {
        $filename = "{$baseName}_{$year}_{$timestamp}.xlsx";
        $exporter->exportErroresExcel($data, $filename, $reportType);
    } else {
        http_response_code(400);
        die('Formato no válido para este tipo de reporte. Use: excel');
    }
    exit;
}

// Exportación general (existing functionality)
$filters = ['anio' => $year];
if ($month) $filters['mes'] = $month;
if ($months) $filters['meses'] = $mesesFiltro;
if ($estado) $filters['estado'] = $estado;
if ($establecimiento_id) $filters['establecimiento_id'] = $establecimiento_id;

$observations = $obsModel->getWithFilters($filters);

if (empty($observations)) {
    http_response_code(404);
    die('No se encontraron observaciones para exportar');
}

$data = $exporter->prepareObservationsData($observations);
$headers = $exporter->getObservationsHeaders();

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
        if ($month) $title .= " - {$month}";
        $exporter->exportToPDF($data, $filename, $headers, $title);
        break;

    default:
        http_response_code(400);
        die('Formato no válido. Use: excel o pdf');
}
