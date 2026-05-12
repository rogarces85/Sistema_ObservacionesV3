<?php
/**
 * Generador de Plantilla XLSX para Importación
 * Actualizado con nuevos campos: TIPO, SERIE, REM
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Verificar autenticación
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Crear spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Observaciones');

// Encabezados - mismos campos que el formulario de nueva observación
$headers = [
    'A1' => 'codigo_establecimiento',   // PRIORITARIO - valida por código
    'B1' => 'establecimiento',          // OPCIONAL - si no hay código, busca por nombre
    'C1' => 'mes',
    'D1' => 'tipo',
    'E1' => 'serie',
    'F1' => 'rem',
    'G1' => 'detalle_observacion',
    'H1' => 'plazo_entrega',
    'I1' => 'usa_validador',
    'J1' => 'respuesta_establecimiento'
];

foreach ($headers as $cell => $value) {
    $sheet->setCellValue($cell, $value);
}

// Estilo de encabezados
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '17a2b8']
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
];
$sheet->getStyle('A1:J1')->applyFromArray($headerStyle);

// Resaltar columna de código en verde (prioritaria)
$codeStyle = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '28a745']
    ]
];
$sheet->getStyle('A1')->applyFromArray($codeStyle);

// Datos de ejemplo con mismos campos que el formulario
$ejemplos = [
    [125301, 'CESFAM Dr. Marcelo Lopetegui Adams', 'Enero', 'S/OBSERVACION', 'SERIE A', 'A01', 'Sin observaciones', 'dentro_plazo', 'si', ''],
    [123130, 'Hospital Base San José de Osorno', 'Febrero', 'ERROR', 'SERIE BM', 'BM18', 'Discrepancia en total', 'dentro_plazo', 'no', 'Se corrigió'],
    [125310, 'CESFAM Quinta Centenario', 'Marzo', 'REVISAR', 'SERIE D', 'D05', 'Valores a verificar', 'fuera_plazo', 'si', ''],
    [123131, 'Hospital de Purranque Dr. Juan Hepp Dubiau', 'Abril', 'F/PLAZO', 'SERIE ANEXO', 'Hoja Control', 'Entrega fuera de plazo', 'fuera_plazo', 'no', 'Sin respuesta'],
];

$row = 2;
foreach ($ejemplos as $ejemplo) {
    $col = 'A';
    foreach ($ejemplo as $value) {
        $sheet->setCellValue($col . $row, $value);
        $col++;
    }
    $row++;
}

// Estilo de datos
$dataStyle = [
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]
    ],
    'alignment' => [
        'vertical' => Alignment::VERTICAL_TOP,
        'wrapText' => true
    ]
];
$sheet->getStyle('A2:J5')->applyFromArray($dataStyle);

// Ajustar anchos de columna
$sheet->getColumnDimension('A')->setWidth(20);  // codigo_establecimiento
$sheet->getColumnDimension('B')->setWidth(40);  // establecimiento (nombre)
$sheet->getColumnDimension('C')->setWidth(12);  // mes
$sheet->getColumnDimension('D')->setWidth(16);  // tipo
$sheet->getColumnDimension('E')->setWidth(14);  // serie
$sheet->getColumnDimension('F')->setWidth(14);  // rem (hoja)
$sheet->getColumnDimension('G')->setWidth(45);  // detalle_observacion
$sheet->getColumnDimension('H')->setWidth(15);  // plazo_entrega
$sheet->getColumnDimension('I')->setWidth(14);  // usa_validador
$sheet->getColumnDimension('J')->setWidth(40);  // respuesta_establecimiento

// Añadir hoja de instrucciones
$instrucciones = $spreadsheet->createSheet();
$instrucciones->setTitle('Instrucciones');

$instrucciones->setCellValue('A1', 'INSTRUCCIONES DE USO - PLANTILLA OBSERVACIONES REM');
$instrucciones->getStyle('A1')->getFont()->setBold(true)->setSize(14);

$instrucciones->setCellValue('A3', 'IDENTIFICACIÓN DEL ESTABLECIMIENTO (usar una de estas opciones):');
$instrucciones->getStyle('A3')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));

$instrucciones->setCellValue('A4', '• codigo_establecimiento (RECOMENDADO): Código numérico del establecimiento. Ejemplo: 125301, 123130, etc.');
$instrucciones->setCellValue('A5', '• establecimiento: Nombre del establecimiento (solo si no conoces el código). DEBE coincidir exactamente con el nombre en el sistema.');

$instrucciones->setCellValue('A7', 'COLUMNAS OBLIGATORIAS:');
$instrucciones->getStyle('A7')->getFont()->setBold(true);

$instrucciones->setCellValue('A8', '• mes: Nombre del mes (Enero, Febrero, Marzo, etc.)');
$instrucciones->setCellValue('A9', '• tipo: Tipo de registro. Valores válidos: S/OBSERVACION, ERROR, REVISAR, F/PLAZO');

$instrucciones->setCellValue('A11', 'COLUMNAS OPCIONALES (pueden dejarse vacías):');
$instrucciones->getStyle('A11')->getFont()->setBold(true);

$instrucciones->setCellValue('A12', '• serie: Serie REM. Valores válidos: SERIE A, SERIE BM, SERIE BS, SERIE D, SERIE ANEXO, SERIE P');
$instrucciones->setCellValue('A13', '• rem: Nombre/código de la hoja REM (ejemplo: A01, BM18, D05, Hoja Control, etc.)');
$instrucciones->setCellValue('A14', '• detalle_observacion: Descripción detallada de la observación');
$instrucciones->setCellValue('A15', '• plazo_entrega: Valores válidos: dentro_plazo, fuera_plazo');
$instrucciones->setCellValue('A16', '• usa_validador: Valores válidos: si, no');
$instrucciones->setCellValue('A17', '• respuesta_establecimiento: Respuesta recibida del establecimiento');

$instrucciones->setCellValue('A18', 'VALORES VÁLIDOS PARA TIPO:');
$instrucciones->getStyle('A18')->getFont()->setBold(true);
$instrucciones->setCellValue('A19', '• S/OBSERVACION - Sin observaciones');
$instrucciones->setCellValue('A20', '• ERROR - Error detectado');
$instrucciones->setCellValue('A21', '• REVISAR - Requiere revisión');
$instrucciones->setCellValue('A22', '• F/PLAZO - Fuera de plazo');

$instrucciones->setCellValue('A24', 'VALORES VÁLIDOS PARA SERIE:');
$instrucciones->getStyle('A24')->getFont()->setBold(true);
$instrucciones->setCellValue('A25', '• SERIE A');
$instrucciones->setCellValue('A26', '• SERIE BM');
$instrucciones->setCellValue('A27', '• SERIE D');
$instrucciones->setCellValue('A28', '• SERIE ANEXO');
$instrucciones->setCellValue('A29', '• SERIE P');

$instrucciones->getColumnDimension('A')->setWidth(90);

// Volver a la primera hoja
$spreadsheet->setActiveSheetIndex(0);

// Nombre del archivo
$filename = 'plantilla_observaciones_' . date('Y-m-d') . '.xlsx';

// Headers para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Generar y descargar
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
