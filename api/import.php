<?php
/**
 * API de Importación de Observaciones desde Excel
 * Acciones: preview (vista previa), confirm (confirmar importación)
 * Solo acceso para rol Registrador
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../includes/csrf.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

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

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || $_SESSION['autenticado'] !== true) {
    responder(false, null, 'No autorizado', 401);
}

$usuarioId = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'];

// Solo registradores pueden importar
if ($rol !== ROL_REGISTRADOR) {
    responder(false, null, 'Solo los registradores pueden importar archivos', 403);
}

$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? '';

try {
    // GET: descargar información de columnas esperadas
    if ($metodo === 'GET' && $accion === 'columnas') {
        responder(true, [
            'columnas' => [
                'codigo_establecimiento' => 'Código DEIS del establecimiento (obligatorio)',
                'mes' => 'Número (1-12) o nombre del mes en español (obligatorio)',
                'codigo_serie' => 'Serie REM (ej: SERIE A, SERIE BM)',
                'codigo_hoja' => 'Hoja REM (ej: A01, BM18)',
                'tipo_error' => 'Tipo: S/OBSERVACION, ERROR, REVISAR, F/PLAZO (obligatorio)',
                'detalle_observacion' => 'Descripción de la observación',
                'plazo_entrega' => 'dentro_plazo o fuera_plazo'
            ],
            'anio_seleccionado' => $_SESSION['anio_trabajo'] ?? date('Y')
        ]);
    }

    // POST: preview o confirm
    if ($metodo === 'POST') {
        CSRF::validateRequest();

        if (!in_array($accion, ['preview', 'confirm'])) {
            responder(false, null, 'Acción no válida. Use preview o confirm', 400);
        }

        // Verificar archivo subido
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            responder(false, null, 'No se recibió un archivo válido', 400);
        }

        $archivo = $_FILES['archivo'];
        $anio = $_POST['anio'] ?? ($_SESSION['anio_trabajo'] ?? date('Y'));
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, ['xlsx', 'xls'])) {
            responder(false, null, 'Formato no válido. Solo se permiten archivos Excel (.xlsx, .xls)', 400);
        }

        // Leer archivo con PhpSpreadsheet (auto-detecta codificación)
        $spreadsheet = IOFactory::load($archivo['tmp_name']);
        $hoja = $spreadsheet->getActiveSheet();
        $datos = $hoja->toArray();

        if (empty($datos)) {
            responder(false, null, 'Archivo Excel vacío o formato inválido', 400);
        }

        // Primera fila como encabezados
        $encabezados = array_map(function($h) {
            return strtolower(trim(str_replace([' ', '-'], '_', $h)));
        }, $datos[0]);

        // Mapear encabezados a nombres estándar
        $mapeoEncabezados = [
            'codigo_establecimiento' => ['codigo_establecimiento', 'cod_establecimiento', 'codigo_est', 'deis'],
            'establecimiento' => ['establecimiento', 'nombre_establecimiento', 'nombre'],
            'mes' => ['mes', 'month', 'mes_rem'],
            'codigo_serie' => ['codigo_serie', 'serie', 'cod_serie'],
            'codigo_hoja' => ['codigo_hoja', 'hoja', 'rem', 'cod_hoja'],
            'tipo_error' => ['tipo_error', 'tipo', 'tipo_observacion'],
            'detalle_observacion' => ['detalle_observacion', 'detalle', 'observacion', 'descripcion'],
            'plazo_entrega' => ['plazo_entrega', 'plazo', 'entrega']
        ];

        // Crear mapeo inverso: índice de columna -> nombre estándar
        $mapeoColumnas = [];
        foreach ($encabezados as $indice => $encabezado) {
            foreach ($mapeoEncabezados as $nombreEstandar => $alias) {
                if (in_array($encabezado, $alias, true)) {
                    $mapeoColumnas[$indice] = $nombreEstandar;
                    break;
                }
            }
        }

        // Parsear filas de datos
        $filas = [];
        for ($i = 1; $i < count($datos); $i++) {
            $fila = [];
            foreach ($mapeoColumnas as $indice => $nombre) {
                $valor = isset($datos[$i][$indice]) ? trim($datos[$i][$indice]) : '';
                $fila[$nombre] = $valor;
            }
            // Solo incluir filas con al menos un dato
            if (!empty(array_filter($fila))) {
                $fila['_fila'] = $i + 1; // Número de fila en Excel (1-indexed)
                $filas[] = $fila;
            }
        }

        if (empty($filas)) {
            responder(false, null, 'No se encontraron filas de datos en el archivo', 400);
        }

        // Obtener todos los establecimientos para mapeo
        $db = Database::getInstance();
        $establecimientos = $db->query("SELECT id, codigo_establecimiento, nombre, nombre_corto FROM establecimientos");

        $mapaPorCodigo = [];
        $mapaPorNombre = [];
        foreach ($establecimientos as $est) {
            if (!empty($est['codigo_establecimiento'])) {
                $mapaPorCodigo[trim($est['codigo_establecimiento'])] = $est;
            }
            $nombreLower = strtolower(trim($est['nombre']));
            $nombreCortoLower = strtolower(trim($est['nombre_corto']));
            $mapaPorNombre[$nombreLower] = $est;
            if ($nombreCortoLower) {
                $mapaPorNombre[$nombreCortoLower] = $est;
            }
        }

        // Mapa de meses en español a número
        $mesesMapa = [
            'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4,
            'mayo' => 5, 'junio' => 6, 'julio' => 7, 'agosto' => 8,
            'septiembre' => 9, 'octubre' => 10, 'noviembre' => 11, 'diciembre' => 12,
            '1' => 1, '2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6,
            '7' => 7, '8' => 8, '9' => 9, '10' => 10, '11' => 11, '12' => 12
        ];

        // Validar cada fila
        $validas = [];
        $errores = [];

        foreach ($filas as $fila) {
            $numFila = $fila['_fila'];
            $erroresFila = [];

            // Validar mes (obligatorio)
            $mesRaw = $fila['mes'] ?? '';
            if (empty($mesRaw)) {
                $erroresFila[] = 'Campo "mes" es requerido';
            } else {
                $mesLower = strtolower(trim($mesRaw));
                // Si es numérico, validar rango
                if (is_numeric($mesRaw)) {
                    $mesNum = (int) $mesRaw;
                    if ($mesNum < 1 || $mesNum > 12) {
                        $erroresFila[] = "Mes '{$mesRaw}' fuera de rango (1-12)";
                    }
                } elseif (isset($mesesMapa[$mesLower])) {
                    $mesNum = $mesesMapa[$mesLower];
                } else {
                    $erroresFila[] = "Mes '{$mesRaw}' no reconocido. Use número (1-12) o nombre en español";
                }
            }

            // Validar tipo_error (obligatorio)
            $tipoError = $fila['tipo_error'] ?? '';
            if (empty($tipoError)) {
                $erroresFila[] = 'Campo "tipo_error" es requerido';
            }

            // Buscar establecimiento: prioridad por código DEIS, fallback por nombre
            $establecimientoId = null;
            $establecimientoNombre = '';
            $codigoEst = $fila['codigo_establecimiento'] ?? '';
            $nombreEst = $fila['establecimiento'] ?? '';

            if (!empty($codigoEst)) {
                $codigoEst = trim($codigoEst);
                if (isset($mapaPorCodigo[$codigoEst])) {
                    $establecimientoId = $mapaPorCodigo[$codigoEst]['id'];
                    $establecimientoNombre = $mapaPorCodigo[$codigoEst]['nombre'];
                } else {
                    $erroresFila[] = "Código de establecimiento '{$codigoEst}' no encontrado";
                }
            } elseif (!empty($nombreEst)) {
                $nombreBuscar = strtolower(trim($nombreEst));
                if (isset($mapaPorNombre[$nombreBuscar])) {
                    $establecimientoId = $mapaPorNombre[$nombreBuscar]['id'];
                    $establecimientoNombre = $mapaPorNombre[$nombreBuscar]['nombre'];
                } else {
                    $erroresFila[] = "Establecimiento '{$nombreEst}' no encontrado. Use el código DEIS para mayor precisión";
                }
            } else {
                $erroresFila[] = 'Debe especificar codigo_establecimiento o establecimiento';
            }

            if (!empty($erroresFila)) {
                $errores[] = [
                    'fila' => $numFila,
                    'errores' => $erroresFila,
                    'datos' => $fila
                ];
            } else {
                // Construir fila válida
                $validas[] = [
                    'fila' => $numFila,
                    'anio' => (int) $anio,
                    'mes' => isset($mesNum) ? $mesNum : null,
                    'establecimiento_id' => $establecimientoId,
                    'establecimiento_nombre' => $establecimientoNombre,
                    'codigo_serie' => $fila['codigo_serie'] ?? '',
                    'codigo_hoja' => $fila['codigo_hoja'] ?? '',
                    'tipo_error' => $tipoError,
                    'detalle_observacion' => $fila['detalle_observacion'] ?? '',
                    'plazo_entrega' => $fila['plazo_entrega'] ?? ''
                ];
            }
        }

        // Detectar duplicados dentro del archivo (mismo establecimiento + mes + serie + hoja + tipo)
        $firmas = [];
        $duplicados = [];
        foreach ($validas as $idx => $fila) {
            $firma = "{$fila['establecimiento_id']}-{$fila['mes']}-{$fila['codigo_serie']}-{$fila['codigo_hoja']}-{$fila['tipo_error']}";
            if (isset($firmas[$firma])) {
                $duplicados[] = [
                    'fila' => $fila['fila'],
                    'mensaje' => "Duplicado interno: misma combinación establecimiento/mes/serie/hoja/tipo que fila {$firmas[$firma]}"
                ];
            } else {
                $firmas[$firma] = $fila['fila'];
            }
        }

        // Detectar duplicados contra la base de datos
        $duplicadosBD = [];
        foreach ($validas as $fila) {
            $sql = "SELECT COUNT(*) as total FROM observaciones 
                    WHERE establecimiento_id = ? AND mes = ? AND anio = ? 
                    AND COALESCE(codigo_serie, '') = ? AND COALESCE(codigo_hoja, '') = ? 
                    AND tipo_error = ?";
            $resultado = $db->queryOne($sql, [
                $fila['establecimiento_id'],
                $fila['mes'],
                $fila['anio'],
                $fila['codigo_serie'],
                $fila['codigo_hoja'],
                $fila['tipo_error']
            ]);
            if ($resultado && (int) $resultado['total'] > 0) {
                $duplicadosBD[] = [
                    'fila' => $fila['fila'],
                    'establecimiento' => $fila['establecimiento_nombre'],
                    'mes' => $fila['mes'],
                    'mensaje' => "Ya existe en BD: {$fila['establecimiento_nombre']} - Mes {$fila['mes']}"
                ];
            }
        }

        // Si es preview, retornar resumen sin guardar
        if ($accion === 'preview') {
            responder(true, [
                'total_filas' => count($filas),
                'validas' => count($validas),
                'con_errores' => count($errores),
                'duplicados_internos' => count($duplicados),
                'duplicados_bd' => count($duplicadosBD),
                'anio' => (int) $anio,
                'preview' => $validas,
                'errores' => $errores,
                'duplicados' => $duplicados,
                'duplicados_bd' => $duplicadosBD
            ]);
        }

        // Si es confirm, insertar solo filas válidas
        if ($accion === 'confirm') {
            $db->beginTransaction();
            try {
                $importadas = 0;
                $omitidas = 0;
                $omitirDuplicados = isset($_POST['omitir_duplicados']) && $_POST['omitir_duplicados'] === '1';

                foreach ($validas as $fila) {
                    // Verificar duplicado en BD si se solicita omitir
                    if ($omitirDuplicados) {
                        $sql = "SELECT COUNT(*) as total FROM observaciones 
                                WHERE establecimiento_id = ? AND mes = ? AND anio = ? 
                                AND COALESCE(codigo_serie, '') = ? AND COALESCE(codigo_hoja, '') = ? 
                                AND tipo_error = ?";
                        $resultado = $db->queryOne($sql, [
                            $fila['establecimiento_id'],
                            $fila['mes'],
                            $fila['anio'],
                            $fila['codigo_serie'],
                            $fila['codigo_hoja'],
                            $fila['tipo_error']
                        ]);
                        if ($resultado && (int) $resultado['total'] > 0) {
                            $omitidas++;
                            continue;
                        }
                    }

                    $sql = "INSERT INTO observaciones 
                            (usuario_registro_id, establecimiento_id, comuna_id, anio, mes, 
                             codigo_serie, codigo_hoja, tipo_error, detalle_observacion, 
                             plazo_entrega, anio_rem, mes_rem, estado_actual, clasificacion, 
                             fecha_creacion, fecha_actualizacion)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

                    // Obtener comuna del establecimiento
                    $comuna = $db->queryOne("SELECT comuna_id FROM establecimientos WHERE id = ?", [$fila['establecimiento_id']]);
                    $comunaId = $comuna ? (int) $comuna['comuna_id'] : null;

                    $db->execute($sql, [
                        $usuarioId,
                        $fila['establecimiento_id'],
                        $comunaId,
                        $fila['anio'],
                        $fila['mes'],
                        $fila['codigo_serie'] ?: null,
                        $fila['codigo_hoja'] ?: null,
                        $fila['tipo_error'],
                        $fila['detalle_observacion'],
                        $fila['plazo_entrega'] ?: null,
                        $fila['anio'],
                        $fila['mes'],
                        ESTADO_PENDIENTE,
                        null
                    ]);
                    $importadas++;
                }

                $db->commit();
                responder(true, [
                    'importadas' => $importadas,
                    'omitidas' => $omitidas,
                    'mensaje' => "Se importaron {$importadas} observaciones" . ($omitidas > 0 ? ", se omitieron {$omitidas} duplicadas" : "")
                ]);
            } catch (Exception $e) {
                $db->rollback();
                responder(false, null, 'Error al importar: ' . $e->getMessage(), 500);
            }
        }
    }

    responder(false, null, 'Método no permitido', 405);

} catch (Exception $e) {
    error_log("Error en importación: " . $e->getMessage());
    responder(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
