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
        $pdf->Output($filename, 'D');
        exit;
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
}
