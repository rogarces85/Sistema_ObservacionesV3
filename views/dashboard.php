<?php
require_once 'models/Observation.php';
require_once 'models/EstablecimientoAsignacion.php';
require_once 'includes/icons.php';

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

<div class="mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <h2 class="mb-1 fw-bold text-primary">Panel de Control <?php echo $currentYear; ?></h2>
            <p class="text-secondary mb-0">Resumen estadístico del sistema de observaciones REM</p>
        </div>
        <div class="d-flex gap-2">
            <?php if ($userRole === ROL_REGISTRADOR): ?>
                <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="btn btn-primary">
                    <?php echo icon_edit(); ?> Nueva Observación
                </a>
            <?php endif; ?>
            <?php if ($userRole === ROL_SUPERVISOR): ?>
                <a href="?page=supervision&year=<?php echo $currentYear; ?>" class="btn btn-outline-secondary">
                    <?php echo icon_eye(); ?> Supervisar
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($userRole === ROL_REGISTRADOR && !$tieneAsignaciones): ?>
        <div class="alert alert-warning alert-icon mt-3" role="alert">
            <?php echo icon_alert_triangle('icon me-2'); ?>
            <div>
                <strong>No tiene establecimientos asignados</strong><br>
                <small>No tiene establecimientos asignados para el año <strong><?php echo $currentYear; ?></strong>.
                Contacte a su supervisor para que le asigne los establecimientos correspondientes.</small>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($userRole === ROL_SUPERVISOR && !empty($registradoresSinAsignaciones)): ?>
        <div class="alert alert-danger alert-icon mt-3" role="alert">
            <?php echo icon_alert_triangle('icon me-2'); ?>
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
    <div class="row g-3 mt-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-primary-lt">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 rounded-3 bg-primary text-white">
                            <?php echo icon_chart_bar('icon icon-lg'); ?>
                        </div>
                        <div>
                            <div class="fs-1 fw-bold text-primary"><?php echo $stats['total']; ?></div>
                            <div class="small fw-semibold text-secondary">Total Registradas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-warning-lt">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 rounded-3 bg-warning text-white">
                            <?php echo icon_clock('icon icon-lg'); ?>
                        </div>
                        <div>
                            <div class="fs-1 fw-bold text-warning"><?php echo $pendientes; ?></div>
                            <div class="small fw-semibold text-warning">Pendientes</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-success-lt">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 rounded-3 bg-success text-white">
                            <?php echo icon_circle_check('icon icon-lg'); ?>
                        </div>
                        <div>
                            <div class="fs-1 fw-bold text-success"><?php echo $aprobados; ?></div>
                            <div class="small fw-semibold text-success">Aprobados</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card bg-danger-lt">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 rounded-3 bg-danger text-white">
                            <?php echo icon_alert_triangle('icon icon-lg'); ?>
                        </div>
                        <div>
                            <div class="fs-1 fw-bold text-danger"><?php echo $problemas; ?></div>
                            <div class="small fw-semibold text-danger">Con Problemas</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts + Acciones Rápidas -->
    <div class="row g-3 mt-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><?php echo icon_chart_pie('icon me-2'); ?>Distribución por Estado</h3></div>
                <div class="card-body">
                    <div style="height: 240px;"><canvas id="chartEstado"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><?php echo icon_search('icon me-2'); ?>Top Tipos de Error</h3></div>
                <div class="card-body">
                    <div style="height: 240px;"><canvas id="chartTipoError"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h3 class="card-title"><?php echo icon_zap('icon me-2'); ?>Acciones Rápidas</h3></div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <?php if ($userRole === ROL_REGISTRADOR): ?>
                            <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 rounded-3 mb-1">
                                <span class="p-2 rounded-2 bg-primary text-white"><?php echo icon_edit('icon'); ?></span>
                                <div class="flex-grow-1"><div class="fw-semibold">Nueva Observación</div><small class="text-secondary">Registrar una nueva observación</small></div>
                                <span class="text-primary">→</span>
                            </a>
                        <?php endif; ?>
                        <a href="api/import_template.php" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 rounded-3 mb-1">
                            <span class="p-2 rounded-2 bg-success text-white"><?php echo icon_download('icon'); ?></span>
                            <div class="flex-grow-1"><div class="fw-semibold">Descargar Plantilla</div><small class="text-secondary">CSV para importación masiva</small></div>
                            <span class="text-success">→</span>
                        </a>
                        <a href="?page=reportes&year=<?php echo $currentYear; ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 rounded-3 mb-1">
                            <span class="p-2 rounded-2 bg-secondary text-white"><?php echo icon_chart_bar('icon'); ?></span>
                            <div class="flex-grow-1"><div class="fw-semibold">Generar Reportes</div><small class="text-secondary">Exportar datos a Excel</small></div>
                            <span class="text-secondary">→</span>
                        </a>
                        <?php if ($userRole === ROL_SUPERVISOR): ?>
                            <a href="?page=supervision&year=<?php echo $currentYear; ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 rounded-3 mb-1">
                                <span class="p-2 rounded-2 bg-warning text-white"><?php echo icon_eye('icon'); ?></span>
                                <div class="flex-grow-1"><div class="fw-semibold">Supervisar</div><small class="text-secondary"><?php echo $pendientes; ?> pendientes de revisión</small></div>
                                <span class="text-warning">→</span>
                            </a>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#modalInforme" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 rounded-3 mb-1 w-100 text-start border-0">
                                <span class="p-2 rounded-2 bg-danger text-white"><?php echo icon_file_text('icon'); ?></span>
                                <div class="flex-grow-1"><div class="fw-semibold">Informe de Errores</div><small class="text-secondary">Trimestral o anual en PDF</small></div>
                                <span class="text-danger">→</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart por Mes -->
    <div class="card mt-3">
        <div class="card-header"><h3 class="card-title"><?php echo icon_calendar('icon me-2'); ?>Observaciones por Mes - <?php echo $currentYear; ?></h3></div>
        <div class="card-body">
            <div style="height: 280px;"><canvas id="chartTendencia"></canvas></div>
        </div>
    </div>

    <!-- Últimas Observaciones -->
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><?php echo icon_clipboard_list('icon me-2'); ?>Últimas Observaciones Registradas</h3>
            <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="small text-decoration-none text-primary">Ver todas →</a>
        </div>
        <?php if (!empty($recentObs)): ?>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead><tr><th>Establecimiento</th><th>Mes</th><th>Tipo de Error</th><th>Estado</th><th>Fecha</th></tr></thead>
                    <tbody>
                        <?php foreach ($recentObs as $obs): ?>
                            <tr>
                                <td><div class="fw-semibold"><?php echo htmlspecialchars($obs['nombre_corto']); ?></div><div class="text-muted small"><?php echo htmlspecialchars($obs['comuna']); ?></div></td>
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
                <p class="fw-medium text-secondary">No hay observaciones registradas</p>
                <?php if ($userRole === ROL_REGISTRADOR): ?>
                    <p class="small text-muted mb-3">Comienza creando tu primera observación</p>
                    <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="btn btn-primary"><?php echo icon_edit('icon me-2'); ?>Crear Observación</a>
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
                    <h5 class="modal-title"><?php echo icon_file_text('icon me-2'); ?>Informe de Errores REM</h5>
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
                <button type="button" onclick="cargarInformeWeb()" class="btn btn-primary flex-fill"><?php echo icon_edit('icon me-2'); ?>Ver en Web</button>
                <button type="button" onclick="descargarInformePDF()" class="btn btn-outline-secondary flex-fill"><?php echo icon_download('icon me-2'); ?>Descargar PDF</button>
            </div>
        </div>
    </div>
</div>

<!-- Informe Resultados Web -->
<div id="informeResultados" class="mt-3" style="display:none">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title"><?php echo icon_clipboard_list('icon me-2'); ?>Detalle de Errores REM</h3>
                <span id="informePeriodo" class="text-muted small"></span>
            </div>
            <div class="d-flex gap-2">
                <button onclick="descargarInformePDF()" class="btn btn-outline-secondary btn-sm"><?php echo icon_download('icon me-2'); ?>PDF</button>
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
            '<td><span class="fw-semibold">' + escapeHtml(row.comuna) + '</span></td>' +
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
