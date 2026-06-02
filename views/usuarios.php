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
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
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

<!-- ID de usuario de sesión para JavaScript -->
<input type="hidden" id="usuarioIdSesion" value="<?php echo (int)$_SESSION['usuario_id']; ?>">

<!-- Modal Crear Usuario -->
<div class="modal fade" id="modalCrearUsuario" tabindex="-1" aria-labelledby="modalCrearTitulo" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="formCrearUsuario" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCrearTitulo">Crear Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="crearUsername" name="username" required
                               pattern="[a-z0-9_]{4,50}" minlength="4" maxlength="50"
                               placeholder="ej: juan_perez" autocomplete="off">
                        <div class="form-text">Solo letras minúsculas, números y guión bajo. Entre 4 y 50 caracteres.</div>
                        <div class="invalid-feedback">Ingrese un nombre de usuario válido.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Nombre Completo</label>
                        <input type="text" class="form-control" id="crearNombreCompleto" name="nombre_completo" required
                               placeholder="ej: Juan Pérez González">
                        <div class="invalid-feedback">Ingrese el nombre completo.</div>
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
                        <div class="invalid-feedback">La contraseña no cumple con la política requerida.</div>
                    </div>
                    <div id="crearPasswordGenerada" class="mb-3" style="display:none;">
                        <div class="alert alert-info">
                            <h4 class="alert-title">Contraseña generada</h4>
                            <div class="d-flex align-items-center">
                                <code id="passwordGeneradaTexto" class="fs-3 fw-bold me-2"></code>
                                <button type="button" class="btn btn-sm btn-ghost-primary" id="btnCopiarPassword" title="Copiar al portapapeles">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" /><path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /></svg>
                                    Copiar
                                </button>
                            </div>
                            <p class="mb-0 mt-2 text-muted">El usuario deberá cambiar esta contraseña en su próximo inicio de sesión.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarUsuario">Crear Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarTitulo" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="formEditarUsuario" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarTitulo">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
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
                        <div class="invalid-feedback">Ingrese el nombre completo.</div>
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
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnActualizarUsuario">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cambiar Contraseña (propia) -->
<div class="modal fade" id="modalCambiarPassword" tabindex="-1" aria-labelledby="modalCambiarTitulo" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formCambiarPassword" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCambiarTitulo">Cambiar Mi Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Contraseña Actual</label>
                        <input type="password" class="form-control" id="cambiarPasswordActual" required>
                        <div class="invalid-feedback">Ingrese su contraseña actual.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="cambiarPasswordNuevo" required minlength="8">
                        <div class="form-text">Mínimo 8 caracteres, al menos una mayúscula y un número.</div>
                        <div class="invalid-feedback">La contraseña no cumple con la política requerida.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="cambiarPasswordConfirmacion" required>
                        <div class="invalid-feedback">Las contraseñas no coinciden.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reset Contraseña (supervisor) -->
<div class="modal fade" id="modalResetPassword" tabindex="-1" aria-labelledby="modalResetTitulo" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalResetTitulo">Restablecer Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <h4 class="alert-title">¿Está seguro?</h4>
                    <p class="mb-0">La contraseña del usuario <strong id="resetUsername"></strong> será restablecida a <code>admin123</code>. El usuario deberá cambiarla en su próximo inicio de sesión.</p>
                </div>
                <input type="hidden" id="resetUserId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarReset">Restablecer Contraseña</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-labelledby="modalEliminarTitulo" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEliminarTitulo">Eliminar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h4 class="alert-title">¿Está seguro de eliminar este usuario?</h4>
                    <p class="mb-0">Se eliminará al usuario <strong id="eliminarUsername"></strong>. Esta acción no se puede deshacer.</p>
                </div>
                <input type="hidden" id="eliminarUserId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">Eliminar Usuario</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/usuarios.js"></script>
