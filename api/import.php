<?php
/**
 * API de Importación de Observaciones
 * Procesa archivos CSV y XLSX para carga masiva
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../models/Observation.php';
require_once __DIR__ . '/../models/Location.php';
require_once __DIR__ . '/../includes/csrf.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Verificar autenticación
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['rol'];

// Solo supervisores y registradores pueden importar
if ($userRole !== ROL_SUPERVISOR && $userRole !== ROL_REGISTRADOR) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tiene permisos para importar']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verificar que se subió un archivo - soportar ambos nombres
        $fileKey = isset($_FILES['csv_file']) ? 'csv_file' : (isset($_FILES['import_file']) ? 'import_file' : null);

        if (!$fileKey || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('No se recibió un archivo válido');
        }

        $file = $_FILES[$fileKey];
        $year = $_POST['year'] ?? date('Y');
        $isPreview = isset($_POST['preview']) && $_POST['preview'] == '1';
        $isConfirm = isset($_POST['confirm']) && $_POST['confirm'] == '1';

        // Detectar tipo de archivo
        $filename = $file['name'];
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $rows = [];

        if ($extension === 'xlsx' || $extension === 'xls') {
            // Leer archivo Excel
            $spreadsheet = IOFactory::load($file['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();

            if (empty($data)) {
                throw new Exception('Archivo Excel vacío o formato inválido');
            }

            // Primera fila como encabezados
            $header = array_map('trim', $data[0]);

            // Resto como datos
            for ($i = 1; $i < count($data); $i++) {
                if (!empty($data[$i][0])) { // Solo filas con datos
                    $row = [];
                    foreach ($header as $index => $colName) {
                        $row[$colName] = isset($data[$i][$index]) ? trim($data[$i][$index]) : '';
                    }
                    $rows[] = $row;
                }
            }
        } else {
            // Leer archivo CSV
            $handle = fopen($file['tmp_name'], 'r');

            if ($handle === false) {
                throw new Exception('No se pudo leer el archivo');
            }

            // Leer encabezado
            $header = fgetcsv($handle, 1000, ',');

            if (!$header) {
                throw new Exception('Archivo CSV vacío o formato inválido');
            }

            // Leer datos
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($data) > 0 && $data[0] !== '') {
                    $rows[] = array_combine($header, $data);
                }
            }
            fclose($handle);
        }

        // Validar y procesar datos
        $obsModel = new Observation();
        $locModel = new Location();

        $valid = [];
        $errors = [];
        $establecimientos = $locModel->getAllEstablecimientos();

        // Crear mapa de establecimientos por código y por nombre
        $estMapByCodigo = [];
        $estMapByNombre = [];
        foreach ($establecimientos as $est) {
            // Mapeo por código de establecimiento (prioritario)
            if (!empty($est['codigo_establecimiento'])) {
                $estMapByCodigo[$est['codigo_establecimiento']] = $est;
            }
            // Mapeo por nombre (fallback)
            $estMapByNombre[strtolower(trim($est['nombre']))] = $est;
            $estMapByNombre[strtolower(trim($est['nombre_corto']))] = $est;
        }

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // +2 porque empieza en 1 y hay header
            $rowErrors = [];

            // Validar campos requeridos (solo los mínimos obligatorios)
            // Se requiere codigo_establecimiento O establecimiento (nombre)
            $required = [
                'mes',
                'tipo'           // Antes: tipo_error
            ];

            // Mapear nombres antiguos si existen (compatibilidad)
            if (!isset($row['tipo']) && isset($row['tipo_error'])) {
                $row['tipo'] = $row['tipo_error'];
            }
            if (!isset($row['serie']) && isset($row['codigo_serie'])) {
                $row['serie'] = $row['codigo_serie'];
            }
            if (!isset($row['rem']) && isset($row['codigo_hoja'])) {
                $row['rem'] = $row['codigo_hoja'];
            }

            // Establecer valores por defecto para campos opcionales
            $row['serie'] = $row['serie'] ?? '';
            $row['rem'] = $row['rem'] ?? '';
            $row['detalle_observacion'] = $row['detalle_observacion'] ?? '';
            $row['plazo_entrega'] = $row['plazo_entrega'] ?? '';
            $row['usa_validador'] = !empty($row['usa_validador']) ? $row['usa_validador'] : 'NO';

            foreach ($required as $field) {
                if (empty($row[$field])) {
                    $rowErrors[] = "Campo '{$field}' es requerido";
                }
            }

            // Buscar establecimiento: PRIORIDAD por código, luego por nombre
            $estId = null;
            $estNombreOficial = '';
            $codigoEst = $row['codigo_establecimiento'] ?? $row['codigo_est'] ?? $row['cod_establecimiento'] ?? '';
            $nombreEst = $row['establecimiento'] ?? $row['nombre_establecimiento'] ?? '';

            // 1. Buscar por código de establecimiento (prioritario)
            if (!empty($codigoEst)) {
                $codigoEst = trim($codigoEst);
                if (isset($estMapByCodigo[$codigoEst])) {
                    $estId = $estMapByCodigo[$codigoEst]['id'];
                    $estNombreOficial = $estMapByCodigo[$codigoEst]['nombre'];
                } else {
                    $rowErrors[] = "Código de establecimiento '{$codigoEst}' no encontrado";
                }
            }
            // 2. Si no hay código, buscar por nombre (fallback)
            elseif (!empty($nombreEst)) {
                $nombreBuscar = strtolower(trim($nombreEst));
                if (isset($estMapByNombre[$nombreBuscar])) {
                    $estId = $estMapByNombre[$nombreBuscar]['id'];
                    $estNombreOficial = $estMapByNombre[$nombreBuscar]['nombre'];
                } else {
                    $rowErrors[] = "Establecimiento '{$nombreEst}' no encontrado. Usa el código de establecimiento para mayor precisión.";
                }
            }
            // 3. Si no hay ni código ni nombre, error
            else {
                $rowErrors[] = "Debe especificar 'codigo_establecimiento' o 'establecimiento'";
            }

            if (count($rowErrors) > 0) {
                $errors[] = [
                    'row' => $rowNum,
                    'message' => implode(', ', $rowErrors)
                ];
            } else {
                $valid[] = [
                    'mes' => $row['mes'],
                    'establecimiento_id' => $estId,
                    'establecimiento_nombre' => $estNombreOficial, // Nombre oficial de la BD
                    'codigo_serie' => $row['serie'],           // Mapeo: serie -> codigo_serie (BD)
                    'codigo_hoja' => $row['rem'],               // Mapeo: rem -> codigo_hoja (BD)
                    'tipo_error' => $row['tipo'],               // Mapeo: tipo -> tipo_error (BD)
                    'detalle_observacion' => $row['detalle_observacion'],
                    'plazo_entrega' => $row['plazo_entrega'],
                    'usa_validador' => $row['usa_validador'],
                    // Campos opcionales
                    'respuesta_establecimiento' => $row['respuesta_establecimiento'] ?? '',
                    'clasificacion' => $row['clasificacion'] ?? '',
                    'detalle_error' => $row['detalle_error'] ?? ''
                ];
            }
        }

        // Si es preview, retornar resumen
        if ($isPreview) {
            echo json_encode([
                'success' => true,
                'data' => [
                    'total' => count($rows),
                    'valid' => count($valid),
                    'errors' => $errors,
                    'preview' => $valid
                ]
            ]);
            exit;
        }

        // Si es confirmación, insertar en BD
        if ($isConfirm) {
            $imported = 0;

            foreach ($valid as $obs) {
                $data = [
                    'anio' => $year,
                    'mes' => $obs['mes'],
                    'establecimiento_id' => $obs['establecimiento_id'],
                    'codigo_serie' => $obs['codigo_serie'],
                    'codigo_hoja' => $obs['codigo_hoja'],
                    'tipo_error' => $obs['tipo_error'],
                    'detalle_observacion' => $obs['detalle_observacion'],
                    'plazo_entrega' => $obs['plazo_entrega'],
                    'usa_validador' => $obs['usa_validador'],
                    'usuario_registro_id' => $userId,
                    // Campos opcionales
                    'respuesta_establecimiento' => $obs['respuesta_establecimiento'] ?? '',
                    'clasificacion' => $obs['clasificacion'] ?? '',
                    'detalle_error' => $obs['detalle_error'] ?? ''
                ];

                if ($obsModel->create($data)) {
                    $imported++;
                }
            }

            echo json_encode([
                'success' => true,
                'imported' => $imported,
                'message' => "Se importaron {$imported} observaciones correctamente"
            ]);
            exit;
        }

    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    }

} catch (Exception $e) {
    error_log("Error en importación: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
