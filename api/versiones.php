<?php
/**
 * API de Versionado del Sistema
 * Fase 11 - Gestión de snapshots y rollbacks
 * Solo accesible para rol Supervisor
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../models/VersionSistema.php';

/**
 * Responder con JSON estandarizado
 */
function responderJson($success, $data = null, $error = null, $codigo = 200)
{
    http_response_code($codigo);
    $respuesta = ['success' => $success];

    if ($data !== null) {
        $respuesta['data'] = $data;
    }

    if ($error !== null) {
        $respuesta['error'] = $error;
    }

    $respuesta['code'] = $codigo;

    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar autenticación
if (!isset($_SESSION['usuario_id']) || $_SESSION['autenticado'] !== true) {
    responderJson(false, null, 'No autenticado', 401);
}

// Verificar rol (Solo Supervisor)
if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    responderJson(false, null, 'Acceso denegado. Solo el rol Supervisor puede acceder a esta función', 403);
}

// Validar CSRF para métodos que modifican datos
$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? '';

if (in_array($metodo, ['POST', 'PUT', 'DELETE'])) {
    if (!CSRF::validateRequest()) {
        exit;
    }
}

$usuarioId = $_SESSION['usuario_id'];

try {
    $modeloVersion = new VersionSistema();

    switch ($metodo) {
        case 'GET':
            if ($accion === 'listar') {
                $versiones = $modeloVersion->listarVersiones();
                responderJson(true, $versiones);
            } elseif ($accion === 'detalle' && isset($_GET['id'])) {
                $version = $modeloVersion->obtenerDetalle($_GET['id']);
                if ($version) {
                    responderJson(true, $version);
                } else {
                    responderJson(false, null, 'Versión no encontrada', 404);
                }
            } else {
                responderJson(false, null, 'Acción no válida', 400);
            }
            break;

        case 'POST':
            $entrada = json_decode(file_get_contents('php://input'), true);

            if ($accion === 'crear') {
                $descripcion = trim($entrada['descripcion'] ?? '');

                if (empty($descripcion)) {
                    responderJson(false, null, 'La descripción es obligatoria', 400);
                }

                $resultado = $modeloVersion->crearVersion($descripcion, $usuarioId);

                if ($resultado) {
                    // Regenerar token CSRF después de acción importante
                    CSRF::regenerateToken();

                    $mensaje = "Snapshot {$resultado['version_tag']} creado exitosamente ({$resultado['archivos_copiados']} archivos)";
                    responderJson(true, [
                        'id' => $resultado['id'],
                        'version_tag' => $resultado['version_tag'],
                        'archivos_copiados' => $resultado['archivos_copiados'],
                        'csrf_token' => $_SESSION['csrf_token']
                    ], $mensaje, 201);
                } else {
                    responderJson(false, null, 'Error al crear el snapshot', 500);
                }
            } elseif ($accion === 'restaurar' && isset($_GET['id'])) {
                $versionId = intval($_GET['id']);
                $resultado = $modeloVersion->restaurarVersion($versionId, $usuarioId);

                if ($resultado) {
                    // Regenerar token CSRF después de acción importante
                    CSRF::regenerateToken();

                    $mensaje = "Restauración desde {$resultado['version_tag_origen']} completada ({$resultado['archivos_restaurados']} archivos)";
                    responderJson(true, [
                        'id_version_nueva' => $resultado['id_version_nueva'],
                        'version_tag_nueva' => $resultado['version_tag_nueva'],
                        'version_tag_origen' => $resultado['version_tag_origen'],
                        'archivos_restaurados' => $resultado['archivos_restaurados'],
                        'archivos_fallidos' => $resultado['archivos_fallidos'],
                        'advertencia_bd' => $resultado['advertencia_bd'],
                        'csrf_token' => $_SESSION['csrf_token']
                    ], $mensaje);
                } else {
                    responderJson(false, null, 'Error al ejecutar la restauración', 500);
                }
            } else {
                responderJson(false, null, 'Acción no válida', 400);
            }
            break;

        default:
            responderJson(false, null, 'Método no permitido', 405);
    }
} catch (Exception $e) {
    error_log("Error en API versiones: " . $e->getMessage());
    responderJson(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
