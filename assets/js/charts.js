if (typeof ChartDataLabels !== 'undefined') {
    Chart.register(ChartDataLabels);
}

/* === Token helpers === */
function chartTokenColor(name, fallback) {
    if (typeof getComputedStyle === 'undefined') return fallback;
    const v = getComputedStyle(document.documentElement).getPropertyValue(name);
    return (v && v.trim()) || fallback;
}

function chartBaseFont() {
    return chartTokenColor('--tblr-font-family-base', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif");
}

function applyChartDefaults() {
    if (typeof Chart === 'undefined') return;
    const theme = getChartTheme();
    Chart.defaults.font.family = chartBaseFont();
    Chart.defaults.color = theme.text;
    Chart.defaults.borderColor = theme.border;
}

applyChartDefaults();

const REM_CHARTS = new Map();

function getChartTheme() {
    return {
        text: chartTokenColor('--tblr-body-color', '#1e293b'),
        muted: chartTokenColor('--chart-tick', '#64748b'),
        legend: chartTokenColor('--chart-legend', '#1e293b'),
        border: chartTokenColor('--tblr-border-color', '#e2e8f0'),
        grid: chartTokenColor('--chart-grid', 'rgba(226, 232, 240, 0.72)'),
        tooltipBg: chartTokenColor('--chart-tooltip-bg', '#ffffff'),
        tooltipText: chartTokenColor('--chart-tooltip-text', '#1e293b'),
        tooltipMuted: chartTokenColor('--chart-tooltip-muted', '#475569'),
        tooltipBorder: chartTokenColor('--chart-tooltip-border', '#e2e8f0'),
        point: chartTokenColor('--chart-point', '#0ea5e9'),
        pointBorder: chartTokenColor('--chart-point-border', '#ffffff')
    };
}

function registerRemChart(chart, chartId) {
    if (!chart) return chart;
    const id = chartId || (chart.canvas && chart.canvas.id) || ('chart-' + REM_CHARTS.size);
    REM_CHARTS.set(id, chart);
    return chart;
}

function refreshChartTheme(chart) {
    if (!chart || !chart.options) return;
    const theme = getChartTheme();

    if (chart.options.plugins) {
        if (chart.options.plugins.legend && chart.options.plugins.legend.labels) {
            chart.options.plugins.legend.labels.color = theme.legend;
            chart.options.plugins.legend.labels.font = {
                ...(chart.options.plugins.legend.labels.font || {}),
                family: chartBaseFont()
            };
        }
        if (chart.options.plugins.tooltip) {
            chart.options.plugins.tooltip.backgroundColor = theme.tooltipBg;
            chart.options.plugins.tooltip.titleColor = theme.tooltipText;
            chart.options.plugins.tooltip.bodyColor = theme.tooltipMuted;
            chart.options.plugins.tooltip.borderColor = theme.tooltipBorder;
            chart.options.plugins.tooltip.titleFont = {
                ...(chart.options.plugins.tooltip.titleFont || {}),
                family: chartBaseFont()
            };
            chart.options.plugins.tooltip.bodyFont = {
                ...(chart.options.plugins.tooltip.bodyFont || {}),
                family: chartBaseFont()
            };
        }
    }

    Object.values(chart.options.scales || {}).forEach(function (scale) {
        if (scale.grid && scale.grid.display !== false) scale.grid.color = theme.grid;
        if (scale.ticks) scale.ticks.color = theme.muted;
    });

    chart.data.datasets.forEach(function (dataset) {
        if (chart.config.type === 'doughnut') dataset.borderColor = theme.pointBorder;
        if (Object.prototype.hasOwnProperty.call(dataset, 'pointBorderColor')) dataset.pointBorderColor = theme.pointBorder;
    });
    chart.update('none');
}

function refreshAllChartThemes() {
    applyChartDefaults();
    REM_CHARTS.forEach(refreshChartTheme);
}

window.addEventListener('rem:theme-changed', refreshAllChartThemes);

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

function tooltipBase(displayColors) {
    const theme = getChartTheme();
    return {
        enabled: true,
        mode: 'index',
        intersect: false,
        backgroundColor: theme.tooltipBg,
        titleColor: theme.tooltipText,
        bodyColor: theme.tooltipMuted,
        borderColor: theme.tooltipBorder,
        borderWidth: 1,
        padding: 12,
        cornerRadius: 12,
        titleFont: { weight: '600', size: 13, family: chartBaseFont() },
        bodyFont: { size: 12, family: chartBaseFont() },
        displayColors: Boolean(displayColors)
    };
}

function tooltipPct() {
    return {
        ...tooltipBase(true),
        callbacks: {
            label: function(ctx) {
                const value = ctx.parsed || 0;
                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                const pct = total > 0 ? ((value / total) * 100).toFixed(0) : 0;
                return ` ${value} observaciones (${pct}%)`;
            }
        }
    };
}

function tooltipValor() {
    return {
        ...tooltipBase(false),
        callbacks: {
            label: function(ctx) {
                return ` ${ctx.parsed.y} observaciones`;
            }
        }
    };
}

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
        .chart-export-btn { position: absolute; top: 8px; right: 8px; z-index: 10; width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--chart-tooltip-border); background: var(--chart-export-bg); color: var(--chart-tooltip-text); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; transition: all 0.2s; opacity: 0.5; line-height: 1; }
        .chart-export-btn:hover { opacity: 1; background: var(--chart-export-bg-hover); border-color: var(--chart-tick); box-shadow: var(--tblr-shadow-sm); }
    `;
    document.head.appendChild(style);
})();

function scaleHorizontal() {
    const theme = getChartTheme();
    return {
        x: {
            beginAtZero: true,
            grid: { display: true, drawBorder: false, color: theme.grid },
            ticks: { stepSize: 1, font: { size: 11 }, color: theme.muted }
        },
        y: {
            grid: { display: false },
            ticks: { font: { size: 12, weight: '600' }, color: theme.text }
        }
    };
}

function scaleVertical() {
    const theme = getChartTheme();
    return {
        y: {
            beginAtZero: true,
            grid: { drawBorder: false, color: theme.grid },
            ticks: { stepSize: 1, font: { size: 11 }, color: theme.muted }
        },
        x: {
            grid: { display: false },
            ticks: { font: { size: 11 }, color: theme.muted }
        }
    };
}

// ============================================================
// GRÁFICOS DEL DASHBOARD
// ============================================================

function createEstadoChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    const theme = getChartTheme();

    const labels = data.map(item => item.estado_actual.charAt(0).toUpperCase() + item.estado_actual.slice(1));
    const values = data.map(item => parseInt(item.total));
    const colors = data.map(item => PALETTE_SISTEMA[item.estado_actual] || PALETTE_SISTEMA.secondary);
    const baseFont = chartBaseFont();

    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: theme.pointBorder
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            animation: { ...ANIM_CONFIG },
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        boxHeight: 10,
                        padding: 14,
                        font: { size: 12, family: baseFont },
                        usePointStyle: true,
                        color: theme.legend
                    }
                },
                tooltip: tooltipPct(),
                datalabels: {
                    color: '#fff',
                    font: { weight: '700', size: 11, family: baseFont },
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
    return registerRemChart(chart, canvasId);
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
                tooltip: tooltipValor(),
                datalabels: { ...datalabelsBar }
            },
            scales: scaleHorizontal()
        }
    });

    addExportButton(chart, canvasId);
    return registerRemChart(chart, canvasId);
}

function createTendenciaChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const labels = data.map(item => item.mes.substring(0, 3));
    const values = data.map(item => parseInt(item.total));
    const color = chartTokenColor('--tblr-primary', '#0ea5e9');
    const theme = getChartTheme();

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
                pointBorderColor: theme.pointBorder,
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { ...ANIM_CONFIG, duration: 800 },
            plugins: {
                legend: { display: false },
                tooltip: tooltipValor(),
                datalabels: { display: false }
            },
            scales: scaleVertical()
        }
    });

    addExportButton(chart, canvasId);
    return registerRemChart(chart, canvasId);
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
                tooltip: tooltipValor(),
                datalabels: { ...datalabelsBar }
            },
            scales: scaleHorizontal()
        }
    });

    addExportButton(chart, canvasId);
    return registerRemChart(chart, canvasId);
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
                tooltip: tooltipValor(),
                datalabels: { ...datalabelsBarVertical }
            },
            scales: {
                y: { ...scaleVertical().y },
                x: { ...scaleVertical().x, ticks: { ...scaleVertical().x.ticks, maxRotation: 45, minRotation: 45 } }
            }
        }
    });

    addExportButton(chart, canvasId);
    return registerRemChart(chart, canvasId);
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
    const theme = getChartTheme();

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
                legend: { display: true, position: 'bottom', labels: { boxWidth: 12, padding: 16, font: { size: 12, family: chartBaseFont() }, color: theme.legend } },
                tooltip: {
                    ...tooltipBase(false),
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
                    ticks: { stepSize: 1, font: { size: 11 }, color: theme.muted },
                    grid: { display: true, drawBorder: false, color: theme.grid }
                },
                y: {
                    stacked: true,
                    grid: { display: false },
                    ticks: { font: { size: 12, weight: '600' }, color: theme.text }
                }
            }
        }
    });

    addExportButton(chart, canvasId);
    return registerRemChart(chart, canvasId);
}

window.PALETTE_SISTEMA = PALETTE_SISTEMA;
window.PALETTE_ERRORES = PALETTE_ERRORES;
window.REMCharts = REM_CHARTS;
window.refreshAllChartThemes = refreshAllChartThemes;
window.createEstadoChart = createEstadoChart;
window.createTendenciaChart = createTendenciaChart;
window.createTipoErrorChart = createTipoErrorChart;
window.createBarHorizontal = createBarHorizontal;
window.createBarVertical = createBarVertical;
window.renderStackedBarChart = renderStackedBarChart;
window.initializeCharts = initializeCharts;
