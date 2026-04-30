<?php
/**
 * Vista de Perfil y Cambio de Contraseña
 * Accesible para todos los usuarios
 */

require_once 'models/User.php';

$userModel = new User();
$userId = $_SESSION['user_id'];
$userInfo = $userModel->getById($userId);
?>

<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Mi Perfil</h2>
        <p class="text-slate-600">Gestiona tu información personal y contraseña</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Información del Usuario -->
        <div class="card p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Información del Usuario</h3>

            <div class="space-y-4">
                <div>
                    <label class="text-sm font-semibold text-slate-600">Usuario</label>
                    <div class="mt-1 p-3 bg-slate-50 rounded-lg">
                        <span class="font-mono text-slate-800">
                            <?php echo htmlspecialchars($userInfo['username']); ?>
                        </span>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-600">Nombre Completo</label>
                    <div class="mt-1 p-3 bg-slate-50 rounded-lg">
                        <span class="text-slate-800">
                            <?php echo htmlspecialchars($userInfo['nombre_completo']); ?>
                        </span>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-600">Rol</label>
                    <div class="mt-1">
                        <span
                            class="badge <?php echo $userInfo['rol'] === ROL_SUPERVISOR ? 'badge-aprobado' : 'badge-pendiente'; ?>">
                            <?php echo ucfirst($userInfo['rol']); ?>
                        </span>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-600">Miembro desde</label>
                    <div class="mt-1 text-slate-800">
                        <?php echo date('d/m/Y', strtotime($userInfo['fecha_creacion'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="card p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">Cambiar Contraseña</h3>

            <form id="formChangePassword" onsubmit="changePassword(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Contraseña Actual *</label>
                    <input type="password" id="currentPassword" name="current_password" required class="w-full"
                        placeholder="Ingrese su contraseña actual">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nueva Contraseña *</label>
                    <input type="password" id="newPassword" name="new_password" required minlength="6" class="w-full"
                        placeholder="Mínimo 6 caracteres">
                    <p class="text-xs text-slate-500 mt-1">Debe tener al menos 6 caracteres</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Confirmar Nueva Contraseña *</label>
                    <input type="password" id="confirmPassword" name="confirm_password" required minlength="6"
                        class="w-full" placeholder="Repita la nueva contraseña">
                </div>

                <div class="pt-2">
                    <button type="submit" class="btn btn-primary w-full">
                        🔒 Cambiar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Actividad Reciente (placeholder) -->
    <div class="card p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">Actividad Reciente</h3>
        <p class="text-sm text-slate-500 text-center py-8">
            No hay actividad reciente para mostrar
        </p>
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

            const response = await fetchAPI('users.php?id=<?php echo $userId; ?>', {
                method: 'PUT',
                body: JSON.stringify({
                    action: 'password',
                    current_password: currentPassword,
                    new_password: newPassword,
                    confirm_password: confirmPassword
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