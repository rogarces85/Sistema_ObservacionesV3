<?php
/**
 * API de Informe de Errores REM
 * Genera informe trimestral/anual de errores (tipo_error = 'ERROR')
 * Solo accesible para rol Supervisor
 * Soporta formato JSON (web) y PDF (descarga)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../models/Observacion.php';

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

$rol = $_SESSION['rol'];
$usuarioId = $_SESSION['usuario_id'];

if ($rol !== ROL_SUPERVISOR) {
    responder(false, null, 'Solo los supervisores pueden generar informes de errores', 403);
}

$metodo = $_SERVER['REQUEST_METHOD'];

if ($metodo === 'GET') {
    $tipo = $_GET['tipo'] ?? '';
    $trimestre = isset($_GET['trimestre']) ? (int)$_GET['trimestre'] : null;
    $anio = (int)($_GET['anio'] ?? $_SESSION['anio_trabajo'] ?? date('Y'));
    $formato = $_GET['formato'] ?? 'json';

    if (!in_array($tipo, ['trimestral', 'anual'])) {
        responder(false, null, 'El parámetro tipo debe ser "trimestral" o "anual"', 400);
    }

    if ($tipo === 'trimestral' && ($trimestre < 1 || $trimestre > 4)) {
        responder(false, null, 'El trimestre debe estar entre 1 y 4', 400);
    }

    try {
        $modeloObservacion = new Observacion();
        $datos = $modeloObservacion->obtenerErroresInforme($anio, $tipo === 'trimestral' ? $trimestre : null, $usuarioId, $rol);

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

        if ($formato === 'pdf') {
            generarPDFInforme($datos, "{$periodo} {$anio}", $anio, $tipo, $trimestre);
        } else {
            $totalErrores = count($datos);
            $porComuna = [];
            $porEstablecimiento = [];
            foreach ($datos as $fila) {
                $comuna = $fila['comuna_nombre'] ?? 'Sin comuna';
                $est = $fila['nombre_corto'] ?? $fila['establecimiento_nombre'] ?? 'Sin establecimiento';
                $porComuna[$comuna] = ($porComuna[$comuna] ?? 0) + 1;
                $porEstablecimiento[$est] = ($porEstablecimiento[$est] ?? 0) + 1;
            }

            responder(true, [
                'datos' => $datos,
                'total' => $totalErrores,
                'periodo' => "{$periodo} {$anio}",
                'tipo' => $tipo,
                'trimestre' => $trimestre,
                'anio' => $anio,
                'por_comuna' => $porComuna,
                'por_establecimiento' => $porEstablecimiento,
                'emitido' => date('d/m/Y H:i')
            ]);
        }
    } catch (Exception $e) {
        error_log("Error en informe_errores: " . $e->getMessage());
        responder(false, null, 'Error al generar el informe: ' . $e->getMessage(), 500);
    }
}

if ($metodo === 'POST') {
    CSRF::validateRequest();

    $entrada = file_get_contents('php://input');
    $cuerpo = json_decode($entrada, true);

    $tipo = $cuerpo['tipo'] ?? '';
    $trimestre = isset($cuerpo['trimestre']) ? (int)$cuerpo['trimestre'] : null;
    $anio = (int)($cuerpo['anio'] ?? $_SESSION['anio_trabajo'] ?? date('Y'));

    if (!in_array($tipo, ['trimestral', 'anual'])) {
        responder(false, null, 'El parámetro tipo debe ser "trimestral" o "anual"', 400);
    }

    if ($tipo === 'trimestral' && ($trimestre < 1 || $trimestre > 4)) {
        responder(false, null, 'El trimestre debe estar entre 1 y 4', 400);
    }

    try {
        $modeloObservacion = new Observacion();
        $datos = $modeloObservacion->obtenerErroresInforme($anio, $tipo === 'trimestral' ? $trimestre : null, $usuarioId, $rol);

        $totalErrores = count($datos);
        require_once __DIR__ . '/../models/ReportQueue.php';
        $cola = new ReportQueue();

        $parametros = ['anio' => $anio, 'tipo' => $tipo, 'trimestre' => $trimestre];
        $idCola = $cola->enqueue($usuarioId, 'informe_errores', 'pdf', $parametros);

        responder(true, [
            'en_cola' => true,
            'id_reporte' => $idCola,
            'total_registros' => $totalErrores,
            'mensaje' => "Informe de errores con {$totalErrores} registros encolado para generación PDF."
        ]);
    } catch (Exception $e) {
        error_log("Error al encolar informe: " . $e->getMessage());
        responder(false, null, 'Error al generar el informe: ' . $e->getMessage(), 500);
    }
}

function generarPDFInforme($datos, $periodo, $anio, $tipo, $trimestre)
{
    require_once __DIR__ . '/../vendor/autoload.php';

    $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');
    $pdf->SetCreator('Sistema Observaciones REM');
    $pdf->SetAuthor('Servicio de Salud Osorno');
    $pdf->SetTitle("Informe de Errores REM - {$periodo}");
    $pdf->SetMargins(18, 40, 18);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(15);
    $pdf->SetAutoPageBreak(true, 22);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->setFooterFont(['helvetica', '', 8]);
    $pdf->AddPage();

    $rutaLogo = __DIR__ . '/../assets/images/logo.png';
    $xLogo = ($pdf->getPageWidth() - 30) / 2;
    if (file_exists($rutaLogo)) {
        $pdf->Image($rutaLogo, $xLogo, 12, 30, 0, 'PNG', '', '', false, 300, '', false, false, 0);
    }

    $pdf->SetY(32);
    $pdf->SetFont('helvetica', 'B', 13);
    $pdf->Cell(0, 7, 'SERVICIO SALUD OSORNO', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 5, 'DEGI - Departamento de Estadística', 0, 1, 'C');
    $pdf->SetDrawColor(0, 82, 136);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(50, $pdf->GetY() + 2, $pdf->getPageWidth() - 50, $pdf->GetY() + 2);
    $pdf->Ln(6);

    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 8, 'INFORME DE ERRORES REM', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(0, 6, $periodo, 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(0, 4, 'Emitido: ' . date('d/m/Y H:i'), 0, 1, 'C');
    $pdf->Ln(3);

    if (empty($datos)) {
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 10, 'No se encontraron errores en el período seleccionado.', 0, 1, 'C');
        $nombreArchivo = "Informe_Errores_{$periodo}_{$anio}.pdf";
        $pdf->Output($nombreArchivo, 'D');
        exit;
    }

    $anchoColumnas = [24, 32, 13, 56, 23, 26];
    $encabezados = ['COMUNA', 'ESTABLECIMIENTO', 'MES', 'DETALLE DEL ERROR', 'CLASIFICACIÓN', 'DETALLE ERROR'];

    $datosAgrupados = [];
    $comunaActual = '';
    $establecimientoActual = '';
    $inicioComuna = 0;
    $inicioEst = 0;

    foreach ($datos as $indice => $fila) {
        $comuna = strtoupper($fila['comuna_nombre'] ?? '');
        $establecimiento = $fila['nombre_corto'] ?? $fila['establecimiento_nombre'] ?? '';

        if ($comuna !== $comunaActual) {
            if ($comunaActual !== '') {
                $datosAgrupados[$inicioComuna]['comuna_span'] = $indice - $inicioComuna;
            }
            $inicioComuna = $indice;
            $comunaActual = $comuna;
            $establecimientoActual = '';
        }

        if ($establecimiento !== $establecimientoActual) {
            if ($establecimientoActual !== '') {
                $datosAgrupados[$inicioEst]['est_span'] = $indice - $inicioEst;
            }
            $inicioEst = $indice;
            $establecimientoActual = $establecimiento;
        }

        $serie = htmlspecialchars($fila['codigo_serie'] ?? '');
        $hoja = htmlspecialchars($fila['codigo_hoja'] ?? '');
        $detalleObs = htmlspecialchars($fila['detalle_observacion'] ?? '');
        $detalleHtml = "<span style=\"font-weight: bold; color: #005288;\">{$serie}</span> | <span style=\"font-weight: bold; color: #005288;\">{$hoja}</span><br/><span style=\"color: #374151;\">{$detalleObs}</span>";

        $datosAgrupados[$indice] = [
            'comuna' => $comuna,
            'establecimiento' => $establecimiento,
            'mes' => $fila['mes'] ?? '',
            'detalle_html' => $detalleHtml,
            'clasificacion' => $fila['clasificacion'] ?? '',
            'detalle_error' => $fila['detalle_error'] ?? '',
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
    }

    $colorPrimario = '#005288';
    $html = '<table border="0" cellpadding="2.5" cellspacing="0" width="100%" style="border-collapse: collapse;">';
    $html .= '<tr>';
    for ($i = 0; $i < count($encabezados); $i++) {
        $html .= '<th width="' . $anchoColumnas[$i] . '" align="center" style="background-color: ' . $colorPrimario . '; color: #FFFFFF; font-weight: bold; font-size: 6.5pt; padding: 4px 2px; border: 0.5px solid #003D66;">' . $encabezados[$i] . '</th>';
    }
    $html .= '</tr>';

    $contadorFilas = 0;
    foreach ($datosAgrupados as $indice => $item) {
        $comuna = $item['comuna'];
        $establecimiento = $item['establecimiento'];
        $mes = $item['mes'];
        $detalleHtml = $item['detalle_html'];
        $clasificacion = htmlspecialchars($item['clasificacion']);
        $detalleError = htmlspecialchars($item['detalle_error']);
        $estado = $item['estado_actual'];

        $comunaSpan = $item['comuna_span'] ?? 1;
        $estSpan = $item['est_span'] ?? 1;

        $colorFondo = '#FFFFFF';
        if ($estado === 'aprobado') {
            $colorFondo = '#F0FDF4';
        } elseif ($estado === 'pendiente') {
            $colorFondo = '#FFFBEB';
        } elseif ($estado === 'rechazado') {
            $colorFondo = '#FEF2F2';
        } elseif ($estado === 'error') {
            $colorFondo = '#FCE4EC';
        }

        if ($contadorFilas % 2 === 0 && $colorFondo === '#FFFFFF') {
            $colorFondo = '#F8FAFC';
        }

        $html .= '<tr style="background-color: ' . $colorFondo . '; font-size: 6.5pt;">';

        if ($comunaSpan > 0) {
            $html .= '<td width="' . $anchoColumnas[0] . '" rowspan="' . $comunaSpan . '" align="center" style="font-weight: bold; color: ' . $colorPrimario . '; vertical-align: middle; padding: 3px 2px; border: 0.3px solid #D1D5DB;">' . htmlspecialchars($comuna) . '</td>';
            $datosAgrupados[$indice]['comuna_span'] = 0;
        }
        if ($estSpan > 0) {
            $html .= '<td width="' . $anchoColumnas[1] . '" rowspan="' . $estSpan . '" style="vertical-align: middle; padding: 3px 2px; border: 0.3px solid #D1D5DB;">' . htmlspecialchars($establecimiento) . '</td>';
            $datosAgrupados[$indice]['est_span'] = 0;
        }

        $html .= '<td width="' . $anchoColumnas[2] . '" align="center" style="padding: 3px 2px; border: 0.3px solid #D1D5DB;">' . htmlspecialchars($mes) . '</td>';
        $html .= '<td width="' . $anchoColumnas[3] . '" style="padding: 3px 3px; border: 0.3px solid #D1D5DB;">' . $detalleHtml . '</td>';
        $html .= '<td width="' . $anchoColumnas[4] . '" align="center" style="padding: 3px 2px; border: 0.3px solid #D1D5DB;">' . $clasificacion . '</td>';
        $html .= '<td width="' . $anchoColumnas[5] . '" style="padding: 3px 3px; border: 0.3px solid #D1D5DB;">' . $detalleError . '</td>';
        $html .= '</tr>';

        $contadorFilas++;
        if ($contadorFilas % 22 === 0) {
            $html .= '</table>';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->AddPage();
            $html = '<table border="0" cellpadding="2.5" cellspacing="0" width="100%" style="border-collapse: collapse;">';
            $html .= '<tr>';
            for ($i = 0; $i < count($encabezados); $i++) {
                $html .= '<th width="' . $anchoColumnas[$i] . '" align="center" style="background-color: ' . $colorPrimario . '; color: #FFFFFF; font-weight: bold; font-size: 6.5pt; padding: 4px 2px; border: 0.5px solid #003D66;">' . $encabezados[$i] . '</th>';
            }
            $html .= '</tr>';
        }
    }

    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Ln(10);
    $pdf->SetFont('helvetica', '', 9);
    $htmlFirmas = '<table width="100%" style="margin-top: 15px;">';
    $htmlFirmas .= '<tr><td align="center" style="padding-top: 25px;">';
    $htmlFirmas .= '<hr style="width: 250px; border: none; border-top: 0.5px solid #9CA3AF; margin-bottom: 5px;"/>';
    $htmlFirmas .= '<span style="font-weight: bold; color: ' . $colorPrimario . ';">Cecilia Barría Ojeda</span><br/>';
    $htmlFirmas .= '<span style="color: #6B7280; font-size: 8pt;">Jefa Subdepto. Producción Estadística</span>';
    $htmlFirmas .= '</td></tr>';
    $htmlFirmas .= '</table>';
    $pdf->writeHTML($htmlFirmas, true, false, true, false, '');

    $nombreArchivo = "Informe_Errores_{$periodo}_{$anio}.pdf";
    $pdf->Output($nombreArchivo, 'D');
    exit;
}
