<?php
/**
 * Clase Exporter
 * Manejo de exportación de datos a diferentes formatos
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class Exporter
{
    // Paleta de colores DEIS Osorno (sincronizada con tokens.css)
    const COLOR_NAVY_ARGB = 'FF003366';      // Excel ARGB format
    const COLOR_NAVY_HEX = '#003366';        // Hex format
    const COLOR_CIAN_ARGB = 'FF00AEEF';
    const COLOR_CIAN_HEX = '#00AEEF';
    const COLOR_SUAVE_ARGB = 'FFEEF7FD';
    const COLOR_SUAVE_HEX = '#EEF7FD';
    const COLOR_BORDE_ARGB = 'FFB6D7EB';
    const COLOR_BORDE_HEX = '#B6D7EB';

    /** Meses en el orden del calendario, para las matrices mensuales. */
    const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                   'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    const MESES_ABREV = ['Enero' => 'ENE', 'Febrero' => 'FEB', 'Marzo' => 'MAR', 'Abril' => 'ABR',
                         'Mayo' => 'MAY', 'Junio' => 'JUN', 'Julio' => 'JUL', 'Agosto' => 'AGO',
                         'Septiembre' => 'SEP', 'Octubre' => 'OCT', 'Noviembre' => 'NOV', 'Diciembre' => 'DIC'];

    /**
     * Carga las librerias de exportacion y avisa de forma clara si faltan.
     * Sin esto, un vendor/ ausente en el servidor produce un fatal error
     * "Class not found" que el navegador muestra como pestana en blanco.
     *
     * @param string $necesita 'pdf' | 'excel'
     * @throws RuntimeException si la libreria no esta disponible
     */
    public static function requerirLibreria(string $necesita): void
    {
        $autoload = __DIR__ . '/../vendor/autoload.php';
        if (!is_file($autoload)) {
            throw new RuntimeException(
                'Faltan las librerias de exportacion (no existe vendor/autoload.php). ' .
                'Ejecute "composer install --no-dev --optimize-autoloader" en el servidor.'
            );
        }
        require_once $autoload;

        if ($necesita === 'pdf' && !class_exists('\TCPDF')) {
            throw new RuntimeException(
                'La libreria de PDF (tecnickcom/tcpdf) no esta instalada. ' .
                'Ejecute "composer install --no-dev" en el servidor.'
            );
        }
        if ($necesita === 'excel' && !class_exists(Spreadsheet::class)) {
            throw new RuntimeException(
                'La libreria de Excel (phpoffice/phpspreadsheet) no esta instalada. ' .
                'Ejecute "composer install --no-dev" en el servidor.'
            );
        }
    }

    /**
     * Deja la salida limpia justo antes de enviar un archivo binario.
     *
     * php.ini trae output_buffering activo: cualquier aviso, espacio o BOM emitido
     * antes queda en el bufer y se pega delante del XLSX/PDF, que entonces "no abre".
     * Tambien se apagan los errores en pantalla por el mismo motivo.
     */
    private function prepararSalidaBinaria(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ini_set('display_errors', '0');

        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }
        if ((int)ini_get('memory_limit') !== -1) {
            @ini_set('memory_limit', '512M');
        }
    }

    /** Cabeceras comunes de descarga/visualizacion de un archivo generado. */
    private function cabecerasArchivo(string $contentType, string $filename, string $disposition = 'attachment'): void
    {
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: ' . $disposition . '; filename="' . basename($filename) . '"');
        header('Cache-Control: max-age=0, must-revalidate');
        header('Pragma: public');
        header('X-Content-Type-Options: nosniff');
    }

    /**
     * Exportar datos a Excel (.xlsx)
     */
    public function exportToExcel($data, $filename, $headers = [], $download = true)
    {
        self::requerirLibreria('excel');

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
        if (!$download) {
            $writer->save($filename);
            return true;
        }
        $this->prepararSalidaBinaria();
        $this->cabecerasArchivo('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $filename);
        $writer->save('php://output');
        exit;
    }

    /**
     * Exportar datos a PDF
     */
    public function exportToPDF($data, $filename, $headers = [], $title = 'Reporte de Observaciones REM', $dest = 'I')
    {
        self::requerirLibreria('pdf');

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
        $pdf->SetFont('helvetica', '', 10);

        // Agregar página
        $pdf->AddPage();

        // Título
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, $title, 0, 1, 'C');

        // Fecha
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'C');
        $pdf->Ln(5);

        // Tabla HTML
        $html = '<table border="1" cellpadding="4" cellspacing="0" width="100%">';

        // Headers
        $html .= '<tr style="background-color: #4B5563; color: #FFFFFF; font-weight: bold;">';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr>';

        // Datos
        foreach ($data as $record) {
            $html .= '<tr>';
            foreach ($record as $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';

        // Escribir HTML
        $pdf->writeHTML($html, true, false, true, false, '');

        // Salida
        $this->prepararSalidaBinaria();
        $pdf->Output($filename, $dest);
        if ($dest === 'F') return true;
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
    public function exportDetalladoPDF($data, $filename, $filters = [], $dest = 'I')
    {
        self::requerirLibreria('pdf');

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
            // $estado debe asignarse ANTES: clasificacionLabel() lo usa como respaldo
            // cuando la clasificacion viene vacia. Antes se leia el estado de la fila
            // anterior (y en la primera fila, una variable indefinida).
            $estado = $item['estado_actual'];
            $clasificacionLabel = clasificacionLabel($clasificacion, $estado);

            $comunaSpan = $item['comuna_span'] ?? 1;
            $estSpan = $item['est_span'] ?? 1;
            $mesSpan = $item['mes_span'] ?? 1;

            // Color de fondo según estado
            $bgColor = '#FFFFFF';
            if (esCorregido($clasificacion, $estado)) {
                $bgColor = '#E8F5E9';
            } elseif ($estado === ESTADO_PENDIENTE || strtolower(trim((string) $clasificacion)) === CLASIF_SIN_RESPUESTA) {
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
            $html .= '<td width="' . $colWidths[4] . '" align="center" style="padding: 2px;">' . htmlspecialchars($clasificacionLabel) . '</td>';

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

        $this->prepararSalidaBinaria();
        $pdf->Output($filename, $dest);
        if ($dest === 'F') return true;
        exit;
    }

    /**
     * Exportar reporte de errores a Excel
     */
    public function exportErroresExcel($data, $filename, $reportType = 'general', $download = true)
    {
        self::requerirLibreria('excel');

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
        if (!$download) {
            $writer->save($filename);
            return true;
        }
        $this->prepararSalidaBinaria();
        $this->cabecerasArchivo('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $filename);
        $writer->save('php://output');
        exit;
    }

    /**
     * Exportar Informe de Errores REM a PDF
     * Formato vertical (portrait), diseño moderno para presentación a directivos
     */
    public function exportInformeErroresPDF($data, $periodo, $filename)
    {
        self::requerirLibreria('pdf');

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
            $this->prepararSalidaBinaria();
            $pdf->Output($filename, 'I');
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
            $clasificacion = htmlspecialchars(clasificacionLabel($item['clasificacion'], $item['estado_actual'] ?? null));
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

        $this->prepararSalidaBinaria();
        $pdf->Output($filename, 'I');
        exit;
    }

    // =====================================================================
    // BOLETIN INFORMATIVO DE ERRORES REM
    // Documento ejecutivo agrupado Comuna > Establecimiento > Mes.
    // Los anchos de columna son CONSTANTES: la tabla se ve identica con
    // una observacion o con quinientas (requisito del boletin impreso).
    // =====================================================================

    /** Definicion unica de columnas, compartida por Excel y PDF. */
    public static function boletinColumnas()
    {
        return [
            ['clave' => 'comuna',          'titulo' => 'COMUNA',                    'excel' => 14, 'pdf' => 22, 'pct' => '10.8%'],
            ['clave' => 'establecimiento', 'titulo' => 'ESTABLECIMIENTO',           'excel' => 22, 'pdf' => 32, 'pct' => '15.4%'],
            ['clave' => 'mes',             'titulo' => 'MES',                       'excel' => 10, 'pdf' => 16, 'pct' => '7.7%'],
            ['clave' => 'rem',             'titulo' => 'REM',                       'excel' => 12, 'pdf' => 20, 'pct' => '9.2%'],
            ['clave' => 'detalle',         'titulo' => 'DETALLE DE LA OBSERVACION', 'excel' => 60, 'pdf' => 60, 'pct' => '46.2%'],
            ['clave' => 'estado',          'titulo' => 'ESTADO',                    'excel' => 18, 'pdf' => 22, 'pct' => '10.8%'],
            ['clave' => 'num',             'titulo' => 'N',                         'excel' =>  7, 'pdf' => 10, 'pct' => '3.8%'],
        ];
    }

    /** Clave de agrupacion a partir de varios campos. */
    private function claveGrupo($row, $campos)
    {
        $partes = [];
        foreach ($campos as $c) {
            $partes[] = (string) ($row[$c] ?? '');
        }
        return implode('||', $partes);
    }

    /**
     * Calcula los rowspan por nivel jerarquico.
     * Devuelve, por fila, [nivel => n]: n > 0 abre un grupo de n filas,
     * n === 0 significa que la celda queda absorbida por la combinacion.
     */
    private function agruparJerarquia(array $data, array $niveles)
    {
        $n = count($data);
        $spans = [];
        for ($i = 0; $i < $n; $i++) {
            $spans[$i] = [];
        }

        foreach ($niveles as $nombre => $campos) {
            $i = 0;
            while ($i < $n) {
                $clave = $this->claveGrupo($data[$i], $campos);
                $j = $i + 1;
                while ($j < $n && $this->claveGrupo($data[$j], $campos) === $clave) {
                    $j++;
                }
                $spans[$i][$nombre] = $j - $i;
                for ($k = $i + 1; $k < $j; $k++) {
                    $spans[$k][$nombre] = 0;
                }
                $i = $j;
            }
        }

        return $spans;
    }

    /** Niveles de agrupacion del boletin. */
    private function boletinNiveles()
    {
        return [
            'comuna'          => ['comuna'],
            'establecimiento' => ['comuna', 'establecimiento'],
            'mes'             => ['comuna', 'establecimiento', 'mes'],
        ];
    }

    /** Normaliza una fila cruda a las celdas visibles del boletin. */
    private function boletinFila($row)
    {
        $rem = trim(($row['codigo_serie'] ?? '') . ' / ' . ($row['codigo_hoja'] ?? ''), ' /');

        return [
            'comuna'          => $row['comuna'] ?? '',
            'establecimiento' => $row['establecimiento'] ?? '',
            'mes'             => $row['mes'] ?? '',
            'rem'             => $rem,
            'detalle'         => $row['detalle_observacion'] ?? '',
            'estado'          => clasificacionLabel($row['clasificacion'] ?? null, $row['estado_actual'] ?? null),
        ];
    }

    /** Colores de relleno del estado (ARGB para Excel). */
    private function boletinColorEstado($row)
    {
        $clasif = strtolower(trim((string) ($row['clasificacion'] ?? '')));
        $estado = $row['estado_actual'] ?? null;

        if (esCorregido($clasif, $estado)) {
            return ['bg' => 'FFDCFCE7', 'fg' => 'FF14532D'];
        }
        if ($clasif === CLASIF_SIN_RESPUESTA) {
            return ['bg' => 'FFFEF3C7', 'fg' => 'FF78350F'];
        }
        if ($clasif === CLASIF_RESPUESTA_INCORRECTA || $clasif === CLASIF_ERROR) {
            return ['bg' => 'FFFEE2E2', 'fg' => 'FF7F1D1D'];
        }
        return ['bg' => 'FFF1F5F9', 'fg' => 'FF1E293B'];
    }

    /**
     * Boletin Informativo de Errores REM en Excel.
     *
     * A diferencia del resto de exportaciones del sistema, aqui NO se usa
     * setAutoSize: los anchos son fijos para que el documento impreso sea
     * identico sin importar cuantas observaciones traiga.
     *
     * @param array $data filas de Observation::getBoletinErrores()
     * @param array $meta ['periodo' => string, 'resumen' => array]
     */
    public function exportBoletinExcel($data, $meta, $filename, $download = true)
    {
        self::requerirLibreria('excel');

        $columnas = self::boletinColumnas();
        $letras = range('A', 'G');
        $ultima = 'G';

        // Use class constants instead of local variables
        $NAVY = self::COLOR_NAVY_ARGB;
        $CIAN = self::COLOR_CIAN_ARGB;
        $SUAVE = self::COLOR_SUAVE_ARGB;
        $BORDE = self::COLOR_BORDE_ARGB;

        $periodo = $meta['periodo'] ?? '';
        $resumen = $meta['resumen'] ?? [];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Boletin');

        $spreadsheet->getProperties()
            ->setCreator('Sistema de Observaciones REM')
            ->setTitle('Boletin Informativo de Errores REM')
            ->setSubject($periodo)
            ->setCompany('Servicio de Salud Osorno');

        // ---- Anchos FIJOS (nunca setAutoSize) ----
        foreach ($columnas as $i => $col) {
            $sheet->getColumnDimension($letras[$i])->setWidth($col['excel']);
        }

        // ---- Cabecera institucional ----
        $sheet->getRowDimension(1)->setRowHeight(42);
        $logo = __DIR__ . '/../assets/images/logo-boletin.png';
        if (!file_exists($logo)) {
            $logo = __DIR__ . '/../assets/images/logo.png';
        }
        // Drawing::setPath() necesita GD para leer las dimensiones: si no
        // esta disponible se degrada sin logo en vez de romper la descarga.
        if (file_exists($logo) && extension_loaded('gd')) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('DEIS Osorno');
            $drawing->setDescription('Servicio de Salud Osorno - DEIS');
            $drawing->setPath($logo);
            $drawing->setHeight(38);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(6);
            $drawing->setOffsetY(3);
            $drawing->setWorksheet($sheet);
        }

        $sheet->mergeCells('B1:' . $ultima . '1');
        $sheet->setCellValue('B1', 'SERVICIO DE SALUD OSORNO   |   DEGI - Departamento de Estadistica');
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(12)->getColor()->setARGB($NAVY);
        $sheet->getStyle('B1')->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->mergeCells('A2:' . $ultima . '2');
        $sheet->setCellValue('A2', 'BOLETIN INFORMATIVO DE ERRORES REM');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB($NAVY);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(24);

        $sheet->mergeCells('A3:' . $ultima . '3');
        $sheet->setCellValue('A3', $periodo . '   |   Emitido: ' . date('d/m/Y H:i'));
        $sheet->getStyle('A3')->getFont()->setSize(10)->getColor()->setARGB('FF475569');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ---- Resumen ejecutivo ----
        $sheet->mergeCells('A4:' . $ultima . '4');
        $sheet->setCellValue('A4', sprintf(
            'Total de errores: %d     Corregidos: %d (%s%%)     Sin respuesta: %d     Establecimientos: %d     Comunas: %d',
            $resumen['total_errores'] ?? 0,
            $resumen['corregidos'] ?? 0,
            $resumen['pct_corregidos'] ?? 0,
            $resumen['sin_respuesta'] ?? 0,
            $resumen['establecimientos'] ?? 0,
            $resumen['comunas'] ?? 0
        ));
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(10)->getColor()->setARGB($NAVY);
        $sheet->getStyle('A4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($SUAVE);
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(4)->setRowHeight(20);

        // Banda de acento cian del logo
        $sheet->mergeCells('A5:' . $ultima . '5');
        $sheet->getStyle('A5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($CIAN);
        $sheet->getRowDimension(5)->setRowHeight(4);

        // ---- Encabezados (fila 6) ----
        $filaHead = 6;
        foreach ($columnas as $i => $col) {
            $sheet->setCellValue($letras[$i] . $filaHead, $col['titulo']);
        }
        $rangoHead = 'A' . $filaHead . ':' . $ultima . $filaHead;
        $sheet->getStyle($rangoHead)->getFont()->setBold(true)->setSize(10)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($rangoHead)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($NAVY);
        $sheet->getStyle($rangoHead)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getRowDimension($filaHead)->setRowHeight(32);

        // ---- Datos ----
        $filaInicio = $filaHead + 1;
        $fila = $filaInicio;
        $TEXTO = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING;

        if (empty($data)) {
            $sheet->mergeCells('A' . $fila . ':' . $ultima . $fila);
            $sheet->setCellValue('A' . $fila, 'No se encontraron errores en el periodo seleccionado.');
            $sheet->getStyle('A' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension($fila)->setRowHeight(24);
            $fila++;
        } else {
            $spans = $this->agruparJerarquia($data, $this->boletinNiveles());

            foreach ($data as $idx => $row) {
                $celdas = $this->boletinFila($row);

                $sheet->setCellValueExplicit('A' . $fila, $celdas['comuna'], $TEXTO);
                $sheet->setCellValueExplicit('B' . $fila, $celdas['establecimiento'], $TEXTO);
                $sheet->setCellValueExplicit('C' . $fila, $celdas['mes'], $TEXTO);
                $sheet->setCellValueExplicit('D' . $fila, $celdas['rem'], $TEXTO);
                $sheet->setCellValueExplicit('E' . $fila, $celdas['detalle'], $TEXTO);
                $sheet->setCellValueExplicit('F' . $fila, $celdas['estado'], $TEXTO);

                // Texto envuelto en la columna detalle; alto automatico
                $sheet->getStyle('E' . $fila)->getAlignment()
                    ->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getRowDimension($fila)->setRowHeight(-1);

                // Estado con color segun clasificacion
                $color = $this->boletinColorEstado($row);
                $sheet->getStyle('F' . $fila)->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($color['bg']);
                $sheet->getStyle('F' . $fila)->getFont()->setBold(true)->getColor()->setARGB($color['fg']);
                $sheet->getStyle('F' . $fila)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                // Combinaciones verticales por grupo
                $spanComuna = $spans[$idx]['comuna'] ?? 1;
                if ($spanComuna > 1) {
                    $sheet->mergeCells('A' . $fila . ':A' . ($fila + $spanComuna - 1));
                }
                $spanEst = $spans[$idx]['establecimiento'] ?? 1;
                if ($spanEst > 1) {
                    $sheet->mergeCells('B' . $fila . ':B' . ($fila + $spanEst - 1));
                }
                if ($spanEst > 0) {
                    // N de errores del establecimiento
                    $sheet->setCellValue('G' . $fila, $spanEst);
                    if ($spanEst > 1) {
                        $sheet->mergeCells('G' . $fila . ':G' . ($fila + $spanEst - 1));
                    }
                    $sheet->getStyle('G' . $fila)->getFont()->setBold(true);
                }
                $spanMes = $spans[$idx]['mes'] ?? 1;
                if ($spanMes > 1) {
                    $sheet->mergeCells('C' . $fila . ':C' . ($fila + $spanMes - 1));
                }

                // Celdas de agrupacion: centradas y con fondo suave
                $sheet->getStyle('A' . $fila . ':D' . $fila)->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle('G' . $fila)->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $fila . ':C' . $fila)->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($SUAVE);

                $fila++;
            }
        }

        $ultimaFila = max($fila - 1, $filaInicio);

        // ---- Bordes y tipografia del cuerpo ----
        $rangoTotal = 'A' . $filaHead . ':' . $ultima . $ultimaFila;
        $sheet->getStyle($rangoTotal)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB($BORDE);
        $sheet->getStyle('A' . $filaInicio . ':' . $ultima . $ultimaFila)->getFont()->setSize(9);

        // ---- Vista e impresion ----
        $sheet->freezePane('A' . $filaInicio);

        $ps = $sheet->getPageSetup();
        $ps->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $ps->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $ps->setFitToWidth(1);
        $ps->setFitToHeight(0);
        // Encabezado repetido en cada pagina impresa
        $ps->setRowsToRepeatAtTopByStartAndEnd($filaHead, $filaHead);

        $sheet->setPrintGridlines(false);
        $sheet->getPageMargins()->setTop(0.5)->setBottom(0.5)->setLeft(0.3)->setRight(0.3);
        $sheet->getHeaderFooter()->setOddFooter(
            '&LBoletin Informativo de Errores REM&CPagina &P de &N&R' . date('d/m/Y')
        );

        $writer = new Xlsx($spreadsheet);
        if (!$download) {
            $writer->save($filename);
            return true;
        }

        $this->prepararSalidaBinaria();
        $this->cabecerasArchivo('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $filename);
        $writer->save('php://output');
        exit;
    }

    /**
     * Boletin Informativo de Errores REM en PDF (A4 horizontal).
     *
     * Se emite un writeHTML por COMUNA: TCPDF descuadra los rowspan que
     * cruzan un salto de pagina, y ademas el documento queda seccionado
     * por comuna, que es como se presenta.
     *
     * @param array $data filas de Observation::getBoletinErrores()
     * @param array $meta ['periodo' => string, 'resumen' => array]
     */
    public function exportBoletinPDF($data, $meta, $filename, $dest = 'I')
    {
        self::requerirLibreria('pdf');

        $columnas = self::boletinColumnas();
        $periodo = $meta['periodo'] ?? '';
        $resumen = $meta['resumen'] ?? [];

        // Use class constants for consistency
        $NAVY = self::COLOR_NAVY_HEX;
        $CIAN = self::COLOR_CIAN_HEX;
        $SUAVE = self::COLOR_SUAVE_HEX;
        $BORDE = self::COLOR_BORDE_HEX;

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('Sistema de Observaciones REM');
        $pdf->SetAuthor('Servicio de Salud Osorno');
        $pdf->SetTitle('Boletin Informativo de Errores REM - ' . $periodo);

        $pdf->SetMargins(10, 38, 10);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(12);
        $pdf->SetAutoPageBreak(true, 18);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterFont(['helvetica', '', 8]);

        $pdf->AddPage();

        // ---- Encabezado institucional ----
        $logo = __DIR__ . '/../assets/images/logo-boletin.png';
        if (!file_exists($logo)) {
            $logo = __DIR__ . '/../assets/images/logo.png';
        }
        if (file_exists($logo)) {
            $pdf->Image($logo, 12, 8, 26, 0, 'PNG', '', '', false, 300, '', false, false, 0);
        }

        $pdf->SetY(10);
        $pdf->SetFont('helvetica', 'B', 13);
        $pdf->Cell(0, 6, 'SERVICIO SALUD OSORNO', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 4, 'DEGI - Departamento de Estadistica', 0, 1, 'C');

        // Linea de acento cian del logo
        $pdf->SetDrawColor(0, 174, 239);
        $pdf->SetLineWidth(0.6);
        $pdf->Line(60, $pdf->GetY() + 2, $pdf->getPageWidth() - 60, $pdf->GetY() + 2);
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 15);
        $pdf->SetTextColor(0, 51, 102);
        $pdf->Cell(0, 7, 'BOLETIN INFORMATIVO DE ERRORES REM', 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, $periodo, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 4, 'Emitido: ' . date('d/m/Y H:i'), 0, 1, 'C');
        $pdf->Ln(2);

        if (empty($data)) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 10, 'No se encontraron errores en el periodo seleccionado.', 0, 1, 'C');
            $this->prepararSalidaBinaria();
            return $pdf->Output($filename, $dest);
        }

        // ---- Resumen ejecutivo ----
        $resumenHtml = '<table border="0" cellpadding="4" style="width:100%;">'
            . '<tr style="background-color:' . $SUAVE . ';">'
            . '<td align="center" style="color:' . $NAVY . ';"><b>Total de errores:</b> ' . (int) ($resumen['total_errores'] ?? 0)
            . ' &nbsp;&nbsp; <b>Corregidos:</b> ' . (int) ($resumen['corregidos'] ?? 0)
            . ' (' . ($resumen['pct_corregidos'] ?? 0) . '%)'
            . ' &nbsp;&nbsp; <b>Sin respuesta:</b> ' . (int) ($resumen['sin_respuesta'] ?? 0)
            . ' &nbsp;&nbsp; <b>Establecimientos:</b> ' . (int) ($resumen['establecimientos'] ?? 0)
            . ' &nbsp;&nbsp; <b>Comunas:</b> ' . (int) ($resumen['comunas'] ?? 0)
            . '</td></tr></table>';
        $pdf->writeHTML($resumenHtml, true, false, false, false, '');
        $pdf->Ln(2);

        // ---- Tabla por comuna ----
        $porComuna = [];
        foreach ($data as $row) {
            $porComuna[$row['comuna'] ?? 'SIN COMUNA'][] = $row;
        }

        $encabezado = '<thead><tr style="background-color:' . $NAVY . '; color:#FFFFFF;">';
        foreach ($columnas as $col) {
            $encabezado .= '<th width="' . $col['pct'] . '" align="center" style="font-weight:bold;">'
                . htmlspecialchars($col['titulo'], ENT_QUOTES, 'UTF-8') . '</th>';
        }
        $encabezado .= '</tr></thead>';

        $primera = true;
        foreach ($porComuna as $comuna => $filas) {
            if (!$primera) {
                $pdf->AddPage();
            }
            $primera = false;

            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor(0, 51, 102);
            $pdf->Cell(0, 6, 'COMUNA: ' . $comuna, 0, 1, 'L');
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Ln(1);

            $spans = $this->agruparJerarquia($filas, $this->boletinNiveles());

            $html = '<table border="0.3" cellpadding="3" style="width:100%; border-color:' . $BORDE . ';">'
                . $encabezado . '<tbody>';

            foreach ($filas as $idx => $row) {
                $celdas = $this->boletinFila($row);
                $color = $this->boletinColorEstado($row);
                $bgEstado = '#' . substr($color['bg'], 2);
                $fgEstado = '#' . substr($color['fg'], 2);

                $html .= '<tr>';

                $spanComuna = $spans[$idx]['comuna'] ?? 1;
                if ($spanComuna > 0) {
                    $html .= '<td width="' . $columnas[0]['pct'] . '" rowspan="' . $spanComuna . '" align="center"'
                        . ' style="background-color:' . $SUAVE . '; font-weight:bold;">'
                        . htmlspecialchars($celdas['comuna'], ENT_QUOTES, 'UTF-8') . '</td>';
                }

                $spanEst = $spans[$idx]['establecimiento'] ?? 1;
                if ($spanEst > 0) {
                    $html .= '<td width="' . $columnas[1]['pct'] . '" rowspan="' . $spanEst . '" align="center"'
                        . ' style="background-color:' . $SUAVE . ';">'
                        . htmlspecialchars($celdas['establecimiento'], ENT_QUOTES, 'UTF-8') . '</td>';
                }

                $spanMes = $spans[$idx]['mes'] ?? 1;
                if ($spanMes > 0) {
                    $html .= '<td width="' . $columnas[2]['pct'] . '" rowspan="' . $spanMes . '" align="center"'
                        . ' style="background-color:' . $SUAVE . ';">'
                        . htmlspecialchars($celdas['mes'], ENT_QUOTES, 'UTF-8') . '</td>';
                }

                $html .= '<td width="' . $columnas[3]['pct'] . '" align="center" style="color:' . $NAVY . '; font-weight:bold;">'
                    . htmlspecialchars($celdas['rem'], ENT_QUOTES, 'UTF-8') . '</td>';
                $html .= '<td width="' . $columnas[4]['pct'] . '">'
                    . nl2br(htmlspecialchars($celdas['detalle'], ENT_QUOTES, 'UTF-8')) . '</td>';
                $html .= '<td width="' . $columnas[5]['pct'] . '" align="center"'
                    . ' style="background-color:' . $bgEstado . '; color:' . $fgEstado . '; font-weight:bold;">'
                    . htmlspecialchars($celdas['estado'], ENT_QUOTES, 'UTF-8') . '</td>';

                if ($spanEst > 0) {
                    $html .= '<td width="' . $columnas[6]['pct'] . '" rowspan="' . $spanEst . '" align="center"'
                        . ' style="font-weight:bold;">' . $spanEst . '</td>';
                }

                $html .= '</tr>';
            }

            $html .= '</tbody></table>';

            $pdf->SetFont('helvetica', '', 7);
            $pdf->writeHTML($html, true, false, false, false, '');
        }

        // ---- Firma ----
        $pdf->Ln(8);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, '_______________________________', 0, 1, 'R');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(0, 5, 'Departamento de Estadistica DEGI', 0, 1, 'R');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 4, 'Servicio de Salud Osorno', 0, 1, 'R');

        $this->prepararSalidaBinaria();
        return $pdf->Output($filename, $dest);
    }

    // ==========================================================
    // Matriz mensual de cumplimiento (Plazo de entrega / Uso de validador)
    // Misma tabla que muestran las pestanas del modulo Reportes.
    // ==========================================================

    /**
     * Reorganiza el detalle mensual en la matriz que se presenta:
     * una fila por establecimiento y una columna por mes.
     *
     * @param array  $detalle    filas de Observation::reportePlazoMensual()/reporteValidadorMensual()
     * @param array  $mesesFiltro meses pedidos; si viene vacio se usan los que tengan datos
     * @param string $campoOk    columna binaria de cumplimiento ('dentro' | 'usa')
     * @param string $campoFalla columna binaria de incumplimiento ('fuera' | 'no_usa')
     * @return array ['meses' => [...], 'filas' => [['nombre','celdas'=>['ok'|'falla'|'vacio'],'cumple','conDatos']], 'totales' => [...]]
     */
    public function armarMatrizMensual(array $detalle, array $mesesFiltro, string $campoOk, string $campoFalla): array
    {
        $meses = $mesesFiltro;
        if (empty($meses)) {
            $conDatos = array_unique(array_column($detalle, 'mes'));
            $meses = array_values(array_filter(self::MESES, fn($m) => in_array($m, $conDatos, true)));
        }

        $porEst = [];
        foreach ($detalle as $row) {
            $clave = $row['nombre_corto'];
            if (!isset($porEst[$clave])) {
                $porEst[$clave] = ['nombre' => $clave, 'meses' => []];
            }
            $porEst[$clave]['meses'][$row['mes']] = $row;
        }

        $totales = array_fill(0, count($meses), ['ok' => 0, 'conDatos' => 0]);
        $filas = [];

        foreach ($porEst as $est) {
            $celdas = [];
            $cumple = 0;
            $conDatos = 0;

            foreach ($meses as $i => $mes) {
                $row = $est['meses'][$mes] ?? null;
                $estado = 'vacio';

                if ($row) {
                    $conDatos++;
                    $totales[$i]['conDatos']++;
                    if ((int) $row[$campoFalla] === 1) {
                        $estado = 'falla';
                    } elseif ((int) $row[$campoOk] === 1) {
                        $estado = 'ok';
                        $cumple++;
                        $totales[$i]['ok']++;
                    }
                }
                $celdas[] = $estado;
            }

            $filas[] = [
                'nombre' => $est['nombre'],
                'celdas' => $celdas,
                'cumple' => $cumple,
                'conDatos' => $conDatos,
            ];
        }

        return ['meses' => $meses, 'filas' => $filas, 'totales' => $totales];
    }

    /** Etiqueta corta del mes para las cabeceras de la matriz. */
    private static function mesAbrev(string $mes): string
    {
        return self::MESES_ABREV[$mes] ?? strtoupper(mb_substr($mes, 0, 3));
    }

    /** Simbolo de cada estado en las exportaciones (sin depender de tipografias con iconos). */
    private static function simboloEstado(string $estado): string
    {
        if ($estado === 'ok') return 'SI';
        if ($estado === 'falla') return 'NO';
        return '-';
    }

    /**
     * Matriz mensual en Excel, con el mismo formato institucional del boletin.
     *
     * @param array $meta ['titulo' => string, 'periodo' => string, 'etiquetaOk' => string, 'etiquetaFalla' => string]
     */
    public function exportMatrizMensualExcel(array $matriz, array $meta, string $filename, bool $download = true)
    {
        self::requerirLibreria('excel');

        $meses = $matriz['meses'];
        $titulo = $meta['titulo'] ?? 'Matriz de cumplimiento';
        $periodo = $meta['periodo'] ?? '';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Matriz');

        $totalCols = count($meses) + 2; // establecimiento + meses + cumple
        $ultimaCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

        // ---- Encabezado ----
        $sheet->setCellValue('A1', 'SERVICIO DE SALUD OSORNO');
        $sheet->mergeCells('A1:' . $ultimaCol . '1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)
            ->getColor()->setARGB(self::COLOR_NAVY_ARGB);

        $sheet->setCellValue('A2', $titulo);
        $sheet->mergeCells('A2:' . $ultimaCol . '2');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

        $sheet->setCellValue('A3', $periodo);
        $sheet->mergeCells('A3:' . $ultimaCol . '3');

        $sheet->setCellValue('A4', 'Emitido: ' . date('d/m/Y H:i'));
        $sheet->mergeCells('A4:' . $ultimaCol . '4');
        $sheet->getStyle('A4')->getFont()->setSize(9)->setItalic(true);

        foreach (['A1', 'A2', 'A3', 'A4'] as $celda) {
            $sheet->getStyle($celda)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // ---- Cabecera de la tabla ----
        $filaCab = 6;
        $sheet->setCellValue('A' . $filaCab, 'ESTABLECIMIENTO');
        foreach ($meses as $i => $mes) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 2);
            $sheet->setCellValue($col . $filaCab, self::mesAbrev($mes));
        }
        $sheet->setCellValue($ultimaCol . $filaCab, 'CUMPLE');

        $rangoCab = 'A' . $filaCab . ':' . $ultimaCol . $filaCab;
        $sheet->getStyle($rangoCab)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($rangoCab)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(self::COLOR_NAVY_ARGB);
        $sheet->getStyle($rangoCab)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ---- Filas ----
        $VERDE = 'FFDCF5E7';
        $ROJO = 'FFFDE2E2';
        $fila = $filaCab + 1;

        foreach ($matriz['filas'] as $registro) {
            $sheet->setCellValue('A' . $fila, $registro['nombre']);
            $sheet->getStyle('A' . $fila)->getFill()->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB(self::COLOR_SUAVE_ARGB);
            $sheet->getStyle('A' . $fila)->getFont()->setBold(true);

            foreach ($registro['celdas'] as $i => $estado) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 2);
                $sheet->setCellValue($col . $fila, self::simboloEstado($estado));
                $sheet->getStyle($col . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                if ($estado === 'ok') {
                    $sheet->getStyle($col . $fila)->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($VERDE);
                } elseif ($estado === 'falla') {
                    $sheet->getStyle($col . $fila)->getFill()->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($ROJO);
                    $sheet->getStyle($col . $fila)->getFont()->setBold(true);
                }
            }

            $resumen = $registro['conDatos'] ? $registro['cumple'] . '/' . $registro['conDatos'] . ' (' . round($registro['cumple'] / $registro['conDatos'] * 100) . '%)' : '-';
            $sheet->setCellValue($ultimaCol . $fila, $resumen);
            $sheet->getStyle($ultimaCol . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($ultimaCol . $fila)->getFont()->setBold(true);
            $fila++;
        }

        // ---- Totales ----
        $sheet->setCellValue('A' . $fila, 'CUMPLEN');
        foreach ($matriz['totales'] as $i => $t) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 2);
            $sheet->setCellValue($col . $fila, $t['conDatos'] ? $t['ok'] . '/' . $t['conDatos'] : '-');
            $sheet->getStyle($col . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        $rangoTot = 'A' . $fila . ':' . $ultimaCol . $fila;
        $sheet->getStyle($rangoTot)->getFont()->setBold(true);
        $sheet->getStyle($rangoTot)->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB(self::COLOR_SUAVE_ARGB);

        // ---- Bordes, anchos y leyenda ----
        $sheet->getStyle('A' . $filaCab . ':' . $ultimaCol . $fila)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB(self::COLOR_BORDE_ARGB);

        $sheet->getColumnDimension('A')->setWidth(46);
        for ($i = 2; $i <= $totalCols; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setWidth(8);
        }

        $filaLeyenda = $fila + 2;
        $sheet->setCellValue('A' . $filaLeyenda, 'SI = ' . ($meta['etiquetaOk'] ?? 'Cumple')
            . '   |   NO = ' . ($meta['etiquetaFalla'] ?? 'No cumple') . '   |   - = Sin datos');
        $sheet->getStyle('A' . $filaLeyenda)->getFont()->setSize(9)->setItalic(true);
        $sheet->setCellValue('A' . ($filaLeyenda + 1), 'Fuente: DEIS-SSO');
        $sheet->getStyle('A' . ($filaLeyenda + 1))->getFont()->setSize(9)->setItalic(true);

        // Repetir la cabecera al imprimir y ajustar al ancho de la hoja
        $sheet->freezePane('B' . ($filaCab + 1));
        $ps = $sheet->getPageSetup();
        $ps->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $ps->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $ps->setFitToWidth(1);
        $ps->setFitToHeight(0);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($filaCab, $filaCab);

        $writer = new Xlsx($spreadsheet);
        if (!$download) {
            $writer->save($filename);
            return true;
        }
        $this->prepararSalidaBinaria();
        $this->cabecerasArchivo('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $filename);
        $writer->save('php://output');
        exit;
    }

    /**
     * Matriz mensual en PDF (A4 horizontal), con el encabezado institucional del boletin.
     */
    public function exportMatrizMensualPDF(array $matriz, array $meta, string $filename, string $dest = 'I')
    {
        self::requerirLibreria('pdf');

        $meses = $matriz['meses'];
        $titulo = $meta['titulo'] ?? 'Matriz de cumplimiento';
        $periodo = $meta['periodo'] ?? '';

        $NAVY = self::COLOR_NAVY_HEX;
        $SUAVE = self::COLOR_SUAVE_HEX;
        $BORDE = self::COLOR_BORDE_HEX;
        $VERDE = '#DCF5E7';
        $ROJO = '#FDE2E2';

        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8');
        $pdf->SetCreator('Sistema de Observaciones REM');
        $pdf->SetAuthor('Servicio de Salud Osorno');
        $pdf->SetTitle($titulo . ' - ' . $periodo);
        $pdf->SetMargins(10, 12, 10);
        $pdf->SetFooterMargin(12);
        $pdf->SetAutoPageBreak(true, 16);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterFont(['helvetica', '', 8]);
        $pdf->AddPage();

        // ---- Encabezado institucional ----
        $logo = __DIR__ . '/../assets/images/logo-boletin.png';
        if (!file_exists($logo)) {
            $logo = __DIR__ . '/../assets/images/logo.png';
        }
        if (file_exists($logo)) {
            $pdf->Image($logo, 12, 8, 24, 0, 'PNG', '', '', false, 300, '', false, false, 0);
        }

        $pdf->SetY(10);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 6, 'SERVICIO SALUD OSORNO', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 4, 'DEGI - Departamento de Estadistica', 0, 1, 'C');

        $pdf->SetDrawColor(0, 174, 239);
        $pdf->SetLineWidth(0.6);
        $pdf->Line(60, $pdf->GetY() + 2, $pdf->getPageWidth() - 60, $pdf->GetY() + 2);
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(0, 51, 102);
        $pdf->Cell(0, 7, mb_strtoupper($titulo, 'UTF-8'), 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, $periodo, 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 4, 'Emitido: ' . date('d/m/Y H:i'), 0, 1, 'C');
        $pdf->Ln(2);

        if (empty($matriz['filas'])) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->Cell(0, 10, 'No se encontraron datos para los filtros seleccionados.', 0, 1, 'C');
            $this->prepararSalidaBinaria();
            return $pdf->Output($filename, $dest);
        }

        // ---- Tabla ----
        $anchoMes = max(8, min(16, 150 / max(1, count($meses))));
        $anchoEst = 100 - ($anchoMes * count($meses)) - 8;
        if ($anchoEst < 25) $anchoEst = 25;

        $html = '<table border="0" cellpadding="3" cellspacing="0" style="width:100%;">';
        $html .= '<thead><tr style="background-color:' . $NAVY . ';color:#FFFFFF;font-weight:bold;">';
        $html .= '<th width="' . $anchoEst . '%" align="left">ESTABLECIMIENTO</th>';
        foreach ($meses as $mes) {
            $html .= '<th width="' . $anchoMes . '%" align="center">' . self::mesAbrev($mes) . '</th>';
        }
        $html .= '<th width="8%" align="center">CUMPLE</th></tr></thead><tbody>';

        foreach ($matriz['filas'] as $registro) {
            $html .= '<tr>';
            $html .= '<td style="background-color:' . $SUAVE . ';"><b>' . htmlspecialchars($registro['nombre'], ENT_QUOTES, 'UTF-8') . '</b></td>';
            foreach ($registro['celdas'] as $estado) {
                $fondo = $estado === 'ok' ? $VERDE : ($estado === 'falla' ? $ROJO : '#FFFFFF');
                $html .= '<td align="center" style="background-color:' . $fondo . ';">' . self::simboloEstado($estado) . '</td>';
            }
            $resumen = $registro['conDatos'] ? $registro['cumple'] . '/' . $registro['conDatos'] . ' (' . round($registro['cumple'] / $registro['conDatos'] * 100) . '%)' : '-';
            $html .= '<td align="center"><b>' . $resumen . '</b></td>';
            $html .= '</tr>';
        }

        $html .= '<tr style="background-color:' . $SUAVE . ';font-weight:bold;">';
        $html .= '<td><b>CUMPLEN</b></td>';
        foreach ($matriz['totales'] as $t) {
            $html .= '<td align="center"><b>' . ($t['conDatos'] ? $t['ok'] . '/' . $t['conDatos'] : '-') . '</b></td>';
        }
        $html .= '<td></td></tr>';
        $html .= '</tbody></table>';

        $pdf->SetFont('helvetica', '', 7.5);
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->Cell(0, 4, 'SI = ' . ($meta['etiquetaOk'] ?? 'Cumple')
            . '   |   NO = ' . ($meta['etiquetaFalla'] ?? 'No cumple')
            . '   |   - = Sin datos', 0, 1, 'L');
        $pdf->Cell(0, 4, 'Fuente: DEIS-SSO', 0, 1, 'L');

        $this->prepararSalidaBinaria();
        return $pdf->Output($filename, $dest);
    }
}
