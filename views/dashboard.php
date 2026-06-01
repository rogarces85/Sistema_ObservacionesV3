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

<div class="page-header d-flex flex-wrap justify-content-between align-items-center">
    <div>
        <div class="page-pretitle">Resumen estadístico del sistema de observaciones REM</div>
        <h2 class="page-title">Panel de Control <?php echo $currentYear; ?></h2>
    </div>
    <div class="btn-list">
        <?php if ($userRole === ROL_REGISTRADOR): ?>
            <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="btn btn-primary">
                <?php echo tablerIcon('plus'); ?>
                Nueva Observación
            </a>
        <?php endif; ?>
        <?php if ($userRole === ROL_SUPERVISOR): ?>
            <a href="?page=supervision&year=<?php echo $currentYear; ?>" class="btn btn-outline-secondary">
                <?php echo tablerIcon('eye'); ?>
                Supervisar
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if ($userRole === ROL_REGISTRADOR && !$tieneAsignaciones): ?>
    <div class="alert alert-warning d-flex align-items-center gap-3" role="alert">
        <div><?php echo tablerIcon('alert-triangle'); ?></div>
        <div>
            <strong>No tiene establecimientos asignados</strong><br>
            <small>No tiene establecimientos asignados para el año <strong><?php echo $currentYear; ?></strong>.
            Contacte a su supervisor para que le asigne los establecimientos correspondientes.</small>
        </div>
    </div>
<?php endif; ?>

<?php if ($userRole === ROL_SUPERVISOR && !empty($registradoresSinAsignaciones)): ?>
    <div class="alert alert-danger" role="alert">
        <div class="d-flex align-items-center gap-3">
            <div><?php echo tablerIcon('alert-circle'); ?></div>
            <div>
                <strong><?php echo count($registradoresSinAsignaciones); ?> registrador(es) sin establecimientos asignados</strong><br>
                <small>Los siguientes registradores no tienen establecimientos asignados para el año <strong><?php echo $currentYear; ?></strong>:</small>
                <ul class="mb-0 mt-1">
                    <?php foreach ($registradoresSinAsignaciones as $reg): ?>
                        <li><?php echo htmlspecialchars($reg['nombre_completo']); ?> (<?php echo htmlspecialchars($reg['username']); ?>)</li>
                    <?php endforeach; ?>
                </ul>
                <a href="?page=asignaciones&year=<?php echo $currentYear; ?>" class="fw-semibold text-decoration-none"><?php echo tablerIcon('arrow-right'); ?> Ir a Asignación de Establecimientos</a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="row g-3">
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-status-top bg-blue"></div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div><?php echo tablerIcon('chart-bar'); ?></div>
                    <div>
                        <div class="h1 mb-0 text-primary"><?php echo $stats['total']; ?></div>
                        <div class="text-secondary small fw-semibold">Total Registradas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-status-top bg-yellow"></div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div><?php echo tablerIcon('clock'); ?></div>
                    <div>
                        <div class="h1 mb-0 text-yellow"><?php echo $pendientes; ?></div>
                        <div class="text-secondary small fw-semibold">Pendientes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-status-top bg-green"></div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div><?php echo tablerIcon('check-circle'); ?></div>
                    <div>
                        <div class="h1 mb-0 text-green"><?php echo $aprobados; ?></div>
                        <div class="text-secondary small fw-semibold">Aprobados</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-status-top bg-red"></div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <div><?php echo tablerIcon('alert-triangle'); ?></div>
                    <div>
                        <div class="h1 mb-0 text-red"><?php echo $problemas; ?></div>
                        <div class="text-secondary small fw-semibold">Con Problemas</div>
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
            <div class="card-header"><h3 class="card-title"><?php echo tablerIcon('chart-bar'); ?> Distribución por Estado</h3></div>
            <div class="card-body">
                <div id="chartEstado" style="height: 240px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><?php echo tablerIcon('activity'); ?> Top Tipos de Error</h3></div>
            <div class="card-body">
                <div id="chartTipoError" style="height: 240px;"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title"><?php echo tablerIcon('list'); ?> Acciones Rápidas</h3></div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php if ($userRole === ROL_REGISTRADOR): ?>
                        <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                            <span class="avatar avatar-sm bg-blue text-blue-fg"><?php echo tablerIcon('plus'); ?></span>
                            <div class="flex-grow-1"><div class="fw-semibold">Nueva Observación</div><small class="text-secondary">Registrar una nueva observación</small></div>
                            <?php echo tablerIcon('chevron-right'); ?>
                        </a>
                    <?php endif; ?>
                    <a href="api/import_template.php" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                        <span class="avatar avatar-sm bg-green text-green-fg"><?php echo tablerIcon('download'); ?></span>
                        <div class="flex-grow-1"><div class="fw-semibold">Descargar Plantilla</div><small class="text-secondary">CSV para importación masiva</small></div>
                        <?php echo tablerIcon('chevron-right'); ?>
                    </a>
                    <a href="?page=reportes&year=<?php echo $currentYear; ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                        <span class="avatar avatar-sm bg-purple text-purple-fg"><?php echo tablerIcon('file-text'); ?></span>
                        <div class="flex-grow-1"><div class="fw-semibold">Generar Reportes</div><small class="text-secondary">Exportar datos a Excel</small></div>
                        <?php echo tablerIcon('chevron-right'); ?>
                    </a>
                    <?php if ($userRole === ROL_SUPERVISOR): ?>
                        <a href="?page=supervision&year=<?php echo $currentYear; ?>" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                            <span class="avatar avatar-sm bg-yellow text-yellow-fg"><?php echo tablerIcon('eye'); ?></span>
                            <div class="flex-grow-1"><div class="fw-semibold">Supervisar</div><small class="text-secondary"><?php echo $pendientes; ?> pendientes de revisión</small></div>
                            <?php echo tablerIcon('chevron-right'); ?>
                        </a>
                        <button type="button" data-bs-toggle="modal" data-bs-target="#modalInforme" class="list-group-item list-group-item-action d-flex align-items-center gap-3 w-100 text-start border-0">
                            <span class="avatar avatar-sm bg-red text-red-fg"><?php echo tablerIcon('file-text'); ?></span>
                            <div class="flex-grow-1"><div class="fw-semibold">Informe de Errores</div><small class="text-secondary">Trimestral o anual en PDF</small></div>
                            <?php echo tablerIcon('chevron-right'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart por Mes -->
<div class="card">
    <div class="card-header"><h3 class="card-title"><?php echo tablerIcon('calendar'); ?> Observaciones por Mes - <?php echo $currentYear; ?></h3></div>
    <div class="card-body">
        <div id="chartTendencia" style="height: 280px;"></div>
    </div>
</div>

<!-- Últimas Observaciones -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><?php echo tablerIcon('list'); ?> Últimas Observaciones Registradas</h3>
        <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="btn btn-ghost-primary btn-sm">Ver todas <?php echo tablerIcon('chevron-right'); ?></a>
    </div>
    <?php if (!empty($recentObs)): ?>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Establecimiento</th><th>Mes</th><th>Tipo de Error</th><th>Estado</th><th>Fecha</th></tr></thead>
                <tbody>
                    <?php foreach ($recentObs as $obs): ?>
                        <tr>
                            <td><div class="fw-semibold"><?php echo htmlspecialchars($obs['nombre_corto']); ?></div><div class="text-secondary"><?php echo htmlspecialchars($obs['comuna']); ?></div></td>
                            <td class="text-secondary"><?php echo htmlspecialchars($obs['mes']); ?></td>
                            <td class="text-secondary"><?php echo htmlspecialchars($obs['tipo_error']); ?></td>
                            <td><span class="badge bg-<?php echo $obs['estado_actual'] === 'pendiente' ? 'yellow text-yellow-fg' : ($obs['estado_actual'] === 'aprobado' ? 'green text-green-fg' : ($obs['estado_actual'] === 'error' ? 'red text-red-fg' : ($obs['estado_actual'] === 'justificado' ? 'blue text-blue-fg' : 'secondary'))); ?>"><?php echo ucfirst($obs['estado_actual']); ?></span></td>
                            <td class="text-secondary"><?php echo $obs['fecha_registro'] ? date('d/m/Y', strtotime($obs['fecha_registro'])) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty">
            <div class="empty-icon"><?php echo tablerIcon('file-text'); ?></div>
            <p class="empty-title">No hay observaciones registradas</p>
            <?php if ($userRole === ROL_REGISTRADOR): ?>
                <p class="empty-subtitle text-secondary">Comienza creando tu primera observación</p>
                <div class="empty-action">
                    <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="btn btn-primary"><?php echo tablerIcon('plus'); ?> Crear Observación</a>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Informe de Errores -->
<div class="modal fade" id="modalInforme" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><?php echo tablerIcon('file-text'); ?> Informe de Errores REM</h5>
                    <small class="text-secondary">Seleccione el período para generar el informe</small>
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
                <button type="button" onclick="cargarInformeWeb()" class="btn btn-primary flex-fill"><?php echo tablerIcon('eye'); ?> Ver en Web</button>
                <button type="button" onclick="descargarInformePDF()" class="btn btn-outline-secondary flex-fill"><?php echo tablerIcon('download'); ?> Descargar PDF</button>
            </div>
        </div>
    </div>
</div>

<!-- Informe Resultados Web -->
<div id="informeResultados" class="mt-3 d-none">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title"><?php echo tablerIcon('file-text'); ?> Detalle de Errores REM</h3>
                <span id="informePeriodo" class="text-secondary small"></span>
            </div>
            <div class="btn-list">
                <button onclick="descargarInformePDF()" class="btn btn-outline-secondary btn-sm"><?php echo tablerIcon('download'); ?> PDF</button>
                <button onclick="document.getElementById('informeResultados').classList.add('d-none')" class="btn-close"></button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table" id="informeTable">
                <thead><tr><th>Comuna</th><th>Establecimiento</th><th>Mes</th><th>Detalle del Error</th><th>Clasificación</th><th>Detalle Error</th></tr></thead>
                <tbody id="informeTableBody"></tbody>
            </table>
        </div>
        <div id="informePagination" class="card-footer d-flex justify-content-between align-items-center d-none">
            <button id="pagPrev" onclick="cambiarPagina(-1)" class="btn btn-outline-secondary btn-sm" disabled><?php echo tablerIcon('chevron-left'); ?> Anterior</button>
            <span id="pagInfo" class="text-secondary small"></span>
            <button id="pagNext" onclick="cambiarPagina(1)" class="btn btn-outline-secondary btn-sm">Siguiente <?php echo tablerIcon('chevron-right'); ?></button>
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
            document.getElementById('informeResultados').classList.remove('d-none');
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
            + '<br/><span class="text-secondary">' + escapeHtml(row.detalle_observacion || '') + '</span>';
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
        pagDiv.classList.remove('d-none');
        document.getElementById('pagInfo').textContent = 'Página ' + paginaActual + ' de ' + totalPaginas + ' (' + informeData.length + ' registros)';
        document.getElementById('pagPrev').disabled = paginaActual <= 1;
        document.getElementById('pagNext').disabled = paginaActual >= totalPaginas;
    } else {
        pagDiv.classList.add('d-none');
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
