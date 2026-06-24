<?php
/**
 * Modelo de notificaciones persistentes.
 */

require_once __DIR__ . '/Database.php';

class Notification
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function create($userId, $tipo, $titulo, $mensaje, $url = null)
    {
        $sql = "INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, url) VALUES (?, ?, ?, ?, ?)";
        try {
            $this->db->execute($sql, [$userId, $tipo, $titulo, $mensaje, $url]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log('Error al crear notificación: ' . $e->getMessage());
            return false;
        }
    }

    public function getForUser($userId, $limit = 10)
    {
        $sql = "SELECT * FROM notificaciones WHERE usuario_id = ? ORDER BY fecha_creacion DESC LIMIT ?";
        return $this->db->query($sql, [$userId, (int)$limit]);
    }

    public function countUnread($userId)
    {
        $row = $this->db->queryOne("SELECT COUNT(*) AS total FROM notificaciones WHERE usuario_id = ? AND leida = 0", [$userId]);
        return (int)($row['total'] ?? 0);
    }

    public function markRead($id, $userId)
    {
        return $this->db->execute(
            "UPDATE notificaciones SET leida = 1, fecha_lectura = NOW() WHERE id = ? AND usuario_id = ?",
            [$id, $userId]
        );
    }

    public function markAllRead($userId)
    {
        return $this->db->execute(
            "UPDATE notificaciones SET leida = 1, fecha_lectura = NOW() WHERE usuario_id = ? AND leida = 0",
            [$userId]
        );
    }
}
