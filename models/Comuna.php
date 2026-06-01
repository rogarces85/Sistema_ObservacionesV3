<?php
/**
 * Clase Comuna
 * Manejo de comunas (solo lectura)
 */

require_once __DIR__ . '/Database.php';

class Comuna
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener todas las comunas ordenadas por nombre
     */
    public function listar()
    {
        $sql = "SELECT * FROM comunas ORDER BY nombre ASC";
        return $this->db->query($sql);
    }

    /**
     * Obtener comuna por ID
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM comunas WHERE id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Obtener comuna por código
     */
    public function obtenerPorCodigo($codigo)
    {
        $sql = "SELECT * FROM comunas WHERE codigo_comuna = ?";
        return $this->db->queryOne($sql, [$codigo]);
    }
}
