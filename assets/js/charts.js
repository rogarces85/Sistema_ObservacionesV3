/**
 * Sistema de Gráficos con Chart.js
 * Visualización de datos estadísticos del sistema REM
 */

// Registrar plugin datalabels si está disponible
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

/**
 * Crear gráfico de barras horizontales - Observaciones por Estado
 */
function createEstadoChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    // Preparar datos
    const labels = data.map(item => item.estado_actual.charAt(0).toUpperCase() + item.estado_actual.slice(1));
    const values = data.map(item => parseInt(item.total));
    const colors = data.map(item => COLORS[item.estado_actual] || COLORS.secondary);

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Cantidad de Observaciones',
                data: values,
                backgroundColor: colors,
                borderColor: colors.map(c => c + 'cc'),
                borderWidth: 2,
                borderRadius: 8,
                barThickness: 40
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    cornerRadius: 8,
                    callbacks: {
                        label: function (context) {
                            return ` ${context.parsed.x} observaciones`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        drawBorder: false,
                        color: 'rgba(226, 232, 240, 0.5)'
                    },
                    ticks: {
                        stepSize: 1,
                        font: { size: 11 }
                    }
                },
                y: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 12, weight: '600' }
                    }
                }
            }
        }
    });
}

/**
 * Crear gráfico de líneas - Tendencia Mensual
 */
function createTendenciaChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const labels = data.map(item => item.mes);
    const values = data.map(item => parseInt(item.total));

    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Observaciones por Mes',
                data: values,
                borderColor: COLORS.primary,
                backgroundColor: COLORS.primary + '20',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#fff',
                pointBorderColor: COLORS.primary,
                pointBorderWidth: 2,
                pointHoverBackgroundColor: COLORS.primary,
                pointHoverBorderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    cornerRadius: 8,
                    callbacks: {
                        label: function (context) {
                            return ` ${context.parsed.y} observaciones`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(226, 232, 240, 0.5)'
                    },
                    ticks: {
                        stepSize: 1,
                        font: { size: 11 }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 11 },
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    });
}

/**
 * Crear gráfico de dona - Distribución por Tipo de Error
 */
function createTipoErrorChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const labels = data.map(item => item.tipo_error);
    const values = data.map(item => parseInt(item.total));

    // Generar colores dinámicamente
    const backgroundColors = [
        '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
        '#ec4899', '#14b8a6', '#f97316', '#06b6d4', '#84cc16'
    ];

    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: backgroundColors.slice(0, data.length),
                borderColor: '#fff',
                borderWidth: 3,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 15,
                        font: { size: 12 },
                        usePointStyle: true,
                        pointStyle: 'circle',
                        generateLabels: function (chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return {
                                        text: `${label} (${percentage}%)`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    cornerRadius: 8,
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return ` ${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
}

/**
 * Inicializar todos los gráficos
 */
function initializeCharts(statsData) {
    const charts = {};

    // Gráfico de estado
    if (statsData.por_estado && statsData.por_estado.length > 0) {
        charts.estado = createEstadoChart('chartEstado', statsData.por_estado);
    }

    // Gráfico de tendencia mensual
    if (statsData.por_mes && statsData.por_mes.length > 0) {
        charts.tendencia = createTendenciaChart('chartTendencia', statsData.por_mes);
    }

    // Gráfico de tipo de error
    if (statsData.por_tipo_error && statsData.por_tipo_error.length > 0) {
        charts.tipoError = createTipoErrorChart('chartTipoError', statsData.por_tipo_error);
    }

    return charts;
}

// Exponer funciones globalmente
window.createEstadoChart = createEstadoChart;
window.createTendenciaChart = createTendenciaChart;
window.createTipoErrorChart = createTipoErrorChart;

/**
 * Crear gráfico de barras horizontal genérico
 * @param {string} canvasId - ID del elemento canvas
 * @param {string[]} labels - Etiquetas (ej. nombres de establecimientos)
 * @param {number[]} values - Valores (ej. conteos)
 * @param {string} color - Color base para las barras
 */
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
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.x}`
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
}

/**
 * Crear gráfico de barras vertical genérico
 * @param {string} canvasId - ID del elemento canvas
 * @param {string[]} labels - Etiquetas
 * @param {number[]} values - Valores
 * @param {string} color - Color base para las barras
 */
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
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                },
                x: {
                    ticks: { maxRotation: 45, minRotation: 45 }
                }
            }
        }
    });
}

/**
 * Crear gráfico de barras horizontales para Dashboard - Distribución por Estado (D1)
 * Con data labels visibles sobre las barras
 */
function createDashboardEstadoChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    if (!data || !data.length) return null;

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
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    color: '#334155',
                    font: { weight: 'bold', size: 13 },
                    formatter: value => value
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    cornerRadius: 8,
                    callbacks: {
                        label: context => ` ${context.parsed.x} observaciones`
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
 * Crear gráfico de barras horizontales para Dashboard - Top Tipos de Error (D2)
 * Con data labels visibles, limitado a Top 10
 */
function createDashboardTiposChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    if (!data || !data.length) return null;

    // Limitar a Top 10
    const topData = data.slice(0, 10);
    const labels = topData.map(item => item.tipo_error);
    const values = topData.map(item => parseInt(item.total));
    const palette = [
        '#0ea5e9', '#6366f1', '#8b5cf6', '#ec4899', '#f97316',
        '#14b8a6', '#06b6d4', '#84cc16', '#f59e0b', '#ef4444'
    ];
    const colors = topData.map((_, i) => palette[i % palette.length]);

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
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    color: '#334155',
                    font: { weight: 'bold', size: 12 },
                    formatter: value => value
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: context => ` ${context.parsed.x} observaciones`
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
 * Crear gráfico de barras agrupadas para Dashboard - Observaciones por Mes comparativo (D3)
 * Sin data labels para no saturar. Soporta 1 o 2 años.
 * @param {Object} dataPorAnio - Objeto con clave año y valor array de {mes, total}
 */
function createDashboardMesesChart(canvasId, dataPorAnio) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const mesesOrden = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    const anios = Object.keys(dataPorAnio).sort();
    const colores = ['#0ea5e9', '#94a3b8']; // Año actual: azul, Año anterior: gris

    const datasets = anios.map((anio, idx) => {
        const mesData = dataPorAnio[anio] || [];
        const mesMap = {};
        mesData.forEach(m => { mesMap[m.mes] = parseInt(m.total); });
        const values = mesesOrden.map(m => mesMap[m] || 0);

        return {
            label: anio,
            data: values,
            backgroundColor: colores[idx % colores.length] + 'cc',
            borderColor: colores[idx % colores.length],
            borderWidth: 1,
            borderRadius: 4,
            barPercentage: anios.length > 1 ? 0.7 : 0.5
        };
    });

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: mesesOrden.map(m => m.substring(0, 3)),
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: anios.length > 1,
                    position: 'top',
                    labels: { usePointStyle: true, padding: 20, font: { size: 12 } }
                },
                datalabels: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: context => ` ${context.dataset.label}: ${context.parsed.y} observaciones`
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

window.createDashboardEstadoChart = createDashboardEstadoChart;
window.createDashboardTiposChart = createDashboardTiposChart;
window.createDashboardMesesChart = createDashboardMesesChart;
window.createBarHorizontal = createBarHorizontal;
window.createBarVertical = createBarVertical;
window.initializeCharts = initializeCharts;
