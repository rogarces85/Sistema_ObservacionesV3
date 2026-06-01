<?php
/**
 * API de Estadísticas del Dashboard
 * Retorna tarjetas: Total, Pendientes, Aprobadas, Problemas
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

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    responder(false, null, 'No autorizado', 401);
}

$usuarioId = (int) ($_SESSION['user_id'] ?? 0);
$rol = $_SESSION['rol'] ?? '';
$anio = (int) ($_GET['anio'] ?? $_SESSION['year'] ?? date('Y'));

try {
    $db = Database::getInstance();

    $where = 'WHERE o.anio = ?';
    $params = [$anio];

    if ($rol === ROL_REGISTRADOR) {
        $sqlEstablecimientos = "SELECT DISTINCT establecimiento_id 
                                FROM asignaciones_establecimientos 
                                WHERE usuario_id = ? AND anio = ?";
        $establecimientos = $db->query($sqlEstablecimientos, [$usuarioId, $anio]);

        if (empty($establecimientos)) {
            responder(true, [
                'total' => 0,
                'pendientes' => 0,
                'aprobadas' => 0,
                'problemas' => 0,
                'por_estado' => [],
                'anio' => $anio
            ]);
        }

        $ids = array_map(fn($f) => (int) $f['establecimiento_id'], $establecimientos);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $where .= " AND o.establecimiento_id IN ($placeholders)";
        $params = array_merge($params, $ids);
    }

    $total = $db->queryOne("SELECT COUNT(*) as total FROM observaciones o {$where}", $params);

    $porEstado = $db->query(
        "SELECT estado_actual, COUNT(*) as total FROM observaciones o {$where} GROUP BY estado_actual",
        $params
    );

    $pendientes = 0;
    $aprobadas = 0;
    $problemas = 0;

    foreach ($porEstado as $fila) {
        switch ($fila['estado_actual']) {
            case ESTADO_PENDIENTE:
                $pendientes = (int) $fila['total'];
                break;
            case ESTADO_APROBADO:
                $aprobadas = (int) $fila['total'];
                break;
            case ESTADO_RECHAZADO:
            case ESTADO_ERROR:
                $problemas += (int) $fila['total'];
                break;
        }
    }

    responder(true, [
        'total' => (int) ($total['total'] ?? 0),
        'pendientes' => $pendientes,
        'aprobadas' => $aprobadas,
        'problemas' => $problemas,
        'por_estado' => $porEstado,
        'anio' => $anio
    ]);

} catch (Exception $e) {
    responder(false, null, 'Error al obtener estadísticas: ' . $e->getMessage(), 500);
}
