<?php
/**
 * Clase PapeleraEliminada
 * Gestión de la papelera de reciclaje - Restaurar y eliminar permanentemente
 * Solo accesible para supervisores
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config/constants.php';

class PapeleraEliminada
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Listar observaciones eliminadas con filtros y paginación
     */
    public function listar($filtros = [])
    {
        $anio = $filtros['anio'] ?? date('Y');
        $pagina = max(1, (int) ($filtros['pagina'] ?? 1));
        $porPagina = 50;
        $offset = ($pagina - 1) * $porPagina;

        $sql = "SELECT oe.*,
                        e.nombre as establecimiento_nombre,
                        e.nombre_corto,
                        e.codigo_establecimiento,
                        c.nombre as comuna_nombre,
                        u.nombre_completo as eliminado_por_nombre,
                        ur.nombre_completo as registrador_nombre
                FROM observaciones_eliminadas oe
                INNER JOIN establecimientos e ON oe.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                INNER JOIN usuarios u ON oe.eliminado_por = u.id
                LEFT JOIN usuarios ur ON oe.observacion_original_id = (
                    SELECT o.usuario_registro_id FROM observaciones o WHERE o.id = oe.observacion_original_id
                )
                WHERE oe.anio = ?";
        $params = [$anio];

        if (!empty($filtros['mes'])) {
            $sql .= " AND oe.mes = ?";
            $params[] = $filtros['mes'];
        }

        if (!empty($filtros['comuna_id'])) {
            $sql .= " AND e.comuna_id = ?";
            $params[] = $filtros['comuna_id'];
        }

        if (!empty($filtros['establecimiento_id'])) {
            $sql .= " AND oe.establecimiento_id = ?";
            $params[] = $filtros['establecimiento_id'];
        }

        if (!empty($filtros['registrador_id'])) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM observaciones o 
                WHERE o.id = oe.observacion_original_id 
                AND o.usuario_registro_id = ?
            )";
            $params[] = $filtros['registrador_id'];
        }

        if (!empty($filtros['busqueda'])) {
            $termino = '%' . $filtros['busqueda'] . '%';
            $sql .= " AND (oe.detalle_correccion LIKE ? OR oe.motivo_eliminacion LIKE ? 
                        OR e.nombre LIKE ? OR e.nombre_corto LIKE ? 
                        OR oe.codigo_prestacion LIKE ? OR oe.nombre_prestacion LIKE ?)";
            $params[] = $termino;
            $params[] = $termino;
            $params[] = $termino;
            $params[] = $termino;
            $termino2 = '%' . $filtros['busqueda'] . '%';
            $params[] = $termino2;
            $params[] = $termino2;
        }

        $sqlConteo = "SELECT COUNT(*) as total FROM observaciones_eliminadas oe
                      INNER JOIN establecimientos e ON oe.establecimiento_id = e.id
                      WHERE oe.anio = ?";
        $paramsConteo = [$anio];

        if (!empty($filtros['mes'])) {
            $sqlConteo .= " AND oe.mes = ?";
            $paramsConteo[] = $filtros['mes'];
        }
        if (!empty($filtros['comuna_id'])) {
            $sqlConteo .= " AND e.comuna_id = ?";
            $paramsConteo[] = $filtros['comuna_id'];
        }
        if (!empty($filtros['establecimiento_id'])) {
            $sqlConteo .= " AND oe.establecimiento_id = ?";
            $paramsConteo[] = $filtros['establecimiento_id'];
        }
        if (!empty($filtros['registrador_id'])) {
            $sqlConteo .= " AND EXISTS (
                SELECT 1 FROM observaciones o 
                WHERE o.id = oe.observacion_original_id 
                AND o.usuario_registro_id = ?
            )";
            $paramsConteo[] = $filtros['registrador_id'];
        }
        if (!empty($filtros['busqueda'])) {
            $termino = '%' . $filtros['busqueda'] . '%';
            $sqlConteo .= " AND (oe.detalle_correccion LIKE ? OR oe.motivo_eliminacion LIKE ? 
                            OR e.nombre LIKE ? OR e.nombre_corto LIKE ?
                            OR oe.codigo_prestacion LIKE ? OR oe.nombre_prestacion LIKE ?)";
            $paramsConteo[] = $termino;
            $paramsConteo[] = $termino;
            $paramsConteo[] = $termino;
            $paramsConteo[] = $termino;
            $paramsConteo[] = $termino;
            $paramsConteo[] = $termino;
        }

        $resultadoConteo = $this->db->queryOne($sqlConteo, $paramsConteo);
        $total = (int) ($resultadoConteo['total'] ?? 0);
        $totalPaginas = ceil($total / $porPagina);

        $sql .= " ORDER BY oe.fecha_eliminacion DESC LIMIT ? OFFSET ?";
        $params[] = $porPagina;
        $params[] = $offset;

        $datos = $this->db->query($sql, $params);

        return [
            'datos' => $datos,
            'total' => $total,
            'pagina' => $pagina,
            'porPagina' => $porPagina,
            'totalPaginas' => $totalPaginas
        ];
    }

    /**
     * Obtener una observación eliminada por ID
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT oe.*,
                        e.nombre as establecimiento_nombre,
                        e.nombre_corto,
                        c.nombre as comuna_nombre,
                        u.nombre_completo as eliminado_por_nombre
                FROM observaciones_eliminadas oe
                INNER JOIN establecimientos e ON oe.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                INNER JOIN usuarios u ON oe.eliminado_por = u.id
                WHERE oe.id = ?";

        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Restaurar una observación eliminada (MOVE: copiar a observaciones + eliminar de eliminadas)
     */
    public function restaurar($id, $supervisorId)
    {
        $eliminada = $this->obtenerPorId($id);

        if (!$eliminada) {
            throw new Exception('Observación eliminada no encontrada', 404);
        }

        $db = Database::getInstance();
        $conexion = $db->getConnection();

        try {
            $conexion->beginTransaction();

            $sqlInsert = "INSERT INTO observaciones 
                          (usuario_registro_id, establecimiento_id, comuna_id, anio, mes,
                           codigo_serie, codigo_hoja, tipo_error, detalle_observacion,
                           plazo_entrega, anio_rem, mes_rem, estado_actual, clasificacion,
                           fecha_creacion, fecha_actualizacion)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $tipoError = $eliminada['estado_clasificacion'] ?? 'S/OBSERVACION';

            $params = [
                $supervisorId,
                $eliminada['establecimiento_id'],
                $eliminada['comuna_id'],
                $eliminada['anio'],
                $eliminada['mes'],
                $eliminada['serie'],
                $eliminada['hoja'],
                $tipoError,
                $eliminada['detalle_correccion'] ?? '',
                null,
                $eliminada['anio'],
                $eliminada['mes'],
                'pendiente',
                $eliminada['estado_clasificacion'],
                $eliminada['fecha_creacion']
            ];

            $this->db->execute($sqlInsert, $params);
            $nuevoId = $this->db->lastInsertId();

            $this->db->execute("DELETE FROM observaciones_eliminadas WHERE id = ?", [$id]);

            $this->db->execute(
                "INSERT INTO historial_observaciones (observacion_id, accion, usuario_id, fecha_creacion, detalles)
                 VALUES (?, 'restaurada', ?, NOW(), ?)",
                [$nuevoId, $supervisorId, 'Observación restaurada desde papelera de eliminadas']
            );

            $conexion->commit();

            return $nuevoId;

        } catch (Exception $e) {
            $conexion->rollBack();
            error_log("Error al restaurar observación: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Eliminar permanentemente una observación de la papelera
     */
    public function eliminarPermanente($id, $supervisorId)
    {
        $eliminada = $this->obtenerPorId($id);

        if (!$eliminada) {
            throw new Exception('Observación eliminada no encontrada', 404);
        }

        $this->db->execute(
            "INSERT INTO historial_observaciones (observacion_id, accion, usuario_id, fecha_creacion, detalles)
             VALUES (?, 'eliminacion_permanente', ?, NOW(), ?)",
            [$eliminada['observacion_original_id'], $supervisorId, 'Eliminación permanente desde papelera']
        );

        $this->db->execute("DELETE FROM observaciones_eliminadas WHERE id = ?", [$id]);

        return true;
    }

    /**
     * Restaurar múltiples observaciones (no transaccional, reporta fallos por ID)
     */
    public function restaurarMasivo($ids, $supervisorId)
    {
        $resultados = ['exitosos' => [], 'fallos' => []];

        foreach ($ids as $id) {
            try {
                $nuevoId = $this->restaurar($id, $supervisorId);
                $resultados['exitosos'][] = ['id' => $id, 'nuevo_id' => $nuevoId];
            } catch (Exception $e) {
                $resultados['fallos'][] = [
                    'id' => $id,
                    'error' => $e->getMessage(),
                    'codigo' => $e->getCode()
                ];
            }
        }

        return $resultados;
    }

    /**
     * Eliminar permanentemente múltiples observaciones (no transaccional, reporta fallos por ID)
     */
    public function eliminarPermanenteMasivo($ids, $supervisorId)
    {
        $resultados = ['exitosos' => [], 'fallos' => []];

        foreach ($ids as $id) {
            try {
                $this->eliminarPermanente($id, $supervisorId);
                $resultados['exitosos'][] = $id;
            } catch (Exception $e) {
                $resultados['fallos'][] = [
                    'id' => $id,
                    'error' => $e->getMessage(),
                    'codigo' => $e->getCode()
                ];
            }
        }

        return $resultados;
    }

    /**
     * Obtener estadísticas de observaciones eliminadas
     */
    public function estadisticas($anio = null)
    {
        $where = $anio ? "WHERE oe.anio = ?" : "";
        $params = $anio ? [$anio] : [];

        $total = $this->db->queryOne(
            "SELECT COUNT(*) as total FROM observaciones_eliminadas oe {$where}",
            $params
        );

        $porEstado = $this->db->query(
            "SELECT estado_clasificacion, COUNT(*) as total 
             FROM observaciones_eliminadas oe {$where}
             GROUP BY estado_clasificacion",
            $params
        );

        $porMes = $this->db->query(
            "SELECT mes, COUNT(*) as total 
             FROM observaciones_eliminadas oe {$where}
             GROUP BY mes 
             ORDER BY FIELD(mes, 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre')",
            $params
        );

        $porEliminador = $this->db->query(
            "SELECT u.nombre_completo, COUNT(*) as total 
             FROM observaciones_eliminadas oe
             INNER JOIN usuarios u ON oe.eliminado_por = u.id
             {$where}
             GROUP BY u.nombre_completo
             ORDER BY total DESC",
            $params
        );

        return [
            'total' => (int) ($total['total'] ?? 0),
            'por_estado' => $porEstado,
            'por_mes' => $porMes,
            'por_eliminador' => $porEliminador
        ];
    }
}
