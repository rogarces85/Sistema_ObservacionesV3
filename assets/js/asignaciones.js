/**
 * asignaciones.js - Gestión de asignaciones de establecimientos a registradores
 * Sistema de Observaciones REM
 */

'use strict';

class GestorAsignaciones {
    constructor() {
        this.anio = document.getElementById('selectorAnio').value;
        this.registradores = [];
        this.registradorSeleccionado = null;
        this.establecimientos = [];
        this.temporales = [];
        this.modalAsignar = null;
        this.modalReferentes = null;
        this.establecimientosSeleccionados = [];

        this.inicializar();
    }

    async inicializar() {
        await this.cargarRegistradores();
        await this.cargarTemporales();
        await this.cargarEstadisticas();

        this.configurarEventos();
    }

    configurarEventos() {
        // Cambio de año
        document.getElementById('selectorAnio').addEventListener('change', (e) => {
            this.anio = e.target.value;
            this.registradorSeleccionado = null;
            this.cargarRegistradores();
            this.cargarTemporales();
            this.cargarEstadisticas();
            this.limpiarPanelDerecho();
        });

        // Copiar año anterior
        document.getElementById('btnCopiarAnio').addEventListener('click', () => this.copiarAnioAnterior());

        // Botón asignar
        document.getElementById('btnAsignar').addEventListener('click', () => this.abrirModalAsignar());

        // Guardar asignaciones
        document.getElementById('btnGuardarAsignaciones').addEventListener('click', () => this.guardarAsignaciones());

        // Tipo de asignación (anual/temporal)
        document.querySelectorAll('input[name="tipoAsignacion"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                const esTemporal = e.target.value === 'temporal';
                document.getElementById('periodoContainer').style.display = esTemporal ? 'block' : 'none';
            });
        });

        // Periodo de asignación (ALL/meses específicos)
        document.querySelectorAll('input[name="periodoAsignacion"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                const container = document.getElementById('mesesEspecificosContainer');
                container.classList.toggle('d-none', e.target.value !== 'MESES');
            });
        });

        // Búsqueda de establecimientos
        const inputBusqueda = document.getElementById('buscarEstablecimiento');
        const busquedaDebounce = debounce(() => {
            this.filtrarEstablecimientosDisponibles(inputBusqueda.value.trim());
        }, 300);
        inputBusqueda.addEventListener('input', busquedaDebounce);
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
        if (nombre === 'Asignar') {
            this.establecimientosSeleccionados = [];
            document.getElementById('buscarEstablecimiento').value = '';
            this.limpiarCheckboxesMeses();
        }
    }

    async cargarRegistradores() {
        try {
            const respuesta = await fetchAPI(`api/asignaciones.php?accion=registradores`);
            if (respuesta.success) {
                this.registradores = respuesta.data;
                this.renderizarListaRegistradores();
            } else {
                mostrarNotificacion('Error al cargar registradores', 'danger');
            }
        } catch (error) {
            mostrarNotificacion('Error de conexión', 'danger');
        }
    }

    renderizarListaRegistradores() {
        const container = document.getElementById('listaRegistradores');

        if (this.registradores.length === 0) {
            container.innerHTML = '<div class="text-center py-4 text-secondary">No hay registradores activos</div>';
            return;
        }

        let html = '';
        this.registradores.forEach(reg => {
            const activo = this.registradorSeleccionado && this.registradorSeleccionado.id === reg.id;
            html += `
                <a href="#" class="list-group-item list-group-item-action ${activo ? 'active' : ''}" 
                   onclick="event.preventDefault(); gestorAsig.seleccionarRegistrador(${reg.id})">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <span class="avatar" style="background: var(--tblr-primary)">${this.obtenerIniciales(reg.nombre_completo)}</span>
                        </div>
                        <div class="col">
                            <div class="fw-semibold">${this.escapeHtml(reg.nombre_completo)}</div>
                            <div class="text-secondary small">@${this.escapeHtml(reg.username)}</div>
                        </div>
                    </div>
                </a>`;
        });

        container.innerHTML = html;
    }

    async seleccionarRegistrador(id) {
        this.registradorSeleccionado = this.registradores.find(r => r.id === id);
        this.renderizarListaRegistradores();

        document.getElementById('registradorSeleccionadoTexto').textContent = this.registradorSeleccionado.nombre_completo;
        document.getElementById('accionesAsignacion').classList.remove('d-none');

        await this.cargarEstablecimientosDelRegistrador();
    }

    async cargarEstablecimientosDelRegistrador() {
        if (!this.registradorSeleccionado) return;

        mostrarLoading(true);
        try {
            const respuesta = await fetchAPI(`api/asignaciones.php?accion=establecimientos&registrador_id=${this.registradorSeleccionado.id}&anio=${this.anio}`);
            if (respuesta.success) {
                this.establecimientos = respuesta.data;
                this.renderizarEstablecimientos();
            } else {
                mostrarNotificacion('Error al cargar establecimientos', 'danger');
            }
        } catch (error) {
            mostrarNotificacion('Error de conexión', 'danger');
        } finally {
            mostrarLoading(false);
        }
    }

    renderizarEstablecimientos() {
        const container = document.getElementById('establecimientosContainer');

        if (this.establecimientos.length === 0) {
            container.innerHTML = '<div class="empty"><p class="empty-title">Sin establecimientos</p><p class="empty-subtitle text-secondary">No hay establecimientos activos en el sistema</p></div>';
            return;
        }

        // Agrupar por comuna
        const porComuna = {};
        this.establecimientos.forEach(est => {
            if (!porComuna[est.comuna_nombre]) {
                porComuna[est.comuna_nombre] = [];
            }
            porComuna[est.comuna_nombre].push(est);
        });

        let html = '';
        Object.keys(porComuna).sort().forEach(comuna => {
            html += `<h4 class="mt-3 mb-2 text-primary">${this.escapeHtml(comuna)}</h4>`;
            html += '<div class="table-responsive"><table class="table table-vcenter card-table table-hover"><tbody>';

            porComuna[comuna].forEach(est => {
                const asignado = est.asignado_a_mi === 1;
                const meses = asignado ? this.formatearMeses(est.meses_mios) : '-';
                const tipo = asignado ? (est.tipo_asignacion_mi === 'temporal' ? 'Temporal' : 'Anual') : '';
                const tipoBadge = asignado ? (est.tipo_asignacion_mi === 'temporal' ? 'bg-orange-lt' : 'bg-green-lt') : '';
                const tieneTemporalOtro = est.asignado_a_usuario_id && est.tipo_asignacion_otro === 'temporal';

                html += `
                    <tr>
                        <td class="fw-semibold">${this.escapeHtml(est.nombre_corto || est.nombre)}</td>
                        <td class="text-secondary">${meses}</td>
                        <td class="text-center">
                            ${asignado ? `<span class="badge ${tipoBadge}">${tipo}</span>` : '<span class="badge bg-secondary-lt">Sin asignar</span>'}
                        </td>
                        <td class="text-center">
                            ${tieneTemporalOtro ? '<span class="badge bg-warning-lt" title="Tiene reasignación temporal"><svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-clock" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 7v5l3 3" /></svg> Temporal</span>' : ''}
                        </td>
                        <td class="text-center">
                            <div class="btn-list justify-content-center">
                                <button class="btn btn-ghost-secondary btn-icon" 
                                        onclick="gestorAsig.verReferentes(${est.id}, '${this.escapeHtml(est.nombre)}')" 
                                        title="Ver referentes" data-bs-toggle="tooltip">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" /><path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /><path d="M21 21v-2a4 4 0 0 0 -3 -3.85" /></svg>
                                </button>
                                ${asignado ? `
                                <button class="btn btn-ghost-danger btn-icon" 
                                        onclick="gestorAsig.eliminarAsignacion(${est.id}, '${est.tipo_asignacion_mi}')" 
                                        title="Quitar asignación" data-bs-toggle="tooltip">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                                </button>` : ''}
                            </div>
                        </td>
                    </tr>`;
            });

            html += '</tbody></table></div>';
        });

        container.innerHTML = html;
    }

    async cargarTemporales() {
        try {
            const respuesta = await fetchAPI(`api/asignaciones.php?accion=temporales&anio=${this.anio}`);
            if (respuesta.success) {
                this.temporales = respuesta.data;
                this.renderizarTemporales();
            }
        } catch (error) {
            console.error('Error al cargar temporales:', error);
        }
    }

    renderizarTemporales() {
        const container = document.getElementById('reasignacionesTemporalesContainer');

        if (this.temporales.length === 0) {
            container.innerHTML = '<div class="empty py-3"><p class="text-secondary mb-0">No hay reasignaciones temporales activas</p></div>';
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-vcenter card-table table-hover"><thead><tr>';
        html += '<th>Establecimiento</th><th>Comuna</th><th>Meses</th><th>Registrador Temporal</th><th>Titular Anual</th><th>Fecha</th>';
        html += '</tr></thead><tbody>';

        this.temporales.forEach(temp => {
            const titular = temp.titular_anual ? temp.titular_anual.nombre_completo : '<span class="text-secondary">Sin titular</span>';
            html += `
                <tr>
                    <td class="fw-semibold">${this.escapeHtml(temp.establecimiento_nombre)}</td>
                    <td class="text-secondary">${this.escapeHtml(temp.comuna_nombre)}</td>
                    <td><span class="badge bg-orange-lt">${this.formatearMeses(temp.meses)}</span></td>
                    <td>${this.escapeHtml(temp.registrador_nombre)}</td>
                    <td>${titular}</td>
                    <td class="text-secondary">${formatearFecha(temp.fecha_creacion)}</td>
                </tr>`;
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    async cargarEstadisticas() {
        try {
            const respuesta = await fetchAPI(`api/asignaciones.php?accion=listar&anio=${this.anio}`);
            if (respuesta.success) {
                const stats = respuesta.data;
                document.getElementById('statRegistradores').textContent = stats.length;
                const conAsignacion = stats.filter(s => s.total_establecimientos > 0).length;
                document.getElementById('statAsignados').textContent = conAsignacion;
                document.getElementById('statTemporales').textContent = this.temporales.length;
                document.getElementById('statSinAsignar').textContent = stats.length - conAsignacion;
            }
        } catch (error) {
            console.error('Error al cargar estadísticas:', error);
        }
    }

    async abrirModalAsignar() {
        if (!this.registradorSeleccionado) return;

        document.getElementById('modalAsignarInfo').textContent = `Asignando a: ${this.registradorSeleccionado.nombre_completo} — Año ${this.anio}`;
        document.getElementById('anioPeriodoLabel').textContent = this.anio;

        // Resetear formulario
        document.querySelector('input[name="tipoAsignacion"][value="anual"]').checked = true;
        document.querySelector('input[name="periodoAsignacion"][value="ALL"]').checked = true;
        document.getElementById('periodoContainer').style.display = 'none';
        document.getElementById('mesesEspecificosContainer').classList.add('d-none');
        this.limpiarCheckboxesMeses();
        this.establecimientosSeleccionados = [];

        // Cargar todos los establecimientos activos
        await this.cargarTodosEstablecimientos();
        this.renderizarEstablecimientosDisponibles();

        this.abrirModal('Asignar');
    }

    async cargarTodosEstablecimientos() {
        try {
            const respuesta = await fetchAPI(`api/asignaciones.php?accion=establecimientos&registrador_id=${this.registradorSeleccionado.id}&anio=${this.anio}`);
            if (respuesta.success) {
                this.establecimientos = respuesta.data;
            }
        } catch (error) {
            mostrarNotificacion('Error al cargar establecimientos', 'danger');
        }
    }

    renderizarEstablecimientosDisponibles(filtro = '') {
        const container = document.getElementById('listaEstablecimientosDisponibles');
        let establecimientos = this.establecimientos;

        if (filtro) {
            const term = filtro.toLowerCase();
            establecimientos = establecimientos.filter(est =>
                est.nombre.toLowerCase().includes(term) ||
                est.nombre_corto.toLowerCase().includes(term) ||
                est.comuna_nombre.toLowerCase().includes(term)
            );
        }

        if (establecimientos.length === 0) {
            container.innerHTML = '<div class="p-3 text-center text-secondary">No se encontraron establecimientos</div>';
            return;
        }

        // Agrupar por comuna
        const porComuna = {};
        establecimientos.forEach(est => {
            if (!porComuna[est.comuna_nombre]) {
                porComuna[est.comuna_nombre] = [];
            }
            porComuna[est.comuna_nombre].push(est);
        });

        let html = '';
        Object.keys(porComuna).sort().forEach(comuna => {
            html += `<div class="p-2 bg-light fw-semibold text-primary small">${this.escapeHtml(comuna)}</div>`;
            porComuna[comuna].forEach(est => {
                const asignado = est.asignado_a_mi === 1;
                const checked = this.establecimientosSeleccionados.includes(est.id) ? 'checked' : '';
                const disabled = asignado ? '' : '';
                html += `
                    <label class="form-check p-2 border-bottom d-flex align-items-center ${asignado ? 'bg-green-lt' : ''}">
                        <input class="form-check-input me-2 est-checkbox" type="checkbox" value="${est.id}" ${checked} ${disabled}>
                        <span class="flex-grow-1">${this.escapeHtml(est.nombre_corto || est.nombre)}</span>
                        ${asignado ? '<span class="badge bg-green-lt small">Asignado</span>' : ''}
                    </label>`;
            });
        });

        container.innerHTML = html;

        // Eventos de checkboxes
        container.querySelectorAll('.est-checkbox').forEach(cb => {
            cb.addEventListener('change', (e) => {
                const id = parseInt(e.target.value);
                if (e.target.checked) {
                    if (!this.establecimientosSeleccionados.includes(id)) {
                        this.establecimientosSeleccionados.push(id);
                    }
                } else {
                    this.establecimientosSeleccionados = this.establecimientosSeleccionados.filter(i => i !== id);
                }
            });
        });
    }

    filtrarEstablecimientosDisponibles(filtro) {
        this.renderizarEstablecimientosDisponibles(filtro);
    }

    async guardarAsignaciones() {
        if (this.establecimientosSeleccionados.length === 0) {
            mostrarNotificacion('Seleccione al menos un establecimiento', 'warning');
            return;
        }

        const tipo = document.querySelector('input[name="tipoAsignacion"]:checked').value;
        let meses = 'ALL';

        if (tipo === 'temporal') {
            const periodo = document.querySelector('input[name="periodoAsignacion"]:checked').value;
            if (periodo === 'MESES') {
                const checkboxes = document.querySelectorAll('.mes-checkbox:checked');
                if (checkboxes.length === 0) {
                    mostrarNotificacion('Seleccione al menos un mes para asignación temporal', 'warning');
                    return;
                }
                meses = Array.from(checkboxes).map(cb => cb.value).join(',');
            }
        }

        mostrarLoading(true);
        try {
            const respuesta = await fetchAPI('api/asignaciones.php', {
                method: 'POST',
                body: JSON.stringify({
                    accion: 'masivo',
                    usuario_id: this.registradorSeleccionado.id,
                    establecimiento_ids: this.establecimientosSeleccionados,
                    anio: this.anio,
                    meses: meses,
                    tipo: tipo
                })
            });

            if (respuesta.success) {
                mostrarNotificacion(respuesta.data || 'Asignaciones guardadas exitosamente', 'success');
                this.cerrarModal('Asignar');
                await this.cargarEstablecimientosDelRegistrador();
                await this.cargarTemporales();
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

    async eliminarAsignacion(establecimientoId, tipo) {
        const confirmado = await confirmarAccion('¿Está seguro de quitar esta asignación?');
        if (!confirmado) return;

        mostrarLoading(true);
        try {
            const respuesta = await fetchAPI('api/asignaciones.php', {
                method: 'POST',
                body: JSON.stringify({
                    accion: 'eliminar',
                    establecimiento_id: establecimientoId,
                    tipo: tipo,
                    usuario_id: this.registradorSeleccionado.id,
                    anio: this.anio
                })
            });

            if (respuesta.success) {
                mostrarNotificacion('Asignación eliminada', 'success');
                await this.cargarEstablecimientosDelRegistrador();
                await this.cargarTemporales();
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

    async copiarAnioAnterior() {
        const anioOrigen = parseInt(this.anio) - 1;
        const anioDestino = parseInt(this.anio);

        if (anioOrigen < 2020) {
            mostrarNotificacion('No hay año anterior disponible para copiar', 'warning');
            return;
        }

        const confirmado = await confirmarAccion(`¿Copiar todas las asignaciones de ${anioOrigen} a ${anioDestino}?`);
        if (!confirmado) return;

        mostrarLoading(true);
        try {
            const respuesta = await fetchAPI('api/asignaciones.php', {
                method: 'POST',
                body: JSON.stringify({
                    accion: 'copiar',
                    anio_origen: anioOrigen,
                    anio_destino: anioDestino
                })
            });

            if (respuesta.success) {
                mostrarNotificacion(`${respuesta.data.cantidad} asignaciones copiadas de ${anioOrigen} a ${anioDestino}`, 'success');
                await this.cargarRegistradores();
                await this.cargarTemporales();
                await this.cargarEstadisticas();
                if (this.registradorSeleccionado) {
                    await this.cargarEstablecimientosDelRegistrador();
                }
            } else {
                mostrarNotificacion(respuesta.error, 'danger');
            }
        } catch (error) {
            mostrarNotificacion('Error de conexión', 'danger');
        } finally {
            mostrarLoading(false);
        }
    }

    async verReferentes(establecimientoId, nombreEstablecimiento) {
        document.getElementById('modalReferentesTitulo').textContent = `Referentes - ${nombreEstablecimiento}`;
        document.getElementById('modalReferentesBody').innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';
        this.abrirModal('Referentes');

        try {
            const respuesta = await fetchAPI(`api/asignaciones.php?accion=referentes&establecimiento_id=${establecimientoId}`);
            if (respuesta.success) {
                this.renderizarReferentes(respuesta.data);
            } else {
                document.getElementById('modalReferentesBody').innerHTML = '<div class="text-center text-secondary py-4">Error al cargar referentes</div>';
            }
        } catch (error) {
            document.getElementById('modalReferentesBody').innerHTML = '<div class="text-center text-secondary py-4">Error de conexión</div>';
        }
    }

    renderizarReferentes(referentes) {
        const container = document.getElementById('modalReferentesBody');

        if (referentes.length === 0) {
            container.innerHTML = '<div class="empty py-3"><p class="text-secondary mb-0">No hay referentes registrados para este establecimiento</p></div>';
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-vcenter"><thead><tr>';
        html += '<th>Nombre</th><th>Cargo</th><th>Teléfono</th><th>Email</th>';
        html += '</tr></thead><tbody>';

        referentes.forEach(ref => {
            html += `
                <tr>
                    <td class="fw-semibold">${this.escapeHtml(ref.nombre)}</td>
                    <td><span class="badge bg-azure-lt">${this.escapeHtml(ref.cargo)}</span></td>
                    <td class="text-secondary">${this.escapeHtml(ref.telefono || '-')}</td>
                    <td class="text-secondary">${this.escapeHtml(ref.email || '-')}</td>
                </tr>`;
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    limpiarPanelDerecho() {
        document.getElementById('registradorSeleccionadoTexto').textContent = 'Seleccione un registrador';
        document.getElementById('accionesAsignacion').classList.add('d-none');
        document.getElementById('establecimientosContainer').innerHTML = '<div class="empty"><div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 7v1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1h-18l2 -4h14l-2 4" /><path d="M5 21l0 -10.15" /><path d="M19 21l0 -10.15" /><path d="M9 21v-4a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v4" /></svg></div><p class="empty-title">Seleccione un registrador</p><p class="empty-subtitle text-secondary">Para ver sus establecimientos y datos de contacto</p></div>';
    }

    limpiarCheckboxesMeses() {
        document.querySelectorAll('.mes-checkbox').forEach(cb => cb.checked = false);
    }

    formatearMeses(meses) {
        if (!meses || meses === 'ALL') {
            return 'Todo el año';
        }
        const mesesArray = meses.split(',').map(m => obtenerNombreMes(parseInt(m)));
        return mesesArray.join(', ');
    }

    obtenerIniciales(nombre) {
        if (!nombre) return '??';
        return nombre.split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();
    }

    escapeHtml(texto) {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }
}

// Inicializar cuando el DOM esté listo
let gestorAsig;
document.addEventListener('DOMContentLoaded', () => {
    window.gestorAsig = new GestorAsignaciones();
    gestorAsig = window.gestorAsig;
});
