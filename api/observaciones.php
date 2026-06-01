<?php
/**
 * API de Observaciones
 * Endpoints: listar, detalle, crear, actualizar, eliminar, historial, stats
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../models/Observacion.php';
require_once __DIR__ . '/../models/HistorialEstado.php';
require_once __DIR__ . '/../models/Establecimiento.php';

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

if (!isset($_SESSION['usuario_id']) || $_SESSION['autenticado'] !== true) {
    responder(false, null, 'No autorizado', 401);
}

$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? '';
$id = $_GET['id'] ?? null;
$usuarioId = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'];

try {
    $modeloObservacion = new Observacion();

    if ($metodo === 'GET') {
        switch ($accion) {
            case 'listar':
                $filtros = [
                    'anio' => $_GET['anio'] ?? date('Y'),
                    'pagina' => $_GET['pagina'] ?? 1,
                    'mes' => $_GET['mes'] ?? null,
                    'estado' => $_GET['estado'] ?? null,
                    'establecimiento_id' => $_GET['establecimiento_id'] ?? null,
                    'tipo_error' => $_GET['tipo_error'] ?? null,
                    'busqueda' => $_GET['busqueda'] ?? null
                ];
                $resultado = $modeloObservacion->listar($filtros, $usuarioId, $rol);
                responder(true, $resultado);
                break;

            case 'detalle':
                if (!$id) {
                    responder(false, null, 'ID de observación requerido', 400);
                }
                $observacion = $modeloObservacion->obtenerPorId($id, $usuarioId, $rol);
                if (!$observacion) {
                    responder(false, null, 'Observación no encontrada o sin permisos', 404);
                }
                responder(true, $observacion);
                break;

            case 'historial':
                if (!$id) {
                    responder(false, null, 'ID de observación requerido', 400);
                }
                $observacion = $modeloObservacion->obtenerPorId($id, $usuarioId, $rol);
                if (!$observacion) {
                    responder(false, null, 'Observación no encontrada o sin permisos', 404);
                }
                $historialModel = new HistorialEstado();
                $historial = $historialModel->obtenerPorObservacion($id);
                responder(true, $historial);
                break;

            case 'stats':
                $anio = $_GET['anio'] ?? date('Y');
                $stats = $modeloObservacion->estadisticas($anio, $usuarioId, $rol);
                responder(true, $stats);
                break;

            default:
                responder(false, null, 'Acción no válida', 400);
        }
    }

    elseif ($metodo === 'POST') {
        CSRF::validateRequest();

        if ($accion !== 'crear') {
            responder(false, null, 'Acción no válida', 400);
        }

        $entrada = file_get_contents('php://input');
        $cuerpo = json_decode($entrada, true);

        $camposRequeridos = ['mes', 'establecimiento_id', 'tipo_error'];
        foreach ($camposRequeridos as $campo) {
            if (!isset($cuerpo[$campo]) || trim($cuerpo[$campo]) === '') {
                responder(false, null, "El campo {$campo} es requerido", 400);
            }
        }

        if ($cuerpo['tipo_error'] !== 'S/OBSERVACION') {
            if (!isset($cuerpo['codigo_hoja']) || trim($cuerpo['codigo_hoja']) === '') {
                responder(false, null, 'El campo codigo_hoja es requerido para este tipo de error', 400);
            }
        }

        if (!isset($cuerpo['codigo_serie']) || trim($cuerpo['codigo_serie']) === '') {
            responder(false, null, 'El campo codigo_serie es requerido', 400);
        }

        $estModel = new Establecimiento();
        if (!$estModel->estaActivo($cuerpo['establecimiento_id'])) {
            responder(false, null, 'No se pueden registrar observaciones en un establecimiento inactivo', 400);
        }

        $datos = [
            'anio' => $cuerpo['anio'] ?? date('Y'),
            'mes' => $cuerpo['mes'],
            'establecimiento_id' => (int) $cuerpo['establecimiento_id'],
            'codigo_serie' => $cuerpo['codigo_serie'],
            'codigo_hoja' => $cuerpo['codigo_hoja'] ?? null,
            'tipo_error' => $cuerpo['tipo_error'],
            'detalle_observacion' => $cuerpo['detalle_observacion'] ?? '',
            'plazo_entrega' => $cuerpo['plazo_entrega'] ?? null,
            'anio_rem' => $cuerpo['anio_rem'] ?? ($cuerpo['anio'] ?? date('Y')),
            'mes_rem' => $cuerpo['mes_rem'] ?? $cuerpo['mes'],
            'clasificacion' => $cuerpo['clasificacion'] ?? null
        ];

        try {
            $idNuevo = $modeloObservacion->crear($datos, $usuarioId, $rol);
            responder(true, ['id' => $idNuevo], 'Observación creada exitosamente', 201);
        } catch (Exception $e) {
            $codigo = $e->getCode() ?: 400;
            responder(false, null, $e->getMessage(), $codigo);
        }
    }

    elseif ($metodo === 'PUT') {
        CSRF::validateRequest();

        if (!$id) {
            responder(false, null, 'ID de observación requerido', 400);
        }

        $entrada = file_get_contents('php://input');
        $cuerpo = json_decode($entrada, true);

        $fechaOriginal = $cuerpo['fecha_actualizacion'] ?? null;

        try {
            $modeloObservacion->actualizar($id, $cuerpo, $usuarioId, $rol, $fechaOriginal);
            responder(true, null, 'Observación actualizada exitosamente');
        } catch (Exception $e) {
            $codigo = $e->getCode() ?: 400;
            responder(false, null, $e->getMessage(), $codigo);
        }
    }

    elseif ($metodo === 'DELETE') {
        CSRF::validateRequest();

        if (!$id) {
            responder(false, null, 'ID de observación requerido', 400);
        }

        try {
            $modeloObservacion->eliminar($id, $usuarioId, $rol);
            responder(true, null, 'Observación eliminada exitosamente');
        } catch (Exception $e) {
            $codigo = $e->getCode() ?: 400;
            responder(false, null, $e->getMessage(), $codigo);
        }
    }

    else {
        responder(false, null, 'Método no permitido', 405);
    }

} catch (Exception $e) {
    error_log("Error en API observaciones: " . $e->getMessage());
    responder(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
