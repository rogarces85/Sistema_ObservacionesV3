<?php
/**
 * Script para generar PDF del reporte de flujo del sistema
 * Usa TCPDF para convertir contenido HTML a PDF
 */

require_once 'vendor/autoload.php';

// Leer el archivo Markdown
$mdFile = __DIR__ . '/reporte_flujo_sistema.md';
if (!file_exists($mdFile)) {
    die("Archivo no encontrado: $mdFile\n");
}

$mdContent = file_get_contents($mdFile);

// Convertir Markdown simple a HTML
function markdownToHtml($text) {
    // Escapar HTML previo
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    // Tablas (formato simple de pipe)
    $lines = explode("\n", $text);
    $htmlLines = [];
    $inTable = false;
    $tableBuffer = [];

    foreach ($lines as $line) {
        $trimmed = trim($line);

        // Tabla
        if (preg_match('/^\|(.+)\|$/', $trimmed)) {
            if (!$inTable) {
                $inTable = true;
                $tableBuffer = [];
            }
            $tableBuffer[] = $trimmed;
            continue;
        } else {
            if ($inTable) {
                // Procesar tabla acumulada
                $htmlLines[] = processTableBuffer($tableBuffer);
                $tableBuffer = [];
                $inTable = false;
            }
        }

        // Línea horizontal
        if ($trimmed === '---' || preg_match('/^-{3,}$/', $trimmed)) {
            $htmlLines[] = '<hr style="border:0;border-top:1px solid #ccc;margin:12px 0;">';
            continue;
        }

        // Encabezados
        if (preg_match('/^(#{1,6})\s+(.+)$/', $trimmed, $m)) {
            $level = strlen($m[1]);
            $content = $m[2];
            $size = [28, 22, 18, 16, 14, 12][$level - 1] ?? 12;
            $htmlLines[] = "<h$level style=\"font-size:{$size}px;color:#1e293b;margin-top:14px;margin-bottom:6px;\">$content</h$level>";
            continue;
        }

        // Código inline y bold
        $line = preg_replace('/`([^`]+)`/', '<code style="background:#f1f5f9;padding:2px 4px;border-radius:3px;font-size:90%;">$1</code>', $line);
        $line = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $line);
        $line = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $line);
        $line = preg_replace('/&gt;\s+(.+)/', '<blockquote style="margin:8px 0;padding:8px 12px;border-left:4px solid #0ea5e9;background:#f0f9ff;color:#0c4a6e;">$1</blockquote>', $line);

        // Listas
        if (preg_match('/^\s*[-*]\s+(.+)/', $line, $m)) {
            $htmlLines[] = "<li style=\"margin-left:18px;\">{$m[1]}</li>";
            continue;
        }

        // Bloques de código
        if (preg_match('/^```/', $trimmed)) {
            $htmlLines[] = '<pre style="background:#1e293b;color:#e2e8f0;padding:10px;border-radius:6px;font-size:10px;overflow-x:auto;">';
            continue;
        }

        // Párrafos vacíos
        if ($trimmed === '') {
            $htmlLines[] = '';
            continue;
        }

        $htmlLines[] = "<p style=\"margin:6px 0;line-height:1.5;\">$line</p>";
    }

    if ($inTable && !empty($tableBuffer)) {
        $htmlLines[] = processTableBuffer($tableBuffer);
    }

    return implode("\n", $htmlLines);
}

function processTableBuffer($buffer) {
    if (count($buffer) < 2) return '';
    $html = '<table border="1" cellpadding="5" cellspacing="0" style="border-collapse:collapse;width:100%;font-size:11px;margin:8px 0;">';
    $isHeader = true;
    foreach ($buffer as $idx => $row) {
        // Saltar línea separadora |---|---|
        if (preg_match('/^\|[\s\-:|]+\|$/', trim($row))) {
            continue;
        }
        $cells = array_map('trim', explode('|', trim($row, '|')));
        $tag = $isHeader ? 'th' : 'td';
        $bg = $isHeader ? 'background:#e2e8f0;' : '';
        $html .= '<tr>';
        foreach ($cells as $cell) {
            $html .= "<$tag style=\"$bg border:1px solid #cbd5e1;padding:5px;\">$cell</$tag>";
        }
        $html .= '</tr>';
        $isHeader = false;
    }
    $html .= '</table>';
    return $html;
}

$htmlContent = markdownToHtml($mdContent);

// Crear PDF con TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

$pdf->SetCreator('Sistema Observaciones REM');
$pdf->SetAuthor('Servicio de Salud Osorno');
$pdf->SetTitle('Manual de Flujo del Sistema de Observaciones REM');
$pdf->SetSubject('Flujo de trabajo para Supervisor y Registradores');

// Quitar header/footer por defecto
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(true, 15);

$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

// Contenido
$pdf->writeHTML($htmlContent, true, false, true, false, '');

// Guardar
$outputFile = __DIR__ . '/reporte_flujo_sistema.pdf';
$pdf->Output($outputFile, 'F');

echo "PDF generado exitosamente: $outputFile\n";
