<?php
/**
 * Clase Location
 * Manejo de comunas y establecimientos
 */

require_once __DIR__ . '/Database.php';

class Location
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtener todas las comunas
     */
    public function getAllComunas()
    {
        $sql = "SELECT * FROM comunas ORDER BY nombre ASC";
        return $this->db->query($sql);
    }

    /**
     * Alias para getAllComunas (compatibilidad)
     */
    public function getComunas()
    {
        return $this->getAllComunas();
    }

    /**
     * Obtener comuna por ID
     */
    public function getComunaById($id)
    {
        $sql = "SELECT * FROM comunas WHERE id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Obtener comuna por nombre
     */
    public function getComunaByNombre($nombre)
    {
        $sql = "SELECT * FROM comunas WHERE nombre = ?";
        return $this->db->queryOne($sql, [$nombre]);
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
                ORDER BY e.nombre ASC";
        return $this->db->query($sql);
    }

    /**
     * Obtener establecimientos por comuna
     */
    public function getEstablecimientosByComuna($comunaId)
    {
        $sql = "SELECT e.*, c.nombre as comuna_nombre 
                FROM establecimientos e
                INNER JOIN comunas c ON e.comuna_id = c.id
                WHERE e.comuna_id = ? AND e.activo = 1
                ORDER BY e.nombre ASC";
        return $this->db->query($sql, [$comunaId]);
    }

    /**
     * Obtener establecimiento por ID
     */
    public function getEstablecimientoById($id)
    {
        $sql = "SELECT e.*, c.nombre as comuna_nombre, c.codigo_comuna
                FROM establecimientos e
                INNER JOIN comunas c ON e.comuna_id = c.id
                WHERE e.id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Buscar establecimientos por nombre
     */
    public function searchEstablecimientos($searchTerm)
    {
        $sql = "SELECT e.*, c.nombre as comuna_nombre 
                FROM establecimientos e
                INNER JOIN comunas c ON e.comuna_id = c.id
                WHERE (e.nombre LIKE ? OR e.nombre_corto LIKE ?) AND e.activo = 1
                ORDER BY e.nombre ASC";

        $term = "%{$searchTerm}%";
        return $this->db->query($sql, [$term, $term]);
    }

    /**
     * Crear nueva comuna
     */
    public function createComuna($codigoComuna, $nombre)
    {
        $sql = "INSERT INTO comunas (codigo_comuna, nombre) VALUES (?, ?)";

        try {
            $this->db->execute($sql, [$codigoComuna, $nombre]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error al crear comuna: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear nuevo establecimiento
     */
    public function createEstablecimiento($codigoEstablecimiento, $nombre, $nombreCorto, $comunaId)
    {
        $sql = "INSERT INTO establecimientos (codigo_establecimiento, nombre, nombre_corto, comuna_id) 
                VALUES (?, ?, ?, ?)";

        try {
            $this->db->execute($sql, [$codigoEstablecimiento, $nombre, $nombreCorto, $comunaId]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error al crear establecimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar establecimiento
     */
    public function updateEstablecimiento($id, $data)
    {
        $fields = [];
        $params = [];

        if (isset($data['codigo_establecimiento'])) {
            $fields[] = "codigo_establecimiento = ?";
            $params[] = $data['codigo_establecimiento'];
        }
        if (isset($data['nombre'])) {
            $fields[] = "nombre = ?";
            $params[] = $data['nombre'];
        }
        if (isset($data['nombre_corto'])) {
            $fields[] = "nombre_corto = ?";
            $params[] = $data['nombre_corto'];
        }
        if (isset($data['comuna_id'])) {
            $fields[] = "comuna_id = ?";
            $params[] = $data['comuna_id'];
        }
        if (isset($data['activo'])) {
            $fields[] = "activo = ?";
            $params[] = $data['activo'] ? 1 : 0;
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE establecimientos SET " . implode(', ', $fields) . " WHERE id = ?";

        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            error_log("Error al actualizar establecimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Desactivar establecimiento (soft delete)
     */
    public function toggleEstablecimiento($id, $activo)
    {
        $sql = "UPDATE establecimientos SET activo = ? WHERE id = ?";
        try {
            return $this->db->execute($sql, [$activo ? 1 : 0, $id]);
        } catch (Exception $e) {
            error_log("Error al cambiar estado establecimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los establecimientos (incluye inactivos para admin)
     */
    public function getAllEstablecimientosConInactivos()
    {
        $sql = "SELECT e.*, c.nombre as comuna_nombre 
                FROM establecimientos e
                INNER JOIN comunas c ON e.comuna_id = c.id
                ORDER BY e.activo DESC, e.nombre ASC";
        return $this->db->query($sql);
    }

    /**
     * Verificar si un código de establecimiento ya existe
     */
    public function codigoEstablecimientoExiste($codigo, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM establecimientos WHERE codigo_establecimiento = ?";
        $params = [$codigo];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $result = $this->db->queryOne($sql, $params);
        return ($result['total'] ?? 0) > 0;
    }
}
