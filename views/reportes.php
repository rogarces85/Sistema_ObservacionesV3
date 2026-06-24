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
                                        <button id="btnQueueExcel" class="btn btn-success" type="button">
                                            <i class="ti ti-clock-plus me-1"></i>Encolar Excel
                                        </button>
                                        <button id="btnQueuePdf" class="btn btn-danger" type="button">
                                            <i class="ti ti-clock-plus me-1"></i>Encolar PDF
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h3 class="card-title mb-0"><i class="ti ti-list-check me-2 text-primary"></i>Reportes Asíncronos</h3>
                            <button id="btnRefreshQueue" class="btn btn-sm btn-outline-secondary" type="button">
                                <i class="ti ti-refresh me-1"></i>Actualizar
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tipo</th>
                                        <th>Formato</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th class="text-end">Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="reportQueueBody">
                                    <tr><td colspan="6" class="text-center text-secondary py-3">Cargando reportes...</td></tr>
                                </tbody>
                            </table>
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
                                <div class="report-chart-frame" id="chart1Container">
                                    <canvas id="chartErroresEst"></canvas>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-vcenter card-table table-hover">
                                        <thead><tr><th>Establecimiento</th><th class="text-end">Errores</th></tr></thead>
                                        <tbody id="tableErroresEst"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 2: Plazos Entrega -->
                            <div id="tab-plazos" class="tab-pane" role="tabpanel">
                                <div class="report-chart-frame" id="chart2Container">
                                    <canvas id="chartPlazoAgregado"></canvas>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-vcenter card-table table-hover">
                                        <thead><tr><th>Establecimiento</th><th class="text-end">Dentro plazo</th><th class="text-end">Fuera plazo</th><th class="text-end">Total meses</th></tr></thead>
                                        <tbody id="tablePlazoResumen"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 3: Uso Validador -->
                            <div id="tab-validador" class="tab-pane" role="tabpanel">
                                <div class="report-chart-frame" id="chart3Container">
                                    <canvas id="chartValidadorAgregado"></canvas>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-vcenter card-table table-hover">
                                        <thead><tr><th>Establecimiento</th><th class="text-end">Usa validador</th><th class="text-end">No usa validador</th><th class="text-end">Total meses</th></tr></thead>
                                        <tbody id="tableValidadorResumen"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 4: Errores por Serie -->
                            <div id="tab-serie" class="tab-pane" role="tabpanel">
                                <div class="report-chart-frame" id="chart4Container">
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
                                <div class="report-chart-frame" id="chart5Container">
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

const TAB_CONFIG = {
    'tab-errores-est': { canvas: 'chartErroresEst', container: 'chart1Container', table: 'tableErroresEst', orientation: 'horizontal', colorToken: '--tblr-danger', colorFallback: '#dc2626', label: 'Errores', key: 'errores_establecimiento' },
    'tab-serie': { canvas: 'chartErroresSerie', container: 'chart4Container', table: 'tableErroresSerie', orientation: 'horizontal', colorToken: '--tblr-primary', colorFallback: '#0ea5e9', label: 'Errores', key: 'errores_serie' },
    'tab-hoja': { canvas: 'chartErroresHoja', container: 'chart5Container', table: 'tableErroresHoja', orientation: 'vertical', colorToken: '--tblr-success', colorFallback: '#10b981', label: 'Errores', key: 'errores_hoja' }
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
            const data = await response.json();
            if (data.success) {
                data.data.forEach(est => {
                    const option = document.createElement('option');
                    option.value = est.id;
                    option.textContent = est.nombre_corto || est.nombre;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            showError('No se pudieron cargar los establecimientos de la comuna seleccionada');
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
        const json = await resp.json();
        if (!json.success) { throw new Error(json.message || 'No se pudieron cargar los reportes'); }

        cachedData = json.data;
        tabDataLoaded = {}; // Reset: all tabs need reload

        // Render active tab immediately
        const activeTab = document.querySelector('.tab-pane.active')?.id || 'tab-errores-est';
        if (activeTab === 'tab-plazos') {
            loadPlazoAgregado();
        } else if (activeTab === 'tab-validador') {
            loadValidadorAgregado();
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

    const resultData = data[config.key] || [];
    const labels = resultData.map(r => r.nombre_corto || r.nombre || r.codigo_serie || r.codigo_hoja);
    const values = resultData.map(r => parseInt(r.total));

    const color = chartTokenColor(config.colorToken, config.colorFallback);
    renderChart(config.canvas, config.container, config.table, config.orientation, labels, values, color, config.label);
}

function setChartFrameSize(container, itemCount, orientation) {
    if (!container) return;
    container.classList.remove('report-chart-frame--tall', 'report-chart-frame--long', 'report-chart-frame--vertical');
    if (orientation === 'vertical') container.classList.add('report-chart-frame--vertical');
    if (itemCount > 18) {
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
// Reportes Mejorados: Plazo y Validador
// ============================================

async function loadPlazoAgregado() {
    const year = document.getElementById('filterYear').value;
    const meses = getMesesFiltro();
    let url = `api/reports.php?report=plazo-agregado&year=${year}`;
    url = appendMeses(url, meses);
    try {
        const resp = await fetch(url);
        const json = await resp.json();
        if (!json.success) throw new Error(json.message || 'No se pudo cargar el reporte de plazos');
        renderPlazoChart(json.data);
        tabDataLoaded['tab-plazos'] = true;
    } catch (e) {
        showError(e.message || 'Error cargando reporte de plazos');
    }
}

function renderPlazoChart(data) {
    const est = data.establecimientos || [];
    const labels = est.map(e => e.nombre_corto);

    // Destruir chart anterior
    if (errorCharts['chartPlazoAgregado']) {
        errorCharts['chartPlazoAgregado'].destroy();
        delete errorCharts['chartPlazoAgregado'];
    }

    const container = document.getElementById('chart2Container');
    if (!document.getElementById('chartPlazoAgregado')) {
        container.innerHTML = '<canvas id="chartPlazoAgregado"></canvas>';
    }
    setChartFrameSize(container, labels.length, 'horizontal');

    if (labels.length === 0) {
        renderEmptyChart(container, 'Sin datos para el año seleccionado');
        document.getElementById('tablePlazoResumen').innerHTML = '<tr><td colspan="4" class="text-center text-secondary py-4">Sin datos para el año seleccionado</td></tr>';
        return;
    }

    errorCharts['chartPlazoAgregado'] = createBarHorizontal('chartPlazoAgregado', labels, est.map(e => parseInt(e.meses_fuera)), chartTokenColor('--tblr-danger', '#dc2626'));

    document.getElementById('tablePlazoResumen').innerHTML = est.map(e => `
        <tr>
            <td>${escapeHtml(e.nombre_corto)}</td>
            <td class="text-end fw-medium text-success">${e.meses_dentro}</td>
            <td class="text-end fw-medium text-danger">${e.meses_fuera}</td>
            <td class="text-end text-secondary">${e.meses_con_datos}</td>
        </tr>
    `).join('');

}

async function loadValidadorAgregado() {
    const year = document.getElementById('filterYear').value;
    const meses = getMesesFiltro();
    let url = `api/reports.php?report=validador-agregado&year=${year}`;
    url = appendMeses(url, meses);
    try {
        const resp = await fetch(url);
        const json = await resp.json();
        if (!json.success) throw new Error(json.message || 'No se pudo cargar el reporte de validador');
        renderValidadorChart(json.data);
        tabDataLoaded['tab-validador'] = true;
    } catch (e) {
        showError(e.message || 'Error cargando reporte de validador');
    }
}

function renderValidadorChart(data) {
    const est = data.establecimientos || [];
    const labels = est.map(e => e.nombre_corto);

    if (errorCharts['chartValidadorAgregado']) {
        errorCharts['chartValidadorAgregado'].destroy();
        delete errorCharts['chartValidadorAgregado'];
    }

    const container = document.getElementById('chart3Container');
    if (!document.getElementById('chartValidadorAgregado')) {
        container.innerHTML = '<canvas id="chartValidadorAgregado"></canvas>';
    }
    setChartFrameSize(container, labels.length, 'horizontal');

    if (labels.length === 0) {
        renderEmptyChart(container, 'Sin datos para el año seleccionado');
        document.getElementById('tableValidadorResumen').innerHTML = '<tr><td colspan="4" class="text-center text-secondary py-4">Sin datos para el año seleccionado</td></tr>';
        return;
    }

    errorCharts['chartValidadorAgregado'] = createBarHorizontal('chartValidadorAgregado', labels, est.map(e => parseInt(e.meses_no_usa)), chartTokenColor('--tblr-muted', '#94a3b8'));

    document.getElementById('tableValidadorResumen').innerHTML = est.map(e => `
        <tr>
            <td>${escapeHtml(e.nombre_corto)}</td>
            <td class="text-end fw-medium text-info">${e.meses_usa}</td>
            <td class="text-end fw-medium text-secondary">${e.meses_no_usa}</td>
            <td class="text-end text-secondary">${e.meses_con_datos}</td>
        </tr>
    `).join('');
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
    document.getElementById('btnQueueExcel').addEventListener('click', () => enqueueReport('excel'));
    document.getElementById('btnQueuePdf').addEventListener('click', () => enqueueReport('pdf'));
    document.getElementById('btnRefreshQueue').addEventListener('click', loadReportQueue);

    // Restore tab from hash
    const hashTab = location.hash.replace('#', '');
    if (hashTab && ['tab-errores-est','tab-plazos','tab-validador','tab-serie','tab-hoja'].includes(hashTab)) {
        const tabBtn = document.querySelector(`[data-bs-target="#${hashTab}"]`);
        if (tabBtn) bootstrap.Tab.getOrCreateInstance(tabBtn).show();
    }

    // Initial load
    loadErrorReports();
    loadReportQueue();
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

function getQueuePayload(format) {
    const activeTab = document.querySelector('.tab-pane.active')?.id || 'tab-errores-est';
    const tipo = format === 'pdf' ? 'detallado' : (EXPORT_REPORT_TYPES[activeTab] || 'errores_establecimiento');
    const params = {
        year: document.getElementById('filterYear').value,
        meses: getMesesFiltro(),
        establecimiento_id: document.getElementById('filterEstablecimiento').value || undefined,
        comuna_id: document.getElementById('filterComuna').value || undefined
    };

    return {
        tipo_reporte: tipo,
        formato: format === 'pdf' ? 'pdf' : 'xlsx',
        parametros: params
    };
}

async function enqueueReport(format) {
    try {
        showLoading();
        const response = await fetchAPI('report_queue.php?action=enqueue', {
            method: 'POST',
            body: JSON.stringify(getQueuePayload(format))
        });
        hideLoading();

        if (response.success) {
            showSuccess('Reporte encolado correctamente. Ejecute el worker para procesarlo.');
            loadReportQueue();
        }
    } catch (error) {
        hideLoading();
        showError(error.message || 'Error al encolar reporte');
    }
}

async function loadReportQueue() {
    const tbody = document.getElementById('reportQueueBody');
    tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-3">Cargando reportes...</td></tr>';

    try {
        const response = await fetchAPI('report_queue.php?action=list');
        const reports = response.data || [];
        if (reports.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-secondary py-3">No hay reportes en cola.</td></tr>';
            return;
        }

        tbody.innerHTML = reports.map(report => {
            const statusClass = {
                PENDIENTE: 'badge bg-yellow text-yellow-fg',
                PROCESANDO: 'badge bg-blue text-blue-fg',
                LISTO: 'badge bg-green text-green-fg',
                ERROR: 'badge bg-red text-red-fg'
            }[report.estado] || 'badge';
            const action = report.estado === 'LISTO'
                ? `<a class="btn btn-sm btn-primary" href="api/report_queue.php?action=download&id=${report.id}"><i class="ti ti-download me-1"></i>Descargar</a>`
                : `<span class="text-secondary small">${escapeHtml(report.mensaje_error || '-')}</span>`;
            return `
                <tr>
                    <td>#${report.id}</td>
                    <td>${escapeHtml(report.tipo_reporte)}</td>
                    <td>${escapeHtml(report.formato)}</td>
                    <td><span class="${statusClass}">${escapeHtml(report.estado)}</span></td>
                    <td class="text-secondary">${formatDateTime(report.fecha_creacion)}</td>
                    <td class="text-end">${action}</td>
                </tr>
            `;
        }).join('');
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-3">No se pudo cargar la cola.</td></tr>';
    }
}

function formatDateTime(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('es-CL', { dateStyle: 'short', timeStyle: 'short' });
}
</script>
