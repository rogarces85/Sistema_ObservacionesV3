<?php
/**
 * Vista del Dashboard - Panel de Control
 * Sistema de Observaciones REM
 */

$usuarioId = $_SESSION['user_id'] ?? 0;
$rol = $_SESSION['rol'] ?? '';
$anio = $_SESSION['year'] ?? date('Y');
$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
?>

<div class="page-header d-flex flex-wrap justify-content-between align-items-center">
    <div>
        <div class="page-pretitle">Resumen estadístico del sistema de observaciones REM</div>
        <h2 class="page-title">Panel de Control <span id="anio-titulo"><?php echo $anio; ?></span></h2>
    </div>
    <div class="btn-list">
        <?php if ($rol === ROL_REGISTRADOR): ?>
            <a href="?page=observaciones&year=<?php echo $anio; ?>" class="btn btn-primary">
                <?php echo tablerIcon('plus'); ?>
                Nueva Observación
            </a>
        <?php endif; ?>
        <?php if ($rol === ROL_SUPERVISOR): ?>
            <a href="?page=supervision&year=<?php echo $anio; ?>" class="btn btn-outline-secondary">
                <?php echo tablerIcon('eye'); ?>
                Supervisar
            </a>
        <?php endif; ?>
        <label class="form-check form-switch form-check-single ms-2" title="Actualización automática">
            <input class="form-check-input" type="checkbox" id="auto-refresh-toggle" checked>
            <span class="form-check-label small ms-1">Auto</span>
        </label>
    </div>
</div>

<div id="alertas-container"></div>

<!-- Pestañas del Dashboard -->
<ul class="nav nav-tabs mb-3" id="dashboard-tabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-resumen" type="button" role="tab">
            <?php echo tablerIcon('chart-bar'); ?> Resumen
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-kanban" type="button" role="tab">
            <?php echo tablerIcon('layout-kanban'); ?> Kanban
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-timeline" type="button" role="tab">
            <?php echo tablerIcon('history'); ?> Actividad
        </button>
    </li>
</ul>

<div class="tab-content" id="dashboard-tab-content">
    <!-- Pestaña Resumen -->
    <div class="tab-pane fade show active" id="tab-resumen" role="tabpanel">
        <!-- Tarjetas de Estadísticas -->
        <div class="row g-3" id="stats-container">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-status-top bg-blue"></div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-md bg-blue-lt text-blue-fg"><?php echo tablerIcon('chart-bar'); ?></div>
                            <div>
                                <div class="h1 mb-0 text-primary" id="stat-total">
                                    <span class="skeleton" style="width:80px;height:2rem;display:inline-block"></span>
                                </div>
                                <div class="text-secondary small fw-semibold">Total Registradas</div>
                            </div>
                        </div>
                        <div id="sparkline-total" class="mt-2" style="height:40px"></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-status-top bg-yellow"></div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-md bg-yellow-lt text-yellow-fg"><?php echo tablerIcon('clock'); ?></div>
                            <div>
                                <div class="h1 mb-0 text-yellow" id="stat-pendientes">
                                    <span class="skeleton" style="width:60px;height:2rem;display:inline-block"></span>
                                </div>
                                <div class="text-secondary small fw-semibold">Pendientes</div>
                            </div>
                        </div>
                        <div id="sparkline-pendientes" class="mt-2" style="height:40px"></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-status-top bg-green"></div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-md bg-green-lt text-green-fg"><?php echo tablerIcon('check-circle'); ?></div>
                            <div>
                                <div class="h1 mb-0 text-green" id="stat-aprobadas">
                                    <span class="skeleton" style="width:60px;height:2rem;display:inline-block"></span>
                                </div>
                                <div class="text-secondary small fw-semibold">Aprobadas</div>
                            </div>
                        </div>
                        <div id="sparkline-aprobadas" class="mt-2" style="height:40px"></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-status-top bg-red"></div>
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-md bg-red-lt text-red-fg"><?php echo tablerIcon('alert-triangle'); ?></div>
                            <div>
                                <div class="h1 mb-0 text-red" id="stat-problemas">
                                    <span class="skeleton" style="width:60px;height:2rem;display:inline-block"></span>
                                </div>
                                <div class="text-secondary small fw-semibold">Con Problemas</div>
                            </div>
                        </div>
                        <div id="sparkline-problemas" class="mt-2" style="height:40px"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row g-3 mt-2">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?php echo tablerIcon('chart-pie'); ?> Distribución por Estado</h3>
                    </div>
                    <div class="card-body">
                        <div id="chart-donut" style="height:280px">
                            <div class="text-center py-5 text-secondary">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Cargando...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?php echo tablerIcon('activity'); ?> Top Tipos de Error</h3>
                    </div>
                    <div class="card-body">
                        <div id="chart-barras" style="height:280px">
                            <div class="text-center py-5 text-secondary">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Cargando...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?php echo tablerIcon('calendar'); ?> Observaciones por Mes</h3>
                        <div class="ms-auto">
                            <select id="selector-mes-local" class="form-select form-select-sm" style="width:auto">
                                <option value="">Todos los meses</option>
                                <?php
                                $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
                                foreach ($meses as $mes): ?>
                                    <option value="<?php echo $mes; ?>"><?php echo $mes; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="chart-lineas" style="height:280px">
                            <div class="text-center py-5 text-secondary">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Cargando...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Observaciones Recientes -->
        <div class="card mt-3">
            <div class="card-header">
                <h3 class="card-title"><?php echo tablerIcon('list'); ?> Últimas Observaciones</h3>
                <a href="?page=observaciones&year=<?php echo $anio; ?>" class="btn btn-ghost-primary btn-sm ms-auto">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <div id="recientes-container" class="table-responsive">
                    <div class="text-center py-5 text-secondary">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        Cargando...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestaña Kanban -->
    <div class="tab-pane fade" id="tab-kanban" role="tabpanel">
        <div id="kanban-container">
            <div class="text-center py-5 text-secondary">
                <div class="spinner-border me-2" role="status"></div>
                Cargando tablero kanban...
            </div>
        </div>
    </div>

    <!-- Pestaña Timeline -->
    <div class="tab-pane fade" id="tab-timeline" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?php echo tablerIcon('history'); ?> Actividad Reciente</h3>
            </div>
            <div class="card-body">
                <div id="timeline-container">
                    <div class="text-center py-5 text-secondary">
                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        Cargando...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.DASHBOARD_CONFIG = {
    anio: <?php echo $anio; ?>,
    rol: '<?php echo $rol; ?>',
    usuarioId: <?php echo $usuarioId; ?>,
    autoRefreshInterval: 120000,
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || ''
};
</script>
<script src="assets/js/dashboard.js"></script>
