<?php
/**
 * API de Supervisión - Fase 6
 * Operaciones: get_filtered, get_detail, approve, cancel, delete, update_status
 * Solo accesible para rol Supervisor (403 para Registrador)
 * Soft delete: mover a observaciones_eliminadas (no DELETE físico)
 * Operaciones masivas no transaccionales (resumen por ID: procesados/fallos)
 */

error_reporting(E_ERROR | E_PARSE);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/HistorialEstado.php';

header('Content-Type: application/json; charset=utf-8');

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

function verificarSupervisor()
{
    if (!isset($_SESSION['usuario_id']) || $_SESSION['autenticado'] !== true) {
        responder(false, null, 'No autorizado', 401);
    }
    if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
        responder(false, null, 'Acceso denegado. Se requiere rol de supervisor', 403);
    }
}

function obtenerBodyJSON()
{
    $entrada = file_get_contents('php://input');
    $cuerpo = json_decode($entrada, true);
    if ($cuerpo === null && json_last_error() !== JSON_ERROR_NONE) {
        responder(false, null, 'Cuerpo de solicitud inválido', 400);
    }
    return $cuerpo ?? [];
}

verificarSupervisor();

$usuarioId = $_SESSION['usuario_id'];
$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? '';

try {
    $db = Database::obtenerInstancia();

    switch ($accion) {
        case 'get_filtered':
            $anio = $_GET['anio'] ?? date('Y');
            $pagina = max(1, (int) ($_GET['pagina'] ?? 1));
            $porPagina = 50;
            $offset = ($pagina - 1) * $porPagina;

            $sql = "SELECT o.*, 
                            e.nombre as establecimiento_nombre,
                            e.nombre_corto,
                            e.codigo_establecimiento,
                            c.nombre as comuna_nombre,
                            c.id as comuna_id,
                            u.nombre_completo as usuario_registro_nombre
                    FROM observaciones o
                    INNER JOIN establecimientos e ON o.establecimiento_id = e.id
                    INNER JOIN comunas c ON e.comuna_id = c.id
                    INNER JOIN usuarios u ON o.usuario_registro_id = u.id
                    WHERE o.anio = ?";
            $params = [$anio];

            if (!empty($_GET['mes'])) {
                $sql .= " AND o.mes = ?";
                $params[] = $_GET['mes'];
            }
            if (!empty($_GET['estado'])) {
                $sql .= " AND o.estado_actual = ?";
                $params[] = $_GET['estado'];
            }
            if (!empty($_GET['establecimiento_id'])) {
                $sql .= " AND o.establecimiento_id = ?";
                $params[] = $_GET['establecimiento_id'];
            }
            if (!empty($_GET['comuna_id'])) {
                $sql .= " AND c.id = ?";
                $params[] = $_GET['comuna_id'];
            }
            if (!empty($_GET['usuario_registro_id'])) {
                $sql .= " AND o.usuario_registro_id = ?";
                $params[] = $_GET['usuario_registro_id'];
            }
            if (!empty($_GET['tipo_error'])) {
                $sql .= " AND o.tipo_error = ?";
                $params[] = $_GET['tipo_error'];
            }
            if (!empty($_GET['busqueda'])) {
                $termino = '%' . $_GET['busqueda'] . '%';
                $sql .= " AND (o.detalle_observacion LIKE ? OR e.nombre LIKE ? OR e.nombre_corto LIKE ?)";
                $params[] = $termino;
                $params[] = $termino;
                $params[] = $termino;
            }

            $sqlConteo = preg_replace('/SELECT.*?FROM/s', 'SELECT COUNT(*) as total FROM', $sql);
            $sqlConteo = preg_replace('/ORDER BY.*$/', '', $sqlConteo);
            $resultadoConteo = $db->consultarUno($sqlConteo, $params);
            $total = (int) ($resultadoConteo['total'] ?? 0);
            $totalPaginas = max(1, ceil($total / $porPagina));

            $sql .= " ORDER BY o.fecha_creacion DESC LIMIT ? OFFSET ?";
            $params[] = $porPagina;
            $params[] = $offset;

            $datos = $db->consultar($sql, $params);

            responder(true, [
                'datos' => $datos,
                'total' => $total,
                'pagina' => $pagina,
                'porPagina' => $porPagina,
                'totalPaginas' => $totalPaginas
            ]);
            break;

        case 'get_detail':
            $id = $_GET['id'] ?? null;
            if (!$id) {
                responder(false, null, 'ID de observación requerido', 400);
            }

            $sql = "SELECT o.*, 
                            e.nombre as establecimiento_nombre,
                            e.nombre_corto,
                            e.codigo_establecimiento,
                            c.nombre as comuna_nombre,
                            c.id as comuna_id,
                            u.nombre_completo as usuario_registro_nombre
                    FROM observaciones o
                    INNER JOIN establecimientos e ON o.establecimiento_id = e.id
                    INNER JOIN comunas c ON e.comuna_id = c.id
                    INNER JOIN usuarios u ON o.usuario_registro_id = u.id
                    WHERE o.id = ?";
            $observacion = $db->consultarUno($sql, [$id]);

            if (!$observacion) {
                responder(false, null, 'Observación no encontrada', 404);
            }

            $historialModel = new HistorialEstado();
            $historial = $historialModel->obtenerPorObservacion($id);

            responder(true, [
                'observacion' => $observacion,
                'historial' => $historial
            ]);
            break;

        case 'approve':
            CSRF::validateRequest();
            $cuerpo = obtenerBodyJSON();

            if (empty($cuerpo['ids']) || !is_array($cuerpo['ids'])) {
                responder(false, null, 'IDs de observación requeridos', 400);
            }

            $ids = array_map('intval', $cuerpo['ids']);
            $comentario = $cuerpo['comentario'] ?? 'Observación aprobada por supervisor';
            $clasificacion = $cuerpo['clasificacion'] ?? null;
            $detalleError = $cuerpo['detalle_error'] ?? null;
            $estadoResultante = $cuerpo['estado_resultante'] ?? null;

            $procesados = 0;
            $fallos = [];

            foreach ($ids as $id) {
                try {
                    $obs = $db->consultarUno("SELECT * FROM observaciones WHERE id = ?", [$id]);
                    if (!$obs) {
                        $fallos[] = ['id' => $id, 'motivo' => 'Observación no encontrada'];
                        continue;
                    }

                    $estadoAnterior = $obs['estado_actual'];
                    $nuevoEstado = ESTADO_APROBADO;

                    $campos = ["estado_actual = ?", "fecha_actualizacion = NOW()"];
                    $params = [$nuevoEstado, $id];

                    if ($clasificacion !== null && $clasificacion !== '') {
                        $campos[] = "clasificacion = ?";
                        $params[] = $clasificacion;
                    }
                    if ($detalleError !== null) {
                        $campos[] = "detalle_error = ?";
                        $params[] = $detalleError;
                    }

                    $sql = "UPDATE observaciones SET " . implode(', ', $campos) . " WHERE id = ?";
                    $db->ejecutar($sql, $params);

                    $histModel = new HistorialEstado();
                    $histComentario = $comentario;
                    if ($estadoResultante) {
                        $histComentario .= " | Resultado: " . $estadoResultante;
                    }
                    $histModel->registrar($id, $usuarioId, $estadoAnterior, $nuevoEstado, $histComentario);

                    $procesados++;
                } catch (Exception $e) {
                    $fallos[] = ['id' => $id, 'motivo' => $e->getMessage()];
                }
            }

            responder(true, [
                'procesados' => $procesados,
                'fallos' => $fallos,
                'total' => count($ids)
            ]);
            break;

        case 'cancel':
            CSRF::validateRequest();
            $cuerpo = obtenerBodyJSON();

            if (empty($cuerpo['ids']) || !is_array($cuerpo['ids'])) {
                responder(false, null, 'IDs de observación requeridos', 400);
            }

            $ids = array_map('intval', $cuerpo['ids']);
            $comentario = $cuerpo['comentario'] ?? 'Observación cancelada por supervisor';

            $procesados = 0;
            $fallos = [];

            foreach ($ids as $id) {
                try {
                    $obs = $db->consultarUno("SELECT * FROM observaciones WHERE id = ?", [$id]);
                    if (!$obs) {
                        $fallos[] = ['id' => $id, 'motivo' => 'Observación no encontrada'];
                        continue;
                    }

                    $estadoAnterior = $obs['estado_actual'];
                    $nuevoEstado = ESTADO_RECHAZADO;

                    $db->ejecutar(
                        "UPDATE observaciones SET estado_actual = ?, fecha_actualizacion = NOW() WHERE id = ?",
                        [$nuevoEstado, $id]
                    );

                    $histModel = new HistorialEstado();
                    $histModel->registrar($id, $usuarioId, $estadoAnterior, $nuevoEstado, $comentario);

                    $procesados++;
                } catch (Exception $e) {
                    $fallos[] = ['id' => $id, 'motivo' => $e->getMessage()];
                }
            }

            responder(true, [
                'procesados' => $procesados,
                'fallos' => $fallos,
                'total' => count($ids)
            ]);
            break;

        case 'delete':
            CSRF::validateRequest();
            $cuerpo = obtenerBodyJSON();

            if (empty($cuerpo['ids']) || !is_array($cuerpo['ids'])) {
                responder(false, null, 'IDs de observación requeridos', 400);
            }

            $ids = array_map('intval', $cuerpo['ids']);
            $motivo = $cuerpo['motivo'] ?? 'Eliminado por supervisor';

            $procesados = 0;
            $fallos = [];

            foreach ($ids as $id) {
                try {
                    $obs = $db->consultarUno("SELECT * FROM observaciones WHERE id = ?", [$id]);
                    if (!$obs) {
                        $fallos[] = ['id' => $id, 'motivo' => 'Observación no encontrada'];
                        continue;
                    }

                    $sqlInsert = "INSERT INTO observaciones_eliminadas 
                                  (observacion_id, anio, mes, establecimiento_id, establecimiento_nombre, 
                                   establecimiento_nombre_corto, comuna, codigo_serie, codigo_hoja, 
                                   tipo_error, detalle_observacion, plazo_entrega, usa_validador, 
                                   estado_actual, clasificacion, usuario_registro_id, nombre_registro, 
                                   usuario_supervisor_id, motivo_eliminacion, fecha_eliminacion, 
                                   fecha_registro_original, fecha_revision)
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)";

                    $db->ejecutar($sqlInsert, [
                        $id,
                        $obs['anio'],
                        $obs['mes'],
                        $obs['establecimiento_id'],
                        $obs['establecimiento_nombre'] ?? '',
                        $obs['nombre_corto'] ?? null,
                        $obs['comuna_nombre'] ?? '',
                        $obs['codigo_serie'] ?? '',
                        $obs['codigo_hoja'] ?? '',
                        $obs['tipo_error'] ?? '',
                        $obs['detalle_observacion'] ?? '',
                        $obs['plazo_entrega'] ?? 'dentro_plazo',
                        $obs['usa_validador'] ?? 'no',
                        $obs['estado_actual'] ?? '',
                        $obs['clasificacion'],
                        $obs['usuario_registro_id'],
                        $obs['usuario_registro_nombre'] ?? '',
                        $usuarioId,
                        $motivo,
                        $obs['fecha_creacion'],
                        $obs['fecha_actualizacion']
                    ]);

                    $db->ejecutar("DELETE FROM historial_estados WHERE observacion_id = ?", [$id]);
                    $db->ejecutar("DELETE FROM observaciones WHERE id = ?", [$id]);

                    $procesados++;
                } catch (Exception $e) {
                    $fallos[] = ['id' => $id, 'motivo' => $e->getMessage()];
                }
            }

            responder(true, [
                'procesados' => $procesados,
                'fallos' => $fallos,
                'total' => count($ids)
            ]);
            break;

        case 'update_status':
            CSRF::validateRequest();
            $cuerpo = obtenerBodyJSON();

            if (empty($cuerpo['ids']) || !is_array($cuerpo['ids'])) {
                responder(false, null, 'IDs de observación requeridos', 400);
            }
            if (empty($cuerpo['estado'])) {
                responder(false, null, 'Estado requerido', 400);
            }

            $estadosValidos = [ESTADO_PENDIENTE, ESTADO_APROBADO, ESTADO_RECHAZADO, ESTADO_ERROR, ESTADO_JUSTIFICADO];
            if (!in_array($cuerpo['estado'], $estadosValidos)) {
                responder(false, null, 'Estado no válido', 400);
            }

            $ids = array_map('intval', $cuerpo['ids']);
            $nuevoEstado = $cuerpo['estado'];
            $comentario = $cuerpo['comentario'] ?? "Cambio de estado a: {$nuevoEstado}";
            $clasificacion = $cuerpo['clasificacion'] ?? null;
            $detalleError = $cuerpo['detalle_error'] ?? null;

            $procesados = 0;
            $fallos = [];

            foreach ($ids as $id) {
                try {
                    $obs = $db->consultarUno("SELECT * FROM observaciones WHERE id = ?", [$id]);
                    if (!$obs) {
                        $fallos[] = ['id' => $id, 'motivo' => 'Observación no encontrada'];
                        continue;
                    }

                    $estadoAnterior = $obs['estado_actual'];

                    $campos = ["estado_actual = ?", "fecha_actualizacion = NOW()"];
                    $params = [$nuevoEstado, $id];

                    if ($clasificacion !== null && $clasificacion !== '') {
                        $campos[] = "clasificacion = ?";
                        $params[] = $clasificacion;
                    }
                    if ($detalleError !== null) {
                        $campos[] = "detalle_error = ?";
                        $params[] = $detalleError;
                    }

                    $sql = "UPDATE observaciones SET " . implode(', ', $campos) . " WHERE id = ?";
                    $db->ejecutar($sql, $params);

                    $histModel = new HistorialEstado();
                    $histModel->registrar($id, $usuarioId, $estadoAnterior, $nuevoEstado, $comentario);

                    $procesados++;
                } catch (Exception $e) {
                    $fallos[] = ['id' => $id, 'motivo' => $e->getMessage()];
                }
            }

            responder(true, [
                'procesados' => $procesados,
                'fallos' => $fallos,
                'total' => count($ids)
            ]);
            break;

        default:
            responder(false, null, 'Acción no válida. Acciones disponibles: get_filtered, get_detail, approve, cancel, delete, update_status', 400);
    }
} catch (Exception $e) {
    error_log("Error en API supervisión: " . $e->getMessage());
    responder(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
