<?php
/**
 * Clase Establecimiento
 * Manejo de establecimientos de salud
 */

require_once __DIR__ . '/Database.php';

class Establecimiento
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Listar todos los establecimientos con filtros opcionales
     */
    public function listar($comunaId = null, $busqueda = null, $incluirInactivos = true)
    {
        $sql = "SELECT e.*, c.nombre as comuna_nombre 
                FROM establecimientos e
                INNER JOIN comunas c ON e.comuna_id = c.id
                WHERE 1=1";
        $parametros = [];

        if ($comunaId) {
            $sql .= " AND e.comuna_id = ?";
            $parametros[] = $comunaId;
        }

        if ($busqueda) {
            $sql .= " AND (e.nombre LIKE ? OR e.nombre_corto LIKE ? OR e.codigo_establecimiento LIKE ?)";
            $term = "%{$busqueda}%";
            $parametros[] = $term;
            $parametros[] = $term;
            $parametros[] = $term;
        }

        if (!$incluirInactivos) {
            $sql .= " AND e.activo = 1";
        }

        $sql .= " ORDER BY e.codigo_establecimiento ASC";

        return $this->db->query($sql, $parametros);
    }

    /**
     * Obtener establecimiento por ID
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT e.*, c.nombre as comuna_nombre, c.codigo_comuna
                FROM establecimientos e
                INNER JOIN comunas c ON e.comuna_id = c.id
                WHERE e.id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Verificar si un código de establecimiento ya existe
     */
    public function codigoExiste($codigo, $excluirId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM establecimientos WHERE codigo_establecimiento = ?";
        $parametros = [$codigo];
        if ($excluirId) {
            $sql .= " AND id != ?";
            $parametros[] = $excluirId;
        }
        $resultado = $this->db->queryOne($sql, $parametros);
        return ($resultado['total'] ?? 0) > 0;
    }

    /**
     * Crear nuevo establecimiento
     */
    public function crear($codigoEstablecimiento, $nombre, $nombreCorto, $comunaId)
    {
        $sql = "INSERT INTO establecimientos (codigo_establecimiento, nombre, nombre_corto, comuna_id, activo) 
                VALUES (?, ?, ?, ?, 1)";

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
    public function actualizar($id, $datos)
    {
        $campos = [];
        $parametros = [];

        if (isset($datos['nombre'])) {
            $campos[] = "nombre = ?";
            $parametros[] = $datos['nombre'];
        }
        if (isset($datos['nombre_corto'])) {
            $campos[] = "nombre_corto = ?";
            $parametros[] = $datos['nombre_corto'];
        }
        if (isset($datos['comuna_id'])) {
            $campos[] = "comuna_id = ?";
            $parametros[] = $datos['comuna_id'];
        }
        if (isset($datos['activo'])) {
            $campos[] = "activo = ?";
            $parametros[] = $datos['activo'] ? 1 : 0;
        }

        if (empty($campos)) {
            return false;
        }

        $parametros[] = $id;
        $sql = "UPDATE establecimientos SET " . implode(', ', $campos) . " WHERE id = ?";

        try {
            return $this->db->execute($sql, $parametros);
        } catch (Exception $e) {
            error_log("Error al actualizar establecimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggle($id, $activo)
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
     * Obtener estadísticas de establecimientos
     */
    public function estadisticas()
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as inactivos
                FROM establecimientos";
        return $this->db->queryOne($sql);
    }

    /**
     * Verificar si un establecimiento está activo
     */
    public function estaActivo($id)
    {
        $sql = "SELECT activo FROM establecimientos WHERE id = ?";
        $resultado = $this->db->queryOne($sql, [$id]);
        return $resultado && $resultado['activo'] == 1;
    }

    /**
     * Obtener conteo de referentes por establecimiento
     */
    public function contarReferentes($establecimientoId)
    {
        $sql = "SELECT COUNT(*) as total FROM referentes_establecimientos 
                WHERE establecimiento_id = ? AND activo = 1";
        $resultado = $this->db->queryOne($sql, [$establecimientoId]);
        return $resultado['total'] ?? 0;
    }
}
