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
$anioActual = $_SESSION['anio_trabajo'] ?? date('Y');
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

<div class="modal-backdrop" id="modalCrearVersionBackdrop" onclick="if(event.target===this)VersionApp.cerrarModal('CrearVersion')"></div>
<div class="modal-container" id="modalCrearVersion">
    <div class="modal">
        <div class="modal-header modal-header-primary">
            <h3><?php echo tablerIcon('camera-plus'); ?> Crear Nuevo Snapshot</h3>
            <button onclick="VersionApp.cerrarModal('CrearVersion')" class="modal-close"><?php echo tablerIcon('x'); ?></button>
        </div>
        <form id="formCrearVersion">
            <div class="modal-body">
                <div class="modal-alert modal-alert-warning">
                    <div class="modal-alert-icon"><?php echo tablerIcon('alert-triangle', 20); ?></div>
                    <div class="modal-alert-content">Se creará un snapshot completo del código fuente actual del sistema.</div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label required">Descripción del cambio</label>
                    <textarea id="crearDescripcion" class="form-control" rows="3" placeholder="Describa los cambios incluidos en esta versión..." required></textarea>
                    <div class="form-text">Este campo es obligatorio para identificar la versión</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Archivos incluidos</label>
                    <div>
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
                    <div>
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
                <button type="button" class="btn btn-ghost" onclick="VersionApp.cerrarModal('CrearVersion')">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnConfirmarCrear"><?php echo tablerIcon('camera'); ?> Crear Snapshot</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="modalDetalleVersionBackdrop" onclick="if(event.target===this)VersionApp.cerrarModal('DetalleVersion')"></div>
<div class="modal-container" id="modalDetalleVersion">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3><?php echo tablerIcon('versions'); ?> Detalle de Versión <span id="detVersionTag" class="badge bg-blue-lt ms-2"></span></h3>
            <button onclick="VersionApp.cerrarModal('DetalleVersion')" class="modal-close"><?php echo tablerIcon('x'); ?></button>
        </div>
        <div class="modal-body">
            <div class="modal-info-grid">
                <div class="modal-info-item"><label>Descripción</label><span id="detDescripcion" class="fw-semibold">-</span></div>
                <div class="modal-info-item"><label>Autor</label><span id="detAutor" class="fw-semibold">-</span></div>
                <div class="modal-info-item"><label>Fecha de creación</label><span id="detFechaCreacion">-</span></div>
                <div class="modal-info-item"><label>Total archivos</label><span id="detTotalArchivos">-</span></div>
            </div>
            <div class="modal-section">
                <div class="modal-section-title">Manifiesto de Archivos</div>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr><th>Ruta Relativa</th><th>MD5</th><th>Tamaño</th></tr>
                        </thead>
                        <tbody id="cuerpoManifiesto"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="VersionApp.cerrarModal('DetalleVersion')">Cerrar</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="modalRestaurarBackdrop" onclick="if(event.target===this)VersionApp.cerrarModal('Restaurar')"></div>
<div class="modal-container" id="modalRestaurar">
    <div class="modal">
        <div class="modal-header modal-header-warning">
            <h3><?php echo tablerIcon('alert-triangle'); ?> Confirmar Restauración</h3>
            <button onclick="VersionApp.cerrarModal('Restaurar')" class="modal-close"><?php echo tablerIcon('x'); ?></button>
        </div>
        <div class="modal-body">
            <div class="modal-alert modal-alert-danger">
                <div class="modal-alert-icon"><?php echo tablerIcon('alert-circle', 20); ?></div>
                <div class="modal-alert-content">
                    <strong>Advertencia: Restauración de archivos</strong>
                    <p class="mb-0 mt-1">Esta acción sobrescribirá los archivos actuales del sistema con los de la versión <strong id="restVersionTag"></strong>.</p>
                </div>
            </div>
            <div class="modal-alert modal-alert-warning mt-3">
                <div class="modal-alert-icon"><?php echo tablerIcon('alert-triangle', 20); ?></div>
                <div class="modal-alert-content">
                    <strong>Importante: Base de datos</strong>
                    <p class="mb-0 mt-1">El snapshot no incluye la base de datos. Si hay cambios de esquema en la BD entre la versión actual y la de destino, deberá ejecutar las migraciones SQL manualmente.</p>
                </div>
            </div>
            <div class="mt-3">
                <p><strong>Detalles de la restauración:</strong></p>
                <ul class="mb-2">
                    <li>Se restaurarán todos los archivos del snapshot seleccionado</li>
                    <li>Los archivos actuales serán sobrescritos</li>
                    <li>Se creará un nuevo registro de versión documentando el rollback</li>
                    <li>Si la restauración falla a medio camino, se mostrará la lista de archivos no restaurados</li>
                </ul>
            </div>
            <div class="form-check">
                <input type="checkbox" id="confirmarRestauracion" class="form-check-input" required>
                <label class="form-check-label" for="confirmarRestauracion">Entiendo que los archivos serán sobrescritos y que debo verificar migraciones de BD manualmente</label>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="VersionApp.cerrarModal('Restaurar')">Cancelar</button>
            <button type="button" class="btn btn-warning" id="btnConfirmarRestaurar" disabled><?php echo tablerIcon('arrow-back'); ?> Restaurar Versión</button>
        </div>
    </div>
</div>

<script src="assets/js/versionado.js"></script>
