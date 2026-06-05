/**
 * papelera.js - Gestión de la Papelera de Eliminadas
 * Restaurar, eliminar permanente, acciones masivas, filtros y paginación
 */

'use strict';

class PapeleraEliminadas {
    constructor() {
        this.anio = window.PAPELERA_CONFIG?.anio || new Date().getFullYear();
        this.paginaActual = 1;
        this.totalPaginas = 1;
        this.totalRegistros = 0;
        this.porPagina = 50;
        this.observaciones = [];
        this.seleccionados = [];
        this.confirmModal = null;

        this.inicializar();
    }

    inicializar() {
        this.configurarEventos();
        this.cargarEstadisticas();
        this.cargarObservaciones();
    }

    abrirModal() {
        document.getElementById('confirmModalBackdrop').classList.add('show');
        document.getElementById('confirmModal').classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    cerrarModal() {
        document.getElementById('confirmModalBackdrop').classList.remove('show');
        document.getElementById('confirmModal').classList.remove('show');
        document.body.style.overflow = '';
    }

    configurarEventos() {
        document.getElementById('btnApplyFilters').addEventListener('click', () => {
            this.paginaActual = 1;
            this.cargarObservaciones();
        });

        document.getElementById('btnClearFilters').addEventListener('click', () => this.limpiarFiltros());

        document.getElementById('selectAll').addEventListener('change', (e) => this.seleccionarTodos(e.target.checked));

        document.getElementById('btnRestoreSelected').addEventListener('click', () => this.restaurarSeleccionados());

        document.getElementById('btnDeletePermanentSelected').addEventListener('click', () => this.eliminarPermanenteSeleccionados());

        document.getElementById('filterComuna').addEventListener('change', () => this.cargarEstablecimientos());

        document.getElementById('confirmActionBtn').addEventListener('click', () => this.ejecutarAccionConfirmada());

        document.getElementById('confirmIrreversible').addEventListener('change', (e) => {
            document.getElementById('confirmActionBtn').disabled = !e.target.checked;
        });

        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('obs-checkbox')) {
                this.actualizarSeleccionados();
            }
        });
    }

    obtenerFiltros() {
        return {
            anio: this.anio,
            pagina: this.paginaActual,
            mes: document.getElementById('filterMes').value,
            comuna_id: document.getElementById('filterComuna').value,
            establecimiento_id: document.getElementById('filterEstablecimiento').value,
            registrador_id: document.getElementById('filterRegistrador').value,
            busqueda: document.getElementById('filterBusqueda').value
        };
    }

    async cargarObservaciones() {
        const loading = document.getElementById('loadingIndicator');
        const tabla = document.getElementById('observationsTable');
        const vacio = document.getElementById('emptyState');
        const paginacion = document.getElementById('paginationContainer');

        loading.classList.remove('d-none');
        tabla.classList.add('d-none');
        vacio.classList.add('d-none');
        paginacion.classList.add('d-none');

        const filtros = this.obtenerFiltros();
        const params = new URLSearchParams();
        Object.entries(filtros).forEach(([k, v]) => {
            if (v !== '' && v !== null && v !== undefined) params.append(k, v);
        });

        try {
            const respuesta = await fetch(`api/eliminadas.php?accion=listar&${params}`);
            const datos = await respuesta.json();

            if (!datos.success) {
                throw new Error(datos.error || 'Error al cargar observaciones');
            }

            this.observaciones = datos.data.datos || [];
            this.totalRegistros = datos.data.total || 0;
            this.paginaActual = datos.data.pagina || 1;
            this.totalPaginas = datos.data.totalPaginas || 1;
            this.porPagina = datos.data.porPagina || 50;

            document.getElementById('obsCount').textContent = `(${this.totalRegistros})`;

            if (this.observaciones.length > 0) {
                this.renderizarTabla();
                tabla.classList.remove('d-none');
                this.renderizarPaginacion();
                paginacion.classList.remove('d-none');
            } else {
                vacio.classList.remove('d-none');
            }

            this.limpiarSeleccion();
            this.cargarEstadisticas();

        } catch (error) {
            console.error('Error al cargar observaciones eliminadas:', error);
            showError('Error al cargar: ' + error.message);
        } finally {
            loading.classList.add('d-none');
        }
    }

    renderizarTabla() {
        const tbody = document.getElementById('observationsBody');
        tbody.innerHTML = '';

        this.observaciones.forEach(obs => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-id', obs.id);

            const serieHoja = obs.serie && obs.hoja ? `${obs.serie} / ${obs.hoja}` : '-';
            const fechaEliminacion = this.formatearFecha(obs.fecha_eliminacion);
            const motivo = obs.motivo_eliminacion || '-';
            const estadoBadge = this.obtenerBadgeEstado(obs.estado_clasificacion);

            tr.innerHTML = `
                <td><input type="checkbox" class="form-check-input obs-checkbox" value="${obs.id}"></td>
                <td><span class="text-muted">#${obs.observacion_original_id}</span></td>
                <td>${fechaEliminacion}</td>
                <td>
                    <div class="fw-semibold">${this.escapeHtml(obs.nombre_corto || obs.establecimiento_nombre)}</div>
                    <div class="text-secondary text-sm">${this.escapeHtml(obs.comuna_nombre)}</div>
                </td>
                <td>${this.escapeHtml(serieHoja)}</td>
                <td>${this.escapeHtml(obs.mes)}</td>
                <td>${estadoBadge}</td>
                <td class="text-secondary">${this.escapeHtml(obs.eliminado_por_nombre)}</td>
                <td class="text-secondary text-truncate" style="max-width: 200px;" title="${this.escapeHtml(motivo)}">
                    ${this.escapeHtml(motivo)}
                </td>
                <td class="text-end">
                    <div class="btn-list justify-content-end">
                        <button class="btn btn-ghost-primary btn-icon btn-restaurar" data-id="${obs.id}" title="Restaurar">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 8l0 4l2 2" /><path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" /></svg>
                        </button>
                        <button class="btn btn-ghost-danger btn-icon btn-eliminar-permanente" data-id="${obs.id}" title="Eliminar permanentemente">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.querySelectorAll('.btn-restaurar').forEach(btn => {
            btn.addEventListener('click', () => this.confirmarRestaurar([parseInt(btn.dataset.id)]));
        });

        document.querySelectorAll('.btn-eliminar-permanente').forEach(btn => {
            btn.addEventListener('click', () => this.confirmarEliminarPermanente([parseInt(btn.dataset.id)]));
        });
    }

    renderizarPaginacion() {
        const info = document.getElementById('paginationInfo');
        const botones = document.getElementById('paginationButtons');

        const inicio = (this.paginaActual - 1) * this.porPagina + 1;
        const fin = Math.min(this.paginaActual * this.porPagina, this.totalRegistros);
        info.textContent = `Mostrando ${inicio}-${fin} de ${this.totalRegistros}`;

        botones.innerHTML = '';

        if (this.totalPaginas <= 1) return;

        const maxBotones = 7;
        let paginaInicio = Math.max(1, this.paginaActual - Math.floor(maxBotones / 2));
        let paginaFin = Math.min(this.totalPaginas, paginaInicio + maxBotones - 1);

        if (paginaFin - paginaInicio < maxBotones - 1) {
            paginaInicio = Math.max(1, paginaFin - maxBotones + 1);
        }

        if (this.paginaActual > 1) {
            botones.appendChild(this.crearBotonPagina('<', () => this.irAPagina(this.paginaActual - 1), false));
        }

        if (paginaInicio > 1) {
            botones.appendChild(this.crearBotonPagina('1', () => this.irAPagina(1), false));
            if (paginaInicio > 2) {
                const li = document.createElement('li');
                li.className = 'page-item disabled';
                li.innerHTML = '<span class="page-link">...</span>';
                botones.appendChild(li);
            }
        }

        for (let i = paginaInicio; i <= paginaFin; i++) {
            botones.appendChild(this.crearBotonPagina(i.toString(), () => this.irAPagina(i), i === this.paginaActual));
        }

        if (paginaFin < this.totalPaginas) {
            if (paginaFin < this.totalPaginas - 1) {
                const li = document.createElement('li');
                li.className = 'page-item disabled';
                li.innerHTML = '<span class="page-link">...</span>';
                botones.appendChild(li);
            }
            botones.appendChild(this.crearBotonPagina(this.totalPaginas.toString(), () => this.irAPagina(this.totalPaginas), false));
        }

        if (this.paginaActual < this.totalPaginas) {
            botones.appendChild(this.crearBotonPagina('>', () => this.irAPagina(this.paginaActual + 1), false));
        }
    }

    crearBotonPagina(texto, onClick, activo) {
        const li = document.createElement('li');
        li.className = `page-item${activo ? ' active' : ''}`;
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = texto;
        a.addEventListener('click', (e) => {
            e.preventDefault();
            if (!activo) onClick();
        });
        li.appendChild(a);
        return li;
    }

    irAPagina(pagina) {
        this.paginaActual = pagina;
        this.cargarObservaciones();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async cargarEstadisticas() {
        try {
            const respuesta = await fetch(`api/eliminadas.php?accion=estadisticas&anio=${this.anio}`);
            const datos = await respuesta.json();
            if (datos.success) this.renderizarEstadisticas(datos.data);
        } catch (error) {
            console.error('Error al cargar estadísticas:', error);
        }
    }

    renderizarEstadisticas(stats) {
        const contenedor = document.getElementById('statsContainer');

        const totalEliminador = stats.por_eliminador && stats.por_eliminador.length > 0
            ? stats.por_eliminador[0] : null;

        const textoEstados = stats.por_estado && stats.por_estado.length > 0
            ? stats.por_estado.map(e => `${this.escapeHtml(e.estado_clasificacion || 'Sin clasificar')}: ${e.total}`).join(', ')
            : 'Sin datos';

        contenedor.innerHTML = `
            <div class="col-md-3">
                <div class="card card-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="bg-danger-lt p-2 rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                        </span>
                        <div>
                            <div class="h1 mb-0 text-danger">${stats.total}</div>
                            <div class="text-secondary small">Total Eliminadas</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="bg-primary-lt p-2 rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 3v18h18" /><path d="M20 18v3" /><path d="M16 16v5" /><path d="M12 13v8" /><path d="M8 16v5" /><path d="M3 11c6 0 5 -5 9 -5s3 5 9 5" /></svg>
                        </span>
                        <div>
                            <div class="fw-bold text-primary">Por Estado</div>
                            <div class="text-secondary small">${textoEstados}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="bg-info-lt p-2 rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-12z" /><path d="M16 3v4" /><path d="M8 3v4" /><path d="M4 11h16" /><path d="M11 15h1" /><path d="M12 15v3" /></svg>
                        </span>
                        <div>
                            <div class="fw-bold text-info">Por Mes</div>
                            <div class="text-secondary small">${stats.por_mes?.length || 0} meses con registros</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-sm">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="bg-warning-lt p-2 rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
                        </span>
                        <div>
                            <div class="fw-bold text-warning">Mayor Eliminador</div>
                            <div class="text-secondary small">${totalEliminador ? `${this.escapeHtml(totalEliminador.nombre_completo)} (${totalEliminador.total})` : 'N/A'}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    seleccionarTodos(seleccionar) {
        document.querySelectorAll('.obs-checkbox').forEach(cb => cb.checked = seleccionar);
        this.actualizarSeleccionados();
    }

    actualizarSeleccionados() {
        this.seleccionados = Array.from(document.querySelectorAll('.obs-checkbox:checked'))
            .map(cb => parseInt(cb.value));

        const btnRestaurar = document.getElementById('btnRestoreSelected');
        const btnEliminar = document.getElementById('btnDeletePermanentSelected');
        const contador = document.getElementById('selectedCount');
        const haySeleccion = this.seleccionados.length > 0;

        btnRestaurar.disabled = !haySeleccion;
        btnEliminar.disabled = !haySeleccion;
        contador.classList.toggle('d-none', !haySeleccion);
        if (haySeleccion) {
            contador.querySelector('span').textContent = this.seleccionados.length;
        }
    }

    limpiarSeleccion() {
        this.seleccionados = [];
        document.getElementById('selectAll').checked = false;
        document.getElementById('btnRestoreSelected').disabled = true;
        document.getElementById('btnDeletePermanentSelected').disabled = true;
        document.getElementById('selectedCount').classList.add('d-none');
    }

    confirmarRestaurar(ids) {
        const cantidad = ids.length;
        const mensaje = cantidad === 1
            ? '¿Restaurar esta observación? Volverá a la tabla principal con estado pendiente.'
            : `¿Restaurar ${cantidad} observaciones seleccionadas? Volverán a la tabla principal con estado pendiente.`;

        this.mostrarModalConfirmacion(
            'Restaurar Observación' + (cantidad > 1 ? 'es' : ''),
            mensaje,
            'restaurar',
            ids,
            false
        );
    }

    confirmarEliminarPermanente(ids) {
        const cantidad = ids.length;
        const mensaje = cantidad === 1
            ? '¿Eliminar permanentemente esta observación? Esta acción no se puede deshacer.'
            : `¿Eliminar permanentemente ${cantidad} observaciones? Esta acción no se puede deshacer.`;

        this.mostrarModalConfirmacion(
            'Eliminar Permanentemente',
            mensaje,
            'eliminar_permanente',
            ids,
            true
        );
    }

    restaurarSeleccionados() {
        if (this.seleccionados.length === 0) return;
        this.confirmarRestaurar([...this.seleccionados]);
    }

    eliminarPermanenteSeleccionados() {
        if (this.seleccionados.length === 0) return;
        this.confirmarEliminarPermanente([...this.seleccionados]);
    }

    mostrarModalConfirmacion(titulo, mensaje, accion, ids, requiereCheckbox) {
        document.getElementById('confirmTitle').textContent = titulo;
        document.getElementById('confirmMessage').textContent = mensaje;
        document.getElementById('confirmComment').value = '';

        const checkboxContainer = document.getElementById('confirmCheckboxContainer');
        const checkbox = document.getElementById('confirmIrreversible');
        const btnConfirmar = document.getElementById('confirmActionBtn');

        this.accionPendiente = accion;
        this.idsPendientes = ids;

        if (requiereCheckbox) {
            checkboxContainer.classList.remove('d-none');
            checkbox.checked = false;
            btnConfirmar.disabled = true;
        } else {
            checkboxContainer.classList.add('d-none');
            btnConfirmar.disabled = false;
        }

        this.abrirModal();
    }

    async ejecutarAccionConfirmada() {
        const accion = this.accionPendiente;
        const ids = this.idsPendientes;
        const comentario = document.getElementById('confirmComment').value;

        if (accion.includes('eliminar_permanente')) {
            const checkbox = document.getElementById('confirmIrreversible');
            if (!checkbox.checked) {
                showError('Debe confirmar que entiende que esta acción es irreversible.');
                return;
            }
        }

        this.cerrarModal();

        try {
            const esMasivo = ids.length > 1;
            const accionApi = esMasivo
                ? (accion === 'restaurar' ? 'restaurar_masivo' : 'eliminar_permanente_masivo')
                : accion;

            const payload = {
                accion: accionApi,
                ids: ids
            };

            if (comentario) payload.comentario = comentario;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || localStorage.getItem('csrf_token') || '';

            const respuesta = await fetch('api/eliminadas.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const datos = await respuesta.json();

            if (datos.success) {
                showSuccess(datos.data ? this.generarMensajeResumen(datos.data, accion) : datos.error || 'Operación exitosa');
                this.cargarObservaciones();
            } else {
                throw new Error(datos.error || 'Error en la operación');
            }
        } catch (error) {
            console.error('Error en acción:', error);
            showError('Error: ' + error.message);
        }
    }

    generarMensajeResumen(resultados, accion) {
        const exitosos = resultados.exitosos?.length || 0;
        const fallos = resultados.fallos || [];

        if (fallos.length === 0) {
            return `${exitosos} observación(es) ${accion === 'restaurar' ? 'restaurada(s)' : 'eliminada(s)'} correctamente.`;
        }

        const detallesFallos = fallos.map(f => `ID ${f.id} (${f.error})`).join(', ');
        return `${exitosos} procesada(s) correctamente. ${fallos.length} fallo(s): ${detallesFallos}`;
    }

    async cargarEstablecimientos() {
        const comunaId = document.getElementById('filterComuna').value;
        const select = document.getElementById('filterEstablecimiento');
        select.innerHTML = '<option value="">Todos</option>';
        select.disabled = !comunaId;

        if (!comunaId) return;

        try {
            const respuesta = await fetch(`api/locations.php?accion=establecimientos&comuna_id=${comunaId}`);
            const datos = await respuesta.json();

            if (datos.success && datos.data) {
                datos.data.forEach(est => {
                    const option = document.createElement('option');
                    option.value = est.id;
                    option.textContent = est.nombre_corto || est.nombre;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error al cargar establecimientos:', error);
        }
    }

    limpiarFiltros() {
        document.getElementById('filterMes').value = '';
        document.getElementById('filterComuna').value = '';
        document.getElementById('filterEstablecimiento').value = '';
        document.getElementById('filterEstablecimiento').disabled = true;
        document.getElementById('filterRegistrador').value = '';
        document.getElementById('filterBusqueda').value = '';
        this.paginaActual = 1;
        this.cargarObservaciones();
    }

    formatearFecha(fechaStr) {
        if (!fechaStr) return '';
        const fecha = new Date(fechaStr);
        if (isNaN(fecha.getTime())) return fechaStr;
        return fecha.toLocaleDateString('es-CL', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    escapeHtml(texto) {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    obtenerBadgeEstado(estado) {
        const colores = {
            'pendiente': 'bg-yellow',
            'aprobado': 'bg-green',
            'rechazado': 'bg-red',
            'error': 'bg-rose',
            'justificado': 'bg-sky'
        };
        const color = colores[estado] || 'bg-secondary';
        const texto = estado ? estado.charAt(0).toUpperCase() + estado.slice(1) : 'Sin clasificar';
        return `<span class="badge ${color}">${this.escapeHtml(texto)}</span>`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.PapeleraApp = new PapeleraEliminadas();
});
