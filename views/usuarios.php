<?php
/**
 * Vista de Administración de Usuarios
 * Solo accesible para supervisores
 */

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    echo '<div class="p-6 text-center"><h2 class="text-xl font-bold text-rose-600">Acceso Denegado</h2><p>Solo los supervisores pueden acceder a esta sección.</p></div>';
    return;
}

require_once 'models/User.php';

$userModel = new User();
$usuarios = $userModel->getAll();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Gestión de Usuarios</h2>
            <p class="text-slate-600">Administración completa del sistema</p>
        </div>
        <button onclick="openCreateUserModal()" class="btn btn-primary">
            ➕ Nuevo Usuario
        </button>
    </div>

    <!-- Tabla de usuarios -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs bg-slate-100 px-2 py-1 rounded font-mono">
                                        <?php echo htmlspecialchars($usuario['username']); ?>
                                    </span>
                                </div>
                            </td>
                            <td class="font-semibold text-slate-800">
                                <?php echo htmlspecialchars($usuario['nombre_completo']); ?>
                            </td>
                            <td>
                                <span
                                    class="badge <?php echo $usuario['rol'] === ROL_SUPERVISOR ? 'badge-aprobado' : 'badge-pendiente'; ?>">
                                    <?php echo ucfirst($usuario['rol']); ?>
                                </span>
                            </td>
                            <td>
                                <span
                                    class="badge <?php echo $usuario['activo'] ? 'badge-aprobado' : 'badge-rechazado'; ?>">
                                    <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                                </span>
                            </td>
                            <td class="text-sm text-slate-500">
                                <?php echo date('d/m/Y', strtotime($usuario['fecha_creacion'])); ?>
                            </td>
                            <td class="text-right">
                                <div class="flex gap-2 justify-end">
                                    <button onclick="editUser(<?php echo htmlspecialchars(json_encode($usuario)); ?>)"
                                        class="btn-secondary px-3 py-1 text-xs" title="Editar">
                                        ✏️
                                    </button>
                                    <button
                                        onclick="toggleUserStatus(<?php echo $usuario['id']; ?>, <?php echo $usuario['activo'] ? 'false' : 'true'; ?>)"
                                        class="btn-secondary px-3 py-1 text-xs"
                                        title="<?php echo $usuario['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                        <?php echo $usuario['activo'] ? '🔒' : '🔓'; ?>
                                    </button>
                                    <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                        <button
                                            onclick="deleteUser(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['username']); ?>')"
                                            class="btn-secondary px-3 py-1 text-xs bg-rose-50 hover:bg-rose-100 text-rose-600"
                                            title="Eliminar">
                                            🗑️
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

<!-- Modal Crear/Editar Usuario -->
<div id="modalUser" class="modal-overlay hidden">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h3 id="modalUserTitle" class="text-xl font-bold text-slate-800">Nuevo Usuario</h3>
                <p class="text-sm text-slate-500">Complete los datos del usuario</p>
            </div>
            <button onclick="closeModal('modalUser')" class="btn-secondary px-3 py-2">✕</button>
        </div>
        <div class="modal-body">
            <form id="formUser" onsubmit="saveUser(event)" class="space-y-4">
                <input type="hidden" id="userId" value="">

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Usuario (username) *</label>
                    <input type="text" id="username" name="username" required pattern="[a-zA-Z0-9_]{3,20}"
                        title="3-20 caracteres, solo letras, números y guión bajo">
                </div>

                <div id="passwordField">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Contraseña *</label>
                    <input type="password" id="password" name="password" minlength="6"
                        placeholder="Mínimo 6 caracteres">
                    <p class="text-xs text-slate-500 mt-1">Deje en blanco para mantener la contraseña actual (solo al
                        editar)</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nombre Completo *</label>
                    <input type="text" id="nombreCompleto" name="nombre_completo" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Rol *</label>
                    <select id="rol" name="rol" required>
                        <option value="registrador">Registrador</option>
                        <option value="supervisor">Supervisor</option>
                    </select>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="btn btn-primary flex-1">Guardar</button>
                    <button type="button" onclick="closeModal('modalUser')" class="btn btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Abrir modal para crear usuario
    function openCreateUserModal() {
        document.getElementById('userId').value = '';
        document.getElementById('formUser').reset();
        document.getElementById('modalUserTitle').textContent = 'Nuevo Usuario';
        document.getElementById('password').required = true;
        openModal('modalUser');
    }

    // Editar usuario
    function editUser(user) {
        document.getElementById('userId').value = user.id;
        document.getElementById('username').value = user.username;
        document.getElementById('username').readOnly = true; // No cambiar username
        document.getElementById('password').value = '';
        document.getElementById('password').required = false;
        document.getElementById('nombreCompleto').value = user.nombre_completo;
        document.getElementById('rol').value = user.rol;

        document.getElementById('modalUserTitle').textContent = 'Editar Usuario';
        openModal('modalUser');
    }

    // Guardar usuario
    async function saveUser(event) {
        event.preventDefault();

        if (!validateForm('formUser')) return;

        const userId = document.getElementById('userId').value;
        const isEdit = userId !== '';

        const userData = {
            username: document.getElementById('username').value,
            nombre_completo: document.getElementById('nombreCompleto').value,
            rol: document.getElementById('rol').value
        };

        // Solo incluir password si es nuevo usuario o si se cambió
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
                closeModal('modalUser');
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            hideLoading();
            showMessage(error.message, 'error');
        }
    }

    // Activar/Desactivar usuario
    async function toggleUserStatus(userId, activate) {
        const action = activate ? 'activar' : 'desactivar';

        if (!confirm(`¿Está seguro de ${action} este usuario?`)) {
            return;
        }

        try {
            showLoading();

            const response = await fetchAPI(`users.php?id=${userId}`, {
                method: 'PUT',
                body: JSON.stringify({
                    action: 'toggle',
                    activo: activate
                })
            });

            hideLoading();

            if (response.success) {
                showMessage(`Usuario ${action}do exitosamente`, 'success');
                setTimeout(() => location.reload(), 1000);
            }
        } catch (error) {
            hideLoading();
            showMessage(error.message, 'error');
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
</script>