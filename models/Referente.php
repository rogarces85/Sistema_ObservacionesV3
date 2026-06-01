<?php
/**
 * Clase Referente
 * Manejo de referentes de establecimientos
 */

require_once __DIR__ . '/Database.php';

class Referente
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Listar referentes por establecimiento con orden específico
     * Orden: Encargado Estadísticas → Digitador Estadísticas → alfabético por nombre
     */
    public function listarPorEstablecimiento($establecimientoId, $incluirInactivos = false)
    {
        $sql = "SELECT * FROM referentes_establecimientos 
                WHERE establecimiento_id = ?";
        $parametros = [$establecimientoId];

        if (!$incluirInactivos) {
            $sql .= " AND activo = 1";
        }

        $sql .= " ORDER BY 
                    CASE 
                        WHEN LOWER(cargo) LIKE '%encargado%' AND LOWER(cargo) LIKE '%estadistica%' THEN 1
                        WHEN LOWER(cargo) LIKE '%digitador%' AND LOWER(cargo) LIKE '%estadistica%' THEN 2
                        ELSE 3
                    END,
                    nombre ASC";

        return $this->db->query($sql, $parametros);
    }

    /**
     * Obtener referente por ID
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM referentes_establecimientos WHERE id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Validar formato de email
     */
    public function validarEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validar formato de teléfono (formato chileno)
     */
    public function validarTelefono($telefono)
    {
        $telefono = preg_replace('/[^0-9+]/', '', $telefono);
        return preg_match('/^(\+56)?[0-9]{8,12}$/', $telefono) || preg_match('/^[0-9]{7,12}$/', $telefono);
    }

    /**
     * Crear nuevo referente
     */
    public function crear($establecimientoId, $cargo, $nombre, $telefono, $email)
    {
        $sql = "INSERT INTO referentes_establecimientos 
                (establecimiento_id, cargo, nombre, telefono, email, activo) 
                VALUES (?, ?, ?, ?, ?, 1)";

        try {
            $this->db->execute($sql, [$establecimientoId, $cargo, $nombre, $telefono, $email]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error al crear referente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar referente
     */
    public function actualizar($id, $datos)
    {
        $campos = [];
        $parametros = [];

        if (isset($datos['cargo'])) {
            $campos[] = "cargo = ?";
            $parametros[] = $datos['cargo'];
        }
        if (isset($datos['nombre'])) {
            $campos[] = "nombre = ?";
            $parametros[] = $datos['nombre'];
        }
        if (isset($datos['telefono'])) {
            $campos[] = "telefono = ?";
            $parametros[] = $datos['telefono'];
        }
        if (isset($datos['email'])) {
            $campos[] = "email = ?";
            $parametros[] = $datos['email'];
        }
        if (isset($datos['activo'])) {
            $campos[] = "activo = ?";
            $parametros[] = $datos['activo'] ? 1 : 0;
        }

        if (empty($campos)) {
            return false;
        }

        $parametros[] = $id;
        $sql = "UPDATE referentes_establecimientos SET " . implode(', ', $campos) . " WHERE id = ?";

        try {
            return $this->db->execute($sql, $parametros);
        } catch (Exception $e) {
            error_log("Error al actualizar referente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggle($id, $activo)
    {
        $sql = "UPDATE referentes_establecimientos SET activo = ? WHERE id = ?";
        try {
            return $this->db->execute($sql, [$activo ? 1 : 0, $id]);
        } catch (Exception $e) {
            error_log("Error al cambiar estado referente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar referente (hard delete)
     */
    public function eliminar($id)
    {
        $sql = "DELETE FROM referentes_establecimientos WHERE id = ?";
        try {
            return $this->db->execute($sql, [$id]);
        } catch (Exception $e) {
            error_log("Error al eliminar referente: " . $e->getMessage());
            return false;
        }
    }
}
