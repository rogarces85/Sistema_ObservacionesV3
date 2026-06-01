<?php
/**
 * Clase HistorialUsuario
 * Registro de auditoría en tabla historial_usuarios
 * Sistema de Observaciones REM - Servicio de Salud Osorno
 */

require_once __DIR__ . '/../config/database.php';

class HistorialUsuario
{
    private $db;

    public function __construct()
    {
        $this->db = Database::obtenerInstancia();
    }

    /**
     * Registrar una acción en el historial
     *
     * @param int $usuarioId ID del usuario que realizó la acción
     * @param int|null $usuarioAfectadoId ID del usuario afectado (nullable)
     * @param string $accion Tipo de acción (CREACION, ACTIVACION, DESACTIVACION, CAMBIO_PASSWORD, etc.)
     * @param string $detalles Descripción de la acción
     */
    public function registrar($usuarioId, $usuarioAfectadoId, $accion, $detalles = '')
    {
        $sql = "INSERT INTO historial_usuarios (usuario_id, usuario_afectado_id, accion, detalles, fecha_creacion)
                VALUES (:usuario_id, :usuario_afectado_id, :accion, :detalles, NOW())";

        try {
            $this->db->ejecutar($sql, [
                'usuario_id' => $usuarioId,
                'usuario_afectado_id' => $usuarioAfectadoId,
                'accion' => $accion,
                'detalles' => $detalles
            ]);
            return true;
        } catch (Exception $e) {
            error_log("Error al registrar historial de usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener historial de un usuario específico
     */
    public function obtenerPorUsuario($usuarioId)
    {
        $sql = "SELECT h.*, u.nombre_completo as ejecutor_nombre
                FROM historial_usuarios h
                LEFT JOIN usuarios u ON h.usuario_id = u.id
                WHERE h.usuario_afectado_id = :usuario_id
                ORDER BY h.fecha_creacion DESC";

        return $this->db->consultar($sql, ['usuario_id' => $usuarioId]);
    }

    /**
     * Obtener todo el historial (para auditoría general)
     */
    public function obtenerTodo($limite = 100)
    {
        $sql = "SELECT h.*,
                        u1.nombre_completo as ejecutor_nombre,
                        u2.nombre_completo as afectado_nombre
                FROM historial_usuarios h
                LEFT JOIN usuarios u1 ON h.usuario_id = u1.id
                LEFT JOIN usuarios u2 ON h.usuario_afectado_id = u2.id
                ORDER BY h.fecha_creacion DESC
                LIMIT :limite";

        return $this->db->consultar($sql, ['limite' => $limite]);
    }
}
