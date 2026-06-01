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

<div class="row row-cards">

                <!-- Header -->
                <div class="col-12">
                    <div class="page-header">
                        <div class="page-pretitle">Análisis de errores por establecimiento, plazo, validador, serie y hoja</div>
                        <h2 class="page-title">Reportes de Errores REM</h2>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title mb-3">Filtros</h3>
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
                                <div class="relative" id="chart1Container" style="height: 400px;">
                                    <div id="chartErroresEst"></div>
                                </div>
                                <div class="mt-3 overflow-x-auto">
                                    <table class="table table-vcenter card-table">
                                        <thead><tr><th>Establecimiento</th><th class="text-end">Errores</th></tr></thead>
                                        <tbody id="tableErroresEst"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 2: Plazos Entrega -->
                            <div id="tab-plazos" class="tab-pane" role="tabpanel">
                                <div class="relative" id="chart2Container" style="height: 400px;">
                                    <div id="chartPlazoAgregado"></div>
                                </div>
                                <div class="mt-3 overflow-x-auto">
                                    <table class="table table-vcenter card-table">
                                        <thead><tr><th>Establecimiento</th><th class="text-end">Dentro plazo</th><th class="text-end">Fuera plazo</th><th class="text-end">Total meses</th></tr></thead>
                                        <tbody id="tablePlazoResumen"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 3: Uso Validador -->
                            <div id="tab-validador" class="tab-pane" role="tabpanel">
                                <div class="relative" id="chart3Container" style="height: 400px;">
                                    <div id="chartValidadorAgregado"></div>
                                </div>
                                <div class="mt-3 overflow-x-auto">
                                    <table class="table table-vcenter card-table">
                                        <thead><tr><th>Establecimiento</th><th class="text-end">Usa validador</th><th class="text-end">No usa validador</th><th class="text-end">Total meses</th></tr></thead>
                                        <tbody id="tableValidadorResumen"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 4: Errores por Serie -->
                            <div id="tab-serie" class="tab-pane" role="tabpanel">
                                <div class="relative" id="chart4Container" style="height: 400px;">
                                    <div id="chartErroresSerie"></div>
                                </div>
                                <div class="mt-3 overflow-x-auto">
                                    <table class="table table-vcenter card-table">
                                        <thead><tr><th>Serie REM</th><th class="text-end">Errores</th></tr></thead>
                                        <tbody id="tableErroresSerie"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 5: Errores por Hoja -->
                            <div id="tab-hoja" class="tab-pane" role="tabpanel">
                                <div class="relative" id="chart5Container" style="height: 400px;">
                                    <div id="chartErroresHoja"></div>
                                </div>
                                <div class="mt-3 overflow-x-auto">
                                    <table class="table table-vcenter card-table">
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
    'tab-errores-est': { canvas: 'chartErroresEst', container: 'chart1Container', table: 'tableErroresEst', orientation: 'horizontal', color: '#dc2626', label: 'Errores', key: 'errores_establecimiento' },
    'tab-serie': { canvas: 'chartErroresSerie', container: 'chart4Container', table: 'tableErroresSerie', orientation: 'horizontal', color: '#0ea5e9', label: 'Errores', key: 'errores_serie' },
    'tab-hoja': { canvas: 'chartErroresHoja', container: 'chart5Container', table: 'tableErroresHoja', orientation: 'vertical', color: '#10b981', label: 'Errores', key: 'errores_hoja' }
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
            const response = await fetch(`api/locations.php?action=get_establecimientos&comuna_id=${comunaId}`);
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
            console.error('Error al cargar establecimientos:', error);
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
        if (!json.success) { console.error(json.message); return; }

        cachedData = json.data;
        tabDataLoaded = {}; // Reset: all tabs need reload

        // Render active tab immediately
        const activeTab = document.querySelector('.tab-panel.active').id;
        if (activeTab === 'tab-plazos' || activeTab === 'tab-validador') {
            switchTab(activeTab);
        } else {
            renderTabChart(activeTab, cachedData);
            tabDataLoaded[activeTab] = true;
        }

    } catch (e) {
        console.error('Error cargando reportes:', e);
    }
}

// ============================================
// Chart Rendering per Tab
// ============================================

function renderTabChart(tabId, data) {
    const config = TAB_CONFIG[tabId];
    if (!config) return;

    if (typeof destroyChart === 'function') destroyChart(config.canvas);

    const resultData = data[config.key] || [];
    const labels = resultData.map(r => r.nombre_corto || r.nombre || r.codigo_serie || r.codigo_hoja);
    const values = resultData.map(r => parseInt(r.total));

    renderChart(config.canvas, config.container, config.table, config.orientation, labels, values, config.color, config.label);
}

function renderChart(canvasId, containerId, tableId, orientation, labels, values, color, colLabel) {
    const tableBody = document.getElementById(tableId);
    const container = document.getElementById(containerId);

    if (!labels.length || values.every(v => v === 0)) {
        tableBody.innerHTML = '<tr><td colspan="2" class="py-2 text-slate-400 text-center">Sin datos para los filtros seleccionados</td></tr>';
        if (container) container.innerHTML = '<p class="text-slate-400 text-center py-8">Sin datos para los filtros seleccionados</p>';
        return;
    }

    if (container && !document.getElementById(canvasId)) {
        container.innerHTML = `<div id="${canvasId}"></div>`;
    }

    if (labels.length > 10) {
        const extraHeight = (labels.length - 10) * 22;
        container.style.height = (orientation === 'horizontal' ? 400 : 500) + extraHeight + 'px';
    } else {
        container.style.height = '400px';
    }

    if (orientation === 'horizontal') {
        errorCharts[canvasId] = createBarHorizontal(canvasId, labels, values, color);
    } else {
        errorCharts[canvasId] = createBarVertical(canvasId, labels, values, color);
    }

    tableBody.innerHTML = labels.map((l, i) => `
        <tr class="border-b border-slate-100">
            <td class="py-1">${escapeHtml(l)}</td>
            <td class="py-1 text-right font-medium">${values[i]}</td>
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
        if (!json.success) return;
        renderPlazoChart(json.data);
        tabDataLoaded['tab-plazos'] = true;
    } catch (e) {
        console.error('Error cargando reporte plazo agregado:', e);
    }
}

function renderPlazoChart(data) {
    const est = data.establecimientos || [];
    const labels = est.map(e => e.nombre_corto);

    if (typeof destroyChart === 'function') destroyChart('chartPlazoAgregado');

    const container = document.getElementById('chart2Container');
    if (!document.getElementById('chartPlazoAgregado')) {
        container.innerHTML = '<div id="chartPlazoAgregado"></div>';
    }
    if (labels.length > 10) {
        container.style.height = (400 + (labels.length - 10) * 22) + 'px';
    } else {
        container.style.height = '400px';
    }

    if (labels.length === 0) {
        container.innerHTML = '<p class="text-slate-400 text-center py-8">Sin datos para el año seleccionado</p>';
        document.getElementById('tablePlazoResumen').innerHTML = '';
        return;
    }

    errorCharts['chartPlazoAgregado'] = createBarHorizontal('chartPlazoAgregado', labels, est.map(e => parseInt(e.meses_fuera)), '#dc2626');

    document.getElementById('tablePlazoResumen').innerHTML = est.map(e => `
        <tr class="border-b border-slate-100">
            <td class="py-1">${escapeHtml(e.nombre_corto)}</td>
            <td class="py-1 text-right font-medium text-green-600">${e.meses_dentro}</td>
            <td class="py-1 text-right font-medium text-red-600">${e.meses_fuera}</td>
            <td class="py-1 text-right text-slate-500">${e.meses_con_datos}</td>
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
        if (!json.success) return;
        renderValidadorChart(json.data);
        tabDataLoaded['tab-validador'] = true;
    } catch (e) {
        console.error('Error cargando reporte validador agregado:', e);
    }
}

function renderValidadorChart(data) {
    const est = data.establecimientos || [];
    const labels = est.map(e => e.nombre_corto);

    if (typeof destroyChart === 'function') destroyChart('chartValidadorAgregado');

    const container = document.getElementById('chart3Container');
    if (!document.getElementById('chartValidadorAgregado')) {
        container.innerHTML = '<div id="chartValidadorAgregado"></div>';
    }
    if (labels.length > 10) {
        container.style.height = (400 + (labels.length - 10) * 22) + 'px';
    } else {
        container.style.height = '400px';
    }

    if (labels.length === 0) {
        container.innerHTML = '<p class="text-slate-400 text-center py-8">Sin datos para el año seleccionado</p>';
        document.getElementById('tableValidadorResumen').innerHTML = '';
        return;
    }

    errorCharts['chartValidadorAgregado'] = createBarHorizontal('chartValidadorAgregado', labels, est.map(e => parseInt(e.meses_no_usa)), '#94a3b8');

    document.getElementById('tableValidadorResumen').innerHTML = est.map(e => `
        <tr class="border-b border-slate-100">
            <td class="py-1">${escapeHtml(e.nombre_corto)}</td>
            <td class="py-1 text-right font-medium text-blue-600">${e.meses_usa}</td>
            <td class="py-1 text-right font-medium text-slate-500">${e.meses_no_usa}</td>
            <td class="py-1 text-right text-slate-500">${e.meses_con_datos}</td>
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
</script>
