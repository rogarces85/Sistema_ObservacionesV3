/**
 * reportes.js - Módulo de Reportes y Exportación
 * Maneja filtros, vista previa paginada, exportación, informes y reportes analíticos.
 */

'use strict';

const Reportes = (() => {
    let paginaActual = 1;
    let totalRegistros = 0;
    let porPagina = 20;
    let totalPaginas = 0;
    let conteoActual = 0;
    let categoriaActiva = 'errores_establecimiento';
    const graficos = {};
    const datosAnaliticos = {};

    const API_BASE = (() => {
        const path = window.location.pathname;
        return path.substring(0, path.lastIndexOf('/') + 1);
    })();

    const TRIMESTRES = {
        '1': ['Enero', 'Febrero', 'Marzo'],
        '2': ['Abril', 'Mayo', 'Junio'],
        '3': ['Julio', 'Agosto', 'Septiembre'],
        '4': ['Octubre', 'Noviembre', 'Diciembre']
    };

    const CATEGORIAS_ANALITICAS = {
        errores_establecimiento: { titulo: 'Errores por establecimiento', color: '#dc2626' },
        plazos_entrega: { titulo: 'Plazos de entrega', color: '#ca8a04' },
        uso_validador: { titulo: 'Uso de validador', color: '#7c3aed' },
        errores_serie: { titulo: 'Errores por serie', color: '#0ea5e9' },
        errores_hoja: { titulo: 'Errores por hoja', color: '#16a34a' }
    };

    function obtenerFiltros() {
        return {
            anio: document.getElementById('filtroAnio').value,
            trimestre: document.getElementById('filtroTrimestre')?.value || '',
            mes: document.getElementById('filtroMes').value,
            estado: document.getElementById('filtroEstado').value,
            comuna_id: document.getElementById('filtroComuna').value,
            establecimiento_id: document.getElementById('filtroEstablecimiento').value,
            tipo_error: document.getElementById('filtroTipoError').value
        };
    }

    function obtenerFiltrosAnaliticos() {
        const filtros = obtenerFiltros();
        return {
            anio: filtros.anio,
            trimestre: filtros.trimestre,
            mes: filtros.mes,
            comuna_id: filtros.comuna_id,
            establecimiento_id: filtros.establecimiento_id
        };
    }

    function validarMesTrimestre() {
        const trimestre = document.getElementById('filtroTrimestre')?.value || '';
        const mes = document.getElementById('filtroMes').value;
        if (!trimestre || !mes) return true;
        const valido = TRIMESTRES[trimestre]?.includes(mes);
        if (!valido) {
            mostrarError('El mes seleccionado no corresponde al trimestre indicado');
        }
        return valido;
    }

    function construirParams(filtros, extras = {}) {
        const params = new URLSearchParams();
        Object.entries({ ...extras, ...filtros }).forEach(([clave, valor]) => {
            if (valor !== null && valor !== undefined && valor !== '') {
                params.append(clave, valor);
            }
        });
        return params;
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || localStorage.getItem('csrf_token') || '';
    }

    async function cargarEstablecimientos() {
        const comunaId = document.getElementById('filtroComuna').value;
        const select = document.getElementById('filtroEstablecimiento');

        select.innerHTML = '<option value="">Todos</option>';
        select.disabled = !comunaId;

        if (comunaId) {
            try {
                const respuesta = await fetch(`${API_BASE}api/locations.php?action=get_establecimientos&comuna_id=${encodeURIComponent(comunaId)}`);
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
                mostrarError('No fue posible cargar establecimientos');
            }
        }
    }

    async function actualizarConteo() {
        const filtros = obtenerFiltros();
        const params = construirParams(filtros, { accion: 'contar' });

        try {
            const respuesta = await fetch(`${API_BASE}api/export.php?${params}`);
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
        const params = construirParams(filtros, {
            accion: 'preview',
            pagina: paginaActual,
            por_pagina: porPagina
        });

        mostrarCargando(true);

        try {
            const respuesta = await fetch(`${API_BASE}api/export.php?${params}`);
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

    async function cargarReportesAnaliticos() {
        if (!validarMesTrimestre()) return;

        const filtros = obtenerFiltrosAnaliticos();
        const params = construirParams(filtros, { report: 'reportes-analiticos' });
        setEstadoAnaliticoTodos('Cargando reportes analíticos...');

        try {
            const respuesta = await fetch(`${API_BASE}api/reports.php?${params}`);
            const datos = await respuesta.json();
            if (!datos.success) {
                throw new Error(datos.message || 'No fue posible cargar reportes analíticos');
            }

            renderizarIndicadores(datos.data.totales_globales || {});
            (datos.data.reportes || []).forEach(reporte => {
                datosAnaliticos[reporte.categoria] = reporte;
                renderizarReporteAnalitico(reporte);
            });
        } catch (error) {
            console.error('Error al cargar reportes analíticos:', error);
            Object.keys(CATEGORIAS_ANALITICAS).forEach(categoria => {
                setEstadoAnalitico(categoria, 'No fue posible cargar esta categoría.', 'danger');
                setBotonExportarAnalitico(categoria, true);
            });
            mostrarError(error.message || 'Error de conexión al cargar reportes analíticos');
        }
    }

    function renderizarIndicadores(totales) {
        ['total_observaciones', 'total_errores', 'total_fuera_plazo', 'total_sin_validador'].forEach(clave => {
            const elemento = document.querySelector(`[data-indicador="${clave}"]`);
            if (elemento) {
                elemento.textContent = Number(totales[clave] || 0).toLocaleString('es-CL');
            }
        });
    }

    function renderizarReporteAnalitico(reporte) {
        const categoria = reporte.categoria;
        const resultados = reporte.resultados || [];
        setBotonExportarAnalitico(categoria, resultados.length === 0);

        if (resultados.length === 0) {
            destruirGrafico(categoria);
            setEstadoAnalitico(categoria, reporte.mensaje || 'No hay datos para los filtros seleccionados.', 'muted');
            renderizarTablaAnalitica(categoria, []);
            renderizarDestacados(categoria, []);
            return;
        }

        setEstadoAnalitico(categoria, `${resultados.length.toLocaleString('es-CL')} filas agregadas`, 'success');
        renderizarGraficoAnalitico(categoria, resultados.slice(0, 15));
        renderizarDestacados(categoria, resultados.slice(0, 3));
        renderizarTablaAnalitica(categoria, resultados);
    }

    function renderizarGraficoAnalitico(categoria, resultados) {
        const contenedor = document.getElementById(`grafico-${categoria}`);
        if (!contenedor || typeof ApexCharts === 'undefined') return;

        destruirGrafico(categoria);
        const config = CATEGORIAS_ANALITICAS[categoria] || {};

        if (categoria === 'plazos_entrega' || categoria === 'uso_validador') {
            const totalCritico = resultados.reduce((total, item) => total + Number(item.total || 0), 0);
            const totalBueno = resultados.reduce((total, item) => {
                if (categoria === 'plazos_entrega') return total + Number(item.dentro_plazo || 0);
                return total + Number(item.usa_validador || 0);
            }, 0);
            const labels = categoria === 'plazos_entrega' ? ['Fuera de plazo', 'Dentro de plazo'] : ['Sin validador', 'Usa validador'];

            graficos[categoria] = new ApexCharts(contenedor, {
                chart: { type: 'donut', height: 320, toolbar: { show: false } },
                series: [totalCritico, totalBueno],
                labels,
                colors: [config.color || '#ca8a04', '#16a34a'],
                legend: { position: 'bottom' },
                dataLabels: {
                    formatter: valor => `${valor.toFixed(1)}%`
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0).toLocaleString('es-CL')
                                }
                            }
                        }
                    }
                },
                tooltip: { y: { formatter: valor => Number(valor).toLocaleString('es-CL') } }
            });
            graficos[categoria].render();
            return;
        }

        graficos[categoria] = new ApexCharts(contenedor, {
            chart: { type: 'bar', height: 340, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
            series: [{ name: 'Total', data: resultados.map(item => Number(item.total || 0)) }],
            xaxis: {
                categories: resultados.map(item => truncarEtiqueta(item.nombre || item.clave || 'Sin nombre', 36)),
                labels: { style: { colors: '#64748b' } }
            },
            colors: [config.color || '#0ea5e9'],
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
            plotOptions: { bar: { horizontal: true, borderRadius: 7, barHeight: '62%', distributed: false } },
            dataLabels: {
                enabled: true,
                style: { fontWeight: 700 },
                formatter: valor => Number(valor).toLocaleString('es-CL')
            },
            tooltip: { y: { formatter: valor => Number(valor).toLocaleString('es-CL') } }
        });
        graficos[categoria].render();
    }

    function renderizarDestacados(categoria, resultados) {
        const contenedor = document.querySelector(`[data-destacados-categoria="${categoria}"]`);
        if (!contenedor) return;

        if (!resultados.length) {
            contenedor.innerHTML = '<div class="text-muted text-center py-4">Sin destacados</div>';
            return;
        }

        contenedor.innerHTML = `
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h5 class="mb-0">Top 3</h5>
                <span class="badge bg-primary-lt">${escapeHtml(CATEGORIAS_ANALITICAS[categoria]?.titulo || 'Categoría')}</span>
            </div>
            ${resultados.map((item, indice) => `
                <div class="reportes-analytics__top-item">
                    <div class="d-flex align-items-start gap-2">
                        <span class="reportes-analytics__top-rank">${indice + 1}</span>
                        <div class="flex-fill">
                            <div class="fw-bold">${escapeHtml(item.nombre || item.clave || 'Sin nombre')}</div>
                            <div class="small text-muted">${escapeHtml(item.comuna || 'Sin comuna asociada')}</div>
                            <div class="d-flex align-items-center justify-content-between mt-2">
                                <span class="text-muted small">Total</span>
                                <span class="h3 mb-0">${Number(item.total || 0).toLocaleString('es-CL')}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('')}
        `;
    }

    function truncarEtiqueta(texto, maximo) {
        const valor = String(texto || '');
        return valor.length > maximo ? `${valor.substring(0, maximo - 1)}…` : valor;
    }

    function destruirGrafico(categoria) {
        if (graficos[categoria]) {
            graficos[categoria].destroy();
            delete graficos[categoria];
        }
    }

    function renderizarTablaAnalitica(categoria, resultados) {
        const cuerpo = document.querySelector(`[data-tabla-categoria="${categoria}"] tbody`);
        if (!cuerpo) return;

        if (resultados.length === 0) {
            cuerpo.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No hay datos para los filtros seleccionados</td></tr>';
            return;
        }

        cuerpo.innerHTML = resultados.map(item => `
            <tr>
                <td>${escapeHtml(item.nombre || item.clave || 'Sin nombre')}</td>
                <td>${escapeHtml(item.comuna || '')}</td>
                <td class="text-end fw-bold">${Number(item.total || 0).toLocaleString('es-CL')}</td>
                <td class="text-end">${Number(item.porcentaje || 0).toLocaleString('es-CL')}%</td>
            </tr>
        `).join('');
    }

    function setEstadoAnaliticoTodos(mensaje) {
        Object.keys(CATEGORIAS_ANALITICAS).forEach(categoria => setEstadoAnalitico(categoria, mensaje, 'muted'));
    }

    function setEstadoAnalitico(categoria, mensaje, tipo) {
        const estado = document.querySelector(`[data-estado-categoria="${categoria}"]`);
        if (!estado) return;
        const clases = {
            success: 'reportes-analytics__estado text-success',
            danger: 'reportes-analytics__estado text-danger',
            muted: 'reportes-analytics__estado text-muted'
        };
        estado.className = clases[tipo] || clases.muted;
        estado.textContent = mensaje;
    }

    function setBotonExportarAnalitico(categoria, deshabilitado) {
        const boton = document.querySelector(`[data-exportar-analitico="${categoria}"]`);
        if (boton) boton.disabled = deshabilitado;
    }

    function seleccionarCategoria(categoria) {
        categoriaActiva = categoria;
        document.querySelectorAll('[data-panel-categoria]').forEach(panel => {
            panel.classList.toggle('d-none', panel.dataset.panelCategoria !== categoria);
        });
        document.querySelectorAll('[data-categoria]').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.categoria === categoria);
        });
    }

    function renderizarTabla(datos) {
        const cuerpo = document.getElementById('cuerpoTablaPreview');

        if (!datos || datos.length === 0) {
            cuerpo.innerHTML = '<tr><td colspan="12" class="text-center text-muted py-4">No se encontraron registros con los filtros seleccionados</td></tr>';
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
                    <td class="text-truncate" title="${escapeHtml(registro.detalle_observacion || '')}">${escapeHtml(registro.detalle_observacion || '')}</td>
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

        let html = `<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-pagina="${paginaActual - 1}">«</a></li>`;
        const paginaInicio = Math.max(1, paginaActual - 2);
        const paginaFin = Math.min(totalPaginas, paginaActual + 2);

        if (paginaInicio > 1) {
            html += '<li class="page-item"><a class="page-link" href="#" data-pagina="1">1</a></li>';
            if (paginaInicio > 2) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }

        for (let p = paginaInicio; p <= paginaFin; p++) {
            html += `<li class="page-item ${p === paginaActual ? 'active' : ''}"><a class="page-link" href="#" data-pagina="${p}">${p}</a></li>`;
        }

        if (paginaFin < totalPaginas) {
            if (paginaFin < totalPaginas - 1) html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            html += `<li class="page-item"><a class="page-link" href="#" data-pagina="${totalPaginas}">${totalPaginas}</a></li>`;
        }

        html += `<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}"><a class="page-link" href="#" data-pagina="${paginaActual + 1}">»</a></li>`;
        lista.innerHTML = html;

        lista.querySelectorAll('.page-link').forEach(enlace => {
            enlace.addEventListener('click', async e => {
                e.preventDefault();
                const nuevaPagina = parseInt(enlace.dataset.pagina, 10);
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
        if (spinner) spinner.classList.toggle('d-none', !mostrar);
    }

    function mostrarError(mensaje) {
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion(mensaje, 'danger');
        } else {
            alert(mensaje);
        }
    }

    function mostrarExito(mensaje) {
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion(mensaje, 'success');
        }
    }

    async function procesarRespuestaExportacion(respuesta) {
        const tipo = respuesta.headers.get('Content-Type') || '';
        if (tipo.includes('application/json')) {
            const datos = await respuesta.json();
            if (!datos.success) throw new Error(datos.error || datos.message || 'Error al exportar');
            return datos;
        }

        const blob = await respuesta.blob();
        const disposicion = respuesta.headers.get('Content-Disposition') || '';
        const match = disposicion.match(/filename="?([^";]+)"?/i);
        const nombre = match ? match[1] : 'reporte_rem';
        const url = window.URL.createObjectURL(blob);
        const enlace = document.createElement('a');
        enlace.href = url;
        enlace.download = nombre;
        document.body.appendChild(enlace);
        enlace.click();
        enlace.remove();
        window.URL.revokeObjectURL(url);
        return { success: true };
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
            const respuesta = await fetch(`${API_BASE}api/export.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                body: JSON.stringify({ ...filtros, formato, tipo_reporte: tipoReporte })
            });

            const datos = await procesarRespuestaExportacion(respuesta);
            if (datos.data?.en_cola) {
                document.getElementById('exportColaMensaje').classList.remove('d-none');
                mostrarExito(datos.data.mensaje || 'Reporte encolado correctamente');
            } else {
                mostrarExito('Exportación completada exitosamente');
            }
        } catch (error) {
            console.error('Error al exportar:', error);
            mostrarError(error.message || 'Error de conexión al exportar');
        } finally {
            mostrarCargando(false);
        }
    }

    async function exportarReporteAnalitico(categoria) {
        const reporte = datosAnaliticos[categoria];
        if (!reporte || !reporte.resultados || reporte.resultados.length === 0) {
            mostrarError('No hay datos para exportar en esta categoría');
            return;
        }

        try {
            const respuesta = await fetch(`${API_BASE}api/export.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                body: JSON.stringify({ ...obtenerFiltrosAnaliticos(), formato: 'excel', tipo_reporte: categoria })
            });
            await procesarRespuestaExportacion(respuesta);
            mostrarExito('Reporte analítico exportado correctamente');
        } catch (error) {
            console.error('Error al exportar reporte analítico:', error);
            mostrarError(error.message || 'No fue posible exportar la categoría');
        }
    }

    async function generarInforme() {
        const tipo = document.getElementById('informeTipo').value;
        const trimestre = tipo === 'trimestral' ? parseInt(document.getElementById('informeTrimestre').value, 10) : null;
        const anio = parseInt(document.getElementById('informeAnio').value, 10);
        const formato = document.getElementById('informeFormato').value;

        mostrarCargando(true);

        try {
            let url = `${API_BASE}api/informe_errores.php?tipo=${tipo}&anio=${anio}&formato=${formato}`;
            if (trimestre) url += `&trimestre=${trimestre}`;

            if (formato === 'pdf') {
                window.open(url, '_blank');
                mostrarExito('Generando PDF del informe...');
            } else {
                const respuesta = await fetch(url);
                const datos = await respuesta.json();
                if (datos.success) renderizarInforme(datos.data);
                else mostrarError(datos.error || 'Error al generar informe');
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
        let html = `<div class="card"><div class="card-header"><h3 class="card-title">Informe: ${escapeHtml(periodo)}</h3><span class="badge bg-purple">${total} errores encontrados</span></div><div class="card-body"><p class="text-muted">Emitido: ${escapeHtml(datos.emitido || '')}</p>`;

        if (datos.por_comuna && Object.keys(datos.por_comuna).length > 0) {
            html += '<h5 class="mt-3">Resumen por Comuna</h5><div class="table-responsive"><table class="table table-sm table-vcenter"><thead><tr><th>Comuna</th><th class="text-end">Errores</th></tr></thead><tbody>';
            Object.entries(datos.por_comuna).forEach(([comuna, cantidad]) => {
                html += `<tr><td>${escapeHtml(comuna)}</td><td class="text-end">${cantidad}</td></tr>`;
            });
            html += '</tbody></table></div>';
        }

        if (datos.por_establecimiento && Object.keys(datos.por_establecimiento).length > 0) {
            html += '<h5 class="mt-3">Resumen por Establecimiento</h5><div class="table-responsive"><table class="table table-sm table-vcenter"><thead><tr><th>Establecimiento</th><th class="text-end">Errores</th></tr></thead><tbody>';
            Object.entries(datos.por_establecimiento).sort((a, b) => b[1] - a[1]).slice(0, 20).forEach(([est, cantidad]) => {
                html += `<tr><td>${escapeHtml(est)}</td><td class="text-end">${cantidad}</td></tr>`;
            });
            html += '</tbody></table></div>';
        }

        html += '</div></div>';
        contenedor.innerHTML = html;
    }

    function limpiarFiltros() {
        document.getElementById('filtroAnio').value = document.querySelector('#filtroAnio option[selected]')?.value || new Date().getFullYear();
        document.getElementById('filtroTrimestre').value = '';
        document.getElementById('filtroMes').value = '';
        document.getElementById('filtroEstado').value = '';
        document.getElementById('filtroComuna').value = '';
        document.getElementById('filtroEstablecimiento').innerHTML = '<option value="">Todos</option>';
        document.getElementById('filtroEstablecimiento').disabled = true;
        document.getElementById('filtroTipoError').value = '';

        paginaActual = 1;
        conteoActual = 0;
        renderizarIndicadores({});
        setEstadoAnaliticoTodos('Aplique filtros para cargar esta categoría.');
        Object.keys(CATEGORIAS_ANALITICAS).forEach(categoria => {
            destruirGrafico(categoria);
            renderizarTablaAnalitica(categoria, []);
            setBotonExportarAnalitico(categoria, true);
            delete datosAnaliticos[categoria];
        });

        document.getElementById('exportInfo').textContent = 'Aplique filtros para ver el conteo';
        document.getElementById('exportInfo').className = 'text-muted small';
        document.getElementById('btnExportar').disabled = true;
        document.getElementById('previewTotal').textContent = '0 registros';
        document.getElementById('cuerpoTablaPreview').innerHTML = '<tr><td colspan="12" class="text-center text-muted py-4">Aplique filtros para ver la vista previa</td></tr>';
        document.getElementById('paginacionPreview').style.display = 'none';
    }

    function inicializar() {
        document.getElementById('filtroComuna').addEventListener('change', cargarEstablecimientos);
        document.getElementById('btnAplicarFiltros').addEventListener('click', () => {
            if (!validarMesTrimestre()) return;
            cargarVistaPrevia();
            actualizarConteo();
            cargarReportesAnaliticos();
        });
        document.getElementById('btnActualizarAnaliticos')?.addEventListener('click', cargarReportesAnaliticos);
        document.getElementById('btnLimpiarFiltros').addEventListener('click', limpiarFiltros);
        document.getElementById('btnExportar').addEventListener('click', exportarDatos);
        document.getElementById('btnGenerarInforme')?.addEventListener('click', generarInforme);

        document.querySelectorAll('[data-categoria]').forEach(tab => {
            tab.addEventListener('click', () => seleccionarCategoria(tab.dataset.categoria));
        });

        document.querySelectorAll('[data-exportar-analitico]').forEach(boton => {
            boton.addEventListener('click', () => exportarReporteAnalitico(boton.dataset.exportarAnalitico));
        });

        const informeTipo = document.getElementById('informeTipo');
        if (informeTipo) {
            informeTipo.addEventListener('change', function () {
                document.getElementById('informeTrimestre').parentElement.style.display = this.value === 'trimestral' ? '' : 'none';
            });
        }

        cargarReportesAnaliticos();
    }

    return { inicializar };
})();

document.addEventListener('DOMContentLoaded', () => {
    Reportes.inicializar();
});
