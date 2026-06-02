<?php
/**
 * API de Sparklines del Dashboard
 * Retorna tendencia de 7 días para cada tarjeta
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

    $where = 'WHERE o.anio = ? AND DATE(o.fecha_creacion) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
    $params = [$anio];

    if ($rol === ROL_REGISTRADOR) {
        $sqlEstablecimientos = "SELECT DISTINCT establecimiento_id 
                                FROM asignaciones_establecimientos 
                                WHERE usuario_id = ? AND anio = ?";
        $establecimientos = $db->query($sqlEstablecimientos, [$usuarioId, $anio]);

        if (empty($establecimientos)) {
            responder(true, [
                'total' => [],
                'pendientes' => [],
                'aprobadas' => [],
                'problemas' => []
            ]);
        }

        $ids = array_map(fn($f) => (int) $f['establecimiento_id'], $establecimientos);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $where .= " AND o.establecimiento_id IN ($placeholders)";
        $params = array_merge($params, $ids);
    }

    $sql = "SELECT DATE(o.fecha_creacion) as fecha,
                   COUNT(*) as total,
                   SUM(CASE WHEN o.estado_actual = ? THEN 1 ELSE 0 END) as pendientes,
                   SUM(CASE WHEN o.estado_actual = ? THEN 1 ELSE 0 END) as aprobadas,
                   SUM(CASE WHEN o.estado_actual IN (?, ?) THEN 1 ELSE 0 END) as problemas
            FROM observaciones o
            {$where}
            GROUP BY DATE(o.fecha_creacion)
            ORDER BY fecha ASC";

    $paramsSpark = array_merge(
        [ESTADO_PENDIENTE, ESTADO_APROBADO, ESTADO_RECHAZADO, ESTADO_ERROR],
        $params
    );

    $datos = $db->query($sql, $paramsSpark);

    $total = [];
    $pendientes = [];
    $aprobadas = [];
    $problemas = [];

    for ($i = 6; $i >= 0; $i--) {
        $fecha = date('Y-m-d', strtotime("-{$i} days"));
        $encontrado = false;

        foreach ($datos as $fila) {
            if ($fila['fecha'] === $fecha) {
                $total[] = (int) $fila['total'];
                $pendientes[] = (int) ($fila['pendientes'] ?? 0);
                $aprobadas[] = (int) ($fila['aprobadas'] ?? 0);
                $problemas[] = (int) ($fila['problemas'] ?? 0);
                $encontrado = true;
                break;
            }
        }

        if (!$encontrado) {
            $total[] = 0;
            $pendientes[] = 0;
            $aprobadas[] = 0;
            $problemas[] = 0;
        }
    }

    responder(true, [
        'total' => $total,
        'pendientes' => $pendientes,
        'aprobadas' => $aprobadas,
        'problemas' => $problemas
    ]);

} catch (Exception $e) {
    responder(false, null, 'Error al obtener sparklines: ' . $e->getMessage(), 500);
}
