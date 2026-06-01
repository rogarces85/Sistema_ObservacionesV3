<?php
/**
 * API de Usuarios
 * Gestión completa de usuarios (solo supervisores)
 * Acciones: listar, crear, actualizar, password, reset_password, toggle, eliminar
 * Sistema de Observaciones REM - Servicio de Salud Osorno
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/HistorialUsuario.php';

/**
 * Responder en formato JSON estandarizado
 */
function responderJson($exito, $datos = null, $error = '', $codigo = 200)
{
    http_response_code($codigo);
    $respuesta = ['success' => $exito, 'data' => $datos];
    if ($error !== '') {
        $respuesta['error'] = $error;
    }
    $respuesta['code'] = $codigo;
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Validar token CSRF
 */
function validarCsrf()
{
    $token = null;

    if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
    } elseif (isset($_POST['csrf_token'])) {
        $token = $_POST['csrf_token'];
    } else {
        $cuerpo = json_decode(file_get_contents('php://input'), true);
        $token = $cuerpo['csrf_token'] ?? null;
    }

    if (!$token || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        responderJson(false, null, 'Token CSRF inválido o expirado', 403);
    }
}

/**
 * Verificar autenticación
 */
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    responderJson(false, null, 'No hay sesión activa', 401);
}

/**
 * Verificar rol supervisor (403 para registrador)
 */
if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    responderJson(false, null, 'Acceso denegado. Solo supervisores pueden gestionar usuarios', 403);
}

$usuarioIdSesion = (int)$_SESSION['usuario_id'];
$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['action'] ?? '';

try {
    $modeloUsuario = new Usuario();
    $modeloHistorial = new HistorialUsuario();

    switch ($accion) {
        // ===== LISTAR USUARIOS =====
        case 'listar':
            if ($metodo !== 'GET') {
                responderJson(false, null, 'Método no permitido', 405);
            }

            $usuarios = $modeloUsuario->obtenerTodos();
            responderJson(true, $usuarios, '', 200);
            break;

        // ===== CREAR USUARIO =====
        case 'crear':
            if ($metodo !== 'POST') {
                responderJson(false, null, 'Método no permitido', 405);
            }
            validarCsrf();

            $cuerpo = json_decode(file_get_contents('php://input'), true);
            $username = trim($cuerpo['username'] ?? '');
            $nombreCompleto = trim($cuerpo['nombre_completo'] ?? '');
            $rol = trim($cuerpo['rol'] ?? ROL_REGISTRADOR);
            $password = $cuerpo['password'] ?? '';
            $generarPassword = $cuerpo['generar_password'] ?? false;

            // Validar campos requeridos
            if (empty($username)) {
                responderJson(false, null, 'El nombre de usuario es requerido', 400);
            }
            if (empty($nombreCompleto)) {
                responderJson(false, null, 'El nombre completo es requerido', 400);
            }

            // Validar formato de username
            $validacionUsername = Usuario::validarUsername($username);
            if (!$validacionUsername['valido']) {
                responderJson(false, null, $validacionUsername['error'], 400);
            }

            // Validar rol
            if (!in_array($rol, [ROL_REGISTRADOR, ROL_SUPERVISOR])) {
                responderJson(false, null, 'Rol inválido', 400);
            }

            // Verificar username único
            if ($modeloUsuario->usernameExiste($username)) {
                responderJson(false, null, 'El nombre de usuario ya existe', 400);
            }

            // Generar o validar contraseña
            if ($generarPassword) {
                $password = Usuario::generarPasswordAleatoria(12);
            } else {
                $validacionPassword = Usuario::validarPassword($password);
                if (!$validacionPassword['valido']) {
                    responderJson(false, null, $validacionPassword['error'], 400);
                }
            }

            // Crear usuario
            $nuevoId = $modeloUsuario->crear($username, $password, $nombreCompleto, $rol);

            if (!$nuevoId) {
                responderJson(false, null, 'Error al crear el usuario', 500);
            }

            // Registrar en historial
            $modeloHistorial->registrar(
                $usuarioIdSesion,
                $nuevoId,
                'CREACION',
                "Usuario '{$username}' creado con rol '{$rol}'"
            );

            $respuesta = ['id' => $nuevoId];
            if ($generarPassword) {
                $respuesta['password_generada'] = $password;
            }

            responderJson(true, $respuesta, 'Usuario creado exitosamente', 201);
            break;

        // ===== ACTUALIZAR USUARIO =====
        case 'actualizar':
            if ($metodo !== 'PUT') {
                responderJson(false, null, 'Método no permitido', 405);
            }
            validarCsrf();

            $cuerpo = json_decode(file_get_contents('php://input'), true);
            $id = (int)($cuerpo['id'] ?? 0);
            $nombreCompleto = trim($cuerpo['nombre_completo'] ?? '');
            $rol = trim($cuerpo['rol'] ?? '');

            if ($id <= 0) {
                responderJson(false, null, 'ID de usuario inválido', 400);
            }
            if (empty($nombreCompleto)) {
                responderJson(false, null, 'El nombre completo es requerido', 400);
            }
            if (!in_array($rol, [ROL_REGISTRADOR, ROL_SUPERVISOR])) {
                responderJson(false, null, 'Rol inválido', 400);
            }

            $usuario = $modeloUsuario->obtenerPorId($id);
            if (!$usuario) {
                responderJson(false, null, 'Usuario no encontrado', 404);
            }

            $resultado = $modeloUsuario->actualizar($id, $nombreCompleto, $rol);

            if (!$resultado) {
                responderJson(false, null, 'Error al actualizar el usuario', 500);
            }

            // Registrar en historial
            $modeloHistorial->registrar(
                $usuarioIdSesion,
                $id,
                'ACTUALIZACION',
                "Datos actualizados. Nombre: '{$nombreCompleto}', Rol: '{$rol}'"
            );

            responderJson(true, null, 'Usuario actualizado exitosamente', 200);
            break;

        // ===== CAMBIAR CONTRASEÑA (propia, requiere contraseña actual) =====
        case 'password':
            if ($metodo !== 'PUT') {
                responderJson(false, null, 'Método no permitido', 405);
            }
            validarCsrf();

            $cuerpo = json_decode(file_get_contents('php://input'), true);
            $id = (int)($cuerpo['id'] ?? 0);
            $passwordActual = $cuerpo['password_actual'] ?? '';
            $passwordNuevo = $cuerpo['password_nuevo'] ?? '';
            $passwordConfirmacion = $cuerpo['password_confirmacion'] ?? '';

            // Solo puede cambiar su propia contraseña
            if ($id !== $usuarioIdSesion) {
                responderJson(false, null, 'Solo puede cambiar su propia contraseña desde esta acción', 403);
            }

            if (empty($passwordActual)) {
                responderJson(false, null, 'La contraseña actual es requerida', 400);
            }

            if (empty($passwordNuevo) || empty($passwordConfirmacion)) {
                responderJson(false, null, 'La nueva contraseña y su confirmación son requeridas', 400);
            }

            if ($passwordNuevo !== $passwordConfirmacion) {
                responderJson(false, null, 'Las contraseñas nuevas no coinciden', 400);
            }

            $validacionPassword = Usuario::validarPassword($passwordNuevo);
            if (!$validacionPassword['valido']) {
                responderJson(false, null, $validacionPassword['error'], 400);
            }

            $resultado = $modeloUsuario->cambiarPassword($id, $passwordActual, $passwordNuevo);

            if (!$resultado['exito']) {
                responderJson(false, null, $resultado['error'], 400);
            }

            // Registrar en historial
            $modeloHistorial->registrar(
                $usuarioIdSesion,
                $id,
                'CAMBIO_PASSWORD',
                'Contraseña cambiada por el propio usuario'
            );

            responderJson(true, null, 'Contraseña cambiada exitosamente', 200);
            break;

        // ===== RESET CONTRASEÑA (supervisor a otro usuario, sin contraseña actual) =====
        case 'reset_password':
            if ($metodo !== 'PUT') {
                responderJson(false, null, 'Método no permitido', 405);
            }
            validarCsrf();

            $cuerpo = json_decode(file_get_contents('php://input'), true);
            $id = (int)($cuerpo['id'] ?? 0);

            if ($id <= 0) {
                responderJson(false, null, 'ID de usuario inválido', 400);
            }

            // No permitir reset de propia contraseña
            if ($id === $usuarioIdSesion) {
                responderJson(false, null, 'Use la sección de perfil para cambiar su propia contraseña', 400);
            }

            $usuario = $modeloUsuario->obtenerPorId($id);
            if (!$usuario) {
                responderJson(false, null, 'Usuario no encontrado', 404);
            }

            $resultado = $modeloUsuario->resetearPassword($id);

            if (!$resultado['exito']) {
                responderJson(false, null, $resultado['error'], 500);
            }

            // Registrar en historial
            $modeloHistorial->registrar(
                $usuarioIdSesion,
                $id,
                'CAMBIO_PASSWORD',
                "Contraseña reseteada a valor por defecto (admin123)"
            );

            responderJson(true, null, 'Contraseña reseteada exitosamente a admin123', 200);
            break;

        // ===== TOGGLE ACTIVO/INACTIVO =====
        case 'toggle':
            if ($metodo !== 'PUT') {
                responderJson(false, null, 'Método no permitido', 405);
            }
            validarCsrf();

            $cuerpo = json_decode(file_get_contents('php://input'), true);
            $id = (int)($cuerpo['id'] ?? 0);
            $activo = (bool)($cuerpo['activo'] ?? true);

            if ($id <= 0) {
                responderJson(false, null, 'ID de usuario inválido', 400);
            }

            // No permitir auto-desactivación
            if ($id === $usuarioIdSesion) {
                responderJson(false, null, 'No puede cambiar el estado de su propia cuenta', 400);
            }

            $usuario = $modeloUsuario->obtenerPorId($id);
            if (!$usuario) {
                responderJson(false, null, 'Usuario no encontrado', 404);
            }

            // Bloquear desactivación del último supervisor activo
            if (!$activo && $usuario['rol'] === ROL_SUPERVISOR) {
                if ($modeloUsuario->esUltimoSupervisorActivo($id)) {
                    responderJson(false, null, 'No se puede desactivar al último supervisor activo', 400);
                }
            }

            $resultado = $modeloUsuario->toggleActivo($id, $activo);

            if (!$resultado['exito']) {
                responderJson(false, null, $resultado['error'], 500);
            }

            // Registrar en historial
            $accionHistorial = $activo ? 'ACTIVACION' : 'DESACTIVACION';
            $modeloHistorial->registrar(
                $usuarioIdSesion,
                $id,
                $accionHistorial,
                "Usuario {$activo ? 'activado' : 'desactivado'}"
            );

            responderJson(true, null, "Usuario " . ($activo ? 'activado' : 'desactivado') . " exitosamente", 200);
            break;

        // ===== ELIMINAR USUARIO =====
        case 'eliminar':
            if ($metodo !== 'DELETE') {
                responderJson(false, null, 'Método no permitido', 405);
            }
            validarCsrf();

            $id = (int)($_GET['id'] ?? 0);

            if ($id <= 0) {
                responderJson(false, null, 'ID de usuario inválido', 400);
            }

            // No permitir auto-eliminación
            if ($id === $usuarioIdSesion) {
                responderJson(false, null, 'No puede eliminar su propia cuenta', 400);
            }

            $usuario = $modeloUsuario->obtenerPorId($id);
            if (!$usuario) {
                responderJson(false, null, 'Usuario no encontrado', 404);
            }

            // Bloquear eliminación del último supervisor activo
            if ($usuario['rol'] === ROL_SUPERVISOR && (int)$usuario['activo'] === 1) {
                if ($modeloUsuario->esUltimoSupervisorActivo($id)) {
                    responderJson(false, null, 'No se puede eliminar al último supervisor activo', 400);
                }
            }

            $resultado = $modeloUsuario->eliminar($id);

            if (!$resultado['exito']) {
                responderJson(false, null, $resultado['error'], 500);
            }

            // Registrar en historial
            $modeloHistorial->registrar(
                $usuarioIdSesion,
                $id,
                'ELIMINACION',
                "Usuario '{$usuario['username']}' eliminado del sistema"
            );

            responderJson(true, null, 'Usuario eliminado exitosamente', 200);
            break;

        default:
            responderJson(false, null, 'Acción no reconocida. Acciones válidas: listar, crear, actualizar, password, reset_password, toggle, eliminar', 400);
    }

} catch (Exception $e) {
    error_log("Error en API usuarios: " . $e->getMessage());
    responderJson(false, null, 'Error interno del servidor', 500);
}
