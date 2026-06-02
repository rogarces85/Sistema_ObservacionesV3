<?php
/**
 * API de Observaciones Recientes
 * Retorna las últimas 5 observaciones
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
$limite = (int) ($_GET['limite'] ?? 5);

try {
    $db = Database::getInstance();

    $sql = "SELECT o.id, o.anio, o.mes, o.tipo_error, o.estado_actual, o.detalle_observacion,
                   o.codigo_serie, o.codigo_hoja, o.fecha_creacion,
                   e.nombre as establecimiento_nombre, e.nombre_corto,
                   c.nombre as comuna_nombre,
                   u.nombre_completo as usuario_registro_nombre
            FROM observaciones o
            INNER JOIN establecimientos e ON o.establecimiento_id = e.id
            INNER JOIN comunas c ON e.comuna_id = c.id
            INNER JOIN usuarios u ON o.usuario_registro_id = u.id
            WHERE o.anio = ?";
    $params = [$anio];

    if ($rol === ROL_REGISTRADOR) {
        $sqlEstablecimientos = "SELECT DISTINCT establecimiento_id 
                                FROM asignaciones_establecimientos 
                                WHERE usuario_id = ? AND anio = ?";
        $establecimientos = $db->query($sqlEstablecimientos, [$usuarioId, $anio]);

        if (empty($establecimientos)) {
            responder(true, ['observaciones' => []]);
        }

        $ids = array_map(fn($f) => (int) $f['establecimiento_id'], $establecimientos);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql .= " AND o.establecimiento_id IN ($placeholders)";
        $params = array_merge($params, $ids);
    }

    $sql .= " ORDER BY o.fecha_creacion DESC LIMIT ?";
    $params[] = $limite;

    $observaciones = $db->query($sql, $params);

    responder(true, ['observaciones' => $observaciones]);

} catch (Exception $e) {
    responder(false, null, 'Error al obtener observaciones recientes: ' . $e->getMessage(), 500);
}
