<?php
/**
 * API de Locations
 * Endpoints para comunas y establecimientos
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../models/Location.php';

function jsonResponse($success, $data = null, $message = '', $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    jsonResponse(false, null, 'No autorizado', 401);
}

$method = $_SERVER['REQUEST_METHOD'];
$type = $_GET['type'] ?? $_GET['action'] ?? '';
$comunaId = $_GET['comunaId'] ?? $_GET['comuna_id'] ?? null;
$comunaNombre = $_GET['comuna_nombre'] ?? null;

try {
    $locationModel = new Location();

    // GET: Lectura (disponible para todos los roles autenticados)
    if ($method === 'GET') {
        switch ($type) {
            case 'comunas':
                jsonResponse(true, $locationModel->getAllComunas(), 'Comunas obtenidas');
                break;

            case 'establecimientos':
            case 'get_establecimientos':
                if ($comunaId) {
                    $data = $locationModel->getEstablecimientosByComuna($comunaId);
                } elseif ($comunaNombre) {
                    $comuna = $locationModel->getComunaByNombre($comunaNombre);
                    $data = $comuna ? $locationModel->getEstablecimientosByComuna($comuna['id']) : [];
                } else {
                    $data = $locationModel->getAllEstablecimientos();
                }
                jsonResponse(true, $data, 'Establecimientos obtenidos');
                break;

            case 'establecimientos_all':
                // Incluye inactivos (solo supervisor)
                if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
                    jsonResponse(false, null, 'No autorizado', 403);
                }
                jsonResponse(true, $locationModel->getAllEstablecimientosConInactivos(), 'Listado completo');
                break;

            default:
                jsonResponse(false, null, 'Tipo no válido. Use: comunas, establecimientos, establecimientos_all', 400);
        }
    }

    // POST: Crear establecimiento (solo supervisor)
    elseif ($method === 'POST') {
        if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
            jsonResponse(false, null, 'Solo supervisores pueden gestionar establecimientos', 403);
        }

        CSRF::validateRequest();

        $raw = file_get_contents('php://input');
        $body = json_decode($raw, true);

        $action = $body['action'] ?? 'create';

        switch ($action) {
            case 'create':
                $codigo = $body['codigo_establecimiento'] ?? null;
                $nombre = $body['nombre'] ?? null;
                $nombreCorto = $body['nombre_corto'] ?? null;
                $comunaId = $body['comuna_id'] ?? null;

                if (!$codigo || !$nombre || !$comunaId) {
                    jsonResponse(false, null, 'Código, nombre y comuna son obligatorios', 400);
                }

                if ($locationModel->codigoEstablecimientoExiste($codigo)) {
                    jsonResponse(false, null, 'Ya existe un establecimiento con ese código', 409);
                }

                $newId = $locationModel->createEstablecimiento($codigo, $nombre, $nombreCorto, $comunaId);
                if ($newId) {
                    jsonResponse(true, ['id' => $newId], 'Establecimiento creado exitosamente');
                }
                jsonResponse(false, null, 'Error al crear establecimiento', 500);
                break;

            case 'update':
                $id = $body['id'] ?? null;
                if (!$id) {
                    jsonResponse(false, null, 'ID requerido', 400);
                }

                $data = [];
                if (isset($body['codigo_establecimiento'])) $data['codigo_establecimiento'] = $body['codigo_establecimiento'];
                if (isset($body['nombre'])) $data['nombre'] = $body['nombre'];
                if (isset($body['nombre_corto'])) $data['nombre_corto'] = $body['nombre_corto'];
                if (isset($body['comuna_id'])) $data['comuna_id'] = $body['comuna_id'];
                if (isset($body['activo'])) $data['activo'] = $body['activo'];

                // Verificar código duplicado si se cambia
                if (isset($data['codigo_establecimiento']) && $locationModel->codigoEstablecimientoExiste($data['codigo_establecimiento'], $id)) {
                    jsonResponse(false, null, 'Ya existe otro establecimiento con ese código', 409);
                }

                if ($locationModel->updateEstablecimiento($id, $data)) {
                    jsonResponse(true, null, 'Establecimiento actualizado');
                }
                jsonResponse(false, null, 'Error al actualizar', 500);
                break;

            case 'toggle':
                $id = $body['id'] ?? null;
                $activo = $body['activo'] ?? null;
                if (!$id || $activo === null) {
                    jsonResponse(false, null, 'ID y estado requeridos', 400);
                }
                if ($locationModel->toggleEstablecimiento($id, $activo)) {
                    jsonResponse(true, null, $activo ? 'Establecimiento activado' : 'Establecimiento desactivado');
                }
                jsonResponse(false, null, 'Error al cambiar estado', 500);
                break;

            default:
                jsonResponse(false, null, 'Acción no válida: use create, update o toggle', 400);
        }
    }

    else {
        jsonResponse(false, null, 'Método no permitido', 405);
    }

} catch (Exception $e) {
    error_log("Error en API locations: " . $e->getMessage());
    jsonResponse(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
