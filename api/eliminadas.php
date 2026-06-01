<?php
/**
 * API de Papelera de Eliminadas
 * Acciones: listar, estadisticas, restaurar, eliminar_permanente,
 *           restaurar_masivo, eliminar_permanente_masivo
 * Solo accesible para supervisores (403 para registradores)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../models/PapeleraEliminada.php';
require_once __DIR__ . '/../models/Location.php';

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

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    responder(false, null, 'Acceso denegado. Se requiere rol de supervisor', 403);
}

$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? '';
$usuarioId = $_SESSION['usuario_id'];

try {
    $modeloPapelera = new PapeleraEliminada();

    if ($metodo === 'GET') {
        switch ($accion) {
            case 'listar':
                $filtros = [
                    'anio' => $_GET['anio'] ?? date('Y'),
                    'pagina' => $_GET['pagina'] ?? 1,
                    'mes' => $_GET['mes'] ?? null,
                    'comuna_id' => $_GET['comuna_id'] ?? null,
                    'establecimiento_id' => $_GET['establecimiento_id'] ?? null,
                    'registrador_id' => $_GET['registrador_id'] ?? null,
                    'busqueda' => $_GET['busqueda'] ?? null
                ];
                $resultado = $modeloPapelera->listar($filtros);
                responder(true, $resultado);
                break;

            case 'estadisticas':
                $anio = $_GET['anio'] ?? null;
                $stats = $modeloPapelera->estadisticas($anio);
                responder(true, $stats);
                break;

            case 'detalle':
                $id = $_GET['id'] ?? null;
                if (!$id) {
                    responder(false, null, 'ID requerido', 400);
                }
                $eliminada = $modeloPapelera->obtenerPorId($id);
                if (!$eliminada) {
                    responder(false, null, 'Observación eliminada no encontrada', 404);
                }
                responder(true, $eliminada);
                break;

            default:
                responder(false, null, 'Acción no válida', 400);
        }
    }

    elseif ($metodo === 'POST') {
        CSRF::validateRequest();

        $entrada = file_get_contents('php://input');
        $cuerpo = json_decode($entrada, true);
        $accion = $cuerpo['accion'] ?? '';

        switch ($accion) {
            case 'restaurar':
                $id = $cuerpo['id'] ?? null;
                if (!$id) {
                    responder(false, null, 'ID de observación eliminada requerido', 400);
                }
                try {
                    $nuevoId = $modeloPapelera->restaurar($id, $usuarioId);
                    responder(true, ['nuevo_id' => $nuevoId], 'Observación restaurada exitosamente');
                } catch (Exception $e) {
                    responder(false, null, $e->getMessage(), $e->getCode() ?: 500);
                }
                break;

            case 'eliminar_permanente':
                $id = $cuerpo['id'] ?? null;
                if (!$id) {
                    responder(false, null, 'ID de observación eliminada requerido', 400);
                }
                try {
                    $modeloPapelera->eliminarPermanente($id, $usuarioId);
                    responder(true, null, 'Observación eliminada permanentemente');
                } catch (Exception $e) {
                    responder(false, null, $e->getMessage(), $e->getCode() ?: 500);
                }
                break;

            case 'restaurar_masivo':
                $ids = $cuerpo['ids'] ?? [];
                if (empty($ids) || !is_array($ids)) {
                    responder(false, null, 'Lista de IDs requerida', 400);
                }
                $resultados = $modeloPapelera->restaurarMasivo($ids, $usuarioId);
                $mensaje = count($resultados['exitosos']) . ' restaurada(s) correctamente.';
                if (!empty($resultados['fallos'])) {
                    $fallos = array_map(function ($f) {
                        return "ID {$f['id']} ({$f['error']})";
                    }, $resultados['fallos']);
                    $mensaje .= ' ' . count($resultados['fallos']) . ' fallo(s): ' . implode(', ', $fallos);
                }
                responder(true, $resultados, $mensaje);
                break;

            case 'eliminar_permanente_masivo':
                $ids = $cuerpo['ids'] ?? [];
                if (empty($ids) || !is_array($ids)) {
                    responder(false, null, 'Lista de IDs requerida', 400);
                }
                $resultados = $modeloPapelera->eliminarPermanenteMasivo($ids, $usuarioId);
                $mensaje = count($resultados['exitosos']) . ' eliminada(s) permanentemente.';
                if (!empty($resultados['fallos'])) {
                    $fallos = array_map(function ($f) {
                        return "ID {$f['id']} ({$f['error']})";
                    }, $resultados['fallos']);
                    $mensaje .= ' ' . count($resultados['fallos']) . ' fallo(s): ' . implode(', ', $fallos);
                }
                responder(true, $resultados, $mensaje);
                break;

            default:
                responder(false, null, 'Acción no válida', 400);
        }
    }

    else {
        responder(false, null, 'Método no permitido', 405);
    }

} catch (Exception $e) {
    error_log("Error en API eliminadas: " . $e->getMessage());
    responder(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
