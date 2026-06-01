<?php
/**
 * Clase User
 * Manejo de usuarios del sistema
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../config/constants.php';

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Autenticar usuario
     */
    public function authenticate($username, $password)
    {
        $sql = "SELECT * FROM usuarios WHERE username = ? AND activo = 1";
        $user = $this->db->queryOne($sql, [$username]);

        if ($user && password_verify($password, $user['password_hash'])) {
            // No retornar el hash de contraseña
            unset($user['password_hash']);
            return $user;
        }

        return false;
    }

    /**
     * Obtener usuario por ID
     */
    public function getById($id)
    {
        $sql = "SELECT id, username, nombre_completo, rol, activo, fecha_creacion 
                FROM usuarios WHERE id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Obtener todos los usuarios
     */
    public function getAll()
    {
        $sql = "SELECT id, username, nombre_completo, rol, activo, fecha_creacion 
                FROM usuarios ORDER BY nombre_completo ASC";
        return $this->db->query($sql);
    }

    /**
     * Obtener usuarios por rol
     */
    public function getByRole($rol)
    {
        $sql = "SELECT id, username, nombre_completo, rol, activo 
                FROM usuarios WHERE rol = ? AND activo = 1 ORDER BY nombre_completo ASC";
        return $this->db->query($sql, [$rol]);
    }

    /**
     * Verificar si el usuario está activo
     */
    public function isActive($id)
    {
        $sql = "SELECT activo FROM usuarios WHERE id = ?";
        $user = $this->db->queryOne($sql, [$id]);
        return $user && $user['activo'] == 1;
    }

    /**
     * Crear nuevo usuario
     */
    public function create($username, $password, $nombreCompleto, $rol)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (username, password_hash, nombre_completo, rol) 
                VALUES (?, ?, ?, ?)";

        try {
            $this->db->execute($sql, [$username, $passwordHash, $nombreCompleto, $rol]);
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword($id, $newPassword)
    {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE usuarios SET password_hash = ?, fecha_actualizacion = NOW() WHERE id = ?";

        try {
            return $this->db->execute($sql, [$passwordHash, $id]);
        } catch (Exception $e) {
            error_log("Error al actualizar contraseña: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Activar/Desactivar usuario
     */
    public function setActive($id, $activo)
    {
        $sql = "UPDATE usuarios SET activo = ?, fecha_actualizacion = NOW() WHERE id = ?";

        try {
            return $this->db->execute($sql, [$activo ? 1 : 0, $id]);
        } catch (Exception $e) {
            error_log("Error al cambiar estado de usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar datos del usuario
     */
    public function update($id, $nombreCompleto, $rol)
    {
        $sql = "UPDATE usuarios SET nombre_completo = ?, rol = ?, fecha_actualizacion = NOW() 
                WHERE id = ?";

        try {
            return $this->db->execute($sql, [$nombreCompleto, $rol, $id]);
        } catch (Exception $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar usuario
     */
    public function delete($id)
    {
        $sql = "DELETE FROM usuarios WHERE id = ?";

        try {
            return $this->db->execute($sql, [$id]);
        } catch (Exception $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener usuario por ID incluyendo password hash (para verificación)
     */
    public function getByIdWithPassword($id)
    {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        return $this->db->queryOne($sql, [$id]);
    }

    /**
     * Verificar si un username ya existe
     */
    public function usernameExists($username, $excludeId = null)
    {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM usuarios WHERE username = ? AND id != ?";
            $result = $this->db->queryOne($sql, [$username, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM usuarios WHERE username = ?";
            $result = $this->db->queryOne($sql, [$username]);
        }

        return $result && $result['count'] > 0;
    }
}
