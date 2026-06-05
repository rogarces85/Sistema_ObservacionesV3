/**
 * dashboard.js - Dashboard simplificado
 * Carga estadísticas y gráficos fidedignos desde la BD
 */

'use strict';

document.addEventListener('DOMContentLoaded', async () => {
    const config = window.DASHBOARD_CONFIG || {};
    const anio = config.anio || new Date().getFullYear();
    
    cargarDashboard(anio);
    
    document.getElementById('year-selector')?.addEventListener('change', function() {
        window.location.href = `?pagina=dashboard&anio=${this.value}`;
    });
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

function mostrarCarga(cargando) {
    const loaders = document.querySelectorAll('.loading');
    loaders.forEach(el => {
        el.style.display = cargando ? 'block' : 'none';
    });
}

function mostrarError(mensaje) {
    const container = document.getElementById('alertas-container');
    if (container) {
        container.innerHTML = `<div class="alert alert-danger">${mensaje}</div>`;
    }
}