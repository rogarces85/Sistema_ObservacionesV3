/**
 * dashboard.js - Módulo principal del Dashboard
 * Carga de datos paralela, auto-refresh, pestañas, kanban drag & drop
 */

'use strict';

class Dashboard {
    constructor(config) {
        this.anio = config.anio;
        this.rol = config.rol;
        this.usuarioId = config.usuarioId;
        this.autoRefreshInterval = config.autoRefreshInterval || 120000;
        this.csrfToken = config.csrfToken || '';

        this.autoRefreshActivo = true;
        this.autoRefreshTimer = null;
        this.inactividadTimer = null;
        this.tiempoInactividad = 10000;

        this.charts = {};
        this.sparklineCharts = {};
        this.kanbanDragActivo = false;
        this.kanbanItemArrastrado = null;

        this.inicializar();
    }

    inicializar() {
        this.cargarDatos();
        this.configurarAutoRefresh();
        this.configurarInactividad();
        this.configurarSelectorAnio();
        this.configurarSelectorMesLocal();
        this.configurarPestanas();

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.detenerAutoRefresh();
            } else if (this.autoRefreshActivo) {
                this.iniciarAutoRefresh();
            }
        });
    }

    async cargarDatos() {
        const anio = this.anio;

        const promesas = {
            estadisticas: this.obtenerJSON(`api/dashboard/estadisticas.php?anio=${anio}`),
            graficos: this.obtenerJSON(`api/dashboard/graficos.php?anio=${anio}`),
            recientes: this.obtenerJSON(`api/dashboard/recientes.php?anio=${anio}`),
            alertas: this.obtenerJSON(`api/dashboard/alertas.php?anio=${anio}`),
            sparklines: this.obtenerJSON(`api/dashboard/sparklines.php?anio=${anio}`),
            timeline: this.obtenerJSON(`api/dashboard/timeline.php?anio=${anio}`),
            kanban: this.obtenerJSON(`api/dashboard/kanban.php?anio=${anio}`)
        };

        const resultados = await Promise.allSettled(Object.values(promesas));
        const claves = Object.keys(promesas);

        resultados.forEach((resultado, indice) => {
            if (resultado.status === 'fulfilled' && resultado.value?.success) {
                this.procesarDatos(claves[indice], resultado.value.data);
            } else {
                console.warn(`Error al cargar ${claves[indice]}:`, resultado.reason);
            }
        });
    }

    async obtenerJSON(url) {
        const respuesta = await fetch(url);
        if (!respuesta.ok) throw new Error(`HTTP ${respuesta.status}`);
        return respuesta.json();
    }

    procesarDatos(tipo, datos) {
        switch (tipo) {
            case 'estadisticas':
                this.actualizarTarjetas(datos);
                break;
            case 'graficos':
                this.renderizarGraficos(datos);
                break;
            case 'recientes':
                this.renderizarRecientes(datos.observaciones || []);
                break;
            case 'alertas':
                this.renderizarAlertas(datos.alertas || []);
                break;
            case 'sparklines':
                this.renderizarSparklines(datos);
                break;
            case 'timeline':
                this.renderizarTimeline(datos.eventos || []);
                break;
            case 'kanban':
                this.renderizarKanban(datos);
                break;
        }
    }

    actualizarTarjetas(datos) {
        this.animarValor('stat-total', datos.total);
        this.animarValor('stat-pendientes', datos.pendientes);
        this.animarValor('stat-aprobadas', datos.aprobadas);
        this.animarValor('stat-problemas', datos.problemas);
    }

    animarValor(elementoId, valorFinal) {
        const elemento = document.getElementById(elementoId);
        if (!elemento) return;

        const valorActual = parseInt(elemento.textContent.replace(/[^0-9]/g, '')) || 0;
        if (valorActual === valorFinal) return;

        const duracion = 800;
        const inicio = performance.now();

        const actualizar = (tiempoActual) => {
            const transcurrido = tiempoActual - inicio;
            const progreso = Math.min(transcurrido / duracion, 1);
            const facilidad = 1 - Math.pow(1 - progreso, 3);
            const valor = Math.floor(valorActual + (valorFinal - valorActual) * facilidad);
            elemento.textContent = valor.toLocaleString('es-CL');
            if (progreso < 1) requestAnimationFrame(actualizar);
        };

        requestAnimationFrame(actualizar);
    }

    renderizarGraficos(datos) {
        this.renderizarDonut(datos.donut || []);
        this.renderizarBarras(datos.barras || []);
        this.renderizarLineas(datos.lineas || []);
    }

    renderizarDonut(datos) {
        const contenedor = document.getElementById('chart-donut');
        if (!contenedor) return;

        if (datos.length === 0) {
            contenedor.innerHTML = '<div class="text-center py-5 text-secondary">Sin datos disponibles</div>';
            return;
        }

        const etiquetas = datos.map(d => d.estado_actual.charAt(0).toUpperCase() + d.estado_actual.slice(1));
        const valores = datos.map(d => parseInt(d.total));
        const colores = datos.map(d => {
            const mapa = { pendiente: '#f59e0b', aprobado: '#059669', rechazado: '#dc2626', error: '#b91c1c', justificado: '#0284c7' };
            return mapa[d.estado_actual] || '#64748b';
        });

        if (this.charts.donut) this.charts.donut.destroy();

        this.charts.donut = new ApexCharts(contenedor, {
            chart: { type: 'donut', height: 280, fontFamily: "'Inter', sans-serif" },
            series: valores,
            labels: etiquetas,
            colors: colores,
            legend: { position: 'bottom', fontSize: '12px' },
            dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 600 } },
            tooltip: {
                y: { formatter: (val) => `${val} observaciones` }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            name: { fontSize: '14px' },
                            value: { fontSize: '20px', fontWeight: 700 },
                            total: {
                                show: true,
                                label: 'Total',
                                fontSize: '14px',
                                formatter: () => valores.reduce((a, b) => a + b, 0).toLocaleString('es-CL')
                            }
                        }
                    }
                }
            }
        });

        this.charts.donut.render();
    }

    renderizarBarras(datos) {
        const contenedor = document.getElementById('chart-barras');
        if (!contenedor) return;

        if (datos.length === 0) {
            contenedor.innerHTML = '<div class="text-center py-5 text-secondary">Sin datos disponibles</div>';
            return;
        }

        const etiquetas = datos.map(d => d.tipo_error);
        const valores = datos.map(d => parseInt(d.total));
        const colores = ['#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#06b6d4', '#84cc16', '#6366f1'];

        if (this.charts.barras) this.charts.barras.destroy();

        this.charts.barras = new ApexCharts(contenedor, {
            chart: { type: 'bar', height: 280, fontFamily: "'Inter', sans-serif", toolbar: { show: false } },
            series: [{ name: 'Cantidad', data: valores }],
            colors: colores.slice(0, valores.length),
            plotOptions: { bar: { horizontal: true, borderRadius: 4, dataLabels: { position: 'top' } } },
            dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 700 }, offsetX: -8 },
            xaxis: { categories: etiquetas, labels: { style: { fontSize: '11px' } } },
            yaxis: { labels: { style: { fontSize: '12px', fontWeight: 600 } } },
            tooltip: { y: { formatter: (val) => `${val} observaciones` } }
        });

        this.charts.barras.render();
    }

    renderizarLineas(datos) {
        const contenedor = document.getElementById('chart-lineas');
        if (!contenedor) return;

        if (datos.length === 0) {
            contenedor.innerHTML = '<div class="text-center py-5 text-secondary">Sin datos disponibles</div>';
            return;
        }

        const etiquetas = datos.map(d => d.mes.substring(0, 3));
        const valores = datos.map(d => parseInt(d.total));

        if (this.charts.lineas) this.charts.lineas.destroy();

        this.charts.lineas = new ApexCharts(contenedor, {
            chart: { type: 'area', height: 280, fontFamily: "'Inter', sans-serif", toolbar: { show: false }, animations: { enabled: true, speed: 800 } },
            series: [{ name: 'Observaciones', data: valores }],
            colors: ['#0ea5e9'],
            fill: { type: 'gradient', gradient: { shade: 'light', type: 'vertical', opacityFrom: 0.7, opacityTo: 0.1 } },
            stroke: { width: 3, curve: 'smooth' },
            dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 700 }, offsetY: -6 },
            xaxis: { categories: etiquetas, labels: { style: { fontSize: '11px' } } },
            yaxis: { labels: { style: { fontSize: '11px' } } },
            tooltip: { y: { formatter: (val) => `${val} observaciones` } }
        });

        this.charts.lineas.render();
    }

    renderizarRecientes(observaciones) {
        const contenedor = document.getElementById('recientes-container');
        if (!contenedor) return;

        if (observaciones.length === 0) {
            contenedor.innerHTML = '<div class="text-center py-5 text-secondary">No hay observaciones registradas</div>';
            return;
        }

        const coloresEstado = {
            pendiente: 'bg-yellow text-yellow-fg',
            aprobado: 'bg-green text-green-fg',
            rechazado: 'bg-red text-red-fg',
            error: 'bg-red text-red-fg',
            justificado: 'bg-blue text-blue-fg'
        };

        let html = `<table class="table table-vcenter card-table mb-0">
            <thead><tr><th>Establecimiento</th><th>Mes</th><th>Tipo</th><th>Estado</th><th>Fecha</th></tr></thead>
            <tbody>`;

        observaciones.forEach(obs => {
            const fecha = obs.fecha_creacion ? new Date(obs.fecha_creacion).toLocaleDateString('es-CL') : '-';
            const claseEstado = coloresEstado[obs.estado_actual] || 'bg-secondary text-secondary-fg';
            html += `<tr>
                <td><div class="fw-semibold">${this.escapeHtml(obs.nombre_corto)}</div><div class="text-secondary small">${this.escapeHtml(obs.comuna_nombre)}</div></td>
                <td class="text-secondary">${this.escapeHtml(obs.mes)}</td>
                <td><span class="badge bg-secondary-lt text-secondary">${this.escapeHtml(obs.tipo_error)}</span></td>
                <td><span class="badge ${claseEstado}">${this.capitalize(obs.estado_actual)}</span></td>
                <td class="text-secondary">${fecha}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        contenedor.innerHTML = html;
    }

    renderizarAlertas(alertas) {
        const contenedor = document.getElementById('alertas-container');
        if (!contenedor) return;

        if (alertas.length === 0) {
            contenedor.innerHTML = '';
            return;
        }

        let html = '';
        alertas.forEach(alerta => {
            const tipo = alerta.tipo === 'danger' ? 'danger' : 'warning';
            html += `<div class="alert alert-${tipo} alert-dismissible" role="alert">
                <div class="d-flex align-items-center gap-3">
                    <div>${tipo === 'danger' ? tablerIcon('alert-circle') : tablerIcon('alert-triangle')}</div>
                    <div>
                        <strong>${this.escapeHtml(alerta.titulo)}</strong><br>
                        <small>${this.escapeHtml(alerta.mensaje)}</small>
                        ${alerta.detalles && alerta.detalles.length > 0 ? `<ul class="mb-0 mt-1">${alerta.detalles.map(d => `<li>${this.escapeHtml(d.nombre_completo)} (${this.escapeHtml(d.username)})</li>`).join('')}</ul>` : ''}
                        ${alerta.accion ? `<a href="${alerta.accion.url}" class="fw-semibold text-decoration-none mt-1 d-inline-block">${this.escapeHtml(alerta.accion.texto)}</a>` : ''}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>`;
        });

        contenedor.innerHTML = html;
    }

    renderizarSparklines(datos) {
        const mapeo = [
            { id: 'sparkline-total', clave: 'total', color: '#0ea5e9' },
            { id: 'sparkline-pendientes', clave: 'pendientes', color: '#f59e0b' },
            { id: 'sparkline-aprobadas', clave: 'aprobadas', color: '#059669' },
            { id: 'sparkline-problemas', clave: 'problemas', color: '#dc2626' }
        ];

        mapeo.forEach(({ id, clave, color }) => {
            const contenedor = document.getElementById(id);
            if (!contenedor) return;

            const datosSerie = datos[clave];
            if (!datosSerie || datosSerie.length === 0) return;

            if (this.sparklineCharts[id]) this.sparklineCharts[id].destroy();

            this.sparklineCharts[id] = new ApexCharts(contenedor, {
                chart: { type: 'area', height: 40, sparkline: { enabled: true } },
                series: [{ data: datosSerie }],
                colors: [color],
                stroke: { width: 2, curve: 'smooth' },
                fill: { opacity: 0.2 },
                tooltip: { fixed: { enabled: false }, x: { show: false }, marker: { show: false } }
            });

            this.sparklineCharts[id].render();
        });
    }

    renderizarTimeline(eventos) {
        const contenedor = document.getElementById('timeline-container');
        if (!contenedor) return;

        if (eventos.length === 0) {
            contenedor.innerHTML = '<div class="empty"><div class="empty-icon">' + tablerIcon('history') + '</div><p class="empty-title">No hay actividad reciente</p></div>';
            return;
        }

        let html = '<div class="timeline">';
        eventos.forEach(evento => {
            const fechaRelativa = this.fechaRelativa(evento.fecha);
            html += `<div class="timeline-item">
                <div class="timeline-item-icon bg-${evento.color}-lt text-${evento.color}-fg">
                    ${tablerIcon(evento.icono)}
                </div>
                <div class="timeline-item-content">
                    <div class="text-muted small">${fechaRelativa}</div>
                    <div>${this.escapeHtml(evento.descripcion)}</div>
                    <div class="text-secondary small">${this.escapeHtml(evento.usuario)}</div>
                </div>
            </div>`;
        });
        html += '</div>';

        contenedor.innerHTML = html;
    }

    renderizarKanban(datos) {
        const contenedor = document.getElementById('kanban-container');
        if (!contenedor) return;

        const columnasDef = [
            { id: 'pendiente', etiqueta: 'Pendiente', color: 'yellow' },
            { id: 'aprobado', etiqueta: 'Aprobado', color: 'green' },
            { id: 'rechazado', etiqueta: 'Rechazado', color: 'red' },
            { id: 'error', etiqueta: 'Error', color: 'danger' },
            { id: 'justificado', etiqueta: 'Justificado', color: 'blue' }
        ];

        const puedeArrastrar = datos.puedeArrastrar || false;

        let html = '<div class="kanban-board d-flex gap-3" style="overflow-x:auto;padding-bottom:1rem">';

        columnasDef.forEach(col => {
            const items = datos.columnas?.[col.id] || [];
            html += `<div class="kanban-column flex-shrink-0" style="min-width:280px;max-width:320px" data-columna="${col.id}">
                <div class="card">
                    <div class="card-header bg-${col.color}-lt py-2">
                        <h4 class="card-title mb-0 small">${col.etiqueta}</h4>
                        <span class="badge bg-${col.color}">${items.length}</span>
                    </div>
                    <div class="card-body p-2 kanban-column-body" data-columna="${col.id}"
                         ${puedeArrastrar ? 'ondragover="dashboard.manejarDragOver(event)" ondrop="dashboard.manejarDrop(event)"' : ''}>
                        ${items.length === 0 ? '<div class="text-center text-secondary small py-3">Sin observaciones</div>' : ''}`;

            items.forEach(item => {
                html += `<div class="card card-sm mb-2 kanban-card" 
                    ${puedeArrastrar ? `draggable="true" ondragstart="dashboard.manejarDragStart(event, ${item.id})"` : ''}
                    data-id="${item.id}" data-estado="${col.id}">
                    <div class="card-body p-2">
                        <div class="fw-semibold small">${this.escapeHtml(item.nombre_corto)}</div>
                        <div class="text-secondary small">${this.escapeHtml(item.mes)}</div>
                        <div class="mt-1">
                            <span class="badge bg-secondary-lt text-secondary small">${this.escapeHtml(item.tipo_error)}</span>
                        </div>
                    </div>
                </div>`;
            });

            html += '</div></div></div>';
        });

        html += '</div>';
        contenedor.innerHTML = html;
    }

    manejarDragStart(evento, id) {
        evento.dataTransfer.setData('text/plain', id.toString());
        evento.dataTransfer.effectAllowed = 'move';
        this.kanbanItemArrastrado = id;
    }

    manejarDragOver(evento) {
        evento.preventDefault();
        evento.dataTransfer.dropEffect = 'move';
    }

    async manejarDrop(evento) {
        evento.preventDefault();
        const columnaDestino = evento.currentTarget.dataset.columna;
        const idItem = evento.dataTransfer.getData('text/plain');

        if (!idItem || !columnaDestino) return;

        const tarjeta = document.querySelector(`.kanban-card[data-id="${idItem}"]`);
        if (!tarjeta) return;

        tarjeta.classList.add('opacity-50');
        const spinner = document.createElement('div');
        spinner.className = 'spinner-border spinner-border-sm position-absolute top-50 start-50';
        spinner.setAttribute('role', 'status');
        tarjeta.style.position = 'relative';
        tarjeta.appendChild(spinner);

        try {
            const csrfToken = localStorage.getItem('csrf_token') || this.csrfToken;
            const respuesta = await fetch('api/dashboard/kanban.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ id: parseInt(idItem), estado: columnaDestino })
            });

            const datos = await respuesta.json();

            if (datos.success) {
                showSuccess('Estado actualizado correctamente');
                this.cargarDatos();
            } else {
                showError(datos.error || 'Error al actualizar estado');
                this.cargarDatos();
            }
        } catch (error) {
            showError('Error de conexión al actualizar estado');
            this.cargarDatos();
        }
    }

    configurarAutoRefresh() {
        const toggle = document.getElementById('auto-refresh-toggle');
        if (toggle) {
            const estadoGuardado = localStorage.getItem('dashboardAutoRefresh');
            if (estadoGuardado !== null) {
                this.autoRefreshActivo = estadoGuardado === 'true';
                toggle.checked = this.autoRefreshActivo;
            }

            toggle.addEventListener('change', () => {
                this.autoRefreshActivo = toggle.checked;
                localStorage.setItem('dashboardAutoRefresh', this.autoRefreshActivo);
                if (this.autoRefreshActivo) {
                    this.iniciarAutoRefresh();
                    showInfo('Auto-refresh activado');
                } else {
                    this.detenerAutoRefresh();
                    showInfo('Auto-refresh desactivado');
                }
            });

            if (this.autoRefreshActivo) {
                this.iniciarAutoRefresh();
            }
        }
    }

    iniciarAutoRefresh() {
        this.detenerAutoRefresh();
        this.autoRefreshTimer = setInterval(() => this.cargarDatos(), this.autoRefreshInterval);
    }

    detenerAutoRefresh() {
        if (this.autoRefreshTimer) {
            clearInterval(this.autoRefreshTimer);
            this.autoRefreshTimer = null;
        }
    }

    configurarInactividad() {
        const eventos = ['mousedown', 'keydown', 'scroll', 'touchstart', 'change', 'select'];

        const pausarPorInactividad = () => {
            if (this.autoRefreshActivo) {
                this.detenerAutoRefresh();
            }
        };

        const reanudarTrasInactividad = () => {
            clearTimeout(this.inactividadTimer);
            this.inactividadTimer = setTimeout(() => {
                if (this.autoRefreshActivo) {
                    this.iniciarAutoRefresh();
                }
            }, this.tiempoInactividad);
        };

        eventos.forEach(evento => {
            document.addEventListener(evento, () => {
                pausarPorInactividad();
                reanudarTrasInactividad();
            }, { passive: true });
        });
    }

    configurarSelectorAnio() {
        const selector = document.getElementById('year-selector');
        if (selector) {
            selector.addEventListener('change', () => {
                const nuevoAnio = selector.value;
                this.anio = parseInt(nuevoAnio);
                document.getElementById('anio-titulo').textContent = nuevoAnio;

                const url = new URL(window.location.href);
                url.searchParams.set('year', nuevoAnio);
                window.location.href = url.toString();
            });
        }
    }

    configurarSelectorMesLocal() {
        const selector = document.getElementById('selector-mes-local');
        if (selector) {
            selector.addEventListener('change', () => {
                const mes = selector.value;
                this.cargarGraficoLineasConFiltro(mes);
            });
        }
    }

    async cargarGraficoLineasConFiltro(mes) {
        let url = `api/dashboard/graficos.php?anio=${this.anio}`;
        if (mes) url += `&mes=${encodeURIComponent(mes)}`;

        try {
            const datos = await this.obtenerJSON(url);
            if (datos.success) {
                this.renderizarLineas(datos.data.lineas || []);
            }
        } catch (error) {
            console.warn('Error al cargar gráfico de líneas con filtro:', error);
        }
    }

    configurarPestanas() {
        document.querySelectorAll('#dashboard-tabs [data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', () => {
                const objetivo = tab.dataset.bsTarget;
                if (objetivo === '#tab-kanban') {
                    this.cargarDatos();
                }
            });
        });
    }

    escapeHtml(texto) {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    capitalize(texto) {
        if (!texto) return '';
        return texto.charAt(0).toUpperCase() + texto.slice(1);
    }

    fechaRelativa(fecha) {
        if (!fecha) return '';
        const ahora = new Date();
        const fechaObj = new Date(fecha);
        const diff = Math.floor((ahora - fechaObj) / 1000);

        if (diff < 60) return 'hace unos segundos';
        if (diff < 3600) return `hace ${Math.floor(diff / 60)} min`;
        if (diff < 86400) return `hace ${Math.floor(diff / 3600)} horas`;
        return `hace ${Math.floor(diff / 86400)} días`;
    }
}

let dashboard;

document.addEventListener('DOMContentLoaded', () => {
    if (window.DASHBOARD_CONFIG) {
        dashboard = new Dashboard(window.DASHBOARD_CONFIG);
    }
});
