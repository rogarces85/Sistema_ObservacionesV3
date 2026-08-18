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

                            <!-- Tab 2: Plazos Entrega (Matriz mensual) -->
                            <div id="tab-plazos" class="tab-pane" role="tabpanel">
                                <section class="rem-informe" data-informe-para="tablaPlazoMatriz">
                                    <header class="rem-informe-head">
                                        <img src="assets/images/logo-boletin.png" alt="Servicio de Salud Osorno" class="rem-informe-logo">
                                        <div class="rem-informe-titulos">
                                            <h2 class="rem-informe-title">Plazo de entrega por establecimiento y mes</h2>
                                            <p class="rem-informe-subtitle">Servicio de Salud Osorno</p>
                                            <p class="rem-informe-periodo"></p>
                                        </div>
                                    </header>

                                    <div class="rem-informe-barra">
                                        <div class="rem-matriz-legend">
                                            <span><i class="ti ti-check rem-matriz-chip rem-matriz-chip--ok"></i>Dentro de plazo</span>
                                            <span><i class="ti ti-x rem-matriz-chip rem-matriz-chip--falla"></i>Fuera de plazo</span>
                                            <span><i class="ti ti-minus rem-matriz-chip rem-matriz-chip--vacio"></i>Sin datos</span>
                                        </div>
                                        <div class="btn-list rem-informe-acciones">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-imprimir-informe>
                                                <i class="ti ti-printer me-1"></i>Imprimir
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-pdf-matriz>
                                                <i class="ti ti-file-type-pdf me-1"></i>PDF
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-responsive rem-matriz-wrap">
                                        <table id="tablaPlazoMatriz" class="table table-bordered table-vcenter card-table rem-matriz-table">
                                            <thead></thead>
                                            <tbody></tbody>
                                            <tfoot></tfoot>
                                        </table>
                                    </div>
                                </section>
                            </div>

                            <!-- Tab 3: Uso Validador (Matriz mensual) -->
                            <div id="tab-validador" class="tab-pane" role="tabpanel">
                                <section class="rem-informe" data-informe-para="tablaValidadorMatriz">
                                    <header class="rem-informe-head">
                                        <img src="assets/images/logo-boletin.png" alt="Servicio de Salud Osorno" class="rem-informe-logo">
                                        <div class="rem-informe-titulos">
                                            <h2 class="rem-informe-title">Uso de validador local por establecimiento y mes</h2>
                                            <p class="rem-informe-subtitle">Servicio de Salud Osorno</p>
                                            <p class="rem-informe-periodo"></p>
                                        </div>
                                    </header>

                                    <div class="rem-informe-barra">
                                        <div class="rem-matriz-legend">
                                            <span><i class="ti ti-check rem-matriz-chip rem-matriz-chip--ok"></i>Usa validador</span>
                                            <span><i class="ti ti-x rem-matriz-chip rem-matriz-chip--falla"></i>No usa validador</span>
                                            <span><i class="ti ti-minus rem-matriz-chip rem-matriz-chip--vacio"></i>Sin datos</span>
                                        </div>
                                        <div class="btn-list rem-informe-acciones">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-imprimir-informe>
                                                <i class="ti ti-printer me-1"></i>Imprimir
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-pdf-matriz>
                                                <i class="ti ti-file-type-pdf me-1"></i>PDF
                                            </button>
                                        </div>
                                    </div>

                                    <div class="table-responsive rem-matriz-wrap">
                                        <table id="tablaValidadorMatriz" class="table table-bordered table-vcenter card-table rem-matriz-table">
                                            <thead></thead>
                                            <tbody></tbody>
                                            <tfoot></tfoot>
                                        </table>
                                    </div>
                                </section>
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

// Plazo y Validador exportan la MISMA matriz que se ve en pantalla; antes
// descargaban un resumen distinto (fuera_plazo_establecimiento).
const EXPORT_REPORT_TYPES = {
    'tab-errores-est': 'errores_establecimiento',
    'tab-plazos': 'plazo_matriz',
    'tab-validador': 'validador_matriz',
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

/**
 * Alto del marco proporcional a la cantidad de barras: con muchos establecimientos
 * un alto fijo dejaba cada barra en ~20px y los nombres se solapaban.
 * Se usa una variable CSS (no style inline) que lee .report-chart-frame en tabler-override.css.
 */
function setChartFrameSize(container, itemCount, orientation) {
    if (!container) return;
    container.dataset.exportContext = buildExportContext(itemCount);
    container.classList.remove('report-chart-frame--tall', 'report-chart-frame--long', 'report-chart-frame--xlong', 'report-chart-frame--vertical');

    if (orientation === 'vertical') {
        container.classList.add('report-chart-frame--vertical');
        container.style.removeProperty('--rem-chart-frame-height');
        return;
    }

    const alto = Math.min(2000, Math.max(320, (itemCount * 30) + 90));
    container.style.setProperty('--rem-chart-frame-height', alto + 'px');
}

function renderEmptyChart(container, message) {
    if (!container) return;
    container.classList.remove('report-chart-frame--tall', 'report-chart-frame--long', 'report-chart-frame--xlong', 'report-chart-frame--vertical');
    container.style.removeProperty('--rem-chart-frame-height');
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

/** URL con los mismos filtros que el resto de la pagina (ano, meses, comuna, establecimiento). */
function buildMatrizUrl(report) {
    const year = document.getElementById('filterYear').value;
    const comunaId = document.getElementById('filterComuna').value;
    const establecimientoId = document.getElementById('filterEstablecimiento').value;

    let url = `api/reports.php?report=${report}&year=${year}`;
    url = appendMeses(url, getMesesFiltro());
    if (comunaId) url += `&comuna_ids[]=${encodeURIComponent(comunaId)}`;
    if (establecimientoId) url += `&establecimiento_id=${encodeURIComponent(establecimientoId)}`;
    return url;
}

async function loadPlazoAgregado() {
    try {
        const resp = await fetch(buildMatrizUrl('plazo-agregado'));
        const json = await parseJsonResponse(resp);
        if (!json.success) throw new Error(json.message || 'No se pudo cargar el reporte de plazos');
        renderPlazoChart(json.data);
        tabDataLoaded['tab-plazos'] = true;
    } catch (e) {
        showError(e.message || 'Error cargando reporte de plazos');
    }
}

function renderPlazoChart(data) {
    renderMatrizMensual('tablaPlazoMatriz', data.detalle_mensual || [], 'dentro', 'fuera', {
        ok: 'Dentro de plazo',
        falla: 'Fuera de plazo'
    });
}

async function loadValidadorAgregado() {
    try {
        const resp = await fetch(buildMatrizUrl('validador-agregado'));
        const json = await parseJsonResponse(resp);
        if (!json.success) throw new Error(json.message || 'No se pudo cargar el reporte de validador');
        renderValidadorChart(json.data);
        tabDataLoaded['tab-validador'] = true;
    } catch (e) {
        showError(e.message || 'Error cargando reporte de validador');
    }
}

function renderValidadorChart(data) {
    renderMatrizMensual('tablaValidadorMatriz', data.detalle_mensual || [], 'usa', 'no_usa', {
        ok: 'Usa validador',
        falla: 'No usa validador'
    });
}

/** Meses a mostrar: los del filtro; si no hay filtro, los que traen datos, en orden de calendario. */
function mesesDeLaMatriz(detalle) {
    const filtro = getMesesFiltro();
    if (filtro.length) return filtro;

    const conDatos = new Set(detalle.map(r => r.mes));
    return mesesList.filter(m => conDatos.has(m));
}

const MES_ABREV = {
    'Enero': 'ENE', 'Febrero': 'FEB', 'Marzo': 'MAR', 'Abril': 'ABR',
    'Mayo': 'MAY', 'Junio': 'JUN', 'Julio': 'JUL', 'Agosto': 'AGO',
    'Septiembre': 'SEP', 'Octubre': 'OCT', 'Noviembre': 'NOV', 'Diciembre': 'DIC'
};

/**
 * Matriz de doble entrada: una fila por establecimiento y una columna por mes.
 * Pensada como informe presentable/imprimible: con 70+ establecimientos el formato
 * vertical anterior ocupaba decenas de paginas.
 * campoOk/campoFalla son las columnas binarias que devuelve la API para cada mes.
 * Llegan por PDO y pueden venir como string ("1"), por eso se comparan con Number().
 */
function renderMatrizMensual(tablaId, detalle, campoOk, campoFalla, textos) {
    const tabla = document.getElementById(tablaId);
    if (!tabla) return;

    const thead = tabla.querySelector('thead');
    const tbody = tabla.querySelector('tbody');
    const tfoot = tabla.querySelector('tfoot');

    if (!detalle.length) {
        thead.innerHTML = '<tr><th>Establecimiento</th></tr>';
        tbody.innerHTML = '<tr><td class="text-center text-secondary py-4">Sin datos para los filtros seleccionados.</td></tr>';
        tfoot.innerHTML = '';
        return;
    }

    const meses = mesesDeLaMatriz(detalle);

    // Agrupar por establecimiento conservando el orden alfabetico de la consulta.
    const porEst = {};
    detalle.forEach(row => {
        const estKey = row.nombre_corto;
        if (!porEst[estKey]) {
            porEst[estKey] = { nombre: row.nombre_corto, meses: {} };
        }
        porEst[estKey].meses[row.mes] = row;
    });

    // --- Cabecera ---
    let headHtml = '<tr><th class="rem-matriz-est-col">Establecimiento</th>';
    meses.forEach(mes => {
        headHtml += `<th class="rem-matriz-mes-col" title="${escapeHtml(mes)}">${escapeHtml(MES_ABREV[mes] || mes.slice(0, 3).toUpperCase())}</th>`;
    });
    headHtml += '<th class="rem-matriz-total-col">Cumple</th></tr>';
    thead.innerHTML = headHtml;

    // --- Cuerpo ---
    const totalesMes = meses.map(() => ({ ok: 0, conDatos: 0 }));

    const filas = Object.values(porEst).map(est => {
        let cumple = 0;
        let conDatos = 0;
        let celdas = '';

        meses.forEach((mes, i) => {
            const row = est.meses[mes];
            let clase = 'rem-matriz-cell rem-matriz-cell--vacio';
            let icono = '<i class="ti ti-minus" aria-hidden="true"></i>';
            let titulo = `${mes}: sin datos`;

            if (row) {
                const obs = Number(row.total_observaciones) || 0;
                const sufijo = `${mes} · ${obs} observación(es)`;
                conDatos++;
                totalesMes[i].conDatos++;

                if (Number(row[campoFalla]) === 1) {
                    clase = 'rem-matriz-cell rem-matriz-cell--falla';
                    icono = '<i class="ti ti-x" aria-hidden="true"></i>';
                    titulo = `${textos.falla} — ${sufijo}`;
                } else if (Number(row[campoOk]) === 1) {
                    clase = 'rem-matriz-cell rem-matriz-cell--ok';
                    icono = '<i class="ti ti-check" aria-hidden="true"></i>';
                    titulo = `${textos.ok} — ${sufijo}`;
                    cumple++;
                    totalesMes[i].ok++;
                }
            }

            celdas += `<td class="${clase}" title="${escapeHtml(titulo)}"><span class="visually-hidden">${escapeHtml(titulo)}</span>${icono}</td>`;
        });

        const resumen = conDatos ? `${cumple}/${conDatos}` : '—';
        const claseResumen = conDatos && cumple < conDatos ? 'rem-matriz-resumen rem-matriz-resumen--parcial' : 'rem-matriz-resumen';

        return `<tr>
                    <th scope="row" class="rem-matriz-est">${escapeHtml(est.nombre)}</th>
                    ${celdas}
                    <td class="${claseResumen}">${resumen}</td>
                </tr>`;
    });

    tbody.innerHTML = filas.join('');

    // --- Totales por mes ---
    let footHtml = '<tr><th scope="row" class="rem-matriz-est">Cumplen</th>';
    meses.forEach((mes, i) => {
        const t = totalesMes[i];
        footHtml += `<td class="rem-matriz-total-mes" title="${escapeHtml(mes)}: ${t.ok} de ${t.conDatos} establecimiento(s) cumplen">${t.conDatos ? t.ok + '/' + t.conDatos : '—'}</td>`;
    });
    footHtml += `<td class="rem-matriz-total-col"></td></tr>`;
    tfoot.innerHTML = footHtml;

    // Contexto del encabezado del informe (visible al imprimir)
    actualizarEncabezadoInforme(tablaId, meses);
}

/** Rellena el subtitulo del encabezado institucional con el periodo y los filtros activos. */
function actualizarEncabezadoInforme(tablaId, meses) {
    const cabecera = document.querySelector(`[data-informe-para="${tablaId}"]`);
    if (!cabecera) return;

    const periodo = cabecera.querySelector('.rem-informe-periodo');
    if (!periodo) return;

    const year = document.getElementById('filterYear').value;
    const partes = [];
    if (meses.length === 1) {
        partes.push(meses[0]);
    } else if (meses.length > 1) {
        partes.push(`${meses[0]} a ${meses[meses.length - 1]}`);
    }
    partes.push(year);

    const comuna = document.getElementById('filterComuna');
    if (comuna && comuna.value && comuna.selectedIndex > 0) {
        partes.push('Comuna ' + comuna.options[comuna.selectedIndex].text);
    }
    const est = document.getElementById('filterEstablecimiento');
    if (est && est.value && est.selectedIndex > 0) {
        partes.push(est.options[est.selectedIndex].text);
    }

    periodo.textContent = partes.join(' · ');
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

    // Imprimir la matriz visible (los estilos @media print dejan solo el informe)
    document.querySelectorAll('[data-imprimir-informe]').forEach(btn => {
        btn.addEventListener('click', () => window.print());
    });

    // PDF de la matriz visible
    document.querySelectorAll('[data-pdf-matriz]').forEach(btn => {
        btn.addEventListener('click', exportMatrizPdf);
    });

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

function exportActiveReportExcel(evento) {
    const activeTab = document.querySelector('.tab-pane.active')?.id || 'tab-errores-est';
    const params = getExportFilters();
    params.set('format', 'excel');
    params.set('report_type', EXPORT_REPORT_TYPES[activeTab] || 'errores_establecimiento');
    descargarArchivo('api/export.php?' + params.toString(), { boton: evento?.currentTarget });
}

function exportDetailedPdf(evento) {
    const params = getExportFilters();
    params.set('format', 'pdf');
    params.set('report_type', 'detallado');
    descargarArchivo('api/export.php?' + params.toString(), { boton: evento?.currentTarget });
}

/** PDF de la matriz visible (pestañas Plazos Entrega / Uso Validador). */
function exportMatrizPdf(evento) {
    const activeTab = document.querySelector('.tab-pane.active')?.id;
    const reportType = EXPORT_REPORT_TYPES[activeTab];
    if (!reportType) return;

    const params = getExportFilters();
    params.set('format', 'pdf');
    params.set('report_type', reportType);
    descargarArchivo('api/export.php?' + params.toString(), { boton: evento?.currentTarget });
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
