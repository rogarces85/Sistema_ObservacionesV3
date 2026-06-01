/**
 * reportes.js - Módulo de Reportes y Exportación
 * Maneja filtros, vista previa paginada, exportación e informes de errores
 */

'use strict';

const Reportes = (() => {
    let paginaActual = 1;
    let totalRegistros = 0;
    let porPagina = 20;
    let totalPaginas = 0;
    let conteoActual = 0;

    const TRIMESTRES = {
        '1': ['Enero', 'Febrero', 'Marzo'],
        '2': ['Abril', 'Mayo', 'Junio'],
        '3': ['Julio', 'Agosto', 'Septiembre'],
        '4': ['Octubre', 'Noviembre', 'Diciembre']
    };

    function obtenerFiltros() {
        return {
            anio: document.getElementById('filtroAnio').value,
            mes: document.getElementById('filtroMes').value,
            estado: document.getElementById('filtroEstado').value,
            comuna_id: document.getElementById('filtroComuna').value,
            establecimiento_id: document.getElementById('filtroEstablecimiento').value,
            tipo_error: document.getElementById('filtroTipoError').value
        };
    }

    async function cargarEstablecimientos() {
        const comunaId = document.getElementById('filtroComuna').value;
        const select = document.getElementById('filtroEstablecimiento');

        select.innerHTML = '<option value="">Todos</option>';
        select.disabled = !comunaId;

        if (comunaId) {
            try {
                const respuesta = await fetch(`api/locations.php?action=get_establecimientos&comuna_id=${comunaId}`);
                const datos = await respuesta.json();
                if (datos.success) {
                    datos.data.forEach(est => {
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

    async function actualizarConteo() {
        const filtros = obtenerFiltros();
        const params = new URLSearchParams();
        params.append('accion', 'contar');
        Object.entries(filtros).forEach(([clave, valor]) => {
            if (valor) params.append(clave, valor);
        });

        try {
            const respuesta = await fetch(`api/export.php?${params}`);
            const datos = await respuesta.json();
            if (datos.success) {
                conteoActual = datos.data.total;
                const info = document.getElementById('exportInfo');
                const btnExportar = document.getElementById('btnExportar');

                if (datos.data.total === 0) {
                    info.textContent = 'Sin registros para los filtros actuales';
                    info.className = 'text-muted small';
                    btnExportar.disabled = true;
                } else if (datos.data.total > datos.data.limite_maximo) {
                    info.textContent = `${datos.data.total.toLocaleString('es-CL')} registros - Excede límite de ${datos.data.limite_maximo.toLocaleString('es-CL')}`;
                    info.className = 'text-danger small fw-bold';
                    btnExportar.disabled = true;
                } else if (datos.data.total > 1000) {
                    info.textContent = `${datos.data.total.toLocaleString('es-CL')} registros - Se procesará en cola asíncrona`;
                    info.className = 'text-warning small fw-bold';
                    btnExportar.disabled = false;
                } else {
                    info.textContent = `${datos.data.total.toLocaleString('es-CL')} registros - Exportación inmediata`;
                    info.className = 'text-success small fw-bold';
                    btnExportar.disabled = false;
                }
            }
        } catch (error) {
            console.error('Error al obtener conteo:', error);
        }
    }

    async function cargarVistaPrevia() {
        paginaActual = 1;
        await obtenerDatosPreview();
    }

    async function obtenerDatosPreview() {
        const filtros = obtenerFiltros();
        const params = new URLSearchParams();
        params.append('accion', 'preview');
        params.append('pagina', paginaActual);
        params.append('por_pagina', porPagina);
        Object.entries(filtros).forEach(([clave, valor]) => {
            if (valor) params.append(clave, valor);
        });

        mostrarCargando(true);

        try {
            const respuesta = await fetch(`api/export.php?${params}`);
            const datos = await respuesta.json();

            if (datos.success) {
                totalRegistros = datos.data.total;
                porPagina = datos.data.porPagina || 20;
                totalPaginas = datos.data.totalPaginas || 0;

                renderizarTabla(datos.data.datos);
                renderizarPaginacion();
                document.getElementById('previewTotal').textContent = `${totalRegistros.toLocaleString('es-CL')} registros`;
            } else {
                mostrarError(datos.error || 'Error al cargar vista previa');
            }
        } catch (error) {
            console.error('Error en vista previa:', error);
            mostrarError('Error de conexión al cargar datos');
        } finally {
            mostrarCargando(false);
        }
    }

    function renderizarTabla(datos) {
        const cuerpo = document.getElementById('cuerpoTablaPreview');

        if (!datos || datos.length === 0) {
            cuerpo.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center text-muted py-4">
                        No se encontraron registros con los filtros seleccionados
                    </td>
                </tr>
            `;
            return;
        }

        cuerpo.innerHTML = datos.map(registro => {
            const claseEstado = obtenerClaseEstado(registro.estado_actual);
            return `
                <tr>
                    <td>${registro.id}</td>
                    <td>${registro.anio}</td>
                    <td>${escapeHtml(registro.mes)}</td>
                    <td>${escapeHtml(registro.comuna_nombre || '')}</td>
                    <td>${escapeHtml(registro.nombre_corto || registro.establecimiento_nombre || '')}</td>
                    <td>${escapeHtml(registro.codigo_serie || '')}</td>
                    <td>${escapeHtml(registro.codigo_hoja || '')}</td>
                    <td>${escapeHtml(registro.tipo_error)}</td>
                    <td class="text-truncate" style="max-width: 200px;" title="${escapeHtml(registro.detalle_observacion || '')}">${escapeHtml(registro.detalle_observacion || '')}</td>
                    <td><span class="badge ${registro.plazo_entrega === 'fuera_plazo' ? 'bg-danger-lt' : 'bg-success-lt'}">${escapeHtml(registro.plazo_entrega || '')}</span></td>
                    <td><span class="badge ${claseEstado}">${escapeHtml(registro.estado_actual)}</span></td>
                    <td>${escapeHtml(registro.clasificacion || '')}</td>
                </tr>
            `;
        }).join('');
    }

    function renderizarPaginacion() {
        const nav = document.getElementById('paginacionPreview');
        const info = document.getElementById('infoPaginacion');
        const lista = document.getElementById('listaPaginacion');

        if (totalPaginas <= 1) {
            nav.style.display = 'none';
            return;
        }

        nav.style.display = 'flex';
        const inicio = ((paginaActual - 1) * porPagina) + 1;
        const fin = Math.min(paginaActual * porPagina, totalRegistros);
        info.textContent = `Mostrando ${inicio}-${fin} de ${totalRegistros.toLocaleString('es-CL')}`;

        let html = '';
        html += `<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-pagina="${paginaActual - 1}">«</a>
                 </li>`;

        let paginaInicio = Math.max(1, paginaActual - 2);
        let paginaFin = Math.min(totalPaginas, paginaActual + 2);

        if (paginaInicio > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" data-pagina="1">1</a></li>`;
            if (paginaInicio > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }

        for (let p = paginaInicio; p <= paginaFin; p++) {
            html += `<li class="page-item ${p === paginaActual ? 'active' : ''}">
                        <a class="page-link" href="#" data-pagina="${p}">${p}</a>
                     </li>`;
        }

        if (paginaFin < totalPaginas) {
            if (paginaFin < totalPaginas - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            html += `<li class="page-item"><a class="page-link" href="#" data-pagina="${totalPaginas}">${totalPaginas}</a></li>`;
        }

        html += `<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-pagina="${paginaActual + 1}">»</a>
                 </li>`;

        lista.innerHTML = html;

        lista.querySelectorAll('.page-link').forEach(enlace => {
            enlace.addEventListener('click', async (e) => {
                e.preventDefault();
                const nuevaPagina = parseInt(enlace.dataset.pagina);
                if (nuevaPagina >= 1 && nuevaPagina <= totalPaginas && nuevaPagina !== paginaActual) {
                    paginaActual = nuevaPagina;
                    await obtenerDatosPreview();
                }
            });
        });
    }

    function obtenerClaseEstado(estado) {
        switch (estado) {
            case 'aprobado': return 'bg-success-lt';
            case 'pendiente': return 'bg-warning-lt';
            case 'rechazado': return 'bg-secondary-lt';
            case 'error': return 'bg-danger-lt';
            default: return 'bg-muted-lt';
        }
    }

    function escapeHtml(texto) {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    function mostrarCargando(mostrar) {
        const spinner = document.getElementById('loading-spinner');
        if (spinner) {
            spinner.classList.toggle('d-none', !mostrar);
        }
    }

    function mostrarError(mensaje) {
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion(mensaje, 'danger');
        } else {
            alert(mensaje);
        }
    }

    async function exportarDatos() {
        const filtros = obtenerFiltros();
        const formato = document.getElementById('exportFormato').value;
        const tipoReporte = document.getElementById('exportTipoReporte').value;

        if (conteoActual === 0) {
            mostrarError('No hay registros para exportar');
            return;
        }

        if (conteoActual > 50000) {
            mostrarError('El número de registros excede el límite máximo de 50,000');
            return;
        }

        mostrarCargando(true);

        try {
            const token = localStorage.getItem('csrf_token');
            const respuesta = await fetch('api/export.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token || ''
                },
                body: JSON.stringify({
                    ...filtros,
                    formato: formato,
                    tipo_reporte: tipoReporte
                })
            });

            const datos = await respuesta.json();

            if (respuesta.status >= 200 && respuesta.status < 300) {
                if (datos.success) {
                    if (datos.data.en_cola) {
                        document.getElementById('exportColaMensaje').classList.remove('d-none');
                        mostrarNotificacion(datos.data.mensaje, 'info', 6000);
                    } else {
                        mostrarNotificacion('Exportación completada exitosamente', 'success');
                    }
                } else {
                    mostrarError(datos.error || 'Error al exportar');
                }
            } else {
                mostrarError(datos.error || `Error HTTP ${respuesta.status}`);
            }
        } catch (error) {
            console.error('Error al exportar:', error);
            mostrarError('Error de conexión al exportar');
        } finally {
            mostrarCargando(false);
        }
    }

    async function generarInforme() {
        const tipo = document.getElementById('informeTipo').value;
        const trimestre = tipo === 'trimestral' ? parseInt(document.getElementById('informeTrimestre').value) : null;
        const anio = parseInt(document.getElementById('informeAnio').value);
        const formato = document.getElementById('informeFormato').value;

        mostrarCargando(true);

        try {
            let url = `api/informe_errores.php?tipo=${tipo}&anio=${anio}&formato=${formato}`;
            if (trimestre) url += `&trimestre=${trimestre}`;

            if (formato === 'pdf') {
                window.open(url, '_blank');
                mostrarNotificacion('Generando PDF del informe...', 'info');
            } else {
                const respuesta = await fetch(url);
                const datos = await respuesta.json();

                if (datos.success) {
                    renderizarInforme(datos.data);
                } else {
                    mostrarError(datos.error || 'Error al generar informe');
                }
            }
        } catch (error) {
            console.error('Error al generar informe:', error);
            mostrarError('Error de conexión al generar informe');
        } finally {
            mostrarCargando(false);
        }
    }

    function renderizarInforme(datos) {
        const contenedor = document.getElementById('informeResultado');
        contenedor.classList.remove('d-none');

        const periodo = datos.periodo || '';
        const total = datos.total || 0;

        let html = `
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informe: ${escapeHtml(periodo)}</h3>
                    <span class="badge bg-purple">${total} errores encontrados</span>
                </div>
                <div class="card-body">
                    <p class="text-muted">Emitido: ${escapeHtml(datos.emitido || '')}</p>
        `;

        if (datos.por_comuna && Object.keys(datos.por_comuna).length > 0) {
            html += `<h5 class="mt-3">Resumen por Comuna</h5>
                     <div class="table-responsive">
                         <table class="table table-sm table-vcenter">
                             <thead><tr><th>Comuna</th><th class="text-end">Errores</th></tr></thead>
                             <tbody>`;
            Object.entries(datos.por_comuna).forEach(([comuna, cantidad]) => {
                html += `<tr><td>${escapeHtml(comuna)}</td><td class="text-end">${cantidad}</td></tr>`;
            });
            html += `</tbody></table></div>`;
        }

        if (datos.por_establecimiento && Object.keys(datos.por_establecimiento).length > 0) {
            html += `<h5 class="mt-3">Resumen por Establecimiento</h5>
                     <div class="table-responsive">
                         <table class="table table-sm table-vcenter">
                             <thead><tr><th>Establecimiento</th><th class="text-end">Errores</th></tr></thead>
                             <tbody>`;
            Object.entries(datos.por_establecimiento)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 20)
                .forEach(([est, cantidad]) => {
                    html += `<tr><td>${escapeHtml(est)}</td><td class="text-end">${cantidad}</td></tr>`;
                });
            html += `</tbody></table></div>`;
        }

        if (datos.datos && datos.datos.length > 0) {
            html += `<h5 class="mt-3">Detalle de Errores</h5>
                     <div class="table-responsive">
                         <table class="table table-sm table-vcenter">
                             <thead>
                                 <tr>
                                     <th>Comuna</th>
                                     <th>Establecimiento</th>
                                     <th>Mes</th>
                                     <th>Serie</th>
                                     <th>Hoja</th>
                                     <th>Detalle</th>
                                     <th>Estado</th>
                                 </tr>
                             </thead>
                             <tbody>`;
            datos.datos.slice(0, 50).forEach(fila => {
                const claseEstado = obtenerClaseEstado(fila.estado_actual);
                html += `<tr>
                    <td>${escapeHtml(fila.comuna_nombre || '')}</td>
                    <td>${escapeHtml(fila.nombre_corto || fila.establecimiento_nombre || '')}</td>
                    <td>${escapeHtml(fila.mes || '')}</td>
                    <td>${escapeHtml(fila.codigo_serie || '')}</td>
                    <td>${escapeHtml(fila.codigo_hoja || '')}</td>
                    <td class="text-truncate" style="max-width: 250px;" title="${escapeHtml(fila.detalle_observacion || '')}">${escapeHtml(fila.detalle_observacion || '')}</td>
                    <td><span class="badge ${claseEstado}">${escapeHtml(fila.estado_actual || '')}</span></td>
                </tr>`;
            });
            html += `</tbody></table></div>`;
            if (datos.datos.length > 50) {
                html += `<p class="text-muted small">Mostrando 50 de ${datos.datos.length} registros. Exporte a PDF para ver el informe completo.</p>`;
            }
        }

        html += `</div></div>`;
        contenedor.innerHTML = html;
    }

    function limpiarFiltros() {
        document.getElementById('filtroAnio').value = document.querySelector('#filtroAnio option[selected]')?.value || new Date().getFullYear();
        document.getElementById('filtroMes').value = '';
        document.getElementById('filtroEstado').value = '';
        document.getElementById('filtroComuna').value = '';
        document.getElementById('filtroEstablecimiento').innerHTML = '<option value="">Todos</option>';
        document.getElementById('filtroEstablecimiento').disabled = true;
        document.getElementById('filtroTipoError').value = '';

        paginaActual = 1;
        conteoActual = 0;

        document.getElementById('exportInfo').textContent = 'Aplique filtros para ver el conteo';
        document.getElementById('exportInfo').className = 'text-muted small';
        document.getElementById('btnExportar').disabled = true;
        document.getElementById('previewTotal').textContent = '0 registros';

        document.getElementById('cuerpoTablaPreview').innerHTML = `
            <tr>
                <td colspan="12" class="text-center text-muted py-4">
                    Aplique filtros para ver la vista previa
                </td>
            </tr>
        `;
        document.getElementById('paginacionPreview').style.display = 'none';
    }

    function inicializar() {
        document.getElementById('filtroComuna').addEventListener('change', cargarEstablecimientos);
        document.getElementById('btnAplicarFiltros').addEventListener('click', () => {
            cargarVistaPrevia();
            actualizarConteo();
        });
        document.getElementById('btnLimpiarFiltros').addEventListener('click', limpiarFiltros);
        document.getElementById('btnExportar').addEventListener('click', exportarDatos);
        document.getElementById('btnGenerarInforme')?.addEventListener('click', generarInforme);

        const informeTipo = document.getElementById('informeTipo');
        if (informeTipo) {
            informeTipo.addEventListener('change', function () {
                document.getElementById('informeTrimestre').parentElement.style.display =
                    this.value === 'trimestral' ? '' : 'none';
            });
        }
    }

    return {
        inicializar
    };
})();

document.addEventListener('DOMContentLoaded', () => {
    Reportes.inicializar();
});
