<?php
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

$rol = $_SESSION['rol'] ?? '';
$usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
$anio = (int)($_GET['anio'] ?? $_SESSION['anio_trabajo'] ?? date('Y'));
$anioAnterior = $anio - 1;

try {
    $db = Database::obtenerInstancia();

    // === Widget 2: Carga del Supervisor (observaciones pendientes por supervisor) ===
    $cargaSupervisor = [];
    if ($rol === ROL_SUPERVISOR) {
        $cargaSupervisor = $db->consultar("
            SELECT u.id, u.nombre_completo, COUNT(o.id) as pendientes
            FROM observaciones o
            JOIN usuarios u ON o.usuario_supervisor_id = u.id
            WHERE o.estado_actual = 'pendiente' AND o.anio = :anio
            GROUP BY o.usuario_supervisor_id
            ORDER BY pendientes DESC
        ", ['anio' => $anio]);
    }

    // === Widget 5: Mapa por Comuna ===
    $mapaComunas = $db->consultar("
        SELECT c.nombre, c.id, COUNT(o.id) as total
        FROM observaciones o
        JOIN establecimientos e ON o.establecimiento_id = e.id
        JOIN comunas c ON e.comuna_id = c.id
        WHERE o.anio = :anio
        GROUP BY c.id, c.nombre
        ORDER BY total DESC
    ", ['anio' => $anio]);

    // === Widget 6: Heatmap Serie × Hoja ===
    $heatmap = $db->consultar("
        SELECT codigo_serie, codigo_hoja, COUNT(*) as total
        FROM observaciones
        WHERE anio = :anio
        GROUP BY codigo_serie, codigo_hoja
        ORDER BY total DESC
        LIMIT 30
    ", ['anio' => $anio]);

    // === Widget 7: Comparativa Interanual ===
    $ordenMeses = "FIELD(mes, 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre')";
    $comparativa = $db->consultar("
        SELECT mes, anio, COUNT(*) as total
        FROM observaciones
        WHERE anio IN (:anio, :anio_ant)
        GROUP BY mes, anio
        ORDER BY $ordenMeses, anio
    ", ['anio' => $anio, 'anio_ant' => $anioAnterior]);

    // === Widget 8: Cumplimiento de Plazo ===
    $cumplimientoPlazo = $db->consultar("
        SELECT plazo_entrega, COUNT(*) as total
        FROM observaciones
        WHERE anio = :anio AND plazo_entrega IS NOT NULL
        GROUP BY plazo_entrega
    ", ['anio' => $anio]);

    // === Widget 13: Alertas Inteligentes ===
    $alertas = [];

    // Establecimientos sin asignar en el año actual
    $sinAsignar = $db->consultarUno("
        SELECT COUNT(*) as total FROM establecimientos e
        WHERE e.activo = 1
        AND e.id NOT IN (
            SELECT ae.establecimiento_id FROM asignaciones_establecimientos ae WHERE ae.anio = :anio
        )
    ", ['anio' => $anio]);
    if ($sinAsignar && $sinAsignar['total'] > 0) {
        $alertas[] = [
            'tipo' => 'warning',
            'icono' => 'building',
            'titulo' => 'Establecimientos sin asignar',
            'mensaje' => "{$sinAsignar['total']} establecimientos activos no tienen registrador asignado en {$anio}",
            'enlace' => '?pagina=asignaciones&anio=' . $anio,
            'texto_enlace' => 'Asignar ahora'
        ];
    }

    // Observaciones pendientes antiguas (> 7 días sin revisar)
    $antiguas = $db->consultarUno("
        SELECT COUNT(*) as total FROM observaciones
        WHERE estado_actual = 'pendiente' AND anio = :anio
        AND fecha_registro < DATE_SUB(NOW(), INTERVAL 7 DAY)
    ", ['anio' => $anio]);
    if ($antiguas && $antiguas['total'] > 0) {
        $alertas[] = [
            'tipo' => 'danger',
            'icono' => 'clock',
            'titulo' => 'Observaciones sin revisar',
            'mensaje' => "{$antiguas['total']} observaciones llevan más de 7 días pendientes de revisión",
            'enlace' => '?pagina=supervision&anio=' . $anio,
            'texto_enlace' => 'Revisar'
        ];
    }

    // Observaciones rechazadas sin justificar
    $rechazadas = $db->consultarUno("
        SELECT COUNT(*) as total FROM observaciones
        WHERE estado_actual = 'rechazado' AND anio = :anio
        AND (respuesta_establecimiento IS NULL OR respuesta_establecimiento = '')
    ", ['anio' => $anio]);
    if ($rechazadas && $rechazadas['total'] > 0) {
        $alertas[] = [
            'tipo' => 'warning',
            'icono' => 'message',
            'titulo' => 'Rechazos sin justificar',
            'mensaje' => "{$rechazadas['total']} observaciones rechazadas no tienen respuesta del establecimiento",
            'enlace' => '?pagina=observaciones&anio=' . $anio,
            'texto_enlace' => 'Ver'
        ];
    }

    // Total registradores activos
    $registradores = $db->consultarUno("
        SELECT COUNT(*) as total FROM usuarios WHERE rol = 'registrador' AND activo = 1
    ");
    $alertas[] = [
        'tipo' => 'info',
        'icono' => 'users',
        'titulo' => 'Registradores activos',
        'mensaje' => "{$registradores['total']} registradores activos en el sistema",
        'enlace' => '?pagina=usuarios',
        'texto_enlace' => 'Gestionar'
    ];

    // === Widget 15: Estacionalidad (comparativa mensual con promedio histórico) ===
    $estacionalidadActual = $db->consultar("
        SELECT mes, COUNT(*) as total
        FROM observaciones
        WHERE anio = :anio
        GROUP BY mes
        ORDER BY $ordenMeses
    ", ['anio' => $anio]);

    $estacionalidadHistorico = $db->consultar("
        SELECT mes, ROUND(AVG(total)) as promedio
        FROM (
            SELECT mes, anio, COUNT(*) as total
            FROM observaciones
            WHERE anio < :anio
            GROUP BY mes, anio
        ) subq
        GROUP BY mes
        ORDER BY $ordenMeses
    ", ['anio' => $anio]);

    $mesesOrden = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

    $estacionalidad = [];
    foreach ($mesesOrden as $mes) {
        $actual = 0;
        $promedio = 0;
        foreach ($estacionalidadActual as $ea) {
            if ($ea['mes'] === $mes) { $actual = (int)$ea['total']; break; }
        }
        foreach ($estacionalidadHistorico as $eh) {
            if ($eh['mes'] === $mes) { $promedio = (int)$eh['promedio']; break; }
        }
        $estacionalidad[] = ['mes' => $mes, 'actual' => $actual, 'promedio' => $promedio];
    }

    responder(true, [
        'carga_supervisor' => $cargaSupervisor,
        'mapa_comunas' => $mapaComunas,
        'heatmap' => $heatmap,
        'comparativa' => $comparativa,
        'cumplimiento_plazo' => $cumplimientoPlazo,
        'alertas_inteligentes' => $alertas,
        'estacionalidad' => $estacionalidad
    ]);

} catch (Exception $e) {
    error_log("Error en widgets.php: " . $e->getMessage());
    responder(false, null, 'Error al cargar widgets', 500);
}