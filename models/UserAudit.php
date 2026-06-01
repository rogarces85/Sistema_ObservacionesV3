<?php
/**
 * Clase UserAudit
 * Registro de auditoría para cambios en usuarios
 */

require_once __DIR__ . '/Database.php';

class UserAudit
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Registrar una acción de auditoría
     */
    public function logAction($userId, $action, $details = '')
    {
        $sql = "INSERT INTO historial_usuarios (usuario_id, accion, detalles, fecha_registro) 
                VALUES (?, ?, ?, NOW())";
        
        try {
            return $this->db->execute($sql, [$userId, $action, $details]);
        } catch (Exception $e) {
            error_log("Error al registrar auditoría de usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener historial de un usuario
     */
    public function getHistory($userId)
    {
        $sql = "SELECT h.*, u.nombre_completo as responsable_nombre 
                FROM historial_usuarios h
                LEFT JOIN usuarios u ON h.usuario_responsable_id = u.id
                WHERE h.usuario_id = ?
                ORDER BY h.fecha_registro DESC";
        
        return $this->db->query($sql, [$userId]);
    }
}
