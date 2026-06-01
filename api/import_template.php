<?php
/**
 * Generador de Plantilla XLSX para Importación de Observaciones
 * Descarga un archivo Excel con encabezados y ejemplos
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
if (!isset($_SESSION['usuario_id']) || $_SESSION['autenticado'] !== true) {
    header('Location: ../index.php');
    exit;
}

// Solo registradores pueden descargar plantilla
if (($_SESSION['rol'] ?? '') !== ROL_REGISTRADOR) {
    header('Location: ../index.php');
    exit;
}

$spreadsheet = new Spreadsheet();
$hoja = $spreadsheet->getActiveSheet();
$hoja->setTitle('Observaciones');

// Encabezados de la plantilla
$encabezados = [
    'A1' => 'codigo_establecimiento',
    'B1' => 'establecimiento',
    'C1' => 'mes',
    'D1' => 'codigo_serie',
    'E1' => 'codigo_hoja',
    'F1' => 'tipo_error',
    'G1' => 'detalle_observacion',
    'H1' => 'plazo_entrega'
];

foreach ($encabezados as $celda => $valor) {
    $hoja->setCellValue($celda, $valor);
}

// Estilo de encabezados
$estiloEncabezado = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '0d6efd']
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
];
$hoja->getStyle('A1:H1')->applyFromArray($estiloEncabezado);

// Resaltar columna de código DEIS en verde (prioritaria)
$estiloCodigo = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'd1e7dd']
    ]
];
$hoja->getStyle('A1')->applyFromArray($estiloCodigo);

// Datos de ejemplo
$ejemplos = [
    [125301, 'CESFAM Dr. Marcelo Lopetegui Adams', 'Enero', 'SERIE A', 'A01', 'S/OBSERVACION', 'Sin observaciones', 'dentro_plazo'],
    [123130, 'Hospital Base San José de Osorno', '2', 'SERIE BM', 'BM18', 'ERROR', 'Discrepancia en total de egresos', 'dentro_plazo'],
    [125310, 'CESFAM Quinta Centenario', 'Marzo', 'SERIE D', 'D15', 'REVISAR', 'Valores de consultas a verificar', 'fuera_plazo'],
    [123131, 'Hospital de Purranque Dr. Juan Hepp Dubiau', '4', 'SERIE ANEXO', 'Hoja Control', 'F/PLAZO', 'Entrega fuera de plazo regulamentario', 'fuera_plazo'],
];

$fila = 2;
foreach ($ejemplos as $ejemplo) {
    $col = 'A';
    foreach ($ejemplo as $valor) {
        $hoja->setCellValue($col . $fila, $valor);
        $col++;
    }
    $fila++;
}

// Estilo de datos
$estiloDatos = [
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]
    ],
    'alignment' => [
        'vertical' => Alignment::VERTICAL_TOP,
        'wrapText' => true
    ]
];
$hoja->getStyle('A2:H5')->applyFromArray($estiloDatos);

// Anchuras de columna
$hoja->getColumnDimension('A')->setWidth(22);
$hoja->getColumnDimension('B')->setWidth(42);
$hoja->getColumnDimension('C')->setWidth(14);
$hoja->getColumnDimension('D')->setWidth(16);
$hoja->getColumnDimension('E')->setWidth(16);
$hoja->getColumnDimension('F')->setWidth(18);
$hoja->getColumnDimension('G')->setWidth(50);
$hoja->getColumnDimension('H')->setWidth(16);

// Hoja de instrucciones
$instrucciones = $spreadsheet->createSheet();
$instrucciones->setTitle('Instrucciones');

$instrucciones->setCellValue('A1', 'INSTRUCCIONES - PLANTILLA DE IMPORTACIÓN OBSERVACIONES REM');
$instrucciones->getStyle('A1')->getFont()->setBold(true)->setSize(14);

$instrucciones->setCellValue('A3', 'IDENTIFICACIÓN DEL ESTABLECIMIENTO:');
$instrucciones->getStyle('A3')->getFont()->setBold(true)->setSize(12);

$instrucciones->setCellValue('A4', '• codigo_establecimiento (RECOMENDADO): Código numérico DEIS. Ej: 125301, 123130');
$instrucciones->setCellValue('A5', '• establecimiento (OPCIONAL): Nombre del establecimiento. Se usa solo si no hay código.');
$instrucciones->setCellValue('A6', '  El sistema busca primero por código, luego por nombre como respaldo.');

$instrucciones->setCellValue('A8', 'COLUMNAS OBLIGATORIAS:');
$instrucciones->getStyle('A8')->getFont()->setBold(true)->setSize(12);

$instrucciones->setCellValue('A9', '• mes: Número (1-12) o nombre en español (Enero, Febrero, etc.)');
$instrucciones->setCellValue('A10', '• tipo_error: S/OBSERVACION, ERROR, REVISAR, F/PLAZO');

$instrucciones->setCellValue('A12', 'COLUMNAS OPCIONALES:');
$instrucciones->getStyle('A12')->getFont()->setBold(true)->setSize(12);

$instrucciones->setCellValue('A13', '• codigo_serie: SERIE A, SERIE BM, SERIE BS, SERIE D, SERIE ANEXO, SERIE P');
$instrucciones->setCellValue('A14', '• codigo_hoja: Código de hoja (ej: A01, BM18, D15, Hoja Control, etc.)');
$instrucciones->setCellValue('A15', '• detalle_observacion: Descripción de la observación');
$instrucciones->setCellValue('A16', '• plazo_entrega: dentro_plazo, fuera_plazo');

$instrucciones->setCellValue('A18', 'NOTAS IMPORTANTES:');
$instrucciones->getStyle('A18')->getFont()->setBold(true)->setSize(12);

$instrucciones->setCellValue('A19', '• El año se toma del selector del formulario, NO del archivo Excel');
$instrucciones->setCellValue('A20', '• Las filas con errores se muestran en la vista previa pero NO se importan');
$instrucciones->setCellValue('A21', '• Los duplicados se detectan y se muestran; usted decide si importarlos');
$instrucciones->setCellValue('A22', '• Si un establecimiento no se encuentra, la fila se marca como error');

$instrucciones->getColumnDimension('A')->setWidth(90);

// Volver a la primera hoja
$spreadsheet->setActiveSheetIndex(0);

// Nombre del archivo
$nombreArchivo = 'plantilla_observaciones_' . date('Y-m-d') . '.xlsx';

// Headers para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');

// Generar y descargar
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
