/**
 * Sistema de Observaciones REM - Graficos (Chart.js)
 *
 * Reglas del proyecto:
 *  - Los colores NUNCA se escriben aqui: se leen de assets/css/tokens.css.
 *    Paleta institucional DEIS Osorno via --chart-series-*, --rem-status-*.
 *  - Cada dataset guarda el TOKEN que le dio color (_remToken / _remTokens),
 *    para poder recolorearse al vuelo cuando cambia el tema.
 *  - Los estilos del boton de exportar viven en tabler-override.css.
 */

if (typeof ChartDataLabels !== 'undefined') {
    Chart.register(ChartDataLabels);
}

const NUM_CL = new Intl.NumberFormat('es-CL');

/* Orden fijo de estados: el mismo color queda siempre en la misma posicion,
   sin importar que devuelva la consulta. */
const ESTADO_ORDEN = ['pendiente', 'aprobado', 'rechazado', 'error', 'justificado'];
const TIPO_ERROR_ORDEN = ['ERROR', 'REVISAR', 'F/PLAZO', 'S/OBSERVACION'];

const MESES_ANIO = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const MESES_ABREV = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
    'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

/* === Token helpers === */
function chartTokenColor(name, fallback) {
    if (typeof getComputedStyle === 'undefined') return fallback;
    const v = getComputedStyle(document.documentElement).getPropertyValue(name);
    return (v && v.trim()) || fallback;
}

function chartBaseFont() {
    return chartTokenColor('--tblr-font-family-base', "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif");
}

/** Color de un estado del dominio, desde --rem-status-*. */
function estadoColor(estado) {
    return chartTokenColor('--rem-status-' + estado, chartTokenColor('--tblr-muted', '#64748b'));
}

function estadoToken(estado) {
    return '--rem-status-' + estado;
}

/** Devuelve n colores de la rampa categorica, ciclando si hace falta. */
function seriesPalette(n) {
    return seriesTokens(n).map(t => chartTokenColor(t, '#0B71B9'));
}

function seriesTokens(n) {
    const count = parseInt(chartTokenColor('--chart-series-count', '8'), 10) || 8;
    const out = [];
    for (let i = 0; i < n; i++) out.push('--chart-series-' + ((i % count) + 1));
    return out;
}

/** Parsea '#rgb', '#rrggbb' o 'rgb(r, g, b)' a [r,g,b]. */
function parseColor(color) {
    if (!color || typeof color !== 'string') return null;
    const c = color.trim();
    if (c.charAt(0) === '#') {
        let hex = c.slice(1);
        if (hex.length === 3) hex = hex.split('').map(x => x + x).join('');
        if (hex.length < 6) return null;
        return [parseInt(hex.substr(0, 2), 16), parseInt(hex.substr(2, 2), 16), parseInt(hex.substr(4, 2), 16)];
    }
    const m = c.match(/rgba?\(\s*(\d+)[,\s]+(\d+)[,\s]+(\d+)/i);
    return m ? [parseInt(m[1], 10), parseInt(m[2], 10), parseInt(m[3], 10)] : null;
}

function hexToRgba(color, alpha) {
    const rgb = parseColor(color);
    if (!rgb) return `rgba(148, 163, 184, ${alpha})`;
    return `rgba(${rgb[0]}, ${rgb[1]}, ${rgb[2]}, ${alpha})`;
}

/** Blanco o navy segun la luminancia del fondo, para que la etiqueta se lea. */
function readableTextOn(color) {
    const rgb = parseColor(color);
    if (!rgb) return chartTokenColor('--chart-datalabel-on-dark', '#ffffff');
    const lin = rgb.map(function (v) {
        const s = v / 255;
        return s <= 0.03928 ? s / 12.92 : Math.pow((s + 0.055) / 1.055, 2.4);
    });
    const L = 0.2126 * lin[0] + 0.7152 * lin[1] + 0.0722 * lin[2];
    return L > 0.45
        ? chartTokenColor('--chart-datalabel-on-light', '#002347')
        : chartTokenColor('--chart-datalabel-on-dark', '#ffffff');
}

/** Color efectivo de la barra/arco de un contexto de datalabels. */
function contextColor(ctx) {
    const bg = ctx.dataset.backgroundColor;
    if (Array.isArray(bg)) return bg[ctx.dataIndex];
    if (typeof bg === 'string') return bg;
    return chartTokenColor('--tblr-primary', '#0B71B9');
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
        point: chartTokenColor('--chart-point', '#0B71B9'),
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
        if (scale.ticks) scale.ticks.color = scale.ticks._remStrong ? theme.text : theme.muted;
        if (scale.title && scale.title.display) scale.title.color = theme.muted;
    });

    // Recolorear las SERIES, no solo los ejes: sin esto el tema oscuro dejaba
    // los datos con los colores del tema claro.
    chart.data.datasets.forEach(function (dataset) {
        if (Array.isArray(dataset._remTokens)) {
            dataset.backgroundColor = dataset._remTokens.map(t => chartTokenColor(t, '#0B71B9'));
        } else if (dataset._remToken) {
            const c = chartTokenColor(dataset._remToken, '#0B71B9');
            if (chart.config.type === 'line') {
                dataset.borderColor = c;
                dataset.pointBackgroundColor = c;
            } else {
                dataset.backgroundColor = c;
            }
        }
        if (chart.config.type === 'doughnut') dataset.borderColor = theme.pointBorder;
        if (Object.prototype.hasOwnProperty.call(dataset, 'pointBorderColor')) {
            dataset.pointBorderColor = theme.pointBorder;
        }
    });

    chart.update('none');
}

function refreshAllChartThemes() {
    applyChartDefaults();
    REM_CHARTS.forEach(refreshChartTheme);
}

window.addEventListener('rem:theme-changed', refreshAllChartThemes);

/* === Tooltips === */
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
            label: function (ctx) {
                const value = Number(ctx.parsed) || 0;
                // Number(): si el JSON trae los totales como texto, sumarlos
                // directamente concatenaba strings y el porcentaje salia mal.
                const total = ctx.dataset.data.reduce((a, b) => a + Number(b || 0), 0);
                if (total <= 0) return ` ${NUM_CL.format(value)} observaciones`;
                const pct = (value / total) * 100;
                const pctTxt = pct > 0 && pct < 1 ? '<1' : pct.toFixed(0);
                return ` ${NUM_CL.format(value)} observaciones (${pctTxt}%)`;
            }
        }
    };
}

/** Tooltip de valor. Muestra la etiqueta completa aunque el eje la trunque. */
function tooltipValor(unidad) {
    const u = unidad || 'observaciones';
    return {
        ...tooltipBase(false),
        callbacks: {
            title: function (items) {
                if (!items.length) return '';
                const chart = items[0].chart;
                const full = chart.$remFullLabels;
                return (full && full[items[0].dataIndex]) || items[0].label;
            },
            label: function (ctx) {
                const v = ctx.parsed.y !== undefined && ctx.chart.options.indexAxis !== 'y'
                    ? ctx.parsed.y : ctx.parsed.x;
                return ` ${NUM_CL.format(Number(v) || 0)} ${u}`;
            }
        }
    };
}

/* === Datalabels ===
   Antes se OCULTABA el valor cuando la barra era corta (<35px), justo donde
   mas se necesita. Ahora, si no cabe dentro, se dibuja fuera de la barra. */
function barCabeDentro(ctx) {
    const meta = ctx.chart.getDatasetMeta(ctx.datasetIndex);
    const bar = meta.data[ctx.dataIndex];
    if (!bar) return false;
    const horizontal = ctx.chart.options.indexAxis === 'y';
    const px = horizontal ? Math.abs(bar.x - bar.base) : Math.abs(bar.y - bar.base);
    return px > 38;
}

const datalabelsBar = {
    anchor: 'end',
    align: ctx => (barCabeDentro(ctx) ? 'end' : 'right'),
    offset: ctx => (barCabeDentro(ctx) ? -8 : 4),
    color: ctx => (barCabeDentro(ctx)
        ? readableTextOn(contextColor(ctx))
        : chartTokenColor('--tblr-body-color', '#1e293b')),
    font: { weight: '700', size: 12 },
    formatter: v => NUM_CL.format(Number(v) || 0),
    display: ctx => Number(ctx.dataset.data[ctx.dataIndex]) > 0
};

const datalabelsBarVertical = {
    anchor: 'end',
    align: ctx => (barCabeDentro(ctx) ? 'end' : 'top'),
    offset: ctx => (barCabeDentro(ctx) ? -6 : 2),
    color: ctx => (barCabeDentro(ctx)
        ? readableTextOn(contextColor(ctx))
        : chartTokenColor('--tblr-body-color', '#1e293b')),
    font: { weight: '700', size: 11 },
    formatter: v => NUM_CL.format(Number(v) || 0),
    display: ctx => Number(ctx.dataset.data[ctx.dataIndex]) > 0
};

const ANIM_CONFIG = {
    duration: 600,
    easing: 'easeOutQuart',
    delay: function (ctx) {
        if (ctx.dataIndex === undefined) return 0;
        const count = ctx.dataset.data.length;
        return count > 30 ? 0 : ctx.dataIndex * 80;
    }
};

/* === Total al centro del doughnut === */
const remDoughnutCenter = {
    id: 'remDoughnutCenter',
    afterDatasetsDraw(chart) {
        const opts = chart.options.plugins && chart.options.plugins.remCenter;
        if (!opts || opts.display === false) return;
        const area = chart.chartArea;
        if (!area) return;

        const total = chart.data.datasets[0].data.reduce((a, b) => a + Number(b || 0), 0);
        const cx = (area.left + area.right) / 2;
        const cy = (area.top + area.bottom) / 2;
        const ctx = chart.ctx;
        const font = chartBaseFont();

        ctx.save();
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillStyle = chartTokenColor('--tblr-heading-color', '#0f172a');
        ctx.font = '700 26px ' + font;
        ctx.fillText(NUM_CL.format(total), cx, cy - 8);
        ctx.fillStyle = chartTokenColor('--chart-tick', '#64748b');
        ctx.font = '500 11px ' + font;
        ctx.fillText(opts.label || 'observaciones', cx, cy + 14);
        ctx.restore();
    }
};
if (typeof Chart !== 'undefined') Chart.register(remDoughnutCenter);

/* === Exportacion a imagen === */
function addExportButton(chart, chartId) {
    const container = chart.canvas && chart.canvas.parentElement;
    if (!container) return;
    container.querySelector('.chart-export-btn')?.remove();
    container.classList.add('chart-canvas-frame');

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'chart-export-btn';
    btn.innerHTML = '<i class="ti ti-download" aria-hidden="true"></i>';
    btn.title = 'Descargar grafico como imagen PNG';
    btn.setAttribute('aria-label', 'Descargar grafico como imagen PNG');
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        downloadChartPNG(chart, chartId, container);
    });
    container.appendChild(btn);
}

/**
 * Genera el PNG sobre un lienzo propio con FONDO SOLIDO, titulo y pie.
 * Chart.js exporta con fondo transparente, lo que hacia ilegible la imagen
 * al pegarla en Word o PowerPoint.
 */
function downloadChartPNG(chart, chartId, container) {
    const SCALE = 2;      // nitidez al ampliar / imprimir
    const PAD = 24;
    const HEAD = 60;
    const FOOT = 30;

    const w = chart.width;   // Chart.js entrega px CSS
    const h = chart.height;
    const ds = container.dataset || {};
    const titulo = ds.exportTitle || document.title || 'Grafico';
    const contexto = ds.exportContext || '';

    const out = document.createElement('canvas');
    out.width = (w + PAD * 2) * SCALE;
    out.height = (h + HEAD + FOOT) * SCALE;

    const c = out.getContext('2d');
    c.scale(SCALE, SCALE);

    const totalW = w + PAD * 2;
    const totalH = h + HEAD + FOOT;
    const font = chartBaseFont();

    c.fillStyle = chartTokenColor('--chart-export-canvas-bg', '#ffffff');
    c.fillRect(0, 0, totalW, totalH);

    c.textBaseline = 'alphabetic';
    c.fillStyle = chartTokenColor('--chart-export-title', '#003366');
    c.font = '600 16px ' + font;
    c.fillText(titulo, PAD, 28);

    if (contexto) {
        c.fillStyle = chartTokenColor('--chart-export-meta', '#475569');
        c.font = '12px ' + font;
        c.fillText(contexto, PAD, 47);
    }

    c.drawImage(chart.canvas, PAD, HEAD, w, h);

    c.fillStyle = chartTokenColor('--chart-export-meta', '#475569');
    c.font = '11px ' + font;
    c.fillText(
        'Servicio de Salud Osorno - Sistema de Observaciones REM - ' + new Date().toLocaleDateString('es-CL'),
        PAD,
        HEAD + h + 20
    );

    const stamp = new Date().toISOString().slice(0, 10);
    const slug = (ds.exportSlug || chartId || 'grafico').replace(/[^a-zA-Z0-9_-]/g, '_');
    const link = document.createElement('a');
    link.href = out.toDataURL('image/png');
    link.download = `REM_${slug}_${stamp}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

/* === Escalas ===
   stepSize:1 pedia un tick por unidad: con 300 errores el eje quedaba
   ilegible. precision:0 mantiene enteros y maxTicksLimit acota la cantidad. */
function truncarEtiqueta(txt, max) {
    const s = String(txt == null ? '' : txt);
    return s.length > max ? s.slice(0, max - 1) + '…' : s;
}

/**
 * Tamano de letra del eje de categorias segun el nombre mas largo del conjunto:
 * los nombres de establecimiento se muestran completos en una linea, asi que la
 * letra baja un punto cuando el texto es largo en vez de cortar el nombre.
 */
function fuenteEjeCategoria(labels) {
    const maxLen = (labels || []).reduce((m, l) => Math.max(m, String(l == null ? '' : l).length), 0);
    if (maxLen > 34) return 10;
    if (maxLen > 26) return 11;
    return 12;
}

/**
 * @param {string} tituloEje titulo del eje de valores
 * @param {object} opts { labelFontSize, maxLabelChars, maxLabelWidthRatio }
 */
function scaleHorizontal(tituloEje, opts) {
    const theme = getChartTheme();
    const o = opts || {};
    const labelFontSize = o.labelFontSize || 12;
    // Corte de seguridad solo para nombres extremos; el caso normal se muestra completo.
    const maxLabelChars = o.maxLabelChars || 60;
    const maxWidthRatio = o.maxLabelWidthRatio || 0.45;

    return {
        x: {
            beginAtZero: true,
            // grace deja aire a la derecha para que el valor de la barra mas larga,
            // cuando se dibuja fuera de la barra, no quede cortado contra el borde.
            grace: '5%',
            grid: { display: true, drawBorder: false, color: theme.grid },
            ticks: { precision: 0, maxTicksLimit: 8, font: { size: 11 }, color: theme.muted },
            title: {
                display: Boolean(tituloEje),
                text: tituloEje || '',
                color: theme.muted,
                font: { size: 11, weight: '500' }
            }
        },
        y: {
            grid: { display: false },
            // Acota la columna de nombres para que las barras conserven espacio util.
            afterFit: function (scale) {
                const tope = scale.chart.width * maxWidthRatio;
                if (scale.width > tope) scale.width = tope;
            },
            ticks: {
                autoSkip: false,
                font: { size: labelFontSize, weight: '600' },
                color: theme.text,
                _remStrong: true,
                padding: 6,
                callback: function (value) {
                    return truncarEtiqueta(this.getLabelForValue(value), maxLabelChars);
                }
            }
        }
    };
}

function scaleVertical(tituloEje) {
    const theme = getChartTheme();
    return {
        y: {
            beginAtZero: true,
            grid: { drawBorder: false, color: theme.grid },
            ticks: { precision: 0, maxTicksLimit: 8, font: { size: 11 }, color: theme.muted },
            title: {
                display: Boolean(tituloEje),
                text: tituloEje || '',
                color: theme.muted,
                font: { size: 11, weight: '500' }
            }
        },
        x: {
            grid: { display: false },
            ticks: { font: { size: 11 }, color: theme.muted }
        }
    };
}

/** true si no hay nada que graficar (sin filas o todo en cero). */
function sinDatos(values) {
    return !values || !values.length || values.every(v => !Number(v));
}

// ============================================================
// Helpers para manejo de datos
// ============================================================

/** Agrupa los elementos después del Top N en una categoría "Otros". */
function aplicarTopNOtros(labels, values, topN) {
    if (!labels || !values || labels.length <= topN) {
        return { labels, values };
    }
    const pares = labels.map((l, i) => ({ label: l, value: values[i] || 0 }));
    const top = pares.slice(0, topN);
    const resto = pares.slice(topN);
    const otrosValue = resto.reduce((s, p) => s + Number(p.value), 0);

    const newLabels = top.map(p => p.label);
    const newValues = top.map(p => p.value);
    if (otrosValue > 0) {
        newLabels.push(`Otros (${resto.length})`);
        newValues.push(otrosValue);
    }
    return { labels: newLabels, values: newValues };
}

// ============================================================
// GRAFICOS DEL DASHBOARD
// ============================================================

function createEstadoChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || !data || !data.length) return null;
    const theme = getChartTheme();

    // Orden fijo del dominio para que el color no baile entre consultas
    const ordenados = data.slice().sort(function (a, b) {
        const ia = ESTADO_ORDEN.indexOf(a.estado_actual);
        const ib = ESTADO_ORDEN.indexOf(b.estado_actual);
        return (ia === -1 ? 99 : ia) - (ib === -1 ? 99 : ib);
    });

    const labels = ordenados.map(i => i.estado_actual.charAt(0).toUpperCase() + i.estado_actual.slice(1));
    const values = ordenados.map(i => parseInt(i.total, 10) || 0);
    const tokens = ordenados.map(i => estadoToken(i.estado_actual));
    const colors = ordenados.map(i => estadoColor(i.estado_actual));
    if (sinDatos(values)) return null;

    const baseFont = chartBaseFont();

    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors,
                _remTokens: tokens,
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
                remCenter: { display: true, label: 'observaciones' },
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        boxHeight: 10,
                        padding: 14,
                        font: { size: 12, family: baseFont },
                        usePointStyle: true,
                        color: theme.legend,
                        // N y % en la leyenda: los segmentos chicos quedan
                        // fuera del datalabel, pero no deben ser invisibles.
                        generateLabels: function (chart2) {
                            const ds = chart2.data.datasets[0];
                            const total = ds.data.reduce((a, b) => a + Number(b || 0), 0);
                            return chart2.data.labels.map(function (label, i) {
                                const v = Number(ds.data[i]) || 0;
                                const pct = total > 0 ? (v / total) * 100 : 0;
                                const pctTxt = pct > 0 && pct < 1 ? '<1' : pct.toFixed(0);
                                return {
                                    text: `${label} - ${NUM_CL.format(v)} (${pctTxt}%)`,
                                    fillStyle: ds.backgroundColor[i],
                                    strokeStyle: ds.backgroundColor[i],
                                    lineWidth: 0,
                                    pointStyle: 'circle',
                                    hidden: false,
                                    index: i
                                };
                            });
                        }
                    }
                },
                tooltip: tooltipPct(),
                datalabels: {
                    color: ctx2 => readableTextOn(contextColor(ctx2)),
                    font: { weight: '700', size: 11, family: baseFont },
                    formatter: (v, ctx2) => {
                        const total = ctx2.dataset.data.reduce((a, b) => a + Number(b || 0), 0);
                        return total > 0 ? ((Number(v) / total) * 100).toFixed(0) + '%' : '';
                    },
                    display: function (ctx2) {
                        const total = ctx2.dataset.data.reduce((a, b) => a + Number(b || 0), 0);
                        if (!total) return false;
                        return (Number(ctx2.dataset.data[ctx2.dataIndex]) / total) * 100 > 8;
                    }
                }
            }
        }
    });

    addExportButton(chart, canvasId);
    return registerRemChart(chart, canvasId);
}

function createTendenciaChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx || !data) return null;

    // Se rellenan los 12 meses. Un mes sin registros va como null (hueco),
    // no como 0: antes la linea unia Marzo con Agosto como si fueran
    // consecutivos, dando una continuidad falsa.
    const porMes = {};
    data.forEach(function (item) {
        porMes[String(item.mes).trim().toLowerCase()] = parseInt(item.total, 10) || 0;
    });
    const values = MESES_ANIO.map(m => {
        const k = m.toLowerCase();
        return Object.prototype.hasOwnProperty.call(porMes, k) ? porMes[k] : null;
    });
    if (sinDatos(values.filter(v => v !== null))) return null;

    const token = '--tblr-primary';
    const theme = getChartTheme();

    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: MESES_ABREV,
            datasets: [{
                label: 'Observaciones',
                data: values,
                _remToken: token,
                borderColor: chartTokenColor(token, '#0B71B9'),
                backgroundColor: function (ctx2) {
                    if (!ctx2.chart || !ctx2.chart.ctx) return chartTokenColor(token, '#0B71B9');
                    // Se lee el token AQUI (no en un closure) para que el
                    // degradado siga al tema cuando cambia.
                    const col = chartTokenColor(token, '#0B71B9');
                    const c = ctx2.chart.ctx;
                    const grad = c.createLinearGradient(0, 0, 0, ctx2.chart.height || 280);
                    grad.addColorStop(0, hexToRgba(col, 0.3));
                    grad.addColorStop(1, hexToRgba(col, 0.02));
                    return grad;
                },
                borderWidth: 2,
                fill: true,
                spanGaps: false,
                tension: 0.3,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: chartTokenColor(token, '#0B71B9'),
                pointBorderColor: theme.pointBorder,
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { ...ANIM_CONFIG, duration: 800 },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    ...tooltipValor('observaciones'),
                    callbacks: {
                        ...tooltipValor('observaciones').callbacks,
                        title: items => (items.length ? MESES_ANIO[items[0].dataIndex] : '')
                    }
                },
                datalabels: { display: false }
            },
            scales: scaleVertical('N.º de observaciones')
        }
    });

    addExportButton(chart, canvasId);
    return registerRemChart(chart, canvasId);
}

// ============================================================
// GRAFICOS DE REPORTES (genericos)
// ============================================================

/**
 * @param {string} colorOrToken color resuelto o nombre de token (--chart-series-1)
 * @param {object} opts { tituloEje, fullLabels, orden }
 */
function createBarHorizontal(canvasId, labels, values, colorOrToken, opts) {
    const el = document.getElementById(canvasId);
    if (!el || sinDatos(values)) return null;
    const o = opts || {};

    const esToken = typeof colorOrToken === 'string' && colorOrToken.indexOf('--') === 0;
    const token = esToken ? colorOrToken : null;
    const color = esToken ? chartTokenColor(colorOrToken, '#0B71B9') : (colorOrToken || chartTokenColor('--chart-series-1', '#0B71B9'));

    const chart = new Chart(el, {
        type: 'bar',
        data: {
            // Nombre completo: antes se cortaba a 30 caracteres y los establecimientos
            // largos quedaban indistinguibles entre si.
            labels: labels.slice(),
            datasets: [{
                label: 'Cantidad',
                data: values,
                backgroundColor: color,
                _remToken: token,
                borderWidth: 0,
                borderRadius: 4,
                barPercentage: 0.82,
                categoryPercentage: 0.86
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            // Espacio a la derecha para el valor que se dibuja fuera de la barra.
            layout: { padding: { right: 14 } },
            animation: { ...ANIM_CONFIG },
            plugins: {
                legend: { display: false },
                tooltip: tooltipValor(o.unidad),
                datalabels: { ...datalabelsBar }
            },
            scales: scaleHorizontal(o.tituloEje || 'N.º de observaciones', {
                labelFontSize: fuenteEjeCategoria(labels)
            })
        }
    });

    chart.$remFullLabels = labels.slice();
    addExportButton(chart, canvasId);
    return registerRemChart(chart, canvasId);
}

function createBarVertical(canvasId, labels, values, colorOrToken, opts) {
    const el = document.getElementById(canvasId);
    if (!el || sinDatos(values)) return null;
    const o = opts || {};

    const esToken = typeof colorOrToken === 'string' && colorOrToken.indexOf('--') === 0;
    const token = esToken ? colorOrToken : null;
    const color = esToken ? chartTokenColor(colorOrToken, '#0B71B9') : (colorOrToken || chartTokenColor('--chart-series-1', '#0B71B9'));

    const base = scaleVertical(o.tituloEje || 'N.º de observaciones');

    const chart = new Chart(el, {
        type: 'bar',
        data: {
            labels: labels.map(l => truncarEtiqueta(l, 22)),
            datasets: [{
                label: 'Cantidad',
                data: values,
                backgroundColor: color,
                _remToken: token,
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
                tooltip: tooltipValor(o.unidad),
                datalabels: { ...datalabelsBarVertical }
            },
            scales: {
                y: base.y,
                x: { ...base.x, ticks: { ...base.x.ticks, maxRotation: 45, minRotation: 45 } }
            }
        }
    });

    chart.$remFullLabels = labels.slice();
    addExportButton(chart, canvasId);
    return registerRemChart(chart, canvasId);
}

// ============================================================
// BARRAS APILADAS (stacked horizontal)
// ============================================================

/**
 * @param {Array} datasets [{ label, data, color|token }]
 * @param {object} opts { unidad, tituloEje, normalize }
 * Si opts.normalize = true, normaliza cada barra a 100% y almacena valores absolutos en _remAbsoluteData
 */
function renderStackedBarChart(canvasId, labels, datasets, opts) {
    const el = document.getElementById(canvasId);
    if (!el || !labels.length) return null;
    const theme = getChartTheme();
    const o = opts || {};
    const unidad = o.unidad || 'meses';
    const normalize = o.normalize === true;

    // Precalcular totales por fila si se va a normalizar
    let rowTotals = null;
    if (normalize) {
        rowTotals = new Array(labels.length).fill(0);
        datasets.forEach(ds => {
            ds.data.forEach((val, idx) => {
                rowTotals[idx] += Number(val) || 0;
            });
        });
    }

    const dss = datasets.map(function (d, i) {
        const token = d.token || seriesTokens(datasets.length)[i];
        let data = d.data.slice();
        let absoluteData = null;

        if (normalize && rowTotals) {
            absoluteData = data.slice();
            data = data.map((val, idx) => {
                const total = rowTotals[idx];
                return total > 0 ? (Number(val) / total) * 100 : 0;
            });
        }

        return {
            label: d.label,
            data: data,
            _remAbsoluteData: absoluteData,
            _remNormalize: normalize,
            backgroundColor: d.color || chartTokenColor(token, '#0B71B9'),
            _remToken: d.color ? null : token,
            borderWidth: 0,
            borderRadius: 4
        };
    });

    const chart = new Chart(el, {
        type: 'bar',
        data: { labels: labels.map(l => truncarEtiqueta(l, 30)), datasets: dss },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: { ...ANIM_CONFIG },
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 16, font: { size: 12, family: chartBaseFont() }, color: theme.legend }
                },
                tooltip: {
                    ...tooltipBase(true),
                    mode: 'index',
                    callbacks: {
                        title: function (items) {
                            if (!items.length) return '';
                            const full = items[0].chart.$remFullLabels;
                            return (full && full[items[0].dataIndex]) || items[0].label;
                        },
                        label: function (ctx) {
                            const idx = ctx.dataIndex;
                            const ds = ctx.dataset;
                            const displayValue = Number(ctx.parsed.x) || 0;
                            let label = ` ${ctx.dataset.label}: ${NUM_CL.format(displayValue)}`;

                            if (ds._remNormalize && ds._remAbsoluteData && ds._remAbsoluteData[idx] !== undefined) {
                                const absolute = ds._remAbsoluteData[idx];
                                label += ` (${NUM_CL.format(absolute)} ${unidad})`;
                            } else if (!ds._remNormalize) {
                                label += ` ${unidad}`;
                            }
                            return label;
                        },
                        afterBody: function (items) {
                            if (!items.length) return '';
                            const idx = items[0].dataIndex;
                            const isNormalized = items[0].chart.data.datasets[0]?._remNormalize;

                            if (isNormalized) {
                                const absoluteTotal = items[0].chart.data.datasets
                                    .reduce((s, ds) => {
                                        if (ds._remAbsoluteData && ds._remAbsoluteData[idx] !== undefined) {
                                            return s + (Number(ds._remAbsoluteData[idx]) || 0);
                                        }
                                        return s;
                                    }, 0);
                                return `Total: ${NUM_CL.format(absoluteTotal)} ${unidad}`;
                            }
                            const total = items[0].chart.data.datasets
                                .reduce((s, ds) => s + (Number(ds.data[idx]) || 0), 0);
                            return `Total ${unidad} con datos: ${NUM_CL.format(total)}`;
                        }
                    }
                },
                datalabels: { display: false }
            },
            scales: {
                x: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: { precision: 0, maxTicksLimit: 8, font: { size: 11 }, color: theme.muted },
                    grid: { display: true, drawBorder: false, color: theme.grid },
                    title: {
                        display: Boolean(o.tituloEje),
                        text: o.tituloEje || '',
                        color: theme.muted,
                        font: { size: 11, weight: '500' }
                    }
                },
                y: {
                    stacked: true,
                    grid: { display: false },
                    ticks: {
                        autoSkip: false,
                        font: { size: 12, weight: '600' },
                        color: theme.text,
                        _remStrong: true
                    }
                }
            }
        }
    });

    chart.$remFullLabels = labels.slice();
    addExportButton(chart, canvasId);
    return registerRemChart(chart, canvasId);
}

/**
 * Renderiza un stacked bar horizontal pivotando datos planos {grupo, categoria, total}
 * Agrupa por 'grupo' (eje Y) y pivota 'categoria' en series (eje X apilado).
 * @param {string} canvasId
 * @param {Array} flatData [{ grupo, categoria, total }, ...]
 * @param {Array} categorias orden fijo de categorías a mostrar (ej: ['ERROR', 'REVISAR', 'F/PLAZO', 'S/OBSERVACION'])
 * @param {Array} colorTokens array de tokens alineados con categorias (ej: ['--chart-series-1', ...])
 * @param {object} opts { topN, unidad }
 */
function createStackedBarByCategory(canvasId, flatData, categorias, colorTokens, opts) {
    const el = document.getElementById(canvasId);
    if (!el || !flatData || !flatData.length) return null;
    const o = opts || {};
    const topN = o.topN || 12;
    const unidad = o.unidad || 'observaciones';

    // Pivotar: agrupar por grupo, luego contar por categoria
    const grupos = {};
    flatData.forEach(row => {
        const g = row.grupo || 'Sin grupo';
        if (!grupos[g]) grupos[g] = {};
        const c = String(row.categoria || 'Otro').trim();
        grupos[g][c] = (grupos[g][c] || 0) + Number(row.total || 0);
    });

    // Ordenar grupos por total descendente
    const gruposOrdenados = Object.entries(grupos)
        .map(([nombre, conteos]) => {
            const total = Object.values(conteos).reduce((s, v) => s + Number(v), 0);
            return { nombre, conteos, total };
        })
        .sort((a, b) => b.total - a.total);

    // Aplicar Top N + Otros
    let labels = gruposOrdenados.map(g => g.nombre);
    let grupoData = gruposOrdenados;

    if (gruposOrdenados.length > topN) {
        const top = gruposOrdenados.slice(0, topN);
        const resto = gruposOrdenados.slice(topN);
        const otrosConteos = {};
        categorias.forEach(cat => { otrosConteos[cat] = 0; });
        resto.forEach(g => {
            categorias.forEach(cat => {
                otrosConteos[cat] += g.conteos[cat] || 0;
            });
        });
        grupoData = [...top, { nombre: `Otros (${resto.length})`, conteos: otrosConteos, total: resto.reduce((s, g) => s + g.total, 0) }];
        labels = grupoData.map(g => g.nombre);
    }

    // Construir datasets por categoria
    const datasets = categorias.map((cat, i) => {
        const token = colorTokens[i] || seriesTokens(i + 1)[0];
        return {
            label: cat,
            data: grupoData.map(g => Number(g.conteos[cat] || 0)),
            backgroundColor: chartTokenColor(token, '#0B71B9'),
            _remToken: token,
            borderWidth: 0,
            borderRadius: 4
        };
    });

    const theme = getChartTheme();
    const baseFont = chartBaseFont();

    const chart = new Chart(el, {
        type: 'bar',
        data: { labels: labels.map(l => truncarEtiqueta(l, 30)), datasets },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            animation: { ...ANIM_CONFIG },
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 16, font: { size: 12, family: baseFont }, color: theme.legend }
                },
                tooltip: {
                    ...tooltipBase(true),
                    mode: 'index',
                    callbacks: {
                        title: function (items) {
                            if (!items.length) return '';
                            const idx = items[0].dataIndex;
                            return labels[idx];
                        },
                        label: function (ctx) {
                            return ` ${ctx.dataset.label}: ${NUM_CL.format(Number(ctx.parsed.x) || 0)}`;
                        },
                        afterBody: function (items) {
                            if (!items.length) return '';
                            const idx = items[0].dataIndex;
                            const total = datasets.reduce((s, ds) => s + (Number(ds.data[idx]) || 0), 0);
                            return `Total: ${NUM_CL.format(total)} ${unidad}`;
                        }
                    }
                },
                datalabels: { display: false }
            },
            scales: {
                x: {
                    stacked: true,
                    beginAtZero: true,
                    ticks: { precision: 0, maxTicksLimit: 8, font: { size: 11 }, color: theme.muted },
                    grid: { display: true, drawBorder: false, color: theme.grid }
                },
                y: {
                    stacked: true,
                    grid: { display: false },
                    ticks: { autoSkip: false, font: { size: 12, weight: '600' }, color: theme.text, _remStrong: true }
                }
            }
        }
    });

    chart.$remFullLabels = labels.slice();
    addExportButton(chart, canvasId);
    return registerRemChart(chart, canvasId);
}

// ============================================================
// INICIALIZACION
// ============================================================

function initializeCharts(statsData) {
    const charts = {};

    if (statsData.por_estado && statsData.por_estado.length > 0) {
        charts.estado = createEstadoChart('chartEstado', statsData.por_estado);
    }
    if (statsData.por_mes && statsData.por_mes.length > 0) {
        charts.tendencia = createTendenciaChart('chartTendencia', statsData.por_mes);
    }

    return charts;
}

window.REMCharts = REM_CHARTS;
window.refreshAllChartThemes = refreshAllChartThemes;
window.chartTokenColor = chartTokenColor;
window.estadoColor = estadoColor;
window.seriesPalette = seriesPalette;
window.createEstadoChart = createEstadoChart;
window.createTendenciaChart = createTendenciaChart;
window.createBarHorizontal = createBarHorizontal;
window.createBarVertical = createBarVertical;
window.renderStackedBarChart = renderStackedBarChart;
window.createStackedBarByCategory = createStackedBarByCategory;
window.aplicarTopNOtros = aplicarTopNOtros;
window.initializeCharts = initializeCharts;
