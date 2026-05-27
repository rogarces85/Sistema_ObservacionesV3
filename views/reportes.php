<?php
/**
 * Vista de Reportes de Errores REM
 * 5 gráficos temáticos con filtros tipo supervision.php
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

    <!-- Gráficos ①-④: Grid 2 columnas -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- ① Errores por Establecimiento -->
        <div class="card p-4 border border-slate-200">
            <h3 class="text-lg font-bold text-slate-800 mb-3">① Errores por Establecimiento</h3>
            <div class="relative" id="chart1Container" style="height: 300px;">
                <canvas id="chartErroresEst"></canvas>
            </div>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-1 font-medium">Establecimiento</th><th class="pb-1 font-medium text-right">Errores</th></tr></thead><tbody id="tableErroresEst"></tbody></table>
            </div>
        </div>

        <!-- ② Fuera de Plazo por Establecimiento -->
        <div class="card p-4 border border-slate-200">
            <h3 class="text-lg font-bold text-slate-800 mb-3">② Fuera de Plazo por Establecimiento</h3>
            <div class="relative" id="chart2Container" style="height: 300px;">
                <canvas id="chartFueraPlazo"></canvas>
            </div>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-1 font-medium">Establecimiento</th><th class="pb-1 font-medium text-right">Fuera Plazo</th></tr></thead><tbody id="tableFueraPlazo"></tbody></table>
            </div>
        </div>

        <!-- ③ No usa Validador por Establecimiento -->
        <div class="card p-4 border border-slate-200">
            <h3 class="text-lg font-bold text-slate-800 mb-3">③ No usa Validador por Establecimiento</h3>
            <div class="relative" id="chart3Container" style="height: 300px;">
                <canvas id="chartNoValidador"></canvas>
            </div>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-1 font-medium">Establecimiento</th><th class="pb-1 font-medium text-right">No usa validador</th></tr></thead><tbody id="tableNoValidador"></tbody></table>
            </div>
        </div>

        <!-- ④ Errores por Serie REM -->
        <div class="card p-4 border border-slate-200">
            <h3 class="text-lg font-bold text-slate-800 mb-3">④ Errores por Serie REM</h3>
            <div class="relative" id="chart4Container" style="height: 300px;">
                <canvas id="chartErroresSerie"></canvas>
            </div>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-1 font-medium">Serie REM</th><th class="pb-1 font-medium text-right">Errores</th></tr></thead><tbody id="tableErroresSerie"></tbody></table>
            </div>
        </div>
    </div>

    <!-- ⑤ Errores por Hoja REM: Ancho completo -->
    <div class="card p-4 border border-slate-200">
        <h3 class="text-lg font-bold text-slate-800 mb-3">⑤ Errores por Hoja REM</h3>
        <div class="relative" id="chart5Container" style="height: 400px;">
            <canvas id="chartErroresHoja"></canvas>
        </div>
        <div class="mt-3 overflow-x-auto">
            <table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-1 font-medium">Hoja REM</th><th class="pb-1 font-medium text-right">Errores</th></tr></thead><tbody id="tableErroresHoja"></tbody></table>
        </div>
    </div>
</div>

<script>
let errorCharts = {};

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

        const d = json.data;

        renderChart('chartErroresEst', 'chart1Container', 'tableErroresEst', 'bar', 'horizontal',
            (d.errores_establecimiento || []).map(r => r.nombre_corto || r.nombre),
            (d.errores_establecimiento || []).map(r => parseInt(r.total)),
            '#dc2626', 'Errores');

        renderChart('chartFueraPlazo', 'chart2Container', 'tableFueraPlazo', 'bar', 'vertical',
            (d.fuera_plazo_establecimiento || []).map(r => r.nombre_corto || r.nombre),
            (d.fuera_plazo_establecimiento || []).map(r => parseInt(r.total)),
            '#f59e0b', 'Fuera Plazo');

        renderChart('chartNoValidador', 'chart3Container', 'tableNoValidador', 'bar', 'vertical',
            (d.no_validador_establecimiento || []).map(r => r.nombre_corto || r.nombre),
            (d.no_validador_establecimiento || []).map(r => parseInt(r.total)),
            '#6366f1', 'No usa validador');

        renderChart('chartErroresSerie', 'chart4Container', 'tableErroresSerie', 'bar', 'horizontal',
            (d.errores_serie || []).map(r => r.codigo_serie),
            (d.errores_serie || []).map(r => parseInt(r.total)),
            '#0ea5e9', 'Errores');

        renderChart('chartErroresHoja', 'chart5Container', 'tableErroresHoja', 'bar', 'vertical',
            (d.errores_hoja || []).map(r => r.codigo_hoja),
            (d.errores_hoja || []).map(r => parseInt(r.total)),
            '#10b981', 'Errores');

    } catch (e) {
        console.error('Error cargando reportes:', e);
    }
}

function renderChart(canvasId, containerId, tableId, type, orientation, labels, values, color, colLabel) {
    Object.values(errorCharts).forEach(c => c.destroy());
    errorCharts = {};

    const tableBody = document.getElementById(tableId);
    const container = document.getElementById(containerId);

    if (!labels.length || values.every(v => v === 0)) {
        tableBody.innerHTML = '<tr><td colspan="2" class="py-2 text-slate-400 text-center">Sin datos para los filtros seleccionados</td></tr>';
        if (container) container.innerHTML = '<p class="text-slate-400 text-center py-8">Sin datos para los filtros seleccionados</p>';
        return;
    }

    // Restaurar canvas si fue reemplazado por mensaje de sin datos
    if (container && !document.getElementById(canvasId)) {
        container.innerHTML = `<canvas id="${canvasId}"></canvas>`;
    }

    // Altura dinámica para más de 10 items
    if (labels.length > 10) {
        const extraHeight = (labels.length - 10) * 22;
        container.style.height = (orientation === 'horizontal' ? 300 : 400) + extraHeight + 'px';
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

// Event listeners
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('filterComuna').addEventListener('change', loadEstablecimientos);
    document.getElementById('btnApplyFilters').addEventListener('click', loadErrorReports);
    document.getElementById('btnClearFilters').addEventListener('click', clearFilters);

    // Carga inicial
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
