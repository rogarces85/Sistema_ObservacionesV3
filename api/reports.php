<?php
/**
 * API de Reportes
 * Devuelve datos agregados para gráficos y tablas de reportes
 * Soporta filtros por año y respeta permisos por rol
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
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
$year = $_GET['anio'] ?? $_GET['year'] ?? $_SESSION['anio_trabajo'] ?? date('Y');
$report = $_GET['report'] ?? 'all';

function obtenerFiltrosAnaliticos()
{
    $mesesPorTrimestre = [
        1 => ['Enero', 'Febrero', 'Marzo'],
        2 => ['Abril', 'Mayo', 'Junio'],
        3 => ['Julio', 'Agosto', 'Septiembre'],
        4 => ['Octubre', 'Noviembre', 'Diciembre']
    ];

    $trimestre = isset($_GET['trimestre']) && $_GET['trimestre'] !== '' ? (int)$_GET['trimestre'] : null;
    $mes = trim($_GET['mes'] ?? '');

    if ($trimestre !== null && !isset($mesesPorTrimestre[$trimestre])) {
        jsonResponse(false, null, 'Trimestre no válido', 400);
    }

    if ($trimestre !== null && $mes !== '' && !in_array($mes, $mesesPorTrimestre[$trimestre], true)) {
        jsonResponse(false, null, 'El mes seleccionado no corresponde al trimestre indicado', 400);
    }

    return [
        'anio' => (int)($_GET['anio'] ?? $_GET['year'] ?? $_SESSION['anio_trabajo'] ?? date('Y')),
        'trimestre' => $trimestre,
        'mes' => $mes !== '' ? $mes : null,
        'meses' => $mes !== '' ? [$mes] : ($trimestre !== null ? $mesesPorTrimestre[$trimestre] : []),
        'comuna_id' => !empty($_GET['comuna_id']) ? (int)$_GET['comuna_id'] : null,
        'establecimiento_id' => !empty($_GET['establecimiento_id']) ? (int)$_GET['establecimiento_id'] : null
    ];
}

function jsonReporteAnalitico(Observation $obsModel, $categoria, array $filtros, $userId, $userRole)
{
    $categoriasPermitidas = [
        'reportes-analiticos',
        'errores_establecimiento',
        'plazos_entrega',
        'uso_validador',
        'errores_serie',
        'errores_hoja'
    ];

    if (!in_array($categoria, $categoriasPermitidas, true)) {
        jsonResponse(false, null, 'Categoría de reporte no válida', 400);
    }

    if (!empty($filtros['comuna_id']) && !empty($filtros['establecimiento_id']) && !$obsModel->establecimientoPerteneceAComuna($filtros['establecimiento_id'], $filtros['comuna_id'])) {
        jsonResponse(false, null, 'El establecimiento no pertenece a la comuna seleccionada', 400);
    }

    if ($categoria === 'reportes-analiticos') {
        jsonResponse(true, $obsModel->getReportesAnaliticos($filtros['anio'], $filtros, $userId, $userRole));
    }

    jsonResponse(true, [
        'filtros' => $filtros,
        'totales_globales' => $obsModel->getTotalesAnaliticos($filtros['anio'], $filtros, $userId, $userRole),
        'reportes' => [$obsModel->getReporteAnaliticoCategoria($categoria, $filtros['anio'], $filtros, $userId, $userRole)]
    ]);
}

try {
    $obsModel = new Observation();

    switch ($report) {
        case 'reportes-analiticos':
        case 'errores_establecimiento':
        case 'plazos_entrega':
        case 'uso_validador':
        case 'errores_serie':
        case 'errores_hoja':
            jsonReporteAnalitico($obsModel, $report, obtenerFiltrosAnaliticos(), $userId, $userRole);
            break;

        // Reportes existentes
        case 'mes':
            jsonResponse(true, $obsModel->reportePorMes($year, $userId, $userRole));
            break;

        case 'establecimiento':
            jsonResponse(true, $obsModel->reportePorEstablecimiento($year, $userId, $userRole));
            break;

        case 'comuna':
            jsonResponse(true, $obsModel->reportePorComuna($year, $userId, $userRole));
            break;

        case 'serie':
            jsonResponse(true, $obsModel->reportePorSerie($year, $userId, $userRole));
            break;

        case 'plazo':
            jsonResponse(true, $obsModel->reportePorPlazo($year, $userId, $userRole));
            break;

        case 'validador':
            jsonResponse(true, $obsModel->reportePorValidador($year, $userId, $userRole));
            break;

        // Nuevos reportes agregados con ambos lados
        case 'plazo-agregado':
        case 'validador-agregado':
            $meses = $_GET['meses'] ?? [];
            if (!is_array($meses)) $meses = $meses ? [$meses] : [];
            if ($report === 'plazo-agregado') {
                jsonResponse(true, [
                    'establecimientos' => $obsModel->reportePlazoAgregado((int)$year, $meses),
                    'detalle_mensual' => $obsModel->reportePlazoMensual((int)$year)
                ]);
            } else {
                jsonResponse(true, [
                    'establecimientos' => $obsModel->reporteValidadorAgregado((int)$year, $meses),
                    'detalle_mensual' => $obsModel->reporteValidadorMensual((int)$year)
                ]);
            }
            break;

        // GRUPO A: Reportes de Errores
        case 'errores_mes':
            jsonResponse(true, $obsModel->reporteErroresPorMes($year, $userId, $userRole));
            break;

        case 'errores_establecimiento':
            jsonResponse(true, $obsModel->reporteErroresPorEstablecimiento($year, $userId, $userRole));
            break;

        case 'errores_comuna':
            jsonResponse(true, $obsModel->reporteErroresPorComuna($year, $userId, $userRole));
            break;

        // GRUPO B: Reportes Fuera de Plazo
        case 'fuera_plazo_mes':
            jsonResponse(true, $obsModel->reporteFueraPlazoPorMes($year, $userId, $userRole));
            break;

        case 'fuera_plazo_establecimiento':
            jsonResponse(true, $obsModel->reporteFueraPlazoPorEstablecimiento($year, $userId, $userRole));
            break;

        case 'fuera_plazo_comuna':
            jsonResponse(true, $obsModel->reporteFueraPlazoPorComuna($year, $userId, $userRole));
            break;

        // GRUPO C: Reportes de Uso del Validador
        case 'validador_mes':
            jsonResponse(true, $obsModel->reporteValidadorPorMes($year, $userId, $userRole));
            break;

        case 'validador_establecimiento':
            jsonResponse(true, $obsModel->reporteValidadorPorEstablecimiento($year, $userId, $userRole));
            break;

        case 'validador_comuna':
            jsonResponse(true, $obsModel->reporteValidadorPorComuna($year, $userId, $userRole));
            break;

        // GRUPO D: Reportes Específicos
        case 'serie_detalle':
            jsonResponse(true, $obsModel->reportePorSerieDetalle($year, $userId, $userRole));
            break;

        case 'hoja_detalle':
            jsonResponse(true, $obsModel->reportePorHojaDetalle($year, $userId, $userRole));
            break;

        // Filtros para reportes
        case 'filtros':
            jsonResponse(true, [
                'comunas' => $obsModel->getComunasConDatos($year, $userId, $userRole),
                'establecimientos' => $obsModel->getEstablecimientosConDatos($year, null, $userId, $userRole)
            ]);
            break;

        // Reportes de errores (nueva vista unificada)
        case 'error-reports':
            $meses = $_GET['meses'] ?? [];
            $comunaIds = $_GET['comuna_ids'] ?? [];
            $establecimientoId = !empty($_GET['establecimiento_id']) ? intval($_GET['establecimiento_id']) : null;
            if (!is_array($meses)) $meses = $meses ? [$meses] : [];
            if (!is_array($comunaIds)) $comunaIds = $comunaIds ? [$comunaIds] : [];
            $comunaIds = array_map('intval', $comunaIds);

            jsonResponse(true, [
                'errores_establecimiento' => $obsModel->reporteErroresPorEstablecimiento($year, $userId, $userRole, $meses, $comunaIds, $establecimientoId),
                'fuera_plazo_establecimiento' => $obsModel->reporteFueraPlazoPorEstablecimiento($year, $userId, $userRole, $meses, $comunaIds, $establecimientoId),
                'no_validador_establecimiento' => $obsModel->reporteNoValidadorPorEstablecimiento($year, $userId, $userRole, $meses, $comunaIds, $establecimientoId),
                'errores_serie' => $obsModel->reporteErroresPorSerie($year, $userId, $userRole, $meses, $comunaIds, $establecimientoId),
                'errores_hoja' => $obsModel->reporteErroresPorHoja($year, $userId, $userRole, $meses, $comunaIds, $establecimientoId)
            ]);
            break;

        case 'all':
        default:
            jsonResponse(true, [
                'mes' => $obsModel->reportePorMes($year, $userId, $userRole),
                'establecimiento' => $obsModel->reportePorEstablecimiento($year, $userId, $userRole),
                'comuna' => $obsModel->reportePorComuna($year, $userId, $userRole),
                'serie' => $obsModel->reportePorSerie($year, $userId, $userRole),
                'plazo' => $obsModel->reportePorPlazo($year, $userId, $userRole),
                'validador' => $obsModel->reportePorValidador($year, $userId, $userRole),
                'errores_mes' => $obsModel->reporteErroresPorMes($year, $userId, $userRole),
                'errores_establecimiento' => $obsModel->reporteErroresPorEstablecimiento($year, $userId, $userRole),
                'errores_comuna' => $obsModel->reporteErroresPorComuna($year, $userId, $userRole),
                'fuera_plazo_mes' => $obsModel->reporteFueraPlazoPorMes($year, $userId, $userRole),
                'fuera_plazo_establecimiento' => $obsModel->reporteFueraPlazoPorEstablecimiento($year, $userId, $userRole),
                'fuera_plazo_comuna' => $obsModel->reporteFueraPlazoPorComuna($year, $userId, $userRole),
                'validador_mes' => $obsModel->reporteValidadorPorMes($year, $userId, $userRole),
                'validador_establecimiento' => $obsModel->reporteValidadorPorEstablecimiento($year, $userId, $userRole),
                'validador_comuna' => $obsModel->reporteValidadorPorComuna($year, $userId, $userRole),
                'serie_detalle' => $obsModel->reportePorSerieDetalle($year, $userId, $userRole),
                'hoja_detalle' => $obsModel->reportePorHojaDetalle($year, $userId, $userRole)
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Error en API reports: " . $e->getMessage());
    jsonResponse(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
