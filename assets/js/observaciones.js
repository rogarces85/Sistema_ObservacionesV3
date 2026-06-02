/**
 * observaciones.js - CRUD de observaciones REM
 * Maneja listado, filtros, paginación, creación, edición, eliminación, detalle e historial
 */

'use strict';

const ObservacionesApp = (() => {
    let paginaActual = 1;
    let modalFormulario = null;
    let modalDetalle = null;
    let modalEliminar = null;

    const inicializar = () => {
        modalFormulario = new bootstrap.Modal(document.getElementById('modalFormulario'));
        modalDetalle = new bootstrap.Modal(document.getElementById('modalDetalle'));
        modalEliminar = new bootstrap.Modal(document.getElementById('modalEliminar'));

        document.getElementById('formObservacion').addEventListener('submit', guardar);
        document.getElementById('frmTipoError').addEventListener('change', alCambiarTipo);
        document.getElementById('frmSerie').addEventListener('change', cargarHojas);

        cargarEstablecimientos();
        cargarDatos();
        cargarEstadisticas();
    };

    const cargarEstablecimientos = async () => {
        try {
            const respuesta = await fetchAPI('api/establecimientos.php?accion=listar&activo=1');
            const select = document.getElementById('frmEstablecimiento');
            select.innerHTML = '<option value="">Seleccione...</option>';
            if (respuesta.success && respuesta.data) {
                respuesta.data.forEach(est => {
                    const opt = document.createElement('option');
                    opt.value = est.id;
                    opt.textContent = est.nombre;
                    select.appendChild(opt);
                });
            }
        } catch (error) {
            console.error('Error al cargar establecimientos:', error);
        }
    };

    const obtenerFiltros = () => ({
        busqueda: document.getElementById('filtroBusqueda').value.trim(),
        mes: document.getElementById('filtroMes').value,
        estado: document.getElementById('filtroEstado').value,
        tipo_error: document.getElementById('filtroTipoError').value
    });

    const cargarDatos = async (pagina = 1) => {
        paginaActual = pagina;
        const filtros = obtenerFiltros();
        const params = new URLSearchParams({
            accion: 'listar',
            pagina: pagina,
            anio: document.getElementById('year-selector')?.value || new Date().getFullYear(),
            ...filtros
        });

        try {
            const respuesta = await fetchAPI('api/observaciones.php?' + params.toString());
            if (respuesta.success) {
                renderizarTabla(respuesta.data);
                renderizarPaginacion(respuesta.data);
            } else {
                mostrarError(respuesta.error || 'Error al cargar datos');
            }
        } catch (error) {
            mostrarError('Error de conexión: ' + error.message);
        }
    };

    const renderizarTabla = (datos) => {
        const cuerpo = document.getElementById('cuerpoTabla');
        const registros = datos.datos || [];

        if (registros.length === 0) {
            cuerpo.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No se encontraron observaciones</td></tr>';
            return;
        }

        const coloresEstado = {
            pendiente: 'bg-yellow text-yellow-fg',
            aprobado: 'bg-green text-green-fg',
            rechazado: 'bg-red text-red-fg',
            error: 'bg-red text-red-fg',
            justificado: 'bg-blue text-blue-fg'
        };

        cuerpo.innerHTML = registros.map(obs => {
            const badgeClass = coloresEstado[obs.estado_actual] || 'bg-secondary text-secondary-fg';
            const puedeEditar = USUARIO_ROL === 'supervisor' ||
                (obs.usuario_registro_id == <?php echo $_SESSION['usuario_id'] ?? 'null'; ?> && obs.estado_actual === 'pendiente');

            return `
                <tr>
                    <td>
                        <div class="fw-semibold">${escapeHtml(obs.nombre_corto || obs.establecimiento_nombre)}</div>
                        <div class="text-muted small">${escapeHtml(obs.comuna_nombre)}</div>
                    </td>
                    <td>${escapeHtml(obs.mes)}</td>
                    <td>
                        <div class="small">${escapeHtml(obs.codigo_serie || '-')}</div>
                        <div class="text-muted small">${escapeHtml(obs.codigo_hoja || '-')}</div>
                    </td>
                    <td><span class="badge bg-secondary-lt">${escapeHtml(obs.tipo_error)}</span></td>
                    <td><span class="badge ${badgeClass}">${capitalizar(obs.estado_actual)}</span></td>
                    <td>
                        <div class="small">${escapeHtml(obs.usuario_registro_nombre)}</div>
                    </td>
                    <td class="text-end">
                        <div class="btn-list flex-nowrap">
                            <button class="btn btn-sm btn-icon" onclick="ObservacionesApp.verDetalle(${obs.id})" title="Ver detalle">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                            </button>
                            ${puedeEditar ? `
                            <button class="btn btn-sm btn-icon" onclick="ObservacionesApp.editar(${obs.id})" title="Editar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/><path d="M16 5l3 3"/></svg>
                            </button>` : ''}
                            ${USUARIO_ROL === 'supervisor' ? `
                            <button class="btn btn-sm btn-icon btn-ghost-danger" onclick="ObservacionesApp.solicitarEliminacion(${obs.id})" title="Eliminar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7h16m-10 -4v4m6 0v4m-6 0v4m6 0v4m-10 0h10m-10 0h-4"/></svg>
                            </button>` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    };

    const renderizarPaginacion = (datos) => {
        const info = document.getElementById('paginacionInfo');
        const nav = document.getElementById('paginacionNav');

        if (datos.totalPaginas <= 1) {
            info.textContent = datos.total > 0 ? `${datos.total} registros` : 'Sin registros';
            nav.innerHTML = '';
            return;
        }

        const inicio = (datos.pagina - 1) * datos.porPagina + 1;
        const fin = Math.min(datos.pagina * datos.porPagina, datos.total);
        info.textContent = `Mostrando ${inicio}-${fin} de ${datos.total}`;

        let html = '<ul class="pagination m-0">';

        html += `<li class="page-item ${datos.pagina <= 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="ObservacionesApp.cargarDatos(${datos.pagina - 1}); return false;">Anterior</a>
                 </li>`;

        let paginaInicio = Math.max(1, datos.pagina - 2);
        let paginaFin = Math.min(datos.totalPaginas, datos.pagina + 2);

        if (paginaInicio > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="ObservacionesApp.cargarDatos(1); return false;">1</a></li>`;
            if (paginaInicio > 2) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        for (let p = paginaInicio; p <= paginaFin; p++) {
            html += `<li class="page-item ${p === datos.pagina ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="ObservacionesApp.cargarDatos(${p}); return false;">${p}</a>
                     </li>`;
        }

        if (paginaFin < datos.totalPaginas) {
            if (paginaFin < datos.totalPaginas - 1) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            html += `<li class="page-item"><a class="page-link" href="#" onclick="ObservacionesApp.cargarDatos(${datos.totalPaginas}); return false;">${datos.totalPaginas}</a></li>`;
        }

        html += `<li class="page-item ${datos.pagina >= datos.totalPaginas ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="ObservacionesApp.cargarDatos(${datos.pagina + 1}); return false;">Siguiente</a>
                 </li>`;

        html += '</ul>';
        nav.innerHTML = html;
    };

    const cargarEstadisticas = async () => {
        try {
            const anio = document.getElementById('year-selector')?.value || new Date().getFullYear();
            const respuesta = await fetchAPI('api/observaciones.php?accion=stats&anio=' + anio);
            if (respuesta.success) {
                const stats = respuesta.data;
                document.getElementById('statTotal').textContent = stats.total || 0;

                const porEstado = {};
                (stats.por_estado || []).forEach(e => { porEstado[e.estado_actual] = parseInt(e.total); });

                document.getElementById('statPendiente').textContent = porEstado['pendiente'] || 0;
                document.getElementById('statAprobado').textContent = porEstado['aprobado'] || 0;
                document.getElementById('statError').textContent = porEstado['error'] || 0;
            }
        } catch (error) {
            console.error('Error al cargar estadísticas:', error);
        }
    };

    const abrirModalCrear = () => {
        document.getElementById('obsId').value = '';
        document.getElementById('obsFechaActualizacion').value = '';
        document.getElementById('modalTitulo').textContent = 'Nueva Observación';
        document.getElementById('formObservacion').reset();
        document.getElementById('contenedorHoja').style.display = '';
        document.getElementById('frmHoja').innerHTML = '<option value="">Seleccione serie primero</option>';
        document.getElementById('frmHoja').disabled = true;
        limpiarErrores();
        modalFormulario.show();
    };

    const editar = async (id) => {
        try {
            const respuesta = await fetchAPI('api/observaciones.php?accion=detalle&id=' + id);
            if (!respuesta.success) {
                mostrarError(respuesta.error || 'Error al cargar observación');
                return;
            }

            const obs = respuesta.data;
            document.getElementById('obsId').value = obs.id;
            document.getElementById('obsFechaActualizacion').value = obs.fecha_actualizacion || '';
            document.getElementById('modalTitulo').textContent = 'Editar Observación';

            document.getElementById('frmMes').value = obs.mes;
            document.getElementById('frmEstablecimiento').value = obs.establecimiento_id;
            document.getElementById('frmTipoError').value = obs.tipo_error;
            alCambiarTipo();
            document.getElementById('frmSerie').value = obs.codigo_serie;

            if (obs.tipo_error !== 'S/OBSERVACION') {
                await cargarHojas();
                document.getElementById('frmHoja').value = obs.codigo_hoja || '';
            }

            document.getElementById('frmDetalle').value = obs.detalle_observacion || '';
            document.getElementById('frmPlazo').value = obs.plazo_entrega || '';
            document.getElementById('frmClasificacion').value = obs.clasificacion || '';
            document.getElementById('frmUsaValidador').value = obs.usa_validador || '';

            limpiarErrores();
            modalFormulario.show();
        } catch (error) {
            mostrarError('Error al cargar: ' + error.message);
        }
    };

    const guardar = async (evento) => {
        evento.preventDefault();
        limpiarErrores();

        const camposRequeridos = ['frmMes', 'frmEstablecimiento', 'frmTipoError', 'frmSerie', 'frmDetalle'];
        let valido = true;

        camposRequeridos.forEach(id => {
            const el = document.getElementById(id);
            if (!el.value.trim()) {
                el.classList.add('is-invalid');
                valido = false;
            }
        });

        const tipoError = document.getElementById('frmTipoError').value;
        if (tipoError !== 'S/OBSERVACION' && !document.getElementById('frmHoja').value) {
            document.getElementById('frmHoja').classList.add('is-invalid');
            valido = false;
        }

        if (!valido) return;

        const id = document.getElementById('obsId').value;
        const datos = {
            mes: document.getElementById('frmMes').value,
            establecimiento_id: parseInt(document.getElementById('frmEstablecimiento').value),
            tipo_error: tipoError,
            codigo_serie: document.getElementById('frmSerie').value,
            codigo_hoja: tipoError === 'S/OBSERVACION' ? null : document.getElementById('frmHoja').value,
            detalle_observacion: document.getElementById('frmDetalle').value,
            plazo_entrega: document.getElementById('frmPlazo').value || null,
            clasificacion: document.getElementById('frmClasificacion').value || null,
            usa_validador: document.getElementById('frmUsaValidador').value || null
        };

        const fechaOriginal = document.getElementById('obsFechaActualizacion').value;
        if (fechaOriginal) {
            datos.fecha_actualizacion = fechaOriginal;
        }

        try {
            document.getElementById('btnGuardar').disabled = true;
            document.getElementById('btnGuardarSpinner').classList.remove('d-none');
            document.getElementById('btnGuardarTexto').textContent = 'Guardando...';

            let respuesta;
            if (id) {
                respuesta = await fetchAPI('api/observaciones.php?id=' + id, {
                    method: 'PUT',
                    body: JSON.stringify(datos)
                });
            } else {
                respuesta = await fetchAPI('api/observaciones.php?accion=crear', {
                    method: 'POST',
                    body: JSON.stringify(datos)
                });
            }

            document.getElementById('btnGuardar').disabled = false;
            document.getElementById('btnGuardarSpinner').classList.add('d-none');
            document.getElementById('btnGuardarTexto').textContent = 'Guardar';

            if (respuesta.success) {
                mostrarExito(id ? 'Observación actualizada' : 'Observación creada');
                modalFormulario.hide();
                cargarDatos(paginaActual);
                cargarEstadisticas();
            } else {
                mostrarError(respuesta.error || 'Error al guardar');
            }
        } catch (error) {
            document.getElementById('btnGuardar').disabled = false;
            document.getElementById('btnGuardarSpinner').classList.add('d-none');
            document.getElementById('btnGuardarTexto').textContent = 'Guardar';
            mostrarError('Error: ' + error.message);
        }
    };

    const verDetalle = async (id) => {
        try {
            const [obsResp, histResp] = await Promise.all([
                fetchAPI('api/observaciones.php?accion=detalle&id=' + id),
                fetchAPI('api/observaciones.php?accion=historial&id=' + id)
            ]);

            if (!obsResp.success) {
                mostrarError(obsResp.error || 'Error al cargar detalle');
                return;
            }

            const obs = obsResp.data;
            document.getElementById('detEstablecimiento').textContent = obs.nombre_corto || obs.establecimiento_nombre || '-';
            document.getElementById('detComuna').textContent = obs.comuna_nombre || '-';

            const badge = document.getElementById('detBadge');
            badge.textContent = capitalizar(obs.estado_actual);
            badge.className = 'badge ' + (coloresBadge[obs.estado_actual] || 'bg-secondary');

            document.getElementById('detMesAnio').textContent = `${obs.mes} ${obs.anio}`;
            document.getElementById('detReferencia').textContent = `${obs.codigo_serie || '-'} / ${obs.codigo_hoja || '-'}`;
            document.getElementById('detTipo').textContent = obs.tipo_error || '-';
            document.getElementById('detDetalle').textContent = obs.detalle_observacion || '-';
            document.getElementById('detPlazo').textContent = obs.plazo_entrega ? capitalizar(obs.plazo_entrega.replace('_', ' ')) : '-';
            document.getElementById('detClasificacion').textContent = obs.clasificacion || '-';
            document.getElementById('detRegistradoPor').textContent = obs.usuario_registro_nombre || '-';
            document.getElementById('detFechaCreacion').textContent = formatearFechaHora(obs.fecha_registro || obs.fecha_creacion);
            document.getElementById('detFechaActualizacion').textContent = formatearFechaHora(obs.fecha_actualizacion);

            const historial = histResp.success ? (histResp.data || []) : [];
            const contenedorHistorial = document.getElementById('detHistorial');

            if (historial.length === 0) {
                contenedorHistorial.innerHTML = '<div class="text-muted text-center py-3">Sin registros de historial</div>';
            } else {
                contenedorHistorial.innerHTML = historial.map(h => `
                    <div class="timeline-item">
                        <div class="timeline-badge bg-primary"></div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold">${escapeHtml(h.estado_anterior || 'Inicio')} → ${escapeHtml(h.estado_nuevo)}</span>
                                <span class="text-muted small">${formatearFechaHora(h.fecha_creacion)}</span>
                            </div>
                            <div class="text-muted small">Por: ${escapeHtml(h.usuario_nombre)}</div>
                            ${h.comentario ? `<div class="text-secondary small mt-1">${escapeHtml(h.comentario)}</div>` : ''}
                        </div>
                    </div>
                `).join('');
            }

            const footer = document.getElementById('detalleFooter');
            if (USUARIO_ROL === 'supervisor') {
                footer.innerHTML = `
                    <button type="button" class="btn btn-danger me-auto" onclick="ObservacionesApp.solicitarEliminacion(${id}); bootstrap.Modal.getInstance(document.getElementById('modalDetalle')).hide();">
                        Eliminar
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                `;
            } else {
                footer.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>';
            }

            modalDetalle.show();
        } catch (error) {
            mostrarError('Error al cargar detalle: ' + error.message);
        }
    };

    const solicitarEliminacion = (id) => {
        document.getElementById('eliminarId').value = id;
        modalEliminar.show();
    };

    const confirmarEliminacion = async () => {
        const id = document.getElementById('eliminarId').value;
        try {
            const respuesta = await fetchAPI('api/observaciones.php?id=' + id, { method: 'DELETE' });
            if (respuesta.success) {
                mostrarExito('Observación eliminada');
                modalEliminar.hide();
                modalDetalle.hide();
                cargarDatos(paginaActual);
                cargarEstadisticas();
            } else {
                mostrarError(respuesta.error || 'Error al eliminar');
            }
        } catch (error) {
            mostrarError('Error: ' + error.message);
        }
    };

    const alCambiarTipo = () => {
        const tipo = document.getElementById('frmTipoError').value;
        const contenedor = document.getElementById('contenedorHoja');
        if (tipo === 'S/OBSERVACION') {
            contenedor.style.display = 'none';
            document.getElementById('frmHoja').value = '';
        } else {
            contenedor.style.display = '';
            cargarHojas();
        }
    };

    const cargarHojas = async () => {
        const serie = document.getElementById('frmSerie').value;
        const select = document.getElementById('frmHoja');
        select.innerHTML = '';

        if (!serie) {
            select.innerHTML = '<option value="">Seleccione serie primero</option>';
            select.disabled = true;
            return;
        }

        const hojas = HOJAS_POR_SERIE[serie] || [];
        if (hojas.length > 0) {
            select.innerHTML = '<option value="">Seleccione...</option>';
            hojas.forEach(hoja => {
                const opt = document.createElement('option');
                opt.value = hoja.codigo;
                opt.textContent = hoja.nombre;
                select.appendChild(opt);
            });
            select.disabled = false;
        } else {
            select.innerHTML = '<option value="">Sin hojas disponibles</option>';
            select.disabled = true;
        }
    };

    const limpiarFiltros = () => {
        document.getElementById('filtroBusqueda').value = '';
        document.getElementById('filtroMes').value = '';
        document.getElementById('filtroEstado').value = '';
        document.getElementById('filtroTipoError').value = '';
        cargarDatos(1);
    };

    const limpiarErrores = () => {
        document.querySelectorAll('#formObservacion .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    };

    const coloresBadge = {
        pendiente: 'bg-yellow text-yellow-fg',
        aprobado: 'bg-green text-green-fg',
        rechazado: 'bg-red text-red-fg',
        error: 'bg-red text-red-fg',
        justificado: 'bg-blue text-blue-fg'
    };

    const escapeHtml = (texto) => {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    };

    const capitalizar = (texto) => {
        if (!texto) return '';
        return texto.charAt(0).toUpperCase() + texto.slice(1);
    };

    const mostrarExito = (mensaje) => {
        if (typeof showSuccess === 'function') showSuccess(mensaje);
        else alert(mensaje);
    };

    const mostrarError = (mensaje) => {
        if (typeof showError === 'function') showError(mensaje);
        else alert(mensaje);
    };

    const formatearFechaHora = (fecha) => {
        if (!fecha) return '-';
        const f = new Date(fecha);
        if (isNaN(f.getTime())) return fecha;
        return f.toLocaleDateString('es-CL', {
            day: '2-digit', month: '2-digit', year: 'numeric'
        }) + ' ' + f.toLocaleTimeString('es-CL', {
            hour: '2-digit', minute: '2-digit'
        });
    };

    document.addEventListener('DOMContentLoaded', inicializar);

    return {
        cargarDatos,
        abrirModalCrear,
        editar,
        verDetalle,
        solicitarEliminacion,
        confirmarEliminacion,
        limpiarFiltros
    };
})();
