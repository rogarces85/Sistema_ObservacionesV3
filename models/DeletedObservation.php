<?php
/**
 * Clase DeletedObservation
 * Manejo de observaciones eliminadas (papelera de reciclaje)
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config/constants.php';

class DeletedObservation
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Mover observación a la papelera (soft delete)
     */
    public function moveToTrash($observacionId, $supervisorId, $reason = '')
    {
        try {
            // Obtener datos de la observación
            $sql = "SELECT o.*, e.nombre as establecimiento, e.nombre_corto,
                           c.nombre as comuna, ur.nombre_completo as nombre_registro
                    FROM observaciones o
                    INNER JOIN establecimientos e ON o.establecimiento_id = e.id
                    INNER JOIN comunas c ON e.comuna_id = c.id
                    INNER JOIN usuarios ur ON o.usuario_registro_id = ur.id
                    WHERE o.id = ?";
            
            $obs = $this->db->queryOne($sql, [$observacionId]);
            
            if (!$obs) {
                return false;
            }

            // Insertar en la tabla de eliminadas
            $sql = "INSERT INTO observaciones_eliminadas 
                    (observacion_id, anio, mes, establecimiento_id, establecimiento_nombre, 
                     establecimiento_nombre_corto, comuna, codigo_serie, codigo_hoja, 
                     tipo_error, detalle_observacion, plazo_entrega, usa_validador, 
                     estado_actual, clasificacion, usuario_registro_id, nombre_registro,
                     usuario_supervisor_id, motivo_eliminacion, fecha_eliminacion, 
                     fecha_registro_original)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $this->db->execute($sql, [
                $observacionId,
                $obs['anio'],
                $obs['mes'],
                $obs['establecimiento_id'],
                $obs['establecimiento'],
                $obs['nombre_corto'],
                $obs['comuna'],
                $obs['codigo_serie'],
                $obs['codigo_hoja'],
                $obs['tipo_error'],
                $obs['detalle_observacion'],
                $obs['plazo_entrega'],
                $obs['usa_validador'],
                $obs['estado_actual'],
                $obs['clasificacion'],
                $obs['usuario_registro_id'],
                $obs['nombre_registro'],
                $supervisorId,
                $reason,
                date('Y-m-d H:i:s'),
                $obs['fecha_registro']
            ]);

            // Eliminar la observación original
            $this->db->execute("DELETE FROM observaciones WHERE id = ?", [$observacionId]);

            return true;
        } catch (Exception $e) {
            error_log("Error al mover observación a papelera: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todas las observaciones eliminadas con filtros
     */
    public function getAll($filters = [])
    {
        $sql = "SELECT * FROM observaciones_eliminadas WHERE 1=1";
        $params = [];

        if (!empty($filters['anio'])) {
            $sql .= " AND anio = ?";
            $params[] = $filters['anio'];
        }

        if (!empty($filters['comuna_nombre'])) {
            $sql .= " AND comuna LIKE ?";
            $params[] = '%' . $filters['comuna_nombre'] . '%';
        }

        if (!empty($filters['establecimiento_id'])) {
            $sql .= " AND establecimiento_id = ?";
            $params[] = $filters['establecimiento_id'];
        }

        if (!empty($filters['usuario_registro_id'])) {
            $sql .= " AND usuario_registro_id = ?";
            $params[] = $filters['usuario_registro_id'];
        }

        if (!empty($filters['mes'])) {
            $sql .= " AND mes = ?";
            $params[] = $filters['mes'];
        }

        if (!empty($filters['busqueda'])) {
            $sql .= " AND (detalle_observacion LIKE ? OR tipo_error LIKE ? OR establecimiento_nombre LIKE ?)";
            $searchTerm = '%' . $filters['busqueda'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY fecha_eliminacion DESC";

        return $this->db->query($sql, $params);
    }

    /**
     * Restaurar una observación eliminada
     */
    public function restore($deletedId, $supervisorId)
    {
        try {
            $sql = "SELECT * FROM observaciones_eliminadas WHERE id = ?";
            $deleted = $this->db->queryOne($sql, [$deletedId]);

            if (!$deleted) {
                return false;
            }

            // Reinsertar en la tabla original
            $sql = "INSERT INTO observaciones 
                    (id, anio, mes, establecimiento_id, codigo_serie, codigo_hoja, 
                     tipo_error, detalle_observacion, plazo_entrega, usa_validador, 
                     estado_actual, clasificacion, usuario_registro_id, usuario_supervisor_id, 
                     fecha_registro, fecha_revision)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $this->db->execute($sql, [
                $deleted['observacion_id'],
                $deleted['anio'],
                $deleted['mes'],
                $deleted['establecimiento_id'],
                $deleted['codigo_serie'],
                $deleted['codigo_hoja'],
                $deleted['tipo_error'],
                $deleted['detalle_observacion'],
                $deleted['plazo_entrega'],
                $deleted['usa_validador'],
                $deleted['estado_actual'],
                $deleted['clasificacion'],
                $deleted['usuario_registro_id'],
                $deleted['usuario_supervisor_id'],
                $deleted['fecha_registro_original'],
                $deleted['fecha_revision'] ?? null
            ]);

            // Eliminar de la tabla de eliminadas
            $this->db->execute("DELETE FROM observaciones_eliminadas WHERE id = ?", [$deletedId]);

            // Registrar en historial
            $this->db->execute(
                "INSERT INTO historial_estados (observacion_id, estado_anterior, estado_nuevo, usuario_id, comentario) 
                 VALUES (?, 'eliminado', ?, ?, 'Observación restaurada desde papelera')",
                [$deleted['observacion_id'], $deleted['estado_actual'], $supervisorId]
            );

            return true;
        } catch (Exception $e) {
            error_log("Error al restaurar observación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar permanentemente una observación
     */
    public function permanentDelete($deletedId)
    {
        try {
            $sql = "DELETE FROM observaciones_eliminadas WHERE id = ?";
            return $this->db->execute($sql, [$deletedId]);
        } catch (Exception $e) {
            error_log("Error al eliminar permanentemente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas de observaciones eliminadas
     */
    public function getStats($year = null)
    {
        $where = $year ? "WHERE anio = ?" : "";
        $params = $year ? [$year] : [];

        // Total eliminadas
        $sql = "SELECT COUNT(*) as total FROM observaciones_eliminadas {$where}";
        $total = $this->db->queryOne($sql, $params);

        // Por estado original
        $sql = "SELECT estado_actual, COUNT(*) as total 
                FROM observaciones_eliminadas 
                {$where}
                GROUP BY estado_actual";
        $porEstado = $this->db->query($sql, $params);

        // Por mes
        $sql = "SELECT mes, COUNT(*) as total 
                FROM observaciones_eliminadas 
                {$where}
                GROUP BY mes";
        $porMes = $this->db->query($sql, $params);

        // Por eliminador
        $sql = "SELECT u.nombre_completo, COUNT(*) as total 
                FROM observaciones_eliminadas oe
                INNER JOIN usuarios u ON oe.usuario_supervisor_id = u.id
                {$where}
                GROUP BY u.nombre_completo
                ORDER BY total DESC";
        $porEliminador = $this->db->query($sql, $params);

        return [
            'total' => $total['total'] ?? 0,
            'por_estado' => $porEstado,
            'por_mes' => $porMes,
            'por_eliminador' => $porEliminador
        ];
    }
}
