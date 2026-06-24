<?php
/**
 * Vista de Observaciones
 * CRUD completo de observaciones REM
 */

require_once 'models/Observation.php';
require_once 'models/Location.php';
require_once 'models/EstablecimientoAsignacion.php';
require_once 'config/constants.php';

$obsModel = new Observation();
$locModel = new Location();
$asigModel = new EstablecimientoAsignacion();
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['rol'];
$currentYear = $_SESSION['year'] ?? date('Y');
$requestedAction = $_GET['action'] ?? '';

// Obtener datos necesarios
$observations = $obsModel->getAll($currentYear, $userId, $userRole);
$comunas = $locModel->getAllComunas();

// Establecimientos según rol
$tieneAsignaciones = false;
if ($userRole === ROL_REGISTRADOR) {
    $tieneAsignaciones = $asigModel->tieneAsignaciones($userId, $currentYear);
    if ($tieneAsignaciones) {
        $establecimientos = $asigModel->getEstablecimientosByRegistrador($userId, $currentYear);
    } else {
        $establecimientos = [];
    }
} else {
    $establecimientos = $locModel->getAllEstablecimientos();
}

global $TIPOS_ERROR, $MESES;
?>

<div class="d-flex flex-column gap-3 rem-fade-in">
    <!-- Header -->
    <header class="page-header">
        <div>
            <h1 class="page-title">
                <i class="ti ti-file-text me-2 text-primary"></i>Listado de Observaciones
            </h1>
            <p class="page-subtitle">Gestiona y realiza seguimiento de tus registros REM</p>
        </div>
        <div class="page-actions">
            <?php if ($userRole === ROL_REGISTRADOR): ?>
                <?php if (!$tieneAsignaciones): ?>
                    <!-- Sin botones de acción si no tiene asignaciones -->
                <?php else: ?>
                    <button onclick="openImportModal()" class="btn btn-outline-secondary">
                        <i class="ti ti-upload me-1"></i>Importar
                    </button>
                    <button onclick="openCreateModal()" class="btn btn-primary">
                        <i class="ti ti-plus me-1"></i>Nueva Observación
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($userRole === ROL_REGISTRADOR && !$tieneAsignaciones): ?>
        <div class="card" style="background: rgba(245, 158, 11, 0.10); border: 0;">
            <div class="card-body empty-state">
                <div class="empty-icon" style="background: rgba(245, 158, 11, 0.18); color: var(--tblr-warning);">
                    <i class="ti ti-alert-triangle"></i>
                </div>
                <h3>No tiene establecimientos asignados</h3>
                <p>No podrá registrar observaciones hasta que su supervisor le asigne establecimientos para el año <strong><?php echo $currentYear; ?></strong>.</p>
                <p class="small">Contacte a su supervisor para solicitar la asignación.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label" for="searchInput">Buscar</label>
                    <div class="input-icon">
                        <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                        <input type="text" id="searchInput" placeholder="Buscar por establecimiento o detalle..."
                            class="form-control" oninput="filterTable()">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" for="filterEstado">Estado</label>
                    <select id="filterEstado" class="form-select" onchange="filterTable()">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="rechazado">Rechazado</option>
                        <option value="error">Error</option>
                        <option value="justificado">Justificado</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label" for="filterMes">Mes</label>
                    <select id="filterMes" class="form-select" onchange="filterTable()">
                        <option value="">Todos los meses</option>
                        <?php foreach ($MESES as $mes): ?>
                            <option value="<?php echo $mes; ?>">
                                <?php echo $mes; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-hover" id="observationsTable">
                <thead>
                    <tr>
                        <th>Establecimiento</th>
                        <th>Referencia</th>
                        <th>Tipo de Error</th>
                        <th>Estado</th>
                        <th>Registrado por</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($observations as $obs): ?>
                        <tr data-estado="<?php echo $obs['estado_actual']; ?>" data-mes="<?php echo $obs['mes']; ?>">
                            <td>
                                <div>
                                    <div class="fw-semibold">
                                        <?php echo htmlspecialchars($obs['nombre_corto']); ?>
                                    </div>
                                    <div class="small text-secondary">
                                        <?php echo htmlspecialchars($obs['comuna']) . ' • ' . htmlspecialchars($obs['mes']); ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-secondary">Serie
                                    <?php echo htmlspecialchars($obs['codigo_serie']); ?>
                                </div>
                                <div class="small text-secondary">Hoja
                                    <?php echo htmlspecialchars($obs['codigo_hoja']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="small text-secondary">
                                    <?php echo htmlspecialchars($obs['tipo_error']); ?>
                                </span>
                            </td>
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
                            <td>
                                <div class="fw-semibold">
                                    <?php echo htmlspecialchars($obs['nombre_registro']); ?>
                                </div>
                                <div class="small text-secondary">
                                    <?php echo $obs['fecha_registro'] ? date('d/m/Y', strtotime($obs['fecha_registro'])) : 'Sin fecha'; ?>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-ghost-secondary btn-icon dropdown-toggle" data-bs-toggle="dropdown" aria-label="Acciones">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="#" onclick="viewObservation(<?php echo $obs['id']; ?>); return false;">
                                            <i class="ti ti-eye me-2"></i>Ver detalle
                                        </a>
                                        <?php
                                        $canEdit = ($userRole === ROL_SUPERVISOR) ||
                                            ($userRole === ROL_REGISTRADOR && $obs['usuario_registro_id'] == $userId && $obs['estado_actual'] === ESTADO_PENDIENTE);
                                        if ($canEdit):
                                            ?>
                                            <a class="dropdown-item" href="#" onclick="editObservation(<?php echo $obs['id']; ?>); return false;">
                                                <i class="ti ti-edit me-2"></i>Editar
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($userRole === ROL_SUPERVISOR): ?>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item text-danger" href="#" onclick="deleteObservation(<?php echo $obs['id']; ?>); return false;">
                                                <i class="ti ti-trash me-2"></i>Enviar a papelera
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($observations)): ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div class="empty-icon"><i class="ti ti-mailbox"></i></div>
                                <h3>Sin observaciones en <?php echo htmlspecialchars($currentYear); ?></h3>
                                <p>
                                    <?php if ($userRole === ROL_REGISTRADOR): ?>
                                        Cuando registres una observación o importes un archivo, aparecerá aquí.
                                    <?php else: ?>
                                        Aún no se han registrado observaciones para el año seleccionado.
                                    <?php endif; ?>
                                </p>
                                <?php if ($userRole === ROL_REGISTRADOR && $tieneAsignaciones): ?>
                                    <a href="#" onclick="openCreateModal(); return false;" class="btn btn-primary">
                                        <i class="ti ti-plus me-1"></i>Crear primera observación
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Crear/Editar (Bootstrap) -->
<div id="modalObservation" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="modalTitle"><i class="ti ti-file-plus me-2 text-primary"></i>Nueva Observación</h5>
                    <div class="text-secondary">Complete los datos de la observación</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formObservation" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="obsId" value="">

                    <!-- Sección 1: Información General -->
                    <div class="card card-borderless mb-3">
                        <div class="card-header">
                            <h6 class="card-title">Información General</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 p-3 rounded rem-themed-surface">
                                <label class="form-label mb-0">Registrado por:</label>
                                <p class="fs-5 fw-bold mb-0"><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario'); ?></p>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Mes</label>
                                    <select id="mes" name="mes" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($MESES as $mes): ?>
                                            <option value="<?php echo $mes; ?>"><?php echo $mes; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Seleccione un mes.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Establecimiento</label>
                                    <select id="establecimiento_id" name="establecimiento_id" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($establecimientos as $est): ?>
                                            <option value="<?php echo $est['id']; ?>"
                                                data-codigo="<?php echo htmlspecialchars($est['codigo_establecimiento'] ?? $est['nombre_corto']); ?>">
                                                <?php echo htmlspecialchars($est['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Seleccione un establecimiento.</div>
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-md-6">
                                    <label class="form-label">Código Establecimiento</label>
                                    <input type="text" id="codigo_establecimiento" name="codigo_establecimiento"
                                        class="form-control" readonly placeholder="Se cargará automáticamente">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 2: Detalle de la Observación -->
                    <div class="card card-borderless mb-3">
                        <div class="card-header">
                            <h6 class="card-title">Detalle de la Observación</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label required">Tipo</label>
                                    <select id="tipo_error" name="tipo_error" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($TIPOS_ERROR as $tipo): ?>
                                            <option value="<?php echo htmlspecialchars($tipo); ?>"><?php echo htmlspecialchars($tipo); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Seleccione un tipo de error.</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Serie</label>
                                    <select id="codigo_serie" name="codigo_serie" class="form-select">
                                        <option value="">Seleccione...</option>
                                        <?php foreach ($SERIES_REM as $serie): ?>
                                            <option value="<?php echo htmlspecialchars($serie); ?>"><?php echo htmlspecialchars($serie); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-3 mt-2">
                                <div class="col-md-6" id="hojaRemContainer">
                                    <label class="form-label">REM (Hoja)</label>
                                    <select id="codigo_hoja" name="codigo_hoja" class="form-select" disabled>
                                        <option value="">Primero seleccione una Serie</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-0 mt-2">
                                <label class="form-label">Detalle de la Observación</label>
                                <textarea id="detalle_observacion" name="detalle_observacion" class="form-control" rows="4"
                                    placeholder="Descripción de la observación..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 3: Clasificación -->
                    <div class="card card-borderless mb-0">
                        <div class="card-header">
                            <h6 class="card-title">Clasificación y Seguimiento</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Plazo de Entrega</label>
                                    <select id="plazo_entrega" name="plazo_entrega" class="form-select">
                                        <option value="">Seleccione...</option>
                                        <option value="dentro_plazo">Dentro de Plazo</option>
                                        <option value="fuera_plazo">Fuera de Plazo</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Usa Validador</label>
                                    <select id="usa_validador" name="usa_validador" class="form-select">
                                        <option value="">Seleccione...</option>
                                        <option value="si">Sí</option>
                                        <option value="no">No</option>
                                        <option value="n/a">N/A</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-0 mt-2" id="respuestaContainer">
                                <label class="form-label">Respuesta del Establecimiento</label>
                                <textarea id="respuesta_establecimiento" name="respuesta_establecimiento" class="form-control" rows="3"
                                    placeholder="Respuesta recibida del establecimiento..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary ms-auto" id="btnSaveObservation">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="btnSaveSpinner"></span>
                        <span id="btnSaveText">Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Importar (Bootstrap) -->
<div id="modalImport" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="ti ti-upload me-2 text-primary"></i>Importar Observaciones</h5>
                    <div class="text-secondary">Carga masiva de observaciones desde archivo Excel (XLSX)</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Stepper -->
                <div class="steps steps-counter mb-4">
                    <div class="step-item active" id="stepperStep1">
                        <span class="step"><i class="ti ti-file-upload"></i></span>
                        <span class="step-title">Archivo</span>
                    </div>
                    <div class="step-item" id="stepperStep2">
                        <span class="step"><i class="ti ti-eye"></i></span>
                        <span class="step-title">Vista previa</span>
                    </div>
                    <div class="step-item" id="stepperStep3">
                        <span class="step"><i class="ti ti-circle-check"></i></span>
                        <span class="step-title">Confirmar</span>
                    </div>
                </div>

                <!-- Paso 1 -->
                <div id="importStep1">
                    <div class="text-center p-6 border-2 border-dashed rounded mb-4">
                        <p class="text-secondary mb-4">Seleccione un archivo Excel (.xlsx, .xls) con las observaciones</p>
                        <input type="file" id="csvFile" accept=".xlsx,.xls" class="d-none" onchange="previewImport()">
                        <button onclick="document.getElementById('csvFile').click()" class="btn btn-primary">
                            <i class="ti ti-file-upload me-1"></i>Seleccionar Archivo Excel
                        </button>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-4 rounded rem-themed-surface">
                        <div>
                            <p class="fw-semibold">¿No tiene la plantilla?</p>
                            <p class="text-secondary small">Descargue la plantilla Excel (.xlsx) con ejemplos</p>
                        </div>
                        <a href="api/import_template.php" class="btn btn-outline-secondary">
                            Descargar Plantilla Excel
                        </a>
                    </div>
                </div>

                <!-- Paso 2 -->
                <div id="importStep2" class="d-none">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-semibold">Resumen de importación:</span>
                            <button onclick="resetImport()" class="btn btn-link btn-sm text-primary p-0">← Volver</button>
                        </div>
                        <div class="row g-3 text-center" id="importSummary">
                            <div class="col-4">
                                <div class="p-3 rounded rem-themed-surface">
                                    <div id="totalRows" class="h3 mb-0">0</div>
                                    <div class="text-secondary small">Total filas</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 bg-lime-light rounded">
                                    <div id="validRows" class="h3 mb-0 text-green">0</div>
                                    <div class="text-green small">Válidas</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 bg-red-light rounded">
                                    <div id="errorRows" class="h3 mb-0 text-danger">0</div>
                                    <div class="text-danger small">Con errores</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="importErrors" class="d-none mb-3" style="max-height: 8rem; overflow-y: auto;">
                        <p class="small fw-semibold text-danger mb-1">Errores encontrados:</p>
                        <ul id="errorList" class="small text-danger"></ul>
                    </div>

                    <div id="importPreview" class="mb-3" style="max-height: 12rem; overflow-y: auto;">
                        <p class="small fw-semibold mb-1">Vista previa:</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-vcenter">
                                <thead>
                                    <tr>
                                        <th>Mes</th>
                                        <th>Establecimiento</th>
                                        <th>Tipo</th>
                                        <th>Serie</th>
                                        <th>REM</th>
                                        <th>Plazo</th>
                                        <th>Validador</th>
                                        <th>Detalle</th>
                                    </tr>
                                </thead>
                                <tbody id="previewBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="importProgress" class="d-none mb-3">
                        <div class="progress">
                            <div class="progress-bar progress-bar-indeterminate bg-primary" role="progressbar"></div>
                        </div>
                        <p class="text-secondary small mt-2 text-center">Importando observaciones...</p>
                    </div>

                    <div class="d-flex gap-3" id="importActions">
                        <button onclick="confirmImport()" class="btn btn-primary flex-fill" id="confirmImportBtn">
                            Confirmar Importación
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Detalles (Bootstrap) -->
<div id="modalDetails" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="ti ti-file-text me-2 text-primary"></i>Detalle de Observación</h5>
                    <div class="text-secondary">Resumen completo del registro</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Header con estado -->
                <div class="d-flex align-items-center justify-content-between mb-4 p-4 rounded rem-themed-surface">
                    <div>
                        <h4 class="h5 mb-1" id="detailEstablecimiento">-</h4>
                        <p class="text-secondary mb-0" id="detailComuna">-</p>
                        <p class="text-secondary small mb-0 mt-1" id="detailCodigoEst">-</p>
                    </div>
                    <span id="detailBadge" class="badge">-</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded rem-detail-tile">
                            <div class="small fw-bold text-primary mb-1">Mes / Año</div>
                            <div class="fw-semibold" id="detailMesAnio">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded rem-detail-tile rem-detail-tile--info">
                            <div class="small fw-bold text-primary mb-1">Referencia</div>
                            <div id="detailReferencia" class="fw-semibold">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded rem-detail-tile rem-detail-tile--warning">
                            <div class="small fw-bold text-warning mb-1">Tipo de Error</div>
                            <div id="detailTipoError" class="fw-semibold">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded rem-detail-tile rem-detail-tile--success">
                            <div class="small fw-bold text-success mb-1">Plazo Entrega</div>
                            <div id="detailPlazo" class="fw-semibold">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded rem-detail-tile rem-detail-tile--info">
                            <div class="small fw-bold text-info mb-1">Usa Validador</div>
                            <div id="detailValidador" class="fw-semibold">-</div>
                        </div>
                    </div>
                </div>

            <!-- Detalle de la observación -->
            <div class="mb-4">
                <div class="small fw-bold text-secondary mb-2">Detalle de la Observación</div>
                <div id="detailObservacion" class="p-4 rounded rem-themed-surface small" style="min-height: 80px;">-
                </div>
            </div>

            <!-- Respuesta (si existe) -->
            <div id="detailRespuestaSection" class="mb-4 d-none">
                <div class="small fw-bold text-secondary mb-2">Respuesta / Justificación</div>
                <div id="detailRespuesta" class="p-4 rounded rem-detail-tile rem-detail-tile--success small" style="min-height: 60px;">-
                </div>
            </div>

            <!-- Clasificación y Detalle Error (solo visibles si el supervisor los completó) -->
            <div id="detailClasificacionSection" class="mb-4 d-none">
                <div class="small fw-bold text-secondary mb-2">Clasificación de Respuesta</div>
                <div id="detailClasificacion" class="p-4 rounded rem-detail-tile rem-detail-tile--info small">-</div>
            </div>
            <div id="detailDetalleErrorSection" class="mb-4 d-none">
                <div class="small fw-bold text-secondary mb-2">Detalle Error</div>
                <div id="detailDetalleError" class="p-4 rounded rem-detail-tile rem-detail-tile--info small">-</div>
            </div>

            <!-- Historial de estados -->
            <div class="mb-4">
                <div class="small fw-bold text-secondary mb-2">Historial de Estados</div>
                <div id="detailHistorial" class="p-4 rounded rem-themed-surface small">
                    <div class="text-secondary">Cargando historial...</div>
                </div>
            </div>

            <!-- Info de registro -->
            <div class="row g-3 p-4 rounded rem-themed-surface">
                <div class="col-md-6">
                    <div class="small text-secondary text-uppercase">Registrado por</div>
                    <div id="detailRegistradoPor" class="fw-semibold">-</div>
                    <div id="detailFechaRegistro" class="small text-secondary">-</div>
                    <div id="detailFechaActualizacion" class="small text-secondary mt-1">-</div>
                </div>
                <div id="detailSupervisorInfo" class="col-md-6 d-none">
                    <div class="small text-secondary text-uppercase">Supervisado por</div>
                    <div id="detailSupervisadoPor" class="fw-semibold">-</div>
                    <div id="detailFechaSupervision" class="small text-secondary">-</div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="mt-4 d-flex flex-wrap gap-2">
                <span id="detailId" class="small text-secondary">ID: -</span>
            </div>
        </div>
    </div>
</div>

<script>
    // ============================================================
    // ObservationForm — encapsulación del modal crear/editar
    // ============================================================
    class ObservationForm {
        constructor() {
            this.modal = new bootstrap.Modal(document.getElementById('modalObservation'));
            this.init();
        }

        // ----------------------------------------------------------
        // Inicialización: wire-up de eventos (Task 2.2)
        // ----------------------------------------------------------
        init() {
            const form = document.getElementById('formObservation');

            form.addEventListener('submit', (e) => this.save(e));

            document.getElementById('tipo_error')
                .addEventListener('change', () => this.onTipoChange());

            document.getElementById('codigo_serie')
                .addEventListener('change', () => this.loadHojasREM());

            document.getElementById('establecimiento_id')
                .addEventListener('change', () => this.loadEstablecimientoCodigo());

            document.getElementById('modalObservation')
                .addEventListener('hidden.bs.modal', () => this.clearValidationErrors());
        }

        // ----------------------------------------------------------
        // Código establecimiento al cambiar dropdown (Task 2.5)
        // ----------------------------------------------------------
        loadEstablecimientoCodigo() {
            const select = document.getElementById('establecimiento_id');
            const selectedOption = select.options[select.selectedIndex];
            const codigo = selectedOption.getAttribute('data-codigo');

            document.getElementById('codigo_establecimiento').value = codigo || '';
        }

        // ----------------------------------------------------------
        // Carga dinámica de hojas REM por serie (Task 2.4)
        // ----------------------------------------------------------
        loadHojasREM() {
            const serieSelect = document.getElementById('codigo_serie');
            const hojaSelect = document.getElementById('codigo_hoja');
            const serieSeleccionada = serieSelect.value;
            const tipoError = document.getElementById('tipo_error').value;

            hojaSelect.innerHTML = '';

            if (tipoError === 'S/OBSERVACION') {
                document.getElementById('hojaRemContainer').classList.add('d-none');
                hojaSelect.value = '';
                return;
            }

            document.getElementById('hojaRemContainer').classList.remove('d-none');

            if (!serieSeleccionada) {
                hojaSelect.innerHTML = '<option value="">Primero seleccione una Serie</option>';
                hojaSelect.disabled = true;
                return;
            }

            const hojas = hojasPorSerie[serieSeleccionada] || [];

            if (hojas.length > 0) {
                hojaSelect.innerHTML = '<option value="">Seleccione...</option>';
                hojas.forEach(hoja => {
                    const option = document.createElement('option');
                    option.value = hoja.codigo;
                    option.textContent = hoja.nombre;
                    hojaSelect.appendChild(option);
                });
                hojaSelect.disabled = false;
            } else {
                hojaSelect.innerHTML = '<option value="">No hay hojas disponibles</option>';
                hojaSelect.disabled = true;
            }
        }

        // ----------------------------------------------------------
        // Cambio de tipo: oculta/muestra campos (Task 2.3)
        // ----------------------------------------------------------
        onTipoChange() {
            const tipoError = document.getElementById('tipo_error').value;
            const hojaContainer = document.getElementById('hojaRemContainer');
            const respuestaContainer = document.getElementById('respuestaContainer');

            if (tipoError === 'S/OBSERVACION') {
                hojaContainer.classList.add('d-none');
                document.getElementById('codigo_hoja').value = '';
                respuestaContainer.classList.add('d-none');
                document.getElementById('respuesta_establecimiento').value = '';
            } else {
                hojaContainer.classList.remove('d-none');
                respuestaContainer.classList.remove('d-none');
                this.loadHojasREM();
            }
        }

        // ----------------------------------------------------------
        // Abrir modal para crear (Task 2.2)
        // ----------------------------------------------------------
        openCreate() {
            document.getElementById('obsId').value = '';
            document.getElementById('modalTitle').textContent = 'Nueva Observación';
            document.getElementById('formObservation').reset();
            document.getElementById('codigo_establecimiento').value = '';

            const hojaSelect = document.getElementById('codigo_hoja');
            hojaSelect.innerHTML = '<option value="">Primero seleccione una Serie</option>';
            hojaSelect.disabled = true;

            document.getElementById('hojaRemContainer').classList.remove('d-none');
            document.getElementById('respuestaContainer').classList.remove('d-none');
            this.clearValidationErrors();
            this.modal.show();
        }

        // ----------------------------------------------------------
        // Abrir modal para editar (Task 2.2)
        // ----------------------------------------------------------
        async edit(id) {
            try {
                showLoading();
                const response = await fetchAPI(`observations.php?id=${id}`);

                if (response.success) {
                    const obs = response.data;
                    document.getElementById('obsId').value = obs.id;
                    document.getElementById('mes').value = obs.mes;
                    document.getElementById('establecimiento_id').value = obs.establecimiento_id;

                    this.loadEstablecimientoCodigo();

                    document.getElementById('tipo_error').value = obs.tipo_error;
                    this.onTipoChange();

                    document.getElementById('codigo_serie').value = obs.codigo_serie;

                    if (obs.tipo_error !== 'S/OBSERVACION') {
                        this.loadHojasREM();
                        document.getElementById('codigo_hoja').value = obs.codigo_hoja;
                    }

                    document.getElementById('detalle_observacion').value = obs.detalle_observacion;
                    document.getElementById('plazo_entrega').value = obs.plazo_entrega;
                    document.getElementById('usa_validador').value = obs.usa_validador || '';
                    document.getElementById('respuesta_establecimiento').value = obs.respuesta_establecimiento || '';

                    document.getElementById('modalTitle').textContent = 'Editar Observación';
                    this.clearValidationErrors();
                    this.modal.show();
                }

                hideLoading();
            } catch (error) {
                hideLoading();
                showError('Error al cargar la observación: ' + error.message);
            }
        }

        // ----------------------------------------------------------
        // Guardar / Actualizar (Task 2.6)
        // ----------------------------------------------------------
        async save(event) {
            event.preventDefault();
            this.clearValidationErrors();

            // --- Validación visual inline (Task 3.x) ---
            const form = document.getElementById('formObservation');
            const required = form.querySelectorAll('[required]');
            let valid = true;

            required.forEach(el => {
                if (!el.value.trim()) {
                    el.classList.add('is-invalid');
                    valid = false;
                }
            });

            if (!valid) return;

            const obsId = document.getElementById('obsId').value;
            const tipoError = document.getElementById('tipo_error').value;
            let usaValidador = document.getElementById('usa_validador').value;

            if (usaValidador === 'n/a') {
                usaValidador = 'no';
            }

            const formData = {
                mes: document.getElementById('mes').value,
                establecimiento_id: parseInt(document.getElementById('establecimiento_id').value),
                codigo_serie: document.getElementById('codigo_serie').value,
                codigo_hoja: tipoError === 'S/OBSERVACION' ? '' : document.getElementById('codigo_hoja').value,
                tipo_error: tipoError,
                detalle_observacion: document.getElementById('detalle_observacion').value,
                plazo_entrega: document.getElementById('plazo_entrega').value,
                usa_validador: usaValidador,
                respuesta_establecimiento: tipoError === 'S/OBSERVACION' ? '' : document.getElementById('respuesta_establecimiento').value
            };

            try {
                this.setLoading(true);

                let response;
                if (obsId) {
                    response = await fetchAPI(`observations.php?id=${obsId}`, {
                        method: 'PUT',
                        body: JSON.stringify(formData)
                    });
                } else {
                    response = await fetchAPI('observations.php', {
                        method: 'POST',
                        body: JSON.stringify(formData)
                    });
                }

                this.setLoading(false);

                if (response.success) {
                    showSuccess(obsId ? 'Observación actualizada correctamente' : 'Observación creada correctamente');
                    this.modal.hide();
                    setTimeout(() => location.reload(), 1500);
                } else if (response.errors) {
                    this.showValidationErrors(response.errors);
                }
            } catch (error) {
                this.setLoading(false);
                showError(error.message || 'Error al guardar la observación');
            }
        }

        // ----------------------------------------------------------
        // Mostrar errores de validación inline (Task 3.1)
        // ----------------------------------------------------------
        showValidationErrors(errors) {
            this.clearValidationErrors();
            errors.forEach(field => {
                const el = document.getElementById(field);
                if (el) el.classList.add('is-invalid');
            });
        }

        // ----------------------------------------------------------
        // Limpiar errores de validación (Task 3.2)
        // ----------------------------------------------------------
        clearValidationErrors() {
            document.querySelectorAll('#formObservation .is-invalid')
                .forEach(el => el.classList.remove('is-invalid'));
        }

        // ----------------------------------------------------------
        // Spinner en botón guardar (Task 3.3)
        // ----------------------------------------------------------
        setLoading(isLoading) {
            const btn = document.getElementById('btnSaveObservation');
            const spinner = document.getElementById('btnSaveSpinner');
            const text = document.getElementById('btnSaveText');

            btn.disabled = isLoading;
            spinner.classList.toggle('d-none', !isLoading);
            text.textContent = isLoading ? 'Guardando...' : 'Guardar';
        }
    }

    // Instancia global del formulario de observaciones
    const observationForm = new ObservationForm();

    // ============================================================
    // Variables globales
    // ============================================================
    let importPreviewData = null;
    const modalImport = new bootstrap.Modal(document.getElementById('modalImport'));
    const modalDetails = new bootstrap.Modal(document.getElementById('modalDetails'));

    // Datos de hojas REM por serie (generado desde PHP)
    const hojasPorSerie = <?php echo json_encode($HOJAS_POR_SERIE); ?>;
    const requestedAction = <?php echo json_encode($requestedAction); ?>;

    // ============================================================
    // Wrappers globales para compatibilidad con onclick en HTML
    // ============================================================
    function openCreateModal() { observationForm.openCreate(); }

    function editObservation(id) { observationForm.edit(id); }

    async function deleteObservation(id) {
        const reason = await remPrompt({
            title: 'Enviar a papelera',
            message: 'Indique el motivo para enviar esta observación a la papelera.',
            label: 'Motivo',
            placeholder: 'Eliminado por supervisor desde observaciones',
            multiline: true,
            confirmText: 'Enviar a papelera',
            cancelText: 'Cancelar',
        });
        if (reason === null) return;

        try {
            showLoading();
            const response = await fetchAPI(`observations.php?id=${id}`, {
                method: 'DELETE',
                body: JSON.stringify({ reason: reason.trim() || 'Eliminado por supervisor desde observaciones' })
            });
            hideLoading();

            if (response.success) {
                showSuccess('Observación enviada a papelera correctamente');
                setTimeout(() => location.reload(), 800);
            }
        } catch (error) {
            hideLoading();
            showError(error.message || 'Error al enviar la observación a papelera');
        }
    }

    // ============================================================
    // Filtros de tabla
    // ============================================================
    function filterTable() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const estado = document.getElementById('filterEstado').value;
        const mes = document.getElementById('filterMes').value;
        const rows = document.querySelectorAll('#observationsTable tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowEstado = row.dataset.estado;
            const rowMes = row.dataset.mes;

            const matchSearch = text.includes(search);
            const matchEstado = !estado || rowEstado === estado;
            const matchMes = !mes || rowMes === mes;

            row.style.display = (matchSearch && matchEstado && matchMes) ? '' : 'none';
        });
    }

    // ============================================================
    // Importación
    // ============================================================
    function openImportModal() {
        resetImport();
        modalImport.show();
    }

    function resetImport() {
        document.getElementById('importStep1').classList.remove('d-none');
        document.getElementById('importStep2').classList.add('d-none');
        document.getElementById('importErrors').classList.add('d-none');
        document.getElementById('importProgress').classList.add('d-none');
        document.getElementById('importActions').classList.remove('d-none');
        document.getElementById('confirmImportBtn').disabled = false;
        document.getElementById('confirmImportBtn').classList.remove('opacity-50');
        document.getElementById('csvFile').value = '';
        importPreviewData = null;
        document.getElementById('stepperStep1').classList.add('active');
        document.getElementById('stepperStep2').classList.remove('active');
        document.getElementById('stepperStep3').classList.remove('active');
    }

    async function previewImport() {
        const fileInput = document.getElementById('csvFile');
        if (!fileInput.files || fileInput.files.length === 0) return;

        const file = fileInput.files[0];
        const extension = file.name.split('.').pop().toLowerCase();
        if (!['xlsx', 'xls'].includes(extension)) {
            showError('Formato no válido. Seleccione un archivo Excel (.xlsx, .xls).');
            fileInput.value = '';
            return;
        }

        const formData = new FormData();
        formData.append('csv_file', file);
        formData.append('preview', '1');
        formData.append('year', <?php echo $currentYear; ?>);

        try {
            showLoading();

            const response = await fetch('api/import.php', {
                method: 'POST',
                body: formData
            });

            const data = await parseJsonResponse(response);
            hideLoading();

            if (data.success) {
                importPreviewData = data.data;
                showImportPreview(data.data);
            } else {
                showError(data.message || 'Error al procesar archivo');
            }
        } catch (error) {
            hideLoading();
            showError('Error al procesar archivo: ' + error.message);
        }
    }

    function showImportPreview(data) {
        document.getElementById('importStep1').classList.add('d-none');
        document.getElementById('importStep2').classList.remove('d-none');
        document.getElementById('stepperStep2').classList.add('active');
        document.getElementById('stepperStep3').classList.add('active');

        document.getElementById('totalRows').textContent = data.total;
        document.getElementById('validRows').textContent = data.valid;
        document.getElementById('errorRows').textContent = data.errors.length;

        const errorsDiv = document.getElementById('importErrors');
        const errorList = document.getElementById('errorList');
        if (data.errors.length > 0) {
            errorsDiv.classList.remove('d-none');
            errorList.innerHTML = data.errors.map(e =>
                `<li>Fila ${e.row}: ${e.message}</li>`
            ).join('');
        } else {
            errorsDiv.classList.add('d-none');
        }

        const previewBody = document.getElementById('previewBody');
        const previewItems = data.preview.slice(0, 5);
        previewBody.innerHTML = previewItems.map(item => `
            <tr>
                <td class="p-2">${escapeHtml(item.mes)}</td>
                <td class="p-2">${escapeHtml(item.establecimiento_nombre)}</td>
                <td class="p-2">${escapeHtml(item.tipo_error)}</td>
                <td class="p-2">${escapeHtml(item.codigo_serie || '-')}</td>
                <td class="p-2">${escapeHtml(item.codigo_hoja || '-')}</td>
                <td class="p-2">${escapeHtml(item.plazo_entrega || '-')}</td>
                <td class="p-2">${escapeHtml(item.usa_validador || '-')}</td>
                <td class="p-2">${escapeHtml(item.detalle_observacion ? item.detalle_observacion.substring(0, 40) + (item.detalle_observacion.length > 40 ? '...' : '') : '-')}</td>
            </tr>
        `).join('');

        if (data.preview.length > 5) {
            previewBody.innerHTML += `
                <tr>
                    <td colspan="8" class="p-2 text-center text-secondary">
                        ... y ${data.preview.length - 5} más
                    </td>
                </tr>
            `;
        }

        const confirmBtn = document.getElementById('confirmImportBtn');
        confirmBtn.disabled = data.valid === 0;
        confirmBtn.classList.toggle('opacity-50', data.valid === 0);
    }

    async function confirmImport() {
        if (!importPreviewData || importPreviewData.valid === 0) {
            showError('No hay registros válidos para importar');
            return;
        }

        const fileInput = document.getElementById('csvFile');
        if (!fileInput.files || fileInput.files.length === 0) {
            showError('Por favor seleccione el archivo nuevamente');
            resetImport();
            return;
        }

        const file = fileInput.files[0];
        const formData = new FormData();
        formData.append('csv_file', file);
        formData.append('confirm', '1');
        formData.append('year', <?php echo $currentYear; ?>);
        const confirmBtn = document.getElementById('confirmImportBtn');

        try {
            document.getElementById('importProgress').classList.remove('d-none');
            document.getElementById('importActions').classList.add('d-none');
            confirmBtn.disabled = true;

            const response = await fetch('api/import.php', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken() || ''
                },
                body: formData
            });

            const data = await parseJsonResponse(response);
            document.getElementById('importProgress').classList.add('d-none');
            document.getElementById('importActions').classList.remove('d-none');
            confirmBtn.disabled = false;

            if (data.success) {
                showSuccess(`Se importaron ${data.imported} observaciones correctamente`);
                modalImport.hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showError(data.message || 'Error al importar');
            }
        } catch (error) {
            document.getElementById('importProgress').classList.add('d-none');
            document.getElementById('importActions').classList.remove('d-none');
            confirmBtn.disabled = false;
            showError('Error al importar: ' + error.message);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (requestedAction === 'new') {
            <?php if ($userRole === ROL_REGISTRADOR && $tieneAsignaciones): ?>
                openCreateModal();
            <?php elseif ($userRole === ROL_REGISTRADOR): ?>
                showWarning('No tiene establecimientos asignados para crear observaciones en <?php echo htmlspecialchars($currentYear, ENT_QUOTES, 'UTF-8'); ?>.');
            <?php endif; ?>
        }
    });

    // ============================================================
    // Ver detalle de observación
    // ============================================================
    async function viewObservation(id) {
        try {
            showLoading();
            const response = await fetchAPI('observations.php?id=' + id);

            if (response.success) {
                const obs = response.data;

                document.getElementById('detailEstablecimiento').textContent = obs.nombre_corto || obs.nombre || '-';
                document.getElementById('detailComuna').textContent = obs.comuna || '-';
                document.getElementById('detailCodigoEst').textContent = obs.codigo_establecimiento ? 'Código: ' + obs.codigo_establecimiento : '-';

                const badge = document.getElementById('detailBadge');
                badge.textContent = obs.estado_actual ? obs.estado_actual.charAt(0).toUpperCase() + obs.estado_actual.slice(1) : '-';
                badge.className = 'badge bg-' + ({
                    'pendiente': 'yellow',
                    'aprobado': 'green',
                    'rechazado': 'red',
                    'error': 'red',
                    'justificado': 'blue'
                }[obs.estado_actual] || 'secondary') + ' text-' + ({
                    'pendiente': 'yellow-fg',
                    'aprobado': 'green-fg',
                    'rechazado': 'red-fg',
                    'error': 'red-fg',
                    'justificado': 'blue-fg'
                }[obs.estado_actual] || 'secondary-fg') + ' fw-normal';

                document.getElementById('detailMesAnio').textContent = (obs.mes || '-') + ' ' + (obs.anio || '');
                document.getElementById('detailReferencia').textContent = 'Serie ' + (obs.codigo_serie || '-') + ' / Hoja ' + (obs.codigo_hoja || '-');
                document.getElementById('detailTipoError').textContent = obs.tipo_error || '-';
                document.getElementById('detailPlazo').textContent = obs.plazo_entrega || 'No especificado';
                document.getElementById('detailObservacion').textContent = obs.detalle_observacion || 'Sin detalle registrado';

                const respuestaSection = document.getElementById('detailRespuestaSection');
                if (obs.respuesta_establecimiento) {
                    document.getElementById('detailRespuesta').textContent = obs.respuesta_establecimiento;
                    respuestaSection.classList.remove('d-none');
                } else {
                    respuestaSection.classList.add('d-none');
                }

                const clasifSection = document.getElementById('detailClasificacionSection');
                if (obs.clasificacion) {
                    document.getElementById('detailClasificacion').textContent = obs.clasificacion;
                    clasifSection.classList.remove('d-none');
                } else {
                    clasifSection.classList.add('d-none');
                }
                const detErrorSection = document.getElementById('detailDetalleErrorSection');
                if (obs.detalle_error) {
                    document.getElementById('detailDetalleError').textContent = obs.detalle_error;
                    detErrorSection.classList.remove('d-none');
                } else {
                    detErrorSection.classList.add('d-none');
                }

                document.getElementById('detailRegistradoPor').textContent = obs.nombre_registro || '-';
                document.getElementById('detailFechaRegistro').textContent = obs.fecha_registro ? formatDate(obs.fecha_registro) : '-';
                document.getElementById('detailFechaActualizacion').textContent = obs.fecha_actualizacion ? 'Última modificación: ' + formatDate(obs.fecha_actualizacion) : '';

                const supervisorInfo = document.getElementById('detailSupervisorInfo');
                if (obs.nombre_supervisor) {
                    document.getElementById('detailSupervisadoPor').textContent = obs.nombre_supervisor;
                    document.getElementById('detailFechaSupervision').textContent = obs.fecha_revision ? formatDate(obs.fecha_revision) : '-';
                    supervisorInfo.classList.remove('d-none');
                } else {
                    supervisorInfo.classList.add('d-none');
                }

                const validadorEl = document.getElementById('detailValidador');
                if (obs.tipo_error === 'S/OBSERVACION') {
                    validadorEl.textContent = 'N/A';
                } else if (obs.usa_validador && obs.usa_validador !== 'no') {
                    validadorEl.textContent = 'Sí';
                } else {
                    validadorEl.textContent = 'No';
                }

                document.getElementById('detailId').textContent = 'ID: ' + obs.id;

                await loadObservationHistory(id);

                modalDetails.show();
            }

            hideLoading();
        } catch (error) {
            hideLoading();
            showError('Error al cargar detalles: ' + error.message);
        }
    }

    async function loadObservationHistory(id) {
        const container = document.getElementById('detailHistorial');
        if (!container) return;

        container.innerHTML = '<div class="text-secondary">Cargando historial...</div>';

        try {
            const response = await fetchAPI('observations.php?action=historial&id=' + id);
            const items = response.data || [];

            if (items.length === 0) {
                container.innerHTML = '<div class="text-secondary">Sin historial registrado.</div>';
                return;
            }

            container.innerHTML = items.map(item => {
                const from = item.estado_anterior ? escapeHtml(item.estado_anterior) : 'Inicial';
                const to = item.estado_nuevo ? escapeHtml(item.estado_nuevo) : '-';
                const user = escapeHtml(item.usuario_nombre || 'Usuario');
                const comment = item.comentario ? `<div class="text-secondary mt-1">${escapeHtml(item.comentario)}</div>` : '';
                return `
                    <div class="d-flex gap-3 pb-3 mb-3 border-bottom">
                        <span class="status status-blue mt-1" aria-hidden="true"></span>
                        <div>
                            <div class="fw-semibold">${from} -> ${to}</div>
                            <div class="text-secondary">${user} · ${formatDateTime(item.fecha_cambio)}</div>
                            ${comment}
                        </div>
                    </div>
                `;
            }).join('');
        } catch (error) {
            container.innerHTML = '<div class="text-danger">No se pudo cargar el historial.</div>';
        }
    }

    // ============================================================
    // Utilidades
    // ============================================================
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-CL');
    }

    function formatDateTime(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleString('es-CL', { dateStyle: 'short', timeStyle: 'short' });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
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
