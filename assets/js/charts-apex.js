window.apexChartsInstances = window.apexChartsInstances || {};

const PALETTE_SISTEMA = {
    pendiente: '#f59e0b',
    aprobado: '#059669',
    rechazado: '#dc2626',
    error: '#b91c1c',
    justificado: '#0284c7',
    primary: '#0ea5e9',
    secondary: '#64748b',
    success: '#059669',
    danger: '#dc2626',
    warning: '#f59e0b',
    info: '#0284c7'
};

const PALETTE_ERRORES = [
    '#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899',
    '#14b8a6', '#f97316', '#06b6d4', '#84cc16', '#6366f1'
];

const PALETTE_TENDENCIA = { start: '#e0f2fe', end: '#0ea5e9' };

function getCSSVar(name, fallback) {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;
}

function destroyChart(id) {
    if (window.apexChartsInstances[id]) {
        window.apexChartsInstances[id].destroy();
        delete window.apexChartsInstances[id];
    }
}

function buildTooltipHtml(value, total, label, emoji) {
    const pct = total > 0 ? ((value / total) * 100).toFixed(0) : 0;
    const barLen = total > 0 ? Math.max(1, Math.round((value / total) * 15)) : 1;
    const bar = '\u2588'.repeat(barLen) + '\u2591'.repeat(Math.max(0, 15 - barLen));
    const emojiChar = emoji || '';
    return `<div class="py-1">${emojiChar} <strong>${value}</strong> observaciones (${pct}%)<br><span style="font-size:14px;letter-spacing:1px">${bar}</span><br><span class="text-secondary text-xs">Total: ${total}</span></div>`;
}

const TOOLTIP_THEME = {
    theme: 'light',
    style: { fontSize: '12px', fontFamily: "'Inter', -apple-system, sans-serif" },
    marker: { show: false },
    fixed: { enabled: false },
    x: { show: false }
};

function createBarHorizontal(canvasId, labels, values, color) {
    const el = document.getElementById(canvasId);
    if (!el || !labels.length) return null;
    destroyChart(canvasId);

    const total = values.reduce((a, b) => a + b, 0);
    const chart = new ApexCharts(el, {
        chart: {
            type: 'bar',
            height: '100%',
            fontFamily: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            toolbar: { show: true, tools: { download: true, zoom: false, pan: false, reset: false } },
            animations: { enabled: true, easing: 'easeout', speed: 800, animateGradually: { enabled: true, delay: 120 } },
            events: {
                mounted: function(chartContext) { chartContext.windowResizeHandler(); }
            }
        },
        series: [{ name: 'Cantidad', data: values }],
        colors: [color],
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 4,
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            textAnchor: 'start',
            style: { colors: ['#fff'], fontSize: '12px', fontWeight: 700 },
            offsetX: -8,
            formatter: function(val) {
                if (!val || val === 0) return '';
                return val;
            }
        },
        xaxis: {
            categories: labels,
            labels: { style: { fontSize: '11px', colors: '#64748b' } }
        },
        yaxis: {
            labels: { style: { fontSize: '12px', fontWeight: 600, colors: '#1e293b' } }
        },
        grid: {
            xaxis: { lines: { show: true } },
            borderColor: '#e2e8f0',
            strokeDashArray: 4
        },
        tooltip: {
            ...TOOLTIP_THEME,
            y: {
                formatter: function(val) {
                    const pct = total > 0 ? ((val / total) * 100).toFixed(0) : 0;
                    return `${val} observaciones (${pct}%)`;
                }
            }
        },
        states: { hover: { filter: { type: 'darken', value: 0.15 } } }
    });
    chart.render();
    window.apexChartsInstances[canvasId] = chart;
    return chart;
}

function createBarVertical(canvasId, labels, values, color) {
    const el = document.getElementById(canvasId);
    if (!el || !labels.length) return null;
    destroyChart(canvasId);

    const total = values.reduce((a, b) => a + b, 0);
    const chart = new ApexCharts(el, {
        chart: {
            type: 'bar',
            height: '100%',
            fontFamily: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            toolbar: { show: true, tools: { download: true, zoom: false, pan: false, reset: false } },
            animations: { enabled: true, easing: 'easeout', speed: 800, animateGradually: { enabled: true, delay: 120 } }
        },
        series: [{ name: 'Cantidad', data: values }],
        colors: [color],
        plotOptions: {
            bar: {
                horizontal: false,
                borderRadius: 4,
                columnWidth: '60%',
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            style: { colors: ['#fff'], fontSize: '11px', fontWeight: 700 },
            offsetY: -6,
            formatter: function(val) {
                if (!val || val === 0) return '';
                return val;
            }
        },
        xaxis: {
            categories: labels,
            labels: { style: { fontSize: '11px', colors: '#64748b' }, maxRotation: 45, minRotation: 45 }
        },
        yaxis: {
            labels: { style: { fontSize: '11px', colors: '#64748b' } }
        },
        grid: {
            yaxis: { lines: { show: true } },
            borderColor: '#e2e8f0',
            strokeDashArray: 4
        },
        tooltip: {
            ...TOOLTIP_THEME,
            y: {
                formatter: function(val) {
                    const pct = total > 0 ? ((val / total) * 100).toFixed(0) : 0;
                    return `${val} observaciones (${pct}%)`;
                }
            }
        },
        states: { hover: { filter: { type: 'darken', value: 0.15 } } }
    });
    chart.render();
    window.apexChartsInstances[canvasId] = chart;
    return chart;
}

function createEstadoChart(canvasId, data) {
    const el = document.getElementById(canvasId);
    if (!el || !data.length) return null;
    destroyChart(canvasId);

    const labels = data.map(item => item.estado_actual.charAt(0).toUpperCase() + item.estado_actual.slice(1));
    const values = data.map(item => parseInt(item.total));
    const colors = data.map(item => PALETTE_SISTEMA[item.estado_actual] || PALETTE_SISTEMA.secondary);
    const total = values.reduce((a, b) => a + b, 0);

    const emojiMap = data.map(item => {
        const map = { pendiente: '\u23F3', aprobado: '\u2705', rechazado: '\u274C', error: '\u26A0\uFE0F', justificado: '\u2139\uFE0F' };
        return map[item.estado_actual] || '\uD83D\uDCCA';
    });

    const chart = new ApexCharts(el, {
        chart: {
            type: 'bar',
            height: '100%',
            fontFamily: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            toolbar: { show: true, tools: { download: true, zoom: false, pan: false, reset: false } },
            animations: { enabled: true, easing: 'easeout', speed: 800, animateGradually: { enabled: true, delay: 120 } }
        },
        series: [{ name: 'Cantidad', data: values }],
        colors: colors,
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 6,
                barHeight: '70%',
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            textAnchor: 'start',
            style: { colors: ['#fff'], fontSize: '12px', fontWeight: 700 },
            offsetX: -8,
            formatter: function(val) {
                if (!val || val === 0) return '';
                return val;
            }
        },
        xaxis: {
            categories: labels,
            labels: { style: { fontSize: '11px', colors: '#64748b' } }
        },
        yaxis: {
            labels: { style: { fontSize: '12px', fontWeight: 600, colors: '#1e293b' } }
        },
        grid: {
            xaxis: { lines: { show: true } },
            borderColor: '#e2e8f0',
            strokeDashArray: 4
        },
        tooltip: {
            ...TOOLTIP_THEME,
            custom: function({ series, seriesIndex, dataPointIndex, w }) {
                const val = series[seriesIndex][dataPointIndex];
                return buildTooltipHtml(val, total, labels[dataPointIndex], emojiMap[dataPointIndex]);
            }
        },
        states: { hover: { filter: { type: 'darken', value: 0.15 } } }
    });
    chart.render();
    window.apexChartsInstances[canvasId] = chart;
    return chart;
}

function createTipoErrorChart(canvasId, data) {
    const el = document.getElementById(canvasId);
    if (!el || !data.length) return null;
    destroyChart(canvasId);

    const labels = data.map(item => item.tipo_error);
    const values = data.map(item => parseInt(item.total));
    const colors = data.map((_, i) => PALETTE_ERRORES[i % PALETTE_ERRORES.length]);
    const total = values.reduce((a, b) => a + b, 0);

    const chart = new ApexCharts(el, {
        chart: {
            type: 'bar',
            height: '100%',
            fontFamily: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            toolbar: { show: true, tools: { download: true, zoom: false, pan: false, reset: false } },
            animations: { enabled: true, easing: 'easeout', speed: 800, animateGradually: { enabled: true, delay: 120 } }
        },
        series: [{ name: 'Cantidad', data: values }],
        colors: colors,
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 4,
                barHeight: '65%',
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            textAnchor: 'start',
            style: { colors: ['#fff'], fontSize: '11px', fontWeight: 700 },
            offsetX: -8,
            formatter: function(val) {
                if (!val || val === 0) return '';
                return val;
            }
        },
        xaxis: {
            categories: labels,
            labels: { style: { fontSize: '11px', colors: '#64748b' } }
        },
        yaxis: {
            labels: { style: { fontSize: '11px', fontWeight: 500, colors: '#1e293b' } }
        },
        grid: {
            xaxis: { lines: { show: true } },
            borderColor: '#e2e8f0',
            strokeDashArray: 4
        },
        tooltip: {
            ...TOOLTIP_THEME,
            y: {
                formatter: function(val) {
                    const pct = total > 0 ? ((val / total) * 100).toFixed(0) : 0;
                    return `${val} observaciones (${pct}%)`;
                }
            }
        },
        states: { hover: { filter: { type: 'darken', value: 0.15 } } }
    });
    chart.render();
    window.apexChartsInstances[canvasId] = chart;
    return chart;
}

function createTendenciaChart(canvasId, data) {
    const el = document.getElementById(canvasId);
    if (!el || !data.length) return null;
    destroyChart(canvasId);

    const labels = data.map(item => item.mes.substring(0, 3));
    const values = data.map(item => parseInt(item.total));
    const total = values.reduce((a, b) => a + b, 0);
    const primaryColor = getCSSVar('--tblr-primary', PALETTE_TENDENCIA.end);

    const chart = new ApexCharts(el, {
        chart: {
            type: 'bar',
            height: '100%',
            fontFamily: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            toolbar: { show: true, tools: { download: true, zoom: false, pan: false, reset: false } },
            animations: { enabled: true, easing: 'easeout', speed: 800, animateGradually: { enabled: true, delay: 120 } }
        },
        series: [{ name: 'Observaciones', data: values }],
        colors: [primaryColor],
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                gradientToColors: ['#e0f2fe'],
                opacityFrom: 0.9,
                opacityTo: 0.3
            }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                borderRadius: 4,
                columnWidth: '60%',
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: {
            enabled: true,
            style: { colors: ['#fff'], fontSize: '11px', fontWeight: 700 },
            offsetY: -6,
            formatter: function(val) {
                if (!val || val === 0) return '';
                return val;
            }
        },
        xaxis: {
            categories: labels,
            labels: { style: { fontSize: '11px', colors: '#64748b' } }
        },
        yaxis: {
            labels: { style: { fontSize: '11px', colors: '#64748b' } }
        },
        grid: {
            yaxis: { lines: { show: true } },
            borderColor: '#e2e8f0',
            strokeDashArray: 4
        },
        tooltip: {
            ...TOOLTIP_THEME,
            y: {
                formatter: function(val) {
                    const pct = total > 0 ? ((val / total) * 100).toFixed(0) : 0;
                    return `${val} observaciones (${pct}%)`;
                }
            }
        },
        states: { hover: { filter: { type: 'darken', value: 0.15 } } }
    });
    chart.render();
    window.apexChartsInstances[canvasId] = chart;
    return chart;
}

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

function renderStackedBarChart(canvasId, labels, datasets) {
    const el = document.getElementById(canvasId);
    if (!el || !labels.length) return null;
    destroyChart(canvasId);

    const series = datasets.map(d => ({
        name: d.label,
        data: d.data
    }));
    const colors = datasets.map(d => d.color);

    const chart = new ApexCharts(el, {
        chart: {
            type: 'bar',
            height: '100%',
            fontFamily: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            stacked: true,
            toolbar: { show: true, tools: { download: true, zoom: false, pan: false, reset: false } },
            animations: { enabled: true, easing: 'easeout', speed: 800 }
        },
        series: series,
        colors: colors,
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 2,
                dataLabels: { position: 'top' }
            }
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: labels,
            labels: { style: { fontSize: '11px', colors: '#64748b' } }
        },
        yaxis: {
            labels: { style: { fontSize: '12px', fontWeight: 600, colors: '#1e293b' } }
        },
        grid: {
            xaxis: { lines: { show: true } },
            borderColor: '#e2e8f0',
            strokeDashArray: 4
        },
        tooltip: {
            ...TOOLTIP_THEME,
            y: {
                formatter: function(val) { return `${val} meses`; }
            }
        },
        legend: {
            show: true,
            position: 'bottom',
            fontSize: '12px',
            markers: { width: 12, height: 12 }
        }
    });
    chart.render();
    window.apexChartsInstances[canvasId] = chart;
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
