<?php
/**
 * Clase HistorialEstado
 * Registro de cambios de estado de observaciones
 */

require_once __DIR__ . '/Database.php';

class HistorialEstado
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Registrar un cambio de estado
     */
    public function registrar($observacionId, $usuarioId, $estadoAnterior, $estadoNuevo, $comentario = '')
    {
        $sql = "INSERT INTO historial_estados 
                (observacion_id, usuario_id, estado_anterior, estado_nuevo, comentario, fecha_creacion)
                VALUES (?, ?, ?, ?, ?, NOW())";

        return $this->db->ejecutar($sql, [
            $observacionId,
            $usuarioId,
            $estadoAnterior,
            $estadoNuevo,
            $comentario
        ]);
    }

    /**
     * Obtener historial de una observación
     */
    public function obtenerPorObservacion($observacionId)
    {
        $sql = "SELECT h.*, u.nombre_completo as usuario_nombre
                FROM historial_estados h
                INNER JOIN usuarios u ON h.usuario_id = u.id
                WHERE h.observacion_id = ?
                ORDER BY h.fecha_creacion ASC";

        return $this->db->consultar($sql, [$observacionId]);
    }
}
