<?php
/**
 * Clase EstablecimientoAsignacion
 * Manejo de asignaciones de establecimientos a registradores por año
 */

require_once __DIR__ . '/Database.php';

class EstablecimientoAsignacion
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener todos los registradores activos
     */
    public function getAllRegistradores()
    {
        $sql = "SELECT id, username, nombre_completo 
                FROM usuarios 
                WHERE rol = 'registrador' AND activo = 1 
                ORDER BY nombre_completo ASC";
        return $this->db->query($sql);
    }

    /**
     * Obtener establecimientos asignados a un registrador para un año específico
     */
    public function getEstablecimientosByRegistrador($registradorId, $anio)
    {
        $sql = "SELECT ae.id as asignacion_id, ae.anio, e.*, c.nombre as comuna_nombre 
                FROM asignaciones_establecimientos ae
                INNER JOIN establecimientos e ON ae.establecimiento_id = e.id
                INNER JOIN comunas c ON e.comuna_id = c.id
                WHERE ae.usuario_id = ? AND ae.anio = ?
                ORDER BY c.nombre ASC, e.nombre ASC";
        return $this->db->query($sql, [$registradorId, $anio]);
    }

    /**
     * Obtener todos los establecimientos
     */
    public function getAllEstablecimientos()
    {
        $sql = "SELECT e.*, c.nombre as comuna_nombre 
                FROM establecimientos e
                INNER JOIN comunas c ON e.comuna_id = c.id
                WHERE e.activo = 1
                ORDER BY c.nombre ASC, e.nombre ASC";
        return $this->db->query($sql);
    }

    /**
     * Obtener todos los establecimientos activos con información de asignación
     * para un año y registrador específico.
     *
     * Campos extra:
     *   asignado_a_mi  (0/1)
     *   asignado_a_usuario_id  (NULL o ID del dueño)
     *   asignado_a_nombre      (NULL o nombre del dueño)
     */
    public function getEstablecimientosConAsignacion($registradorId, $anio)
    {
        $sql = "SELECT e.*, c.nombre as comuna_nombre,
                       CASE WHEN ae_mi.usuario_id IS NOT NULL THEN 1 ELSE 0 END as asignado_a_mi,
                       ae_otro.usuario_id as asignado_a_usuario_id,
                       u.nombre_completo as asignado_a_nombre
                FROM establecimientos e
                INNER JOIN comunas c ON e.comuna_id = c.id
                LEFT JOIN asignaciones_establecimientos ae_mi
                       ON e.id = ae_mi.establecimiento_id AND ae_mi.anio = ? AND ae_mi.usuario_id = ?
                LEFT JOIN asignaciones_establecimientos ae_otro
                       ON e.id = ae_otro.establecimiento_id AND ae_otro.anio = ? AND ae_otro.usuario_id != ?
                LEFT JOIN usuarios u ON ae_otro.usuario_id = u.id
                WHERE e.activo = 1
                ORDER BY c.nombre ASC, e.nombre ASC";
        return $this->db->query($sql, [$anio, $registradorId, $anio, $registradorId]);
    }

    /**
     * Verificar si un establecimiento ya está asignado a otro registrador en un año
     */
    private function estaAsignadoAOtro($usuarioId, $establecimientoId, $anio)
    {
        $sql = "SELECT usuario_id FROM asignaciones_establecimientos 
                WHERE establecimiento_id = ? AND anio = ? AND usuario_id != ?
                LIMIT 1";
        $result = $this->db->queryOne($sql, [$establecimientoId, $anio, $usuarioId]);
        return $result ? $result['usuario_id'] : false;
    }

    /**
     * Asignar un establecimiento a un registrador para un año
     * @param string $meses 'ALL' o lista de meses '1,2,3'
     */
    public function asignar($usuarioId, $establecimientoId, $anio, $meses = 'ALL')
    {
        // Normalizar meses
        if (empty($meses) || $meses === 'ALL') {
            $meses = 'ALL';
        }

        // No permitir duplicados para el mismo usuario y periodo
        $sql = "SELECT COUNT(*) as count FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND establecimiento_id = ? AND anio = ?";
        $result = $this->db->queryOne($sql, [$usuarioId, $establecimientoId, $anio]);
        
        if ($result && $result['count'] > 0) {
            // Si ya existe, actualizamos los meses? 
            // Por ahora la spec dice no duplicados. Retornamos false.
            return false;
        }

        // No permitir asignar si ya está asignado a otro registrador
        if ($this->estaAsignadoAOtro($usuarioId, $establecimientoId, $anio)) {
            return false;
        }

        $sql = "INSERT INTO asignaciones_establecimientos (usuario_id, establecimiento_id, anio, meses) 
                VALUES (?, ?, ?, ?)";
        
        try {
            $this->db->execute($sql, [$usuarioId, $establecimientoId, $anio, $meses]);
            return true;
        } catch (Exception $e) {
            error_log("Error al asignar establecimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remover asignación
     */
    public function remover($usuarioId, $establecimientoId, $anio)
    {
        $sql = "DELETE FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND establecimiento_id = ? AND anio = ?";
        
        try {
            return $this->db->execute($sql, [$usuarioId, $establecimientoId, $anio]);
        } catch (Exception $e) {
            error_log("Error al remover asignación: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remover todas las asignaciones de un registrador para un año
     */
    public function removerTodas($usuarioId, $anio)
    {
        $sql = "DELETE FROM asignaciones_establecimientos WHERE usuario_id = ? AND anio = ?";
        
        try {
            return $this->db->execute($sql, [$usuarioId, $anio]);
        } catch (Exception $e) {
            error_log("Error al remover todas las asignaciones: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Asignar múltiples establecimientos a un registrador para un año
     */
    public function asignarMultiple($usuarioId, $establecimientoIds, $anio, $meses = 'ALL')
    {
        try {
            $this->db->beginTransaction();

            $this->removerTodas($usuarioId, $anio);
            
            $sql = "INSERT INTO asignaciones_establecimientos (usuario_id, establecimiento_id, anio, meses) 
                    VALUES (?, ?, ?, ?)";
            
            foreach ($establecimientoIds as $establecimientoId) {
                // Saltar si ya está asignado a otro registrador
                if ($this->estaAsignadoAOtro($usuarioId, $establecimientoId, $anio)) {
                    continue;
                }
                $this->db->execute($sql, [$usuarioId, $establecimientoId, $anio, $meses]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al asignar múltiples establecimientos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener IDs de establecimientos asignados a un registrador para un año
     */
    public function getIdsAsignados($usuarioId, $anio)
    {
        $sql = "SELECT establecimiento_id FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND anio = ?";
        $rows = $this->db->query($sql, [$usuarioId, $anio]);
        return array_map(fn($r) => (int)$r['establecimiento_id'], $rows);
    }

    /**
     * Verificar si un usuario tiene asignado un establecimiento para un mes específico
     */
    public function tieneAsignacionParaMes($usuarioId, $establecimientoId, $anio, $mesNombre)
    {
        // Mapeo de nombre de mes a número (1-12)
        $mesesMap = [
            'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4, 
            'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8, 
            'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
        ];
        
        $mesNum = $mesesMap[$mesNombre] ?? null;
        if (!$mesNum) return false; // Mes inválido

        // Obtener la asignación
        $sql = "SELECT meses FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND establecimiento_id = ? AND anio = ?";
        $row = $this->db->queryOne($sql, [$usuarioId, $establecimientoId, $anio]);

        if (!$row) return false;

        // Si es 'ALL', tiene acceso a todos los meses
        if ($row['meses'] === 'ALL' || empty($row['meses'])) {
            return true;
        }

        // Verificar si el mes está en la lista
        $mesesAsignados = array_map('intval', explode(',', $row['meses']));
        return in_array($mesNum, $mesesAsignados);
    }

    /**
     * Verificar si un registrador tiene establecimientos asignados para un año
     */
    public function tieneAsignaciones($usuarioId, $anio)
    {
        $sql = "SELECT COUNT(*) as count FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND anio = ?";
        $result = $this->db->queryOne($sql, [$usuarioId, $anio]);
        return $result && $result['count'] > 0;
    }

    /**
     * Obtener registradores que NO tienen establecimientos asignados para un año
     */
    public function getRegistradoresSinAsignaciones($anio)
    {
        $sql = "SELECT u.id, u.username, u.nombre_completo 
                FROM usuarios u
                WHERE u.rol = 'registrador' AND u.activo = 1
                  AND u.id NOT IN (
                      SELECT DISTINCT usuario_id 
                      FROM asignaciones_establecimientos 
                      WHERE anio = ?
                  )
                ORDER BY u.nombre_completo ASC";
        return $this->db->query($sql, [$anio]);
    }

    /**
     * Obtener estadísticas de asignaciones por registrador para un año
     */
    public function getEstadisticasAsignaciones($anio)
    {
        $sql = "SELECT u.id, u.nombre_completo, u.username,
                       COUNT(ae.establecimiento_id) as total_establecimientos
                FROM usuarios u
                LEFT JOIN asignaciones_establecimientos ae ON u.id = ae.usuario_id AND ae.anio = ?
                WHERE u.rol = 'registrador' AND u.activo = 1
                GROUP BY u.id, u.nombre_completo, u.username
                ORDER BY u.nombre_completo ASC";
        return $this->db->query($sql, [$anio]);
    }

    /**
     * Copiar asignaciones de un año a otro
     */
    public function copiarAsignaciones($anioOrigen, $anioDestino)
    {
        try {
            $sql = "INSERT INTO asignaciones_establecimientos (usuario_id, establecimiento_id, anio, meses)
                    SELECT usuario_id, establecimiento_id, ?, meses 
                    FROM asignaciones_establecimientos 
                    WHERE anio = ?";
            $this->db->execute($sql, [$anioDestino, $anioOrigen]);
            return true;
        } catch (Exception $e) {
            error_log("Error al copiar asignaciones: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener referentes de un establecimiento
     */
    public function getReferentes($establecimientoId)
    {
        $sql = "SELECT * FROM referentes_establecimientos 
                WHERE establecimiento_id = ? AND activo = 1
                ORDER BY FIELD(cargo, 'Encargado Estadísticas', 'Digitador Estadísticas') ASC";
        return $this->db->query($sql, [$establecimientoId]);
    }

    /**
     * Obtener referentes de múltiples establecimientos
     */
    public function getReferentesMultiple($establecimientoIds)
    {
        if (empty($establecimientoIds)) return [];
        
        $placeholders = implode(',', array_fill(0, count($establecimientoIds), '?'));
        $sql = "SELECT * FROM referentes_establecimientos 
                WHERE establecimiento_id IN ($placeholders) AND activo = 1
                ORDER BY establecimiento_id, FIELD(cargo, 'Encargado Estadísticas', 'Digitador Estadísticas') ASC";
        return $this->db->query($sql, $establecimientoIds);
    }
}
