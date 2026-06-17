<?php
/**
 * Clase Exporter
 * Manejo de exportación de datos a diferentes formatos
 */

require_once __DIR__ . '/../config/config.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class Exporter
{
    /**
     * Exportar datos a Excel (.xlsx)
     */
    public function exportToExcel($data, $filename, $headers = [])
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Título
        $sheet->setCellValue('A1', 'Sistema de Observaciones REM');
        $sheet->mergeCells('A1:' . chr(64 + count($headers)) . '1');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Fecha de generación
        $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:' . chr(64 + count($headers)) . '2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Headers
        $col = 'A';
        $row = 4;
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4B5563');
            $sheet->getStyle($col . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col++;
        }

        // Datos
        $row = 5;
        foreach ($data as $record) {
            $col = 'A';
            foreach ($record as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        // Estilos de tabla
        $lastCol = chr(64 + count($headers));
        $lastRow = $row - 1;
        $sheet->getStyle('A4:' . $lastCol . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Auto-width
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generar archivo
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Exportar datos a PDF
     */
    public function exportToPDF($data, $filename, $headers = [], $title = 'Reporte de Observaciones REM')
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        // Crear PDF
        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');

        // Configuración del documento
        $pdf->SetCreator('Sistema Observaciones REM');
        $pdf->SetAuthor('Servicio de Salud Osorno');
        $pdf->SetTitle($title);

        // Configuración de la página
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(true, 15);

        // Fuente
        $pdf->SetFont('helvetica', '', 8);

        // Agregar página
        $pdf->AddPage();

        // Título
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, $title, 0, 1, 'C');

        // Fecha
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 5, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'C');
        $pdf->Ln(5);

        $columnCount = max(1, count($headers));
        $usableWidth = 277;
        $widths = $columnCount === 4 ? [132, 70, 35, 40] : array_fill(0, $columnCount, $usableWidth / $columnCount);
        $this->writeFixedPdfHeader($pdf, $headers, $widths);

        foreach ($data as $index => $record) {
            if ($pdf->GetY() > 190) {
                $pdf->AddPage();
                $this->writeFixedPdfHeader($pdf, $headers, $widths);
            }
            $this->writeFixedPdfRow($pdf, array_values($record), $widths, $index % 2 === 0 ? [250, 250, 250] : [255, 255, 255]);
        }

        // Salida
        $pdf->Output($filename, 'D');
        exit;
    }

    private function writeFixedPdfHeader($pdf, array $headers, array $widths)
    {
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetFillColor(0, 82, 136);
        $pdf->SetTextColor(255, 255, 255);
        foreach ($headers as $index => $header) {
            $pdf->Cell($widths[$index], 7, $this->truncatePdfText($header, $widths[$index]), 1, 0, 'C', true);
        }
        $pdf->Ln();
        $pdf->SetTextColor(30, 41, 59);
        $pdf->SetFont('helvetica', '', 7);
    }

    private function writeFixedPdfRow($pdf, array $record, array $widths, array $fill)
    {
        $pdf->SetFillColor($fill[0], $fill[1], $fill[2]);
        foreach ($widths as $index => $width) {
            $value = $record[$index] ?? '';
            $align = $index >= count($widths) - 2 ? 'R' : 'L';
            $pdf->Cell($width, 7, $this->truncatePdfText($value, $width), 1, 0, $align, true);
        }
        $pdf->Ln();
    }

    private function truncatePdfText($text, $width)
    {
        $text = trim(preg_replace('/\s+/', ' ', (string)$text));
        $max = max(8, (int)floor($width * 0.95));
        $length = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        if ($length <= $max) {
            return $text;
        }
        $truncated = function_exists('mb_substr') ? mb_substr($text, 0, max(1, $max - 1), 'UTF-8') : substr($text, 0, max(1, $max - 1));
        return $truncated . '…';
    }

    /**
     * Exportar datos a CSV
     */
    public function exportToCSV($data, $filename, $headers = [])
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');

        // BOM para Excel UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Headers
        if (!empty($headers)) {
            fputcsv($output, $headers, ';');
        }

        // Datos
        foreach ($data as $record) {
            fputcsv($output, $record, ';');
        }

        fclose($output);
        exit;
    }

    /**
     * Preparar datos de observaciones para exportación
     */
    public function prepareObservationsData($observations)
    {
        $data = [];

        foreach ($observations as $obs) {
            $data[] = [
                $obs['id'],
                $obs['anio'],
                $obs['mes'],
                $obs['establecimiento'] ?? $obs['nombre_corto'],
                $obs['comuna'] ?? '',
                $obs['codigo_serie'],
                $obs['codigo_hoja'],
                $obs['tipo_error'],
                $obs['detalle_observacion'],
                $obs['plazo_entrega'],
                $obs['usa_validador'],
                $obs['estado_actual'],
                $obs['clasificacion'] ?? '',
                $obs['nombre_registro'] ?? '',
                $obs['nombre_supervisor'] ?? '',
                date('d/m/Y H:i', strtotime($obs['fecha_registro']))
            ];
        }

        return $data;
    }

    /**
     * Obtener headers para observaciones
     */
    public function getObservationsHeaders()
    {
        return [
            'ID',
            'Año',
            'Mes',
            'Establecimiento',
            'Comuna',
            'Código Serie',
            'Código Hoja',
            'Tipo Error',
            'Detalle',
            'Plazo Entrega',
            'Usa Validador',
            'Estado',
            'Clasificación',
            'Registrador',
            'Supervisor',
            'Fecha Registro'
        ];
    }

    /**
     * Exportar reporte detallado jerárquico a PDF (comuna → establecimiento → mes)
     */
    public function exportDetalladoPDF($data, $filename, $filters = [])
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');

        $pdf->SetCreator('Sistema Observaciones REM');
        $pdf->SetAuthor('Servicio de Salud Osorno');
        $pdf->SetTitle('Reporte Detallado de Validaciones');

        $pdf->SetMargins(8, 12, 8);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(true, 12);

        $pdf->SetFont('helvetica', '', 7);

        $pdf->AddPage();

        // Título
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 8, 'Reporte Detallado de Validaciones REM', 0, 1, 'C');

        // Filtros aplicados
        $pdf->SetFont('helvetica', '', 8);
        $filterText = 'Año: ' . ($filters['anio'] ?? 'Todos');
        if (!empty($filters['comuna'])) $filterText .= ' | Comuna: ' . $filters['comuna'];
        if (!empty($filters['establecimiento'])) $filterText .= ' | Establecimiento: ' . $filters['establecimiento'];
        if (!empty($filters['mes'])) $filterText .= ' | Mes: ' . $filters['mes'];
        if (!empty($filters['estado'])) $filterText .= ' | Estado: ' . $filters['estado'];
        $pdf->Cell(0, 5, $filterText, 0, 1, 'C');
        $pdf->Cell(0, 5, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'C');
        $pdf->Ln(2);

        // 6 columnas exactas como la imagen: COMUNAS | ESTABLECIMIENTOS | MES | DETALLE | DETALLE ERROR | ERRORES
        $colWidths = [28, 42, 18, 100, 35, 12];
        $headers = ['COMUNAS', 'ESTABLECIMIENTOS', 'MES', 'DETALLE', 'DETALLE ERROR', 'ERRORES'];

        // Agrupamiento jerárquico - calcular row spans
        $groupedData = [];
        $currentComuna = '';
        $currentEstablecimiento = '';
        $currentMes = '';
        $comunaStart = 0;
        $estStart = 0;
        $mesStart = 0;

        foreach ($data as $idx => $row) {
            $comuna = strtoupper($row['comuna'] ?? '');
            $establecimiento = $row['establecimiento'] ?? '';
            $mes = strtolower($row['mes'] ?? '');

            if ($comuna !== $currentComuna) {
                if ($currentComuna !== '') {
                    $groupedData[$comunaStart]['comuna_span'] = $idx - $comunaStart;
                }
                $comunaStart = $idx;
                $currentComuna = $comuna;
                $currentEstablecimiento = '';
                $currentMes = '';
            }

            if ($establecimiento !== $currentEstablecimiento) {
                if ($currentEstablecimiento !== '') {
                    $groupedData[$estStart]['est_span'] = $idx - $estStart;
                }
                $estStart = $idx;
                $currentEstablecimiento = $establecimiento;
                $currentMes = '';
            }

            if ($mes !== $currentMes) {
                if ($currentMes !== '') {
                    $groupedData[$mesStart]['mes_span'] = $idx - $mesStart;
                }
                $mesStart = $idx;
                $currentMes = $mes;
            }

            $groupedData[$idx] = [
                'comuna' => $comuna,
                'establecimiento' => $establecimiento,
                'mes' => $mes,
                'detalle_observacion' => $row['detalle_observacion'] ?? '',
                'clasificacion' => $row['clasificacion'] ?? ($row['estado_actual'] ?? ''),
                'estado_actual' => $row['estado_actual'] ?? 'pendiente'
            ];
        }
        // Cerrar últimos spans
        if (!empty($groupedData)) {
            $lastIdx = count($data) - 1;
            if (!isset($groupedData[$comunaStart]['comuna_span'])) $groupedData[$comunaStart]['comuna_span'] = $lastIdx - $comunaStart + 1;
            if (!isset($groupedData[$estStart]['est_span'])) $groupedData[$estStart]['est_span'] = $lastIdx - $estStart + 1;
            if (!isset($groupedData[$mesStart]['mes_span'])) $groupedData[$mesStart]['mes_span'] = $lastIdx - $mesStart + 1;
        }

        // Header de tabla - fondo rojo oscuro como la imagen
        $html = '<table border="1" cellpadding="2" cellspacing="0" width="100%">';
        $html .= '<tr style="background-color: #8B1A1A; color: #FFFFFF; font-weight: bold; font-size: 7pt;">';
        for ($i = 0; $i < count($headers); $i++) {
            $html .= '<th width="' . $colWidths[$i] . '" align="center" style="padding: 3px 2px;">' . $headers[$i] . '</th>';
        }
        $html .= '</tr>';

        // Filas de datos con rowspan para agrupamiento
        $rowCount = 0;
        foreach ($groupedData as $idx => $item) {
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

            // Color de fondo según estado
            $bgColor = '#FFFFFF';
            if ($estado === 'aprobado' || $clasificacion === 'Corregido') {
                $bgColor = '#E8F5E9';
            } elseif ($estado === 'pendiente' || strpos($clasificacion, 'Sin respuesta') !== false) {
                $bgColor = '#FFF3E0';
            } elseif ($estado === 'rechazado') {
                $bgColor = '#FFEBEE';
            } elseif ($estado === 'justificado') {
                $bgColor = '#E3F2FD';
            }

            // Alternar filas para legibilidad
            if ($rowCount % 2 === 0 && $bgColor === '#FFFFFF') {
                $bgColor = '#FAFAFA';
            }

            $html .= '<tr style="background-color: ' . $bgColor . '; font-size: 6.5pt;">';

            // COMUNAS con rowspan
            if ($comunaSpan > 0) {
                $html .= '<td width="' . $colWidths[0] . '" rowspan="' . $comunaSpan . '" align="center" style="font-weight: bold; vertical-align: middle; padding: 2px;">' . htmlspecialchars($comuna) . '</td>';
                $item['comuna_span'] = 0;
            }

            // ESTABLECIMIENTOS con rowspan
            if ($estSpan > 0) {
                $html .= '<td width="' . $colWidths[1] . '" rowspan="' . $estSpan . '" style="vertical-align: middle; padding: 2px;">' . htmlspecialchars($establecimiento) . '</td>';
                $item['est_span'] = 0;
            }

            // MES con rowspan
            if ($mesSpan > 0) {
                $html .= '<td width="' . $colWidths[2] . '" rowspan="' . $mesSpan . '" align="center" style="vertical-align: middle; padding: 2px;">' . htmlspecialchars($mes) . '</td>';
                $item['mes_span'] = 0;
            }

            // DETALLE (texto completo de la observación)
            $html .= '<td width="' . $colWidths[3] . '" style="padding: 2px 4px;">' . htmlspecialchars($detalle) . '</td>';

            // DETALLE ERROR (clasificación / estado de respuesta)
            $html .= '<td width="' . $colWidths[4] . '" align="center" style="padding: 2px;">' . htmlspecialchars($clasificacion) . '</td>';

            // ERRORES (cantidad = 1 por fila)
            $html .= '<td width="' . $colWidths[5] . '" align="center" style="font-weight: bold; padding: 2px;">1</td>';

            $html .= '</tr>';
            $rowCount++;

            // Nueva página cada ~35 filas
            if ($rowCount % 35 === 0) {
                $html .= '</table>';
                $pdf->writeHTML($html, true, false, true, false, '');
                $pdf->AddPage();
                $html = '<table border="1" cellpadding="2" cellspacing="0" width="100%">';
                $html .= '<tr style="background-color: #8B1A1A; color: #FFFFFF; font-weight: bold; font-size: 7pt;">';
                for ($i = 0; $i < count($headers); $i++) {
                    $html .= '<th width="' . $colWidths[$i] . '" align="center" style="padding: 3px 2px;">' . $headers[$i] . '</th>';
                }
                $html .= '</tr>';
            }
        }

        $html .= '</table>';

        // Resumen final
        $html .= '<br/><table width="100%">';
        $html .= '<tr><td style="font-size: 7pt; color: #6B7280;">Total registros: ' . count($data) . '</td></tr>';
        $html .= '</table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output($filename, 'D');
        exit;
    }

    /**
     * Exportar reporte de errores a Excel
     */
    public function exportErroresExcel($data, $filename, $reportType = 'general')
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $titles = [
            'errores_mes' => 'Reporte de Errores por Mes',
            'errores_establecimiento' => 'Reporte de Errores por Establecimiento',
            'errores_comuna' => 'Reporte de Errores por Comuna',
            'fuera_plazo_mes' => 'Reporte Fuera de Plazo por Mes',
            'fuera_plazo_establecimiento' => 'Reporte Fuera de Plazo por Establecimiento',
            'fuera_plazo_comuna' => 'Reporte Fuera de Plazo por Comuna',
            'validador_mes' => 'Reporte Uso Validador por Mes',
            'validador_establecimiento' => 'Reporte Uso Validador por Establecimiento',
            'validador_comuna' => 'Reporte Uso Validador por Comuna',
            'serie_detalle' => 'Reporte por Serie REM',
            'hoja_detalle' => 'Reporte por Hoja REM'
        ];

        $title = $titles[$reportType] ?? 'Reporte de Observaciones REM';

        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:C1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A2:C2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Determinar headers según tipo
        $headers = ['Dimensión', 'Sub-dimensión', 'Cantidad'];
        if (isset($data[0])) {
            $headers = array_keys($data[0]);
        }

        $col = 'A';
        $row = 4;
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $sheet->getStyle($col . $row)->getFont()->setBold(true);
            $sheet->getStyle($col . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4B5563');
            $sheet->getStyle($col . $row)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col++;
        }

        $row = 5;
        foreach ($data as $record) {
            $col = 'A';
            foreach ($record as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        $lastCol = chr(64 + count($headers));
        $lastRow = $row - 1;
        $sheet->getStyle('A4:' . $lastCol . $lastRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        foreach (range('A', $lastCol) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function exportAnalitico(array $reporte, string $filename, string $formato = 'excel')
    {
        $headers = ['Dimensión', 'Comuna', 'Total', 'Porcentaje'];
        $data = [];

        foreach ($reporte['resultados'] ?? [] as $fila) {
            $data[] = [
                $fila['nombre'] ?? $fila['clave'] ?? 'Sin nombre',
                $fila['comuna'] ?? '',
                (int)($fila['total'] ?? 0),
                number_format((float)($fila['porcentaje'] ?? 0), 1, ',', '.') . '%'
            ];
        }

        if ($formato === 'csv') {
            $this->exportToCSV($data, $filename, $headers);
        }

        if ($formato === 'pdf') {
            $this->exportToPDF($data, $filename, $headers, $reporte['titulo'] ?? 'Reporte Analítico REM');
        }

        $this->exportToExcel($data, $filename, $headers);
    }

    /**
     * Exportar Informe de Errores REM a PDF
     * Formato vertical (portrait), diseño moderno para presentación a directivos
     */
    public function exportInformeErroresPDF($data, $periodo, $filename)
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8');

        $pdf->SetCreator('Sistema Observaciones REM');
        $pdf->SetAuthor('Servicio de Salud Osorno');
        $pdf->SetTitle("Informe de Errores REM - $periodo");

        $pdf->SetMargins(18, 40, 18);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(15);
        $pdf->SetAutoPageBreak(true, 22);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterFont(['helvetica', '', 8]);

        $pdf->AddPage();

        // ---- ENCABEZADO ----
        $logoPath = __DIR__ . '/../assets/images/logo.png';
        $xLogo = ($pdf->getPageWidth() - 30) / 2;
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, $xLogo, 12, 30, 0, 'PNG', '', '', false, 300, '', false, false, 0);
        }

        $pdf->SetY(32);
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->Cell(0, 7, 'SERVICIO SALUD OSORNO', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'DEGI - Departamento de Estadística', 0, 1, 'C');

        // Línea decorativa
        $pdf->SetDrawColor(0, 82, 136);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(50, $pdf->GetY() + 2, $pdf->getPageWidth() - 50, $pdf->GetY() + 2);

        $pdf->Ln(6);

        // ---- TÍTULO ----
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 8, 'INFORME DE ERRORES REM', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->Cell(0, 6, $periodo, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 4, 'Emitido: ' . date('d/m/Y H:i'), 0, 1, 'C');
        $pdf->Ln(3);

        if (empty($data)) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 10, 'No se encontraron errores en el período seleccionado.', 0, 1, 'C');
            $pdf->Output($filename, 'D');
            exit;
        }

        // ---- TABLA ----
        // Columnas fijas en portrait (ancho útil: 210 - 36 = 174mm)
        $colWidths = [24, 32, 13, 56, 23, 26];
        $headers = ['COMUNA', 'ESTABLECIMIENTO', 'MES', 'DETALLE DEL ERROR', 'CLASIFICACIÓN', 'DETALLE ERROR'];

        // Agrupamiento jerárquico
        $groupedData = [];
        $currentComuna = '';
        $currentEstablecimiento = '';
        $comunaStart = 0;
        $estStart = 0;

        foreach ($data as $idx => $row) {
            $comuna = strtoupper($row['comuna'] ?? '');
            $establecimiento = $row['establecimiento'] ?? '';
            $mes = $row['mes'] ?? '';

            if ($comuna !== $currentComuna) {
                if ($currentComuna !== '') {
                    $groupedData[$comunaStart]['comuna_span'] = $idx - $comunaStart;
                }
                $comunaStart = $idx;
                $currentComuna = $comuna;
                $currentEstablecimiento = '';
            }

            if ($establecimiento !== $currentEstablecimiento) {
                if ($currentEstablecimiento !== '') {
                    $groupedData[$estStart]['est_span'] = $idx - $estStart;
                }
                $estStart = $idx;
                $currentEstablecimiento = $establecimiento;
            }

            $serie = htmlspecialchars($row['codigo_serie'] ?? '');
            $hoja = htmlspecialchars($row['codigo_hoja'] ?? '');
            $detalleObs = htmlspecialchars($row['detalle_observacion'] ?? '');

            $detalleHtml = "<span style=\"font-weight: bold; color: #005288;\">{$serie}</span> | <span style=\"font-weight: bold; color: #005288;\">{$hoja}</span><br/><span style=\"color: #374151;\">{$detalleObs}</span>";

            $groupedData[$idx] = [
                'comuna' => $comuna,
                'establecimiento' => $establecimiento,
                'mes' => $mes,
                'detalle_html' => $detalleHtml,
                'clasificacion' => $row['clasificacion'] ?? '',
                'detalle_error' => $row['detalle_error'] ?? '',
                'estado_actual' => $row['estado_actual'] ?? 'pendiente'
            ];
        }
        if (!empty($groupedData)) {
            $lastIdx = count($data) - 1;
            if (!isset($groupedData[$comunaStart]['comuna_span'])) {
                $groupedData[$comunaStart]['comuna_span'] = $lastIdx - $comunaStart + 1;
            }
            if (!isset($groupedData[$estStart]['est_span'])) {
                $groupedData[$estStart]['est_span'] = $lastIdx - $estStart + 1;
            }
        }

        // Paleta de colores institucional
        $colorPrimary = '#005288';
        $colorPrimaryLight = '#E8F0F8';
        $colorAccent = '#00A3B5';

        $html = '<table border="0" cellpadding="2.5" cellspacing="0" width="100%" style="border-collapse: collapse;">';

        // Header con bordes sólidos
        $html .= '<tr>';
        for ($i = 0; $i < count($headers); $i++) {
            $html .= '<th width="' . $colWidths[$i] . '" align="center" style="background-color: ' . $colorPrimary . '; color: #FFFFFF; font-weight: bold; font-size: 6.5pt; padding: 4px 2px; border: 0.5px solid #003D66;">' . $headers[$i] . '</th>';
        }
        $html .= '</tr>';

        $rowCount = 0;
        foreach ($groupedData as $idx => $item) {
            $comuna = $item['comuna'];
            $establecimiento = $item['establecimiento'];
            $mes = $item['mes'];
            $detalleHtml = $item['detalle_html'];
            $clasificacion = htmlspecialchars($item['clasificacion']);
            $detalleError = htmlspecialchars($item['detalle_error']);
            $estado = $item['estado_actual'];

            $comunaSpan = $item['comuna_span'] ?? 1;
            $estSpan = $item['est_span'] ?? 1;

            // Color según estado (más sutiles, tonos pastel)
            $bgColor = '#FFFFFF';
            if ($estado === 'aprobado') {
                $bgColor = '#F0FDF4';
            } elseif ($estado === 'pendiente') {
                $bgColor = '#FFFBEB';
            } elseif ($estado === 'rechazado') {
                $bgColor = '#FEF2F2';
            } elseif ($estado === 'justificado') {
                $bgColor = '#EFF6FF';
            }

            if ($rowCount % 2 === 0 && $bgColor === '#FFFFFF') {
                $bgColor = '#F8FAFC';
            }

            $html .= '<tr style="background-color: ' . $bgColor . '; font-size: 6.5pt;">';

            // COMUNA
            if ($comunaSpan > 0) {
                $html .= '<td width="' . $colWidths[0] . '" rowspan="' . $comunaSpan . '" align="center" style="font-weight: bold; color: ' . $colorPrimary . '; vertical-align: middle; padding: 3px 2px; border: 0.3px solid #D1D5DB;">' . htmlspecialchars($comuna) . '</td>';
                $groupedData[$idx]['comuna_span'] = 0;
            }

            // ESTABLECIMIENTO
            if ($estSpan > 0) {
                $html .= '<td width="' . $colWidths[1] . '" rowspan="' . $estSpan . '" style="vertical-align: middle; padding: 3px 2px; border: 0.3px solid #D1D5DB;">' . htmlspecialchars($establecimiento) . '</td>';
                $groupedData[$idx]['est_span'] = 0;
            }

            // MES
            $html .= '<td width="' . $colWidths[2] . '" align="center" style="padding: 3px 2px; border: 0.3px solid #D1D5DB;">' . htmlspecialchars($mes) . '</td>';

            // DETALLE DEL ERROR
            $html .= '<td width="' . $colWidths[3] . '" style="padding: 3px 3px; border: 0.3px solid #D1D5DB;">' . $detalleHtml . '</td>';

            // CLASIFICACIÓN
            $html .= '<td width="' . $colWidths[4] . '" align="center" style="padding: 3px 2px; border: 0.3px solid #D1D5DB;">' . $clasificacion . '</td>';

            // DETALLE ERROR
            $html .= '<td width="' . $colWidths[5] . '" style="padding: 3px 3px; border: 0.3px solid #D1D5DB;">' . $detalleError . '</td>';

            $html .= '</tr>';
            $rowCount++;

            // Paginación cada ~22 filas (por ser portrait)
            if ($rowCount % 22 === 0) {
                $html .= '</table>';
                $pdf->writeHTML($html, true, false, true, false, '');
                $pdf->AddPage();
                $html = '<table border="0" cellpadding="2.5" cellspacing="0" width="100%" style="border-collapse: collapse;">';
                $html .= '<tr>';
                for ($i = 0; $i < count($headers); $i++) {
                    $html .= '<th width="' . $colWidths[$i] . '" align="center" style="background-color: ' . $colorPrimary . '; color: #FFFFFF; font-weight: bold; font-size: 6.5pt; padding: 4px 2px; border: 0.5px solid #003D66;">' . $headers[$i] . '</th>';
                }
                $html .= '</tr>';
            }
        }

        $html .= '</table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        // ---- FIRMAS ----
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', '', 9);
        $html = '<table width="100%" style="margin-top: 15px;">';
        $html .= '<tr><td align="center" style="padding-top: 25px;">';
        $html .= '<hr style="width: 250px; border: none; border-top: 0.5px solid #9CA3AF; margin-bottom: 5px;"/>';
        $html .= '<span style="font-weight: bold; color: ' . $colorPrimary . ';">Cecilia Barría Ojeda</span><br/>';
        $html .= '<span style="color: #6B7280; font-size: 8pt;">Jefa Subdepto. Producción Estadística</span>';
        $html .= '</td></tr>';
        $html .= '</table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Output($filename, 'D');
        exit;
    }
}
