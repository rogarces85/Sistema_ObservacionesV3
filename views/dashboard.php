<?php
/**
 * Dashboard Light - Panel de Control
 * Sistema de Observaciones REM
 */

$usuarioId = $_SESSION['usuario_id'] ?? 0;
$rol = $_SESSION['rol'] ?? '';
$anio = $_SESSION['anio_trabajo'] ?? date('Y');
$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
$partesNombre = explode(' ', $nombreUsuario);
$primerNombre = $partesNombre[0] ?? 'Usuario';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center w-100 gap-3">
            <div>
                <div class="page-pretitle">Bienvenido de vuelta</div>
                <h2 class="page-title mb-0">
                    Hola, <?php echo htmlspecialchars($primerNombre); ?>
                    <span class="badge bg-blue-lt text-blue ms-2"><?php echo $anio; ?></span>
                </h2>
            </div>
            <div class="btn-list">
                <?php if ($rol === ROL_REGISTRADOR): ?>
                    <a href="?pagina=observaciones&anio=<?php echo $anio; ?>" class="btn btn-primary">
                        <?php echo tablerIcon('plus'); ?>
                        Nueva Observación
                    </a>
                <?php endif; ?>
                <?php if ($rol === ROL_SUPERVISOR): ?>
                    <a href="?pagina=supervision&anio=<?php echo $anio; ?>" class="btn btn-outline-secondary">
                        <?php echo tablerIcon('eye'); ?>
                        Supervisar
                    </a>
                <?php endif; ?>
                <button class="btn btn-icon btn-outline-secondary" onclick="location.reload()" title="Actualizar">
                    <?php echo tablerIcon('refresh'); ?>
                </button>
            </div>
        </div>
    </div>

    <div id="alertas-container"></div>

    <div class="row g-4 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Total Registradas</div>
                    </div>
                    <div class="h1 mb-3 mt-2" id="stat-total">
                        <span class="placeholder" style="width:60px"></span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="chart-sparkline" id="sparkline-total"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Pendientes</div>
                    </div>
                    <div class="h1 mb-3 mt-2" id="stat-pendientes">
                        <span class="placeholder" style="width:50px"></span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="chart-sparkline" id="sparkline-pendientes"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Aprobadas</div>
                    </div>
                    <div class="h1 mb-3 mt-2" id="stat-aprobadas">
                        <span class="placeholder" style="width:50px"></span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="chart-sparkline" id="sparkline-aprobadas"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="subheader">Con Problemas</div>
                    </div>
                    <div class="h1 mb-3 mt-2" id="stat-problemas">
                        <span class="placeholder" style="width:50px"></span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="chart-sparkline" id="sparkline-problemas"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo tablerIcon('table'); ?> Observaciones Recientes</h3>
                    <div class="card-actions">
                        <a href="?pagina=observaciones&anio=<?php echo $anio; ?>" class="btn btn-sm btn-ghost-primary">
                            Ver todas
                        </a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table card-table table-vcenter">
                        <thead>
                            <tr>
                                <th>Establecimiento</th>
                                <th>Serie</th>
                                <th>Mes</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th class="w-1"></th>
                            </tr>
                        </thead>
                        <tbody id="tabla-recientes">
                            <tr>
                                <td colspan="6" class="text-center py-4 text-secondary">
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo tablerIcon('bolt'); ?> Acciones Rápidas</h3>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php if ($rol === ROL_REGISTRADOR): ?>
                        <div class="col-6">
                            <a href="?pagina=observaciones&anio=<?php echo $anio; ?>" class="btn btn-outline-primary w-100 h-100">
                                <div class="d-flex flex-column align-items-center py-2">
                                    <?php echo tablerIcon('plus', 24); ?>
                                    <span class="mt-1">Nueva</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?pagina=importacion&anio=<?php echo $anio; ?>" class="btn btn-outline-primary w-100 h-100">
                                <div class="d-flex flex-column align-items-center py-2">
                                    <?php echo tablerIcon('upload', 24); ?>
                                    <span class="mt-1">Importar</span>
                                </div>
                            </a>
                        </div>
                        <?php endif; ?>
                        <?php if ($rol === ROL_SUPERVISOR): ?>
                        <div class="col-6">
                            <a href="?pagina=supervision&anio=<?php echo $anio; ?>" class="btn btn-outline-warning w-100 h-100">
                                <div class="d-flex flex-column align-items-center py-2">
                                    <?php echo tablerIcon('eye', 24); ?>
                                    <span class="mt-1">Supervisar</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?pagina=reportes&anio=<?php echo $anio; ?>" class="btn btn-outline-primary w-100 h-100">
                                <div class="d-flex flex-column align-items-center py-2">
                                    <?php echo tablerIcon('report', 24); ?>
                                    <span class="mt-1">Reportes</span>
                                </div>
                            </a>
                        </div>
                        <?php endif; ?>
                        <div class="col-6">
                            <a href="?pagina=perfil&anio=<?php echo $anio; ?>" class="btn btn-outline-secondary w-100 h-100">
                                <div class="d-flex flex-column align-items-center py-2">
                                    <?php echo tablerIcon('user', 24); ?>
                                    <span class="mt-1">Perfil</span>
                                </div>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?pagina=reportes&tipo=errores&anio=<?php echo $anio; ?>" class="btn btn-outline-danger w-100 h-100">
                                <div class="d-flex flex-column align-items-center py-2">
                                    <?php echo tablerIcon('alert-triangle', 24); ?>
                                    <span class="mt-1">Errores</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo tablerIcon('chart-donut'); ?> Distribución por Estado</h3>
                </div>
                <div class="card-body">
                    <div id="chart-donut" style="height: 250px">
                        <div class="text-center py-5 text-secondary">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                            Cargando...
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo tablerIcon('grid-dots'); ?> Heatmap Serie × Hoja</h3>
                    <div class="card-actions">
                        <select id="selector-mes-heatmap" class="form-select form-select-sm" style="width: auto">
                            <option value="">Todos los meses</option>
                            <?php
                            $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                            foreach ($meses as $i => $mes): ?>
                                <option value="<?php echo $i + 1; ?>"><?php echo $mes; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div id="chart-heatmap" style="height: 250px">
                        <div class="text-center py-5 text-secondary">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                            Cargando...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo tablerIcon('chart-bar'); ?> Top Establecimientos con Problemas</h3>
                </div>
                <div class="card-body p-0">
                    <div id="chart-top-problemas" class="table-responsive">
                        <table class="table table-sm table-vcenter mb-0">
                            <thead>
                                <tr>
                                    <th>Establecimiento</th>
                                    <th class="text-end">Errores</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-top-problemas">
                                <tr>
                                    <td colspan="2" class="text-center py-3 text-secondary">
                                        <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><?php echo tablerIcon('calendar'); ?> Observaciones por Mes</h3>
                </div>
                <div class="card-body">
                    <div id="chart-lineas" style="height: 200px">
                        <div class="text-center py-5 text-secondary">
                            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.dashboard-header {
    margin-bottom: 1.5rem;
}

.subheader {
    font-size: 0.75rem;
    color: var(--tblr-muted);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.chart-sparkline {
    height: 40px;
    width: 100%;
}

.btn.h-100 {
    min-height: 80px;
}

.btn-outline-primary:hover, .btn-outline-warning:hover, .btn-outline-danger:hover, .btn-outline-secondary:hover {
    transform: translateY(-2px);
}

.btn .ti {
    margin-bottom: 2px;
}

.table-vcenter > thead > tr > th,
.table-vcenter > tbody > tr > td {
    vertical-align: middle;
}

#tabla-recientes .badge {
    font-size: 0.65rem;
}
</style>

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