<?php
/**
 * API de Usuarios
 * Gestión completa de usuarios (solo supervisores)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/UserAudit.php';

// Función para responder en JSON
function jsonResponse($success, $data = null, $message = '', $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Función para generar contraseña aleatoria segura
function generateRandomPassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

// Función para validar política de contraseñas
function validatePasswordPolicy($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Z]/', $password)) return false; // Al menos una mayúscula
    if (!preg_match('/[0-9]/', $password)) return false; // Al menos un número
    return true;
}

// Verificar autenticación
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    jsonResponse(false, null, 'No autenticado', 401);
}

$userRole = $_SESSION['rol'];
$userId = $_SESSION['user_id'];

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

try {
    $userModel = new User();

    switch ($method) {
        case 'GET':
            if ($id) {
                // Obtener un usuario específico
                $user = $userModel->getById($id);
                if ($user) {
                    jsonResponse(true, $user);
                } else {
                    jsonResponse(false, null, 'Usuario no encontrado', 404);
                }
            } else {
                // Listar todos los usuarios (solo supervisores)
                if ($userRole !== ROL_SUPERVISOR) {
                    jsonResponse(false, null, 'Acceso denegado', 403);
                }

                $users = $userModel->getAll();
                jsonResponse(true, $users);
            }
            break;

        case 'POST':
            // Crear nuevo usuario (solo supervisores)
            if ($userRole !== ROL_SUPERVISOR) {
                jsonResponse(false, null, 'Acceso denegado', 403);
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $username = trim($input['username'] ?? '');
            $password = $input['password'] ?? '';
            $generatePassword = $input['generate_password'] ?? false;
            $nombreCompleto = trim($input['nombre_completo'] ?? '');
            $rol = $input['rol'] ?? 'registrador';

            // Validaciones
            if (empty($username) || empty($nombreCompleto)) {
                jsonResponse(false, null, 'Usuario y nombre completo son requeridos', 400);
            }

            if ($generatePassword) {
                $password = generateRandomPassword();
            } elseif (empty($password)) {
                jsonResponse(false, null, 'La contraseña es requerida o debe generar una aleatoria', 400);
            }

            if (!validatePasswordPolicy($password)) {
                jsonResponse(false, null, 'La contraseña debe tener al menos 8 caracteres, una mayúscula y un número', 400);
            }

            if (!in_array($rol, [ROL_REGISTRADOR, ROL_SUPERVISOR])) {
                jsonResponse(false, null, 'Rol inválido', 400);
            }

            // Crear usuario
            $newId = $userModel->create($username, $password, $nombreCompleto, $rol);

            if ($newId) {
                $audit = new UserAudit();
                $audit->logAction($newId, 'CREACION', "Usuario creado por supervisor ID: $userId");
                
                $response = ['id' => $newId];
                if ($generatePassword) {
                    $response['generated_password'] = $password;
                }
                jsonResponse(true, $response, 'Usuario creado exitosamente', 201);
            } else {
                jsonResponse(false, null, 'Error al crear usuario (el username podría estar duplicado)', 400);
            }
            break;

        case 'PUT':
            // Actualizar usuario
            if (!$id) {
                jsonResponse(false, null, 'ID de usuario requerido', 400);
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $action = $input['action'] ?? 'update';

            if ($action === 'password') {
                // Cambiar contraseña
                // Los usuarios pueden cambiar su propia contraseña
                // Los supervisores pueden cambiar cualquier contraseña
                if ($userRole !== ROL_SUPERVISOR && $userId != $id) {
                    jsonResponse(false, null, 'Solo puede cambiar su propia contraseña', 403);
                }

                $currentPassword = $input['current_password'] ?? '';
                $newPassword = $input['new_password'] ?? '';
                $confirmPassword = $input['confirm_password'] ?? '';

                // Si no es supervisor, debe proporcionar contraseña actual
                if ($userRole !== ROL_SUPERVISOR) {
                    if (empty($currentPassword)) {
                        jsonResponse(false, null, 'Contraseña actual requerida', 400);
                    }

                    // Verificar contraseña actual
                    $user = $userModel->getByIdWithPassword($id);
                    if (!password_verify($currentPassword, $user['password_hash'])) {
                        jsonResponse(false, null, 'Contraseña actual incorrecta', 400);
                    }
                }

                if (empty($newPassword) || empty($confirmPassword)) {
                    jsonResponse(false, null, 'Nueva contraseña y confirmación son requeridas', 400);
                }

                if ($newPassword !== $confirmPassword) {
                    jsonResponse(false, null, 'Las contraseñas no coinciden', 400);
                }

                if (!validatePasswordPolicy($newPassword)) {
                    jsonResponse(false, null, 'La contraseña debe tener al menos 8 caracteres, una mayúscula y un número', 400);
                }

                $success = $userModel->updatePassword($id, $newPassword);
                if ($success) {
                    $audit = new UserAudit();
                    $audit->logAction($id, 'CAMBIO_PASSWORD', "Contraseña actualizada por usuario ID: $userId");
                    jsonResponse(true, null, 'Contraseña actualizada exitosamente');
                } else {
                    jsonResponse(false, null, 'Error al actualizar contraseña', 500);
                }
            } elseif ($action === 'reset_password') {
                // Restablecer contraseña a credencial por defecto (solo supervisores)
                if ($userRole !== ROL_SUPERVISOR) {
                    jsonResponse(false, null, 'Acceso denegado', 403);
                }

                // No permitir restablecerse a sí mismo
                if ($userId == $id) {
                    jsonResponse(false, null, 'Use la sección de perfil para cambiar su propia contraseña', 400);
                }

                $defaultPassword = 'admin123';
                $success = $userModel->updatePassword($id, $defaultPassword);

                if ($success) {
                    jsonResponse(true, null, 'Contraseña restablecida exitosamente');
                } else {
                    jsonResponse(false, null, 'Error al restablecer contraseña', 500);
                }
            } elseif ($action === 'toggle') {
                // Activar/Desactivar usuario (solo supervisores)
                if ($userRole !== ROL_SUPERVISOR) {
                    jsonResponse(false, null, 'Acceso denegado', 403);
                }

                // No permitir desactivarse a sí mismo
                if ($userId == $id) {
                    jsonResponse(false, null, 'No puede desactivar su propia cuenta', 400);
                }

                $activo = $input['activo'] ?? true;
                $success = $userModel->setActive($id, $activo);

                if ($success) {
                    $audit = new UserAudit();
                    $estado = $activo ? 'ACTIVADO' : 'DESACTIVADO';
                    $audit->logAction($id, $estado, "Estado cambiado por supervisor ID: $userId");
                    jsonResponse(true, null, 'Estado de usuario actualizado');
                } else {
                    jsonResponse(false, null, 'Error al actualizar estado', 500);
                }
            } else {
                // Actualizar datos generales (solo supervisores)
                if ($userRole !== ROL_SUPERVISOR) {
                    jsonResponse(false, null, 'Acceso denegado', 403);
                }

                $nombreCompleto = trim($input['nombre_completo'] ?? '');
                $rol = $input['rol'] ?? '';

                if (empty($nombreCompleto) || empty($rol)) {
                    jsonResponse(false, null, 'Nombre completo y rol son requeridos', 400);
                }

                if (!in_array($rol, [ROL_REGISTRADOR, ROL_SUPERVISOR])) {
                    jsonResponse(false, null, 'Rol inválido', 400);
                }

                $success = $userModel->update($id, $nombreCompleto, $rol);

                if ($success) {
                    $audit = new UserAudit();
                    $audit->logAction($id, 'ACTUALIZACION', "Datos actualizados por supervisor ID: $userId. Nuevo rol: $rol");
                    jsonResponse(true, null, 'Usuario actualizado exitosamente');
                } else {
                    jsonResponse(false, null, 'Error al actualizar usuario', 500);
                }
            }
            break;

        case 'DELETE':
            // Eliminar usuario (solo supervisores)
            if ($userRole !== ROL_SUPERVISOR) {
                jsonResponse(false, null, 'Acceso denegado', 403);
            }

            if (!$id) {
                jsonResponse(false, null, 'ID de usuario requerido', 400);
            }

            // No permitir eliminarse a sí mismo
            if ($userId == $id) {
                jsonResponse(false, null, 'No puede eliminar su propia cuenta', 400);
            }

            $success = $userModel->delete($id);

            if ($success) {
                $audit = new UserAudit();
                $audit->logAction($id, 'ELIMINACION', "Usuario eliminado por supervisor ID: $userId");
                jsonResponse(true, null, 'Usuario eliminado exitosamente');
            } else {
                jsonResponse(false, null, 'Error al eliminar usuario', 500);
            }
            break;

        default:
            jsonResponse(false, null, 'Método no permitido', 405);
    }

} catch (Exception $e) {
    error_log("Error en API usuarios: " . $e->getMessage());
    jsonResponse(false, null, 'Error en el servidor: ' . $e->getMessage(), 500);
}
