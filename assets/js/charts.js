/**
 * Sistema de Gráficos con Chart.js
 * Visualización de datos estadísticos del sistema REM
 * Con chartjs-plugin-datalabels para mostrar valores visibles
 */

// Registrar plugin datalabels
if (typeof ChartDataLabels !== 'undefined') {
    Chart.register(ChartDataLabels);
}

// Configuración global de Chart.js
Chart.defaults.font.family = '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
Chart.defaults.color = '#64748b';

// Paleta de colores del sistema
const COLORS = {
    pendiente: '#f59e0b',
    aprobado: '#10b981',
    rechazado: '#ef4444',
    error: '#dc2626',
    justificado: '#0ea5e9',
    primary: '#0ea5e9',
    secondary: '#64748b',
    success: '#10b981',
    danger: '#ef4444',
    warning: '#f59e0b',
    info: '#0ea5e9'
};

// ============================================
// Configuración común de datalabels
// ============================================

const datalabelsBarHorizontal = {
    anchor: 'end',
    align: 'end',
    offset: 4,
    color: '#334155',
    font: { weight: 'bold', size: 12 },
    formatter: value => value
};

const datalabelsBarVertical = {
    anchor: 'end',
    align: 'top',
    offset: 4,
    color: '#334155',
    font: { weight: 'bold', size: 12 },
    formatter: value => value
};

const datalabelsDoughnut = {
    anchor: 'center',
    align: 'center',
    color: '#fff',
    font: { weight: 'bold', size: 13 },
    formatter: (value, ctx) => {
        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
        const pct = total > 0 ? ((value / total) * 100).toFixed(0) : 0;
        return value + '\n(' + pct + '%)';
    }
};

// ============================================
// Gráficos del Dashboard
// ============================================

/**
 * D1 - Distribución por Estado (barras horizontales con datalabels)
 */
function createEstadoChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const labels = data.map(item => item.estado_actual.charAt(0).toUpperCase() + item.estado_actual.slice(1));
    const values = data.map(item => parseInt(item.total));
    const colors = data.map(item => COLORS[item.estado_actual] || COLORS.secondary);

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cantidad',
                data: values,
                backgroundColor: colors,
                borderColor: colors.map(c => c + 'cc'),
                borderWidth: 2,
                borderRadius: 8,
                barThickness: 36
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: datalabelsBarHorizontal,
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.x} observaciones`
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { display: true, drawBorder: false, color: 'rgba(226, 232, 240, 0.5)' },
                    ticks: { stepSize: 1, font: { size: 11 } }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { size: 12, weight: '600' } }
                }
            }
        }
    });
}

/**
 * D2 - Top Tipos de Error (barras horizontales con datalabels)
 */
function createTipoErrorChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const labels = data.map(item => item.tipo_error);
    const values = data.map(item => parseInt(item.total));
    const palette = [
        '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
        '#ec4899', '#14b8a6', '#f97316', '#06b6d4', '#84cc16'
    ];
    const colors = data.map((_, i) => palette[i % palette.length]);

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cantidad',
                data: values,
                backgroundColor: colors.map(c => c + 'cc'),
                borderColor: colors,
                borderWidth: 1,
                borderRadius: 6,
                barThickness: 28
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: datalabelsBarHorizontal,
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.x} observaciones`
                    }
                }
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
}

/**
 * D3 - Observaciones por Mes (barras verticales con datalabels)
 */
function createTendenciaChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const labels = data.map(item => item.mes.substring(0, 3));
    const values = data.map(item => parseInt(item.total));

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Observaciones',
                data: values,
                backgroundColor: '#0ea5e9cc',
                borderColor: '#0ea5e9',
                borderWidth: 1,
                borderRadius: 6,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: datalabelsBarVertical,
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} observaciones`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { drawBorder: false, color: 'rgba(226, 232, 240, 0.5)' },
                    ticks: { stepSize: 1, font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });
}

// ============================================
// Gráficos de Reportes (genéricos con datalabels)
// ============================================

function createBarHorizontal(canvasId, labels, values, color) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    if (!labels.length) return null;

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cantidad',
                data: values,
                backgroundColor: color + 'cc',
                borderColor: color,
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: datalabelsBarHorizontal,
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.parsed.x}` }
                }
            },
            scales: {
                x: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

function createBarVertical(canvasId, labels, values, color) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    if (!labels.length) return null;

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cantidad',
                data: values,
                backgroundColor: color + 'cc',
                borderColor: color,
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: datalabelsBarVertical,
                tooltip: {
                    callbacks: { label: ctx => ` ${ctx.parsed.y}` }
                }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { ticks: { maxRotation: 45, minRotation: 45 } }
            }
        }
    });
}

// ============================================
// Inicialización del Dashboard
// ============================================

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

// Exponer funciones globalmente
window.createEstadoChart = createEstadoChart;
window.createTendenciaChart = createTendenciaChart;
window.createTipoErrorChart = createTipoErrorChart;
window.createBarHorizontal = createBarHorizontal;
window.createBarVertical = createBarVertical;
window.initializeCharts = initializeCharts;
