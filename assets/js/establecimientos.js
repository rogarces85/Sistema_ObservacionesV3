/**
 * establecimientos.js - Gestión de establecimientos y referentes
 * Sistema de Observaciones REM
 */

'use strict';

class GestorEstablecimientos {
    constructor() {
        this.establecimientos = [];
        this.comunas = [];
        this.filtroComuna = '';
        this.busqueda = '';
        this.incluirInactivos = false;
        this.ordenColumna = 'codigo_establecimiento';
        this.ordenAscendente = true;
        this.establecimientoActual = null;
        this.modalEst = null;
        this.modalRef = null;

        this.inicializar();
    }

    async inicializar() {
        await this.cargarComunas();
        await this.cargarEstablecimientos();
        await this.cargarEstadisticas();

        this.configurarEventos();
    }

    abrirModal(nombre) {
        document.getElementById(`modal${nombre}Backdrop`).classList.add('show');
        document.getElementById(`modal${nombre}`).classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    cerrarModal(nombre) {
        document.getElementById(`modal${nombre}Backdrop`).classList.remove('show');
        document.getElementById(`modal${nombre}`).classList.remove('show');
        document.body.style.overflow = '';
    }

    configurarEventos() {
        document.getElementById('filtroComuna').addEventListener('change', (e) => {
            this.filtroComuna = e.target.value;
            this.cargarEstablecimientos();
        });

        const inputBusqueda = document.getElementById('busqueda');
        const busquedaDebounce = debounce(() => {
            this.busqueda = inputBusqueda.value.trim();
            this.cargarEstablecimientos();
        }, 300);
        inputBusqueda.addEventListener('input', busquedaDebounce);

        document.getElementById('incluirInactivos').addEventListener('change', (e) => {
            this.incluirInactivos = e.target.checked;
            this.cargarEstablecimientos();
        });

        document.getElementById('formEstablecimiento').addEventListener('submit', (e) => {
            e.preventDefault();
            this.guardarEstablecimiento();
        });

        document.getElementById('formReferente').addEventListener('submit', (e) => {
            e.preventDefault();
            this.guardarReferente();
        });

        document.getElementById('modalEstablecimiento').addEventListener('hidden.bs.modal', () => {
            document.getElementById('formEstablecimiento').reset();
            document.getElementById('estId').value = '';
        });

        document.getElementById('modalReferentes').addEventListener('hidden.bs.modal', () => {
            document.getElementById('formReferente').reset();
            document.getElementById('refId').value = '';
        });
    }

    async cargarComunas() {
        try {
            const respuesta = await fetchAPI('api/establecimientos.php?accion=comunas');
            if (respuesta.success) {
                this.comunas = respuesta.data;
                this.renderizarSelectComunas();
            }
        } catch (error) {
            mostrarNotificacion('Error al cargar comunas', 'danger');
        }
    }

    renderizarSelectComunas() {
        const selectFiltro = document.getElementById('filtroComuna');
        const selectForm = document.getElementById('estComuna');

        let opciones = '<option value="">Todas las comunas</option>';
        let opcionesForm = '<option value="">Seleccionar comuna...</option>';

        this.comunas.forEach(comuna => {
            opciones += `<option value="${comuna.id}">${this.escapeHtml(comuna.nombre)}</option>`;
            opcionesForm += `<option value="${comuna.id}">${this.escapeHtml(comuna.nombre)}</option>`;
        });

        selectFiltro.innerHTML = opciones;
        selectForm.innerHTML = opcionesForm;
    }

    async cargarEstablecimientos() {
        mostrarLoading(true);
        try {
            let url = `api/establecimientos.php?accion=listar&incluir_inactivos=${this.incluirInactivos ? '1' : '0'}`;
            if (this.filtroComuna) url += `&comuna_id=${this.filtroComuna}`;
            if (this.busqueda) url += `&busqueda=${encodeURIComponent(this.busqueda)}`;

            const respuesta = await fetchAPI(url);
            if (respuesta.success) {
                this.establecimientos = respuesta.data;
                this.renderizarTabla();
            } else {
                mostrarNotificacion(respuesta.error || 'Error al cargar establecimientos', 'danger');
            }
        } catch (error) {
            mostrarNotificacion('Error de conexión', 'danger');
        } finally {
            mostrarLoading(false);
        }
    }

    renderizarTabla() {
        const tbody = document.getElementById('tablaEstablecimientos');
        
        if (this.establecimientos.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-secondary py-4">
                        No se encontraron establecimientos
                    </td>
                </tr>`;
            return;
        }

        let html = '';
        this.establecimientos.forEach(est => {
            const estadoClase = est.activo ? 'bg-green text-green-fg' : 'bg-secondary text-secondary-fg';
            const estadoTexto = est.activo ? 'Activo' : 'Inactivo';
            const nombreClase = est.activo ? '' : 'text-decoration-line-through text-secondary';
            const toggleIcon = est.activo ? 'player-pause' : 'player-play';
            const toggleTitle = est.activo ? 'Desactivar' : 'Activar';

            html += `
                <tr>
                    <td class="font-monospace text-secondary">${this.escapeHtml(est.codigo_establecimiento)}</td>
                    <td class="fw-semibold ${nombreClase}">${this.escapeHtml(est.nombre)}</td>
                    <td class="text-secondary">${this.escapeHtml(est.nombre_corto)}</td>
                    <td class="text-secondary">${this.escapeHtml(est.comuna_nombre)}</td>
                    <td class="text-center">
                        <span class="badge ${estadoClase}">${estadoTexto}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-azure-lt">${est.referentes_count} referente${est.referentes_count !== 1 ? 's' : ''}</span>
                    </td>
                    <td class="text-center">
                        <div class="btn-list justify-content-center">
                            <button class="btn btn-ghost-secondary btn-icon" 
                                    onclick="gestorEst.abrirModalReferentes(${est.id}, '${this.escapeHtml(est.nombre)}')" 
                                    title="Gestionar referentes" data-bs-toggle="tooltip">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                            </button>
                            <button class="btn btn-ghost-secondary btn-icon" 
                                    onclick="gestorEst.abrirEditar(${est.id})" 
                                    title="Editar" data-bs-toggle="tooltip">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                            </button>
                            <button class="btn btn-ghost-secondary btn-icon" 
                                    onclick="gestorEst.toggleEstablecimiento(${est.id}, ${est.activo ? 0 : 1})" 
                                    title="${toggleTitle}" data-bs-toggle="tooltip">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 4v16l13 -8z" /></svg>
                            </button>
                        </div>
                    </td>
                </tr>`;
        });

        tbody.innerHTML = html;

        // Reinicializar tooltips
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            new bootstrap.Tooltip(el);
        });
    }

    async cargarEstadisticas() {
        try {
            const respuesta = await fetchAPI('api/establecimientos.php?accion=estadisticas');
            if (respuesta.success) {
                document.getElementById('statActivos').textContent = respuesta.data.activos || 0;
                document.getElementById('statInactivos').textContent = respuesta.data.inactivos || 0;
                document.getElementById('statTotal').textContent = respuesta.data.total || 0;
            }
        } catch (error) {
            console.error('Error al cargar estadísticas:', error);
        }
    }

    abrirCrear() {
        document.getElementById('modalEstablecimientoTitulo').textContent = 'Nuevo Establecimiento';
        document.getElementById('btnGuardarEst').textContent = 'Crear Establecimiento';
        document.getElementById('formEstablecimiento').reset();
        document.getElementById('estId').value = '';
        document.getElementById('estCodigo').disabled = false;
        this.modalEst.show();
    }

    async abrirEditar(id) {
        try {
            const respuesta = await fetchAPI(`api/establecimientos.php?accion=obtener&id=${id}`);
            if (respuesta.success) {
                const est = respuesta.data;
                document.getElementById('modalEstablecimientoTitulo').textContent = 'Editar Establecimiento';
                document.getElementById('btnGuardarEst').textContent = 'Guardar Cambios';
                document.getElementById('estId').value = est.id;
                document.getElementById('estCodigo').value = est.codigo_establecimiento;
                document.getElementById('estCodigo').disabled = true;
                document.getElementById('estNombre').value = est.nombre;
                document.getElementById('estNombreCorto').value = est.nombre_corto;
                document.getElementById('estComuna').value = est.comuna_id;
                this.modalEst.show();
            }
        } catch (error) {
            mostrarNotificacion('Error al cargar establecimiento', 'danger');
        }
    }

    async guardarEstablecimiento() {
        const id = document.getElementById('estId').value;
        const accion = id ? 'actualizar' : 'crear';

        const datos = {
            accion: accion,
            nombre: document.getElementById('estNombre').value.trim(),
            nombre_corto: document.getElementById('estNombreCorto').value.trim(),
            comuna_id: parseInt(document.getElementById('estComuna').value)
        };

        if (!datos.nombre || !datos.nombre_corto || !datos.comuna_id) {
            mostrarNotificacion('Complete todos los campos obligatorios', 'warning');
            return;
        }

        if (!id) {
            datos.codigo_establecimiento = parseInt(document.getElementById('estCodigo').value);
            if (!datos.codigo_establecimiento) {
                mostrarNotificacion('El código es obligatorio', 'warning');
                return;
            }
        }

        if (id) datos.id = parseInt(id);

        mostrarLoading(true);
        try {
            const respuesta = await fetchAPI('api/establecimientos.php', {
                method: 'POST',
                body: JSON.stringify(datos)
            });

            if (respuesta.success) {
                mostrarNotificacion(respuesta.data ? 'Establecimiento creado exitosamente' : 'Establecimiento actualizado exitosamente', 'success');
                this.modalEst.hide();
                await this.cargarEstablecimientos();
                await this.cargarEstadisticas();
            } else {
                mostrarNotificacion(respuesta.error, 'danger');
            }
        } catch (error) {
            mostrarNotificacion('Error de conexión', 'danger');
        } finally {
            mostrarLoading(false);
        }
    }

    async toggleEstablecimiento(id, nuevoEstado) {
        const accion = nuevoEstado ? 'activar' : 'desactivar';
        const confirmado = await confirmarAccion(`¿Está seguro de ${accion} este establecimiento?`);
        if (!confirmado) return;

        mostrarLoading(true);
        try {
            const respuesta = await fetchAPI('api/establecimientos.php', {
                method: 'POST',
                body: JSON.stringify({ accion: 'toggle', id: id, activo: nuevoEstado })
            });

            if (respuesta.success) {
                mostrarNotificacion(respuesta.data || 'Estado actualizado', 'success');
                await this.cargarEstablecimientos();
                await this.cargarEstadisticas();
            } else {
                mostrarNotificacion(respuesta.error, 'danger');
            }
        } catch (error) {
            mostrarNotificacion('Error de conexión', 'danger');
        } finally {
            mostrarLoading(false);
        }
    }

    async abrirModalReferentes(establecimientoId, nombreEstablecimiento) {
        this.establecimientoActual = establecimientoId;
        document.getElementById('referentesTitulo').textContent = `Referentes - ${nombreEstablecimiento}`;
        document.getElementById('refEstablecimientoId').value = establecimientoId;
        this.modalRef.show();
        await this.cargarReferentes();
    }

    async cargarReferentes() {
        if (!this.establecimientoActual) return;

        try {
            const respuesta = await fetchAPI(`api/establecimientos.php?accion=listar_referentes&establecimiento_id=${this.establecimientoActual}`);
            if (respuesta.success) {
                this.renderizarTablaReferentes(respuesta.data);
            }
        } catch (error) {
            mostrarNotificacion('Error al cargar referentes', 'danger');
        }
    }

    renderizarTablaReferentes(referentes) {
        const tbody = document.getElementById('tablaReferentes');

        if (referentes.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-secondary py-4">
                        No hay registrados referentes para este establecimiento
                    </td>
                </tr>`;
            return;
        }

        let html = '';
        referentes.forEach(ref => {
            const estadoClase = ref.activo ? 'bg-green text-green-fg' : 'bg-secondary text-secondary-fg';
            const estadoTexto = ref.activo ? 'Activo' : 'Inactivo';
            const nombreClase = ref.activo ? '' : 'text-decoration-line-through text-secondary';

            html += `
                <tr>
                    <td class="fw-semibold ${nombreClase}">${this.escapeHtml(ref.nombre)}</td>
                    <td class="text-secondary">${this.escapeHtml(ref.cargo)}</td>
                    <td class="text-secondary">${this.escapeHtml(ref.telefono || '-')}</td>
                    <td class="text-secondary">${this.escapeHtml(ref.email || '-')}</td>
                    <td class="text-center">
                        <span class="badge ${estadoClase}">${estadoTexto}</span>
                    </td>
                    <td class="text-center">
                        <div class="btn-list justify-content-center">
                            <button class="btn btn-ghost-secondary btn-icon" 
                                    onclick="gestorEst.abrirEditarReferente(${ref.id})" 
                                    title="Editar" data-bs-toggle="tooltip">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                            </button>
                            <button class="btn btn-ghost-secondary btn-icon" 
                                    onclick="gestorEst.toggleReferente(${ref.id}, ${ref.activo ? 0 : 1})" 
                                    title="${ref.activo ? 'Desactivar' : 'Activar'}" data-bs-toggle="tooltip">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 4v16l13 -8z" /></svg>
                            </button>
                            <button class="btn btn-ghost-danger btn-icon" 
                                    onclick="gestorEst.eliminarReferente(${ref.id})" 
                                    title="Eliminar" data-bs-toggle="tooltip">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7h16" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /><path d="M10 12l4 4m0 -4l-4 4" /></svg>
                            </button>
                        </div>
                    </td>
                </tr>`;
        });

        tbody.innerHTML = html;
    }

    abrirCrearReferente() {
        document.getElementById('formReferente').reset();
        document.getElementById('refId').value = '';
        document.getElementById('modalReferenteFormTitulo').textContent = 'Nuevo Referente';
        document.getElementById('btnGuardarRef').textContent = 'Crear Referente';
        this.abrirModal('ReferenteForm');
    }

    async abrirEditarReferente(id) {
        try {
            const respuesta = await fetchAPI(`api/establecimientos.php?accion=obtener_referente&id=${id}`);
            if (respuesta.success) {
                const ref = respuesta.data;
                document.getElementById('refId').value = ref.id;
                document.getElementById('refNombre').value = ref.nombre;
                document.getElementById('refCargo').value = ref.cargo;
                document.getElementById('refTelefono').value = ref.telefono || '';
                document.getElementById('refEmail').value = ref.email || '';
                document.getElementById('modalReferenteFormTitulo').textContent = 'Editar Referente';
                document.getElementById('btnGuardarRef').textContent = 'Guardar Cambios';
                this.abrirModal('ReferenteForm');
            }
        } catch (error) {
            mostrarNotificacion('Error al cargar referente', 'danger');
        }
    }

    async guardarReferente() {
        const id = document.getElementById('refId').value;
        const accion = id ? 'actualizar_referente' : 'crear_referente';

        const datos = {
            accion: accion,
            establecimiento_id: this.establecimientoActual,
            nombre: document.getElementById('refNombre').value.trim(),
            cargo: document.getElementById('refCargo').value.trim(),
            telefono: document.getElementById('refTelefono').value.trim(),
            email: document.getElementById('refEmail').value.trim()
        };

        if (!datos.nombre || !datos.cargo) {
            mostrarNotificacion('Nombre y cargo son obligatorios', 'warning');
            return;
        }

        // Validar email en frontend
        if (datos.email && !this.validarEmail(datos.email)) {
            mostrarNotificacion('El formato del email no es válido', 'warning');
            return;
        }

        // Validar teléfono en frontend
        if (datos.telefono && !this.validarTelefono(datos.telefono)) {
            mostrarNotificacion('El formato del teléfono no es válido', 'warning');
            return;
        }

        if (id) datos.id = parseInt(id);

        mostrarLoading(true);
        try {
            const respuesta = await fetchAPI('api/establecimientos.php', {
                method: 'POST',
                body: JSON.stringify(datos)
            });

            if (respuesta.success) {
                mostrarNotificacion(id ? 'Referente actualizado' : 'Referente creado', 'success');
                this.cerrarModal('ReferenteForm');
                await this.cargarReferentes();
            } else {
                mostrarNotificacion(respuesta.error, 'danger');
            }
        } catch (error) {
            mostrarNotificacion('Error de conexión', 'danger');
        } finally {
            mostrarLoading(false);
        }
    }

    async toggleReferente(id, nuevoEstado) {
        const accion = nuevoEstado ? 'activar' : 'desactivar';
        const confirmado = await confirmarAccion(`¿Está seguro de ${accion} este referente?`);
        if (!confirmado) return;

        try {
            const respuesta = await fetchAPI('api/establecimientos.php', {
                method: 'POST',
                body: JSON.stringify({ accion: 'toggle_referente', id: id, activo: nuevoEstado })
            });

            if (respuesta.success) {
                mostrarNotificacion(respuesta.data || 'Estado actualizado', 'success');
                await this.cargarReferentes();
            } else {
                mostrarNotificacion(respuesta.error, 'danger');
            }
        } catch (error) {
            mostrarNotificacion('Error de conexión', 'danger');
        }
    }

    async eliminarReferente(id) {
        const confirmado = await confirmarAccion('¿Está seguro de eliminar este referente? Esta acción no se puede deshacer.');
        if (!confirmado) return;

        try {
            const respuesta = await fetchAPI('api/establecimientos.php', {
                method: 'POST',
                body: JSON.stringify({ accion: 'eliminar_referente', id: id })
            });

            if (respuesta.success) {
                mostrarNotificacion('Referente eliminado', 'success');
                await this.cargarReferentes();
            } else {
                mostrarNotificacion(respuesta.error, 'danger');
            }
        } catch (error) {
            mostrarNotificacion('Error de conexión', 'danger');
        }
    }

    validarEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    validarTelefono(telefono) {
        const limpio = telefono.replace(/[^0-9+]/g, '');
        return /^(\+56)?[0-9]{8,12}$/.test(limpio) || /^[0-9]{7,12}$/.test(limpio);
    }

    escapeHtml(texto) {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }
}

function mostrarLoading(mostrar) {
    const spinner = document.getElementById('loading-spinner');
    if (spinner) {
        spinner.classList.toggle('d-none', !mostrar);
    }
}

// Inicializar cuando el DOM esté listo
let gestorEst;
document.addEventListener('DOMContentLoaded', () => {
    window.gestorEst = new GestorEstablecimientos();
    gestorEst = window.gestorEst;
});
