<?php
/**
 * Worker de Procesamiento de Reportes
 * Este script debe ejecutarse periódicamente (ej. cada minuto vía cron)
 * para procesar los reportes pendientes en la cola.
 * 
 * Uso: php worker_reportes.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/Observation.php';
require_once __DIR__ . '/models/Exporter.php';
require_once __DIR__ . '/models/ReportQueue.php';
require_once __DIR__ . '/models/Database.php';

$queue = new ReportQueue();
$obsModel = new Observation();
$exporter = new Exporter();
$db = Database::getInstance();

// Obtener siguiente reporte pendiente
$report = $queue->getNextPending();

if (!$report) {
    echo "No hay reportes pendientes.\n";
    exit(0);
}

// Marcar como procesando
$queue->markProcessing($report['id']);

echo "Procesando reporte ID: {$report['id']} - Tipo: {$report['tipo_reporte']}\n";

try {
    $parametros = json_decode($report['parametros'], true) ?? [];
    $userId = $report['usuario_id'];
    
    // Obtener rol del usuario para filtrar datos
    $userSql = "SELECT rol FROM usuarios WHERE id = ?";
    $user = $db->queryOne($userSql, [$userId]);
    if (!$user) {
        throw new Exception('Usuario del reporte no encontrado');
    }
    $userRole = $user['rol'];
    
    $year = $parametros['year'] ?? date('Y');
    $filename = "Reporte_{$report['tipo_reporte']}_{$report['id']}_" . date('Y-m-d_His') . ".{$report['formato']}";
    $outputPath = __DIR__ . '/uploads/reportes/' . $filename;

    // Crear directorio si no existe
    if (!is_dir(__DIR__ . '/uploads/reportes')) {
        mkdir(__DIR__ . '/uploads/reportes', 0755, true);
    }

    // Generar datos según tipo de reporte
    $data = [];
    $headers = [];
    $title = '';

    switch ($report['tipo_reporte']) {
        case 'general':
            $filters = ['anio' => $year];
            if (!empty($parametros['mes'])) $filters['mes'] = $parametros['mes'];
            if (!empty($parametros['estado'])) $filters['estado'] = $parametros['estado'];
            if (!empty($parametros['establecimiento_id'])) $filters['establecimiento_id'] = $parametros['establecimiento_id'];
            if ($userRole === ROL_REGISTRADOR) $filters['usuario_registro_id'] = $userId;
            
            $observations = $obsModel->getWithFilters($filters);
            $data = $exporter->prepareObservationsData($observations);
            $headers = $exporter->getObservationsHeaders();
            $title = "Reporte General - Año {$year}";
            break;
            
        case 'detallado':
            // Solo PDF
            $filters = ['anio' => $year];
            $data = $obsModel->reporteDetalladoPDF($filters, $userId, $userRole);
            break;
            
        case 'errores_establecimiento':
            $data = $obsModel->reporteErroresPorEstablecimiento($year, $userId, $userRole);
            $title = "Errores por Establecimiento - Año {$year}";
            break;

        case 'fuera_plazo_establecimiento':
            $data = $obsModel->reporteFueraPlazoPorEstablecimiento($year, $userId, $userRole);
            $title = "Fuera de Plazo por Establecimiento - Año {$year}";
            break;

        case 'validador_establecimiento':
            $data = $obsModel->reporteValidadorPorEstablecimiento($year, $userId, $userRole);
            $title = "Uso de Validador por Establecimiento - Año {$year}";
            break;
            
        default:
            throw new Exception("Tipo de reporte no reconocido: {$report['tipo_reporte']}");
    }

    if (empty($data)) {
        throw new Exception("No se encontraron datos para generar el reporte.");
    }

    // Exportar archivo
    if ($report['formato'] === 'xlsx' || $report['formato'] === 'excel') {
        if ($report['tipo_reporte'] === 'detallado') {
            throw new Exception("El reporte detallado solo puede ser PDF.");
        }
        if (!empty($headers)) {
            $exporter->exportToExcel($data, $outputPath, $headers, false);
        } else {
            $exporter->exportErroresExcel($data, $outputPath, $report['tipo_reporte'], false);
        }
    } elseif ($report['formato'] === 'pdf') {
        if ($report['tipo_reporte'] === 'detallado') {
            $pdfFilters = ['anio' => $year];
            $exporter->exportDetalladoPDF($data, $outputPath, $pdfFilters, 'F');
        } else {
            if (empty($headers)) {
                $headers = !empty($data[0]) ? array_keys($data[0]) : [];
            }
            $exporter->exportToPDF($data, $outputPath, $headers, $title, 'F');
        }
    } else {
        throw new Exception("Formato no soportado: {$report['formato']}");
    }

    // Marcar como listo
    $queue->markReady($report['id'], '/uploads/reportes/' . $filename);
    echo "Reporte ID: {$report['id']} generado exitosamente.\n";

} catch (Exception $e) {
    $queue->markError($report['id'], $e->getMessage());
    echo "Error procesando reporte ID: {$report['id']} - " . $e->getMessage() . "\n";
    exit(1);
}
