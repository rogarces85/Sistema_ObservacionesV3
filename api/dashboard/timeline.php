<?php
/**
 * API de Timeline del Dashboard
 * Retorna actividad reciente del sistema
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
$limite = (int) ($_GET['limite'] ?? 15);

try {
    $db = Database::getInstance();

    $sql = "SELECT o.id, o.estado_actual, o.tipo_error, o.fecha_creacion,
                   e.nombre_corto,
                   u.nombre_completo as usuario_nombre,
                   'observacion' as tipo_evento
            FROM observaciones o
            INNER JOIN establecimientos e ON o.establecimiento_id = e.id
            INNER JOIN usuarios u ON o.usuario_registro_id = u.id
            WHERE o.anio = ?";
    $params = [$anio];

    if ($rol === ROL_REGISTRADOR) {
        $sqlEstablecimientos = "SELECT DISTINCT establecimiento_id 
                                FROM asignaciones_establecimientos 
                                WHERE usuario_id = ? AND anio = ?";
        $establecimientos = $db->query($sqlEstablecimientos, [$usuarioId, $anio]);

        if (empty($establecimientos)) {
            responder(true, ['eventos' => []]);
        }

        $ids = array_map(fn($f) => (int) $f['establecimiento_id'], $establecimientos);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql .= " AND o.establecimiento_id IN ($placeholders)";
        $params = array_merge($params, $ids);
    }

    $sql .= " ORDER BY o.fecha_creacion DESC LIMIT ?";
    $params[] = $limite;

    $observaciones = $db->query($sql, $params);

    $mapaIconos = [
        ESTADO_PENDIENTE => 'clock',
        ESTADO_APROBADO => 'check',
        ESTADO_RECHAZADO => 'x',
        ESTADO_ERROR => 'alert-triangle',
        ESTADO_JUSTIFICADO => 'info-circle'
    ];

    $mapaColores = [
        ESTADO_PENDIENTE => 'yellow',
        ESTADO_APROBADO => 'green',
        ESTADO_RECHAZADO => 'red',
        ESTADO_ERROR => 'red',
        ESTADO_JUSTIFICADO => 'blue'
    ];

    $eventos = [];
    foreach ($observaciones as $obs) {
        $estado = $obs['estado_actual'];
        $eventos[] = [
            'id' => $obs['id'],
            'icono' => $mapaIconos[$estado] ?? 'circle',
            'color' => $mapaColores[$estado] ?? 'secondary',
            'descripcion' => sprintf('Observación #%d - %s - %s', $obs['id'], $obs['nombre_corto'], $obs['tipo_error']),
            'usuario' => $obs['usuario_nombre'] ?? 'Sistema',
            'fecha' => $obs['fecha_creacion'],
            'estado' => $estado
        ];
    }

    responder(true, ['eventos' => $eventos]);

} catch (Exception $e) {
    responder(false, null, 'Error al obtener timeline: ' . $e->getMessage(), 500);
}
