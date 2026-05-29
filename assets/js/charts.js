/**
 * Sistema de Gráficos con Chart.js — Refinado
 * Gradientes, tooltips enriquecidos, animación secuencial, hover dim, export PNG
 */

if (typeof ChartDataLabels !== 'undefined') {
    Chart.register(ChartDataLabels);
}

// ─── Configuración Global ─────────────────────────────────
Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif";
Chart.defaults.color = '#64748b';

// ─── Paletas de Color ────────────────────────────────────
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

// ─── Helpers ─────────────────────────────────────────────
function hexToRgba(hex, alpha) {
    if (!hex || typeof hex !== 'string') return `rgba(148, 163, 184, ${alpha})`;
    hex = hex.replace('#', '');
    if (hex.length === 3) hex = hex.split('').map(c => c + c).join('');
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

// ─── Plugin: Efectos Visuales (Gradiente + Hover Dim) ───
const chartEffectsPlugin = {
    id: 'chartEffects',
    afterEvent(chart, args) {
        const { event } = args;
        if (event.type === 'mousemove') {
            chart._activeElements = chart.getElementsAtEventForMode(
                event, 'nearest', { intersect: false }, false
            );
            chart.draw();
        } else if (event.type === 'mouseout') {
            chart._activeElements = [];
            chart.draw();
        }
    },
    beforeDatasetDraw(chart, args) {
        if (args.index !== 0) return;
        const meta = chart.getDatasetMeta(0);
        if (!meta.data || meta.data.length === 0) return;
        const colors = chart._barColors;
        if (!colors) return;

        const ctx = chart.ctx;
        const isHorizontal = chart.options.indexAxis === 'y';
        const active = chart._activeElements || [];
        const activeIdx = active.length > 0 && active[0].datasetIndex === 0 ? active[0].index : -1;
        const anyActive = activeIdx >= 0;

        meta.data.forEach((bar, i) => {
            const color = colors[i] || '#64748b';
            let w, h, startX, startY;

            if (isHorizontal) {
                startX = bar.base;
                startY = bar.y - (bar.height || 0) / 2;
                w = Math.abs(bar.x - bar.base);
                h = bar.height || 0;
            } else {
                startX = bar.x - (bar.width || 0) / 2;
                startY = bar.base;
                w = bar.width || 0;
                h = Math.abs(bar.y - bar.base);
            }

            if (w < 1 && h < 1) {
                bar.options.backgroundColor = color;
                return;
            }

            if (anyActive && i !== activeIdx) {
                bar.options.backgroundColor = hexToRgba(color, 0.08);
                bar.options.borderColor = 'transparent';
                bar.options.borderWidth = 0;
            } else {
                let grad;
                if (isHorizontal) {
                    grad = ctx.createLinearGradient(startX, 0, startX + w, 0);
                } else {
                    grad = ctx.createLinearGradient(0, startY, 0, startY + h);
                }
                grad.addColorStop(0, hexToRgba(color, 0.25));
                grad.addColorStop(1, color);

                bar.options.backgroundColor = grad;
                bar.options.borderColor = anyActive && i === activeIdx ? color : hexToRgba(color, 0.5);
                bar.options.borderWidth = anyActive && i === activeIdx ? 2 : 1;
            }
        });
    }
};

Chart.register(chartEffectsPlugin);

// ─── Tooltip Base ────────────────────────────────────────
function buildTooltipCallbacks(emojiMap) {
    return {
        title: () => '',
        label: function(ctx) {
            const value = ctx.parsed.x || ctx.parsed.y || 0;
            const data = ctx.dataset.data;
            const total = data.reduce((a, b) => a + b, 0);
            const pct = total > 0 ? ((value / total) * 100).toFixed(0) : 0;
            const barLen = total > 0 ? Math.max(1, Math.round((value / total) * 15)) : 1;
            const bar = '█'.repeat(barLen) + '░'.repeat(Math.max(0, 15 - barLen));
            const emoji = emojiMap ? (emojiMap[ctx.dataIndex] || '📊') : '📊';
            return `${emoji} ${value} observaciones (${pct}%)\n${bar}`;
        },
        afterLabel: function(ctx) {
            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
            return `\n Total: ${total}`;
        }
    };
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
    padding: 14,
    cornerRadius: 16,
    titleFont: { weight: '700', size: 13 },
    bodyFont: { size: 12 },
    displayColors: false
};

// ─── Datalabels ──────────────────────────────────────────
const datalabelsBarInside = {
    anchor: 'end',
    align: 'end',
    offset: -8,
    color: '#fff',
    font: { weight: '700', size: 12 },
    textStrokeColor: 'rgba(0,0,0,0.2)',
    textStrokeWidth: 1,
    formatter: v => v,
    display: smartLabelDisplay
};

const datalabelsBarVerticalInside = {
    anchor: 'end',
    align: 'top',
    offset: -6,
    color: '#fff',
    font: { weight: '700', size: 11 },
    textStrokeColor: 'rgba(0,0,0,0.2)',
    textStrokeWidth: 1,
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

// ─── Animación por defecto ───────────────────────────────
const ANIM_CONFIG = {
    duration: 800,
    easing: 'easeOutQuart',
    delay: function(ctx) {
        if (ctx.dataIndex === undefined) return 0;
        const count = ctx.dataset.data.length;
        return count > 30 ? 0 : ctx.dataIndex * 120;
    }
};

// ─── Export Button ───────────────────────────────────────
(function injectExportStyles() {
    const id = 'chart-export-styles';
    if (document.getElementById(id)) return;
    const style = document.createElement('style');
    style.id = id;
    style.textContent = `
        .chart-export-btn {
            position: absolute; top: 8px; right: 8px; z-index: 10;
            width: 36px; height: 36px; border-radius: 10px;
            border: 1px solid #e2e8f0; background: rgba(255,255,255,0.85);
            cursor: pointer; display: flex; align-items: center;
            justify-content: center; font-size: 16px;
            transition: all 0.2s; opacity: 0.5;
            backdrop-filter: blur(4px); line-height: 1;
        }
        .chart-export-btn:hover {
            opacity: 1; background: white; border-color: #94a3b8;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        .chart-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; }
        .chart-header h3 { margin-bottom: 0 !important; }
    `;
    document.head.appendChild(style);
})();

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
        const canvas = chart.canvas;
        const origBg = canvas.style.backgroundColor;
        canvas.style.backgroundColor = '#ffffff';
        const link = document.createElement('a');
        link.href = chart.toBase64Image('image/png', 1);
        link.download = (chartId || 'chart') + '.png';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        canvas.style.backgroundColor = origBg;
    });

    container.appendChild(btn);
}

// ─── Scale helpers ────────────────────────────────────────
const SCALE_DEFAULT = {
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

    const emojiMap = data.map(item => {
        const map = { pendiente: '⏳', aprobado: '✅', rechazado: '❌', error: '⚠️', justificado: 'ℹ️' };
        return map[item.estado_actual] || '📊';
    });

    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Cantidad',
                data: values,
                borderWidth: 1,
                borderRadius: 8,
                barThickness: 36
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: { ...ANIM_CONFIG },
            plugins: {
                legend: { display: false },
                tooltip: { ...TOOLTIP_BASE, callbacks: buildTooltipCallbacks(emojiMap) },
                datalabels: datalabelsBarInside
            },
            scales: { ...SCALE_DEFAULT }
        }
    });

    chart._barColors = colors;
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
                borderWidth: 1,
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
                tooltip: { ...TOOLTIP_BASE, callbacks: buildTooltipCallbacks() },
                datalabels: datalabelsBarInside
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { display: true, drawBorder: false, color: 'rgba(226, 232, 240, 0.5)' },
                    ticks: { stepSize: 1, font: { size: 11 } }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 11, weight: '500' } }
                }
            }
        }
    });

    chart._barColors = colors;
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
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Observaciones',
                data: values,
                borderWidth: 1,
                borderRadius: 6,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { ...ANIM_CONFIG },
            plugins: {
                legend: { display: false },
                tooltip: { ...TOOLTIP_BASE, callbacks: buildTooltipCallbacks() },
                datalabels: datalabelsBarVerticalInside
            },
            scales: { ...SCALE_VERTICAL }
        }
    });

    chart._barColors = values.map(() => color);
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
                borderWidth: 1,
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
                tooltip: { ...TOOLTIP_BASE, callbacks: buildTooltipCallbacks() },
                datalabels: datalabelsBarInside
            },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    chart._barColors = values.map(() => color);
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
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { ...ANIM_CONFIG },
            plugins: {
                legend: { display: false },
                tooltip: { ...TOOLTIP_BASE, callbacks: buildTooltipCallbacks() },
                datalabels: datalabelsBarVerticalInside
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { ticks: { maxRotation: 45, minRotation: 45 } }
            }
        }
    });

    chart._barColors = values.map(() => color);
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
        borderWidth: 1,
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

// Exponer globalmente
window.PALETTE_SISTEMA = PALETTE_SISTEMA;
window.PALETTE_ERRORES = PALETTE_ERRORES;
window.createEstadoChart = createEstadoChart;
window.createTendenciaChart = createTendenciaChart;
window.createTipoErrorChart = createTipoErrorChart;
window.createBarHorizontal = createBarHorizontal;
window.createBarVertical = createBarVertical;
window.renderStackedBarChart = renderStackedBarChart;
window.initializeCharts = initializeCharts;
