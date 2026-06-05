/**
 * dashboard.js - Dashboard simplificado
 * Carga estadísticas y gráficos fidedignos desde la BD
 */

'use strict';

document.addEventListener('DOMContentLoaded', async () => {
    const config = window.DASHBOARD_CONFIG || {};
    const anio = config.anio || new Date().getFullYear();

    cargarDashboard(anio);
});

async function cargarDashboard(anio) {
    mostrarCarga(true);
    
    try {
        const [statsRes, chartsRes] = await Promise.all([
            fetchAPI(`api/dashboard/estadisticas.php?anio=${anio}`),
            fetchAPI(`api/dashboard/graficos.php?anio=${anio}`)
        ]);
        
        if (statsRes.success) {
            actualizarStats(statsRes.data);
        }
        
        if (chartsRes.success) {
            renderizarDonut(chartsRes.data.donut || []);
            renderizarLineas(chartsRes.data.lineas || []);
            renderizarBreakdown(chartsRes.data);
        }
    } catch (error) {
        console.error('Error al cargar dashboard:', error);
        mostrarError('Error al cargar los datos');
    } finally {
        mostrarCarga(false);
    }
}

function actualizarStats(datos) {
    const {
        total = 0,
        pendientes = 0,
        aprobadas = 0,
        problemas = 0
    } = datos;

    animarNumero('stat-total', total);
    animarNumero('stat-pendientes', pendientes);
    animarNumero('stat-aprobadas', aprobadas);
    animarNumero('stat-problemas', problemas);

    if (total > 0) {
        const pctAprobado = Math.round((aprobadas / total) * 100);
        const pctPendiente = Math.round((pendientes / total) * 100);
        const pctProblema = Math.round((problemas / total) * 100);

        animarRing('ring-aprobado', pctAprobado, 'ring-aprobado-value');
        animarRing('ring-pendiente', pctPendiente, 'ring-pendiente-value');

        actualizarMeta('stat-aprobado-meta', pctAprobado, 'Resueltas');
        actualizarMeta('stat-pendiente-meta', pctPendiente, 'Requieren atención');
        actualizarMeta('stat-problema-meta', pctProblema, 'Requieren revisión');
        const metaTotal = document.getElementById('stat-total-meta');
        if (metaTotal) {
            metaTotal.innerHTML = metaTotal.innerHTML.replace('En el año', `${total} obs. en ${datos.anio || ''}`.trim());
        }
    } else {
        document.getElementById('ring-aprobado-value').textContent = '0%';
        document.getElementById('ring-pendiente-value').textContent = '0%';
    }
}

function actualizarMeta(id, porcentaje, fallback) {
    const el = document.getElementById(id);
    if (!el) return;
    const svg = el.querySelector('svg');
    const svgHTML = svg ? svg.outerHTML : '';
    el.innerHTML = svgHTML + `<span>${porcentaje}% • ${fallback}</span>`;
}

function animarRing(idRing, porcentaje, idValor) {
    const ring = document.getElementById(idRing);
    const valor = document.getElementById(idValor);
    if (!ring || !valor) return;

    const circunferencia = 213.6;
    const offset = circunferencia - (porcentaje / 100) * circunferencia;
    ring.style.strokeDashoffset = offset;

    const duracion = 1200;
    const inicio = performance.now();
    function tick(tiempo) {
        const progreso = Math.min((tiempo - inicio) / duracion, 1);
        const valorActual = Math.floor(porcentaje * easeOutCubic(progreso));
        valor.textContent = valorActual + '%';
        if (progreso < 1) requestAnimationFrame(tick);
    }
    requestAnimationFrame(tick);
}

function animarNumero(id, valorFinal) {
    const el = document.getElementById(id);
    if (!el) return;
    
    const valorActual = parseInt(el.textContent.replace(/\D/g, '')) || 0;
    if (valorActual === valorFinal) return;
    
    const duracion = 600;
    const inicio = performance.now();
    
    function actualizar(tiempo) {
        const progreso = Math.min((tiempo - inicio) / duracion, 1);
        const valor = Math.floor(valorActual + (valorFinal - valorActual) * easeOutCubic(progreso));
        el.textContent = valor.toLocaleString('es-CL');
        if (progreso < 1) requestAnimationFrame(actualizar);
    }
    
    requestAnimationFrame(actualizar);
}

function easeOutCubic(x) {
    return 1 - Math.pow(1 - x, 3);
}

function renderizarDonut(datos) {
    const contenedor = document.getElementById('chart-donut');
    if (!contenedor) return;
    
    if (datos.length === 0) {
        contenedor.innerHTML = '<div class="empty-state"><p>Sin datos disponibles</p></div>';
        return;
    }
    
    const etiquetas = datos.map(d => formatearEstado(d.estado_actual));
    const valores = datos.map(d => parseInt(d.total));
    const colores = datos.map(d => getColorEstado(d.estado_actual));
    
    if (window.chartDonut) window.chartDonut.destroy();
    
    window.chartDonut = new ApexCharts(contenedor, {
        chart: { type: 'donut', height: 260, fontFamily: "'Inter', sans-serif", animations: { enabled: true, speed: 800 } },
        series: valores,
        labels: etiquetas,
        colors: colores,
        legend: { position: 'bottom', fontSize: '12px', labels: { colors: '#64748b' } },
        dataLabels: { enabled: true, style: { fontSize: '11px', fontWeight: 600 } },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        name: { fontSize: '13px', color: '#64748b' },
                        value: { fontSize: '18px', fontWeight: 700, color: '#1e293b' },
                        total: {
                            show: true,
                            label: 'Total',
                            fontSize: '13px',
                            color: '#64748b',
                            formatter: () => valores.reduce((a, b) => a + b, 0).toLocaleString('es-CL')
                        }
                    }
                }
            }
        },
        tooltip: { y: { formatter: (val) => `${val} observaciones` } },
        responsive: [{ breakpoint: 480, options: { legend: { position: 'bottom' } } }]
    });
    
    window.chartDonut.render();
}

function renderizarLineas(datos) {
    const contenedor = document.getElementById('chart-lineas');
    if (!contenedor) return;
    
    if (datos.length === 0) {
        contenedor.innerHTML = '<div class="empty-state"><p>Sin datos disponibles</p></div>';
        return;
    }
    
    const mesesOrden = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    
    const datosOrdenados = mesesOrden.map((mes, i) => {
        const item = datos.find(d => d.mes === mes);
        return item ? parseInt(item.total) : 0;
    });
    
    const etiquetas = mesesOrden.map(m => m.substring(0, 3));
    
    if (window.chartLineas) window.chartLineas.destroy();
    
    window.chartLineas = new ApexCharts(contenedor, {
        chart: { type: 'area', height: 260, fontFamily: "'Inter', sans-serif", toolbar: { show: false }, animations: { enabled: true, speed: 800 } },
        series: [{ name: 'Observaciones', data: datosOrdenados }],
        colors: ['#0ea5e9'],
        fill: {
            type: 'gradient',
            gradient: { shade: 'light', type: 'vertical', opacityFrom: 0.5, opacityTo: 0.05 }
        },
        stroke: { width: 3, curve: 'smooth', colors: ['#0ea5e9'] },
        dataLabels: { enabled: false },
        xaxis: { categories: etiquetas, labels: { style: { colors: '#64748b', fontSize: '11px' } } },
        yaxis: { labels: { style: { colors: '#64748b', fontSize: '11px' } } },
        grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
        tooltip: { y: { formatter: (val) => `${val} obs.` } }
    });
    
    window.chartLineas.render();
}

function getColorEstado(estado) {
    const colores = {
        'pendiente': '#f59e0b',
        'aprobado': '#16a34a',
        'rechazado': '#dc2626',
        'error': '#dc2626',
        'justificado': '#0ea5e9'
    };
    return colores[estado] || '#64748b';
}

function formatearEstado(estado) {
    if (!estado) return 'Sin estado';
    return estado.charAt(0).toUpperCase() + estado.slice(1);
}

function renderizarBreakdown(data) {
    const totalGlobal = (data.donut || []).reduce((s, d) => s + parseInt(d.total || 0), 0);
    renderTablaBreakdown('tbody-tipo', data.barras || [], totalGlobal, 'tipo_error', 'tipo');
    renderTablaBreakdown('tbody-mes', data.lineas || [], totalGlobal, 'mes', 'mes');
}

function renderTablaBreakdown(tbodyId, datos, total, campo, tipo) {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;
    if (!datos || datos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="loading-cell">Sin datos para el período</td></tr>`;
        return;
    }

    const colores = {
        'pendiente': '#f59e0b',
        'aprobado': '#16a34a',
        'rechazado': '#dc2626',
        'error': '#dc2626',
        'justificado': '#0ea5e9'
    };
    const coloresTipo = ['#0ea5e9', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#3b82f6', '#ef4444', '#6366f1', '#14b8a6', '#f97316'];
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    const filas = datos.map((d, i) => {
        const valor = parseInt(d.total);
        const pct = total > 0 ? (valor / total) * 100 : 0;
        const etiqueta = d[campo] || '-';
        const color = tipo === 'tipo' ? coloresTipo[i % coloresTipo.length] : '#0ea5e9';
        const barClass = tipo === 'tipo' ? 'tipo' : 'estado-aprobado';

        return `<tr>
            <td>
                <div class="breakdown-label">
                    <span class="breakdown-dot" style="background:${color}"></span>
                    ${escapeHtml(etiqueta)}
                </div>
            </td>
            <td class="num">${valor.toLocaleString('es-CL')}</td>
            <td class="num breakdown-pct">${pct.toFixed(1)}%</td>
            <td>
                <div class="breakdown-bar">
                    <div class="breakdown-bar-fill ${barClass}" style="width:${pct}%"></div>
                </div>
            </td>
        </tr>`;
    }).join('');

    tbody.innerHTML = filas;
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}

function initBreakdownTabs() {
    const tabs = document.querySelectorAll('.breakdown-tab');
    const panes = document.querySelectorAll('.breakdown-pane');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            tabs.forEach(t => t.classList.toggle('active', t === tab));
            panes.forEach(p => p.classList.toggle('active', p.dataset.pane === target));
        });
    });
}

document.addEventListener('DOMContentLoaded', initBreakdownTabs);

function mostrarCarga(cargando) {
    const loaders = document.querySelectorAll('.loading');
    loaders.forEach(el => {
        el.style.display = cargando ? 'block' : 'none';
    });
}

function mostrarError(mensaje) {
    const container = document.getElementById('alertas-container');
    if (container) {
        const div = document.createElement('div');
        div.className = 'alert alert-danger';
        div.textContent = mensaje;
        container.innerHTML = '';
        container.appendChild(div);
    }
}