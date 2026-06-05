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
        this.inicializar();
    }

    inicializar() {
        this.bindEventos();
        this.cargarUsuarios();
    }

    bindEventos() {
        document.getElementById('btnNuevoUsuario').addEventListener('click', () => this.abrirModalCrear());
        document.getElementById('crearGenerarPassword').addEventListener('change', (e) => {
            document.getElementById('crearPasswordManual').style.display = e.target.checked ? 'none' : 'block';
            document.getElementById('crearPasswordGenerada').style.display = 'none';
        });
        document.getElementById('formCrearUsuario').addEventListener('submit', (e) => this.crearUsuario(e));
        document.getElementById('btnCopiarPassword').addEventListener('click', () => this.copiarPassword());
        document.getElementById('formEditarUsuario').addEventListener('submit', (e) => this.actualizarUsuario(e));
        document.getElementById('formCambiarPassword').addEventListener('submit', (e) => this.cambiarPassword(e));
        document.getElementById('btnConfirmarReset').addEventListener('click', () => this.confirmarResetPassword());
        document.getElementById('btnConfirmarEliminar').addEventListener('click', () => this.confirmarEliminar());
        document.getElementById('buscarUsuario').addEventListener('input', (e) => this.filtrarUsuarios(e.target.value));
        document.getElementById('porPagina').addEventListener('change', () => this.cargarUsuarios());
    }

    async cargarUsuarios() {
        try {
            const respuesta = await fetchAPI('api/usuarios.php?action=listar');
            if (respuesta.success) {
                this.usuarios = respuesta.data || [];
                this.renderizarTabla(this.usuarios);
            }
        } catch (error) {
            console.error('Error al cargar usuarios:', error);
        }
    }

    renderizarTabla(usuarios) {
        const cuerpo = document.getElementById('cuerpoTablaUsuarios');
        document.getElementById('totalUsuarios').textContent = usuarios.length;

        if (usuarios.length === 0) {
            cuerpo.innerHTML = `<tr><td colspan="7" class="text-center py-5"><p class="text-secondary">No se encontraron usuarios</p></td></tr>`;
            return;
        }

        cuerpo.innerHTML = usuarios.map(u => this.filaUsuario(u)).join('');
    }

    filaUsuario(usuario) {
        const esPropio = parseInt(usuario.id) === this.usuarioIdSesion;
        const rolBadge = usuario.rol === 'supervisor'
            ? '<span class="badge bg-blue-lt">Supervisor</span>'
            : '<span class="badge bg-azure-lt">Registrador</span>';
        const estadoBadge = parseInt(usuario.activo) === 1
            ? '<span class="badge bg-green-lt">Activo</span>'
            : '<span class="badge bg-secondary-lt">Inactivo</span>';
        const fechaCreacion = this.formatearFecha(usuario.fecha_creacion);

        let acciones = `<div class="btn-list flex-nowrap justify-content-end">
            <button type="button" class="btn btn-icon btn-ghost-primary btn-sm" onclick="gestorUsuarios.abrirModalEditar(${usuario.id})" title="Editar">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/><path d="M16 5l3 3"/></svg>
            </button>
            <button type="button" class="btn btn-icon btn-ghost-secondary btn-sm" onclick="gestorUsuarios.toggleEstado(${usuario.id}, ${parseInt(usuario.activo) === 1 ? 0 : 1})" title="${parseInt(usuario.activo) === 1 ? 'Desactivar' : 'Activar'}">
                ${parseInt(usuario.activo) === 1
                    ? '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z"/><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0"/><path d="M8 11v-4a4 4 0 1 1 8 0v4"/></svg>'
                    : '<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-6z"/><path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0"/><path d="M8 11v-4a4 4 0 1 1 6.832 -2.615"/></svg>'
                }
            </button>`;

        if (!esPropio) {
            acciones += `
                <button type="button" class="btn btn-icon btn-ghost-warning btn-sm" onclick="gestorUsuarios.abrirModalReset(${usuario.id}, '${this.escapeHtml(usuario.username)}')" title="Restablecer contraseña">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M16.555 3.843l3.602 3.602a2.877 2.877 0 0 1 0 4.069l-2.643 2.643a2.877 2.877 0 0 1 -4.069 0l-.301 -.301l-6.895 6.895a2 2 0 0 1 -1.414 .586h-2.17a1 1 0 0 1 -1 -1v-2.172a2 2 0 0 1 .586 -1.414l6.895 -6.895l-.301 -.301a2.877 2.877 0 0 1 0 -4.069l2.643 -2.643a2.877 2.877 0 0 1 4.069 0z"/><path d="M15 9h.01"/></svg>
                </button>
                <button type="button" class="btn btn-icon btn-ghost-danger btn-sm" onclick="gestorUsuarios.abrirModalEliminar(${usuario.id}, '${this.escapeHtml(usuario.username)}')" title="Eliminar">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                </button>`;
        }

        acciones += '</div>';

        return `<tr>
            <td><span class="font-monospace text-secondary">${this.escapeHtml(usuario.username)}</span></td>
            <td class="fw-semibold">${this.escapeHtml(usuario.nombre_completo)}</td>
            <td>${rolBadge}</td>
            <td>${estadoBadge}</td>
            <td class="text-secondary">${fechaCreacion}</td>
            <td class="text-end">${acciones}</td>
        </tr>`;
    }

    abrirModal(modal) {
        document.getElementById(`modal${modal}Backdrop`).classList.add('show');
        document.getElementById(`modal${modal}`).classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    cerrarModal(modal) {
        document.getElementById(`modal${modal}Backdrop`).classList.remove('show');
        document.getElementById(`modal${modal}`).classList.remove('show');
        document.body.style.overflow = '';
    }

    abrirModalCrear() {
        document.getElementById('formCrearUsuario').reset();
        document.getElementById('crearPasswordManual').style.display = 'none';
        document.getElementById('crearPasswordGenerada').style.display = 'none';
        document.getElementById('crearGenerarPassword').checked = true;
        this.abrirModal('Crear');
    }

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
            if (!this.validarPasswordPolitica(password)) return;
            datos.password = password;
        }

        try {
            const respuesta = await fetchAPI('api/usuarios.php?action=crear', {
                method: 'POST',
                body: JSON.stringify(datos)
            });

            if (respuesta.success) {
                ToastSystem.success('Usuario creado exitosamente');
                if (respuesta.data && respuesta.data.password_generada) {
                    document.getElementById('passwordGeneradaTexto').textContent = respuesta.data.password_generada;
                    document.getElementById('crearPasswordGenerada').style.display = 'block';
                    return;
                }
                this.cerrarModal('Crear');
                await this.cargarUsuarios();
            } else {
                ToastSystem.error(respuesta.error);
            }
        } catch (error) {
            ToastSystem.error(error.message || 'Error al crear el usuario');
        }
    }

    copiarPassword() {
        const texto = document.getElementById('passwordGeneradaTexto').textContent;
        navigator.clipboard.writeText(texto).then(() => ToastSystem.success('Contraseña copiada'));
    }

    abrirModalEditar(id) {
        const usuario = this.usuarios.find(u => parseInt(u.id) === id);
        if (!usuario) return;
        document.getElementById('editarId').value = usuario.id;
        document.getElementById('editarUsername').value = usuario.username;
        document.getElementById('editarNombreCompleto').value = usuario.nombre_completo;
        document.getElementById('editarRol').value = usuario.rol;
        this.abrirModal('Editar');
    }

    async actualizarUsuario(evento) {
        evento.preventDefault();
        const formulario = document.getElementById('formEditarUsuario');
        if (!formulario.checkValidity()) return;

        const datos = {
            id: parseInt(document.getElementById('editarId').value),
            nombre_completo: document.getElementById('editarNombreCompleto').value.trim(),
            rol: document.getElementById('editarRol').value
        };

        try {
            const respuesta = await fetchAPI('api/usuarios.php?action=actualizar', {
                method: 'PUT',
                body: JSON.stringify(datos)
            });

            if (respuesta.success) {
                ToastSystem.success('Usuario actualizado');
                this.cerrarModal('Editar');
                await this.cargarUsuarios();
            } else {
                ToastSystem.error(respuesta.error);
            }
        } catch (error) {
            ToastSystem.error(error.message);
        }
    }

    abrirModalCambiarPassword() {
        document.getElementById('formCambiarPassword').reset();
        this.abrirModal('Password');
    }

    async cambiarPassword(evento) {
        evento.preventDefault();
        const passwordNuevo = document.getElementById('cambiarPasswordNuevo').value;
        const passwordConfirmacion = document.getElementById('cambiarPasswordConfirmacion').value;
        if (passwordNuevo !== passwordConfirmacion) {
            document.getElementById('cambiarPasswordConfirmacion').classList.add('is-invalid');
            return;
        }
        if (!this.validarPasswordPolitica(passwordNuevo)) return;

        try {
            const respuesta = await fetchAPI('api/usuarios.php?action=password', {
                method: 'PUT',
                body: JSON.stringify({
                    id: this.usuarioIdSesion,
                    password_actual: document.getElementById('cambiarPasswordActual').value,
                    password_nuevo: passwordNuevo,
                    password_confirmacion: passwordConfirmacion
                })
            });

            if (respuesta.success) {
                ToastSystem.success('Contraseña cambiada');
                this.cerrarModal('Password');
            } else {
                ToastSystem.error(respuesta.error);
            }
        } catch (error) {
            ToastSystem.error(error.message);
        }
    }

    abrirModalReset(id, username) {
        document.getElementById('resetUserId').value = id;
        document.getElementById('resetUsername').textContent = username;
        this.abrirModal('Reset');
    }

    async confirmarResetPassword() {
        const id = parseInt(document.getElementById('resetUserId').value);
        const username = document.getElementById('resetUsername').textContent;
        try {
            const respuesta = await fetchAPI('api/usuarios.php?action=reset_password', {
                method: 'PUT',
                body: JSON.stringify({ id: id })
            });
            if (respuesta.success) {
                ToastSystem.success(`Contraseña de "${username}" restablecida`);
                this.cerrarModal('Reset');
                await this.cargarUsuarios();
            } else {
                ToastSystem.error(respuesta.error);
            }
        } catch (error) {
            ToastSystem.error(error.message);
        }
    }

    async toggleEstado(id, nuevoEstado) {
        try {
            const respuesta = await fetchAPI('api/usuarios.php?action=toggle', {
                method: 'PUT',
                body: JSON.stringify({ id: id, activo: nuevoEstado === 1 })
            });
            if (respuesta.success) {
                ToastSystem.success('Estado actualizado');
                await this.cargarUsuarios();
            } else {
                ToastSystem.error(respuesta.error);
            }
        } catch (error) {
            ToastSystem.error(error.message);
        }
    }

    abrirModalEliminar(id, username) {
        document.getElementById('eliminarUserId').value = id;
        document.getElementById('eliminarUsername').textContent = username;
        this.abrirModal('Eliminar');
    }

    async confirmarEliminar() {
        const id = parseInt(document.getElementById('eliminarUserId').value);
        const username = document.getElementById('eliminarUsername').textContent;
        try {
            const respuesta = await fetchAPI(`api/usuarios.php?action=eliminar&id=${id}`, { method: 'DELETE' });
            if (respuesta.success) {
                ToastSystem.success(`Usuario "${username}" eliminado`);
                this.cerrarModal('Eliminar');
                await this.cargarUsuarios();
            } else {
                ToastSystem.error(respuesta.error);
            }
        } catch (error) {
            ToastSystem.error(error.message);
        }
    }

    filtrarUsuarios(termino) {
        if (!termino) return this.renderizarTabla(this.usuarios);
        const t = termino.toLowerCase();
        this.renderizarTabla(this.usuarios.filter(u =>
            u.username.toLowerCase().includes(t) ||
            u.nombre_completo.toLowerCase().includes(t) ||
            u.rol.toLowerCase().includes(t)
        ));
    }

    validarPasswordPolitica(password) {
        if (password.length < 8 || !/[A-Z]/.test(password) || !/[0-9]/.test(password)) return false;
        return true;
    }

    formatearFecha(fecha) {
        if (!fecha) return '';
        const f = new Date(fecha);
        if (isNaN(f.getTime())) return fecha;
        return f.toLocaleDateString('es-CL', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    escapeHtml(texto) {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }
}

let gestorUsuarios;
document.addEventListener('DOMContentLoaded', () => {
    const inputUsuarioId = document.getElementById('usuarioIdSesion');
    if (inputUsuarioId) window.USUARIO_ID = parseInt(inputUsuarioId.value);
    window.gestorUsuarios = new GestorUsuarios();
    gestorUsuarios = window.gestorUsuarios;
});