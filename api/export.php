<?php
/**
 * API de Exportación - Sistema de Observaciones REM
 * Exportación híbrida: ≤1000 registros sync, >1000 queue asíncrono
 * Límite máximo: 50,000 registros
 * Formatos: excel (xlsx), pdf, csv
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../models/Observacion.php';
require_once __DIR__ . '/../models/Observation.php';
require_once __DIR__ . '/../models/Establecimiento.php';
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../models/Exporter.php';

const LIMITE_SYNC = 1000;
const LIMITE_MAXIMO = 50000;

function responder($exito, $datos = null, $error = '', $codigo = 200)
{
    http_response_code($codigo);
    $respuesta = ['success' => $exito];
    if ($exito) {
        $respuesta['data'] = $datos;
    } else {
        $respuesta['error'] = $error;
    }
    $respuesta['code'] = $codigo;
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['autenticado'] !== true) {
    responder(false, null, 'No autorizado', 401);
}

$metodo = $_SERVER['REQUEST_METHOD'];
$usuarioId = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'];

if ($metodo === 'GET') {
    $accion = $_GET['accion'] ?? '';

    if ($accion === 'contar') {
        $filtros = obtenerFiltros();
        $modeloObservacion = new Observacion();
        $resultado = $modeloObservacion->listar($filtros, $usuarioId, $rol);
        responder(true, ['total' => $resultado['total'], 'limite_maximo' => LIMITE_MAXIMO]);
    }

    elseif ($accion === 'preview') {
        $filtros = obtenerFiltros();
        $filtros['pagina'] = 1;
        $filtros['porPagina'] = 20;
        $modeloObservacion = new Observacion();
        $resultado = $modeloObservacion->listar($filtros, $usuarioId, $rol);
        responder(true, $resultado);
    }

    elseif ($accion === 'establecimientos') {
        $comunaId = $_GET['comuna_id'] ?? null;
        $locationModel = new Location();
        if ($comunaId) {
            $establecimientos = $locationModel->getEstablecimientosByComuna($comunaId);
        } else {
            $establecimientos = $locationModel->getAllEstablecimientos();
        }
        responder(true, $establecimientos);
    }

    else {
        responder(false, null, 'Acción no válida', 400);
    }
}

if ($metodo === 'POST') {
    CSRF::validateRequest();

    $entrada = file_get_contents('php://input');
    $cuerpo = json_decode($entrada, true);

    $formato = $cuerpo['formato'] ?? 'excel';
    $tipoReporte = $cuerpo['tipo_reporte'] ?? 'general';

    if (!in_array($formato, ['excel', 'pdf', 'csv'])) {
        responder(false, null, 'Formato no válido. Use: excel, pdf o csv', 400);
    }

    $tiposAnaliticos = ['errores_establecimiento', 'plazos_entrega', 'uso_validador', 'errores_serie', 'errores_hoja'];

    if (!in_array($tipoReporte, array_merge(['general', 'detallado'], $tiposAnaliticos), true)) {
        responder(false, null, 'Tipo de reporte no válido', 400);
    }

    if (in_array($tipoReporte, $tiposAnaliticos, true)) {
        generarExportacionAnalitica($cuerpo, $formato, $tipoReporte, $usuarioId, $rol);
    }

    $filtros = [
        'anio' => $cuerpo['anio'] ?? date('Y'),
        'mes' => $cuerpo['mes'] ?? null,
        'estado' => $cuerpo['estado'] ?? null,
        'establecimiento_id' => $cuerpo['establecimiento_id'] ?? null,
        'tipo_error' => $cuerpo['tipo_error'] ?? null,
        'busqueda' => $cuerpo['busqueda'] ?? null
    ];

    $modeloObservacion = new Observacion();
    $resultado = $modeloObservacion->listar($filtros, $usuarioId, $rol);
    $totalRegistros = $resultado['total'];

    if ($totalRegistros === 0) {
        responder(false, null, 'No se encontraron observaciones con los filtros seleccionados', 404);
    }

    if ($totalRegistros > LIMITE_MAXIMO) {
        responder(false, null, "El número de registros ({$totalRegistros}) excede el límite máximo de " . number_format(LIMITE_MAXIMO) . ". Refine los filtros.", 400);
    }

    if ($totalRegistros <= LIMITE_SYNC) {
        generarExportacionSincrona($totalRegistros, $filtros, $formato, $tipoReporte, $usuarioId, $rol);
    } else {
        encolarExportacion($filtros, $formato, $tipoReporte, $totalRegistros, $usuarioId);
    }
}

function generarExportacionAnalitica($cuerpo, $formato, $tipoReporte, $usuarioId, $rol)
{
    $filtros = obtenerFiltrosAnaliticosDesdeCuerpo($cuerpo);
    $modeloObservacion = new Observation();

    if (!empty($filtros['comuna_id']) && !empty($filtros['establecimiento_id']) && !$modeloObservacion->establecimientoPerteneceAComuna($filtros['establecimiento_id'], $filtros['comuna_id'])) {
        responder(false, null, 'El establecimiento no pertenece a la comuna seleccionada', 400);
    }

    $reporte = $modeloObservacion->getReporteAnaliticoCategoria($tipoReporte, $filtros['anio'], $filtros, $usuarioId, $rol);
    $totalRegistros = count($reporte['resultados'] ?? []);

    if ($totalRegistros === 0) {
        responder(false, null, 'No hay datos exportables para la categoría seleccionada', 400);
    }

    if ($totalRegistros > LIMITE_MAXIMO) {
        responder(false, null, 'La exportación supera el límite máximo permitido. Ajuste los filtros.', 400);
    }

    $exportador = new Exporter();
    $nombreSeguro = preg_replace('/[^a-z0-9_]+/i', '_', $tipoReporte);
    $extension = $formato === 'excel' ? 'xlsx' : $formato;
    $archivo = "Reporte_Analitico_{$nombreSeguro}_" . date('Y-m-d_His') . ".{$extension}";
    $exportador->exportAnalitico($reporte, $archivo, $formato);
}

function obtenerFiltrosAnaliticosDesdeCuerpo($cuerpo)
{
    $mesesPorTrimestre = [
        1 => ['Enero', 'Febrero', 'Marzo'],
        2 => ['Abril', 'Mayo', 'Junio'],
        3 => ['Julio', 'Agosto', 'Septiembre'],
        4 => ['Octubre', 'Noviembre', 'Diciembre']
    ];
    $trimestre = isset($cuerpo['trimestre']) && $cuerpo['trimestre'] !== '' ? (int)$cuerpo['trimestre'] : null;
    $mes = trim($cuerpo['mes'] ?? '');

    if ($trimestre !== null && !isset($mesesPorTrimestre[$trimestre])) {
        responder(false, null, 'Trimestre no válido', 400);
    }
    if ($trimestre !== null && $mes !== '' && !in_array($mes, $mesesPorTrimestre[$trimestre], true)) {
        responder(false, null, 'El mes seleccionado no corresponde al trimestre indicado', 400);
    }

    return [
        'anio' => (int)($cuerpo['anio'] ?? date('Y')),
        'trimestre' => $trimestre,
        'mes' => $mes !== '' ? $mes : null,
        'meses' => $mes !== '' ? [$mes] : ($trimestre !== null ? $mesesPorTrimestre[$trimestre] : []),
        'comuna_id' => !empty($cuerpo['comuna_id']) ? (int)$cuerpo['comuna_id'] : null,
        'establecimiento_id' => !empty($cuerpo['establecimiento_id']) ? (int)$cuerpo['establecimiento_id'] : null
    ];
}

function obtenerFiltros()
{
    return [
        'anio' => $_GET['anio'] ?? date('Y'),
        'mes' => $_GET['mes'] ?? null,
        'estado' => $_GET['estado'] ?? null,
        'establecimiento_id' => $_GET['establecimiento_id'] ?? null,
        'tipo_error' => $_GET['tipo_error'] ?? null,
        'busqueda' => $_GET['busqueda'] ?? null,
        'pagina' => $_GET['pagina'] ?? 1,
        'porPagina' => $_GET['por_pagina'] ?? 20
    ];
}

function generarExportacionSincrona($totalRegistros, $filtros, $formato, $tipoReporte, $usuarioId, $rol)
{
    $modeloObservacion = new Observacion();
    $datosCompletos = [];
    $pagina = 1;
    $porPagina = 500;

    while (true) {
        $filtrosPagina = array_merge($filtros, ['pagina' => $pagina, 'porPagina' => $porPagina]);
        $resultado = $modeloObservacion->listar($filtrosPagina, $usuarioId, $rol);
        $datosCompletos = array_merge($datosCompletos, $resultado['datos']);
        if (count($datosCompletos) >= $totalRegistros || empty($resultado['datos'])) {
            break;
        }
        $pagina++;
    }

    $timestamp = date('Y-m-d_His');

    if ($formato === 'excel') {
        exportarExcel($datosCompletos, $filtros, $timestamp, $tipoReporte);
    } elseif ($formato === 'pdf') {
        exportarPDF($datosCompletos, $filtros, $timestamp, $tipoReporte);
    } elseif ($formato === 'csv') {
        exportarCSV($datosCompletos, $filtros, $timestamp);
    }
}

function encolarExportacion($filtros, $formato, $tipoReporte, $totalRegistros, $usuarioId)
{
    require_once __DIR__ . '/../models/ReportQueue.php';
    $cola = new ReportQueue();

    $parametros = array_merge($filtros, ['tipo_reporte' => $tipoReporte]);
    $idCola = $cola->enqueue($usuarioId, $tipoReporte, $formato, $parametros);

    if ($idCola) {
        responder(true, [
            'en_cola' => true,
            'id_reporte' => $idCola,
            'total_registros' => $totalRegistros,
            'mensaje' => "Reporte con {$totalRegistros} registros encolado. Se procesará en segundo plano."
        ]);
    } else {
        responder(false, null, 'Error al encolar el reporte', 500);
    }
}

function exportarExcel($datos, $filtros, $timestamp, $tipoReporte)
{
    require_once __DIR__ . '/../vendor/autoload.php';

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $hoja = $spreadsheet->getActiveSheet();

    $titulo = $tipoReporte === 'detallado' ? 'Reporte Detallado de Observaciones REM' : 'Reporte de Observaciones REM';
    $hoja->setCellValue('A1', $titulo);
    $hoja->mergeCells('A1:O1');
    $hoja->getStyle('A1')->getFont()->setSize(16)->setBold(true);
    $hoja->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $filtroDesc = generarDescripcionFiltros($filtros);
    $hoja->setCellValue('A2', $filtroDesc . ' | Generado: ' . date('d/m/Y H:i'));
    $hoja->mergeCells('A2:O2');
    $hoja->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $encabezados = ['ID', 'Año', 'Mes', 'Comuna', 'Establecimiento', 'Código Serie', 'Código Hoja', 'Tipo Error', 'Detalle', 'Plazo Entrega', 'Estado Actual', 'Clasificación', 'Usuario Registro', 'Fecha Creación', 'Fecha Actualización'];

    $col = 'A';
    $fila = 4;
    foreach ($encabezados as $encabezado) {
        $hoja->setCellValue($col . $fila, $encabezado);
        $hoja->getStyle($col . $fila)->getFont()->setBold(true);
        $hoja->getStyle($col . $fila)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF4B5563');
        $hoja->getStyle($col . $fila)->getFont()->getColor()->setARGB('FFFFFFFF');
        $hoja->getStyle($col . $fila)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $col++;
    }

    $fila = 5;
    foreach ($datos as $registro) {
        $valores = [
            $registro['id'],
            $registro['anio'],
            $registro['mes'],
            $registro['comuna_nombre'] ?? '',
            $registro['nombre_corto'] ?? $registro['establecimiento_nombre'] ?? '',
            $registro['codigo_serie'] ?? '',
            $registro['codigo_hoja'] ?? '',
            $registro['tipo_error'],
            $registro['detalle_observacion'] ?? '',
            $registro['plazo_entrega'] ?? '',
            $registro['estado_actual'],
            $registro['clasificacion'] ?? '',
            $registro['usuario_registro_nombre'] ?? '',
            $registro['fecha_creacion'] ?? '',
            $registro['fecha_actualizacion'] ?? ''
        ];
        $col = 'A';
        foreach ($valores as $valor) {
            $hoja->setCellValue($col . $fila, $valor);
            $col++;
        }
        $fila++;
    }

    $ultimaCol = chr(64 + count($encabezados));
    $ultimaFila = $fila - 1;
    $hoja->getStyle('A4:' . $ultimaCol . $ultimaFila)->getBorders()->getAllBorders()
        ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

    foreach (range('A', $ultimaCol) as $c) {
        $hoja->getColumnDimension($c)->setAutoSize(true);
    }

    $nombreArchivo = "Observaciones_REM_{$timestamp}.xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Cache-Control: max-age=0');

    $escritor = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $escritor->save('php://output');
    exit;
}

function exportarPDF($datos, $filtros, $timestamp, $tipoReporte)
{
    require_once __DIR__ . '/../vendor/autoload.php';

    if ($tipoReporte === 'detallado') {
        exportarPDFDetallado($datos, $filtros, $timestamp);
        return;
    }

    $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');
    $pdf->SetCreator('Sistema Observaciones REM');
    $pdf->SetAuthor('Servicio de Salud Osorno');
    $pdf->SetTitle('Reporte de Observaciones REM');
    $pdf->SetMargins(8, 12, 8);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    $pdf->SetAutoPageBreak(true, 12);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'Reporte de Observaciones REM', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 8);
    $filtroDesc = generarDescripcionFiltros($filtros);
    $pdf->Cell(0, 5, $filtroDesc, 0, 1, 'C');
    $pdf->Cell(0, 5, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'C');
    $pdf->Ln(2);

    $anchoColumnas = [12, 14, 22, 28, 38, 22, 18, 18, 60, 20, 18, 22, 28, 28, 28];
    $encabezados = ['ID', 'Año', 'Mes', 'Comuna', 'Establecimiento', 'Serie', 'Hoja', 'Tipo', 'Detalle', 'Plazo', 'Estado', 'Clasificación', 'Registrador', 'F. Creación', 'F. Actualización'];

    $html = '<table border="1" cellpadding="2" cellspacing="0" width="100%">';
    $html .= '<tr style="background-color: #8B1A1A; color: #FFFFFF; font-weight: bold; font-size: 6.5pt;">';
    for ($i = 0; $i < count($encabezados); $i++) {
        $html .= '<th width="' . $anchoColumnas[$i] . '" align="center">' . $encabezados[$i] . '</th>';
    }
    $html .= '</tr>';

    $contadorFilas = 0;
    foreach ($datos as $registro) {
        $estado = $registro['estado_actual'] ?? 'pendiente';
        $colorFondo = obtenerColorFondoEstado($estado);

        $html .= '<tr style="background-color: ' . $colorFondo . '; font-size: 6pt;">';
        $html .= '<td width="' . $anchoColumnas[0] . '" align="center">' . htmlspecialchars($registro['id']) . '</td>';
        $html .= '<td width="' . $anchoColumnas[1] . '" align="center">' . htmlspecialchars($registro['anio']) . '</td>';
        $html .= '<td width="' . $anchoColumnas[2] . '">' . htmlspecialchars($registro['mes']) . '</td>';
        $html .= '<td width="' . $anchoColumnas[3] . '">' . htmlspecialchars($registro['comuna_nombre'] ?? '') . '</td>';
        $html .= '<td width="' . $anchoColumnas[4] . '">' . htmlspecialchars($registro['nombre_corto'] ?? $registro['establecimiento_nombre'] ?? '') . '</td>';
        $html .= '<td width="' . $anchoColumnas[5] . '">' . htmlspecialchars($registro['codigo_serie'] ?? '') . '</td>';
        $html .= '<td width="' . $anchoColumnas[6] . '">' . htmlspecialchars($registro['codigo_hoja'] ?? '') . '</td>';
        $html .= '<td width="' . $anchoColumnas[7] . '">' . htmlspecialchars($registro['tipo_error']) . '</td>';
        $html .= '<td width="' . $anchoColumnas[8] . '">' . htmlspecialchars($registro['detalle_observacion'] ?? '') . '</td>';
        $html .= '<td width="' . $anchoColumnas[9] . '" align="center">' . htmlspecialchars($registro['plazo_entrega'] ?? '') . '</td>';
        $html .= '<td width="' . $anchoColumnas[10] . '" align="center">' . htmlspecialchars($estado) . '</td>';
        $html .= '<td width="' . $anchoColumnas[11] . '">' . htmlspecialchars($registro['clasificacion'] ?? '') . '</td>';
        $html .= '<td width="' . $anchoColumnas[12] . '">' . htmlspecialchars($registro['usuario_registro_nombre'] ?? '') . '</td>';
        $html .= '<td width="' . $anchoColumnas[13] . '">' . htmlspecialchars($registro['fecha_creacion'] ?? '') . '</td>';
        $html .= '<td width="' . $anchoColumnas[14] . '">' . htmlspecialchars($registro['fecha_actualizacion'] ?? '') . '</td>';
        $html .= '</tr>';

        $contadorFilas++;
        if ($contadorFilas % 35 === 0) {
            $html .= '</table>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->AddPage();
            $html = '<table border="1" cellpadding="2" cellspacing="0" width="100%">';
            $html .= '<tr style="background-color: #8B1A1A; color: #FFFFFF; font-weight: bold; font-size: 6.5pt;">';
            for ($i = 0; $i < count($encabezados); $i++) {
                $html .= '<th width="' . $anchoColumnas[$i] . '" align="center">' . $encabezados[$i] . '</th>';
            }
            $html .= '</tr>';
        }
    }

    $html .= '</table>';
    $html .= '<br/><p style="font-size: 7pt; color: #6B7280;">Total registros: ' . count($datos) . '</p>';
    $pdf->writeHTML($html, true, false, true, false, '');

    $nombreArchivo = "Observaciones_REM_{$timestamp}.pdf";
    $pdf->Output($nombreArchivo, 'D');
    exit;
}

function exportarPDFDetallado($datos, $filtros, $timestamp)
{
    require_once __DIR__ . '/../vendor/autoload.php';

    $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');
    $pdf->SetCreator('Sistema Observaciones REM');
    $pdf->SetAuthor('Servicio de Salud Osorno');
    $pdf->SetTitle('Reporte Detallado de Observaciones REM');
    $pdf->SetMargins(8, 12, 8);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);
    $pdf->SetAutoPageBreak(true, 12);
    $pdf->SetFont('helvetica', '', 7);
    $pdf->AddPage();

    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 8, 'Reporte Detallado de Observaciones REM', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 8);
    $filtroDesc = generarDescripcionFiltros($filtros);
    $pdf->Cell(0, 5, $filtroDesc, 0, 1, 'C');
    $pdf->Cell(0, 5, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'C');
    $pdf->Ln(2);

    $anchoColumnas = [28, 42, 18, 100, 35, 12];
    $encabezados = ['COMUNAS', 'ESTABLECIMIENTOS', 'MES', 'DETALLE', 'DETALLE ERROR', 'ERRORES'];

    $datosAgrupados = [];
    $comunaActual = '';
    $establecimientoActual = '';
    $mesActual = '';
    $inicioComuna = 0;
    $inicioEst = 0;
    $inicioMes = 0;

    foreach ($datos as $indice => $fila) {
        $comuna = strtoupper($fila['comuna_nombre'] ?? '');
        $establecimiento = $fila['nombre_corto'] ?? $fila['establecimiento_nombre'] ?? '';
        $mes = strtolower($fila['mes'] ?? '');

        if ($comuna !== $comunaActual) {
            if ($comunaActual !== '') {
                $datosAgrupados[$inicioComuna]['comuna_span'] = $indice - $inicioComuna;
            }
            $inicioComuna = $indice;
            $comunaActual = $comuna;
            $establecimientoActual = '';
            $mesActual = '';
        }

        if ($establecimiento !== $establecimientoActual) {
            if ($establecimientoActual !== '') {
                $datosAgrupados[$inicioEst]['est_span'] = $indice - $inicioEst;
            }
            $inicioEst = $indice;
            $establecimientoActual = $establecimiento;
            $mesActual = '';
        }

        if ($mes !== $mesActual) {
            if ($mesActual !== '') {
                $datosAgrupados[$inicioMes]['mes_span'] = $indice - $inicioMes;
            }
            $inicioMes = $indice;
            $mesActual = $mes;
        }

        $datosAgrupados[$indice] = [
            'comuna' => $comuna,
            'establecimiento' => $establecimiento,
            'mes' => $mes,
            'detalle_observacion' => $fila['detalle_observacion'] ?? '',
            'clasificacion' => $fila['clasificacion'] ?? ($fila['estado_actual'] ?? ''),
            'estado_actual' => $fila['estado_actual'] ?? 'pendiente'
        ];
    }

    if (!empty($datosAgrupados)) {
        $ultimoIndice = count($datos) - 1;
        if (!isset($datosAgrupados[$inicioComuna]['comuna_span'])) {
            $datosAgrupados[$inicioComuna]['comuna_span'] = $ultimoIndice - $inicioComuna + 1;
        }
        if (!isset($datosAgrupados[$inicioEst]['est_span'])) {
            $datosAgrupados[$inicioEst]['est_span'] = $ultimoIndice - $inicioEst + 1;
        }
        if (!isset($datosAgrupados[$inicioMes]['mes_span'])) {
            $datosAgrupados[$inicioMes]['mes_span'] = $ultimoIndice - $inicioMes + 1;
        }
    }

    $html = '<table border="1" cellpadding="2" cellspacing="0" width="100%">';
    $html .= '<tr style="background-color: #8B1A1A; color: #FFFFFF; font-weight: bold; font-size: 7pt;">';
    for ($i = 0; $i < count($encabezados); $i++) {
        $html .= '<th width="' . $anchoColumnas[$i] . '" align="center">' . $encabezados[$i] . '</th>';
    }
    $html .= '</tr>';

    $contadorFilas = 0;
    foreach ($datosAgrupados as $indice => $item) {
        if (!isset($item['detalle_observacion'])) continue;

        $comuna = $item['comuna'];
        $establecimiento = $item['establecimiento'];
        $mes = $item['mes'];
        $detalle = $item['detalle_observacion'];
        $clasificacion = $item['clasificacion'];
        $estado = $item['estado_actual'];

        $comunaSpan = $item['comuna_span'] ?? 1;
        $estSpan = $item['est_span'] ?? 1;
        $mesSpan = $item['mes_span'] ?? 1;

        $colorFondo = obtenerColorFondoEstado($estado);
        if ($contadorFilas % 2 === 0 && $colorFondo === '#FFFFFF') {
            $colorFondo = '#FAFAFA';
        }

        $html .= '<tr style="background-color: ' . $colorFondo . '; font-size: 6.5pt;">';

        if ($comunaSpan > 0) {
            $html .= '<td width="' . $anchoColumnas[0] . '" rowspan="' . $comunaSpan . '" align="center" style="font-weight: bold; vertical-align: middle;">' . htmlspecialchars($comuna) . '</td>';
            $datosAgrupados[$indice]['comuna_span'] = 0;
        }
        if ($estSpan > 0) {
            $html .= '<td width="' . $anchoColumnas[1] . '" rowspan="' . $estSpan . '" style="vertical-align: middle;">' . htmlspecialchars($establecimiento) . '</td>';
            $datosAgrupados[$indice]['est_span'] = 0;
        }
        if ($mesSpan > 0) {
            $html .= '<td width="' . $anchoColumnas[2] . '" rowspan="' . $mesSpan . '" align="center" style="vertical-align: middle;">' . htmlspecialchars($mes) . '</td>';
            $datosAgrupados[$indice]['mes_span'] = 0;
        }

        $html .= '<td width="' . $anchoColumnas[3] . '">' . htmlspecialchars($detalle) . '</td>';
        $html .= '<td width="' . $anchoColumnas[4] . '" align="center">' . htmlspecialchars($clasificacion) . '</td>';
        $html .= '<td width="' . $anchoColumnas[5] . '" align="center" style="font-weight: bold;">1</td>';
        $html .= '</tr>';

        $contadorFilas++;
        if ($contadorFilas % 35 === 0) {
            $html .= '</table>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->AddPage();
            $html = '<table border="1" cellpadding="2" cellspacing="0" width="100%">';
            $html .= '<tr style="background-color: #8B1A1A; color: #FFFFFF; font-weight: bold; font-size: 7pt;">';
            for ($i = 0; $i < count($encabezados); $i++) {
                $html .= '<th width="' . $anchoColumnas[$i] . '" align="center">' . $encabezados[$i] . '</th>';
            }
            $html .= '</tr>';
        }
    }

    $html .= '</table>';
    $html .= '<br/><p style="font-size: 7pt; color: #6B7280;">Total registros: ' . count($datos) . '</p>';
    $pdf->writeHTML($html, true, false, true, false, '');

    $nombreArchivo = "Reporte_Detallado_REM_{$timestamp}.pdf";
    $pdf->Output($nombreArchivo, 'D');
    exit;
}

function exportarCSV($datos, $filtros, $timestamp)
{
    $nombreArchivo = "Observaciones_REM_{$timestamp}.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
    header('Cache-Control: max-age=0');

    $salida = fopen('php://output', 'w');
    fprintf($salida, chr(0xEF) . chr(0xBB) . chr(0xBF));

    $encabezados = ['ID', 'Año', 'Mes', 'Comuna', 'Establecimiento', 'Código Serie', 'Código Hoja', 'Tipo Error', 'Detalle', 'Plazo Entrega', 'Estado Actual', 'Clasificación', 'Usuario Registro', 'Fecha Creación', 'Fecha Actualización'];
    fputcsv($salida, $encabezados, ';');

    foreach ($datos as $registro) {
        $valores = [
            $registro['id'],
            $registro['anio'],
            $registro['mes'],
            $registro['comuna_nombre'] ?? '',
            $registro['nombre_corto'] ?? $registro['establecimiento_nombre'] ?? '',
            $registro['codigo_serie'] ?? '',
            $registro['codigo_hoja'] ?? '',
            $registro['tipo_error'],
            $registro['detalle_observacion'] ?? '',
            $registro['plazo_entrega'] ?? '',
            $registro['estado_actual'],
            $registro['clasificacion'] ?? '',
            $registro['usuario_registro_nombre'] ?? '',
            $registro['fecha_creacion'] ?? '',
            $registro['fecha_actualizacion'] ?? ''
        ];
        fputcsv($salida, $valores, ';');
    }

    fclose($salida);
    exit;
}

function generarDescripcionFiltros($filtros)
{
    $partes = [];
    $partes[] = 'Año: ' . ($filtros['anio'] ?? 'Todos');
    if (!empty($filtros['mes'])) $partes[] = 'Mes: ' . $filtros['mes'];
    if (!empty($filtros['estado'])) $partes[] = 'Estado: ' . $filtros['estado'];
    if (!empty($filtros['establecimiento_id'])) $partes[] = 'Establecimiento ID: ' . $filtros['establecimiento_id'];
    if (!empty($filtros['tipo_error'])) $partes[] = 'Tipo: ' . $filtros['tipo_error'];
    return implode(' | ', $partes);
}

function obtenerColorFondoEstado($estado)
{
    switch ($estado) {
        case 'aprobado':
            return '#E8F5E9';
        case 'pendiente':
            return '#FFF3E0';
        case 'rechazado':
            return '#FFEBEE';
        case 'error':
            return '#FCE4EC';
        default:
            return '#FFFFFF';
    }
}
