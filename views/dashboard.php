<?php
/**
 * Dashboard - Panel de Control
 * Muestra estadísticas y resumen del sistema
 */

require_once 'models/Observation.php';
require_once 'models/EstablecimientoAsignacion.php';

$obsModel = new Observation();
$asigModel = new EstablecimientoAsignacion();
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['rol'];
$currentYear = $_SESSION['year'] ?? date('Y');

// Verificar asignaciones
$tieneAsignaciones = false;
$registradoresSinAsignaciones = [];

if ($userRole === ROL_REGISTRADOR) {
    $tieneAsignaciones = $asigModel->tieneAsignaciones($userId, $currentYear);
} elseif ($userRole === ROL_SUPERVISOR) {
    $registradoresSinAsignaciones = $asigModel->getRegistradoresSinAsignaciones($currentYear);
}

// Obtener estadísticas para Dashboard (formato comparativo)
$defaultYears = [$currentYear, $currentYear - 1];
$stats = $obsModel->getDashboardStats($defaultYears, [], $userId, $userRole);
$dashboardStatsJson = json_encode($stats);

// Obtener últimas observaciones
$recentObs = $obsModel->getAll($currentYear, $userId, $userRole);
$recentObs = array_slice($recentObs, 0, 5); // Solo las últimas 5

// Calcular estadísticas por estado
$pendientes = 0;
$aprobados = 0;
$problemas = 0;
$justificados = 0;

foreach ($stats['por_estado'] as $estado) {
    switch ($estado['estado_actual']) {
        case ESTADO_PENDIENTE:
            $pendientes = $estado['total'];
            break;
        case ESTADO_APROBADO:
            $aprobados = $estado['total'];
            break;
        case ESTADO_RECHAZADO:
        case ESTADO_ERROR:
            $problemas += $estado['total'];
            break;
        case ESTADO_JUSTIFICADO:
            $justificados = $estado['total'];
            break;
    }
}

global $MESES;
?>

<div class="space-y-6">
    <!-- Header con título y acciones rápidas -->
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Panel de Control <?php echo $currentYear; ?></h2>
            <p class="text-slate-600">Resumen estadístico del sistema de observaciones REM</p>
        </div>
        <div class="flex gap-2">
            <?php if ($userRole === ROL_REGISTRADOR): ?>
                <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="btn btn-primary">
                    📝 Nueva Observación
                </a>
            <?php endif; ?>
            <?php if ($userRole === ROL_SUPERVISOR): ?>
                <a href="?page=supervision&year=<?php echo $currentYear; ?>" class="btn btn-secondary">
                    👁️ Supervisar
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alertas de asignaciones -->
    <?php if ($userRole === ROL_REGISTRADOR && !$tieneAsignaciones): ?>
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-4">
            <div class="text-2xl">⚠️</div>
            <div class="flex-1">
                <p class="font-bold text-amber-800">No tiene establecimientos asignados</p>
                <p class="text-sm text-amber-700">
                    No tiene establecimientos asignados para el año <strong><?php echo $currentYear; ?></strong>. 
                    Contacte a su supervisor para que le asigne los establecimientos correspondientes.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($userRole === ROL_SUPERVISOR && !empty($registradoresSinAsignaciones)): ?>
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-4">
            <div class="text-2xl">🚨</div>
            <div class="flex-1">
                <p class="font-bold text-rose-800">
                    <?php echo count($registradoresSinAsignaciones); ?> registrador(es) sin establecimientos asignados
                </p>
                <p class="text-sm text-rose-700 mb-2">
                    Los siguientes registradores no tienen establecimientos asignados para el año 
                    <strong><?php echo $currentYear; ?></strong>:
                </p>
                <ul class="text-sm text-rose-700 list-disc list-inside">
                    <?php foreach ($registradoresSinAsignaciones as $reg): ?>
                        <li><?php echo htmlspecialchars($reg['nombre_completo']); ?> (<?php echo htmlspecialchars($reg['username']); ?>)</li>
                    <?php endforeach; ?>
                </ul>
                <a href="?page=asignaciones&year=<?php echo $currentYear; ?>" class="inline-block mt-2 text-sm font-semibold text-rose-800 hover:underline">
                    → Ir a Asignación de Establecimientos
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Cards de estadísticas principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total de observaciones -->
        <div class="card p-5"
            style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-color: #bae6fd;">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
                    <span class="text-2xl">📊</span>
                </div>
                <div>
                    <div class="text-3xl font-bold text-slate-800"><?php echo $stats['total']; ?></div>
                    <div class="text-sm font-semibold text-slate-600">Total Registradas</div>
                </div>
            </div>
        </div>

        <!-- Pendientes -->
        <div class="card p-5"
            style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-color: #fde68a;">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <span class="text-2xl">⏳</span>
                </div>
                <div>
                    <div class="text-3xl font-bold text-amber-700"><?php echo $pendientes; ?></div>
                    <div class="text-sm font-semibold text-amber-600">Pendientes</div>
                </div>
            </div>
        </div>

        <!-- Aprobados -->
        <div class="card p-5"
            style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-color: #a7f3d0;">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <span class="text-2xl">✅</span>
                </div>
                <div>
                    <div class="text-3xl font-bold text-emerald-700"><?php echo $aprobados; ?></div>
                    <div class="text-sm font-semibold text-emerald-600">Aprobados</div>
                </div>
            </div>
        </div>

        <!-- Con Problemas -->
        <div class="card p-5"
            style="background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%); border-color: #fecdd3;">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);">
                    <span class="text-2xl">⚠️</span>
                </div>
                <div>
                    <div class="text-3xl font-bold text-rose-700"><?php echo $problemas; ?></div>
                    <div class="text-sm font-semibold text-rose-600">Con Problemas</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros del Dashboard -->
    <div class="card p-4">
        <div class="flex flex-wrap gap-6 items-start">
            <div>
                <label class="text-sm font-semibold text-slate-700 block mb-2">Años (comparativo):</label>
                <div class="flex flex-wrap gap-2" id="dashboardYearFilters">
                    <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                        <label class="inline-flex items-center text-sm cursor-pointer">
                            <input type="checkbox" value="<?php echo $y; ?>" class="dashboard-year-filter rounded border-slate-300"
                                <?php echo ($y == $currentYear || $y == $currentYear - 1) ? 'checked' : ''; ?>
                                onchange="loadDashboardStats()">
                            <span class="ml-1"><?php echo $y; ?></span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>
            <div>
                <label class="text-sm font-semibold text-slate-700 block mb-2">Meses:</label>
                <div class="flex flex-wrap gap-2 mb-2" id="dashboardMesesCheckboxes">
                    <?php foreach ($MESES as $m): ?>
                        <label class="inline-flex items-center text-sm cursor-pointer">
                            <input type="checkbox" value="<?php echo $m; ?>" class="dashboard-mes-filter rounded border-slate-300" checked onchange="loadDashboardStats()">
                            <span class="ml-1"><?php echo substr($m, 0, 3); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="flex flex-wrap gap-1">
                    <button type="button" onclick="selectDashboardQuarter('Q1')" class="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Q1</button>
                    <button type="button" onclick="selectDashboardQuarter('Q2')" class="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Q2</button>
                    <button type="button" onclick="selectDashboardQuarter('Q3')" class="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Q3</button>
                    <button type="button" onclick="selectDashboardQuarter('Q4')" class="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Q4</button>
                    <button type="button" onclick="selectDashboardSemester('H1')" class="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">H1</button>
                    <button type="button" onclick="selectDashboardSemester('H2')" class="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">H2</button>
                    <button type="button" onclick="selectDashboardAllMonths()" class="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium">Todos</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Distribución por Estado (D1) -->
        <div class="card p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span>📈</span> Distribución por Estado
            </h3>
            <div class="relative" style="height: 240px;">
                <canvas id="chartDashboardEstado"></canvas>
                <p id="msgDashboardEstado" class="hidden text-slate-400 text-center py-20">Sin datos para los filtros seleccionados</p>
            </div>
        </div>

        <!-- Top Tipos de Error (D2) -->
        <div class="card p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span>🔍</span> Top Tipos de Error
            </h3>
            <div class="relative" style="height: 240px;">
                <canvas id="chartDashboardTipos"></canvas>
                <p id="msgDashboardTipos" class="hidden text-slate-400 text-center py-20">Sin datos para los filtros seleccionados</p>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="card p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span>⚡</span> Acciones Rápidas
            </h3>
            <div class="space-y-3">
                <?php if ($userRole === ROL_REGISTRADOR): ?>
                    <a href="?page=observaciones&year=<?php echo $currentYear; ?>"
                        class="flex items-center gap-3 p-4 rounded-xl bg-sky-50 hover:bg-sky-100 transition-all cursor-pointer group">
                        <div class="p-2 rounded-lg bg-sky-500 text-white">📝</div>
                        <div class="flex-1">
                            <p class="font-semibold text-slate-800 group-hover:text-sky-700">Nueva Observación</p>
                            <p class="text-xs text-slate-500">Registrar una nueva observación</p>
                        </div>
                        <span class="text-sky-500">→</span>
                    </a>
                <?php endif; ?>

                <a href="api/import_template.php"
                    class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 hover:bg-emerald-100 transition-all cursor-pointer group">
                    <div class="p-2 rounded-lg bg-emerald-500 text-white">📥</div>
                    <div class="flex-1">
                        <p class="font-semibold text-slate-800 group-hover:text-emerald-700">Descargar Plantilla</p>
                        <p class="text-xs text-slate-500">CSV para importación masiva</p>
                    </div>
                    <span class="text-emerald-500">→</span>
                </a>

                <a href="?page=reportes&year=<?php echo $currentYear; ?>"
                    class="flex items-center gap-3 p-4 rounded-xl bg-violet-50 hover:bg-violet-100 transition-all cursor-pointer group">
                    <div class="p-2 rounded-lg bg-violet-500 text-white">📊</div>
                    <div class="flex-1">
                        <p class="font-semibold text-slate-800 group-hover:text-violet-700">Generar Reportes</p>
                        <p class="text-xs text-slate-500">Exportar datos a Excel</p>
                    </div>
                    <span class="text-violet-500">→</span>
                </a>

                <?php if ($userRole === ROL_SUPERVISOR): ?>
                    <a href="?page=supervision&year=<?php echo $currentYear; ?>"
                        class="flex items-center gap-3 p-4 rounded-xl bg-amber-50 hover:bg-amber-100 transition-all cursor-pointer group">
                        <div class="p-2 rounded-lg bg-amber-500 text-white">👁️</div>
                        <div class="flex-1">
                            <p class="font-semibold text-slate-800 group-hover:text-amber-700">Supervisar</p>
                            <p class="text-xs text-slate-500"><?php echo $pendientes; ?> pendientes de revisión</p>
                        </div>
                        <span class="text-amber-500">→</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gráfico de Observaciones por Mes Comparativo (D3) -->
    <div class="card p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
            <span>📅</span> Observaciones por Mes
        </h3>
        <div class="relative" style="height: 300px;">
            <canvas id="chartDashboardMeses"></canvas>
            <p id="msgDashboardMeses" class="hidden text-slate-400 text-center py-28">Sin datos para los filtros seleccionados</p>
        </div>
    </div>

    <script>
    // Datos iniciales del servidor
    const __dashboardInitialData = <?php echo $dashboardStatsJson; ?>;
    let dashboardCharts = {};

    function getSelectedDashboardYears() {
        const all = Array.from(document.querySelectorAll('.dashboard-year-filter'));
        const checked = all.filter(cb => cb.checked).map(cb => cb.value);
        // Limitar a máximo 2 años
        if (checked.length > 2) {
            const firstChecked = all.find(cb => cb.checked);
            if (firstChecked) firstChecked.checked = false;
            return getSelectedDashboardYears();
        }
        return checked;
    }

    function getSelectedDashboardMeses() {
        return Array.from(document.querySelectorAll('.dashboard-mes-filter:checked')).map(cb => cb.value);
    }

    function renderDashboardCharts(data) {
        // Destruir gráficos anteriores
        Object.values(dashboardCharts).forEach(c => c && c.destroy && c.destroy());
        dashboardCharts = {};

        // D1 - Estados
        const d1Container = document.getElementById('chartDashboardEstado');
        const d1Msg = document.getElementById('msgDashboardEstado');
        if (d1Container && d1Msg) {
            if (data.por_estado && data.por_estado.length > 0) {
                d1Container.style.display = 'block';
                d1Msg.classList.add('hidden');
                dashboardCharts.estado = createDashboardEstadoChart('chartDashboardEstado', data.por_estado);
            } else {
                d1Container.style.display = 'none';
                d1Msg.classList.remove('hidden');
            }
        }

        // D2 - Tipos de Error
        const d2Container = document.getElementById('chartDashboardTipos');
        const d2Msg = document.getElementById('msgDashboardTipos');
        if (d2Container && d2Msg) {
            if (data.por_tipo_error && data.por_tipo_error.length > 0) {
                d2Container.style.display = 'block';
                d2Msg.classList.add('hidden');
                dashboardCharts.tipos = createDashboardTiposChart('chartDashboardTipos', data.por_tipo_error);
            } else {
                d2Container.style.display = 'none';
                d2Msg.classList.remove('hidden');
            }
        }

        // D3 - Meses comparativo
        const d3Container = document.getElementById('chartDashboardMeses');
        const d3Msg = document.getElementById('msgDashboardMeses');
        if (d3Container && d3Msg) {
            const anios = Object.keys(data.por_mes || {});
            const hasData = anios.length > 0 && anios.some(a => data.por_mes[a].length > 0);
            if (hasData) {
                d3Container.style.display = 'block';
                d3Msg.classList.add('hidden');
                dashboardCharts.meses = createDashboardMesesChart('chartDashboardMeses', data.por_mes);
            } else {
                d3Container.style.display = 'none';
                d3Msg.classList.remove('hidden');
            }
        }
    }

    async function loadDashboardStats() {
        const years = getSelectedDashboardYears();
        const meses = getSelectedDashboardMeses();

        if (years.length === 0 || meses.length === 0) {
            renderDashboardCharts({ por_estado: [], por_tipo_error: [], por_mes: {} });
            return;
        }

        let url = `api/reports.php?report=dashboard-stats`;
        years.forEach(y => url += `&years[]=${y}`);
        meses.forEach(m => url += `&meses[]=${encodeURIComponent(m)}`);

        try {
            const resp = await fetch(url);
            const json = await resp.json();
            if (!json.success) { console.error(json.message); return; }
            renderDashboardCharts(json.data);
        } catch (e) {
            console.error('Error cargando dashboard stats:', e);
        }
    }

    function selectDashboardQuarter(q) {
        const map = { 'Q1': ['Enero','Febrero','Marzo'], 'Q2': ['Abril','Mayo','Junio'], 'Q3': ['Julio','Agosto','Septiembre'], 'Q4': ['Octubre','Noviembre','Diciembre'] };
        const meses = map[q] || [];
        document.querySelectorAll('.dashboard-mes-filter').forEach(cb => {
            cb.checked = meses.includes(cb.value);
        });
        loadDashboardStats();
    }

    function selectDashboardSemester(h) {
        const map = { 'H1': ['Enero','Febrero','Marzo','Abril','Mayo','Junio'], 'H2': ['Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'] };
        const meses = map[h] || [];
        document.querySelectorAll('.dashboard-mes-filter').forEach(cb => {
            cb.checked = meses.includes(cb.value);
        });
        loadDashboardStats();
    }

    function selectDashboardAllMonths() {
        document.querySelectorAll('.dashboard-mes-filter').forEach(cb => cb.checked = true);
        loadDashboardStats();
    }

    // Carga inicial
    document.addEventListener('DOMContentLoaded', () => {
        if (__dashboardInitialData) {
            renderDashboardCharts(__dashboardInitialData);
        }
    });
    </script>
    <!-- Últimas Observaciones -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <span>📋</span> Últimas Observaciones Registradas
            </h3>
            <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="text-sm text-sky-600 hover:underline">
                Ver todas →
            </a>
        </div>
        <?php if (!empty($recentObs)): ?>
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Establecimiento</th>
                            <th>Mes</th>
                            <th>Tipo de Error</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentObs as $obs): ?>
                            <tr class="hover:bg-slate-50">
                                <td>
                                    <div class="font-semibold text-slate-800">
                                        <?php echo htmlspecialchars($obs['nombre_corto']); ?></div>
                                    <div class="text-xs text-slate-400"><?php echo htmlspecialchars($obs['comuna']); ?></div>
                                </td>
                                <td class="text-sm text-slate-600"><?php echo htmlspecialchars($obs['mes']); ?></td>
                                <td class="text-sm text-slate-600"><?php echo htmlspecialchars($obs['tipo_error']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $obs['estado_actual']; ?>">
                                        <?php echo ucfirst($obs['estado_actual']); ?>
                                    </span>
                                </td>
                                <td class="text-sm text-slate-500">
                                    <?php echo $obs['fecha_registro'] ? date('d/m/Y', strtotime($obs['fecha_registro'])) : '-'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-12 text-center">
                <div class="text-4xl mb-3">📭</div>
                <p class="text-slate-600 font-medium">No hay observaciones registradas</p>
                <?php if ($userRole === ROL_REGISTRADOR): ?>
                    <p class="text-sm text-slate-400 mb-4">Comienza creando tu primera observación</p>
                    <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="btn btn-primary">
                        ➕ Crear Observación
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>