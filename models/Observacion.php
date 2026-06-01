<?php
/**
 * Clase Observacion
 * CRUD completo de observaciones REM con validación de permisos por asignación
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Asignacion.php';

class Observacion
{
    private $db;
    private $asignacion;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->asignacion = new Asignacion();
    }

    /**
     * Obtener listado de observaciones con filtros y paginación
     */
    public function listar($filtros = [], $usuarioId = null, $rol = null)
    {
        $anio = $filtros['anio'] ?? date('Y');
        $pagina = max(1, (int) ($filtros['pagina'] ?? 1));
        $porPagina = $filtros['porPagina'] ?? 50;
        $offset = ($pagina - 1) * $porPagina;

        $sql = "SELECT o.*, 
                        e.nombre as establecimiento_nombre,
                        e.nombre_corto,
                        e.codigo_establecimiento,
                        c.nombre as comuna_nombre,
                        u.nombre_completo as usuario_registro_nombre
                FROM observaciones o
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                INNER JOIN usuarios u ON o.usuario_registro_id = u.id
                WHERE o.anio = ?";
        $params = [$anio];

        if ($rol === ROL_REGISTRADOR && $usuarioId) {
            $idsEstablecimientos = $this->obtenerEstablecimientosAsignados($usuarioId, $anio);
            if (empty($idsEstablecimientos)) {
                return ['datos' => [], 'total' => 0, 'pagina' => $pagina, 'porPagina' => $porPagina, 'totalPaginas' => 0];
            }
            $placeholders = implode(',', array_fill(0, count($idsEstablecimientos), '?'));
            $sql .= " AND o.establecimiento_id IN ($placeholders)";
            $params = array_merge($params, $idsEstablecimientos);
        }

        if (!empty($filtros['mes'])) {
            $sql .= " AND o.mes = ?";
            $params[] = $filtros['mes'];
        }

        if (!empty($filtros['estado'])) {
            $sql .= " AND o.estado_actual = ?";
            $params[] = $filtros['estado'];
        }

        if (!empty($filtros['establecimiento_id'])) {
            $sql .= " AND o.establecimiento_id = ?";
            $params[] = $filtros['establecimiento_id'];
        }

        if (!empty($filtros['tipo_error'])) {
            $sql .= " AND o.tipo_error = ?";
            $params[] = $filtros['tipo_error'];
        }

        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (o.detalle_observacion LIKE ? OR e.nombre LIKE ? OR e.nombre_corto LIKE ?)";
            $termino = '%' . $filtros['busqueda'] . '%';
            $params[] = $termino;
            $params[] = $termino;
            $params[] = $termino;
        }

        $sql .= " ORDER BY o.fecha_creacion DESC";

        $sqlConteo = preg_replace('/SELECT.*?FROM/s', 'SELECT COUNT(*) as total FROM', $sql);
        $sqlConteo = preg_replace('/ORDER BY.*$/', '', $sqlConteo);
        $resultadoConteo = $this->db->consultarUno($sqlConteo, $params);
        $total = (int) ($resultadoConteo['total'] ?? 0);
        $totalPaginas = ceil($total / $porPagina);

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $porPagina;
        $params[] = $offset;

        $datos = $this->db->consultar($sql, $params);

        return [
            'datos' => $datos,
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'totalPaginas' => $totalPaginas
        ];
    }

    /**
     * Obtener una observación por ID
     */
    public function obtenerPorId($id, $usuarioId = null, $rol = null)
    {
        $sql = "SELECT o.*, 
                        e.nombre as establecimiento_nombre,
                        e.nombre_corto,
                        e.codigo_establecimiento,
                        c.nombre as comuna_nombre,
                        c.id as comuna_id,
                        u.nombre_completo as usuario_registro_nombre
                FROM observaciones o
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                INNER JOIN usuarios u ON o.usuario_registro_id = u.id
                WHERE o.id = ?";
        $observacion = $this->db->consultarUno($sql, [$id]);

        if (!$observacion) {
            return null;
        }

        if ($rol === ROL_REGISTRADOR && $usuarioId) {
            if (!$this->tieneAcceso($usuarioId, $observacion['establecimiento_id'], $observacion['anio'], $observacion['mes'])) {
                return null;
            }
        }

        return $observacion;
    }

    /**
     * Crear nueva observación
     */
    public function crear($datos, $usuarioId, $rol)
    {
        $anio = (int) ($datos['anio'] ?? date('Y'));
        $mes = $datos['mes'];
        $establecimientoId = (int) $datos['establecimiento_id'];

        if ($rol === ROL_REGISTRADOR) {
            if (!$this->tieneAsignacion($usuarioId, $establecimientoId, $anio, $mes)) {
                throw new Exception('No tiene asignación para este establecimiento en el mes seleccionado', 403);
            }
        }

        $sql = "INSERT INTO observaciones 
                (usuario_registro_id, establecimiento_id, comuna_id, anio, mes, 
                 codigo_serie, codigo_hoja, tipo_error, detalle_observacion, 
                 plazo_entrega, anio_rem, mes_rem, estado_actual, clasificacion, 
                 fecha_creacion, fecha_actualizacion)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

        $comunaId = $this->obtenerComunaEstablecimiento($establecimientoId);

        $params = [
            $usuarioId,
            $establecimientoId,
            $comunaId,
            $anio,
            $mes,
            $datos['codigo_serie'] ?? null,
            $datos['codigo_hoja'] ?? null,
            $datos['tipo_error'],
            $datos['detalle_observacion'] ?? '',
            $datos['plazo_entrega'] ?? null,
            $datos['anio_rem'] ?? $anio,
            $datos['mes_rem'] ?? $mes,
            ESTADO_PENDIENTE,
            $datos['clasificacion'] ?? null
        ];

        $this->db->ejecutar($sql, $params);
        $id = $this->db->ultimoIdInsertado();

        $historial = new HistorialEstado();
        $historial->registrar($id, $usuarioId, null, ESTADO_PENDIENTE, 'Registro inicial');

        return $id;
    }

    /**
     * Actualizar observación con last-write-wins
     */
    public function actualizar($id, $datos, $usuarioId, $rol, $fechaActualizacionOriginal = null)
    {
        $observacion = $this->db->consultarUno("SELECT * FROM observaciones WHERE id = ?", [$id]);

        if (!$observacion) {
            throw new Exception('Observación no encontrada', 404);
        }

        if ($rol === ROL_REGISTRADOR) {
            if ($observacion['usuario_registro_id'] != $usuarioId) {
                throw new Exception('No tiene permisos para editar esta observación', 403);
            }
            if ($observacion['estado_actual'] !== ESTADO_PENDIENTE) {
                throw new Exception('Solo puede editar observaciones pendientes', 403);
            }
            $estId = $datos['establecimiento_id'] ?? $observacion['establecimiento_id'];
            $mes = $datos['mes'] ?? $observacion['mes'];
            $anio = $datos['anio'] ?? $observacion['anio'];
            if (!$this->tieneAsignacion($usuarioId, $estId, $anio, $mes)) {
                throw new Exception('No tiene asignación para este establecimiento en el mes seleccionado', 403);
            }
        }

        if ($fechaActualizacionOriginal !== null && $observacion['fecha_actualizacion'] !== $fechaActualizacionOriginal) {
            throw new Exception('La observación fue modificada por otro usuario. Recargue los datos.', 409);
        }

        $estadoAnterior = $observacion['estado_actual'];

        $campos = [];
        $params = [];

        $camposPermitidos = [
            'mes', 'establecimiento_id', 'comuna_id', 'anio', 'codigo_serie',
            'codigo_hoja', 'tipo_error', 'detalle_observacion', 'plazo_entrega',
            'anio_rem', 'mes_rem', 'estado_actual', 'clasificacion'
        ];

        foreach ($camposPermitidos as $campo) {
            if (isset($datos[$campo])) {
                $campos[] = "{$campo} = ?";
                $params[] = $datos[$campo];
            }
        }

        if (empty($campos)) {
            throw new Exception('No hay datos para actualizar', 400);
        }

        $campos[] = "fecha_actualizacion = NOW()";
        $params[] = $id;

        $sql = "UPDATE observaciones SET " . implode(', ', $campos) . " WHERE id = ?";
        $this->db->ejecutar($sql, $params);

        $estadoNuevo = $datos['estado_actual'] ?? $estadoAnterior;
        if ($estadoNuevo !== $estadoAnterior) {
            $historial = new HistorialEstado();
            $historial->registrar($id, $usuarioId, $estadoAnterior, $estadoNuevo, $datos['comentario_cambio'] ?? 'Cambio de estado');
        }

        return true;
    }

    /**
     * Eliminar físicamente una observación
     */
    public function eliminar($id, $usuarioId, $rol)
    {
        $observacion = $this->db->consultarUno("SELECT * FROM observaciones WHERE id = ?", [$id]);

        if (!$observacion) {
            throw new Exception('Observación no encontrada', 404);
        }

        if ($rol === ROL_REGISTRADOR) {
            throw new Exception('Solo los supervisores pueden eliminar observaciones', 403);
        }

        $historial = new HistorialEstado();
        $historial->registrar($id, $usuarioId, $observacion['estado_actual'], 'eliminado', 'Eliminación física del registro');

        $this->db->ejecutar("DELETE FROM historial_estados WHERE observacion_id = ?", [$id]);
        $this->db->ejecutar("DELETE FROM observaciones WHERE id = ?", [$id]);

        return true;
    }

    /**
     * Obtener estadísticas
     */
    public function estadisticas($anio, $usuarioId = null, $rol = null)
    {
        $where = "WHERE o.anio = ?";
        $params = [$anio];

        if ($rol === ROL_REGISTRADOR && $usuarioId) {
            $idsEstablecimientos = $this->obtenerEstablecimientosAsignados($usuarioId, $anio);
            if (empty($idsEstablecimientos)) {
                return [
                    'total' => 0,
                    'por_estado' => [],
                    'por_mes' => [],
                    'por_tipo_error' => [],
                    'por_establecimiento' => []
                ];
            }
            $placeholders = implode(',', array_fill(0, count($idsEstablecimientos), '?'));
            $where .= " AND o.establecimiento_id IN ($placeholders)";
            $params = array_merge($params, $idsEstablecimientos);
        }

        $total = $this->db->consultarUno("SELECT COUNT(*) as total FROM observaciones o {$where}", $params);

        $porEstado = $this->db->consultar(
            "SELECT estado_actual, COUNT(*) as total FROM observaciones o {$where} GROUP BY estado_actual",
            $params
        );

        $porMes = $this->db->consultar(
            "SELECT mes, COUNT(*) as total FROM observaciones o {$where} GROUP BY mes ORDER BY FIELD(mes, 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre')",
            $params
        );

        $porTipoError = $this->db->consultar(
            "SELECT tipo_error, COUNT(*) as total FROM observaciones o {$where} GROUP BY tipo_error ORDER BY total DESC",
            $params
        );

        return [
            'total' => (int) ($total['total'] ?? 0),
            'por_estado' => $porEstado,
            'por_mes' => $porMes,
            'por_tipo_error' => $porTipoError
        ];
    }

    /**
     * Verificar si un usuario tiene acceso a un establecimiento para un mes
     */
    private function tieneAcceso($usuarioId, $establecimientoId, $anio, $mes)
    {
        return $this->asignacion->tieneAsignacionParaMes($usuarioId, $establecimientoId, $anio, $mes);
    }

    /**
     * Verificar asignación para mes específico
     */
    private function tieneAsignacion($usuarioId, $establecimientoId, $anio, $mes)
    {
        return $this->asignacion->tieneAsignacionParaMes($usuarioId, $establecimientoId, $anio, $mes);
    }

    /**
     * Obtener IDs de establecimientos asignados a un registrador
     */
    private function obtenerEstablecimientosAsignados($usuarioId, $anio)
    {
        $sql = "SELECT DISTINCT establecimiento_id 
                FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND anio = ?";
        $filas = $this->db->consultar($sql, [$usuarioId, $anio]);
        return array_map(fn($f) => (int) $f['establecimiento_id'], $filas);
    }

    /**
     * Obtener comuna de un establecimiento
     */
    private function obtenerComunaEstablecimiento($establecimientoId)
    {
        $fila = $this->db->consultarUno("SELECT comuna_id FROM establecimientos WHERE id = ?", [$establecimientoId]);
        return $fila ? (int) $fila['comuna_id'] : null;
    }

    /**
     * Obtener errores para informe trimestral/anual (tipo_error = 'ERROR')
     * Solo para supervisores, ordena por comuna → establecimiento → mes
     */
    public function obtenerErroresInforme($anio, $trimestre = null, $usuarioId = null, $rol = null)
    {
        $sql = "SELECT
                    c.nombre as comuna_nombre,
                    e.nombre as establecimiento_nombre,
                    e.nombre_corto,
                    e.codigo_establecimiento,
                    o.mes,
                    o.codigo_serie,
                    o.codigo_hoja,
                    o.detalle_observacion,
                    o.clasificacion,
                    o.detalle_error,
                    o.estado_actual,
                    o.fecha_creacion
                FROM observaciones o
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                WHERE o.anio = ? AND o.tipo_error = 'ERROR'";

        $params = [$anio];

        if ($trimestre !== null) {
            $trimestres = [
                1 => ['Enero', 'Febrero', 'Marzo'],
                2 => ['Abril', 'Mayo', 'Junio'],
                3 => ['Julio', 'Agosto', 'Septiembre'],
                4 => ['Octubre', 'Noviembre', 'Diciembre'],
            ];
            $meses = $trimestres[$trimestre] ?? [];
            if (!empty($meses)) {
                $placeholders = implode(',', array_fill(0, count($meses), '?'));
                $sql .= " AND o.mes IN ($placeholders)";
                $params = array_merge($params, $meses);
            }
        }

        $sql .= " ORDER BY c.nombre,
                    CASE
                        WHEN e.nombre LIKE '%HOSPITAL%' THEN 1
                        WHEN e.nombre LIKE '%CESFAM%' THEN 2
                        WHEN e.nombre LIKE '%CECOSF%' THEN 3
                        WHEN e.nombre LIKE '%POSTA%' THEN 4
                        ELSE 5
                    END,
                    e.nombre,
                    FIELD(o.mes, 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre')";

        return $this->db->consultar($sql, $params);
    }
}
