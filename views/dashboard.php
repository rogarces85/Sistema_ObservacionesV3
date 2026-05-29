<?php
require_once 'models/Observation.php';
require_once 'models/EstablecimientoAsignacion.php';

$obsModel = new Observation();
$asigModel = new EstablecimientoAsignacion();
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['rol'];
$currentYear = $_SESSION['year'] ?? date('Y');

$tieneAsignaciones = false;
$registradoresSinAsignaciones = [];

if ($userRole === ROL_REGISTRADOR) {
    $tieneAsignaciones = $asigModel->tieneAsignaciones($userId, $currentYear);
} elseif ($userRole === ROL_SUPERVISOR) {
    $registradoresSinAsignaciones = $asigModel->getRegistradoresSinAsignaciones($currentYear);
}

$stats = $obsModel->getStats($currentYear, $userId, $userRole);

$recentObs = $obsModel->getAll($currentYear, $userId, $userRole);
$recentObs = array_slice($recentObs, 0, 5);

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

$mesesData = [];
foreach ($stats['por_mes'] as $mes) {
    $mesesData[$mes['mes']] = $mes['total'];
}
global $MESES;
$maxValue = !empty($mesesData) ? max(array_values($mesesData)) : 1;

$statsJson = json_encode($stats);
?>

<div class="space-y-6">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h2 class="text-2xl fw-bold" style="color:#1e293b">Panel de Control <?php echo $currentYear; ?></h2>
            <p style="color:#64748b">Resumen estadístico del sistema de observaciones REM</p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($userRole === ROL_REGISTRADOR): ?>
                <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="btn btn-primary">
                    📝 Nueva Observación
                </a>
            <?php endif; ?>
            <?php if ($userRole === ROL_SUPERVISOR): ?>
                <a href="?page=supervision&year=<?php echo $currentYear; ?>" class="btn btn-outline-secondary">
                    👁️ Supervisar
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($userRole === ROL_REGISTRADOR && !$tieneAsignaciones): ?>
        <div class="alert alert-warning d-flex align-items-center gap-3" role="alert">
            <div class="fs-2">⚠️</div>
            <div>
                <strong>No tiene establecimientos asignados</strong><br>
                <small>No tiene establecimientos asignados para el año <strong><?php echo $currentYear; ?></strong>.
                Contacte a su supervisor para que le asigne los establecimientos correspondientes.</small>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($userRole === ROL_SUPERVISOR && !empty($registradoresSinAsignaciones)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-3" role="alert">
            <div class="fs-2">🚨</div>
            <div>
                <strong><?php echo count($registradoresSinAsignaciones); ?> registrador(es) sin establecimientos asignados</strong><br>
                <small>Los siguientes registradores no tienen establecimientos asignados para el año <strong><?php echo $currentYear; ?></strong>:</small>
                <ul class="mb-0 mt-1">
                    <?php foreach ($registradoresSinAsignaciones as $reg): ?>
                        <li><?php echo htmlspecialchars($reg['nombre_completo']); ?> (<?php echo htmlspecialchars($reg['username']); ?>)</li>
                    <?php endforeach; ?>
                </ul>
                <a href="?page=asignaciones&year=<?php echo $currentYear; ?>" class="fw-semibold text-decoration-none">→ Ir a Asignación de Establecimientos</a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row g-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-color: #bae6fd;">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 rounded-3" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
                            <span class="fs-2">📊</span>
                        </div>
                        <div>
                            <div class="fs-1 fw-bold" style="color:#1e293b"><?php echo $stats['total']; ?></div>
                            <div class="small fw-semibold" style="color:#64748b">Total Registradas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-color: #fde68a;">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 rounded-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <span class="fs-2">⏳</span>
                        </div>
                        <div>
                            <div class="fs-1 fw-bold" style="color:#b45309"><?php echo $pendientes; ?></div>
                            <div class="small fw-semibold" style="color:#d97706">Pendientes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-color: #a7f3d0;">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 rounded-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <span class="fs-2">✅</span>
                        </div>
                        <div>
                            <div class="fs-1 fw-bold" style="color:#047857"><?php echo $aprobados; ?></div>
                            <div class="small fw-semibold" style="color:#059669">Aprobados</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card" style="background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%); border-color: #fecdd3;">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 rounded-3" style="background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);">
                            <span class="fs-2">⚠️</span>
                        </div>
                        <div>
                            <div class="fs-1 fw-bold" style="color:#be123c"><?php echo $problemas; ?></div>
                            <div class="small fw-semibold" style="color:#e11d48">Con Problemas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts + Acciones Rápidas -->
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">📈 Distribución por Estado</h3></div>
                <div class="card-body">
                    <div style="height: 240px;"><canvas id="chartEstado"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">🔍 Top Tipos de Error</h3></div>
                <div class="card-body">
                    <div style="height: 240px;"><canvas id="chartTipoError"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title">⚡ Acciones Rápidas</h3></div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php if ($userRole === ROL_REGISTRADOR): ?>
                            <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 rounded-3 mb-1" style="background:#f0f9ff">
                                <span class="p-2 rounded-2 text-white" style="background:#0ea5e9">📝</span>
                                <div class="flex-grow-1"><div class="fw-semibold" style="color:#1e293b">Nueva Observación</div><small style="color:#64748b">Registrar una nueva observación</small></div>
                                <span style="color:#0ea5e9">→</span>
                            </a>
                        <?php endif; ?>
                        <a href="api/import_template.php" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 rounded-3 mb-1" style="background:#ecfdf5">
                            <span class="p-2 rounded-2 text-white" style="background:#10b981">📥</span>
                            <div class="flex-grow-1"><div class="fw-semibold" style="color:#1e293b">Descargar Plantilla</div><small style="color:#64748b">CSV para importación masiva</small></div>
                            <span style="color:#10b981">→</span>
                        </a>
                        <a href="?page=reportes&year=<?php echo $currentYear; ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 rounded-3 mb-1" style="background:#f5f3ff">
                            <span class="p-2 rounded-2 text-white" style="background:#8b5cf6">📊</span>
                            <div class="flex-grow-1"><div class="fw-semibold" style="color:#1e293b">Generar Reportes</div><small style="color:#64748b">Exportar datos a Excel</small></div>
                            <span style="color:#8b5cf6">→</span>
                        </a>
                        <?php if ($userRole === ROL_SUPERVISOR): ?>
                            <a href="?page=supervision&year=<?php echo $currentYear; ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 rounded-3 mb-1" style="background:#fffbeb">
                                <span class="p-2 rounded-2 text-white" style="background:#f59e0b">👁️</span>
                                <div class="flex-grow-1"><div class="fw-semibold" style="color:#1e293b">Supervisar</div><small style="color:#64748b"><?php echo $pendientes; ?> pendientes de revisión</small></div>
                                <span style="color:#f59e0b">→</span>
                            </a>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#modalInforme" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 rounded-3 mb-1 w-100 text-start border-0" style="background:#fff1f2">
                                <span class="p-2 rounded-2 text-white" style="background:#f43f5e">📄</span>
                                <div class="flex-grow-1"><div class="fw-semibold" style="color:#1e293b">Informe de Errores</div><small style="color:#64748b">Trimestral o anual en PDF</small></div>
                                <span style="color:#f43f5e">→</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart por Mes -->
    <div class="card">
        <div class="card-header"><h3 class="card-title">📅 Observaciones por Mes - <?php echo $currentYear; ?></h3></div>
        <div class="card-body">
            <div style="height: 280px;"><canvas id="chartTendencia"></canvas></div>
        </div>
    </div>

    <!-- Últimas Observaciones -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">📋 Últimas Observaciones Registradas</h3>
            <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="small text-decoration-none" style="color:#0ea5e9">Ver todas →</a>
        </div>
        <?php if (!empty($recentObs)): ?>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Establecimiento</th><th>Mes</th><th>Tipo de Error</th><th>Estado</th><th>Fecha</th></tr></thead>
                    <tbody>
                        <?php foreach ($recentObs as $obs): ?>
                            <tr>
                                <td><div class="fw-semibold" style="color:#1e293b"><?php echo htmlspecialchars($obs['nombre_corto']); ?></div><div class="text-xs text-muted"><?php echo htmlspecialchars($obs['comuna']); ?></div></td>
                                <td class="text-muted"><?php echo htmlspecialchars($obs['mes']); ?></td>
                                <td class="text-muted"><?php echo htmlspecialchars($obs['tipo_error']); ?></td>
                                <td><span class="badge bg-<?php echo $obs['estado_actual'] === 'pendiente' ? 'warning' : ($obs['estado_actual'] === 'aprobado' ? 'success' : ($obs['estado_actual'] === 'error' ? 'danger' : ($obs['estado_actual'] === 'justificado' ? 'info' : 'secondary'))); ?>"><?php echo ucfirst($obs['estado_actual']); ?></span></td>
                                <td class="text-muted"><?php echo $obs['fecha_registro'] ? date('d/m/Y', strtotime($obs['fecha_registro'])) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card-body text-center py-5">
                <div class="fs-1 mb-2">📭</div>
                <p class="fw-medium" style="color:#64748b">No hay observaciones registradas</p>
                <?php if ($userRole === ROL_REGISTRADOR): ?>
                    <p class="small text-muted mb-3">Comienza creando tu primera observación</p>
                    <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="btn btn-primary">➕ Crear Observación</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Informe de Errores -->
<div class="modal fade" id="modalInforme" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">📄 Informe de Errores REM</h5>
                    <small class="text-muted">Seleccione el período para generar el informe</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formInforme">
                    <div class="mb-3">
                        <label class="form-label">Tipo de Informe</label>
                        <select id="informeTipo" name="tipo" onchange="toggleTrimestre()" class="form-select">
                            <option value="trimestral">Trimestral</option>
                            <option value="anual">Anual</option>
                        </select>
                    </div>
                    <div id="trimestreGroup" class="mb-3">
                        <label class="form-label">Trimestre</label>
                        <select id="informeTrimestre" name="trimestre" class="form-select">
                            <option value="1">1° Trimestre (Ene - Mar)</option>
                            <option value="2">2° Trimestre (Abr - Jun)</option>
                            <option value="3">3° Trimestre (Jul - Sep)</option>
                            <option value="4">4° Trimestre (Oct - Dic)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Año</label>
                        <select id="informeAnio" name="anio" class="form-select">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo $y == $currentYear ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="cargarInformeWeb()" class="btn btn-primary flex-fill">🌐 Ver en Web</button>
                <button type="button" onclick="descargarInformePDF()" class="btn btn-outline-secondary flex-fill">📥 Descargar PDF</button>
            </div>
        </div>
    </div>
</div>

<!-- Informe Resultados Web -->
<div id="informeResultados" class="mt-3" style="display:none">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title">📋 Detalle de Errores REM</h3>
                <span id="informePeriodo" class="text-muted small"></span>
            </div>
            <div class="d-flex gap-2">
                <button onclick="descargarInformePDF()" class="btn btn-outline-secondary btn-sm">📥 PDF</button>
                <button onclick="document.getElementById('informeResultados').style.display='none'" class="btn-close"></button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table" id="informeTable">
                <thead><tr><th>Comuna</th><th>Establecimiento</th><th>Mes</th><th>Detalle del Error</th><th>Clasificación</th><th>Detalle Error</th></tr></thead>
                <tbody id="informeTableBody"></tbody>
            </table>
        </div>
        <div id="informePagination" class="card-footer d-flex justify-content-between align-items-center" style="display:none">
            <button id="pagPrev" onclick="cambiarPagina(-1)" class="btn btn-outline-secondary btn-sm" disabled>← Anterior</button>
            <span id="pagInfo" class="text-muted small"></span>
            <button id="pagNext" onclick="cambiarPagina(1)" class="btn btn-outline-secondary btn-sm">Siguiente →</button>
        </div>
    </div>
</div>

<script>
const __dashboardStats = <?php echo $statsJson; ?>;

document.addEventListener('DOMContentLoaded', () => {
    if (__dashboardStats) {
        initializeCharts(__dashboardStats);
    }
});

let informeData = [];
let paginaActual = 1;
const FILAS_POR_PAGINA = 20;

function toggleTrimestre() {
    const tipo = document.getElementById('informeTipo').value;
    document.getElementById('trimestreGroup').style.display = tipo === 'trimestral' ? '' : 'none';
}

function getInformeParams() {
    const params = new URLSearchParams();
    params.set('tipo', document.getElementById('informeTipo').value);
    params.set('anio', document.getElementById('informeAnio').value);
    if (document.getElementById('informeTipo').value === 'trimestral') {
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalInforme'));
            if (modal) modal.hide();
            document.getElementById('informeResultados').style.display = '';
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
            + '<br/><span class="text-muted">' + escapeHtml(row.detalle_observacion || '') + '</span>';
        return '<tr>' +
            '<td><span class="fw-semibold" style="color:#1e293b">' + escapeHtml(row.comuna) + '</span></td>' +
            '<td>' + escapeHtml(row.establecimiento) + '</td>' +
            '<td>' + escapeHtml(row.mes) + '</td>' +
            '<td>' + detalle + '</td>' +
            '<td>' + escapeHtml(row.clasificacion || '-') + '</td>' +
            '<td>' + escapeHtml(row.detalle_error || '-') + '</td></tr>';
    }).join('');
    const pagDiv = document.getElementById('informePagination');
    if (totalPaginas > 1) {
        pagDiv.style.display = '';
        document.getElementById('pagInfo').textContent = 'Página ' + paginaActual + ' de ' + totalPaginas + ' (' + informeData.length + ' registros)';
        document.getElementById('pagPrev').disabled = paginaActual <= 1;
        document.getElementById('pagNext').disabled = paginaActual >= totalPaginas;
    } else {
        pagDiv.style.display = 'none';
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
