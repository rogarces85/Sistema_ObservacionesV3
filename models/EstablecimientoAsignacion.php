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
     * Asignar un establecimiento a un registrador para un año
     */
    public function asignar($usuarioId, $establecimientoId, $anio)
    {
        $sql = "SELECT COUNT(*) as count FROM asignaciones_establecimientos 
                WHERE usuario_id = ? AND establecimiento_id = ? AND anio = ?";
        $result = $this->db->queryOne($sql, [$usuarioId, $establecimientoId, $anio]);
        
        if ($result && $result['count'] > 0) {
            return false;
        }

        $sql = "INSERT INTO asignaciones_establecimientos (usuario_id, establecimiento_id, anio) 
                VALUES (?, ?, ?)";
        
        try {
            $this->db->execute($sql, [$usuarioId, $establecimientoId, $anio]);
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
    public function asignarMultiple($usuarioId, $establecimientoIds, $anio)
    {
        try {
            $this->removerTodas($usuarioId, $anio);
            
            $sql = "INSERT INTO asignaciones_establecimientos (usuario_id, establecimiento_id, anio) 
                    VALUES (?, ?, ?)";
            
            foreach ($establecimientoIds as $establecimientoId) {
                $this->db->execute($sql, [$usuarioId, $establecimientoId, $anio]);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error al asignar múltiples establecimientos: " . $e->getMessage());
            return false;
        }
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
            $sql = "INSERT INTO asignaciones_establecimientos (usuario_id, establecimiento_id, anio)
                    SELECT usuario_id, establecimiento_id, ? 
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
