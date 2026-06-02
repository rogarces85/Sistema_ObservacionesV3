<?php
/**
 * API de Alertas del Dashboard
 * Supervisor: ve registradores sin asignar
 * Registrador: ve si no tiene establecimientos asignados
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Database.php';

function responder($success, $data = null, $error = null, $codigo = 200)
{
    http_response_code($codigo);
    $respuesta = ['success' => $success];
    if ($data !== null) $respuesta['data'] = $data;
    if ($error !== null) $respuesta['error'] = $error;
    $respuesta['code'] = $codigo;
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    responder(false, null, 'No autorizado', 401);
}

$usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
$rol = $_SESSION['rol'] ?? '';
$anio = (int) ($_GET['anio'] ?? $_SESSION['anio_trabajo'] ?? date('Y'));

try {
    $db = Database::getInstance();
    $alertas = [];

    if ($rol === ROL_SUPERVISOR) {
        $sql = "SELECT u.id, u.nombre_completo, u.username,
                       (SELECT COUNT(*) FROM asignaciones_establecimientos 
                        WHERE usuario_id = u.id AND anio = ?) as total_asignaciones
                FROM usuarios u
                WHERE u.rol = ? AND u.activo = 1
                HAVING total_asignaciones = 0
                ORDER BY u.nombre_completo ASC";
        $sinAsignaciones = $db->query($sql, [$anio, ROL_REGISTRADOR]);

        if (!empty($sinAsignaciones)) {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => count($sinAsignaciones) . ' registrador(es) sin establecimientos asignados',
                'mensaje' => 'Los siguientes registradores no tienen establecimientos asignados para el año ' . $anio,
                'detalles' => $sinAsignaciones,
                'accion' => [
                    'texto' => 'Ir a Asignación de Establecimientos',
                    'url' => '?pagina=asignaciones&anio=' . $anio
                ]
            ];
        }
    } elseif ($rol === ROL_REGISTRADOR) {
        $sql = "SELECT COUNT(*) as total 
                FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND anio = ?";
        $resultado = $db->queryOne($sql, [$usuarioId, $anio]);

        if ((int) ($resultado['total'] ?? 0) === 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'titulo' => 'No tiene establecimientos asignados',
                'mensaje' => 'No tiene establecimientos asignados para el año ' . $anio . '. Contacte a su supervisor.',
                'detalles' => [],
                'accion' => null
            ];
        }
    }

    responder(true, ['alertas' => $alertas]);

} catch (Exception $e) {
    responder(false, null, 'Error al obtener alertas: ' . $e->getMessage(), 500);
}
