<?php
/**
 * API de Establecimientos
 * Endpoints para gestión de establecimientos y referentes
 * Solo accesible para rol Supervisor
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../models/Establecimiento.php';
require_once __DIR__ . '/../models/Comuna.php';
require_once __DIR__ . '/../models/Referente.php';

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
    responder(false, null, 'Acceso denegado. Solo supervisores pueden gestionar establecimientos.', 403);
}

$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

try {
    $modeloEstablecimiento = new Establecimiento();
    $modeloComuna = new Comuna();
    $modeloReferente = new Referente();

    // GET: Lectura
    if ($metodo === 'GET') {
        switch ($accion) {
            case 'listar':
                $comunaId = $_GET['comuna_id'] ?? null;
                $busqueda = $_GET['busqueda'] ?? null;
                $incluirInactivos = isset($_GET['incluir_inactivos']) && $_GET['incluir_inactivos'] === '1';
                
                $establecimientos = $modeloEstablecimiento->listar($comunaId, $busqueda, $incluirInactivos);
                
                // Agregar conteo de referentes a cada establecimiento
                foreach ($establecimientos as &$est) {
                    $est['referentes_count'] = $modeloEstablecimiento->contarReferentes($est['id']);
                }
                
                responder(true, $establecimientos);
                break;

            case 'comunas':
                $comunas = $modeloComuna->listar();
                responder(true, $comunas);
                break;

            case 'estadisticas':
                $stats = $modeloEstablecimiento->estadisticas();
                responder(true, $stats);
                break;

            case 'obtener':
                $id = $_GET['id'] ?? null;
                if (!$id) {
                    responder(false, null, 'ID de establecimiento requerido', 400);
                }
                $establecimiento = $modeloEstablecimiento->obtenerPorId($id);
                if (!$establecimiento) {
                    responder(false, null, 'Establecimiento no encontrado', 404);
                }
                responder(true, $establecimiento);
                break;

            case 'listar_referentes':
                $establecimientoId = $_GET['establecimiento_id'] ?? null;
                if (!$establecimientoId) {
                    responder(false, null, 'ID de establecimiento requerido', 400);
                }
                $referentes = $modeloReferente->listarPorEstablecimiento($establecimientoId);
                responder(true, $referentes);
                break;

            case 'obtener_referente':
                $id = $_GET['id'] ?? null;
                if (!$id) {
                    responder(false, null, 'ID de referente requerido', 400);
                }
                $referente = $modeloReferente->obtenerPorId($id);
                if (!$referente) {
                    responder(false, null, 'Referente no encontrado', 404);
                }
                responder(true, $referente);
                break;

            default:
                responder(false, null, 'Acción no válida', 400);
        }
    }

    // POST: Crear/Actualizar
    elseif ($metodo === 'POST') {
        CSRF::validateRequest();

        $entrada = file_get_contents('php://input');
        $cuerpo = json_decode($entrada, true);
        $accion = $cuerpo['accion'] ?? '';

        switch ($accion) {
            case 'crear':
                $codigo = $cuerpo['codigo_establecimiento'] ?? null;
                $nombre = $cuerpo['nombre'] ?? null;
                $nombreCorto = $cuerpo['nombre_corto'] ?? null;
                $comunaId = $cuerpo['comuna_id'] ?? null;

                if (!$codigo || !$nombre || !$comunaId) {
                    responder(false, null, 'Código, nombre y comuna son obligatorios', 400);
                }

                if ($modeloEstablecimiento->codigoExiste($codigo)) {
                    responder(false, null, 'Ya existe un establecimiento con ese código', 400);
                }

                $nuevoId = $modeloEstablecimiento->crear($codigo, $nombre, $nombreCorto, $comunaId);
                if ($nuevoId) {
                    responder(true, ['id' => $nuevoId], 'Establecimiento creado exitosamente');
                }
                responder(false, null, 'Error al crear establecimiento', 500);
                break;

            case 'actualizar':
                $id = $cuerpo['id'] ?? null;
                if (!$id) {
                    responder(false, null, 'ID requerido', 400);
                }

                $datos = [];
                if (isset($cuerpo['nombre'])) $datos['nombre'] = trim($cuerpo['nombre']);
                if (isset($cuerpo['nombre_corto'])) $datos['nombre_corto'] = trim($cuerpo['nombre_corto']);
                if (isset($cuerpo['comuna_id'])) $datos['comuna_id'] = (int) $cuerpo['comuna_id'];

                // Verificar código duplicado si se cambia
                if (isset($cuerpo['codigo_establecimiento'])) {
                    if ($modeloEstablecimiento->codigoExiste($cuerpo['codigo_establecimiento'], $id)) {
                        responder(false, null, 'Ya existe otro establecimiento con ese código', 400);
                    }
                }

                if ($modeloEstablecimiento->actualizar($id, $datos)) {
                    responder(true, null, 'Establecimiento actualizado exitosamente');
                }
                responder(false, null, 'Error al actualizar establecimiento', 500);
                break;

            case 'toggle':
                $id = $cuerpo['id'] ?? null;
                $activo = $cuerpo['activo'] ?? null;
                if (!$id || $activo === null) {
                    responder(false, null, 'ID y estado requeridos', 400);
                }
                if ($modeloEstablecimiento->toggle($id, $activo)) {
                    responder(true, null, $activo ? 'Establecimiento activado' : 'Establecimiento desactivado');
                }
                responder(false, null, 'Error al cambiar estado', 500);
                break;

            case 'crear_referente':
                $establecimientoId = $cuerpo['establecimiento_id'] ?? null;
                $cargo = $cuerpo['cargo'] ?? null;
                $nombre = $cuerpo['nombre'] ?? null;
                $telefono = $cuerpo['telefono'] ?? null;
                $email = $cuerpo['email'] ?? null;

                if (!$establecimientoId || !$nombre || !$cargo) {
                    responder(false, null, 'Establecimiento, nombre y cargo son obligatorios', 400);
                }

                // Validar email si se proporciona
                if ($email && !$modeloReferente->validarEmail($email)) {
                    responder(false, null, 'El formato del email no es válido', 400);
                }

                // Validar teléfono si se proporciona
                if ($telefono && !$modeloReferente->validarTelefono($telefono)) {
                    responder(false, null, 'El formato del teléfono no es válido', 400);
                }

                $nuevoId = $modeloReferente->crear($establecimientoId, $cargo, $nombre, $telefono, $email);
                if ($nuevoId) {
                    responder(true, ['id' => $nuevoId], 'Referente creado exitosamente');
                }
                responder(false, null, 'Error al crear referente', 500);
                break;

            case 'actualizar_referente':
                $id = $cuerpo['id'] ?? null;
                if (!$id) {
                    responder(false, null, 'ID de referente requerido', 400);
                }

                $datos = [];
                if (isset($cuerpo['cargo'])) $datos['cargo'] = trim($cuerpo['cargo']);
                if (isset($cuerpo['nombre'])) $datos['nombre'] = trim($cuerpo['nombre']);
                if (isset($cuerpo['telefono'])) $datos['telefono'] = trim($cuerpo['telefono']);
                if (isset($cuerpo['email'])) $datos['email'] = trim($cuerpo['email']);

                // Validar email si se proporciona
                if (isset($datos['email']) && $datos['email'] && !$modeloReferente->validarEmail($datos['email'])) {
                    responder(false, null, 'El formato del email no es válido', 400);
                }

                // Validar teléfono si se proporciona
                if (isset($datos['telefono']) && $datos['telefono'] && !$modeloReferente->validarTelefono($datos['telefono'])) {
                    responder(false, null, 'El formato del teléfono no es válido', 400);
                }

                if ($modeloReferente->actualizar($id, $datos)) {
                    responder(true, null, 'Referente actualizado exitosamente');
                }
                responder(false, null, 'Error al actualizar referente', 500);
                break;

            case 'toggle_referente':
                $id = $cuerpo['id'] ?? null;
                $activo = $cuerpo['activo'] ?? null;
                if (!$id || $activo === null) {
                    responder(false, null, 'ID y estado requeridos', 400);
                }
                if ($modeloReferente->toggle($id, $activo)) {
                    responder(true, null, $activo ? 'Referente activado' : 'Referente desactivado');
                }
                responder(false, null, 'Error al cambiar estado del referente', 500);
                break;

            case 'eliminar_referente':
                $id = $cuerpo['id'] ?? null;
                if (!$id) {
                    responder(false, null, 'ID de referente requerido', 400);
                }
                if ($modeloReferente->eliminar($id)) {
                    responder(true, null, 'Referente eliminado exitosamente');
                }
                responder(false, null, 'Error al eliminar referente', 500);
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

        if ($accion === 'eliminar_referente') {
            $id = $cuerpo['id'] ?? null;
            if (!$id) {
                responder(false, null, 'ID de referente requerido', 400);
            }
            if ($modeloReferente->eliminar($id)) {
                responder(true, null, 'Referente eliminado exitosamente');
            }
            responder(false, null, 'Error al eliminar referente', 500);
        }

        responder(false, null, 'Acción no válida', 400);
    }

    else {
        responder(false, null, 'Método no permitido', 405);
    }

} catch (Exception $e) {
    error_log("Error en API establecimientos: " . $e->getMessage());
    responder(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
