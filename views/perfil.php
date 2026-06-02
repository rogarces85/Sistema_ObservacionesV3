<?php
/**
 * Vista de Perfil y Cambio de Contraseña
 * Accesible para todos los usuarios
 */

require_once 'models/Usuario.php';

$modeloUsuario = new Usuario();
$userId = $_SESSION['usuario_id'];
$userInfo = $modeloUsuario->obtenerPorId($userId);
?>

<div class="row row-cards">
                <div class="col-12">
                    <div class="page-header">
                        <div class="page-pretitle">Gestiona tu información personal y contraseña</div>
                        <h2 class="page-title">Mi Perfil</h2>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Información del Usuario</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Usuario</label>
                                <div class="form-control-plaintext font-mono"><?php echo htmlspecialchars($userInfo['username']); ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nombre Completo</label>
                                <div class="form-control-plaintext"><?php echo htmlspecialchars($userInfo['nombre_completo']); ?></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Rol</label>
                                <div>
                                    <span class="badge <?php echo $userInfo['rol'] === ROL_SUPERVISOR ? 'bg-blue text-blue-fg' : 'bg-azure text-azure-fg'; ?>">
                                        <?php echo ucfirst($userInfo['rol']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label">Miembro desde</label>
                                <div class="form-control-plaintext"><?php echo date('d/m/Y', strtotime($userInfo['fecha_creacion'])); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Cambiar Contraseña</h3>
                        </div>
                        <div class="card-body">
                            <form id="formChangePassword" onsubmit="changePassword(event)">
                                <div class="mb-3">
                                    <label class="form-label required">Contraseña Actual</label>
                                    <input type="password" id="currentPassword" name="current_password" class="form-control" required
                                        placeholder="Ingrese su contraseña actual">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Nueva Contraseña</label>
                                    <input type="password" id="newPassword" name="new_password" class="form-control" required minlength="6"
                                        placeholder="Mínimo 6 caracteres">
                                    <div class="form-hint">Debe tener al menos 6 caracteres</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label required">Confirmar Nueva Contraseña</label>
                                    <input type="password" id="confirmPassword" name="confirm_password" class="form-control" required minlength="6"
                                        placeholder="Repita la nueva contraseña">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Cambiar Contraseña</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Actividad Reciente</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-secondary text-center py-4 mb-0">No hay actividad reciente para mostrar</p>
                        </div>
                    </div>
                </div>
            </div>

<script>
    // Cambiar contraseña
    async function changePassword(event) {
        event.preventDefault();

        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;

        // Validar que las contraseñas coincidan
        if (newPassword !== confirmPassword) {
            showMessage('Las contraseñas nuevas no coinciden', 'error');
            return;
        }

        // Validar longitud mínima
        if (newPassword.length < 6) {
            showMessage('La contraseña debe tener al menos 6 caracteres', 'error');
            return;
        }

        try {
            showLoading();

            const response = await fetchAPI('api/usuarios.php?action=password', {
                method: 'PUT',
                body: JSON.stringify({
                    id: <?php echo $userId; ?>,
                    password_actual: currentPassword,
                    password_nuevo: newPassword,
                    password_confirmacion: confirmPassword
                })
            });

            hideLoading();

            if (response.success) {
                showMessage('Contraseña actualizada exitosamente', 'success');
                document.getElementById('formChangePassword').reset();
            }
        } catch (error) {
            hideLoading();
            showMessage(error.message, 'error');
        }
    }
</script>