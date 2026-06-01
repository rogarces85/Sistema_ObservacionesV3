<?php
/**
 * API de Asignaciones
 * Gestión de asignaciones de establecimientos a registradores por año
 * Solo accesible para rol Supervisor
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../models/Asignacion.php';

/**
 * Retornar respuesta JSON
 */
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

// Verificar rol Supervisor
if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    responder(false, null, 'Acceso denegado. Solo supervisores pueden gestionar asignaciones.', 403);
}

$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? '';

try {
    $modeloAsignacion = new Asignacion();

    // GET: Lectura
    if ($metodo === 'GET') {
        switch ($accion) {
            case 'listar':
                $anio = $_GET['anio'] ?? date('Y');
                $estadisticas = $modeloAsignacion->obtenerEstadisticas($anio);
                responder(true, $estadisticas);
                break;

            case 'registradores':
                $registradores = $modeloAsignacion->obtenerRegistradores();
                responder(true, $registradores);
                break;

            case 'establecimientos':
                $registradorId = $_GET['registrador_id'] ?? null;
                $anio = $_GET['anio'] ?? date('Y');
                if ($registradorId) {
                    $establecimientos = $modeloAsignacion->obtenerEstablecimientosConAsignacion($registradorId, $anio);
                } else {
                    responder(false, null, 'ID de registrador requerido', 400);
                }
                responder(true, $establecimientos);
                break;

            case 'asignados':
                $registradorId = $_GET['registrador_id'] ?? null;
                $anio = $_GET['anio'] ?? date('Y');
                if (!$registradorId) {
                    responder(false, null, 'ID de registrador requerido', 400);
                }
                $asignados = $modeloAsignacion->obtenerPorRegistrador($registradorId, $anio);
                responder(true, $asignados);
                break;

            case 'referentes':
                $establecimientoId = $_GET['establecimiento_id'] ?? null;
                if (!$establecimientoId) {
                    responder(false, null, 'ID de establecimiento requerido', 400);
                }
                $referentes = $modeloAsignacion->obtenerReferentes($establecimientoId);
                responder(true, $referentes);
                break;

            case 'temporales':
                $anio = $_GET['anio'] ?? date('Y');
                $temporales = $modeloAsignacion->obtenerTemporales($anio);
                
                // Agregar información del titular anual para cada temporal
                foreach ($temporales as &$temp) {
                    $titular = $modeloAsignacion->obtenerTitularAnual($temp['establecimiento_id'], $anio);
                    $temp['titular_anual'] = $titular;
                }
                
                responder(true, $temporales);
                break;

            default:
                responder(false, null, 'Acción no válida', 400);
        }
    }

    // POST: Crear/Actualizar/Acciones especiales
    elseif ($metodo === 'POST') {
        CSRF::validateRequest();

        $entrada = file_get_contents('php://input');
        $cuerpo = json_decode($entrada, true);
        $accion = $cuerpo['accion'] ?? '';

        switch ($accion) {
            case 'crear':
                $usuarioId = $cuerpo['usuario_id'] ?? null;
                $establecimientoId = $cuerpo['establecimiento_id'] ?? null;
                $anio = $cuerpo['anio'] ?? date('Y');
                $meses = $cuerpo['meses'] ?? 'ALL';
                $tipo = $cuerpo['tipo'] ?? 'anual';

                if (!$usuarioId || !$establecimientoId) {
                    responder(false, null, 'Usuario y establecimiento son requeridos', 400);
                }

                try {
                    $modeloAsignacion->crear($usuarioId, $establecimientoId, $anio, $meses, $tipo);
                    $mensaje = ($tipo === 'temporal') 
                        ? 'Asignación temporal creada exitosamente' 
                        : 'Establecimiento asignado exitosamente';
                    responder(true, null, $mensaje);
                } catch (Exception $e) {
                    responder(false, null, $e->getMessage(), 400);
                }
                break;

            case 'actualizar':
                $id = $cuerpo['id'] ?? null;
                $meses = $cuerpo['meses'] ?? null;
                $tipo = $cuerpo['tipo'] ?? 'anual';

                if (!$id || $meses === null) {
                    responder(false, null, 'ID y meses son requeridos', 400);
                }

                try {
                    $modeloAsignacion->actualizar($id, $meses, $tipo);
                    responder(true, null, 'Asignación actualizada exitosamente');
                } catch (Exception $e) {
                    responder(false, null, $e->getMessage(), 400);
                }
                break;

            case 'eliminar':
                $id = $cuerpo['id'] ?? null;
                if (!$id) {
                    responder(false, null, 'ID de asignación requerido', 400);
                }

                if ($modeloAsignacion->eliminarPorId($id)) {
                    responder(true, null, 'Asignación eliminada exitosamente');
                }
                responder(false, null, 'Error al eliminar asignación', 500);
                break;

            case 'masivo':
                $usuarioId = $cuerpo['usuario_id'] ?? null;
                $establecimientoIds = $cuerpo['establecimiento_ids'] ?? [];
                $anio = $cuerpo['anio'] ?? date('Y');
                $meses = $cuerpo['meses'] ?? 'ALL';
                $tipo = $cuerpo['tipo'] ?? 'anual';

                if (!$usuarioId || empty($establecimientoIds)) {
                    responder(false, null, 'Usuario y lista de establecimientos son requeridos', 400);
                }

                try {
                    $modeloAsignacion->asignacionMasiva($usuarioId, $establecimientoIds, $anio, $meses, $tipo);
                    $mensaje = ($tipo === 'temporal') 
                        ? 'Asignaciones temporales creadas exitosamente' 
                        : 'Establecimientos asignados exitosamente';
                    responder(true, null, $mensaje);
                } catch (Exception $e) {
                    responder(false, null, $e->getMessage(), 400);
                }
                break;

            case 'copiar':
                $anioOrigen = $cuerpo['anio_origen'] ?? null;
                $anioDestino = $cuerpo['anio_destino'] ?? null;

                if (!$anioOrigen || !$anioDestino) {
                    responder(false, null, 'Año origen y destino son requeridos', 400);
                }

                try {
                    $cantidad = $modeloAsignacion->copiarAsignaciones($anioOrigen, $anioDestino);
                    responder(true, ['cantidad' => $cantidad], "Asignaciones copiadas de {$anioOrigen} a {$anioDestino}");
                } catch (Exception $e) {
                    responder(false, null, 'Error al copiar asignaciones: ' . $e->getMessage(), 500);
                }
                break;

            default:
                responder(false, null, 'Acción no válida', 400);
        }
    }

    // DELETE: Eliminar
    elseif ($metodo === 'DELETE') {
        CSRF::validateRequest();

        $entrada = file_get_contents('php://input');
        $cuerpo = json_decode($entrada, true);
        $accion = $cuerpo['accion'] ?? '';

        if ($accion === 'eliminar') {
            $id = $cuerpo['id'] ?? null;
            if (!$id) {
                responder(false, null, 'ID de asignación requerido', 400);
            }
            if ($modeloAsignacion->eliminarPorId($id)) {
                responder(true, null, 'Asignación eliminada exitosamente');
            }
            responder(false, null, 'Error al eliminar asignación', 500);
        }

        responder(false, null, 'Acción no válida', 400);
    }

    else {
        responder(false, null, 'Método no permitido', 405);
    }

} catch (Exception $e) {
    error_log("Error en API asignaciones: " . $e->getMessage());
    responder(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
