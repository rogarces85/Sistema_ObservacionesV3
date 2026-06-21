<?php
/**
 * Vista de Supervisión
 * Panel completo para aprobar, rechazar y gestionar observaciones
 * Solo accesible para supervisores
 */

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    echo '<div class="p-6 text-center"><h2 class="text-xl font-bold text-rose-600">Acceso Denegado</h2><p>Solo los supervisores pueden acceder a esta sección.</p></div>';
    return;
}

require_once 'models/Observation.php';
require_once 'models/User.php';
require_once 'models/Location.php';

$obsModel = new Observation();
$userModel = new User();
$locationModel = new Location();
$currentYear = $_SESSION['year'] ?? date('Y');

// Obtener datos para filtros
$registradores = $userModel->getByRole(ROL_REGISTRADOR);
$comunas = $locationModel->getComunas();
?>

<div class="d-flex flex-column gap-3 rem-fade-in">
    <!-- Header -->
    <header class="page-header">
        <div>
            <h1 class="page-title">
                <i class="ti ti-eye me-2 text-primary"></i>Panel de Supervisión
            </h1>
            <p class="page-subtitle">Revise y gestione las observaciones registradas</p>
        </div>
        <div class="page-actions align-items-center flex-wrap">
            <span id="selectedCount" class="badge badge-soft-primary me-2 d-none">
                <i class="ti ti-checkbox me-1"></i><span class="fw-semibold">0</span> seleccionadas
            </span>
            <button id="btnApproveSelected" class="btn btn-primary" disabled>
                <i class="ti ti-check me-1"></i>Aprobar
            </button>
            <button id="btnCancelSelected" class="btn btn-warning" disabled>
                <i class="ti ti-player-pause me-1"></i>Cancelar
            </button>
            <button id="btnDeleteSelected" class="btn btn-danger" disabled>
                <i class="ti ti-trash me-1"></i>Eliminar
            </button>
        </div>
    </header>

    <!-- Progress bar for bulk actions -->
    <div id="bulkProgress" class="d-none">
        <div class="progress" style="height: 0.5rem;">
            <div class="progress-bar progress-bar-indeterminate bg-primary" role="progressbar"></div>
        </div>
        <p class="text-secondary small mt-1" id="bulkProgressText">Procesando...</p>
    </div>

    <!-- Filtros -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h3 class="card-title mb-0"><i class="ti ti-filter me-2 text-primary"></i>Filtros</h3>
            </div>
            <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Estado</label>
                <select id="filterEstado" class="form-select">
                    <option value="">Todos</option>
                    <option value="<?php echo ESTADO_PENDIENTE; ?>">Pendiente</option>
                    <option value="<?php echo ESTADO_APROBADO; ?>">Aprobado</option>
                    <option value="<?php echo ESTADO_RECHAZADO; ?>">Rechazado</option>
                    <option value="<?php echo ESTADO_ERROR; ?>">Error</option>
                    <option value="<?php echo ESTADO_JUSTIFICADO; ?>">Justificado</option>
                </select>
            </div>

            <div class="col-md-6 col-lg-3">
                <label class="form-label">Mes</label>
                <select id="filterMes" class="form-select">
                    <option value="">Todos</option>
                    <option value="Enero">Enero</option>
                    <option value="Febrero">Febrero</option>
                    <option value="Marzo">Marzo</option>
                    <option value="Abril">Abril</option>
                    <option value="Mayo">Mayo</option>
                    <option value="Junio">Junio</option>
                    <option value="Julio">Julio</option>
                    <option value="Agosto">Agosto</option>
                    <option value="Septiembre">Septiembre</option>
                    <option value="Octubre">Octubre</option>
                    <option value="Noviembre">Noviembre</option>
                    <option value="Diciembre">Diciembre</option>
                </select>
            </div>

            <div class="col-md-6 col-lg-3">
                <label class="form-label">Comuna</label>
                <select id="filterComuna" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($comunas as $comuna): ?>
                        <option value="<?php echo $comuna['id']; ?>">
                            <?php echo htmlspecialchars($comuna['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6 col-lg-3">
                <label class="form-label">Establecimiento</label>
                <select id="filterEstablecimiento" class="form-select" disabled>
                    <option value="">Todos</option>
                </select>
            </div>

            <div class="col-md-6 col-lg-3">
                <label class="form-label">Registrador</label>
                <select id="filterRegistrador" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($registradores as $reg): ?>
                        <option value="<?php echo $reg['id']; ?>">
                            <?php echo htmlspecialchars($reg['nombre_completo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6 col-lg-3">
                <label class="form-label">Búsqueda</label>
                <input type="text" id="filterBusqueda" class="form-control" placeholder="Buscar en detalles..." />
            </div>

            <div class="col-12">
                <div class="btn-list">
                    <button id="btnApplyFilters" class="btn btn-primary">
                        Aplicar Filtros
                    </button>
                    <button id="btnClearFilters" class="btn btn-outline-secondary">
                        Limpiar
                    </button>
                </div>
            </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Observaciones -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Observaciones <span id="obsCount" class="text-secondary"></span>
            </h3>
            <div class="card-actions">
                <label class="form-check">
                    <input type="checkbox" class="form-check-input" id="selectAll">
                    <span class="form-check-label">Seleccionar Todas</span>
                </label>
            </div>
        </div>

        <div id="loadingIndicator" class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th class="w-1"></th>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Establecimiento</th>
                        <th>Mes</th>
                        <th>Tipo Error</th>
                        <th>Estado</th>
                        <th>Registrador</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="9"><div class="placeholder-glow"><span class="placeholder col-12"></span></div></td></tr>
                    <tr><td colspan="9"><div class="placeholder-glow"><span class="placeholder col-10"></span></div></td></tr>
                    <tr><td colspan="9"><div class="placeholder-glow"><span class="placeholder col-11"></span></div></td></tr>
                    <tr><td colspan="9"><div class="placeholder-glow"><span class="placeholder col-9"></span></div></td></tr>
                    <tr><td colspan="9"><div class="placeholder-glow"><span class="placeholder col-12"></span></div></td></tr>
                </tbody>
            </table>
        </div>

        <div id="observationsTable" class="d-none table-responsive">
            <table class="table table-vcenter card-table table-hover">
                <thead>
                    <tr>
                        <th class="w-1"></th>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Establecimiento</th>
                        <th>Mes</th>
                        <th>Tipo Error</th>
                        <th>Estado</th>
                        <th>Registrador</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="observationsBody">
                    <!-- Se llenará dinámicamente -->
                </tbody>
            </table>
        </div>

        <div id="emptyState" class="d-none card-body text-center py-8">
            <div class="empty-state">
                <div class="empty-icon"><i class="ti ti-inbox"></i></div>
                <h3>Sin observaciones</h3>
                <p>No se encontraron observaciones con los filtros aplicados.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalle (Tabler) -->
<div id="detailModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="ti ti-file-text me-2 text-primary"></i>Detalle de Observación</h5>
                    <div class="text-secondary">Resumen completo del registro</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="detailContent">
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación (Tabler) -->
<div id="confirmModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="confirmTitle"><i class="ti ti-shield-check me-2 text-warning"></i>Confirmar Acción</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
                <div class="mt-4">
                    <label class="form-label">Comentario (opcional)</label>
                    <textarea id="confirmComment" class="form-control" rows="3"></textarea>
                </div>
                <div id="approveExtraFields" class="d-none mt-4">
                    <div class="mb-3">
                        <label class="form-label">Clasificación de Respuesta *</label>
                        <div class="mt-2">
                            <label class="form-check me-4">
                                <input type="radio" name="estadoResultante" value="sin_observacion" class="form-check-input">
                                <span class="form-check-label">Sin Observación</span>
                            </label>
                            <label class="form-check">
                                <input type="radio" name="estadoResultante" value="error" class="form-check-input">
                                <span class="form-check-label">Error</span>
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Clasificación</label>
                        <select id="approveClasificacion" class="form-select">
                            <option value="">Sin clasificar</option>
                            <option value="corregido">Corregido</option>
                            <option value="error">Error</option>
                            <option value="sin_respuesta">Sin respuesta del Establecimiento</option>
                            <option value="respuesta_incorrecta">Respuesta incorrecta de Establecimiento</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Detalle Error</label>
                        <input type="text" id="approveDetalleError" class="form-control" placeholder="Descripción del error si aplica...">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="confirmActionBtn">
                    <i class="ti ti-check me-1"></i>Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentObservations = [];
    let selectedIds = [];
    let currentAction = '';

    // Cargar observaciones al cargar la página
    document.addEventListener('DOMContentLoaded', function () {
        loadObservations();
        setupEventListeners();
    });

    function setupEventListeners() {
        // Filtros
        document.getElementById('btnApplyFilters').addEventListener('click', loadObservations);
        document.getElementById('btnClearFilters').addEventListener('click', clearFilters);

        // Seleccionar todas
        document.getElementById('selectAll').addEventListener('click', toggleSelectAll);

        // Botón de aprobación masiva
        document.getElementById('btnApproveSelected').addEventListener('click', () => approveSelected());
        
        // Botón de cancelación masiva
        document.getElementById('btnCancelSelected').addEventListener('click', () => cancelSelected());
        
        // Botón de eliminación masiva
        document.getElementById('btnDeleteSelected').addEventListener('click', () => deleteSelected());

        // Cargar establecimientos al cambiar comuna
        document.getElementById('filterComuna').addEventListener('change', loadEstablecimientos);
    }

    async function loadObservations() {
        const loadingIndicator = document.getElementById('loadingIndicator');
        const observationsTable = document.getElementById('observationsTable');
        const emptyState = document.getElementById('emptyState');

        loadingIndicator.classList.remove('d-none');
        observationsTable.classList.add('d-none');
        emptyState.classList.add('d-none');

        const filters = {
            anio: <?php echo $currentYear; ?>,
            estado: document.getElementById('filterEstado').value,
            mes: document.getElementById('filterMes').value,
            establecimiento_id: document.getElementById('filterEstablecimiento').value,
            usuario_registro_id: document.getElementById('filterRegistrador').value,
            busqueda: document.getElementById('filterBusqueda').value
        };

        try {
            const response = await fetch('api/supervision.php?action=get_filtered&' + new URLSearchParams(filters));
            const data = await response.json();

            if (data.success) {
                currentObservations = data.data;
                renderObservations(currentObservations);
                document.getElementById('obsCount').textContent = `(${currentObservations.length})`;

                if (currentObservations.length > 0) {
                    observationsTable.classList.remove('d-none');
                } else {
                    emptyState.classList.remove('d-none');
                }
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error al cargar observaciones:', error);
            showError('Error al cargar observaciones: ' + error.message);
        } finally {
            loadingIndicator.classList.add('d-none');
        }
    }

    function renderObservations(observations) {
        const tbody = document.getElementById('observationsBody');
        tbody.innerHTML = '';

        observations.forEach(obs => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td>
                <input type="checkbox" class="form-check-input obs-checkbox" value="${obs.id}">
            </td>
            <td>#${obs.id}</td>
            <td>${formatDate(obs.fecha_registro)}</td>
            <td>
                <div class="fw-semibold">${escapeHtml(obs.nombre_corto)}</div>
                <div class="small text-secondary">${escapeHtml(obs.comuna)}</div>
            </td>
            <td>${escapeHtml(obs.mes)}</td>
            <td><span class="small">${escapeHtml(obs.tipo_error)}</span></td>
            <td>${getEstadoBadge(obs.estado_actual)}</td>
            <td class="small">${escapeHtml(obs.nombre_registro)}</td>
            <td class="text-end">
                <div class="dropdown">
                    <button class="btn btn-ghost-secondary btn-icon dropdown-toggle" data-bs-toggle="dropdown">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon"><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/><circle cx="12" cy="5" r="1"/></svg>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#" onclick="viewDetail(${obs.id}); return false;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2"><circle cx="12" cy="12" r="2"/><path d="M22 12c-2.667 4.667-6 7-10 7s-7.333-2.333-10-7c2.667-4.667 6-7 10-7s7.333 2.333 10 7z"/></svg>
                            Ver detalle
                        </a>
                        ${obs.estado_actual === '<?php echo ESTADO_PENDIENTE; ?>' ? `
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-success" href="#" onclick="approveSingle(${obs.id}); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2"><path d="M5 12l5 5l10 -10"/></svg>
                                Aprobar
                            </a>
                            <a class="dropdown-item text-warning" href="#" onclick="cancelSingle(${obs.id}); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
                                Cancelar
                            </a>
                            <a class="dropdown-item text-danger" href="#" onclick="deleteSingle(${obs.id}); return false;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon me-2"><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>
                                Eliminar
                            </a>
                        ` : ''}
                    </div>
                </div>
            </td>
        `;
            tbody.appendChild(tr);
        });

        // Actualizar listeners de checkboxes
        document.querySelectorAll('.obs-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedIds);
        });
    }

    function updateSelectedIds() {
        selectedIds = Array.from(document.querySelectorAll('.obs-checkbox:checked')).map(cb => cb.value);
        const btnApprove = document.getElementById('btnApproveSelected');
        const btnCancel = document.getElementById('btnCancelSelected');
        const btnDelete = document.getElementById('btnDeleteSelected');
        const countDisplay = document.getElementById('selectedCount');

        const hasSelection = selectedIds.length > 0;
        btnApprove.disabled = !hasSelection;
        btnCancel.disabled = !hasSelection;
        btnDelete.disabled = !hasSelection;

        // Mostrar/ocultar contador de seleccionadas
        if (hasSelection) {
            countDisplay.classList.remove('d-none');
            countDisplay.querySelector('.font-medium').textContent = selectedIds.length;
        } else {
            countDisplay.classList.add('d-none');
        }
    }

    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll').checked;
        document.querySelectorAll('.obs-checkbox').forEach(cb => {
            cb.checked = selectAll;
        });
        updateSelectedIds();
    }

    async function viewDetail(id) {
        try {
            const response = await fetch(`api/supervision.php?action=get_detail&id=${id}`);
            const data = await response.json();

            if (data.success) {
                showDetailModal(data.data, data.historial);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            showError('Error al cargar detalle: ' + error.message);
        }
    }

    function showDetailModal(obs, historial) {
        const content = document.getElementById('detailContent');
        content.innerHTML = `
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <p class="small text-secondary mb-1">Establecimiento</p>
                <p class="fw-bold mb-0">${escapeHtml(obs.establecimiento)}</p>
                ${obs.codigo_establecimiento ? `<p class="small text-secondary mb-0">Código: ${escapeHtml(obs.codigo_establecimiento)}</p>` : ''}
            </div>
            <div class="col-md-6">
                <p class="small text-secondary mb-1">Estado</p>
                <p class="mb-0">${getEstadoBadge(obs.estado_actual)}</p>
            </div>
            <div class="col-md-6">
                <p class="small text-secondary mb-1">Año/Mes</p>
                <p class="mb-0">${obs.anio} - ${escapeHtml(obs.mes)}</p>
            </div>
            <div class="col-md-6">
                <p class="small text-secondary mb-1">Registrador</p>
                <p class="mb-0">${escapeHtml(obs.nombre_registro)}</p>
            </div>
            <div class="col-12">
                <p class="small text-secondary mb-1">Tipo de Error</p>
                <p class="mb-0">${escapeHtml(obs.tipo_error)}</p>
            </div>
            <div class="col-md-6">
                <p class="small text-secondary mb-1">Plazo Entrega</p>
                <p class="mb-0">${obs.plazo_entrega ? escapeHtml(obs.plazo_entrega.replace('_', ' ')) : '-'}</p>
            </div>
            <div class="col-md-6">
                <p class="small text-secondary mb-1">Usa Validador</p>
                <p class="mb-0">${obs.usa_validador ? escapeHtml(obs.usa_validador) : '-'}</p>
            </div>
            <div class="col-md-6">
                <p class="small text-secondary mb-1">Serie REM</p>
                <p class="mb-0">${obs.codigo_serie ? escapeHtml(obs.codigo_serie) : '-'}</p>
            </div>
            <div class="col-md-6">
                <p class="small text-secondary mb-1">Hoja REM</p>
                <p class="mb-0">${obs.codigo_hoja ? escapeHtml(obs.codigo_hoja) : '-'}</p>
            </div>
            <div class="col-12">
                <p class="small text-secondary mb-1">Detalle de Observación</p>
                <p class="mb-0" style="white-space: pre-wrap;">${escapeHtml(obs.detalle_observacion)}</p>
            </div>
            ${obs.respuesta_establecimiento ? `
            <div class="col-12">
                <p class="small text-secondary mb-1">Respuesta del Establecimiento</p>
                <p class="mb-0" style="white-space: pre-wrap;">${escapeHtml(obs.respuesta_establecimiento)}</p>
            </div>
            ` : ''}
            ${obs.clasificacion ? `
            <div class="col-12">
                <div class="p-3 bg-primary-lt rounded-3">
                    <p class="small fw-semibold text-primary mb-1">Clasificación de Respuesta</p>
                    <p class="mb-0">${escapeHtml(obs.clasificacion)}</p>
                </div>
            </div>
            ` : ''}
            ${obs.detalle_error ? `
            <div class="col-12">
                <div class="p-3 bg-primary-lt rounded-3">
                    <p class="small fw-semibold text-primary mb-1">Detalle Error</p>
                    <p class="mb-0">${escapeHtml(obs.detalle_error)}</p>
                </div>
            </div>
            ` : ''}
            ${obs.fecha_actualizacion ? `
            <div class="col-12">
                <hr class="my-2">
                <p class="small text-secondary text-end mb-0">Última modificación: ${formatDate(obs.fecha_actualizacion)}</p>
            </div>
            ` : ''}
        </div>

        <h4 class="fw-bold mb-3">Historial de Cambios</h4>
        <div class="timeline">
            ${historial.map(h => `
                <div class="timeline-event">
                    <div class="timeline-event-badge bg-primary"></div>
                    <div class="timeline-event-card card">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold">${escapeHtml(h.usuario_nombre)}</span>
                                <span class="text-secondary small">${formatDate(h.fecha_cambio)}</span>
                            </div>
                            <p class="mb-1 small">
                                ${h.estado_anterior ? escapeHtml(h.estado_anterior) : '<em>inicial</em>'}
                                → <strong>${escapeHtml(h.estado_nuevo)}</strong>
                            </p>
                            ${h.comentario ? `<p class="mb-0 text-secondary small">${escapeHtml(h.comentario)}</p>` : ''}
                        </div>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

        const detailModal = new bootstrap.Modal(document.getElementById('detailModal'));
    detailModal.show();
    }

    function closeDetailModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('detailModal'));
        if (modal) modal.hide();
    }

    function approveSingle(id) {
        performAction('approve', [id], '¿Aprobar esta observación?');
    }

    function approveSelected() {
        performAction('approve', selectedIds, `¿Aprobar ${selectedIds.length} observaciones seleccionadas?`);
    }

    function cancelSingle(id) {
        performAction('cancel', [id], '¿Cancelar esta observación?', 'Cancelar Observación');
    }

    function cancelSelected() {
        performAction('cancel', selectedIds, `¿Cancelar ${selectedIds.length} observaciones seleccionadas?`, 'Cancelar Observaciones');
    }

    function deleteSingle(id) {
        performAction('delete', [id], '¿Eliminar esta observación? Se moverá a la papelera de reciclaje.', 'Eliminar Observación');
    }

    function deleteSelected() {
        performAction('delete', selectedIds, `¿Eliminar ${selectedIds.length} observaciones seleccionadas? Se moverán a la papelera de reciclaje.`, 'Eliminar Observaciones');
    }

    function performAction(action, ids, message, title = null) {
        currentAction = action;
        document.getElementById('confirmTitle').textContent = title || (ids.length > 1 ? 'Aprobar Observaciones' : 'Aprobar Observación');
        document.getElementById('confirmMessage').textContent = message;
        document.getElementById('confirmComment').value = '';

        // Mostrar/ocultar campos de clasificación solo al aprobar
        const extraFields = document.getElementById('approveExtraFields');
        if (action === 'approve') {
            extraFields.classList.remove('d-none');
            document.getElementById('approveClasificacion').value = '';
            document.getElementById('approveDetalleError').value = '';
            const radios = document.querySelectorAll('input[name="estadoResultante"]');
            radios.forEach(r => r.checked = false);
        } else {
            extraFields.classList.add('d-none');
        }

        const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
        confirmModal.show();

        document.getElementById('confirmActionBtn').onclick = async () => {
            const comment = document.getElementById('confirmComment').value;
            const clasificacion = document.getElementById('approveClasificacion').value;
            const detalleError = document.getElementById('approveDetalleError').value;
            let estadoResultante = '';

            if (action === 'approve' && ids.length === 1) {
                const selected = document.querySelector('input[name="estadoResultante"]:checked');
                if (!selected) {
                    alert('Debe seleccionar "Sin Observación" o "Error" como Clasificación de Respuesta');
                    return;
                }
                estadoResultante = selected.value;
            }

            await executeAction(action, ids, comment, clasificacion, detalleError, estadoResultante);
            closeConfirmModal();
        };
    }

    async function executeAction(action, ids, comment, clasificacion = '', detalleError = '', estadoResultante = '') {
        const progressEl = document.getElementById('bulkProgress');
        const progressText = document.getElementById('bulkProgressText');
        progressEl.classList.remove('d-none');
        progressText.textContent = `Procesando ${ids.length} observación(es)...`;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            const payload = {
                id: ids.length === 1 ? ids[0] : ids,
                comment: comment || undefined,
                reason: comment || undefined
            };

            if (action === 'approve') {
                payload.clasificacion = clasificacion || undefined;
                payload.detalle_error = detalleError || undefined;
                if (estadoResultante) {
                    payload.estado_resultante = estadoResultante;
                }
            }

            const response = await fetch(`api/supervision.php?action=${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            progressEl.classList.add('d-none');

            if (data.success) {
                const actionTexts = {
                    'approve': 'Observación(es) aprobada(s) correctamente',
                    'cancel': 'Observación(es) cancelada(s) correctamente',
                    'delete': 'Observación(es) eliminada(s) correctamente'
                };
                showSuccess(actionTexts[action] || data.message);
                loadObservations();
                selectedIds = [];
                document.getElementById('selectAll').checked = false;
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            progressEl.classList.add('d-none');
            showError('Error: ' + error.message);
        }
    }

    function closeConfirmModal() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
        if (modal) modal.hide();
    }

    function clearFilters() {
        document.getElementById('filterEstado').value = '';
        document.getElementById('filterMes').value = '';
        document.getElementById('filterComuna').value = '';
        document.getElementById('filterEstablecimiento').value = '';
        document.getElementById('filterRegistrador').value = '';
        document.getElementById('filterBusqueda').value = '';
        loadObservations();
    }

    async function loadEstablecimientos() {
        const comunaId = document.getElementById('filterComuna').value;
        const select = document.getElementById('filterEstablecimiento');

        select.innerHTML = '<option value="">Todos</option>';
        select.disabled = !comunaId;

        if (comunaId) {
            try {
                const response = await fetch(`api/locations.php?action=get_establecimientos&comuna_id=${comunaId}`);
                const data = await response.json();

                if (data.success) {
                    data.data.forEach(est => {
                        const option = document.createElement('option');
                        option.value = est.id;
                        option.textContent = est.nombre_corto || est.nombre;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error al cargar establecimientos:', error);
            }
        }
    }

    // Utilidades
    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('es-CL', { day: '2-digit', month: '2-digit', year: 'numeric' });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function getEstadoBadge(estado) {
        const badges = {
            '<?php echo ESTADO_PENDIENTE; ?>': '<span class="badge bg-yellow text-yellow-fg">Pendiente</span>',
            '<?php echo ESTADO_APROBADO; ?>': '<span class="badge bg-green text-green-fg">Aprobado</span>',
            '<?php echo ESTADO_RECHAZADO; ?>': '<span class="badge bg-red text-red-fg">Rechazado</span>',
            '<?php echo ESTADO_ERROR; ?>': '<span class="badge bg-red text-red-fg">Error</span>',
            '<?php echo ESTADO_JUSTIFICADO; ?>': '<span class="badge bg-blue text-blue-fg">Justificado</span>'
        };
        return badges[estado] || `<span class="badge">${estado}</span>`;
    }
</script>

<style>
    .btn-icon {
        padding: 0.25rem 0.5rem;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 1.25rem;
        transition: transform 0.2s;
    }

    .btn-icon:hover {
        transform: scale(1.2);
    }
</style>