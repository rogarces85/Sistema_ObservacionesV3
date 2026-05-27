<?php
/**
 * Vista de Reportes de Errores REM
 * 5 gráficos temáticos con filtros multi-select (año, meses, comunas)
 */

$currentYear = $_SESSION['year'] ?? date('Y');
$userRole = $_SESSION['rol'];
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Reportes de Errores REM</h2>
            <p class="text-slate-600">Análisis de errores por establecimiento, plazo, validador, serie y hoja</p>
        </div>
        <div class="flex gap-3 items-center">
            <label class="text-sm font-semibold text-slate-700">Año:</label>
            <select id="reportYear" class="form-select w-28" onchange="loadErrorReports()">
                <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $currentYear ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card p-4">
        <div class="flex flex-wrap gap-6 items-start">
            <div>
                <label class="text-sm font-semibold text-slate-700 block mb-2">Meses:</label>
                <div class="flex flex-wrap gap-2" id="mesesCheckboxes">
                    <?php
                    $mesesList = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                    foreach ($mesesList as $m): ?>
                        <label class="inline-flex items-center text-sm cursor-pointer">
                            <input type="checkbox" value="<?php echo $m; ?>" class="mes-filter rounded border-slate-300" checked onchange="loadErrorReports()">
                            <span class="ml-1"><?php echo substr($m, 0, 3); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="flex flex-wrap gap-1 mt-2">
                    <button type="button" onclick="selectQuarter('Q1', '.mes-filter', loadErrorReports)" class="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Q1</button>
                    <button type="button" onclick="selectQuarter('Q2', '.mes-filter', loadErrorReports)" class="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Q2</button>
                    <button type="button" onclick="selectQuarter('Q3', '.mes-filter', loadErrorReports)" class="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Q3</button>
                    <button type="button" onclick="selectQuarter('Q4', '.mes-filter', loadErrorReports)" class="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Q4</button>
                    <button type="button" onclick="selectSemester('H1', '.mes-filter', loadErrorReports)" class="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">H1</button>
                    <button type="button" onclick="selectSemester('H2', '.mes-filter', loadErrorReports)" class="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">H2</button>
                    <button type="button" onclick="selectAllMonths('.mes-filter', loadErrorReports)" class="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Todos</button>
                </div>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700 block mb-2">Comunas:</label>
                <div class="flex flex-wrap gap-2" id="comunasCheckboxes">
                    <span class="text-sm text-slate-400">Cargando...</span>
                </div>
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

function getSelectedMeses() {
    return Array.from(document.querySelectorAll('.mes-filter:checked')).map(cb => cb.value);
}

function getSelectedComunas() {
    return Array.from(document.querySelectorAll('.comuna-filter:checked')).map(cb => cb.value);
}

async function loadComunas() {
    try {
        const year = document.getElementById('reportYear').value;
        const resp = await fetch(`api/reports.php?report=filtros&year=${year}`);
        const data = await resp.json();
        if (data.success && data.data.comunas) {
            const container = document.getElementById('comunasCheckboxes');
            container.innerHTML = data.data.comunas.map(c => `
                <label class="inline-flex items-center text-sm cursor-pointer">
                    <input type="checkbox" value="${c.id}" class="comuna-filter rounded border-slate-300" checked onchange="loadErrorReports()">
                    <span class="ml-1">${escapeHtml(c.nombre)}</span>
                </label>
            `).join('');
        }
    } catch (e) {
        console.error('Error cargando comunas:', e);
    }
}

async function loadErrorReports() {
    const year = document.getElementById('reportYear').value;
    const meses = getSelectedMeses();
    const comunas = getSelectedComunas();

    let url = `api/reports.php?report=error-reports&year=${year}`;
    meses.forEach(m => url += `&meses[]=${encodeURIComponent(m)}`);
    comunas.forEach(c => url += `&comuna_ids[]=${c}`);

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

document.addEventListener('DOMContentLoaded', () => {
    loadComunas();
    loadErrorReports();
});
</script>
