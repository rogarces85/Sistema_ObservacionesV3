<?php
/**
 * API de Gráficos del Dashboard
 * Retorna datos para: donut (distribución por estado), barras (tipos de error), líneas (tendencia mensual)
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
$mes = $_GET['mes'] ?? '';

try {
    $db = Database::getInstance();

    $where = 'WHERE o.anio = ?';
    $params = [$anio];

    if ($mes !== '') {
        $where .= ' AND o.mes = ?';
        $params[] = $mes;
    }

    if ($rol === ROL_REGISTRADOR) {
        $sqlEstablecimientos = "SELECT DISTINCT establecimiento_id 
                                FROM asignaciones_establecimientos 
                                WHERE usuario_id = ? AND anio = ?";
        $establecimientos = $db->query($sqlEstablecimientos, [$usuarioId, $anio]);

        if (empty($establecimientos)) {
            responder(true, [
                'donut' => [],
                'barras' => [],
                'lineas' => []
            ]);
        }

        $ids = array_map(fn($f) => (int) $f['establecimiento_id'], $establecimientos);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $where .= " AND o.establecimiento_id IN ($placeholders)";
        $params = array_merge($params, $ids);
    }

    // Donut: distribución por estado
    $donut = $db->query(
        "SELECT estado_actual, COUNT(*) as total FROM observaciones o {$where} GROUP BY estado_actual ORDER BY total DESC",
        $params
    );

    // Barras: tipos de error
    $barras = $db->query(
        "SELECT tipo_error, COUNT(*) as total FROM observaciones o {$where} GROUP BY tipo_error ORDER BY total DESC LIMIT 10",
        $params
    );

    // Líneas: tendencia mensual
    $lineas = $db->query(
        "SELECT mes, COUNT(*) as total FROM observaciones o {$where} GROUP BY mes ORDER BY FIELD(mes, 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre')",
        $params
    );

    responder(true, [
        'donut' => $donut,
        'barras' => $barras,
        'lineas' => $lineas
    ]);

} catch (Exception $e) {
    responder(false, null, 'Error al obtener datos de gráficos: ' . $e->getMessage(), 500);
}
