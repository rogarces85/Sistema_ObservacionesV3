<?php
/**
 * API de Exportación
 * Endpoint para generar reportes en diferentes formatos.
 *
 * Todo el cuerpo va dentro de un try/catch: si la generación falla (libreria
 * ausente, memoria, datos inesperados) el usuario debe ver una pagina con el
 * motivo, no una pestana en blanco.
 */

require_once '../config/config.php';
require_once '../config/constants.php';
require_once '../models/Observation.php';
require_once '../models/Exporter.php';

/**
 * Pagina de aviso para el usuario final. Se usa tanto para errores como para
 * "no hay datos", porque la descarga se abre en una pestana aparte.
 */
function exportAviso(string $titulo, string $mensaje, string $detalle = '', int $codigo = 400): void
{
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($codigo);
    header('Content-Type: text/html; charset=utf-8');

    $esError = $codigo >= 500 || $codigo === 400;
    $icono = $esError ? '&#9888;' : '&#8505;';

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . htmlspecialchars($titulo) . '</title>'
        . '<link rel="stylesheet" href="../assets/css/tokens.css">'
        . '<style>'
        . 'body{font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:#f6f8fb;'
        . 'color:#1e293b;margin:0;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:1.5rem}'
        . '.aviso{background:#fff;border:1px solid #e2e8f0;border-radius:12px;max-width:34rem;width:100%;'
        . 'padding:2rem;box-shadow:0 10px 30px rgba(15,23,42,.08);text-align:center}'
        . '.aviso .icono{font-size:2.5rem;line-height:1;color:#0B71B9}'
        . '.aviso h1{font-size:1.15rem;margin:.75rem 0 .5rem;color:#003366}'
        . '.aviso p{margin:0 0 1rem;line-height:1.5}'
        . '.aviso .detalle{font-size:.8125rem;color:#64748b;background:#f1f5f9;border-radius:8px;'
        . 'padding:.75rem;text-align:left;word-break:break-word;margin-bottom:1rem}'
        . '.aviso a{display:inline-block;background:#0B71B9;color:#fff;text-decoration:none;'
        . 'padding:.55rem 1.25rem;border-radius:8px;font-weight:600}'
        . '</style></head><body><div class="aviso">'
        . '<div class="icono">' . $icono . '</div>'
        . '<h1>' . htmlspecialchars($titulo) . '</h1>'
        . '<p>' . htmlspecialchars($mensaje) . '</p>';

    if ($detalle !== '') {
        echo '<div class="detalle">' . htmlspecialchars($detalle) . '</div>';
    }

    echo '<a href="javascript:window.close()">Cerrar</a>'
        . '</div></body></html>';
    exit;
}

if (!isset($_SESSION['user_id'])) {
    exportAviso('Sesión expirada', 'Debe iniciar sesión nuevamente para exportar reportes.', '', 401);
}

try {

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

// Matriz mensual de cumplimiento: la misma tabla que muestran las pestañas
// "Plazos Entrega" y "Uso Validador" del módulo Reportes.
if ($reportType === 'plazo_matriz' || $reportType === 'validador_matriz') {
    $esPlazo = $reportType === 'plazo_matriz';
    $comunaIds = $comuna_id ? [(int)$comuna_id] : [];

    $detalle = $esPlazo
        ? $obsModel->reportePlazoMensual((int)$year, $mesesFiltro, $userId, $userRole, $comunaIds, $establecimiento_id)
        : $obsModel->reporteValidadorMensual((int)$year, $mesesFiltro, $userId, $userRole, $comunaIds, $establecimiento_id);

    if (empty($detalle)) {
        exportAviso('Sin datos para exportar', 'No se encontraron registros con los filtros seleccionados. Ajuste el período, la comuna o el establecimiento y vuelva a intentarlo.', '', 404);
    }

    $matriz = $exporter->armarMatrizMensual(
        $detalle,
        $mesesFiltro,
        $esPlazo ? 'dentro' : 'usa',
        $esPlazo ? 'fuera' : 'no_usa'
    );

    // El periodo se arma con los meses efectivamente incluidos en la matriz.
    $meses = $matriz['meses'];
    $periodo = count($meses) === 1
        ? $meses[0] . ' ' . $year
        : (count($meses) > 1 ? $meses[0] . ' a ' . end($meses) . ' ' . $year : (string)$year);

    $meta = [
        'titulo' => $esPlazo
            ? 'Plazo de entrega por establecimiento y mes'
            : 'Uso de validador local por establecimiento y mes',
        'periodo' => $periodo,
        'etiquetaOk' => $esPlazo ? 'Dentro de plazo' : 'Usa validador',
        'etiquetaFalla' => $esPlazo ? 'Fuera de plazo' : 'No usa validador',
    ];

    $baseName = $esPlazo ? 'Plazo_Entrega_Matriz' : 'Uso_Validador_Matriz';

    if ($format === 'pdf') {
        $exporter->exportMatrizMensualPDF($matriz, $meta, "{$baseName}_{$year}_{$timestamp}.pdf");
    } else {
        $exporter->exportMatrizMensualExcel($matriz, $meta, "{$baseName}_{$year}_{$timestamp}.xlsx");
    }
    exit;
}

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
        exportAviso('Sin datos para exportar', 'No se encontraron registros con los filtros seleccionados. Ajuste el período, la comuna o el establecimiento y vuelva a intentarlo.', '', 404);
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
        exportAviso('Sin datos para exportar', 'No se encontraron registros con los filtros seleccionados. Ajuste el período, la comuna o el establecimiento y vuelva a intentarlo.', '', 404);
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
        exportAviso('Formato no disponible', 'Este reporte solo puede exportarse en Excel.', 'format=' . $format, 400);
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
    exportAviso('Sin datos para exportar', 'No se encontraron registros con los filtros seleccionados. Ajuste el período, la comuna o el establecimiento y vuelva a intentarlo.', '', 404);
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
        exportAviso('Formato no disponible', 'Los formatos admitidos son Excel y PDF.', 'format=' . $format, 400);
}

} catch (Throwable $e) {
    // Sin esto, un fallo de TCPDF/PhpSpreadsheet deja la pestaña en blanco.
    error_log('Error en exportacion (' . ($reportType ?? '?') . '/' . ($format ?? '?') . '): '
        . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());

    exportAviso(
        'No se pudo generar el archivo',
        'Ocurrió un problema al generar la exportación. Si el problema persiste, informe al administrador del sistema.',
        $e->getMessage(),
        500
    );
}
