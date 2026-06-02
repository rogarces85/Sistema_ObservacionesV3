<?php
/**
 * API de Kanban del Dashboard
 * Columnas por estado, soporte drag & drop para cambiar estado
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Database.php';
require_once __DIR__ . '/../../includes/csrf.php';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRF::validateRequest();

    $entrada = json_decode(file_get_contents('php://input'), true);
    if (!$entrada || !isset($entrada['id']) || !isset($entrada['estado'])) {
        responder(false, null, 'ID y estado son requeridos', 400);
    }

    $observacionId = (int) $entrada['id'];
    $nuevoEstado = $entrada['estado'];

    $estadosPermitidos = [ESTADO_PENDIENTE, ESTADO_APROBADO, ESTADO_RECHAZADO, ESTADO_ERROR, ESTADO_JUSTIFICADO];
    if (!in_array($nuevoEstado, $estadosPermitidos)) {
        responder(false, null, 'Estado no válido', 400);
    }

    try {
        $db = Database::getInstance();

        $sqlVerificar = "SELECT o.*, e.nombre_corto 
                         FROM observaciones o 
                         INNER JOIN establecimientos e ON o.establecimiento_id = e.id 
                         WHERE o.id = ? AND o.anio = ?";
        $paramsVerificar = [$observacionId, $anio];

        if ($rol === ROL_REGISTRADOR) {
            $sqlVerificar .= " AND o.usuario_registro_id = ?";
            $paramsVerificar[] = $usuarioId;
        }

        $observacion = $db->queryOne($sqlVerificar, $paramsVerificar);

        if (!$observacion) {
            responder(false, null, 'Observación no encontrada', 404);
        }

        $db->execute(
            "UPDATE observaciones SET estado_actual = ?, fecha_actualizacion = NOW() WHERE id = ?",
            [$nuevoEstado, $observacionId]
        );

        responder(true, [
            'id' => $observacionId,
            'estado' => $nuevoEstado,
            'nombre_corto' => $observacion['nombre_corto']
        ]);

    } catch (Exception $e) {
        responder(false, null, 'Error al actualizar estado: ' . $e->getMessage(), 500);
    }
}

try {
    $db = Database::getInstance();

    $sql = "SELECT o.id, o.estado_actual, o.tipo_error, o.mes, o.codigo_serie, o.codigo_hoja,
                   o.fecha_creacion,
                   e.nombre_corto,
                   u.nombre_completo as usuario_nombre
            FROM observaciones o
            INNER JOIN establecimientos e ON o.establecimiento_id = e.id
            INNER JOIN usuarios u ON o.usuario_registro_id = u.id
            WHERE o.anio = ?
            ORDER BY o.fecha_creacion DESC";
    $params = [$anio];

    if ($rol === ROL_REGISTRADOR) {
        $sqlEstablecimientos = "SELECT DISTINCT establecimiento_id 
                                FROM asignaciones_establecimientos 
                                WHERE usuario_id = ? AND anio = ?";
        $establecimientos = $db->query($sqlEstablecimientos, [$usuarioId, $anio]);

        if (empty($establecimientos)) {
            responder(true, [
                'columnas' => [
                    ESTADO_PENDIENTE => [],
                    ESTADO_APROBADO => [],
                    ESTADO_RECHAZADO => [],
                    ESTADO_ERROR => [],
                    ESTADO_JUSTIFICADO => []
                ],
                'puedeArrastrar' => false
            ]);
        }

        $ids = array_map(fn($f) => (int) $f['establecimiento_id'], $establecimientos);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql .= " AND o.establecimiento_id IN ($placeholders)";
        $params = array_merge($params, $ids);
    }

    $observaciones = $db->query($sql, $params);

    $columnas = [
        ESTADO_PENDIENTE => [],
        ESTADO_APROBADO => [],
        ESTADO_RECHAZADO => [],
        ESTADO_ERROR => [],
        ESTADO_JUSTIFICADO => []
    ];

    foreach ($observaciones as $obs) {
        $estado = $obs['estado_actual'];
        if (isset($columnas[$estado])) {
            $columnas[$estado][] = [
                'id' => $obs['id'],
                'nombre_corto' => $obs['nombre_corto'],
                'mes' => $obs['mes'],
                'tipo_error' => $obs['tipo_error'],
                'codigo_serie' => $obs['codigo_serie'],
                'codigo_hoja' => $obs['codigo_hoja'],
                'usuario_nombre' => $obs['usuario_nombre'],
                'fecha_creacion' => $obs['fecha_creacion']
            ];
        }
    }

    responder(true, [
        'columnas' => $columnas,
        'puedeArrastrar' => $rol === ROL_SUPERVISOR
    ]);

} catch (Exception $e) {
    responder(false, null, 'Error al obtener kanban: ' . $e->getMessage(), 500);
}
