<?php
/**
 * Vista de Reportes
 * Dashboard de reportes interactivos con gráficos y tablas
 */

$currentYear = $_SESSION['year'] ?? date('Y');
$userRole = $_SESSION['rol'];
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Reportes y Estadísticas</h2>
            <p class="text-slate-600">Análisis detallado de observaciones REM — Año <span id="reportYearLabel"><?php echo $currentYear; ?></span></p>
        </div>
        <div class="flex gap-3 items-center">
            <label class="text-sm font-semibold text-slate-700">Año:</label>
            <select id="reportYearSelector" class="form-select w-28" onchange="changeReportYear(this.value)">
                <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $currentYear ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
            <button onclick="exportData('excel')" class="btn btn-primary text-sm">
                📊 Excel
            </button>
        </div>
    </div>

    <!-- KPIs Globales -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="kpiContainer">
        <div class="card p-4 text-center">
            <div class="text-3xl font-bold text-slate-800" id="kpiTotal">—</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Total Observaciones</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-3xl font-bold text-rose-600" id="kpiFueraPlazo">—</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Fuera de Plazo</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-3xl font-bold text-sky-600" id="kpiConValidador">—</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Usan Validador</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-3xl font-bold text-emerald-600" id="kpiSeries">—</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Series REM</div>
        </div>
    </div>

    <!-- Fila 1: Por Mes + Por Comuna -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Reporte por Mes -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span>📅</span> Errores por Mes
                </h3>
                <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-2 py-1 rounded">Barras</span>
            </div>
            <div class="relative" style="height: 300px;">
                <canvas id="chartMes"></canvas>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="pb-2 font-medium">Mes</th>
                            <th class="pb-2 font-medium text-right">Total</th>
                            <th class="pb-2 font-medium text-right">%</th>
                        </tr>
                    </thead>
                    <tbody id="tableMes"></tbody>
                </table>
            </div>
        </div>

        <!-- Reporte por Comuna -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span>📍</span> Errores por Comuna
                </h3>
                <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-2 py-1 rounded">Dona</span>
            </div>
            <div class="relative" style="height: 300px;">
                <canvas id="chartComuna"></canvas>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="pb-2 font-medium">Comuna</th>
                            <th class="pb-2 font-medium text-right">Total</th>
                            <th class="pb-2 font-medium text-right">%</th>
                        </tr>
                    </thead>
                    <tbody id="tableComuna"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Fila 2: Por Establecimiento (ancho completo) -->
    <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <span>🏥</span> Errores por Establecimiento
            </h3>
            <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-2 py-1 rounded">Barras Horizontales</span>
        </div>
        <div class="relative" style="height: 360px;">
            <canvas id="chartEstablecimiento"></canvas>
        </div>
        <div class="mt-4 overflow-x-auto max-h-60">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-slate-500 border-b border-slate-200">
                        <th class="pb-2 font-medium">Establecimiento</th>
                        <th class="pb-2 font-medium text-right">Total</th>
                        <th class="pb-2 font-medium text-right">%</th>
                    </tr>
                </thead>
                <tbody id="tableEstablecimiento"></tbody>
            </table>
        </div>
    </div>

    <!-- Fila 3: Por Serie REM + Por Plazo -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Reporte por Serie REM -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span>📄</span> Errores por Serie REM
                </h3>
                <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-2 py-1 rounded">Barras</span>
            </div>
            <div class="relative" style="height: 300px;">
                <canvas id="chartSerie"></canvas>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="pb-2 font-medium">Serie</th>
                            <th class="pb-2 font-medium text-right">Total</th>
                            <th class="pb-2 font-medium text-right">%</th>
                        </tr>
                    </thead>
                    <tbody id="tableSerie"></tbody>
                </table>
            </div>
        </div>

        <!-- Reporte por Plazo de Entrega -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span>⏱️</span> Errores por Plazo de Entrega
                </h3>
                <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-2 py-1 rounded">Dona</span>
            </div>
            <div class="relative" style="height: 300px;">
                <canvas id="chartPlazo"></canvas>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="pb-2 font-medium">Plazo</th>
                            <th class="pb-2 font-medium text-right">Total</th>
                            <th class="pb-2 font-medium text-right">%</th>
                        </tr>
                    </thead>
                    <tbody id="tablePlazo"></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Fila 4: Por Validador (ancho completo) -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <span>✅</span> Errores por Uso de Validador
                </h3>
                <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-2 py-1 rounded">Dona</span>
            </div>
            <div class="relative" style="height: 300px;">
                <canvas id="chartValidador"></canvas>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500 border-b border-slate-200">
                            <th class="pb-2 font-medium">Usa Validador</th>
                            <th class="pb-2 font-medium text-right">Total</th>
                            <th class="pb-2 font-medium text-right">%</th>
                        </tr>
                    </thead>
                    <tbody id="tableValidador"></tbody>
                </table>
            </div>
        </div>

        <!-- Resumen/exportación -->
        <div class="card p-6 flex flex-col justify-center">
            <h3 class="text-lg font-bold text-slate-800 mb-4">📥 Exportar Datos</h3>
            <p class="text-sm text-slate-600 mb-6">
                Descargue el reporte completo del año seleccionado en formato Excel o PDF con todos los filtros aplicados.
            </p>
            <div class="flex flex-wrap gap-3">
                <button onclick="exportData('excel')" class="btn btn-primary flex-1">
                    📊 Exportar Excel
                </button>
                <button onclick="exportData('pdf')" class="btn btn-secondary flex-1">
                    📄 Exportar PDF
                </button>
            </div>
            <div class="mt-6 p-4 bg-slate-50 rounded-lg text-xs text-slate-500">
                <p><strong>Nota:</strong> Los reportes respetan su perfil de usuario. Como <?php echo $userRole === ROL_SUPERVISOR ? 'supervisor' : 'registrador'; ?>, 
                <?php echo $userRole === ROL_SUPERVISOR ? 've todos los datos del sistema.' : 'solo se muestran las observaciones registradas por usted.'; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    let charts = {};
    let currentYear = <?php echo $currentYear; ?>;

    // Paleta de colores moderna
    const PALETTE = [
        '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
        '#ec4899', '#14b8a6', '#f97316', '#06b6d4', '#84cc16',
        '#6366f1', '#d946ef', '#22c55e', '#eab308', '#3b82f6'
    ];

    const PLAZO_COLORS = { 'dentro_plazo': '#10b981', 'fuera_plazo': '#ef4444' };
    const VALIDADOR_COLORS = { 'si': '#0ea5e9', 'no': '#94a3b8' };

    document.addEventListener('DOMContentLoaded', function () {
        loadAllReports();
    });

    async function loadAllReports() {
        try {
            showLoadingAll();
            const response = await fetch(`api/reports.php?report=all&year=${currentYear}`);
            const result = await response.json();

            if (result.success) {
                renderKPIs(result.data);
                renderMes(result.data.mes);
                renderComuna(result.data.comuna);
                renderEstablecimiento(result.data.establecimiento);
                renderSerie(result.data.serie);
                renderPlazo(result.data.plazo);
                renderValidador(result.data.validador);
            } else {
                showError(result.message || 'Error al cargar reportes');
            }
        } catch (error) {
            console.error(error);
            showError('Error al cargar reportes');
        }
    }

    function showLoadingAll() {
        document.getElementById('kpiTotal').textContent = '—';
        document.getElementById('kpiFueraPlazo').textContent = '—';
        document.getElementById('kpiConValidador').textContent = '—';
        document.getElementById('kpiSeries').textContent = '—';
    }

    function renderKPIs(data) {
        const total = (data.mes || []).reduce((sum, m) => sum + parseInt(m.total), 0);
        const fueraPlazo = (data.plazo || []).find(p => p.plazo_entrega === 'fuera_plazo');
        const conValidador = (data.validador || []).find(v => v.usa_validador === 'si');
        const seriesCount = (data.serie || []).length;

        document.getElementById('kpiTotal').textContent = total;
        document.getElementById('kpiFueraPlazo').textContent = fueraPlazo ? fueraPlazo.total : 0;
        document.getElementById('kpiConValidador').textContent = conValidador ? conValidador.total : 0;
        document.getElementById('kpiSeries').textContent = seriesCount;
    }

    function renderTable(tbodyId, data, labelKey, totalGlobal) {
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-slate-400 py-3">Sin datos</td></tr>';
            return;
        }
        const total = totalGlobal || data.reduce((sum, r) => sum + parseInt(r.total), 0);
        data.forEach(row => {
            const val = parseInt(row.total);
            const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
            const label = row[labelKey] || 'Sin especificar';
            tbody.innerHTML += `
                <tr class="border-b border-slate-50 hover:bg-slate-50">
                    <td class="py-2 text-slate-700">${escapeHtml(label)}</td>
                    <td class="py-2 text-right font-semibold text-slate-800">${val}</td>
                    <td class="py-2 text-right text-slate-500">${pct}%</td>
                </tr>
            `;
        });
    }

    // ---------- Reporte por Mes ----------
    function renderMes(data) {
        const labels = data.map(d => d.mes);
        const values = data.map(d => parseInt(d.total));
        const total = values.reduce((a, b) => a + b, 0);

        renderTable('tableMes', data, 'mes', total);

        if (charts.mes) charts.mes.destroy();
        charts.mes = new Chart(document.getElementById('chartMes'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Observaciones',
                    data: values,
                    backgroundColor: '#0ea5e9',
                    borderRadius: 6,
                    barThickness: 'flex',
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }

    // ---------- Reporte por Comuna ----------
    function renderComuna(data) {
        const labels = data.map(d => d.nombre);
        const values = data.map(d => parseInt(d.total));
        const total = values.reduce((a, b) => a + b, 0);

        renderTable('tableComuna', data, 'nombre', total);

        if (charts.comuna) charts.comuna.destroy();
        charts.comuna = new Chart(document.getElementById('chartComuna'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: PALETTE.slice(0, data.length),
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
                }
            }
        });
    }

    // ---------- Reporte por Establecimiento ----------
    function renderEstablecimiento(data) {
        // Limitar a top 15 para legibilidad del gráfico
        const topData = data.slice(0, 15);
        const labels = topData.map(d => d.nombre_corto || d.nombre);
        const values = topData.map(d => parseInt(d.total));
        const total = data.reduce((a, b) => a + parseInt(b.total), 0);

        renderTable('tableEstablecimiento', data, 'nombre', total);

        if (charts.establecimiento) charts.establecimiento.destroy();
        charts.establecimiento = new Chart(document.getElementById('chartEstablecimiento'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Observaciones',
                    data: values,
                    backgroundColor: '#10b981',
                    borderRadius: 6,
                    barThickness: 'flex',
                    maxBarThickness: 24
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' } },
                    y: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }

    // ---------- Reporte por Serie REM ----------
    function renderSerie(data) {
        const labels = data.map(d => d.codigo_serie);
        const values = data.map(d => parseInt(d.total));
        const total = values.reduce((a, b) => a + b, 0);

        renderTable('tableSerie', data, 'codigo_serie', total);

        if (charts.serie) charts.serie.destroy();
        charts.serie = new Chart(document.getElementById('chartSerie'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Observaciones',
                    data: values,
                    backgroundColor: '#8b5cf6',
                    borderRadius: 6,
                    barThickness: 'flex',
                    maxBarThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' } },
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    }

    // ---------- Reporte por Plazo ----------
    function renderPlazo(data) {
        const labels = data.map(d => d.plazo_entrega === 'fuera_plazo' ? 'Fuera de Plazo' : 'Dentro de Plazo');
        const values = data.map(d => parseInt(d.total));
        const total = values.reduce((a, b) => a + b, 0);
        const colors = data.map(d => PLAZO_COLORS[d.plazo_entrega] || '#94a3b8');

        renderTable('tablePlazo', data.map(d => ({...d, plazo_entrega: d.plazo_entrega === 'fuera_plazo' ? 'Fuera de Plazo' : 'Dentro de Plazo'})), 'plazo_entrega', total);

        if (charts.plazo) charts.plazo.destroy();
        charts.plazo = new Chart(document.getElementById('chartPlazo'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
                }
            }
        });
    }

    // ---------- Reporte por Validador ----------
    function renderValidador(data) {
        const labels = data.map(d => d.usa_validador === 'si' ? 'Sí usa validador' : 'No usa validador');
        const values = data.map(d => parseInt(d.total));
        const total = values.reduce((a, b) => a + b, 0);
        const colors = data.map(d => VALIDADOR_COLORS[d.usa_validador] || '#94a3b8');

        renderTable('tableValidador', data.map(d => ({...d, usa_validador: d.usa_validador === 'si' ? 'Sí' : 'No'})), 'usa_validador', total);

        if (charts.validador) charts.validador.destroy();
        charts.validador = new Chart(document.getElementById('chartValidador'), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } }
                }
            }
        });
    }

    function changeReportYear(year) {
        currentYear = parseInt(year);
        document.getElementById('reportYearLabel').textContent = currentYear;
        loadAllReports();
    }

    function exportData(format) {
        const params = new URLSearchParams();
        params.append('format', format);
        params.append('year', currentYear);
        window.location.href = 'api/export.php?' + params.toString();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }
</script>
