/**
 * usuarios.js - Gestión de Usuarios
 * CRUD, cambio de contraseña, toggle activo, eliminación
 * Sistema de Observaciones REM - Servicio de Salud Osorno
 */

'use strict';

class GestorUsuarios {
    constructor() {
        this.usuarioIdSesion = window.USUARIO_ID || null;
        this.usuarios = [];
        this.modalCrear = null;
        this.modalEditar = null;
        this.modalCambiarPassword = null;
        this.modalResetPassword = null;
        this.modalEliminar = null;
        this.inicializar();
    }

    /**
     * Inicializar eventos y cargar datos
     */
    inicializar() {
        // Inicializar modales de Bootstrap
        this.modalCrear = new bootstrap.Modal(document.getElementById('modalCrearUsuario'));
        this.modalEditar = new bootstrap.Modal(document.getElementById('modalEditarUsuario'));
        this.modalCambiarPassword = new bootstrap.Modal(document.getElementById('modalCambiarPassword'));
        this.modalResetPassword = new bootstrap.Modal(document.getElementById('modalResetPassword'));
        this.modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminar'));

        // Eventos
        this.bindEventos();

        // Cargar usuarios
        this.cargarUsuarios();
    }

    /**
     * Vincular eventos a elementos del DOM
     */
    bindEventos() {
        // Botón nuevo usuario
        document.getElementById('btnNuevoUsuario').addEventListener('click', () => this.abrirModalCrear());

        // Toggle generar contraseña manual
        document.getElementById('crearGenerarPassword').addEventListener('change', (e) => {
            document.getElementById('crearPasswordManual').style.display = e.target.checked ? 'none' : 'block';
            document.getElementById('crearPasswordGenerada').style.display = 'none';
        });

        // Formulario crear usuario
        document.getElementById('formCrearUsuario').addEventListener('submit', (e) => this.crearUsuario(e));

        // Copiar contraseña generada
        document.getElementById('btnCopiarPassword').addEventListener('click', () => this.copiarPassword());

        // Formulario editar usuario
        document.getElementById('formEditarUsuario').addEventListener('submit', (e) => this.actualizarUsuario(e));

        // Formulario cambiar contraseña
        document.getElementById('formCambiarPassword').addEventListener('submit', (e) => this.cambiarPassword(e));

        // Confirmar reset contraseña
        document.getElementById('btnConfirmarReset').addEventListener('click', () => this.confirmarResetPassword());

        // Confirmar eliminar
        document.getElementById('btnConfirmarEliminar').addEventListener('click', () => this.confirmarEliminar());

        // Búsqueda en tiempo real
        document.getElementById('buscarUsuario').addEventListener('input', (e) => this.filtrarUsuarios(e.target.value));

        // Por página
        document.getElementById('porPagina').addEventListener('change', () => this.cargarUsuarios());
    }

    /**
     * Cargar lista de usuarios desde API
     */
    async cargarUsuarios() {
        try {
            const respuesta = await fetchAPI('api/usuarios.php?action=listar');

            if (respuesta.success) {
                this.usuarios = respuesta.data || [];
                this.renderizarTabla(this.usuarios);
            }
        } catch (error) {
            console.error('Error al cargar usuarios:', error);
            this.mostrarError('Error al cargar la lista de usuarios');
        }
    }

    /**
     * Renderizar tabla de usuarios
     */
    renderizarTabla(usuarios) {
        const cuerpo = document.getElementById('cuerpoTablaUsuarios');
        document.getElementById('totalUsuarios').textContent = usuarios.length;

        if (usuarios.length === 0) {
            cuerpo.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <p class="text-secondary">No se encontraron usuarios</p>
                    </td>
                </tr>
            `;
            return;
        }

        cuerpo.innerHTML = usuarios.map(usuario => this.filaUsuario(usuario)).join('');

        // Activar tooltips
        const tooltips = cuerpo.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));
    }

    /**
     * Generar HTML de una fila de usuario
     */
    filaUsuario(usuario) {
        const esPropio = parseInt(usuario.id) === this.usuarioIdSesion;
        const rolBadge = usuario.rol === 'supervisor'
            ? '<span class="badge bg-blue-lt">Supervisor</span>'
            : '<span class="badge bg-azure-lt">Registrador</span>';

        const estadoBadge = parseInt(usuario.activo) === 1
            ? '<span class="badge bg-green-lt">Activo</span>'
            : '<span class="badge bg-secondary-lt">Inactivo</span>';

        const fechaCreacion = this.formatearFecha(usuario.fecha_creacion);

        let acciones = `
            <div class="btn-list flex-nowrap justify-content-end">
                <button type="button" class="btn btn-icon btn-ghost-primary btn-sm" onclick="gestorUsuarios.abrirModalEditar(${usuario.id})" data-bs-toggle="tooltip" title="Editar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                </button>
                <button type="button" class="btn btn-icon btn-ghost-secondary btn-sm" onclick="gestorUsuarios.toggleEstado(${usuario.id}, ${parseInt(usuario.activo) === 1 ? 0 : 1})" data-bs-toggle="tooltip" title="${parseInt(usuario.activo) === 1 ? 'Desactivar' : 'Activar'}">
                    ${parseInt(usuario.activo) === 1
                        ? '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" /><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M8 11v-4a4 4 0 1 1 8 0v4" /></svg>'
                        : '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z" /><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" /><path d="M8 11v-4a4 4 0 0 1 6.832 -2.615" /></svg>'
                    }
                </button>
        `;

        // Reset password y eliminar solo si no es el propio usuario
        if (!esPropio) {
            acciones += `
                <button type="button" class="btn btn-icon btn-ghost-warning btn-sm" onclick="gestorUsuarios.abrirModalReset(${usuario.id}, '${this.escapeHtml(usuario.username)}')" data-bs-toggle="tooltip" title="Restablecer contraseña">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16.555 3.843l3.602 3.602a2.877 2.877 0 0 1 0 4.069l-2.643 2.643a2.877 2.877 0 0 1 -4.069 0l-.301 -.301l-6.895 6.895a2 2 0 0 1 -1.414 .586h-2.17a1 1 0 0 1 -1 -1v-2.172a2 2 0 0 1 .586 -1.414l6.895 -6.895l-.301 -.301a2.877 2.877 0 0 1 0 -4.069l2.643 -2.643a2.877 2.877 0 0 1 4.069 0z" /><path d="M15 9h.01" /></svg>
                </button>
                <button type="button" class="btn btn-icon btn-ghost-danger btn-sm" onclick="gestorUsuarios.abrirModalEliminar(${usuario.id}, '${this.escapeHtml(usuario.username)}')" data-bs-toggle="tooltip" title="Eliminar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                </button>
            `;
        }

        acciones += '</div>';

        return `
            <tr>
                <td><span class="font-monospace text-secondary">${this.escapeHtml(usuario.username)}</span></td>
                <td class="fw-semibold">${this.escapeHtml(usuario.nombre_completo)}</td>
                <td>${rolBadge}</td>
                <td>${estadoBadge}</td>
                <td class="text-secondary">${fechaCreacion}</td>
                <td class="text-end">${acciones}</td>
            </tr>
        `;
    }

    /**
     * Abrir modal de creación
     */
    abrirModalCrear() {
        document.getElementById('formCrearUsuario').reset();
        document.getElementById('crearPasswordManual').style.display = 'none';
        document.getElementById('crearPasswordGenerada').style.display = 'none';
        document.getElementById('crearGenerarPassword').checked = true;
        document.getElementById('formCrearUsuario').classList.remove('was-validated');
        this.modalCrear.show();
    }

    /**
     * Crear usuario
     */
    async crearUsuario(evento) {
        evento.preventDefault();
        const formulario = document.getElementById('formCrearUsuario');

        if (!formulario.checkValidity()) {
            formulario.classList.add('was-validated');
            return;
        }

        const generarPassword = document.getElementById('crearGenerarPassword').checked;
        const datos = {
            username: document.getElementById('crearUsername').value.trim().toLowerCase(),
            nombre_completo: document.getElementById('crearNombreCompleto').value.trim(),
            rol: document.getElementById('crearRol').value,
            generar_password: generarPassword
        };

        if (!generarPassword) {
            const password = document.getElementById('crearPassword').value;
            if (!this.validarPasswordPolitica(password)) {
                document.getElementById('crearPassword').classList.add('is-invalid');
                return;
            }
            datos.password = password;
        }

        try {
            this.mostrarCargando(true);
            const respuesta = await fetchAPI('api/usuarios.php?action=crear', {
                method: 'POST',
                body: JSON.stringify(datos)
            });

            this.mostrarCargando(false);

            if (respuesta.success) {
                ToastSystem.success('Usuario creado exitosamente');

                // Mostrar contraseña generada si aplica
                if (respuesta.data && respuesta.data.password_generada) {
                    document.getElementById('passwordGeneradaTexto').textContent = respuesta.data.password_generada;
                    document.getElementById('crearPasswordGenerada').style.display = 'block';
                    // No cerrar el modal para que pueda copiar la contraseña
                    return;
                }

                this.modalCrear.hide();
                await this.cargarUsuarios();
            }
        } catch (error) {
            this.mostrarCargando(false);
            this.mostrarError(error.message || 'Error al crear el usuario');
        }
    }

    /**
     * Copiar contraseña al portapapeles
     */
    copiarPassword() {
        const texto = document.getElementById('passwordGeneradaTexto').textContent;
        navigator.clipboard.writeText(texto).then(() => {
            ToastSystem.success('Contraseña copiada al portapapeles');
        }).catch(() => {
            // Fallback para navegadores que no soportan clipboard API
            const area = document.createElement('textarea');
            area.value = texto;
            document.body.appendChild(area);
            area.select();
            document.execCommand('copy');
            document.body.removeChild(area);
            ToastSystem.success('Contraseña copiada al portapapeles');
        });
    }

    /**
     * Abrir modal de edición
     */
    async abrirModalEditar(id) {
        const usuario = this.usuarios.find(u => parseInt(u.id) === id);
        if (!usuario) return;

        document.getElementById('editarId').value = usuario.id;
        document.getElementById('editarUsername').value = usuario.username;
        document.getElementById('editarNombreCompleto').value = usuario.nombre_completo;
        document.getElementById('editarRol').value = usuario.rol;
        document.getElementById('formEditarUsuario').classList.remove('was-validated');
        this.modalEditar.show();
    }

    /**
     * Actualizar usuario
     */
    async actualizarUsuario(evento) {
        evento.preventDefault();
        const formulario = document.getElementById('formEditarUsuario');

        if (!formulario.checkValidity()) {
            formulario.classList.add('was-validated');
            return;
        }

        const datos = {
            id: parseInt(document.getElementById('editarId').value),
            nombre_completo: document.getElementById('editarNombreCompleto').value.trim(),
            rol: document.getElementById('editarRol').value
        };

        try {
            this.mostrarCargando(true);
            const respuesta = await fetchAPI('api/usuarios.php?action=actualizar', {
                method: 'PUT',
                body: JSON.stringify(datos)
            });

            this.mostrarCargando(false);

            if (respuesta.success) {
                ToastSystem.success('Usuario actualizado exitosamente');
                this.modalEditar.hide();
                await this.cargarUsuarios();
            }
        } catch (error) {
            this.mostrarCargando(false);
            this.mostrarError(error.message || 'Error al actualizar el usuario');
        }
    }

    /**
     * Abrir modal de cambio de contraseña (propia)
     */
    abrirModalCambiarPassword() {
        document.getElementById('formCambiarPassword').reset();
        document.getElementById('formCambiarPassword').classList.remove('was-validated');
        this.modalCambiarPassword.show();
    }

    /**
     * Cambiar contraseña propia
     */
    async cambiarPassword(evento) {
        evento.preventDefault();
        const formulario = document.getElementById('formCambiarPassword');

        if (!formulario.checkValidity()) {
            formulario.classList.add('was-validated');
            return;
        }

        const passwordNuevo = document.getElementById('cambiarPasswordNuevo').value;
        const passwordConfirmacion = document.getElementById('cambiarPasswordConfirmacion').value;

        if (passwordNuevo !== passwordConfirmacion) {
            document.getElementById('cambiarPasswordConfirmacion').classList.add('is-invalid');
            return;
        }

        if (!this.validarPasswordPolitica(passwordNuevo)) {
            document.getElementById('cambiarPasswordNuevo').classList.add('is-invalid');
            return;
        }

        const datos = {
            id: this.usuarioIdSesion,
            password_actual: document.getElementById('cambiarPasswordActual').value,
            password_nuevo: passwordNuevo,
            password_confirmacion: passwordConfirmacion
        };

        try {
            this.mostrarCargando(true);
            const respuesta = await fetchAPI('api/usuarios.php?action=password', {
                method: 'PUT',
                body: JSON.stringify(datos)
            });

            this.mostrarCargando(false);

            if (respuesta.success) {
                ToastSystem.success('Contraseña cambiada exitosamente');
                this.modalCambiarPassword.hide();
            }
        } catch (error) {
            this.mostrarCargando(false);
            this.mostrarError(error.message || 'Error al cambiar la contraseña');
        }
    }

    /**
     * Abrir modal de reset de contraseña
     */
    abrirModalReset(id, username) {
        document.getElementById('resetUserId').value = id;
        document.getElementById('resetUsername').textContent = username;
        this.modalResetPassword.show();
    }

    /**
     * Confirmar reset de contraseña
     */
    async confirmarResetPassword() {
        const id = parseInt(document.getElementById('resetUserId').value);
        const username = document.getElementById('resetUsername').textContent;

        try {
            this.mostrarCargando(true);
            const respuesta = await fetchAPI('api/usuarios.php?action=reset_password', {
                method: 'PUT',
                body: JSON.stringify({ id: id })
            });

            this.mostrarCargando(false);

            if (respuesta.success) {
                ToastSystem.success(`Contraseña de "${username}" restablecida a admin123`);
                this.modalResetPassword.hide();
                await this.cargarUsuarios();
            }
        } catch (error) {
            this.mostrarCargando(false);
            this.mostrarError(error.message || 'Error al restablecer la contraseña');
        }
    }

    /**
     * Toggle estado activo/inactivo
     */
    async toggleEstado(id, nuevoEstado) {
        const usuario = this.usuarios.find(u => parseInt(u.id) === id);
        if (!usuario) return;

        const accion = nuevoEstado === 1 ? 'activar' : 'desactivar';

        try {
            this.mostrarCargando(true);
            const respuesta = await fetchAPI('api/usuarios.php?action=toggle', {
                method: 'PUT',
                body: JSON.stringify({ id: id, activo: nuevoEstado === 1 })
            });

            this.mostrarCargando(false);

            if (respuesta.success) {
                ToastSystem.success(`Usuario ${accion}do exitosamente`);
                await this.cargarUsuarios();
            }
        } catch (error) {
            this.mostrarCargando(false);
            this.mostrarError(error.message || `Error al ${accion} el usuario`);
        }
    }

    /**
     * Abrir modal de eliminación
     */
    abrirModalEliminar(id, username) {
        document.getElementById('eliminarUserId').value = id;
        document.getElementById('eliminarUsername').textContent = username;
        this.modalEliminar.show();
    }

    /**
     * Confirmar eliminación
     */
    async confirmarEliminar() {
        const id = parseInt(document.getElementById('eliminarUserId').value);
        const username = document.getElementById('eliminarUsername').textContent;

        try {
            this.mostrarCargando(true);
            const respuesta = await fetchAPI(`api/usuarios.php?action=eliminar&id=${id}`, {
                method: 'DELETE'
            });

            this.mostrarCargando(false);

            if (respuesta.success) {
                ToastSystem.success(`Usuario "${username}" eliminado exitosamente`);
                this.modalEliminar.hide();
                await this.cargarUsuarios();
            }
        } catch (error) {
            this.mostrarCargando(false);
            this.mostrarError(error.message || 'Error al eliminar el usuario');
        }
    }

    /**
     * Filtrar usuarios por búsqueda
     */
    filtrarUsuarios(termino) {
        if (!termino) {
            this.renderizarTabla(this.usuarios);
            return;
        }

        const terminoLower = termino.toLowerCase();
        const filtrados = this.usuarios.filter(u =>
            u.username.toLowerCase().includes(terminoLower) ||
            u.nombre_completo.toLowerCase().includes(terminoLower) ||
            u.rol.toLowerCase().includes(terminoLower)
        );

        this.renderizarTabla(filtrados);
    }

    /**
     * Validar política de contraseña
     */
    validarPasswordPolitica(password) {
        if (password.length < 8) return false;
        if (!/[A-Z]/.test(password)) return false;
        if (!/[0-9]/.test(password)) return false;
        return true;
    }

    /**
     * Formatear fecha a formato chileno
     */
    formatearFecha(fecha) {
        if (!fecha) return '';
        const f = new Date(fecha);
        if (isNaN(f.getTime())) return fecha;
        return f.toLocaleDateString('es-CL', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    /**
     * Escape HTML para prevenir XSS
     */
    escapeHtml(texto) {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    /**
     * Mostrar/ocultar spinner de carga
     */
    mostrarCargando(mostrar) {
        const spinner = document.getElementById('loading-spinner');
        if (spinner) {
            spinner.classList.toggle('d-none', !mostrar);
        }
    }

    /**
     * Mostrar error
     */
    mostrarError(mensaje) {
        ToastSystem.error(mensaje);
    }
}

// Inicializar cuando el DOM esté listo
let gestorUsuarios;
document.addEventListener('DOMContentLoaded', () => {
    // Obtener ID de usuario desde el DOM (inyectado por PHP si es necesario)
    const inputUsuarioId = document.getElementById('usuarioIdSesion');
    if (inputUsuarioId) {
        window.USUARIO_ID = parseInt(inputUsuarioId.value);
    }
    gestorUsuarios = new GestorUsuarios();
});
