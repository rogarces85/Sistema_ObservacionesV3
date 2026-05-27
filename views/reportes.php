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

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Reportes de Errores REM</h2>
            <p class="text-slate-600">Análisis de errores por establecimiento, plazo, validador, serie y hoja</p>
        </div>
    </div>

    <!-- Filtros estilo supervision.php -->
    <div class="card p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">🔍 Filtros</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="form-label">Año</label>
                <select id="filterYear" class="form-select">
                    <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $currentYear ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div>
                <label class="form-label">Mes</label>
                <select id="filterMes" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($mesesList as $m): ?>
                        <option value="<?php echo $m; ?>"><?php echo $m; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="form-label">Comuna</label>
                <select id="filterComuna" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($comunas as $comuna): ?>
                        <option value="<?php echo $comuna['id']; ?>"><?php echo htmlspecialchars($comuna['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="form-label">Establecimiento</label>
                <select id="filterEstablecimiento" class="form-select" disabled>
                    <option value="">Todos</option>
                </select>
            </div>

            <div class="lg:col-span-4 flex items-end gap-3">
                <button id="btnApplyFilters" class="btn btn-primary">
                    Aplicar Filtros
                </button>
                <button id="btnClearFilters" class="btn btn-secondary">
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    <!-- Nav Tabs -->
    <div class="card overflow-hidden">
        <nav class="report-tabs" role="tablist" aria-label="Reportes de errores">
            <button class="report-tab active" role="tab" aria-selected="true" data-tab="tab-errores-est">
                📊 Total Errores
            </button>
            <button class="report-tab" role="tab" aria-selected="false" data-tab="tab-plazos">
                ⏰ Plazos Entrega
            </button>
            <button class="report-tab" role="tab" aria-selected="false" data-tab="tab-validador">
                🔍 Uso Validador
            </button>
            <button class="report-tab" role="tab" aria-selected="false" data-tab="tab-serie">
                📋 Errores por Serie
            </button>
            <button class="report-tab" role="tab" aria-selected="false" data-tab="tab-hoja">
                📄 Errores por Hoja
            </button>
        </nav>

        <!-- Tab Panels -->
        <div class="tab-panels">
            <!-- Tab 1: Total Errores -->
            <div id="tab-errores-est" class="tab-panel active" role="tabpanel">
                <div class="p-4">
                    <div class="relative" id="chart1Container" style="height: 400px;">
                        <canvas id="chartErroresEst"></canvas>
                    </div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="text-left text-slate-500 border-b"><th class="pb-1 font-medium">Establecimiento</th><th class="pb-1 font-medium text-right">Errores</th></tr></thead>
                            <tbody id="tableErroresEst"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Plazos Entrega -->
            <div id="tab-plazos" class="tab-panel" role="tabpanel" hidden>
                <div class="p-4">
                    <div class="relative" id="chart2Container" style="height: 400px;">
                        <canvas id="chartFueraPlazo"></canvas>
                    </div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="text-left text-slate-500 border-b"><th class="pb-1 font-medium">Establecimiento</th><th class="pb-1 font-medium text-right">Fuera Plazo</th></tr></thead>
                            <tbody id="tableFueraPlazo"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Uso Validador -->
            <div id="tab-validador" class="tab-panel" role="tabpanel" hidden>
                <div class="p-4">
                    <div class="relative" id="chart3Container" style="height: 400px;">
                        <canvas id="chartNoValidador"></canvas>
                    </div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="text-left text-slate-500 border-b"><th class="pb-1 font-medium">Establecimiento</th><th class="pb-1 font-medium text-right">No usa validador</th></tr></thead>
                            <tbody id="tableNoValidador"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 4: Errores por Serie -->
            <div id="tab-serie" class="tab-panel" role="tabpanel" hidden>
                <div class="p-4">
                    <div class="relative" id="chart4Container" style="height: 400px;">
                        <canvas id="chartErroresSerie"></canvas>
                    </div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="text-left text-slate-500 border-b"><th class="pb-1 font-medium">Serie REM</th><th class="pb-1 font-medium text-right">Errores</th></tr></thead>
                            <tbody id="tableErroresSerie"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 5: Errores por Hoja -->
            <div id="tab-hoja" class="tab-panel" role="tabpanel" hidden>
                <div class="p-4">
                    <div class="relative" id="chart5Container" style="height: 400px;">
                        <canvas id="chartErroresHoja"></canvas>
                    </div>
                    <div class="mt-3 overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="text-left text-slate-500 border-b"><th class="pb-1 font-medium">Hoja REM</th><th class="pb-1 font-medium text-right">Errores</th></tr></thead>
                            <tbody id="tableErroresHoja"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos de Nav Tabs */
.report-tabs {
    display: flex;
    border-bottom: 2px solid #e2e8f0;
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
    background: #f8fafc;
}
.report-tabs::-webkit-scrollbar {
    display: none;
}

.report-tab {
    flex: 1;
    min-width: 140px;
    padding: 1rem 1.25rem;
    border: none;
    background: transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
    white-space: nowrap;
    text-align: center;
}

.report-tab:hover {
    color: #334155;
    background: #f1f5f9;
}

.report-tab.active {
    color: #0ea5e9;
    border-bottom-color: #0ea5e9;
    background: #fff;
}

.report-tab:focus {
    outline: 2px solid #0ea5e9;
    outline-offset: -2px;
}

/* Tab Panels */
.tab-panels {
    background: #fff;
}

.tab-panel {
    display: none;
}

.tab-panel.active {
    display: block;
}

/* Responsive */
@media (max-width: 768px) {
    .report-tab {
        min-width: 120px;
        padding: 0.75rem 0.5rem;
        font-size: 0.8rem;
    }
}
</style>

<script>
let errorCharts = {};
let tabDataLoaded = {};
let cachedData = null;

const TAB_CONFIG = {
    'tab-errores-est': { canvas: 'chartErroresEst', container: 'chart1Container', table: 'tableErroresEst', orientation: 'horizontal', color: '#dc2626', label: 'Errores', key: 'errores_establecimiento' },
    'tab-plazos': { canvas: 'chartFueraPlazo', container: 'chart2Container', table: 'tableFueraPlazo', orientation: 'vertical', color: '#f59e0b', label: 'Fuera Plazo', key: 'fuera_plazo_establecimiento' },
    'tab-validador': { canvas: 'chartNoValidador', container: 'chart3Container', table: 'tableNoValidador', orientation: 'vertical', color: '#6366f1', label: 'No usa validador', key: 'no_validador_establecimiento' },
    'tab-serie': { canvas: 'chartErroresSerie', container: 'chart4Container', table: 'tableErroresSerie', orientation: 'horizontal', color: '#0ea5e9', label: 'Errores', key: 'errores_serie' },
    'tab-hoja': { canvas: 'chartErroresHoja', container: 'chart5Container', table: 'tableErroresHoja', orientation: 'vertical', color: '#10b981', label: 'Errores', key: 'errores_hoja' }
};

// ============================================
// Tab Switching
// ============================================

function switchTab(tabId) {
    // Update tab buttons
    document.querySelectorAll('.report-tab').forEach(tab => {
        const isActive = tab.dataset.tab === tabId;
        tab.classList.toggle('active', isActive);
        tab.setAttribute('aria-selected', isActive);
    });

    // Update tab panels
    document.querySelectorAll('.tab-panel').forEach(panel => {
        const isActive = panel.id === tabId;
        panel.classList.toggle('active', isActive);
        panel.hidden = !isActive;
    });

    // Update URL hash
    location.hash = tabId;

    // Lazy load: load data if not loaded yet
    if (!tabDataLoaded[tabId] && cachedData) {
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

async function loadErrorReports() {
    const year = document.getElementById('filterYear').value;
    const mes = document.getElementById('filterMes').value;
    const comunaId = document.getElementById('filterComuna').value;
    const establecimientoId = document.getElementById('filterEstablecimiento').value;

    let url = `api/reports.php?report=error-reports&year=${year}`;
    if (mes) url += `&meses[]=${encodeURIComponent(mes)}`;
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
        renderTabChart(activeTab, cachedData);
        tabDataLoaded[activeTab] = true;

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

    // Destroy existing chart for this tab
    if (errorCharts[config.canvas]) {
        errorCharts[config.canvas].destroy();
        delete errorCharts[config.canvas];
    }

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

    // Restaurar canvas si fue reemplazado
    if (container && !document.getElementById(canvasId)) {
        container.innerHTML = `<canvas id="${canvasId}"></canvas>`;
    }

    // Altura dinámica
    if (labels.length > 10) {
        const extraHeight = (labels.length - 10) * 22;
        container.style.height = (orientation === 'horizontal' ? 400 : 500) + extraHeight + 'px';
    } else {
        container.style.height = '400px';
    }

    // Create chart
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
// Event Listeners
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Tab clicks
    document.querySelectorAll('.report-tab').forEach(tab => {
        tab.addEventListener('click', () => switchTab(tab.dataset.tab));
    });

    // Filters
    document.getElementById('filterComuna').addEventListener('change', loadEstablecimientos);
    document.getElementById('btnApplyFilters').addEventListener('click', loadErrorReports);
    document.getElementById('btnClearFilters').addEventListener('click', clearFilters);

    // Restore tab from hash
    const hashTab = location.hash.replace('#', '');
    if (hashTab && TAB_CONFIG[hashTab]) {
        switchTab(hashTab);
    }

    // Initial load
    loadErrorReports();
});

function clearFilters() {
    document.getElementById('filterYear').value = '<?php echo $currentYear; ?>';
    document.getElementById('filterMes').value = '';
    document.getElementById('filterComuna').value = '';
    document.getElementById('filterEstablecimiento').innerHTML = '<option value="">Todos</option>';
    document.getElementById('filterEstablecimiento').disabled = true;
    loadErrorReports();
}
</script>
