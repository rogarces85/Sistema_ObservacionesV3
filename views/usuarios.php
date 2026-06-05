<?php
/**
 * Vista de Gestión de Usuarios
 * Solo accesible para supervisores
 * Sistema de Observaciones REM - Servicio de Salud Osorno
 */

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    echo '<div class="empty"><div class="empty-header text-danger">403</div><p class="empty-title">Acceso Denegado</p><p class="empty-subtitle text-secondary">Solo los supervisores pueden acceder a esta sección.</p></div>';
    return;
}
?>

<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="page-pretitle">Administración del sistema</div>
                <h2 class="page-title">Gestión de Usuarios</h2>
            </div>
            <div class="col-auto ms-auto">
                <button type="button" class="btn btn-primary" id="btnNuevoUsuario">
                    <?php echo tablerIcon('user-plus'); ?>
                    Nuevo Usuario
                </button>
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Lista de Usuarios</h3>
                    </div>
                    <div class="card-body border-bottom py-3">
                        <div class="d-flex">
                            <div class="text-secondary">
                                Mostrar
                                <div class="mx-2 d-inline-block">
                                    <select id="porPagina" class="form-select form-select-sm">
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                                usuarios
                            </div>
                            <div class="ms-auto text-secondary">
                                Buscar:
                                <div class="ms-2 d-inline-block">
                                    <input type="text" id="buscarUsuario" class="form-control form-control-sm" placeholder="Buscar por nombre o username..." autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-vcenter card-table" id="tablaUsuarios">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Nombre Completo</th>
                                    <th>Rol</th>
                                    <th>Estado</th>
                                    <th>Fecha Creación</th>
                                    <th class="w-1 text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="cuerpoTablaUsuarios">
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                        <p class="mt-2 text-secondary">Cargando usuarios...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer d-flex align-items-center">
                        <p class="m-0 text-secondary">Total: <span id="totalUsuarios">0</span> usuarios</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="usuarioIdSesion" value="<?php echo (int)$_SESSION['usuario_id']; ?>">

<div class="modal-backdrop" id="modalCrearBackdrop" onclick="if(event.target===this)UsuariosApp.cerrarModal('crear')"></div>
<div class="modal-container" id="modalCrear">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3><?php echo tablerIcon('user-plus'); ?> <span id="modalCrearTitulo">Crear Nuevo Usuario</span></h3>
            <button onclick="UsuariosApp.cerrarModal('crear')" class="modal-close"><?php echo tablerIcon('x'); ?></button>
        </div>
        <form id="formCrearUsuario" novalidate>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Nombre de Usuario</label>
                    <input type="text" class="form-control" id="crearUsername" name="username" required
                           pattern="[a-z0-9_]{4,50}" minlength="4" maxlength="50"
                           placeholder="ej: juan_perez" autocomplete="off">
                    <div class="form-text">Solo letras minúsculas, números y guión bajo. Entre 4 y 50 caracteres.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Nombre Completo</label>
                    <input type="text" class="form-control" id="crearNombreCompleto" name="nombre_completo" required
                           placeholder="ej: Juan Pérez González">
                </div>
                <div class="mb-3">
                    <label class="form-label required">Rol</label>
                    <select class="form-select" id="crearRol" name="rol" required>
                        <option value="registrador">Registrador</option>
                        <option value="supervisor">Supervisor</option>
                    </select>
                </div>
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="crearGenerarPassword" name="generar_password" checked>
                        <label class="form-check-label" for="crearGenerarPassword">
                            Generar contraseña aleatoria (12 caracteres)
                        </label>
                    </div>
                </div>
                <div id="crearPasswordManual" class="mb-3" style="display:none;">
                    <label class="form-label required">Contraseña</label>
                    <input type="password" class="form-control" id="crearPassword" name="password"
                           minlength="8" placeholder="Mínimo 8 caracteres, 1 mayúscula, 1 número">
                    <div class="form-text">Mínimo 8 caracteres, al menos una mayúscula y un número.</div>
                </div>
                <div id="crearPasswordGenerada" class="mb-3" style="display:none;">
                    <div class="modal-alert modal-alert-info">
                        <div class="modal-alert-icon"><?php echo tablerIcon('key', 20); ?></div>
                        <div class="modal-alert-content">
                            <strong>Contraseña generada</strong>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <code id="passwordGeneradaTexto" class="fs-5 fw-bold"></code>
                                <button type="button" class="btn btn-sm btn-ghost-primary" id="btnCopiarPassword" title="Copiar al portapapeles">
                                    <?php echo tablerIcon('copy', 16); ?> Copiar
                                </button>
                            </div>
                            <p class="mb-0 mt-2 text-secondary" style="font-size:0.8rem">El usuario deberá cambiar esta contraseña en su próximo inicio de sesión.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="UsuariosApp.cerrarModal('crear')">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnGuardarUsuario">Crear Usuario</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="modalEditarBackdrop" onclick="if(event.target===this)UsuariosApp.cerrarModal('editar')"></div>
<div class="modal-container" id="modalEditar">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3><?php echo tablerIcon('user-edit'); ?> <span id="modalEditarTitulo">Editar Usuario</span></h3>
            <button onclick="UsuariosApp.cerrarModal('editar')" class="modal-close"><?php echo tablerIcon('x'); ?></button>
        </div>
        <form id="formEditarUsuario" novalidate>
            <div class="modal-body">
                <input type="hidden" id="editarId">
                <div class="mb-3">
                    <label class="form-label">Nombre de Usuario</label>
                    <input type="text" class="form-control" id="editarUsername" readonly>
                    <div class="form-text">El nombre de usuario no se puede modificar.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Nombre Completo</label>
                    <input type="text" class="form-control" id="editarNombreCompleto" required>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Rol</label>
                    <select class="form-select" id="editarRol" required>
                        <option value="registrador">Registrador</option>
                        <option value="supervisor">Supervisor</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="UsuariosApp.cerrarModal('editar')">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnActualizarUsuario">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="modalPasswordBackdrop" onclick="if(event.target===this)UsuariosApp.cerrarModal('password')"></div>
<div class="modal-container" id="modalPassword">
    <div class="modal">
        <div class="modal-header">
            <h3><?php echo tablerIcon('lock'); ?> <span>Cambiar Mi Contraseña</span></h3>
            <button onclick="UsuariosApp.cerrarModal('password')" class="modal-close"><?php echo tablerIcon('x'); ?></button>
        </div>
        <form id="formCambiarPassword" novalidate>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label required">Contraseña Actual</label>
                    <input type="password" class="form-control" id="cambiarPasswordActual" required>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Nueva Contraseña</label>
                    <input type="password" class="form-control" id="cambiarPasswordNuevo" required minlength="8">
                    <div class="form-text">Mínimo 8 caracteres, al menos una mayúscula y un número.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label required">Confirmar Nueva Contraseña</label>
                    <input type="password" class="form-control" id="cambiarPasswordConfirmacion" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="UsuariosApp.cerrarModal('password')">Cancelar</button>
                <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="modalResetBackdrop" onclick="if(event.target===this)UsuariosApp.cerrarModal('reset')"></div>
<div class="modal-container" id="modalReset">
    <div class="modal">
        <div class="modal-header">
            <h3><?php echo tablerIcon('key'); ?> <span>Restablecer Contraseña</span></h3>
            <button onclick="UsuariosApp.cerrarModal('reset')" class="modal-close"><?php echo tablerIcon('x'); ?></button>
        </div>
        <div class="modal-body">
            <div class="modal-alert modal-alert-warning">
                <div class="modal-alert-icon"><?php echo tablerIcon('alert-triangle', 20); ?></div>
                <div class="modal-alert-content">
                    <strong>¿Está seguro?</strong>
                    <p class="mb-0 mt-1">La contraseña del usuario <strong id="resetUsername"></strong> será restablecida a <code>admin123</code>. El usuario deberá cambiarla en su próximo inicio de sesión.</p>
                </div>
            </div>
            <input type="hidden" id="resetUserId">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="UsuariosApp.cerrarModal('reset')">Cancelar</button>
            <button type="button" class="btn btn-warning" id="btnConfirmarReset">Restablecer Contraseña</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="modalEliminarBackdrop" onclick="if(event.target===this)UsuariosApp.cerrarModal('eliminar')"></div>
<div class="modal-container" id="modalEliminar">
    <div class="modal">
        <div class="modal-header">
            <h3><?php echo tablerIcon('trash'); ?> <span>Eliminar Usuario</span></h3>
            <button onclick="UsuariosApp.cerrarModal('eliminar')" class="modal-close"><?php echo tablerIcon('x'); ?></button>
        </div>
        <div class="modal-body">
            <div class="modal-alert modal-alert-danger">
                <div class="modal-alert-icon"><?php echo tablerIcon('alert-circle', 20); ?></div>
                <div class="modal-alert-content">
                    <strong>¿Está seguro de eliminar este usuario?</strong>
                    <p class="mb-0 mt-1">Se eliminará al usuario <strong id="eliminarUsername"></strong>. Esta acción no se puede deshacer.</p>
                </div>
            </div>
            <input type="hidden" id="eliminarUserId">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="UsuariosApp.cerrarModal('eliminar')">Cancelar</button>
            <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar Usuario</button>
        </div>
    </div>
</div>

<script src="assets/js/usuarios.js"></script>