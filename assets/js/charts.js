if (typeof ChartDataLabels !== 'undefined') {
    Chart.register(ChartDataLabels);
}

Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
Chart.defaults.color = '#64748b';

const PALETTE_SISTEMA = {
    pendiente:  '#f59e0b',
    aprobado:   '#059669',
    rechazado:  '#dc2626',
    error:      '#b91c1c',
    justificado:'#0284c7',
    primary:    '#0ea5e9',
    secondary:  '#64748b',
    success:    '#059669',
    danger:     '#dc2626',
    warning:    '#f59e0b',
    info:       '#0284c7'
};

const PALETTE_ERRORES = [
    '#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899',
    '#14b8a6', '#f97316', '#06b6d4', '#84cc16', '#6366f1'
];

const PALETTE_TENDENCIA = { start: '#e0f2fe', end: '#0ea5e9' };

function hexToRgba(hex, alpha) {
    if (!hex || typeof hex !== 'string') return `rgba(148, 163, 184, ${alpha})`;
    hex = hex.replace('#', '');
    if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

const TOOLTIP_BASE = {
    enabled: true,
    mode: 'index',
    intersect: false,
    backgroundColor: '#fff',
    titleColor: '#1e293b',
    bodyColor: '#475569',
    borderColor: '#e2e8f0',
    borderWidth: 1,
    padding: 12,
    cornerRadius: 12,
    titleFont: { weight: '600', size: 13 },
    bodyFont: { size: 12 },
    displayColors: false
};

const TOOLTIP_PCT = {
    enabled: true,
    mode: 'index',
    intersect: false,
    backgroundColor: '#fff',
    titleColor: '#1e293b',
    bodyColor: '#475569',
    borderColor: '#e2e8f0',
    borderWidth: 1,
    padding: 12,
    cornerRadius: 12,
    titleFont: { weight: '600', size: 13 },
    bodyFont: { size: 12 },
    displayColors: true,
    callbacks: {
        label: function(ctx) {
            const value = ctx.parsed || 0;
            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
            const pct = total > 0 ? ((value / total) * 100).toFixed(0) : 0;
            return ` ${value} observaciones (${pct}%)`;
        }
    }
};

const TOOLTIP_VALOR = {
    enabled: true,
    mode: 'index',
    intersect: false,
    backgroundColor: '#fff',
    titleColor: '#1e293b',
    bodyColor: '#475569',
    borderColor: '#e2e8f0',
    borderWidth: 1,
    padding: 12,
    cornerRadius: 12,
    titleFont: { weight: '600', size: 13 },
    bodyFont: { size: 12 },
    displayColors: false,
    callbacks: {
        label: function(ctx) {
            return ` ${ctx.parsed.y} observaciones`;
        }
    }
};

const datalabelsBar = {
    anchor: 'end',
    align: 'end',
    offset: -8,
    color: '#fff',
    font: { weight: '700', size: 12 },
    formatter: v => v,
    display: smartLabelDisplay
};

const datalabelsBarVertical = {
    anchor: 'end',
    align: 'top',
    offset: -6,
    color: '#fff',
    font: { weight: '700', size: 11 },
    formatter: v => v,
    display: smartLabelDisplay
};

function smartLabelDisplay(ctx) {
    const meta = ctx.chart.getDatasetMeta(ctx.datasetIndex);
    const bar = meta.data[ctx.dataIndex];
    if (!bar) return false;
    const isHorizontal = ctx.chart.options.indexAxis === 'y';
    const px = isHorizontal ? Math.abs(bar.x - bar.base) : Math.abs(bar.y - bar.base);
    return px > 35;
}

const ANIM_CONFIG = {
    duration: 600,
    easing: 'easeOutQuart',
    delay: function(ctx) {
        if (ctx.dataIndex === undefined) return 0;
        const count = ctx.dataset.data.length;
        return count > 30 ? 0 : ctx.dataIndex * 80;
    }
};

function addExportButton(chart, chartId) {
    const container = chart.canvas && chart.canvas.parentElement;
    if (!container) return;
    container.querySelector('.chart-export-btn')?.remove();
    container.style.position = 'relative';
    const btn = document.createElement('button');
    btn.className = 'chart-export-btn';
    btn.innerHTML = '📥';
    btn.title = 'Exportar PNG';
    btn.setAttribute('aria-label', 'Exportar gráfico como PNG');
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        const link = document.createElement('a');
        link.href = chart.toBase64Image('image/png', 1);
        link.download = (chartId || 'chart') + '.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
    container.appendChild(btn);
}

(function injectExportStyles() {
    const id = 'chart-export-styles';
    if (document.getElementById(id)) return;
    const style = document.createElement('style');
    style.id = id;
    style.textContent = `
        .chart-export-btn { position: absolute; top: 8px; right: 8px; z-index: 10; width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e2e8f0; background: rgba(255,255,255,0.85); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; transition: all 0.2s; opacity: 0.5; line-height: 1; }
        .chart-export-btn:hover { opacity: 1; background: white; border-color: #94a3b8; box-shadow: 0 2px 4px rgba(0,0,0,0.08); }
    `;
    document.head.appendChild(style);
})();

const SCALE_HORIZONTAL = {
    x: {
        beginAtZero: true,
        grid: { display: true, drawBorder: false, color: 'rgba(226, 232, 240, 0.5)' },
        ticks: { stepSize: 1, font: { size: 11 } }
    },
    y: {
        grid: { display: false },
        ticks: { font: { size: 12, weight: '600' } }
    }
};

const SCALE_VERTICAL = {
    y: {
        beginAtZero: true,
        grid: { drawBorder: false, color: 'rgba(226, 232, 240, 0.5)' },
        ticks: { stepSize: 1, font: { size: 11 } }
    },
    x: {
        grid: { display: false },
        ticks: { font: { size: 11 } }
    }
};

// ============================================================
// GRÁFICOS DEL DASHBOARD
// ============================================================

function createEstadoChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const labels = data.map(item => item.estado_actual.charAt(0).toUpperCase() + item.estado_actual.slice(1));
    const values = data.map(item => parseInt(item.total));
    const colors = data.map(item => PALETTE_SISTEMA[item.estado_actual] || PALETTE_SISTEMA.secondary);

    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            animation: { ...ANIM_CONFIG },
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 16, font: { size: 12 }, usePointStyle: true }
                },
                tooltip: { ...TOOLTIP_PCT },
                datalabels: {
                    color: '#fff',
                    font: { weight: '700', size: 11 },
                    formatter: (v, ctx2) => {
                        const total = ctx2.dataset.data.reduce((a, b) => a + b, 0);
                        return total > 0 ? ((v / total) * 100).toFixed(0) + '%' : '';
                    },
                    display: function(ctx2) {
                        const meta = ctx2.chart.getDatasetMeta(0);
                        const arc = meta.data[ctx2.dataIndex];
                        if (!arc) return false;
                        const total = ctx2.dataset.data.reduce((a, b) => a + b, 0);
                        const pct = (ctx2.dataset.data[ctx2.dataIndex] / total) * 100;
                        return pct > 8;
                    }
                }
            }
        }
    });

    addExportButton(chart, canvasId);
    return chart;
}

function createTipoErrorChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const labels = data.map(item => item.tipo_error);
    const values = data.map(item => parseInt(item.total));
    const colors = data.map((_, i) => PALETTE_ERRORES[i % PALETTE_ERRORES.length]);

    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Cantidad',
                data: values,
                backgroundColor: colors,
                borderWidth: 0,
                borderRadius: 6,
                barThickness: 28
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: { ...ANIM_CONFIG },
            plugins: {
                legend: { display: false },
                tooltip: { ...TOOLTIP_VALOR },
                datalabels: { ...datalabelsBar }
            },
            scales: { ...SCALE_HORIZONTAL }
        }
    });

    addExportButton(chart, canvasId);
    return chart;
}

function createTendenciaChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const labels = data.map(item => item.mes.substring(0, 3));
    const values = data.map(item => parseInt(item.total));
    const color = PALETTE_TENDENCIA.end;

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Observaciones',
                data: values,
                borderColor: color,
                backgroundColor: function(ctx2) {
                    if (!ctx2.chart || !ctx2.chart.ctx) return color;
                    const c = ctx2.chart.ctx;
                    const grad = c.createLinearGradient(0, 0, 0, ctx2.chart.height || 280);
                    grad.addColorStop(0, hexToRgba(color, 0.3));
                    grad.addColorStop(1, hexToRgba(color, 0.02));
                    return grad;
                },
                borderWidth: 2,
                fill: values.length >= 2,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: color,
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { ...ANIM_CONFIG, duration: 800 },
            plugins: {
                legend: { display: false },
                tooltip: { ...TOOLTIP_VALOR },
                datalabels: { display: false }
            },
            scales: { ...SCALE_VERTICAL }
        }
    });

    addExportButton(chart, canvasId);
    return chart;
}

// ============================================================
// GRÁFICOS DE REPORTES (genéricos)
// ============================================================

function createBarHorizontal(canvasId, labels, values, color) {
    const el = document.getElementById(canvasId);
    if (!el || !labels.length) return null;

    const chart = new Chart(el, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Cantidad',
                data: values,
                backgroundColor: color,
                borderWidth: 0,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: { ...ANIM_CONFIG },
            plugins: {
                legend: { display: false },
                tooltip: { ...TOOLTIP_VALOR },
                datalabels: { ...datalabelsBar }
            },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    addExportButton(chart, canvasId);
    return chart;
}

function createBarVertical(canvasId, labels, values, color) {
    const el = document.getElementById(canvasId);
    if (!el || !labels.length) return null;

    const chart = new Chart(el, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Cantidad',
                data: values,
                backgroundColor: color,
                borderWidth: 0,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { ...ANIM_CONFIG },
            plugins: {
                legend: { display: false },
                tooltip: { ...TOOLTIP_VALOR },
                datalabels: { ...datalabelsBarVertical }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { ticks: { maxRotation: 45, minRotation: 45 } }
            }
        }
    });

    addExportButton(chart, canvasId);
    return chart;
}

// ============================================================
// INICIALIZACIÓN
// ============================================================

function initializeCharts(statsData) {
    const charts = {};

    if (statsData.por_estado && statsData.por_estado.length > 0) {
        charts.estado = createEstadoChart('chartEstado', statsData.por_estado);
    }
    if (statsData.por_mes && statsData.por_mes.length > 0) {
        charts.tendencia = createTendenciaChart('chartTendencia', statsData.por_mes);
    }
    if (statsData.por_tipo_error && statsData.por_tipo_error.length > 0) {
        charts.tipoError = createTipoErrorChart('chartTipoError', statsData.por_tipo_error);
    }

    return charts;
}

// ============================================================
// GRÁFICO DE BARRAS APILADAS (stacked horizontal)
// ============================================================

function renderStackedBarChart(canvasId, labels, datasets) {
    const el = document.getElementById(canvasId);
    if (!el || !labels.length) return null;

    datasets = datasets.map(d => ({
        label: d.label,
        data: d.data,
        backgroundColor: d.color,
        borderWidth: 0,
        borderRadius: 4
    }));

    const chart = new Chart(el, {
        type: 'bar',
        data: { labels, datasets },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: { ...ANIM_CONFIG },
            plugins: {
                legend: { display: true, position: 'bottom', labels: { boxWidth: 12, padding: 16, font: { size: 12 } } },
                tooltip: {
                    ...TOOLTIP_BASE,
                    mode: 'point',
                    callbacks: {
                        title: () => '',
                        label: function(ctx) {
                            return `${ctx.dataset.label}: ${ctx.parsed.x} meses`;
                        },
                        afterLabel: function(ctx) {
                            const total = ctx.chart.data.datasets.reduce((s, ds) => s + (ds.data[ctx.dataIndex] || 0), 0);
                            return `Total meses con datos: ${total}`;
                        }
                    }
                },
                datalabels: { display: false }
            },
            scales: {
                x: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: { stepSize: 1, font: { size: 11 } },
                    grid: { display: true, drawBorder: false, color: 'rgba(226, 232, 240, 0.5)' }
                },
                y: {
                    stacked: true,
                    grid: { display: false },
                    ticks: { font: { size: 12, weight: '600' } }
                }
            }
        }
    });

    addExportButton(chart, canvasId);
    return chart;
}

window.PALETTE_SISTEMA = PALETTE_SISTEMA;
window.PALETTE_ERRORES = PALETTE_ERRORES;
window.createEstadoChart = createEstadoChart;
window.createTendenciaChart = createTendenciaChart;
window.createTipoErrorChart = createTipoErrorChart;
window.createBarHorizontal = createBarHorizontal;
window.createBarVertical = createBarVertical;
window.renderStackedBarChart = renderStackedBarChart;
window.initializeCharts = initializeCharts;
