<?php
/**
 * API del Boletin Informativo de Errores REM
 *
 * Documento ejecutivo agrupado Comuna > Establecimiento > Mes, con el
 * detalle de la observacion, la respuesta del establecimiento y la gestion
 * del supervisor. Solo incluye observaciones con tipo_error = 'ERROR'.
 *
 * Formatos: json (vista en pantalla), excel, pdf.
 *
 * Endpoint propio en vez de una rama de api/export.php: ese archivo solo
 * valida que exista sesion y no comprueba rol, asi que meter aqui el
 * boletin obligaria a un guard parcial dentro de un despachador compartido.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../models/Observation.php';
require_once __DIR__ . '/../models/Exporter.php';

function jsonResponse($success, $data = null, $message = '', $statusCode = 200)
{
    // Descarta cualquier salida previa: un aviso suelto invalidaria el JSON.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
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

$userRole = $_SESSION['rol'] ?? '';
$userId = $_SESSION['user_id'] ?? null;

// El boletin es un documento institucional: solo supervisores.
// El menu lateral ademas oculta la pagina, pero quien decide es el backend.
if ($userRole !== ROL_SUPERVISOR) {
    jsonResponse(false, null, 'Solo los supervisores pueden generar el boletin', 403);
}

$anio = (int) ($_GET['anio'] ?? $_SESSION['year'] ?? date('Y'));
$periodoTipo = $_GET['periodo'] ?? 'anual';
$trimestre = isset($_GET['trimestre']) ? (int) $_GET['trimestre'] : null;
$mes = $_GET['mes'] ?? '';
$comunaId = isset($_GET['comuna_id']) && $_GET['comuna_id'] !== '' ? (int) $_GET['comuna_id'] : null;
$establecimientoId = isset($_GET['establecimiento_id']) && $_GET['establecimiento_id'] !== '' ? (int) $_GET['establecimiento_id'] : null;
$format = $_GET['format'] ?? 'json';

if (!in_array($format, ['json', 'excel', 'pdf'], true)) {
    jsonResponse(false, null, 'Formato no valido. Use json, excel o pdf', 400);
}
if (!in_array($periodoTipo, ['anual', 'trimestral', 'mensual'], true)) {
    jsonResponse(false, null, 'El periodo debe ser anual, trimestral o mensual', 400);
}
if ($periodoTipo === 'trimestral' && ($trimestre < 1 || $trimestre > 4)) {
    jsonResponse(false, null, 'El trimestre debe estar entre 1 y 4', 400);
}
if ($mes !== '' && !in_array($mes, $MESES, true)) {
    jsonResponse(false, null, 'Mes no valido', 400);
}
if ($anio < 2020 || $anio > (int) date('Y') + 1) {
    jsonResponse(false, null, 'Año fuera de rango', 400);
}

$TRIMESTRES = [
    1 => ['Enero', 'Febrero', 'Marzo'],
    2 => ['Abril', 'Mayo', 'Junio'],
    3 => ['Julio', 'Agosto', 'Septiembre'],
    4 => ['Octubre', 'Noviembre', 'Diciembre'],
];

try {
    $filters = ['anio' => $anio];

    if ($comunaId) {
        $filters['comuna_id'] = $comunaId;
    }
    if ($establecimientoId) {
        $filters['establecimiento_id'] = $establecimientoId;
    }

    if ($periodoTipo === 'mensual' && $mes !== '') {
        $filters['mes'] = $mes;
        $periodo = "$mes $anio";
    } elseif ($periodoTipo === 'trimestral') {
        $filters['meses'] = $TRIMESTRES[$trimestre];
        $periodo = "{$trimestre}° Trimestre $anio (" . implode(' - ', $TRIMESTRES[$trimestre]) . ')';
    } else {
        if ($mes !== '') {
            $filters['mes'] = $mes;
            $periodo = "$mes $anio";
        } else {
            $periodo = "Año $anio";
        }
    }

    $obsModel = new Observation();
    $datos = $obsModel->getBoletinErrores($filters, $userId, $userRole);
    $resumen = Observation::resumenBoletin($datos);

    $meta = ['periodo' => $periodo, 'resumen' => $resumen];
    $stamp = date('Ymd');
    $slug = preg_replace('/[^A-Za-z0-9]+/', '_', $periodo);

    if ($format === 'excel') {
        $exporter = new Exporter();
        $exporter->exportBoletinExcel($datos, $meta, "Boletin_Errores_REM_{$slug}_{$stamp}.xlsx");
        exit;
    }

    if ($format === 'pdf') {
        $exporter = new Exporter();
        $exporter->exportBoletinPDF($datos, $meta, "Boletin_Errores_REM_{$slug}_{$stamp}.pdf");
        exit;
    }

    jsonResponse(true, [
        'datos' => $datos,
        'total' => count($datos),
        'resumen' => $resumen,
        'periodo' => $periodo,
        'emitido' => date('d/m/Y H:i')
    ], 'Boletin generado exitosamente');

} catch (Throwable $e) {
    error_log('Error al generar el boletin: ' . $e->getMessage());
    jsonResponse(false, null, 'Error al generar el boletin: ' . $e->getMessage(), 500);
}
