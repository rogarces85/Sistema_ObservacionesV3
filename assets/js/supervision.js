/**
 * supervision.js - Módulo de Supervisión
 * Gestión de observaciones: aprobar, cancelar, eliminar (soft delete)
 * Operaciones individuales y masivas no transaccionales
 */

'use strict';

const SupervisionApp = (() => {
    'use strict';

    let datosActuales = [];
    let idsSeleccionados = [];
    let paginaActual = 1;
    let totalPaginas = 1;

    function abrirModal(nombre) {
        document.getElementById(`modal${nombre}Backdrop`).classList.add('show');
        document.getElementById(`modal${nombre}`).classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModal(nombre) {
        document.getElementById(`modal${nombre}Backdrop`).classList.remove('show');
        document.getElementById(`modal${nombre}`).classList.remove('show');
        document.body.style.overflow = '';
    }

    const getAnio = () => document.getElementById('year-selector')?.value || new Date().getFullYear();

    function iniciar() {
        configurarEventos();
        cargarEstadisticas();
        cargarDatos();
    }

    function configurarEventos() {
        document.getElementById('btnAplicarFiltros').addEventListener('click', () => {
            paginaActual = 1;
            cargarDatos();
        });

        document.getElementById('btnLimpiarFiltros').addEventListener('click', limpiarFiltros);

        document.getElementById('seleccionarTodas').addEventListener('change', toggleSeleccionTodas);

        document.getElementById('btnAprobarSeleccion').addEventListener('click', abrirModalAprobar);
        document.getElementById('btnCancelarSeleccion').addEventListener('click', abrirModalCancelar);
        document.getElementById('btnEliminarSeleccion').addEventListener('click', abrirModalEliminar);

        document.getElementById('formAprobar').addEventListener('submit', confirmarAprobacion);
        document.getElementById('formCancelar').addEventListener('submit', confirmarCancelacion);
        document.getElementById('formEliminar').addEventListener('submit', confirmarEliminacion);

        document.getElementById('filtroComuna').addEventListener('change', cargarEstablecimientosPorComuna);

        document.getElementById('filtroBusqueda').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                paginaActual = 1;
                cargarDatos();
            }
        });
    }

    function obtenerFiltros() {
        return {
            anio: getAnio(),
            pagina: paginaActual,
            mes: document.getElementById('filtroMes').value || null,
            estado: document.getElementById('filtroEstado').value || null,
            establecimiento_id: document.getElementById('filtroEstablecimiento').value || null,
            comuna_id: document.getElementById('filtroComuna').value || null,
            usuario_registro_id: document.getElementById('filtroRegistrador').value || null,
            tipo_error: document.getElementById('filtroTipoError').value || null,
            busqueda: document.getElementById('filtroBusqueda').value || null
        };
    }

    async function cargarDatos() {
        const cuerpo = document.getElementById('cuerpoTablaSupervision');
        cuerpo.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">Cargando...</td></tr>';

        const filtros = obtenerFiltros();
        const params = new URLSearchParams();
        Object.entries(filtros).forEach(([clave, valor]) => {
            if (valor !== null && valor !== '') {
                params.append(clave, valor);
            }
        });

        try {
            const respuesta = await fetchAPI(`api/supervision.php?accion=get_filtered&${params.toString()}`);
            if (respuesta.success) {
                datosActuales = respuesta.data.datos || [];
                paginaActual = respuesta.data.pagina || 1;
                totalPaginas = respuesta.data.totalPaginas || 1;
                renderizarTabla(datosActuales);
                renderizarPaginacion(respuesta.data);
                actualizarEstadisticasRapidas(respuesta.data);
            } else {
                throw new Error(respuesta.error || 'Error al cargar datos');
            }
        } catch (error) {
            console.error('Error al cargar datos:', error);
            cuerpo.innerHTML = `<tr><td colspan="10" class="text-center text-danger py-4">${escaparHtml(error.message)}</td></tr>`;
        }
    }

    function renderizarTabla(datos) {
        const cuerpo = document.getElementById('cuerpoTablaSupervision');

        if (datos.length === 0) {
            cuerpo.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-4">No se encontraron observaciones</td></tr>';
            return;
        }

        cuerpo.innerHTML = datos.map(obs => `
            <tr>
                <td>
                    <input type="checkbox" class="form-check-input obs-checkbox" value="${obs.id}" ${idsSeleccionados.includes(String(obs.id)) ? 'checked' : ''}>
                </td>
                <td><span class="text-muted">#${obs.id}</span></td>
                <td>
                    <div class="fw-medium">${escaparHtml(obs.nombre_corto || obs.establecimiento_nombre)}</div>
                    <div class="text-muted small">${escaparHtml(obs.comuna_nombre)}</div>
                </td>
                <td>${escaparHtml(obs.mes)}</td>
                <td>
                    ${obs.codigo_serie ? `<span class="badge bg-secondary-lt">${escaparHtml(obs.codigo_serie)}</span>` : '-'}
                    ${obs.codigo_hoja ? `<span class="badge bg-secondary-lt ms-1">${escaparHtml(obs.codigo_hoja)}</span>` : ''}
                </td>
                <td><span class="badge ${obtenerClaseTipo(obs.tipo_error)}">${escaparHtml(obs.tipo_error)}</span></td>
                <td>${obtenerBadgeEstado(obs.estado_actual)}</td>
                <td>${escaparHtml(obs.usuario_registro_nombre)}</td>
                <td><span class="text-muted small">${formatearFecha(obs.fecha_creacion)}</span></td>
                <td class="text-end">
                    <div class="btn-list flex-nowrap justify-content-end">
                        <button class="btn btn-icon btn-sm" onclick="SupervisionApp.verDetalle(${obs.id})" title="Ver Detalle">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                        </button>
                        ${obs.estado_actual === 'pendiente' ? `
                        <button class="btn btn-icon btn-sm btn-success" onclick="SupervisionApp.aprobarIndividual(${obs.id})" title="Aprobar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
                        </button>
                        <button class="btn btn-icon btn-sm btn-warning" onclick="SupervisionApp.cancelarIndividual(${obs.id})" title="Cancelar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
                        </button>
                        <button class="btn btn-icon btn-sm btn-danger" onclick="SupervisionApp.eliminarIndividual(${obs.id})" title="Eliminar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                        </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `).join('');

        document.querySelectorAll('.obs-checkbox').forEach(cb => {
            cb.addEventListener('change', actualizarSeleccion);
        });

        actualizarBotonesMasivos();
    }

    function renderizarPaginacion(data) {
        const info = document.getElementById('paginacionInfo');
        const nav = document.getElementById('paginacionNav');

        const inicio = ((data.pagina - 1) * data.porPagina) + 1;
        const fin = Math.min(data.pagina * data.porPagina, data.total);
        info.textContent = data.total > 0 ? `Mostrando ${inicio}-${fin} de ${data.total}` : 'Sin resultados';

        if (data.totalPaginas <= 1) {
            nav.innerHTML = '';
            return;
        }

        let html = '<ul class="pagination m-0">';

        html += `<li class="page-item ${data.pagina <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="SupervisionApp.irPagina(${data.pagina - 1}); return false;" aria-label="Anterior">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 6l-6 6l6 6"/></svg>
            </a>
        </li>`;

        let paginaInicio = Math.max(1, data.pagina - 2);
        let paginaFin = Math.min(data.totalPaginas, data.pagina + 2);

        if (paginaInicio > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="SupervisionApp.irPagina(1); return false;">1</a></li>`;
            if (paginaInicio > 2) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        for (let p = paginaInicio; p <= paginaFin; p++) {
            html += `<li class="page-item ${p === data.pagina ? 'active' : ''}">
                <a class="page-link" href="#" onclick="SupervisionApp.irPagina(${p}); return false;">${p}</a>
            </li>`;
        }

        if (paginaFin < data.totalPaginas) {
            if (paginaFin < data.totalPaginas - 1) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            html += `<li class="page-item"><a class="page-link" href="#" onclick="SupervisionApp.irPagina(${data.totalPaginas}); return false;">${data.totalPaginas}</a></li>`;
        }

        html += `<li class="page-item ${data.pagina >= data.totalPaginas ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="SupervisionApp.irPagina(${data.pagina + 1}); return false;" aria-label="Siguiente">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 6l6 6l-6 6"/></svg>
            </a>
        </li>`;

        html += '</ul>';
        nav.innerHTML = html;
    }

    function irPagina(pagina) {
        if (pagina < 1 || pagina > totalPaginas) return;
        paginaActual = pagina;
        cargarDatos();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function cargarEstadisticas() {
        try {
            const respuesta = await fetchAPI(`api/observaciones.php?accion=stats&anio=${getAnio()}`);
            if (respuesta.success) {
                const stats = respuesta.data;
                document.getElementById('statTotal').textContent = stats.total || 0;

                const porEstado = stats.por_estado || [];
                const pendientes = porEstado.find(e => e.estado_actual === 'pendiente');
                const aprobados = porEstado.find(e => e.estado_actual === 'aprobado');
                const errores = porEstado.find(e => e.estado_actual === 'error');

                document.getElementById('statPendiente').textContent = pendientes?.total || 0;
                document.getElementById('statAprobado').textContent = aprobados?.total || 0;
                document.getElementById('statError').textContent = errores?.total || 0;
            }
        } catch (error) {
            console.error('Error al cargar estadísticas:', error);
        }
    }

    function actualizarEstadisticasRapidas(data) {
        document.getElementById('statTotal').textContent = data.total || 0;
    }

    function toggleSeleccionTodas() {
        const seleccionarTodas = document.getElementById('seleccionarTodas').checked;
        document.querySelectorAll('.obs-checkbox').forEach(cb => {
            cb.checked = seleccionarTodas;
        });
        actualizarSeleccion();
    }

    function actualizarSeleccion() {
        idsSeleccionados = Array.from(document.querySelectorAll('.obs-checkbox:checked')).map(cb => cb.value);
        actualizarBotonesMasivos();
    }

    function actualizarBotonesMasivos() {
        const tieneSeleccion = idsSeleccionados.length > 0;
        document.getElementById('btnAprobarSeleccion').disabled = !tieneSeleccion;
        document.getElementById('btnCancelarSeleccion').disabled = !tieneSeleccion;
        document.getElementById('btnEliminarSeleccion').disabled = !tieneSeleccion;

        const contador = document.getElementById('selectedCount');
        const numero = document.getElementById('selectedNumber');
        if (tieneSeleccion) {
            contador.classList.remove('d-none');
            numero.textContent = idsSeleccionados.length;
        } else {
            contador.classList.add('d-none');
        }
    }

    function limpiarFiltros() {
        document.getElementById('filtroBusqueda').value = '';
        document.getElementById('filtroMes').value = '';
        document.getElementById('filtroEstado').value = '';
        document.getElementById('filtroTipoError').value = '';
        document.getElementById('filtroComuna').value = '';
        document.getElementById('filtroEstablecimiento').value = '';
        document.getElementById('filtroEstablecimiento').disabled = true;
        document.getElementById('filtroRegistrador').value = '';
        paginaActual = 1;
        cargarDatos();
    }

    async function cargarEstablecimientosPorComuna() {
        const comunaId = document.getElementById('filtroComuna').value;
        const select = document.getElementById('filtroEstablecimiento');
        select.innerHTML = '<option value="">Todos</option>';
        select.disabled = !comunaId;

        if (comunaId) {
            try {
                const respuesta = await fetchAPI(`api/locations.php?accion=get_establecimientos&comuna_id=${comunaId}`);
                if (respuesta.success) {
                    respuesta.data.forEach(est => {
                        const opcion = document.createElement('option');
                        opcion.value = est.id;
                        opcion.textContent = est.nombre_corto || est.nombre;
                        select.appendChild(opcion);
                    });
                }
            } catch (error) {
                console.error('Error al cargar establecimientos:', error);
            }
        }
    }

    async function verDetalle(id) {
        try {
            const respuesta = await fetchAPI(`api/supervision.php?accion=get_detail&id=${id}`);
            if (respuesta.success) {
                mostrarModalDetalle(respuesta.data.observacion, respuesta.data.historial);
            } else {
                throw new Error(respuesta.error || 'Error al cargar detalle');
            }
        } catch (error) {
            showError('Error al cargar detalle: ' + error.message);
        }
    }

    function mostrarModalDetalle(obs, historial) {
        document.getElementById('detEstablecimiento').textContent = obs.nombre_corto || obs.establecimiento_nombre;
        document.getElementById('detComuna').textContent = obs.comuna_nombre;
        document.getElementById('detBadge').outerHTML = obtenerBadgeEstado(obs.estado_actual).replace('<td>', '<span id="detBadge"').replace('</td>', '</span>');
        document.getElementById('detMesAnio').textContent = `${obs.mes} ${obs.anio}`;
        document.getElementById('detReferencia').textContent = `${obs.codigo_serie || '-'} / ${obs.codigo_hoja || '-'}`;
        document.getElementById('detTipo').textContent = obs.tipo_error;
        document.getElementById('detDetalle').textContent = obs.detalle_observacion;
        document.getElementById('detPlazo').textContent = obs.plazo_entrega ? capitalizar(obs.plazo_entrega.replace('_', ' ')) : '-';
        document.getElementById('detClasificacion').textContent = obs.clasificacion || '-';
        document.getElementById('detRegistradoPor').textContent = obs.usuario_registro_nombre;
        document.getElementById('detFechaCreacion').textContent = formatearFechaHora(obs.fecha_creacion);
        document.getElementById('detFechaActualizacion').textContent = formatearFechaHora(obs.fecha_actualizacion);

        const contenedorHistorial = document.getElementById('detHistorial');
        if (historial && historial.length > 0) {
            contenedorHistorial.innerHTML = historial.map(h => `
                <div class="p-3 bg-light rounded mb-2">
                    <div class="d-flex justify-content-between">
                        <span class="fw-medium">${escaparHtml(h.usuario_nombre)}</span>
                        <span class="text-muted small">${formatearFechaHora(h.fecha_creacion)}</span>
                    </div>
                    <p class="mb-1 small">
                        ${h.estado_anterior ? escaparHtml(h.estado_anterior) : '<em>inicio</em>'} 
                        → <strong>${escaparHtml(h.estado_nuevo)}</strong>
                    </p>
                    ${h.comentario ? `<p class="text-muted small mb-0">${escaparHtml(h.comentario)}</p>` : ''}
                </div>
            `).join('');
        } else {
            contenedorHistorial.innerHTML = '<div class="text-muted text-center">Sin historial de cambios</div>';
        }

        abrirModal('Detalle');
    }

    function aprobarIndividual(id) {
        abrirModalAprobar([id]);
    }

    function cancelarIndividual(id) {
        abrirModalCancelar([id]);
    }

    function eliminarIndividual(id) {
        abrirModalEliminar([id]);
    }

    function abrirModalAprobar(ids = null) {
        const listaIds = ids || idsSeleccionados;
        if (listaIds.length === 0) return;

        document.getElementById('aprobarIds').value = JSON.stringify(listaIds);
        document.getElementById('aprobarMensaje').textContent = listaIds.length === 1
            ? '¿Aprobar esta observación?'
            : `¿Aprobar ${listaIds.length} observaciones seleccionadas?`;
        document.querySelector('input[name="estado_resultante"][value="sin_observacion"]').checked = false;
        document.querySelector('input[name="estado_resultante"][value="error"]').checked = false;
        document.getElementById('aprobarClasificacion').value = '';
        document.getElementById('aprobarDetalleError').value = '';
        document.getElementById('aprobarComentario').value = '';

        abrirModal('Aprobar');
    }

    async function confirmarAprobacion(e) {
        e.preventDefault();

        const estadoResultante = document.querySelector('input[name="estado_resultante"]:checked');
        if (!estadoResultante) {
            showError('Debe seleccionar "Sin Observación" o "Error" como clasificación de respuesta');
            return;
        }

        const ids = JSON.parse(document.getElementById('aprobarIds').value);
        const comentario = document.getElementById('aprobarComentario').value;
        const clasificacion = document.getElementById('aprobarClasificacion').value;
        const detalleError = document.getElementById('aprobarDetalleError').value;

        const spinner = document.getElementById('btnAprobarSpinner');
        const btn = document.getElementById('btnConfirmarAprobar');
        spinner.classList.remove('d-none');
        btn.disabled = true;

        try {
            const respuesta = await fetchAPI('api/supervision.php?accion=approve', {
                method: 'POST',
                body: JSON.stringify({
                    ids,
                    comentario,
                    clasificacion: clasificacion || null,
                    detalle_error: detalleError || null,
                    estado_resultante: estadoResultante.value
                })
            });

            if (respuesta.success) {
                const datos = respuesta.data;
                let mensaje = `${datos.procesados} observación(es) aprobada(s) correctamente`;
                if (datos.fallos.length > 0) {
                    mensaje += `. ${datos.fallos.length} fallo(s): ${datos.fallos.map(f => `ID ${f.id} (${f.motivo})`).join(', ')}`;
                }
                showSuccess(mensaje);
                cerrarModal('Aprobar');
                idsSeleccionados = [];
                document.getElementById('seleccionarTodas').checked = false;
                cargarDatos();
                cargarEstadisticas();
            } else {
                throw new Error(respuesta.error || 'Error al aprobar');
            }
        } catch (error) {
            showError('Error: ' + error.message);
        } finally {
            spinner.classList.add('d-none');
            btn.disabled = false;
        }
    }

    function abrirModalCancelar(ids = null) {
        const listaIds = ids || idsSeleccionados;
        if (listaIds.length === 0) return;

        document.getElementById('cancelarIds').value = JSON.stringify(listaIds);
        document.getElementById('cancelarMensaje').textContent = listaIds.length === 1
            ? '¿Cancelar esta observación?'
            : `¿Cancelar ${listaIds.length} observaciones seleccionadas?`;
        document.getElementById('cancelarComentario').value = '';

        abrirModal('Cancelar');
    }

    async function confirmarCancelacion(e) {
        e.preventDefault();

        const ids = JSON.parse(document.getElementById('cancelarIds').value);
        const comentario = document.getElementById('cancelarComentario').value;

        const spinner = document.getElementById('btnCancelarSpinner');
        const btn = document.getElementById('btnConfirmarCancelar');
        spinner.classList.remove('d-none');
        btn.disabled = true;

        try {
            const respuesta = await fetchAPI('api/supervision.php?accion=cancel', {
                method: 'POST',
                body: JSON.stringify({ ids, comentario })
            });

            if (respuesta.success) {
                const datos = respuesta.data;
                let mensaje = `${datos.procesados} observación(es) cancelada(s) correctamente`;
                if (datos.fallos.length > 0) {
                    mensaje += `. ${datos.fallos.length} fallo(s): ${datos.fallos.map(f => `ID ${f.id} (${f.motivo})`).join(', ')}`;
                }
                showSuccess(mensaje);
                cerrarModal('Cancelar');
                idsSeleccionados = [];
                document.getElementById('seleccionarTodas').checked = false;
                cargarDatos();
                cargarEstadisticas();
            } else {
                throw new Error(respuesta.error || 'Error al cancelar');
            }
        } catch (error) {
            showError('Error: ' + error.message);
        } finally {
            spinner.classList.add('d-none');
            btn.disabled = false;
        }
    }

    function abrirModalEliminar(ids = null) {
        const listaIds = ids || idsSeleccionados;
        if (listaIds.length === 0) return;

        document.getElementById('eliminarIds').value = JSON.stringify(listaIds);
        document.getElementById('eliminarMensaje').textContent = listaIds.length === 1
            ? '¿Eliminar esta observación? Se moverá a la papelera de reciclaje.'
            : `¿Eliminar ${listaIds.length} observaciones seleccionadas? Se moverán a la papelera de reciclaje.`;
        document.getElementById('eliminarMotivo').value = '';

        abrirModal('Eliminar');
    }

    async function confirmarEliminacion(e) {
        e.preventDefault();

        const motivo = document.getElementById('eliminarMotivo').value.trim();
        if (!motivo) {
            showError('Debe ingresar un motivo de eliminación');
            return;
        }

        const ids = JSON.parse(document.getElementById('eliminarIds').value);

        const spinner = document.getElementById('btnEliminarSpinner');
        const btn = document.getElementById('btnConfirmarEliminar');
        spinner.classList.remove('d-none');
        btn.disabled = true;

        try {
            const respuesta = await fetchAPI('api/supervision.php?accion=delete', {
                method: 'POST',
                body: JSON.stringify({ ids, motivo })
            });

            if (respuesta.success) {
                const datos = respuesta.data;
                let mensaje = `${datos.procesados} observación(es) eliminada(s) correctamente`;
                if (datos.fallos.length > 0) {
                    mensaje += `. ${datos.fallos.length} fallo(s): ${datos.fallos.map(f => `ID ${f.id} (${f.motivo})`).join(', ')}`;
                }
                showSuccess(mensaje);
                cerrarModal('Eliminar');
                idsSeleccionados = [];
                document.getElementById('seleccionarTodas').checked = false;
                cargarDatos();
                cargarEstadisticas();
            } else {
                throw new Error(respuesta.error || 'Error al eliminar');
            }
        } catch (error) {
            showError('Error: ' + error.message);
        } finally {
            spinner.classList.add('d-none');
            btn.disabled = false;
        }
    }

    function obtenerBadgeEstado(estado) {
        const clases = {
            'pendiente': 'bg-yellow text-yellow-fg',
            'aprobado': 'bg-green text-green-fg',
            'rechazado': 'bg-red text-red-fg',
            'error': 'bg-rose text-rose-fg',
            'justificado': 'bg-sky text-sky-fg'
        };
        const clase = clases[estado] || 'bg-secondary';
        return `<span class="badge ${clase}">${capitalizar(estado)}</span>`;
    }

    function obtenerClaseTipo(tipo) {
        const clases = {
            'ERROR': 'bg-rose-lt text-rose-fg',
            'S/OBSERVACION': 'bg-green-lt text-green-fg',
            'REVISAR': 'bg-amber-lt text-amber-fg',
            'F/PLAZO': 'bg-orange-lt text-orange-fg'
        };
        return clases[tipo] || 'bg-secondary-lt';
    }

    function escaparHtml(texto) {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    return {
        iniciar,
        cargarDatos,
        irPagina,
        verDetalle,
        aprobarIndividual,
        cancelarIndividual,
        eliminarIndividual,
        abrirModal,
        cerrarModal
    };
})();

window.SupervisionApp = SupervisionApp;

document.addEventListener('DOMContentLoaded', () => {
    SupervisionApp.iniciar();
});
