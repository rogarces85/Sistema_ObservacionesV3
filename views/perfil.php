<?php
/**
 * Vista de Perfil y Cambio de Contraseña
 * Accesible para todos los usuarios
 */

require_once 'models/User.php';
require_once 'models/UserAudit.php';

$userModel = new User();
$auditModel = new UserAudit();
$userId = $_SESSION['user_id'];
$userInfo = $userModel->getById($userId);
$activity = $auditModel->getHistory($userId);
$activity = array_slice($activity, 0, 8);
?>

<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="page-header">
                        <div>
                            <h1 class="page-title"><i class="ti ti-user me-2 text-primary"></i>Mi Perfil</h1>
                            <p class="page-subtitle">Gestiona tu información personal y contraseña</p>
                        </div>
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
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="ti ti-lock me-1"></i>Cambiar Contraseña
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="ti ti-activity me-2 text-primary"></i>Actividad Reciente</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($activity)): ?>
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="ti ti-clock-off"></i></div>
                                        <h3>Sin actividad reciente</h3>
                                        <p>Aquí aparecerán tus últimos movimientos en el sistema.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-vcenter">
                                            <thead><tr><th>Acción</th><th>Detalle</th><th class="text-end">Fecha</th></tr></thead>
                                            <tbody>
                                                <?php foreach ($activity as $item): ?>
                                                    <tr>
                                                        <td><span class="badge bg-blue text-blue-fg"><?php echo htmlspecialchars($item['accion']); ?></span></td>
                                                        <td class="text-secondary"><?php echo htmlspecialchars($item['detalles'] ?? ''); ?></td>
                                                        <td class="text-end text-secondary"><?php echo date('d/m/Y H:i', strtotime($item['fecha_registro'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                    </div>
                </div>
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
