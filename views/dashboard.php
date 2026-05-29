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

// Obtener estadísticas
$stats = $obsModel->getStats($currentYear, $userId, $userRole);

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

// Preparar datos para gráfico de meses
$mesesData = [];
foreach ($stats['por_mes'] as $mes) {
    $mesesData[$mes['mes']] = $mes['total'];
}
global $MESES;
$maxValue = !empty($mesesData) ? max(array_values($mesesData)) : 1;

// Datos para Chart.js
$statsJson = json_encode($stats);
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

    <!-- Sección de Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Distribución por Estado (Chart.js) -->
        <div class="card p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span>📈</span> Distribución por Estado
            </h3>
            <div class="relative" style="height: 240px;">
                <canvas id="chartEstado"></canvas>
            </div>
        </div>

        <!-- Top Tipos de Error (Chart.js) -->
        <div class="card p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span>🔍</span> Top Tipos de Error
            </h3>
            <div class="relative" style="height: 240px;">
                <canvas id="chartTipoError"></canvas>
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
                    <button onclick="openInformeModal()"
                        class="flex items-center gap-3 p-4 rounded-xl bg-rose-50 hover:bg-rose-100 transition-all cursor-pointer group w-full text-left">
                        <div class="p-2 rounded-lg bg-rose-500 text-white">📄</div>
                        <div class="flex-1">
                            <p class="font-semibold text-slate-800 group-hover:text-rose-700">Informe de Errores</p>
                            <p class="text-xs text-slate-500">Trimestral o anual en PDF</p>
                        </div>
                        <span class="text-rose-500">→</span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gráfico de Observaciones por Mes (Chart.js) -->
    <div class="card p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
            <span>📅</span> Observaciones por Mes - <?php echo $currentYear; ?>
        </h3>
        <div class="relative" style="height: 280px;">
            <canvas id="chartTendencia"></canvas>
        </div>
    </div>

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

    <!-- Informe de Errores - Modal de selección -->
    <div id="modalInforme" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <div>
                    <h3 class="text-xl font-bold text-slate-800">📄 Informe de Errores REM</h3>
                    <p class="text-sm text-slate-500">Seleccione el período para generar el informe</p>
                </div>
                <button onclick="closeModal('modalInforme')" class="btn-secondary px-3 py-2" type="button">✕</button>
            </div>
            <div class="modal-body">
                <form id="formInforme" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tipo de Informe</label>
                        <select id="informeTipo" name="tipo" onchange="toggleTrimestre()" class="w-full">
                            <option value="trimestral">Trimestral</option>
                            <option value="anual">Anual</option>
                        </select>
                    </div>
                    <div id="trimestreGroup">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Trimestre</label>
                        <select id="informeTrimestre" name="trimestre" class="w-full">
                            <option value="1">1° Trimestre (Ene - Mar)</option>
                            <option value="2">2° Trimestre (Abr - Jun)</option>
                            <option value="3">3° Trimestre (Jul - Sep)</option>
                            <option value="4">4° Trimestre (Oct - Dic)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Año</label>
                        <select id="informeAnio" name="anio" class="w-full">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo $y == $currentYear ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="cargarInformeWeb()" class="btn btn-primary flex-1">
                            🌐 Ver en Web
                        </button>
                        <button type="button" onclick="descargarInformePDF()" class="btn btn-secondary flex-1">
                            📥 Descargar PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Informe de Errores - Resultados Web -->
    <div id="informeResultados" class="hidden">
        <div class="card overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span>📋</span> Detalle de Errores REM
                    </h3>
                    <p id="informePeriodo" class="text-sm text-slate-500"></p>
                </div>
                <div class="flex gap-2">
                    <button onclick="descargarInformePDF()" class="btn btn-secondary text-sm">
                        📥 PDF
                    </button>
                    <button onclick="document.getElementById('informeResultados').classList.add('hidden')" class="btn-secondary px-3 py-1 text-xs">
                        ✕ Cerrar
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="informeTable">
                    <thead>
                        <tr>
                            <th>Comuna</th>
                            <th>Establecimiento</th>
                            <th>Mes</th>
                            <th>Detalle del Error</th>
                            <th>Clasificación</th>
                            <th>Detalle Error</th>
                        </tr>
                    </thead>
                    <tbody id="informeTableBody"></tbody>
                </table>
            </div>
            <div id="informePagination" class="hidden p-4 border-t border-slate-100 flex items-center justify-between">
                <button id="pagPrev" onclick="cambiarPagina(-1)" class="btn-secondary px-4 py-2 text-sm" disabled>
                    ← Anterior
                </button>
                <span id="pagInfo" class="text-sm text-slate-600"></span>
                <button id="pagNext" onclick="cambiarPagina(1)" class="btn-secondary px-4 py-2 text-sm">
                    Siguiente →
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Datos iniciales del servidor para Chart.js
const __dashboardStats = <?php echo $statsJson; ?>;

document.addEventListener('DOMContentLoaded', () => {
    if (__dashboardStats) {
        initializeCharts(__dashboardStats);
    }
});

// --- Informe de Errores ---
let informeData = [];
let paginaActual = 1;
const FILAS_POR_PAGINA = 20;

function openInformeModal() {
    document.getElementById('informeResultados').classList.add('hidden');
    openModal('modalInforme');
}

function toggleTrimestre() {
    const tipo = document.getElementById('informeTipo').value;
    document.getElementById('trimestreGroup').style.display = tipo === 'trimestral' ? '' : 'none';
}

function getInformeParams() {
    const tipo = document.getElementById('informeTipo').value;
    const params = new URLSearchParams();
    params.set('tipo', tipo);
    params.set('anio', document.getElementById('informeAnio').value);
    if (tipo === 'trimestral') {
        params.set('trimestre', document.getElementById('informeTrimestre').value);
    }
    return params;
}

async function cargarInformeWeb() {
    const params = getInformeParams();
    params.set('format', 'json');

    try {
        showLoading();
        const response = await fetch('api/informe_errores.php?' + params.toString());
        const result = await response.json();

        hideLoading();

        if (result.success) {
            informeData = result.data.datos;
            paginaActual = 1;
            document.getElementById('informePeriodo').textContent = 'Período: ' + result.data.periodo + ' | Total: ' + result.data.total + ' errores';
            renderInformeTabla();
            closeModal('modalInforme');
            document.getElementById('informeResultados').classList.remove('hidden');
        } else {
            showError(result.message || 'Error al cargar el informe');
        }
    } catch (error) {
        hideLoading();
        showError('Error al cargar el informe: ' + error.message);
    }
}

function descargarInformePDF() {
    const params = getInformeParams();
    params.set('format', 'pdf');
    window.open('api/informe_errores.php?' + params.toString(), '_blank');
}

function renderInformeTabla() {
    const tbody = document.getElementById('informeTableBody');
    const totalPaginas = Math.ceil(informeData.length / FILAS_POR_PAGINA);
    const inicio = (paginaActual - 1) * FILAS_POR_PAGINA;
    const fin = Math.min(inicio + FILAS_POR_PAGINA, informeData.length);
    const paginas = informeData.slice(inicio, fin);

    tbody.innerHTML = paginas.map(row => {
        const detalle = (row.codigo_serie ? '<strong>' + escapeHtml(row.codigo_serie) + '</strong>' : '')
            + (row.codigo_hoja ? ' | <strong>' + escapeHtml(row.codigo_hoja) + '</strong>' : '')
            + '<br/><span class="text-slate-600">' + escapeHtml(row.detalle_observacion || '') + '</span>';
        return '<tr>' +
            '<td><span class="font-semibold text-slate-700">' + escapeHtml(row.comuna) + '</span></td>' +
            '<td>' + escapeHtml(row.establecimiento) + '</td>' +
            '<td>' + escapeHtml(row.mes) + '</td>' +
            '<td>' + detalle + '</td>' +
            '<td>' + escapeHtml(row.clasificacion || '-') + '</td>' +
            '<td>' + escapeHtml(row.detalle_error || '-') + '</td>' +
            '</tr>';
    }).join('');

    // Paginación
    const pagDiv = document.getElementById('informePagination');
    if (totalPaginas > 1) {
        pagDiv.classList.remove('hidden');
        document.getElementById('pagInfo').textContent = 'Página ' + paginaActual + ' de ' + totalPaginas + ' (' + informeData.length + ' registros)';
        document.getElementById('pagPrev').disabled = paginaActual <= 1;
        document.getElementById('pagNext').disabled = paginaActual >= totalPaginas;
    } else {
        pagDiv.classList.add('hidden');
    }
}

function cambiarPagina(delta) {
    const totalPaginas = Math.ceil(informeData.length / FILAS_POR_PAGINA);
    const nueva = paginaActual + delta;
    if (nueva >= 1 && nueva <= totalPaginas) {
        paginaActual = nueva;
        renderInformeTabla();
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>