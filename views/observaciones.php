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

<div class="space-y-6">
    <!-- Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <div class="page-pretitle">Gestiona y realiza seguimiento de tus registros REM</div>
            <h2 class="page-title">Listado de Observaciones</h2>
        </div>
        <div class="btn-list">
            <?php if ($userRole === ROL_REGISTRADOR): ?>
                <?php if (!$tieneAsignaciones): ?>
                <?php else: ?>
                    <button onclick="openImportModal()" class="btn btn-secondary">
                        <?php echo tablerIcon('file-import'); ?>
                        Importar
                    </button>
                    <button onclick="openCreateModal()" class="btn btn-primary">
                        <?php echo tablerIcon('plus'); ?>
                        Nueva Observación
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($userRole === ROL_REGISTRADOR && !$tieneAsignaciones): ?>
        <div class="p-6 rounded-xl bg-amber-50 border border-amber-200 text-center">
            <div class="empty-icon"><i class="ti ti-alert-triangle"></i></div>
            <p class="font-bold text-amber-800 text-lg">No tiene establecimientos asignados</p>
            <p class="text-sm text-amber-700 mt-2">
                No tiene establecimientos asignados para el año <strong><?php echo $currentYear; ?></strong>. 
                No podrá registrar observaciones hasta que su supervisor le asigne establecimientos.
            </p>
            <p class="text-xs text-amber-600 mt-4">
                Contacte a su supervisor para solicitar la asignación de establecimientos.
            </p>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col">
                    <input type="text" id="searchInput" class="form-control" placeholder="Buscar por establecimiento o detalle..." oninput="filterTable()">
                </div>
                <div class="col-auto">
                    <select id="filterEstado" class="form-select" onchange="filterTable()">
                        <option value="">Todos los estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="rechazado">Rechazado</option>
                        <option value="error">Error</option>
                        <option value="justificado">Justificado</option>
                    </select>
                </div>
                <div class="col-auto">
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
    <div class="card">
        <div class="table-responsive">
            <table id="observationsTable" class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Establecimiento</th>
                        <th>Referencia</th>
                        <th>Tipo de Error</th>
                        <th>Estado</th>
                        <th>Registrado por</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($observations as $obs): ?>
                        <tr data-estado="<?php echo $obs['estado_actual']; ?>" data-mes="<?php echo $obs['mes']; ?>">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div>
                                        <div class="text-sm font-bold text-slate-800">
                                            <?php echo htmlspecialchars($obs['nombre_corto']); ?>
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            <?php echo htmlspecialchars($obs['comuna']) . ' • ' . htmlspecialchars($obs['mes']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-xs font-semibold text-slate-500">Serie
                                    <?php echo htmlspecialchars($obs['codigo_serie']); ?>
                                </div>
                                <div class="text-xs text-slate-400">Hoja
                                    <?php echo htmlspecialchars($obs['codigo_hoja']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="text-xs font-medium text-slate-600">
                                    <?php echo htmlspecialchars($obs['tipo_error']); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $badgeColors = [
                                    'pendiente' => 'bg-yellow text-yellow-fg',
                                    'aprobado' => 'bg-green text-green-fg',
                                    'rechazado' => 'bg-red text-red-fg',
                                    'error' => 'bg-red text-red-fg',
                                    'justificado' => 'bg-blue text-blue-fg',
                                ];
                                $badgeClass = $badgeColors[$obs['estado_actual']] ?? 'bg-secondary text-secondary-fg';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo ucfirst($obs['estado_actual']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-sm text-slate-700">
                                    <?php echo htmlspecialchars($obs['nombre_registro']); ?>
                                </div>
                                <div class="text-xs text-slate-400">
                                    <?php echo $obs['fecha_registro'] ? date('d/m/Y', strtotime($obs['fecha_registro'])) : 'Sin fecha'; ?>
                                </div>
                            </td>
                            <td class="text-right">
                                <button onclick="viewObservation(<?php echo $obs['id']; ?>)"
                                    class="btn-secondary px-3 py-1 text-xs" title="Ver detalle">
                                    <i class="ti ti-eye"></i>
                                </button>
                                <?php
                                $canEdit = ($userRole === ROL_SUPERVISOR) ||
                                    ($userRole === ROL_REGISTRADOR && $obs['usuario_registro_id'] == $userId && $obs['estado_actual'] === ESTADO_PENDIENTE);
                                if ($canEdit):
                                    ?>
                                    <button onclick="editObservation(<?php echo $obs['id']; ?>)"
                                        class="btn-secondary px-3 py-1 text-xs" title="Editar">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($observations)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-slate-400 py-8">
                                No se encontraron observaciones para el año
                                <?php echo $currentYear; ?>
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
                    <h5 class="modal-title" id="modalTitle">Nueva Observación</h5>
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
                            <div class="mb-3 p-3 bg-light rounded">
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
                                        class="form-control bg-light" readonly placeholder="Se cargará automáticamente">
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
                    <h5 class="modal-title">Importar Observaciones</h5>
                    <div class="text-secondary">Carga masiva de observaciones desde archivo Excel (XLSX)</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Paso 1 -->
                <div id="importStep1">
                    <div class="text-center p-6 border-2 border-dashed rounded mb-4">
                        <p class="text-secondary mb-4">Seleccione un archivo Excel (.xlsx) o CSV con las observaciones</p>
                        <input type="file" id="csvFile" accept=".xlsx,.xls,.csv" class="d-none" onchange="previewImport()">
                        <button onclick="document.getElementById('csvFile').click()" class="btn btn-primary">
                            Seleccionar Archivo Excel
                        </button>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-4 bg-light rounded">
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
                                <div class="p-3 bg-light rounded">
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

                    <div class="d-flex gap-3">
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
                    <h5 class="modal-title">Detalle de Observación</h5>
                    <div class="text-secondary">Resumen completo del registro</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Header con estado -->
                <div class="d-flex align-items-center justify-content-between mb-4 p-4 rounded bg-light">
                    <div>
                        <h4 class="h5 mb-1" id="detailEstablecimiento">-</h4>
                        <p class="text-secondary mb-0" id="detailComuna">-</p>
                        <p class="text-secondary small mb-0 mt-1" id="detailCodigoEst">-</p>
                    </div>
                    <span id="detailBadge" class="badge">-</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded bg-primary-light">
                            <div class="small fw-bold text-primary mb-1">Mes / Año</div>
                            <div class="fw-semibold" id="detailMesAnio">-</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded bg-purple-light">
                    <div class="text-xs text-violet-600 uppercase font-bold mb-1"><i class="ti ti-file-text me-1"></i>Referencia</div>
                    <div id="detailReferencia" class="font-semibold text-slate-800">-</div>
                </div>
                <div class="p-4 rounded-xl bg-amber-50">
                    <div class="text-xs text-amber-600 uppercase font-bold mb-1"><i class="ti ti-alert-triangle me-1"></i>Tipo de Error</div>
                    <div id="detailTipoError" class="font-semibold text-slate-800">-</div>
                </div>
                <div class="p-4 rounded-xl bg-emerald-50">
                    <div class="text-xs text-emerald-600 uppercase font-bold mb-1"><i class="ti ti-calendar me-1"></i>Plazo Entrega</div>
                    <div id="detailPlazo" class="font-semibold text-slate-800">-</div>
                </div>
                <div class="p-4 rounded-xl bg-teal-50">
                    <div class="text-xs text-teal-600 uppercase font-bold mb-1"><i class="ti ti-check me-1"></i>Usa Validador</div>
                    <div id="detailValidador" class="font-semibold text-slate-800">-</div>
                </div>
            </div>

            <!-- Detalle de la observación -->
            <div class="mb-6">
                <div class="text-sm font-bold text-slate-700 mb-2"><i class="ti ti-notes me-1"></i>Detalle de la Observación</div>
                <div id="detailObservacion" class="p-4 bg-slate-100 rounded-xl text-sm text-slate-700 min-h-[80px]">-
                </div>
            </div>

            <!-- Respuesta (si existe) -->
            <div id="detailRespuestaSection" class="mb-6 hidden">
                <div class="text-sm font-bold text-slate-700 mb-2"><i class="ti ti-message me-1"></i>Respuesta / Justificación</div>
                <div id="detailRespuesta" class="p-4 bg-emerald-50 rounded-xl text-sm text-slate-700 min-h-[60px]">-
                </div>
            </div>

            <!-- Clasificación y Detalle Error (solo visibles si el supervisor los completó) -->
            <div id="detailClasificacionSection" class="mb-6 hidden">
                <div class="text-sm font-bold text-slate-700 mb-2"><i class="ti ti-clipboard-list me-1"></i>Clasificación de Respuesta</div>
                <div id="detailClasificacion" class="p-4 bg-sky-50 rounded-xl text-sm text-slate-700">-</div>
            </div>
            <div id="detailDetalleErrorSection" class="mb-6 hidden">
                <div class="text-sm font-bold text-slate-700 mb-2"><i class="ti ti-search me-1"></i>Detalle Error</div>
                <div id="detailDetalleError" class="p-4 bg-sky-50 rounded-xl text-sm text-slate-700">-</div>
            </div>

            <!-- Info de registro -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-xl border border-slate-200">
                <div>
                    <div class="text-xs text-slate-400 uppercase">Registrado por</div>
                    <div id="detailRegistradoPor" class="font-semibold text-slate-700">-</div>
                    <div id="detailFechaRegistro" class="text-xs text-slate-400">-</div>
                    <div id="detailFechaActualizacion" class="text-xs text-slate-400 mt-1">-</div>
                </div>
                <div id="detailSupervisorInfo" class="hidden">
                    <div class="text-xs text-slate-400 uppercase">Supervisado por</div>
                    <div id="detailSupervisadoPor" class="font-semibold text-slate-700">-</div>
                    <div id="detailFechaSupervision" class="text-xs text-slate-400">-</div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="mt-4 flex flex-wrap gap-2">
                <span id="detailId" class="text-xs text-slate-400">ID: -</span>
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
                document.getElementById('hojaRemContainer').style.display = 'none';
                hojaSelect.value = '';
                return;
            }

            document.getElementById('hojaRemContainer').style.display = '';

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
                hojaContainer.style.display = 'none';
                document.getElementById('codigo_hoja').value = '';
                respuestaContainer.style.display = 'none';
                document.getElementById('respuesta_establecimiento').value = '';
            } else {
                hojaContainer.style.display = '';
                respuestaContainer.style.display = '';
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

            document.getElementById('hojaRemContainer').style.display = '';
            document.getElementById('respuestaContainer').style.display = '';
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

    // ============================================================
    // Wrappers globales para compatibilidad con onclick en HTML
    // ============================================================
    function openCreateModal() { observationForm.openCreate(); }

    function editObservation(id) { observationForm.edit(id); }

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
        document.getElementById('importStep1').classList.remove('hidden');
        document.getElementById('importStep2').classList.add('hidden');
        document.getElementById('csvFile').value = '';
        importPreviewData = null;
    }

    async function previewImport() {
        const fileInput = document.getElementById('csvFile');
        if (!fileInput.files || fileInput.files.length === 0) return;

        const file = fileInput.files[0];
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

            const data = await response.json();
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
        document.getElementById('importStep1').classList.add('hidden');
        document.getElementById('importStep2').classList.remove('hidden');

        document.getElementById('totalRows').textContent = data.total;
        document.getElementById('validRows').textContent = data.valid;
        document.getElementById('errorRows').textContent = data.errors.length;

        const errorsDiv = document.getElementById('importErrors');
        const errorList = document.getElementById('errorList');
        if (data.errors.length > 0) {
            errorsDiv.classList.remove('hidden');
            errorList.innerHTML = data.errors.map(e =>
                `<li>Fila ${e.row}: ${e.message}</li>`
            ).join('');
        } else {
            errorsDiv.classList.add('hidden');
        }

        const previewBody = document.getElementById('previewBody');
        const previewItems = data.preview.slice(0, 5);
        previewBody.innerHTML = previewItems.map(item => `
            <tr class="border-b border-slate-100">
                <td class="p-2">${item.mes}</td>
                <td class="p-2">${item.establecimiento_nombre}</td>
                <td class="p-2">${item.tipo_error}</td>
                <td class="p-2">${item.codigo_serie || '-'}</td>
                <td class="p-2">${item.codigo_hoja || '-'}</td>
                <td class="p-2">${item.plazo_entrega || '-'}</td>
                <td class="p-2">${item.usa_validador || '-'}</td>
                <td class="p-2">${item.detalle_observacion ? item.detalle_observacion.substring(0, 40) + (item.detalle_observacion.length > 40 ? '...' : '') : '-'}</td>
            </tr>
        `).join('');

        if (data.preview.length > 5) {
            previewBody.innerHTML += `
                <tr class="border-b border-slate-100">
                    <td colspan="8" class="p-2 text-center text-slate-400">
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

        try {
            showLoading();

            const response = await fetch('api/import.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            hideLoading();

            if (data.success) {
                showSuccess(`Se importaron ${data.imported} observaciones correctamente`);
                modalImport.hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showError(data.message || 'Error al importar');
            }
        } catch (error) {
            hideLoading();
            showError('Error al importar: ' + error.message);
        }
    }

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
                if (obs.respuesta) {
                    document.getElementById('detailRespuesta').textContent = obs.respuesta;
                    respuestaSection.classList.remove('hidden');
                } else {
                    respuestaSection.classList.add('hidden');
                }

                const clasifSection = document.getElementById('detailClasificacionSection');
                if (obs.clasificacion) {
                    document.getElementById('detailClasificacion').textContent = obs.clasificacion;
                    clasifSection.classList.remove('hidden');
                } else {
                    clasifSection.classList.add('hidden');
                }
                const detErrorSection = document.getElementById('detailDetalleErrorSection');
                if (obs.detalle_error) {
                    document.getElementById('detailDetalleError').textContent = obs.detalle_error;
                    detErrorSection.classList.remove('hidden');
                } else {
                    detErrorSection.classList.add('hidden');
                }

                document.getElementById('detailRegistradoPor').textContent = obs.nombre_registro || '-';
                document.getElementById('detailFechaRegistro').textContent = obs.fecha_registro ? formatDate(obs.fecha_registro) : '-';
                document.getElementById('detailFechaActualizacion').textContent = obs.fecha_actualizacion ? 'Última modificación: ' + formatDate(obs.fecha_actualizacion) : '';

                const supervisorInfo = document.getElementById('detailSupervisorInfo');
                if (obs.nombre_supervisor) {
                    document.getElementById('detailSupervisadoPor').textContent = obs.nombre_supervisor;
                    document.getElementById('detailFechaSupervision').textContent = obs.fecha_supervision ? formatDate(obs.fecha_supervision) : '-';
                    supervisorInfo.classList.remove('hidden');
                } else {
                    supervisorInfo.classList.add('hidden');
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

                modalDetails.show();
            }

            hideLoading();
        } catch (error) {
            hideLoading();
            showError('Error al cargar detalles: ' + error.message);
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
</script>