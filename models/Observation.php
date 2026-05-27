<?php
/**
 * Clase Observation
 * Manejo de observaciones REM y historial
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config/constants.php';

class Observation
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener todas las observaciones con filtros
     */
    public function getAll($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT o.*, 
                e.nombre as establecimiento, 
                e.nombre_corto,
                e.codigo_establecimiento,
                c.nombre as comuna,
                c.codigo_comuna,
                ur.nombre_completo as nombre_registro,
                us.nombre_completo as nombre_supervisor
                FROM observaciones o
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                INNER JOIN usuarios ur ON o.usuario_registro_id = ur.id
                LEFT JOIN usuarios us ON o.usuario_supervisor_id = us.id
                WHERE o.anio = ?";

        $params = [$year];

        // Si es registrador, solo ver sus propias observaciones
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }

        $sql .= " ORDER BY o.fecha_registro DESC";

        return $this->db->query($sql, $params);
    }

    /**
     * Obtener observación por ID
     */
    public function getById($id)
    {
        $sql = "SELECT o.*, 
                e.nombre as establecimiento, 
                e.nombre_corto,
                e.codigo_establecimiento,
                c.nombre as comuna,
                c.codigo_comuna,
                c.id as comuna_id,
                ur.nombre_completo as nombre_registro,
                us.nombre_completo as nombre_supervisor
                FROM observaciones o
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                INNER JOIN usuarios ur ON o.usuario_registro_id = ur.id
                LEFT JOIN usuarios us ON o.usuario_supervisor_id = us.id
                WHERE o.id = ?";

        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Crear nueva observación
     */
    public function create($data)
    {
        $sql = "INSERT INTO observaciones 
                (anio, mes, establecimiento_id, codigo_serie, codigo_hoja, tipo_error, 
                detalle_observacion, plazo_entrega, usa_validador, usuario_registro_id, estado_actual,
                respuesta_establecimiento, clasificacion, detalle_error)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $data['anio'],
            $data['mes'],
            $data['establecimiento_id'],
            $data['codigo_serie'],
            $data['codigo_hoja'],
            $data['tipo_error'],
            $data['detalle_observacion'],
            $data['plazo_entrega'],
            $data['usa_validador'],
            $data['usuario_registro_id'],
            $data['estado_actual'] ?? ESTADO_PENDIENTE,
            $data['respuesta_establecimiento'] ?? null,
            $data['clasificacion'] ?? null,
            $data['detalle_error'] ?? null
        ];

        try {
            $this->db->execute($sql, $params);
            $newId = $this->db->lastInsertId();

            // Registrar en el historial
            $this->addHistorial($newId, '', ESTADO_PENDIENTE, $data['usuario_registro_id'], 'Registro inicial');

            return $newId;
        } catch (Exception $e) {
            error_log("Error al crear observación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar observación
     */
    public function update($id, $data, $userId)
    {
        // Obtener estado anterior
        $obsAnterior = $this->getById($id);

        $fields = [];
        $params = [];

        // Campos actualizables
        $allowedFields = [
            'mes',
            'establecimiento_id',
            'codigo_serie',
            'codigo_hoja',
            'tipo_error',
            'detalle_observacion',
            'plazo_entrega',
            'usa_validador',
            'estado_actual',
            'clasificacion',
            'usuario_supervisor_id',
            'respuesta_establecimiento',
            'detalle_error'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        // Si se actualiza el estado, registrar fecha de revisión
        if (isset($data['estado_actual']) && $data['estado_actual'] !== $obsAnterior['estado_actual']) {
            $fields[] = "fecha_revision = NOW()";

            // Registrar en historial
            $comentario = $data['clasificacion'] ?? 'Cambio de estado';
            $this->addHistorial($id, $obsAnterior['estado_actual'], $data['estado_actual'], $userId, $comentario);
        }

        $params[] = $id;
        $sql = "UPDATE observaciones SET " . implode(', ', $fields) . " WHERE id = ?";

        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Error al actualizar observación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar observación
     */
    public function delete($id)
    {
        // El historial se eliminará en cascada por la FK
        $sql = "DELETE FROM observaciones WHERE id = ?";

        try {
            return $this->db->execute($sql, [$id]);
        } catch (Exception $e) {
            error_log("Error al eliminar observación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener historial de una observación
     */
    public function getHistorial($observacionId)
    {
        $sql = "SELECT h.*, u.nombre_completo as usuario_nombre
                FROM historial_estados h
                INNER JOIN usuarios u ON h.usuario_id = u.id
                WHERE h.observacion_id = ?
                ORDER BY h.fecha_cambio DESC";

        return $this->db->query($sql, [$observacionId]);
    }

    /**
     * Agregar entrada al historial
     */
    private function addHistorial($observacionId, $estadoAnterior, $estadoNuevo, $usuarioId, $comentario = '')
    {
        $sql = "INSERT INTO historial_estados 
                (observacion_id, estado_anterior, estado_nuevo, usuario_id, comentario)
                VALUES (?, ?, ?, ?, ?)";

        try {
            return $this->db->execute($sql, [$observacionId, $estadoAnterior, $estadoNuevo, $usuarioId, $comentario]);
        } catch (Exception $e) {
            error_log("Error al registrar historial: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estadísticas para dashboard
     */
    public function getStats($year, $userId = null, $userRole = null)
    {
        $where = "WHERE o.anio = ?";
        $params = [$year];

        if ($userRole === ROL_REGISTRADOR && $userId) {
            $where .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }

        // Total por estado
        $sql = "SELECT estado_actual, COUNT(*) as total
                FROM observaciones o
                {$where}
                GROUP BY estado_actual";

        $estadoStats = $this->db->query($sql, $params);

        // Total por mes
        $sql = "SELECT mes, COUNT(*) as total
                FROM observaciones o
                {$where}
                GROUP BY mes";

        $mesStats = $this->db->query($sql, $params);

        // Total por tipo de error
        $sql = "SELECT tipo_error, COUNT(*) as total
                FROM observaciones o
                {$where}
                GROUP BY tipo_error
                ORDER BY total DESC
                LIMIT 10";

        $tipoErrorStats = $this->db->query($sql, $params);

        // Total general
        $sql = "SELECT COUNT(*) as total FROM observaciones o {$where}";
        $totalGeneral = $this->db->queryOne($sql, $params);

        return [
            'total' => $totalGeneral['total'] ?? 0,
            'por_estado' => $estadoStats,
            'por_mes' => $mesStats,
            'por_tipo_error' => $tipoErrorStats
        ];
    }

    /**
     * Actualizar estado de observación (para supervisores)
     */
    public function updateStatus($id, $newStatus, $supervisorId, $comment = null, $extraData = [])
    {
        // Obtener estado anterior
        $obs = $this->getById($id);
        if (!$obs) {
            return false;
        }

        $fields = ["estado_actual = ?", "usuario_supervisor_id = ?", "fecha_revision = NOW()"];
        $params = [$newStatus, $supervisorId];

        if (!empty($extraData['clasificacion'])) {
            $fields[] = "clasificacion = ?";
            $params[] = $extraData['clasificacion'];
        }
        if (isset($extraData['detalle_error'])) {
            $fields[] = "detalle_error = ?";
            $params[] = $extraData['detalle_error'];
        }
        if (!empty($extraData['tipo_error'])) {
            $fields[] = "tipo_error = ?";
            $params[] = $extraData['tipo_error'];
        }

        $params[] = $id;
        $sql = "UPDATE observaciones SET " . implode(', ', $fields) . " WHERE id = ?";

        try {
            $result = $this->db->execute($sql, $params);

            if ($result) {
                // Registrar en historial
                $this->addHistorial($id, $obs['estado_actual'], $newStatus, $supervisorId, $comment ?? 'Cambio de estado');
            }

            return $result;
        } catch (Exception $e) {
            error_log("Error al actualizar estado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar observación con registro de auditoría
     */
    public function deleteWithAudit($id, $supervisorId, $reason = 'Eliminado por supervisor')
    {
        try {
            // Registrar en historial antes de eliminar
            $obs = $this->getById($id);
            if (!$obs) {
                return false;
            }

            $this->addHistorial($id, $obs['estado_actual'], 'eliminado', $supervisorId, $reason);

            // Eliminar observación
            return $this->delete($id);
        } catch (Exception $e) {
            error_log("Error al eliminar observación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar estado de múltiples observaciones (operación masiva)
     */
    public function bulkUpdateStatus($ids, $newStatus, $supervisorId, $comment = null)
    {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }

        $successCount = 0;
        foreach ($ids as $id) {
            if ($this->updateStatus($id, $newStatus, $supervisorId, $comment)) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * Obtener observaciones con filtros (para supervisión)
     */
    public function getWithFilters($filters = [])
    {
        $sql = "SELECT o.*, 
                e.nombre as establecimiento, 
                e.nombre_corto,
                c.nombre as comuna,
                ur.nombre_completo as nombre_registro,
                us.nombre_completo as nombre_supervisor
                FROM observaciones o
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                INNER JOIN usuarios ur ON o.usuario_registro_id = ur.id
                LEFT JOIN usuarios us ON o.usuario_supervisor_id = us.id
                WHERE 1=1";

        $params = [];

        // Filtrar por año
        if (!empty($filters['anio'])) {
            $sql .= " AND o.anio = ?";
            $params[] = $filters['anio'];
        }

        // Filtrar por mes
        if (!empty($filters['mes'])) {
            $sql .= " AND o.mes = ?";
            $params[] = $filters['mes'];
        }

        // Filtrar por estado
        if (!empty($filters['estado'])) {
            $sql .= " AND o.estado_actual = ?";
            $params[] = $filters['estado'];
        }

        // Filtrar por establecimiento
        if (!empty($filters['establecimiento_id'])) {
            $sql .= " AND o.establecimiento_id = ?";
            $params[] = $filters['establecimiento_id'];
        }

        // Filtrar por registrador
        if (!empty($filters['usuario_registro_id'])) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $filters['usuario_registro_id'];
        }

        // Búsqueda de texto
        if (!empty($filters['busqueda'])) {
            $sql .= " AND (o.detalle_observacion LIKE ? OR o.tipo_error LIKE ?)";
            $searchTerm = '%' . $filters['busqueda'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY o.fecha_registro DESC";

        // Paginación
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT ?";
            $params[] = (int) $filters['limit'];

            if (!empty($filters['offset'])) {
                $sql .= " OFFSET ?";
                $params[] = (int) $filters['offset'];
            }
        }

        return $this->db->query($sql, $params);
    }

    /**
     * Reporte: observaciones agrupadas por mes
     */
    public function reportePorMes($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT o.mes, COUNT(*) as total 
                FROM observaciones o 
                WHERE o.anio = ?";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY o.mes ORDER BY FIELD(o.mes, 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre')";
        return $this->db->query($sql, $params);
    }

    /**
     * Reporte: observaciones agrupadas por establecimiento
     */
    public function reportePorEstablecimiento($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT e.id, e.nombre, e.nombre_corto, COUNT(*) as total 
                FROM observaciones o 
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id 
                WHERE o.anio = ?";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY e.id, e.nombre, e.nombre_corto ORDER BY total DESC";
        return $this->db->query($sql, $params);
    }

    /**
     * Reporte: observaciones agrupadas por comuna
     */
    public function reportePorComuna($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT c.id, c.nombre, COUNT(*) as total 
                FROM observaciones o 
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id 
                INNER JOIN comunas c ON e.comuna_id = c.id 
                WHERE o.anio = ?";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY c.id, c.nombre ORDER BY total DESC";
        return $this->db->query($sql, $params);
    }

    /**
     * Reporte: observaciones agrupadas por serie REM
     */
    public function reportePorSerie($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT o.codigo_serie, COUNT(*) as total 
                FROM observaciones o 
                WHERE o.anio = ? AND o.codigo_serie IS NOT NULL AND o.codigo_serie != ''";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY o.codigo_serie ORDER BY total DESC";
        return $this->db->query($sql, $params);
    }

    /**
     * Reporte: observaciones por plazo de entrega
     */
    public function reportePorPlazo($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT o.plazo_entrega, COUNT(*) as total 
                FROM observaciones o 
                WHERE o.anio = ? AND o.plazo_entrega IS NOT NULL AND o.plazo_entrega != ''";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY o.plazo_entrega";
        return $this->db->query($sql, $params);
    }

    /**
     * Reporte: observaciones por uso de validador
     */
    public function reportePorValidador($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT o.usa_validador, COUNT(*) as total 
                FROM observaciones o 
                WHERE o.anio = ? AND o.usa_validador IS NOT NULL AND o.usa_validador != ''";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY o.usa_validador";
        return $this->db->query($sql, $params);
    }

    /**
     * GRUPO A: Reportes de Errores (tipo_error = 'ERROR')
     */
    public function reporteErroresPorMes($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT o.mes, COUNT(*) as total 
                FROM observaciones o 
                WHERE o.anio = ? AND o.tipo_error = 'ERROR'";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY o.mes ORDER BY FIELD(o.mes, 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre')";
        return $this->db->query($sql, $params);
    }

    public function reporteErroresPorEstablecimiento($year, $userId = null, $userRole = null, $meses = [], $comunaIds = [])
    {
        $sql = "SELECT e.id, e.nombre, e.nombre_corto, COUNT(*) as total 
                FROM observaciones o 
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id 
                WHERE o.anio = ? AND o.tipo_error = 'ERROR'";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        if (!empty($meses)) {
            $placeholders = implode(',', array_fill(0, count($meses), '?'));
            $sql .= " AND o.mes IN ($placeholders)";
            $params = array_merge($params, $meses);
        }
        if (!empty($comunaIds)) {
            $placeholders = implode(',', array_fill(0, count($comunaIds), '?'));
            $sql .= " AND e.comuna_id IN ($placeholders)";
            $params = array_merge($params, $comunaIds);
        }
        $sql .= " GROUP BY e.id, e.nombre, e.nombre_corto ORDER BY total DESC";
        return $this->db->query($sql, $params);
    }

    public function reporteErroresPorComuna($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT c.id, c.nombre, COUNT(*) as total 
                FROM observaciones o 
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id 
                INNER JOIN comunas c ON e.comuna_id = c.id 
                WHERE o.anio = ? AND o.tipo_error = 'ERROR'";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY c.id, c.nombre ORDER BY total DESC";
        return $this->db->query($sql, $params);
    }

    /**
     * GRUPO B: Reportes Fuera de Plazo
     */
    public function reporteFueraPlazoPorMes($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT o.mes, COUNT(*) as total 
                FROM observaciones o 
                WHERE o.anio = ? AND o.plazo_entrega = 'fuera_plazo'";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY o.mes ORDER BY FIELD(o.mes, 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre')";
        return $this->db->query($sql, $params);
    }

    public function reporteFueraPlazoPorEstablecimiento($year, $userId = null, $userRole = null, $meses = [], $comunaIds = [])
    {
        $sql = "SELECT e.id, e.nombre, e.nombre_corto, COUNT(*) as total 
                FROM observaciones o 
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id 
                WHERE o.anio = ? AND o.plazo_entrega = 'fuera_plazo'";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        if (!empty($meses)) {
            $placeholders = implode(',', array_fill(0, count($meses), '?'));
            $sql .= " AND o.mes IN ($placeholders)";
            $params = array_merge($params, $meses);
        }
        if (!empty($comunaIds)) {
            $placeholders = implode(',', array_fill(0, count($comunaIds), '?'));
            $sql .= " AND e.comuna_id IN ($placeholders)";
            $params = array_merge($params, $comunaIds);
        }
        $sql .= " GROUP BY e.id, e.nombre, e.nombre_corto ORDER BY total DESC";
        return $this->db->query($sql, $params);
    }

    public function reporteFueraPlazoPorComuna($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT c.id, c.nombre, COUNT(*) as total 
                FROM observaciones o 
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id 
                INNER JOIN comunas c ON e.comuna_id = c.id 
                WHERE o.anio = ? AND o.plazo_entrega = 'fuera_plazo'";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY c.id, c.nombre ORDER BY total DESC";
        return $this->db->query($sql, $params);
    }

    /**
     * GRUPO C: Reportes de Uso del Validador
     */
    public function reporteValidadorPorMes($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT o.mes, COUNT(*) as total 
                FROM observaciones o 
                WHERE o.anio = ? AND o.usa_validador = 'si'";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY o.mes ORDER BY FIELD(o.mes, 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre')";
        return $this->db->query($sql, $params);
    }

    public function reporteValidadorPorEstablecimiento($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT e.id, e.nombre, e.nombre_corto, COUNT(*) as total 
                FROM observaciones o 
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id 
                WHERE o.anio = ? AND o.usa_validador = 'si'";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY e.id, e.nombre, e.nombre_corto ORDER BY total DESC";
        return $this->db->query($sql, $params);
    }

    public function reporteValidadorPorComuna($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT c.id, c.nombre, COUNT(*) as total 
                FROM observaciones o 
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id 
                INNER JOIN comunas c ON e.comuna_id = c.id 
                WHERE o.anio = ? AND o.usa_validador = 'si'";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY c.id, c.nombre ORDER BY total DESC";
        return $this->db->query($sql, $params);
    }

    /**
     * GRUPO D: Reporte por Serie REM × Tipo Error
     */
    public function reportePorSerieDetalle($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT o.codigo_serie, o.tipo_error, COUNT(*) as total 
                FROM observaciones o 
                WHERE o.anio = ? AND o.codigo_serie IS NOT NULL AND o.codigo_serie != ''";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY o.codigo_serie, o.tipo_error ORDER BY o.codigo_serie, total DESC";
        return $this->db->query($sql, $params);
    }

    /**
     * GRUPO D: Reporte por Hoja REM × Descripción
     */
    public function reportePorHojaDetalle($year, $userId = null, $userRole = null)
    {
        $sql = "SELECT o.codigo_hoja, o.tipo_error, o.detalle_observacion, COUNT(*) as total 
                FROM observaciones o 
                WHERE o.anio = ? AND o.codigo_hoja IS NOT NULL AND o.codigo_hoja != ''";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " GROUP BY o.codigo_hoja, o.tipo_error ORDER BY total DESC";
        return $this->db->query($sql, $params);
    }

    /**
     * Reporte detallado para PDF (jerárquico: comuna → establecimiento → mes)
     */
    public function reporteDetalladoPDF($filters = [], $userId = null, $userRole = null)
    {
        $sql = "SELECT 
                    c.nombre as comuna,
                    e.nombre as establecimiento,
                    e.nombre_corto,
                    o.mes,
                    o.codigo_serie,
                    o.codigo_hoja,
                    o.tipo_error,
                    o.detalle_observacion,
                    o.plazo_entrega,
                    o.usa_validador,
                    o.estado_actual,
                    o.clasificacion,
                    o.detalle_error,
                    o.fecha_registro,
                    ur.nombre_completo as registrador,
                    us.nombre_completo as supervisor
                FROM observaciones o
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                INNER JOIN usuarios ur ON o.usuario_registro_id = ur.id
                LEFT JOIN usuarios us ON o.usuario_supervisor_id = us.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['anio'])) {
            $sql .= " AND o.anio = ?";
            $params[] = $filters['anio'];
        }
        if (!empty($filters['comuna_id'])) {
            $sql .= " AND c.id = ?";
            $params[] = $filters['comuna_id'];
        }
        if (!empty($filters['establecimiento_id'])) {
            $sql .= " AND e.id = ?";
            $params[] = $filters['establecimiento_id'];
        }
        if (!empty($filters['mes'])) {
            $sql .= " AND o.mes = ?";
            $params[] = $filters['mes'];
        }
        if (!empty($filters['estado'])) {
            $sql .= " AND o.estado_actual = ?";
            $params[] = $filters['estado'];
        }
        if (!empty($filters['tipo_error'])) {
            $sql .= " AND o.tipo_error = ?";
            $params[] = $filters['tipo_error'];
        }

        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }

        $sql .= " ORDER BY c.nombre, e.nombre, FIELD(o.mes, 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'), o.fecha_registro";

        return $this->db->query($sql, $params);
    }

    /**
     * Obtener lista de comunas para filtros
     */
    public function getComunasConDatos($year = null, $userId = null, $userRole = null)
    {
        $sql = "SELECT DISTINCT c.id, c.nombre 
                FROM observaciones o
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                WHERE 1=1";
        $params = [];
        if ($year) {
            $sql .= " AND o.anio = ?";
            $params[] = $year;
        }
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " ORDER BY c.nombre";
        return $this->db->query($sql, $params);
    }

    /**
     * Obtener lista de establecimientos para filtros
     */
    public function getEstablecimientosConDatos($year = null, $comunaId = null, $userId = null, $userRole = null)
    {
        $sql = "SELECT DISTINCT e.id, e.nombre, e.nombre_corto, e.comuna_id
                FROM observaciones o
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id
                WHERE 1=1";
        $params = [];
        if ($year) {
            $sql .= " AND o.anio = ?";
            $params[] = $year;
        }
        if ($comunaId) {
            $sql .= " AND e.comuna_id = ?";
            $params[] = $comunaId;
        }
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        $sql .= " ORDER BY e.nombre";
        return $this->db->query($sql, $params);
    }

    /**
     * Reporte: No usa validador por establecimiento
     */
    public function reporteNoValidadorPorEstablecimiento($year, $userId = null, $userRole = null, $meses = [], $comunaIds = [])
    {
        $sql = "SELECT e.id, e.nombre, e.nombre_corto, COUNT(*) as total 
                FROM observaciones o 
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id 
                WHERE o.anio = ? AND o.usa_validador = 'no'";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        if (!empty($meses)) {
            $placeholders = implode(',', array_fill(0, count($meses), '?'));
            $sql .= " AND o.mes IN ($placeholders)";
            $params = array_merge($params, $meses);
        }
        if (!empty($comunaIds)) {
            $placeholders = implode(',', array_fill(0, count($comunaIds), '?'));
            $sql .= " AND e.comuna_id IN ($placeholders)";
            $params = array_merge($params, $comunaIds);
        }
        $sql .= " GROUP BY e.id, e.nombre, e.nombre_corto ORDER BY total DESC";
        return $this->db->query($sql, $params);
    }

    /**
     * Reporte: Errores por Serie REM (solo tipo_error = 'ERROR')
     */
    public function reporteErroresPorSerie($year, $userId = null, $userRole = null, $meses = [], $comunaIds = [])
    {
        $sql = "SELECT o.codigo_serie, COUNT(*) as total 
                FROM observaciones o 
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id 
                WHERE o.anio = ? AND o.tipo_error = 'ERROR' AND o.codigo_serie IS NOT NULL AND o.codigo_serie != ''";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        if (!empty($meses)) {
            $placeholders = implode(',', array_fill(0, count($meses), '?'));
            $sql .= " AND o.mes IN ($placeholders)";
            $params = array_merge($params, $meses);
        }
        if (!empty($comunaIds)) {
            $placeholders = implode(',', array_fill(0, count($comunaIds), '?'));
            $sql .= " AND e.comuna_id IN ($placeholders)";
            $params = array_merge($params, $comunaIds);
        }
        $sql .= " GROUP BY o.codigo_serie ORDER BY total DESC";
        return $this->db->query($sql, $params);
    }

    /**
     * Reporte: Errores por Hoja REM (solo tipo_error = 'ERROR')
     */
    public function reporteErroresPorHoja($year, $userId = null, $userRole = null, $meses = [], $comunaIds = [])
    {
        $sql = "SELECT o.codigo_hoja, COUNT(*) as total 
                FROM observaciones o 
                INNER JOIN establecimientos e ON o.establecimiento_id = e.id 
                WHERE o.anio = ? AND o.tipo_error = 'ERROR' AND o.codigo_hoja IS NOT NULL AND o.codigo_hoja != ''";
        $params = [$year];
        if ($userRole === ROL_REGISTRADOR && $userId) {
            $sql .= " AND o.usuario_registro_id = ?";
            $params[] = $userId;
        }
        if (!empty($meses)) {
            $placeholders = implode(',', array_fill(0, count($meses), '?'));
            $sql .= " AND o.mes IN ($placeholders)";
            $params = array_merge($params, $meses);
        }
        if (!empty($comunaIds)) {
            $placeholders = implode(',', array_fill(0, count($comunaIds), '?'));
            $sql .= " AND e.comuna_id IN ($placeholders)";
            $params = array_merge($params, $comunaIds);
        }
        $sql .= " GROUP BY o.codigo_hoja ORDER BY total DESC";
        return $this->db->query($sql, $params);
    }
}

