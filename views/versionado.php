<?php
/**
 * Vista de Versionado - Fase 11
 * Lista cronológica de snapshots, creación y restauración
 * Solo accesible para rol Supervisor
 */

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    echo '<div class="p-6 text-center"><h2 class="text-xl font-bold text-rose-600">Acceso Denegado</h2><p>Solo los supervisores pueden acceder a esta sección.</p></div>';
    return;
}

$usuarioId = $_SESSION['usuario_id'];
$anioActual = $_SESSION['year'] ?? date('Y');
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <div class="page-pretitle">Gestión de versiones y snapshots del sistema</div>
            <h2 class="page-title">Versionado del Sistema</h2>
        </div>
        <div class="btn-list">
            <button id="btnCrearVersion" class="btn btn-primary">
                <?php echo tablerIcon('camera-plus'); ?>
                Crear Snapshot
            </button>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row g-3" id="statsContainer">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-primary text-white avatar"><?php echo tablerIcon('versions'); ?></span></div>
                        <div class="col"><div class="font-weight-medium" id="statTotalVersiones">-</div><div class="text-secondary">Total Versiones</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-green text-white avatar"><?php echo tablerIcon('camera-check'); ?></span></div>
                        <div class="col"><div class="font-weight-medium" id="statUltimaVersion">-</div><div class="text-secondary">Última Versión</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-azure text-white avatar"><?php echo tablerIcon('file'); ?></span></div>
                        <div class="col"><div class="font-weight-medium" id="statTotalArchivos">-</div><div class="text-secondary">Archivos en Última</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-yellow text-white avatar"><?php echo tablerIcon('alert-triangle'); ?></span></div>
                        <div class="col"><div class="font-weight-medium">Solo código</div><div class="text-secondary">Excluye uploads, vendor, .git</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alerta informativa -->
    <div class="alert alert-info alert-dismissible" role="alert">
        <h4 class="alert-title"><?php echo tablerIcon('info-circle'); ?> Información importante</h4>
        <div class="text-secondary">
            <ul class="mb-0 mt-2">
                <li>Los snapshots incluyen solo código fuente activo (.php, .js, .css, .sql, .json, .md)</li>
                <li>Se excluyen: node_modules/, .git/, uploads/, vendor/, *.log, *.tmp, assets/cache/, .env</li>
                <li>El rollback solo restaura archivos. Los cambios de esquema de BD requieren migraciones manuales</li>
                <li>Cada rollback genera un nuevo registro de versión para mantener el historial</li>
            </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>

    <!-- Tabla de versiones -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Historial de Versiones</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped" id="tablaVersiones">
                <thead>
                    <tr>
                        <th>Versión</th>
                        <th>Descripción</th>
                        <th>Autor</th>
                        <th>Archivos</th>
                        <th>Fecha de Creación</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaVersiones">
                    <tr><td colspan="6" class="text-center text-muted py-4">Cargando versiones...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Creación de Snapshot -->
<div class="modal fade" id="modalCrearVersion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><?php echo tablerIcon('camera-plus'); ?> Crear Nuevo Snapshot</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCrearVersion">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Se creará un snapshot completo</strong> del código fuente actual del sistema.
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">Descripción del cambio</label>
                        <textarea id="crearDescripcion" class="form-control" rows="3"
                                  placeholder="Describa los cambios incluidos en esta versión..." required></textarea>
                        <small class="form-hint">Este campo es obligatorio para identificar la versión</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Archivos incluidos</label>
                        <div class="small text-secondary">
                            <span class="badge bg-green-lt">.php</span>
                            <span class="badge bg-green-lt">.js</span>
                            <span class="badge bg-green-lt">.css</span>
                            <span class="badge bg-green-lt">.sql</span>
                            <span class="badge bg-green-lt">.json</span>
                            <span class="badge bg-green-lt">.md</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Archivos excluidos</label>
                        <div class="small text-secondary">
                            <span class="badge bg-red-lt">node_modules/</span>
                            <span class="badge bg-red-lt">.git/</span>
                            <span class="badge bg-red-lt">uploads/</span>
                            <span class="badge bg-red-lt">vendor/</span>
                            <span class="badge bg-red-lt">*.log</span>
                            <span class="badge bg-red-lt">*.tmp</span>
                            <span class="badge bg-red-lt">.env</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnConfirmarCrear">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="btnCrearSpinner"></span>
                        <?php echo tablerIcon('camera'); ?>
                        Crear Snapshot
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Detalle de Versión -->
<div class="modal fade" id="modalDetalleVersion" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Versión <span id="detVersionTag" class="badge bg-primary-lt ms-2"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded bg-primary-lt">
                            <div class="small text-primary fw-bold mb-1">Descripción</div>
                            <div id="detDescripcion" class="fw-semibold">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded bg-azure-lt">
                            <div class="small text-azure fw-bold mb-1">Autor</div>
                            <div id="detAutor" class="fw-semibold">-</div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="small fw-bold mb-1">Fecha de creación</div>
                        <div id="detFechaCreacion">-</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small fw-bold mb-1">Total de archivos</div>
                        <div id="detTotalArchivos">-</div>
                    </div>
                </div>

                <hr>

                <h6 class="fw-bold mb-3">Manifiesto de Archivos</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped" id="tablaManifiesto">
                        <thead>
                            <tr>
                                <th>Ruta Relativa</th>
                                <th>MD5</th>
                                <th>Tamaño</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoManifiesto">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Restauración -->
<div class="modal fade" id="modalRestaurar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><?php echo tablerIcon('alert-triangle'); ?> Confirmar Restauración</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <h4 class="alert-title">Advertencia: Restauración de archivos</h4>
                    <div>
                        Esta acción sobrescribirá los archivos actuales del sistema con los de la versión
                        <strong id="restVersionTag"></strong>.
                    </div>
                </div>

                <div class="alert alert-warning">
                    <h4 class="alert-title">Importante: Base de datos</h4>
                    <div>
                        El snapshot <strong>no incluye la base de datos</strong>. Si hay cambios de esquema en la BD
                        entre la versión actual y la versión de destino, deberá ejecutar las migraciones SQL manualmente.
                    </div>
                </div>

                <div class="mb-3">
                    <p><strong>Detalles de la restauración:</strong></p>
                    <ul class="mb-0">
                        <li>Se restaurarán todos los archivos del snapshot seleccionado</li>
                        <li>Los archivos actuales serán sobrescritos</li>
                        <li>Se creará un nuevo registro de versión documentando el rollback</li>
                        <li>Si la restauración falla a medio camino, se mostrará la lista de archivos no restaurados</li>
                    </ul>
                </div>

                <div class="mb-3">
                    <label class="form-check form-check-single">
                        <input type="checkbox" id="confirmarRestauracion" class="form-check-input" required>
                        <span class="form-check-label">
                            Entiendo que los archivos serán sobrescritos y que debo verificar migraciones de BD manualmente
                        </span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" id="btnConfirmarRestaurar" disabled>
                    <span class="spinner-border spinner-border-sm me-2 d-none" id="btnRestaurarSpinner"></span>
                    <?php echo tablerIcon('arrow-back'); ?>
                    Restaurar Versión
                </button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/versionado.js"></script>
