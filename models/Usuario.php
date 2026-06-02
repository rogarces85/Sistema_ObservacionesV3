<?php
/**
 * Clase Usuario
 * CRUD completo, cambio de contraseña, toggle activo
 * Sistema de Observaciones REM - Servicio de Salud Osorno
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';

class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = Database::obtenerInstancia();
    }

    /**
     * Obtener todos los usuarios
     */
    public function obtenerTodos()
    {
        $sql = "SELECT id, username, nombre_completo, rol, activo, fecha_creacion, fecha_actualizacion
                FROM usuarios ORDER BY nombre_completo ASC";
        return $this->db->consultar($sql);
    }

    /**
     * Obtener usuario por ID
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT id, username, nombre_completo, rol, activo, fecha_creacion, fecha_actualizacion
                FROM usuarios WHERE id = :id LIMIT 1";
        return $this->db->consultarUno($sql, ['id' => $id]);
    }

    /**
     * Obtener usuario con hash de contraseña (para verificación)
     */
    public function obtenerConPassword($id)
    {
        $sql = "SELECT * FROM usuarios WHERE id = :id LIMIT 1";
        return $this->db->consultarUno($sql, ['id' => $id]);
    }

    /**
     * Verificar si un username ya existe
     */
    public function usernameExiste($username, $excluirId = null)
    {
        if ($excluirId) {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE username = :username AND id != :id";
            $resultado = $this->db->consultarUno($sql, ['username' => $username, 'id' => $excluirId]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM usuarios WHERE username = :username";
            $resultado = $this->db->consultarUno($sql, ['username' => $username]);
        }
        return $resultado && (int)$resultado['total'] > 0;
    }

    /**
     * Validar formato de username (solo minúsculas, números, guión bajo, 4-50 chars)
     */
    public static function validarUsername($username)
    {
        if (empty($username)) {
            return ['valido' => false, 'error' => 'El nombre de usuario es requerido'];
        }
        if (strlen($username) < 4 || strlen($username) > 50) {
            return ['valido' => false, 'error' => 'El nombre de usuario debe tener entre 4 y 50 caracteres'];
        }
        if (!preg_match('/^[a-z0-9_]+$/', $username)) {
            return ['valido' => false, 'error' => 'El nombre de usuario solo puede contener letras minúsculas, números y guión bajo'];
        }
        return ['valido' => true, 'error' => ''];
    }

    /**
     * Validar política de contraseña (min 8, 1 mayúscula, 1 número)
     */
    public static function validarPassword($password)
    {
        if (empty($password)) {
            return ['valido' => false, 'error' => 'La contraseña es requerida'];
        }
        if (strlen($password) < 8) {
            return ['valido' => false, 'error' => 'La contraseña debe tener al menos 8 caracteres'];
        }
        if (!preg_match('/[A-Z]/', $password)) {
            return ['valido' => false, 'error' => 'La contraseña debe contener al menos una letra mayúscula'];
        }
        if (!preg_match('/[0-9]/', $password)) {
            return ['valido' => false, 'error' => 'La contraseña debe contener al menos un número'];
        }
        return ['valido' => true, 'error' => ''];
    }

    /**
     * Generar contraseña aleatoria segura de 12 caracteres
     */
    public static function generarPasswordAleatoria($longitud = 12)
    {
        $minusculas = 'abcdefghijklmnopqrstuvwxyz';
        $mayusculas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numeros = '0123456789';
        $especiales = '!@#$%^&*()';
        $todos = $minusculas . $mayusculas . $numeros . $especiales;

        // Asegurar al menos uno de cada tipo para complejidad
        $password = '';
        $password .= $minusculas[random_int(0, strlen($minusculas) - 1)];
        $password .= $mayusculas[random_int(0, strlen($mayusculas) - 1)];
        $password .= $numeros[random_int(0, strlen($numeros) - 1)];
        $password .= $especiales[random_int(0, strlen($especiales) - 1)];

        // Rellenar el resto
        for ($i = strlen($password); $i < $longitud; $i++) {
            $password .= $todos[random_int(0, strlen($todos) - 1)];
        }

        // Mezclar
        return str_shuffle($password);
    }

    /**
     * Crear nuevo usuario
     */
    public function crear($username, $password, $nombreCompleto, $rol)
    {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios (username, password_hash, nombre_completo, rol, activo, fecha_creacion, fecha_actualizacion)
                VALUES (:username, :password_hash, :nombre_completo, :rol, 1, NOW(), NOW())";

        try {
            $this->db->ejecutar($sql, [
                'username' => $username,
                'password_hash' => $passwordHash,
                'nombre_completo' => $nombreCompleto,
                'rol' => $rol
            ]);
            return $this->db->ultimoIdInsertado();
        } catch (Exception $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar datos del usuario
     */
    public function actualizar($id, $nombreCompleto, $rol)
    {
        $sql = "UPDATE usuarios SET nombre_completo = :nombre_completo, rol = :rol, fecha_actualizacion = NOW()
                WHERE id = :id";

        try {
            return $this->db->ejecutar($sql, [
                'id' => $id,
                'nombre_completo' => $nombreCompleto,
                'rol' => $rol
            ]);
        } catch (Exception $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambiar contraseña (requiere contraseña actual para cambio propio)
     */
    public function cambiarPassword($id, $passwordActual, $passwordNuevo)
    {
        $usuario = $this->obtenerConPassword($id);
        if (!$usuario) {
            return ['exito' => false, 'error' => 'Usuario no encontrado'];
        }

        if (!password_verify($passwordActual, $usuario['password_hash'])) {
            return ['exito' => false, 'error' => 'La contraseña actual es incorrecta'];
        }

        $passwordHash = password_hash($passwordNuevo, PASSWORD_BCRYPT);
        $sql = "UPDATE usuarios SET password_hash = :password_hash, fecha_actualizacion = NOW()
                WHERE id = :id";

        try {
            $this->db->ejecutar($sql, ['password_hash' => $passwordHash, 'id' => $id]);
            return ['exito' => true, 'error' => ''];
        } catch (Exception $e) {
            error_log("Error al cambiar contraseña: " . $e->getMessage());
            return ['exito' => false, 'error' => 'Error al cambiar la contraseña'];
        }
    }

    /**
     * Resetear contraseña a valor por defecto (solo supervisor, sin requerir contraseña actual)
     */
    public function resetearPassword($id, $passwordDefecto = 'admin123')
    {
        $passwordHash = password_hash($passwordDefecto, PASSWORD_BCRYPT);
        $sql = "UPDATE usuarios SET password_hash = :password_hash, fecha_actualizacion = NOW()
                WHERE id = :id";

        try {
            $this->db->ejecutar($sql, ['password_hash' => $passwordHash, 'id' => $id]);
            return ['exito' => true, 'error' => ''];
        } catch (Exception $e) {
            error_log("Error al resetear contraseña: " . $e->getMessage());
            return ['exito' => false, 'error' => 'Error al resetear la contraseña'];
        }
    }

    /**
     * Activar/Desactivar usuario
     */
    public function toggleActivo($id, $activo)
    {
        $sql = "UPDATE usuarios SET activo = :activo, fecha_actualizacion = NOW() WHERE id = :id";

        try {
            $this->db->ejecutar($sql, ['activo' => $activo ? 1 : 0, 'id' => $id]);
            return ['exito' => true, 'error' => ''];
        } catch (Exception $e) {
            error_log("Error al cambiar estado de usuario: " . $e->getMessage());
            return ['exito' => false, 'error' => 'Error al cambiar el estado del usuario'];
        }
    }

    /**
     * Eliminar usuario
     */
    public function eliminar($id)
    {
        $sql = "DELETE FROM usuarios WHERE id = :id";

        try {
            $this->db->ejecutar($sql, ['id' => $id]);
            return ['exito' => true, 'error' => ''];
        } catch (Exception $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
            return ['exito' => false, 'error' => 'Error al eliminar el usuario'];
        }
    }

    /**
     * Verificar si es el último supervisor activo
     */
    public function esUltimoSupervisorActivo($id)
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE rol = :rol AND activo = 1";
        $resultado = $this->db->consultarUno($sql, ['rol' => ROL_SUPERVISOR]);
        return (int)$resultado['total'] <= 1;
    }

    /**
     * Contar supervisores activos
     */
    public function contarSupervisoresActivos()
    {
        $sql = "SELECT COUNT(*) as total FROM usuarios WHERE rol = :rol AND activo = 1";
        $resultado = $this->db->consultarUno($sql, ['rol' => ROL_SUPERVISOR]);
        return (int)$resultado['total'];
    }
}
