<?php
/**
 * Vista de Administración de Usuarios
 * Solo accesible para supervisores
 */

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    echo '<div class="page-wrapper"><div class="page-body"><div class="container-xl"><div class="empty"><div class="empty-header text-danger">403</div><p class="empty-title">Acceso Denegado</p><p class="empty-subtitle text-secondary">Solo los supervisores pueden acceder a esta sección.</p></div></div></div></div>';
    return;
}

require_once 'models/User.php';

$userModel = new User();
$usuarios = $userModel->getAll();
?>

<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">

                <div class="col-12">
                    <div class="page-header">
                        <div>
                            <h1 class="page-title"><i class="ti ti-users me-2 text-primary"></i>Gestión de Usuarios</h1>
                            <p class="page-subtitle">Administración completa del sistema</p>
                        </div>
                        <div class="page-actions">
                            <button onclick="openCreateUserModal()" class="btn btn-primary">
                                <i class="ti ti-user-plus me-1"></i>Nuevo Usuario
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-hover">
                                <thead>
                                    <tr>
                                        <th>Usuario</th>
                                        <th>Nombre Completo</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Fecha Creación</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td>
                                                <span class="text-muted font-mono"><?php echo htmlspecialchars($usuario['username']); ?></span>
                                            </td>
                                            <td class="fw-semibold"><?php echo htmlspecialchars($usuario['nombre_completo']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $usuario['rol'] === ROL_SUPERVISOR ? 'bg-blue text-blue-fg' : 'bg-azure text-azure-fg'; ?>">
                                                    <?php echo ucfirst($usuario['rol']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <label class="form-check form-switch">
                                                    <input type="checkbox" class="form-check-input" <?php echo $usuario['activo'] ? 'checked' : ''; ?>
                                                        onchange="toggleUserStatus(<?php echo $usuario['id']; ?>, this.checked)">
                                                    <span class="form-check-label"><?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?></span>
                                                </label>
                                            </td>
                                            <td class="text-secondary"><?php echo date('d/m/Y', strtotime($usuario['fecha_creacion'])); ?></td>
                                            <td>
                                                <div class="btn-list justify-content-end">
                                <button onclick="editUser(<?php echo htmlspecialchars(json_encode($usuario)); ?>)"
                                    class="btn btn-ghost-secondary btn-icon" title="Editar" data-bs-toggle="tooltip" aria-label="Editar">
                                    <i class="ti ti-edit"></i>
                                </button>
                                <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                     <button
                                         onclick="resetPassword(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['username']); ?>')"
                                         class="btn btn-ghost-warning btn-icon"
                                         title="Restablecer contraseña" data-bs-toggle="tooltip" aria-label="Restablecer contraseña">
                                         <i class="ti ti-key"></i>
                                     </button>
                                    <button
                                        onclick="viewUserHistory(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['username']); ?>')"
                                        class="btn btn-ghost-primary btn-icon"
                                        title="Ver auditoría" data-bs-toggle="tooltip" aria-label="Ver auditoría">
                                        <i class="ti ti-history"></i>
                                    </button>
                                     <button
                                         onclick="deleteUser(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['username']); ?>')"
                                        class="btn btn-ghost-danger btn-icon"
                                        title="Eliminar" data-bs-toggle="tooltip" aria-label="Eliminar">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modalUserHistory" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUserHistoryTitle"><i class="ti ti-history me-2 text-primary"></i>Auditoría de Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="userHistoryBody" class="text-secondary">Cargando...</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear/Editar Usuario (Bootstrap) -->
<div id="modalUser" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="modalUserTitle"><i class="ti ti-user-plus me-2 text-primary"></i>Nuevo Usuario</h5>
                    <div class="text-secondary">Complete los datos del usuario</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formUser" onsubmit="saveUser(event)">
                    <input type="hidden" id="userId" value="">
                    <div class="mb-3">
                        <label class="form-label required">Usuario (username)</label>
                        <input type="text" id="username" name="username" class="form-control" required pattern="[a-zA-Z0-9_]{3,20}"
                            title="3-20 caracteres, solo letras, números y guión bajo">
                    </div>
                    <div class="mb-3" id="passwordField">
                        <label class="form-label required">Contraseña</label>
                        <input type="password" id="password" name="password" class="form-control" minlength="6"
                            placeholder="Mínimo 6 caracteres">
                        <div class="form-hint">Deje en blanco para mantener la contraseña actual (solo al editar)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Nombre Completo</label>
                        <input type="text" id="nombreCompleto" name="nombre_completo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Rol</label>
                        <select id="rol" name="rol" class="form-select" required>
                            <option value="registrador">Registrador</option>
                            <option value="supervisor">Supervisor</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary ms-auto" onclick="saveUser(event)">
                    <i class="ti ti-device-floppy me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const modalUser = new bootstrap.Modal(document.getElementById('modalUser'));
    const modalUserHistory = new bootstrap.Modal(document.getElementById('modalUserHistory'));

    function openCreateUserModal() {
        document.getElementById('userId').value = '';
        document.getElementById('formUser').reset();
        document.getElementById('modalUserTitle').textContent = 'Nuevo Usuario';
        document.getElementById('username').readOnly = false;
        document.getElementById('password').required = true;
        modalUser.show();
    }

    function editUser(user) {
        document.getElementById('userId').value = user.id;
        document.getElementById('username').value = user.username;
        document.getElementById('username').readOnly = true;
        document.getElementById('password').value = '';
        document.getElementById('password').required = false;
        document.getElementById('nombreCompleto').value = user.nombre_completo;
        document.getElementById('rol').value = user.rol;
        document.getElementById('modalUserTitle').textContent = 'Editar Usuario';
        modalUser.show();
    }

    async function saveUser(event) {
        event.preventDefault();
        const form = document.getElementById('formUser');
        if (!form.checkValidity()) { form.classList.add('was-validated'); return; }

        const userId = document.getElementById('userId').value;
        const isEdit = userId !== '';

        const userData = {
            username: document.getElementById('username').value,
            nombre_completo: document.getElementById('nombreCompleto').value,
            rol: document.getElementById('rol').value
        };

        const password = document.getElementById('password').value;
        if (!isEdit || password) {
            userData.password = password;
        }

        try {
            showLoading();
            let response;
            if (isEdit) {
                response = await fetchAPI(`users.php?id=${userId}`, {
                    method: 'PUT',
                    body: JSON.stringify(userData)
                });
            } else {
                response = await fetchAPI('users.php', {
                    method: 'POST',
                    body: JSON.stringify(userData)
                });
            }
            hideLoading();
            if (response.success) {
                showMessage(isEdit ? 'Usuario actualizado' : 'Usuario creado', 'success');
                modalUser.hide();
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            hideLoading();
            showMessage(error.message, 'error');
        }
    }

    async function toggleUserStatus(userId, activate) {
        try {
            const response = await fetchAPI(`users.php?id=${userId}`, {
                method: 'PUT',
                body: JSON.stringify({
                    action: 'toggle',
                    activo: activate
                })
            });

            if (response.success) {
                showMessage(`Usuario ${activate ? 'activado' : 'desactivado'} exitosamente`, 'success');
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            showMessage(error.message, 'error');
            location.reload();
        }
    }

    // Eliminar usuario
    async function deleteUser(userId, username) {
        if (!confirm(`¿Está seguro de eliminar al usuario "${username}"?\n\nEsta acción no se puede deshacer.`)) {
            return;
        }

        try {
            showLoading();

            const response = await fetchAPI(`users.php?id=${userId}`, {
                method: 'DELETE'
            });

            hideLoading();

            if (response.success) {
                showMessage('Usuario eliminado exitosamente', 'success');
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            hideLoading();
            showMessage(error.message, 'error');
        }
    }

    // Restablecer contraseña
    async function resetPassword(userId, username) {
        if (!confirm(`¿Restablecer la contraseña del usuario "${username}"?\n\nLa contraseña volverá a: admin123`)) {
            return;
        }

        try {
            showLoading();

            const response = await fetchAPI(`users.php?id=${userId}`, {
                method: 'PUT',
                body: JSON.stringify({
                    action: 'reset_password'
                })
            });

            hideLoading();

            if (response.success) {
                showMessage(`Contraseña de "${username}" restablecida a: admin123`, 'success');
            }
        } catch (error) {
            hideLoading();
            showMessage(error.message, 'error');
        }
    }

    async function viewUserHistory(userId, username) {
        document.getElementById('modalUserHistoryTitle').innerHTML = '<i class="ti ti-history me-2 text-primary"></i>Auditoría de ' + username;
        document.getElementById('userHistoryBody').innerHTML = '<div class="text-secondary">Cargando...</div>';
        modalUserHistory.show();

        try {
            const response = await fetchAPI(`users.php?action=history&id=${userId}`);
            const items = response.data || [];
            if (items.length === 0) {
                document.getElementById('userHistoryBody').innerHTML = '<div class="empty-state"><div class="empty-icon"><i class="ti ti-clock-off"></i></div><h3>Sin auditoría</h3><p>No hay cambios registrados para este usuario.</p></div>';
                return;
            }

            document.getElementById('userHistoryBody').innerHTML = `
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <thead><tr><th>Acción</th><th>Detalle</th><th>Fecha</th></tr></thead>
                        <tbody>${items.map(item => `
                            <tr>
                                <td><span class="badge bg-blue text-blue-fg">${escapeHtml(item.accion)}</span></td>
                                <td class="text-secondary">${escapeHtml(item.detalles || '')}</td>
                                <td class="text-secondary">${formatDateTime(item.fecha_registro)}</td>
                            </tr>
                        `).join('')}</tbody>
                    </table>
                </div>`;
        } catch (error) {
            document.getElementById('userHistoryBody').innerHTML = '<div class="text-danger">No se pudo cargar la auditoría.</div>';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function formatDateTime(dateString) {
        if (!dateString) return '-';
        return new Date(dateString).toLocaleString('es-CL', { dateStyle: 'short', timeStyle: 'short' });
    }
</script>
