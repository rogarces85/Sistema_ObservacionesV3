<?php
/**
 * Vista de Reportes de Errores REM
 * 5 reportes modulares con navegación por pestañas (nav-tabs)
 */

require_once 'models/Location.php';

$currentYear = $_SESSION['year'] ?? date('Y');
$userRole = $_SESSION['rol'];

// Obtener comunas para filtro
$locationModel = new Location();
$comunas = $locationModel->getComunas();

$mesesList = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
?>

<div class="d-flex flex-column gap-3 rem-fade-in">

                <!-- Header -->
                <header class="page-header">
                    <div>
                        <h1 class="page-title">
                            <i class="ti ti-chart-bar me-2 text-primary"></i>Reportes de Errores REM
                        </h1>
                        <p class="page-subtitle">Análisis de errores por establecimiento, plazo, validador, serie y hoja</p>
                    </div>
                    <div class="page-actions">
                        <span class="badge badge-soft-primary">
                            <i class="ti ti-calendar-event me-1"></i><?php echo htmlspecialchars($currentYear); ?>
                        </span>
                    </div>
                </header>

                <!-- Filtros -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h3 class="card-title mb-0"><i class="ti ti-filter me-2 text-primary"></i>Filtros</h3>
                            <span class="text-secondary small">Selecciona los criterios y aplica el reporte</span>
                        </div>
                        <div class="row g-3">
                                <div class="col-lg">
                                    <label class="form-label">Año</label>
                                    <select id="filterYear" class="form-select">
                                        <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                                            <option value="<?php echo $y; ?>" <?php echo $y == $currentYear ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="col-lg">
                                    <label class="form-label">Trimestre</label>
                                    <select id="filterTrimestre" class="form-select">
                                        <option value="">Todos</option>
                                        <option value="1">1er Trimestre</option>
                                        <option value="2">2do Trimestre</option>
                                        <option value="3">3er Trimestre</option>
                                        <option value="4">4to Trimestre</option>
                                    </select>
                                </div>

                                <div class="col-lg">
                                    <label class="form-label">Mes</label>
                                    <select id="filterMes" class="form-select">
                                        <option value="">Todos</option>
                                        <?php foreach ($mesesList as $m): ?>
                                            <option value="<?php echo $m; ?>"><?php echo $m; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-lg">
                                    <label class="form-label">Comuna</label>
                                    <select id="filterComuna" class="form-select">
                                        <option value="">Todas</option>
                                        <?php foreach ($comunas as $comuna): ?>
                                            <option value="<?php echo $comuna['id']; ?>"><?php echo htmlspecialchars($comuna['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-lg">
                                    <label class="form-label">Establecimiento</label>
                                    <select id="filterEstablecimiento" class="form-select" disabled>
                                        <option value="">Todos</option>
                                    </select>
                                </div>

                            <div class="col-12">
                                    <div class="btn-list">
                                        <button id="btnApplyFilters" class="btn btn-primary">
                                            Aplicar Filtros
                                        </button>
                                        <button id="btnClearFilters" class="btn btn-outline-secondary">
                                            Limpiar
                                        </button>
                                        <button id="btnExportExcel" class="btn btn-outline-success" type="button">
                                            <i class="ti ti-file-spreadsheet me-1"></i>Exportar Excel
                                        </button>
                                        <button id="btnExportPdf" class="btn btn-outline-danger" type="button">
                                            <i class="ti ti-file-type-pdf me-1"></i>PDF Detallado
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Nav Tabs + Panels -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#tab-errores-est" type="button">
                                        Total Errores
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-plazos" type="button">
                                        Plazos Entrega
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-validador" type="button">
                                        Uso Validador
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-serie" type="button">
                                        Errores por Serie
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#tab-hoja" type="button">
                                        Errores por Hoja
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body tab-content">
                            <!-- Tab 1: Total Errores -->
                            <div id="tab-errores-est" class="tab-pane active" role="tabpanel">
                                <div class="report-chart-frame" id="chart1Container" data-export-title="Errores por Establecimiento" data-export-slug="errores_establecimiento">
                                    <canvas id="chartErroresEst"></canvas>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-vcenter card-table table-hover">
                                        <thead><tr><th>Establecimiento</th><th class="text-end">Errores</th></tr></thead>
                                        <tbody id="tableErroresEst"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 2: Plazos Entrega (Matriz) -->
                            <div id="tab-plazos" class="tab-pane" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-vcenter card-table">
                                        <thead><tr><th>Establecimiento</th><th class="text-center">Plazo de Entrega</th></tr></thead>
                                        <tbody id="tablePlazoMatriz"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 3: Uso Validador (Matriz) -->
                            <div id="tab-validador" class="tab-pane" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-vcenter card-table">
                                        <thead><tr><th>Establecimiento</th><th class="text-center">Uso Validador</th></tr></thead>
                                        <tbody id="tableValidadorMatriz"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 4: Errores por Serie -->
                            <div id="tab-serie" class="tab-pane" role="tabpanel">
                                <div class="report-chart-frame" id="chart4Container" data-export-title="Errores por Serie REM" data-export-slug="errores_serie">
                                    <canvas id="chartErroresSerie"></canvas>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-vcenter card-table table-hover">
                                        <thead><tr><th>Serie REM</th><th class="text-end">Errores</th></tr></thead>
                                        <tbody id="tableErroresSerie"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 5: Errores por Hoja -->
                            <div id="tab-hoja" class="tab-pane" role="tabpanel">
                                <div class="report-chart-frame" id="chart5Container" data-export-title="Errores por Hoja REM" data-export-slug="errores_hoja">
                                    <canvas id="chartErroresHoja"></canvas>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-vcenter card-table table-hover">
                                        <thead><tr><th>Hoja REM</th><th class="text-end">Errores</th></tr></thead>
                                        <tbody id="tableErroresHoja"></tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
let errorCharts = {};
let tabDataLoaded = {};
let cachedData = null;
const mesesList = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
const TRIMESTRES = {
    '1': ['Enero','Febrero','Marzo'],
    '2': ['Abril','Mayo','Junio'],
    '3': ['Julio','Agosto','Septiembre'],
    '4': ['Octubre','Noviembre','Diciembre']
};

// Fallback: definir aplicarTopNOtros si no viene de charts.js
if (typeof aplicarTopNOtros === 'undefined') {
    window.aplicarTopNOtros = function(labels, values, topN) {
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
    };
}

// Fallback: definir createStackedBarByCategory si no viene de charts.js
if (typeof createStackedBarByCategory === 'undefined') {
    console.warn('createStackedBarByCategory no está definida en charts.js');
}

// colorToken apunta a tokens de tokens.css: nunca hex cableado aqui.
const TAB_CONFIG = {
    'tab-errores-est': { canvas: 'chartErroresEst', container: 'chart1Container', table: 'tableErroresEst', orientation: 'horizontal', colorToken: '--rem-status-error', label: 'Errores', key: 'errores_establecimiento' }
};

const EXPORT_REPORT_TYPES = {
    'tab-errores-est': 'errores_establecimiento',
    'tab-plazos': 'fuera_plazo_establecimiento',
    'tab-validador': 'validador_establecimiento',
    'tab-serie': 'serie_detalle',
    'tab-hoja': 'hoja_detalle'
};

// ============================================
// Tab Switching (Bootstrap)
// ============================================

function onTabShown(tabId) {
    location.hash = tabId;

    if (tabDataLoaded[tabId]) return;

    if (tabId === 'tab-plazos') {
        loadPlazoAgregado();
    } else if (tabId === 'tab-validador') {
        loadValidadorAgregado();
    } else if (tabId === 'tab-serie' || tabId === 'tab-hoja') {
        if (cachedData) {
            renderSerieHojaChart(tabId, cachedData);
            tabDataLoaded[tabId] = true;
        }
    } else if (cachedData) {
        renderTabChart(tabId, cachedData);
        tabDataLoaded[tabId] = true;
    }
}

// ============================================
// Data Loading
// ============================================

async function loadEstablecimientos() {
    const comunaId = document.getElementById('filterComuna').value;
    const select = document.getElementById('filterEstablecimiento');

    select.innerHTML = '<option value="">Todos</option>';
    select.disabled = !comunaId;

    if (comunaId) {
    try {
        const response = await fetch(`api/locations.php?action=establecimientos&comuna_id=${comunaId}`);
        const data = await parseJsonResponse(response);
        if (data.success) {
            data.data.forEach(est => {
                const option = document.createElement('option');
                option.value = est.id;
                option.textContent = est.nombre_corto || est.nombre;
                select.appendChild(option);
            });
        } else {
            showError(data.message || 'No se pudieron cargar los establecimientos');
        }
    } catch (error) {
        showError(error.message || 'No se pudieron cargar los establecimientos de la comuna seleccionada');
    }
    }
}

function getMesesFiltro() {
    const trimestre = document.getElementById('filterTrimestre').value;
    if (trimestre && TRIMESTRES[trimestre]) {
        return TRIMESTRES[trimestre];
    }
    const mes = document.getElementById('filterMes').value;
    return mes ? [mes] : [];
}

function appendMeses(url, meses) {
    meses.forEach(m => { url += `&meses[]=${encodeURIComponent(m)}`; });
    return url;
}

async function loadErrorReports() {
    const year = document.getElementById('filterYear').value;
    const meses = getMesesFiltro();
    const comunaId = document.getElementById('filterComuna').value;
    const establecimientoId = document.getElementById('filterEstablecimiento').value;

    let url = `api/reports.php?report=error-reports&year=${year}`;
    url = appendMeses(url, meses);
    if (comunaId) url += `&comuna_ids[]=${comunaId}`;
    if (establecimientoId) url += `&establecimiento_id=${establecimientoId}`;

    try {
        const resp = await fetch(url);
        const json = await parseJsonResponse(resp);
        if (!json.success) { throw new Error(json.message || 'No se pudieron cargar los reportes'); }

        cachedData = json.data;
        tabDataLoaded = {}; // Reset: all tabs need reload

        // Render active tab immediately
        const activeTab = document.querySelector('.tab-pane.active')?.id || 'tab-errores-est';
        if (activeTab === 'tab-plazos') {
            loadPlazoAgregado();
        } else if (activeTab === 'tab-validador') {
            loadValidadorAgregado();
        } else if (activeTab === 'tab-serie' || activeTab === 'tab-hoja') {
            renderSerieHojaChart(activeTab, cachedData);
            tabDataLoaded[activeTab] = true;
        } else {
            renderTabChart(activeTab, cachedData);
            tabDataLoaded[activeTab] = true;
        }

    } catch (e) {
        showError(e.message || 'Error cargando reportes');
    }
}

// ============================================
// Chart Rendering per Tab
// ============================================

function renderTabChart(tabId, data) {
    const config = TAB_CONFIG[tabId];
    if (!config) return;

    // Destroy existing chart for this tab
    if (errorCharts[config.canvas]) {
        errorCharts[config.canvas].destroy();
        delete errorCharts[config.canvas];
    }

    // Orden descendente en todos los rankings: antes dependia del ORDER BY
    // de cada consulta y no era homogeneo entre pestanas.
    let resultData = (data[config.key] || []).slice()
        .sort((a, b) => (parseInt(b.total, 10) || 0) - (parseInt(a.total, 10) || 0));

    let labels = resultData.map(r => r.nombre_corto || r.nombre || r.codigo_serie || r.codigo_hoja);
    let values = resultData.map(r => parseInt(r.total, 10) || 0);

    // Aplicar Top N si está configurado
    if (config.topN && labels.length > config.topN) {
        const topAgg = aplicarTopNOtros(labels, values, config.topN);
        labels = topAgg.labels;
        values = topAgg.values;
    }

    renderChart(config.canvas, config.container, config.table, config.orientation, labels, values, config.colorToken, config.label);
}

function renderSerieHojaChart(tabId, data) {
    const isHoja = tabId === 'tab-hoja';
    const canvasId = isHoja ? 'chartErroresHoja' : 'chartErroresSerie';
    const containerId = isHoja ? 'chart5Container' : 'chart4Container';
    const tableId = isHoja ? 'tableErroresHoja' : 'tableErroresSerie';
    const dataKey = isHoja ? 'errores_hoja' : 'errores_serie';
    const groupField = isHoja ? 'codigo_hoja' : 'codigo_serie';

    if (errorCharts[canvasId]) {
        errorCharts[canvasId].destroy();
        delete errorCharts[canvasId];
    }

    const rawData = data[dataKey] || [];
    if (!rawData.length) {
        const container = document.getElementById(containerId);
        renderEmptyChart(container, 'Sin datos para los filtros seleccionados');
        document.getElementById(tableId).innerHTML = '<tr><td colspan="5" class="text-center text-secondary py-4">Sin datos para los filtros seleccionados</td></tr>';
        return;
    }

    // Pivotar: agrupar por codigo_serie/codigo_hoja, luego contar por tipo_error
    const grupos = {};
    const allTipos = new Set();
    rawData.forEach(row => {
        const g = row[groupField] || 'Sin clasificar';
        const t = String(row.tipo_error || 'Otro').trim();
        allTipos.add(t);
        if (!grupos[g]) grupos[g] = { total: 0 };
        grupos[g][t] = (grupos[g][t] || 0) + Number(row.total || 0);
        grupos[g].total += Number(row.total || 0);
    });

    // Orden fijo de tipos con fallback para tipos no previstos
    const TIPOS_VISTOS = Array.from(allTipos).sort();
    const tiposOrdenados = [];
    ['ERROR', 'REVISAR', 'F/PLAZO', 'S/OBSERVACION'].forEach(t => {
        if (TIPOS_VISTOS.includes(t)) tiposOrdenados.push(t);
    });
    TIPOS_VISTOS.forEach(t => {
        if (!tiposOrdenados.includes(t)) tiposOrdenados.push(t);
    });

    // Convertir a array y ordenar por total descendente
    let gruposArray = Object.entries(grupos)
        .map(([nombre, conteos]) => ({ nombre, conteos, total: conteos.total }))
        .sort((a, b) => b.total - a.total);

    // Aplicar Top 12 + Otros
    const topN = 12;
    if (gruposArray.length > topN) {
        const top = gruposArray.slice(0, topN);
        const resto = gruposArray.slice(topN);
        const otrosConteos = {};
        tiposOrdenados.forEach(t => { otrosConteos[t] = 0; });
        resto.forEach(g => {
            tiposOrdenados.forEach(t => {
                otrosConteos[t] += g.conteos[t] || 0;
            });
        });
        gruposArray = [...top, { nombre: `Otros (${resto.length})`, conteos: otrosConteos, total: resto.reduce((s, g) => s + g.total, 0) }];
    }

    // Mapear tipos a colores categóricos fijos
    const colorTokensPorTipo = {
        'ERROR': '--chart-series-1',
        'REVISAR': '--chart-series-2',
        'F/PLAZO': '--chart-series-3',
        'S/OBSERVACION': '--chart-series-4'
    };
    const colorTokens = tiposOrdenados.map(t => colorTokensPorTipo[t] || '--chart-series-1');

    // Crear datos planos para createStackedBarByCategory
    const flatData = [];
    gruposArray.forEach(g => {
        tiposOrdenados.forEach(t => {
            const v = g.conteos[t] || 0;
            if (v > 0) {
                flatData.push({ grupo: g.nombre, categoria: t, total: v });
            }
        });
    });

    const container = document.getElementById(containerId);
    setChartFrameSize(container, gruposArray.length, 'horizontal');

    const chart = createStackedBarByCategory(canvasId, flatData, tiposOrdenados, colorTokens, { topN: 0, unidad: 'observaciones' });
    if (chart) errorCharts[canvasId] = chart;

    // Actualizar tabla con desglose por tipo
    const tableBody = document.getElementById(tableId);
    let headerHtml = `<tr><th>${isHoja ? 'Hoja REM' : 'Serie REM'}</th>`;
    tiposOrdenados.forEach(t => {
        headerHtml += `<th class="text-end">${escapeHtml(t)}</th>`;
    });
    headerHtml += '<th class="text-end">Total</th></tr>';

    const thead = tableBody.parentElement.querySelector('thead');
    if (thead) {
        thead.innerHTML = headerHtml;
    }

    const rowsHtml = gruposArray.map(g => {
        let row = `<tr><td>${escapeHtml(g.nombre)}</td>`;
        tiposOrdenados.forEach(t => {
            const v = g.conteos[t] || 0;
            row += `<td class="text-end fw-medium">${v}</td>`;
        });
        row += `<td class="text-end fw-bold">${g.total}</td></tr>`;
        return row;
    }).join('');
    tableBody.innerHTML = rowsHtml;
}

/** Contexto que se imprime en el PNG exportado (ano, periodo y filtros). */
function buildExportContext(itemCount) {
    const partes = [];
    const year = document.getElementById('filterYear');
    if (year && year.value) partes.push('Año ' + year.value);

    const tri = document.getElementById('filterTrimestre');
    if (tri && tri.value) partes.push('Trimestre ' + tri.value);

    const mes = document.getElementById('filterMes');
    if (mes && mes.value) partes.push(mes.value);

    const comuna = document.getElementById('filterComuna');
    if (comuna && comuna.value && comuna.selectedIndex > 0) {
        partes.push(comuna.options[comuna.selectedIndex].text);
    }

    const est = document.getElementById('filterEstablecimiento');
    if (est && est.value && est.selectedIndex > 0) {
        partes.push(est.options[est.selectedIndex].text);
    }

    if (typeof itemCount === 'number') partes.push(itemCount + ' registros');
    return partes.join(' · ');
}

function setChartFrameSize(container, itemCount, orientation) {
    if (!container) return;
    container.dataset.exportContext = buildExportContext(itemCount);
    container.classList.remove('report-chart-frame--tall', 'report-chart-frame--long', 'report-chart-frame--xlong', 'report-chart-frame--vertical');
    if (orientation === 'vertical') container.classList.add('report-chart-frame--vertical');
    if (itemCount > 40) {
        container.classList.add('report-chart-frame--xlong');
    } else if (itemCount > 18) {
        container.classList.add('report-chart-frame--long');
    } else if (itemCount > 10) {
        container.classList.add('report-chart-frame--tall');
    }
}

function renderEmptyChart(container, message) {
    if (!container) return;
    container.classList.remove('report-chart-frame--tall', 'report-chart-frame--long', 'report-chart-frame--vertical');
    container.innerHTML = `<div class="report-empty-state"><i class="ti ti-chart-dots-3"></i><span>${escapeHtml(message)}</span></div>`;
}

function renderChart(canvasId, containerId, tableId, orientation, labels, values, color, colLabel) {
    const tableBody = document.getElementById(tableId);
    const container = document.getElementById(containerId);

    if (!labels.length || values.every(v => v === 0)) {
        tableBody.innerHTML = '<tr><td colspan="2" class="text-center text-secondary py-4">Sin datos para los filtros seleccionados</td></tr>';
        renderEmptyChart(container, 'Sin datos para los filtros seleccionados');
        return;
    }

    // Restaurar canvas si fue reemplazado
    if (container && !document.getElementById(canvasId)) {
        container.innerHTML = `<canvas id="${canvasId}"></canvas>`;
    }

    setChartFrameSize(container, labels.length, orientation);

    // Create chart
    if (orientation === 'horizontal') {
        errorCharts[canvasId] = createBarHorizontal(canvasId, labels, values, color);
    } else {
        errorCharts[canvasId] = createBarVertical(canvasId, labels, values, color);
    }

    tableBody.innerHTML = labels.map((l, i) => `
        <tr>
            <td>${escapeHtml(l)}</td>
            <td class="text-end fw-medium">${values[i]}</td>
        </tr>
    `).join('');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ============================================
// Reportes Mejorados: Plazo y Validador (Matriz)
// ============================================

async function loadPlazoAgregado() {
    const year = document.getElementById('filterYear').value;
    const meses = getMesesFiltro();
    let url = `api/reports.php?report=plazo-agregado&year=${year}`;
    url = appendMeses(url, meses);
    try {
        const resp = await fetch(url);
        const json = await parseJsonResponse(resp);
        if (!json.success) throw new Error(json.message || 'No se pudo cargar el reporte de plazos');
        renderPlazoMatriz(json.data, meses);
        tabDataLoaded['tab-plazos'] = true;
    } catch (e) {
        showError(e.message || 'Error cargando reporte de plazos');
    }
}

function renderPlazoChart(data) {
    const detalle = data.detalle_mensual || [];
    const tbody = document.getElementById('tablePlazoMatriz');

    if (!detalle.length) {
        tbody.innerHTML = '<tr><td colspan="2" class="text-center text-secondary py-4">Sin datos para el año seleccionado.</td></tr>';
        return;
    }

    // Agrupar por establecimiento
    const porEst = {};
    detalle.forEach(row => {
        const estKey = row.nombre_corto;
        if (!porEst[estKey]) {
            porEst[estKey] = { nombre: row.nombre_corto, id: row.id, meses: {} };
        }
        porEst[estKey].meses[row.mes] = row;
    });

    const mesesMostrar = mesesList;

    // Construir tabla
    let html = '';
    Object.values(porEst).forEach(est => {
        // Fila de agrupación por establecimiento
        html += `<tr style="background-color: #d3d3d3; font-weight: bold;">
                    <td colspan="2">${escapeHtml(est.nombre)}</td>
                </tr>`;

        // Una fila por mes
        mesesMostrar.forEach(mes => {
            const row = est.meses[mes];
            let icono = '<i class="ti ti-minus text-secondary"></i>';

            if (row) {
                // Si hay algún "fuera de plazo", mostrar ✗ rojo
                if (row.fuera === 1) {
                    icono = '<i class="ti ti-circle-x text-danger" style="font-size: 1.2em;"></i>';
                } else if (row.dentro === 1) {
                    icono = '<i class="ti ti-circle-check text-success" style="font-size: 1.2em;"></i>';
                }
            }

            html += `<tr>
                        <td style="padding-left: 2rem;">${escapeHtml(mes)}</td>
                        <td class="text-center">${icono}</td>
                    </tr>`;
        });
    });

    tbody.innerHTML = html;
}

async function loadValidadorAgregado() {
    const year = document.getElementById('filterYear').value;
    const meses = getMesesFiltro();
    let url = `api/reports.php?report=validador-agregado&year=${year}`;
    url = appendMeses(url, meses);
    try {
        const resp = await fetch(url);
        const json = await parseJsonResponse(resp);
        if (!json.success) throw new Error(json.message || 'No se pudo cargar el reporte de validador');
        renderValidadorChart(json.data);
        tabDataLoaded['tab-validador'] = true;
    } catch (e) {
        showError(e.message || 'Error cargando reporte de validador');
    }
}

function renderValidadorChart(data) {
    const detalle = data.detalle_mensual || [];
    const tbody = document.getElementById('tableValidadorMatriz');

    if (!detalle.length) {
        tbody.innerHTML = '<tr><td colspan="2" class="text-center text-secondary py-4">Sin datos para el año seleccionado.</td></tr>';
        return;
    }

    // Agrupar por establecimiento
    const porEst = {};
    detalle.forEach(row => {
        const estKey = row.nombre_corto;
        if (!porEst[estKey]) {
            porEst[estKey] = { nombre: row.nombre_corto, id: row.id, meses: {} };
        }
        porEst[estKey].meses[row.mes] = row;
    });

    const mesesMostrar = mesesList;

    // Construir tabla
    let html = '';
    Object.values(porEst).forEach(est => {
        // Fila de agrupación por establecimiento
        html += `<tr style="background-color: #d3d3d3; font-weight: bold;">
                    <td colspan="2">${escapeHtml(est.nombre)}</td>
                </tr>`;

        // Una fila por mes
        mesesMostrar.forEach(mes => {
            const row = est.meses[mes];
            let icono = '<i class="ti ti-minus text-secondary"></i>';

            if (row) {
                // Si hay algún "no usa validador", mostrar ✗ rojo
                if (row.no_usa === 1) {
                    icono = '<i class="ti ti-circle-x text-danger" style="font-size: 1.2em;"></i>';
                } else if (row.usa === 1) {
                    icono = '<i class="ti ti-circle-check text-success" style="font-size: 1.2em;"></i>';
                }
            }

            html += `<tr>
                        <td style="padding-left: 2rem;">${escapeHtml(mes)}</td>
                        <td class="text-center">${icono}</td>
                    </tr>`;
        });
    });

    tbody.innerHTML = html;
}

// ============================================
// Event Listeners
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Tab shown event (Bootstrap)
    document.querySelectorAll('.nav-link[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('shown.bs.tab', (e) => {
            onTabShown(e.target.getAttribute('data-bs-target').replace('#', ''));
        });
    });

    // Filters
    document.getElementById('filterComuna').addEventListener('change', loadEstablecimientos);
    document.getElementById('btnApplyFilters').addEventListener('click', loadErrorReports);
    document.getElementById('btnClearFilters').addEventListener('click', clearFilters);
    document.getElementById('btnExportExcel').addEventListener('click', exportActiveReportExcel);
    document.getElementById('btnExportPdf').addEventListener('click', exportDetailedPdf);

    // Restore tab from hash
    const hashTab = location.hash.replace('#', '');
    if (hashTab && ['tab-errores-est','tab-plazos','tab-validador','tab-serie','tab-hoja'].includes(hashTab)) {
        const tabBtn = document.querySelector(`[data-bs-target="#${hashTab}"]`);
        if (tabBtn) bootstrap.Tab.getOrCreateInstance(tabBtn).show();
    }

    // Initial load
    loadErrorReports();
});

function clearFilters() {
    document.getElementById('filterYear').value = '<?php echo $currentYear; ?>';
    document.getElementById('filterTrimestre').value = '';
    document.getElementById('filterMes').value = '';
    document.getElementById('filterComuna').value = '';
    document.getElementById('filterEstablecimiento').innerHTML = '<option value="">Todos</option>';
    document.getElementById('filterEstablecimiento').disabled = true;
    loadErrorReports();
}

function getExportFilters() {
    const params = new URLSearchParams();
    params.set('year', document.getElementById('filterYear').value);

    const meses = getMesesFiltro();
    const comunaId = document.getElementById('filterComuna').value;
    const establecimientoId = document.getElementById('filterEstablecimiento').value;

    if (meses.length === 1) params.set('month', meses[0]);
    if (meses.length > 1) params.set('months', meses.join(','));
    if (comunaId) params.set('comuna_id', comunaId);
    if (establecimientoId) params.set('establecimiento_id', establecimientoId);

    return params;
}

function exportActiveReportExcel() {
    const activeTab = document.querySelector('.tab-pane.active')?.id || 'tab-errores-est';
    const params = getExportFilters();
    params.set('format', 'excel');
    params.set('report_type', EXPORT_REPORT_TYPES[activeTab] || 'errores_establecimiento');
    window.open('api/export.php?' + params.toString(), '_blank');
}

function exportDetailedPdf() {
    const params = getExportFilters();
    params.set('format', 'pdf');
    params.set('report_type', 'detallado');
    window.open('api/export.php?' + params.toString(), '_blank');
}


async function parseJsonResponse(response) {
    const text = await response.text();
    let data = {};
    try {
        data = text ? JSON.parse(text) : {};
    } catch (error) {
        throw new Error('Respuesta inválida del servidor');
    }
    if (!response.ok) {
        throw new Error(data.message || 'Error en la petición');
    }
    return data;
}


</script>
