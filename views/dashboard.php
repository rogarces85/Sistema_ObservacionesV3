<?php
require_once 'models/Observation.php';
require_once 'models/EstablecimientoAsignacion.php';

$obsModel = new Observation();
$asigModel = new EstablecimientoAsignacion();
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['rol'];
$currentYear = $_SESSION['year'] ?? date('Y');

$userName = $_SESSION['nombre_completo'] ?? 'Usuario';
$userInitials = strtoupper(substr($userName, 0, 2));

$tieneAsignaciones = false;
$registradoresSinAsignaciones = [];

try {
    if ($userRole === ROL_REGISTRADOR) {
        $tieneAsignaciones = $asigModel->tieneAsignaciones($userId, $currentYear);
    } elseif ($userRole === ROL_SUPERVISOR) {
        $registradoresSinAsignaciones = $asigModel->getRegistradoresSinAsignaciones($currentYear);
    }

    $stats = $obsModel->getStats($currentYear, $userId, $userRole);
    $recentObs = $obsModel->getAll($currentYear, $userId, $userRole);
    $recentObs = array_slice($recentObs, 0, 5);
} catch (Throwable $e) {
    error_log('Error al cargar dashboard: ' . $e->getMessage());
    $stats = ['total' => 0, 'por_estado' => [], 'por_mes' => [], 'por_tipo_error' => []];
    $recentObs = [];
}

$pendientes = 0;
$aprobados = 0;
$problemas = 0;
$justificados = 0;

foreach ($stats['por_estado'] as $estado) {
    switch ($estado['estado_actual']) {
        case ESTADO_PENDIENTE:  $pendientes = (int) $estado['total']; break;
        case ESTADO_APROBADO:   $aprobados = (int) $estado['total']; break;
        case ESTADO_RECHAZADO:
        case ESTADO_ERROR:       $problemas += (int) $estado['total']; break;
        case ESTADO_JUSTIFICADO: $justificados = (int) $estado['total']; break;
    }
}

$mesesData = [];
foreach ($stats['por_mes'] as $mes) {
    $mesesData[$mes['mes']] = (int) $mes['total'];
}
global $MESES;
$maxValue = !empty($mesesData) ? max(array_values($mesesData)) : 1;
$totalRegistradas = (int) ($stats['total'] ?? 0);

$topErrores = $stats['por_tipo_error'] ?? [];
usort($topErrores, function ($a, $b) { return ((int) $b['total']) - ((int) $a['total']); });
$topErrores = array_slice($topErrores, 0, 5);

$statsJson = json_encode($stats, JSON_UNESCAPED_UNICODE);
?>

<div class="d-flex flex-column gap-3 mt-2 rem-fade-in">
    <header class="card card-stat rem-fade-in" style="--rem-card-bg: var(--rem-grad-hero); color: #ffffff;">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-md-8">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar avatar-lg" style="background: rgba(255,255,255,0.2); color: #ffffff; width: 3.5rem; height: 3.5rem; font-size: 1.1rem;">
                            <?php echo htmlspecialchars($userInitials); ?>
                        </div>
                        <div>
                            <small class="d-block" style="opacity: 0.75; text-transform: uppercase; letter-spacing: 0.08em; font-size: 0.7rem;">Bienvenido de vuelta</small>
                            <h2 class="mb-0 fw-bold" style="font-size: 1.6rem;"><?php echo htmlspecialchars($userName); ?></h2>
                            <span class="badge" style="background: rgba(255,255,255,0.18); color: #ffffff; font-weight: 500;">
                                <i class="ti ti-shield me-1"></i><?php echo htmlspecialchars(ucfirst($userRole)); ?>
                            </span>
                        </div>
                    </div>
                    <p class="mb-4" style="opacity: 0.9; max-width: 36rem;">
                        Resumen del Sistema de Observaciones REM para el año
                        <strong><?php echo htmlspecialchars($currentYear); ?></strong>.
                        Tienes <?php echo $pendientes; ?> observaciones pendientes y
                        <?php echo $aprobados; ?> aprobadas.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <?php if ($userRole === ROL_REGISTRADOR): ?>
                            <a href="?page=observaciones&action=new&year=<?php echo $currentYear; ?>" class="btn" style="background: #ffffff; color: var(--tblr-primary); font-weight: 600;">
                                <i class="ti ti-edit me-1"></i>Nueva Observación
                            </a>
                            <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="btn" style="background: rgba(255,255,255,0.18); color: #ffffff; border: 1px solid rgba(255,255,255,0.3);">
                                <i class="ti ti-list me-1"></i>Mis Observaciones
                            </a>
                        <?php else: ?>
                            <a href="?page=supervision&year=<?php echo $currentYear; ?>" class="btn" style="background: #ffffff; color: var(--tblr-primary); font-weight: 600;">
                                <i class="ti ti-eye me-1"></i>Supervisar
                            </a>
                            <a href="?page=reportes&year=<?php echo $currentYear; ?>" class="btn" style="background: rgba(255,255,255,0.18); color: #ffffff; border: 1px solid rgba(255,255,255,0.3);">
                                <i class="ti ti-chart-bar me-1"></i>Reportes
                            </a>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#modalInforme" class="btn" style="background: rgba(255,255,255,0.18); color: #ffffff; border: 1px solid rgba(255,255,255,0.3);">
                                <i class="ti ti-report-analytics me-1"></i>Informe
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="d-flex flex-column align-items-md-end gap-2">
                        <span class="d-flex align-items-center gap-2" style="opacity: 0.85;">
                            <i class="ti ti-calendar-event"></i>Año de trabajo
                        </span>
                        <span class="display-5 fw-bold" style="line-height: 1; letter-spacing: -0.02em;"><?php echo htmlspecialchars($currentYear); ?></span>
                        <span class="d-inline-flex align-items-center gap-2" style="background: rgba(255,255,255,0.15); padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.85rem;">
                            <span class="status status-green" style="background:#86efac;"></span>
                            Datos en vivo
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <?php if ($userRole === ROL_REGISTRADOR && !$tieneAsignaciones): ?>
        <div class="alert alert-warning alert-icon" role="alert">
            <i class="ti ti-alert-triangle alert-icon-i"></i>
            <div>
                <strong>No tiene establecimientos asignados</strong><br>
                <small>No tiene establecimientos asignados para el año <strong><?php echo $currentYear; ?></strong>.
                Contacte a su supervisor para que le asigne los establecimientos correspondientes.</small>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($userRole === ROL_SUPERVISOR && !empty($registradoresSinAsignaciones)): ?>
        <div class="alert alert-danger alert-icon" role="alert">
            <i class="ti ti-alert-triangle alert-icon-i"></i>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                    <strong><?php echo count($registradoresSinAsignaciones); ?> registrador(es) sin establecimientos asignados</strong>
                    <a href="?page=asignaciones&year=<?php echo $currentYear; ?>" class="btn btn-sm btn-danger">
                        <i class="ti ti-arrow-right me-1"></i>Resolver ahora
                    </a>
                </div>
                <small class="d-block mt-1">Los siguientes registradores no tienen establecimientos asignados para el año <strong><?php echo $currentYear; ?></strong>:</small>
                <ul class="mb-0 mt-1 small">
                    <?php foreach ($registradoresSinAsignaciones as $reg): ?>
                        <li><?php echo htmlspecialchars($reg['nombre_completo']); ?> (<?php echo htmlspecialchars($reg['username']); ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <section aria-label="Indicadores clave">
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stat rem-fade-in" style="--rem-card-bg: var(--rem-grad-card-blue);">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <span class="stat-icon" style="background: var(--tblr-primary);">
                                <i class="ti ti-stack-2"></i>
                            </span>
                            <span class="badge-status badge-status-pendiente">
                                <span class="status-dot"></span>
                                <span class="small fw-semibold">+ este año</span>
                            </span>
                        </div>
                        <div class="mt-3 stat-value" data-countup="<?php echo $totalRegistradas; ?>"><?php echo $totalRegistradas; ?></div>
                        <div class="stat-label">Total Registradas</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stat rem-fade-in" style="--rem-card-bg: var(--rem-grad-card-amber);">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <span class="stat-icon" style="background: var(--tblr-warning); color: #111827;">
                                <i class="ti ti-clock"></i>
                            </span>
                            <span class="badge-status badge-status-pendiente">
                                <span class="status-dot"></span>
                                <span class="small fw-semibold">requiere atención</span>
                            </span>
                        </div>
                        <div class="mt-3 stat-value" data-countup="<?php echo $pendientes; ?>"><?php echo $pendientes; ?></div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stat rem-fade-in" style="--rem-card-bg: var(--rem-grad-card-emerald);">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <span class="stat-icon" style="background: var(--tblr-success);">
                                <i class="ti ti-circle-check"></i>
                            </span>
                            <span class="badge-status badge-status-aprobado">
                                <span class="status-dot"></span>
                                <span class="small fw-semibold">sin observaciones</span>
                            </span>
                        </div>
                        <div class="mt-3 stat-value" data-countup="<?php echo $aprobados; ?>"><?php echo $aprobados; ?></div>
                        <div class="stat-label">Aprobadas</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-stat rem-fade-in" style="--rem-card-bg: var(--rem-grad-card-rose);">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between gap-2">
                            <span class="stat-icon" style="background: var(--tblr-danger);">
                                <i class="ti ti-alert-triangle"></i>
                            </span>
                            <span class="badge-status badge-status-error">
                                <span class="status-dot"></span>
                                <span class="small fw-semibold">acción inmediata</span>
                            </span>
                        </div>
                        <div class="mt-3 stat-value" data-countup="<?php echo $problemas; ?>"><?php echo $problemas; ?></div>
                        <div class="stat-label">Con Problemas</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="row g-3" aria-label="Distribución y acciones">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title">
                        <i class="ti ti-chart-donut me-2 text-primary"></i>Distribución por Estado
                    </h3>
                    <span class="badge badge-soft-primary">Total: <?php echo $totalRegistradas; ?></span>
                </div>
                <div class="card-body">
                    <?php if ($totalRegistradas > 0): ?>
                        <div style="height: 240px;"><canvas id="chartEstado"></canvas></div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class="ti ti-chart-donut"></i></div>
                            <h3>Sin datos aún</h3>
                            <p>Cuando se registren observaciones verás la distribución por estado aquí.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-flame me-2 text-danger"></i>Top Tipos de Error
                    </h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($topErrores)): ?>
                        <ul class="list-unstyled m-0 d-flex flex-column gap-3">
                            <?php foreach ($topErrores as $idx => $err): ?>
                                <?php
                                $pct = $totalRegistradas > 0 ? round(((int) $err['total'] / $totalRegistradas) * 100) : 0;
                                $color = ['bg-primary', 'bg-danger', 'bg-warning', 'bg-info', 'bg-secondary'][$idx % 5];
                                ?>
                                <li>
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                        <span class="fw-semibold text-truncate" title="<?php echo htmlspecialchars($err['tipo_error']); ?>">
                                            <i class="ti ti-tag me-1 text-primary"></i>
                                            <?php echo htmlspecialchars($err['tipo_error']); ?>
                                        </span>
                                        <span class="badge <?php echo $color; ?>"><?php echo (int) $err['total']; ?> · <?php echo $pct; ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 0.4rem;">
                                        <div class="progress-bar <?php echo $color; ?>" role="progressbar" style="width: <?php echo $pct; ?>%;"></div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon"><i class="ti ti-flame"></i></div>
                            <h3>Sin tipos registrados</h3>
                            <p>Aquí verás los tipos de error más frecuentes.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="ti ti-bolt me-2 text-warning"></i>Acciones Rápidas
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-2">
                        <?php if ($userRole === ROL_REGISTRADOR): ?>
                            <a href="?page=observaciones&action=new&year=<?php echo $currentYear; ?>" class="card card-link p-3 d-flex align-items-center gap-3">
                                <span class="stat-icon" style="background: var(--tblr-primary);"><i class="ti ti-edit"></i></span>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">Nueva Observación</div>
                                    <small class="text-secondary">Registrar una nueva observación</small>
                                </div>
                                <i class="ti ti-chevron-right text-primary"></i>
                            </a>
                        <?php endif; ?>
                        <a href="api/import_template.php" class="card card-link p-3 d-flex align-items-center gap-3">
                            <span class="stat-icon" style="background: var(--tblr-success);"><i class="ti ti-download"></i></span>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Descargar Plantilla</div>
                                <small class="text-secondary">CSV para importación masiva</small>
                            </div>
                            <i class="ti ti-chevron-right text-success"></i>
                        </a>
                        <a href="?page=reportes&year=<?php echo $currentYear; ?>" class="card card-link p-3 d-flex align-items-center gap-3">
                            <span class="stat-icon" style="background: var(--tblr-secondary);"><i class="ti ti-chart-bar"></i></span>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Generar Reportes</div>
                                <small class="text-secondary">Análisis y gráficos de errores</small>
                            </div>
                            <i class="ti ti-chevron-right text-secondary"></i>
                        </a>
                        <?php if ($userRole === ROL_SUPERVISOR): ?>
                            <a href="?page=supervision&year=<?php echo $currentYear; ?>" class="card card-link p-3 d-flex align-items-center gap-3">
                                <span class="stat-icon" style="background: var(--tblr-warning); color:#111827;"><i class="ti ti-eye"></i></span>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">Supervisar</div>
                                    <small class="text-secondary"><?php echo $pendientes; ?> pendientes de revisión</small>
                                </div>
                                <i class="ti ti-chevron-right text-warning"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="card" aria-label="Tendencia mensual">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h3 class="card-title">
                    <i class="ti ti-calendar-stats me-2 text-primary"></i>
                    Observaciones por Mes - <?php echo htmlspecialchars($currentYear); ?>
                </h3>
                <small class="text-secondary">Pico del año: <?php echo (int) $maxValue; ?> observaciones.</small>
            </div>
            <span class="badge badge-soft-primary">
                <i class="ti ti-info-circle me-1"></i>Lectura orientativa
            </span>
        </div>
        <div class="card-body">
            <?php if (!empty($mesesData)): ?>
                <div style="height: 280px;"><canvas id="chartTendencia"></canvas></div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon"><i class="ti ti-calendar-stats"></i></div>
                    <h3>Aún no hay tendencia</h3>
                    <p>Cuando existan registros mensuales, mostraremos la línea de tendencia aquí.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="card" aria-label="Últimas observaciones">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h3 class="card-title">
                <i class="ti ti-clipboard-list me-2 text-primary"></i>Últimas Observaciones Registradas
            </h3>
            <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="btn btn-sm btn-ghost-primary">
                Ver todas <i class="ti ti-arrow-right ms-1"></i>
            </a>
        </div>
        <?php if (!empty($recentObs)): ?>
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>Establecimiento</th>
                            <th>Mes</th>
                            <th>Tipo de Error</th>
                            <th>Estado</th>
                            <th class="text-end">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentObs as $obs): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($obs['nombre_corto']); ?></div>
                                    <small class="text-secondary"><?php echo htmlspecialchars($obs['comuna']); ?></small>
                                </td>
                                <td class="text-secondary"><?php echo htmlspecialchars($obs['mes']); ?></td>
                                <td class="text-secondary"><?php echo htmlspecialchars($obs['tipo_error']); ?></td>
                                <td>
                                    <?php
                                    $estadoKey = $obs['estado_actual'];
                                    $estadoClass = 'badge-status-' . $estadoKey;
                                    ?>
                                    <span class="badge-status <?php echo $estadoClass; ?>">
                                        <span class="status-dot"></span>
                                        <?php echo ucfirst(htmlspecialchars($estadoKey)); ?>
                                    </span>
                                </td>
                                <td class="text-end text-secondary"><?php echo $obs['fecha_registro'] ? date('d/m/Y', strtotime($obs['fecha_registro'])) : '—'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="ti ti-mailbox"></i></div>
                <h3>No hay observaciones registradas</h3>
                <p>Cuando se registren, aparecerán aquí los últimos cinco movimientos.</p>
                <?php if ($userRole === ROL_REGISTRADOR): ?>
                    <a href="?page=observaciones&action=new&year=<?php echo $currentYear; ?>" class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i>Crear primera observación
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<div class="modal fade" id="modalInforme" tabindex="-1" aria-labelledby="modalInformeTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="modalInformeTitle">
                        <i class="ti ti-report-analytics me-2 text-primary"></i>Informe de Errores REM
                    </h5>
                    <small class="text-secondary">Seleccione el período para generar el informe</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formInforme">
                    <div class="mb-3">
                        <label class="form-label" for="informeTipo">Tipo de Informe</label>
                        <select id="informeTipo" name="tipo" onchange="toggleTrimestre()" class="form-select">
                            <option value="trimestral">Trimestral</option>
                            <option value="anual">Anual</option>
                        </select>
                    </div>
                    <div id="trimestreGroup" class="mb-3">
                        <label class="form-label" for="informeTrimestre">Trimestre</label>
                        <select id="informeTrimestre" name="trimestre" class="form-select">
                            <option value="1">1° Trimestre (Ene - Mar)</option>
                            <option value="2">2° Trimestre (Abr - Jun)</option>
                            <option value="3">3° Trimestre (Jul - Sep)</option>
                            <option value="4">4° Trimestre (Oct - Dic)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="informeAnio">Año</label>
                        <select id="informeAnio" name="anio" class="form-select">
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <option value="<?php echo $y; ?>" <?php echo $y == $currentYear ? 'selected' : ''; ?>><?php echo $y; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="btnInformeWeb" onclick="cargarInformeWeb()" class="btn btn-primary flex-fill">
                    <i class="ti ti-eye me-1"></i>Ver en Web
                </button>
                <button type="button" id="btnInformePdf" onclick="descargarInformePDF()" class="btn btn-outline-secondary flex-fill">
                    <i class="ti ti-download me-1"></i>Descargar PDF
                </button>
            </div>
        </div>
    </div>
</div>

<div id="informeResultados" class="mt-3" style="display:none">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="card-title">
                    <i class="ti ti-clipboard-list me-2 text-primary"></i>Detalle de Errores REM
                </h3>
                <span id="informePeriodo" class="text-secondary small"></span>
            </div>
            <div class="d-flex gap-2">
                <button onclick="descargarInformePDF()" class="btn btn-outline-secondary btn-sm">
                    <i class="ti ti-download me-1"></i>PDF
                </button>
                <button onclick="document.getElementById('informeResultados').style.display='none'" class="btn-close" aria-label="Cerrar"></button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table" id="informeTable">
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
        <div id="informePagination" class="card-footer d-flex justify-content-between align-items-center" style="display:none">
            <button id="pagPrev" onclick="cambiarPagina(-1)" class="btn btn-outline-secondary btn-sm" disabled>
                <i class="ti ti-chevron-left"></i> Anterior
            </button>
            <span id="pagInfo" class="text-secondary small"></span>
            <button id="pagNext" onclick="cambiarPagina(1)" class="btn btn-outline-secondary btn-sm">
                Siguiente <i class="ti ti-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

<script>
const __dashboardStats = <?php echo $statsJson; ?>;
const __dashboardCharts = [];

document.addEventListener('DOMContentLoaded', () => {
    if (typeof initCountUp === 'function') {
        document.querySelectorAll('[data-countup]').forEach((el) => {
            initCountUp(el);
        });
    }
    if (typeof __dashboardStats !== 'undefined' && __dashboardStats) {
        initializeCharts(__dashboardStats);
    }
});

function toggleTrimestre() {
    const tipo = document.getElementById('informeTipo').value;
    document.getElementById('trimestreGroup').style.display = tipo === 'trimestral' ? '' : 'none';
}

let informeData = [];
let paginaActual = 1;
const FILAS_POR_PAGINA = 20;

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
    const button = document.getElementById('btnInformeWeb');
    if (button && button.disabled) return;

    const params = getInformeParams();
    params.set('format', 'json');
    try {
        showLoading();
        if (button) button.disabled = true;
        const response = await fetch('api/informe_errores.php?' + params.toString());
        const result = await parseJsonResponse(response);
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
        showError(error.message || 'Error al cargar el informe');
    } finally {
        hideLoading();
        if (button) button.disabled = false;
    }
}

function descargarInformePDF() {
    const button = document.getElementById('btnInformePdf');
    if (button && button.disabled) return;
    if (button) button.disabled = true;
    const params = getInformeParams();
    params.set('format', 'pdf');
    window.open('api/informe_errores.php?' + params.toString(), '_blank');
    setTimeout(() => { if (button) button.disabled = false; }, 1500);
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

async function parseJsonResponse(response) {
    const text = await response.text();
    let data = {};
    try {
        data = text ? JSON.parse(text) : {};
    } catch (error) {
        throw new Error('Respuesta inválida del servidor');
    }
    if (!response.ok) {
        throw new Error(data.message || 'Error en la petición');
    }
    return data;
}
</script>
