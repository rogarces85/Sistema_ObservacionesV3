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

<div class="space-y-6">
    <!-- Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <div class="page-pretitle">Revise y gestione las observaciones registradas</div>
            <h2 class="page-title">Panel de Supervisión</h2>
        </div>
        <div class="btn-list">
            <span id="selectedCount" class="text-secondary d-none">
                <span class="fw-semibold">0</span> seleccionadas
            </span>
            <button id="btnApproveSelected" class="btn btn-primary" disabled>
                <?php echo tablerIcon('check'); ?>
                Aprobar
            </button>
            <button id="btnCancelSelected" class="btn btn-warning" disabled>
                <?php echo tablerIcon('x'); ?>
                Cancelar
            </button>
            <button id="btnDeleteSelected" class="btn btn-danger" disabled>
                <?php echo tablerIcon('trash'); ?>
                Eliminar
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card">
        <div class="card-body">
            <h3 class="card-title"><?php echo tablerIcon('filter'); ?> Filtros</h3>
            <div class="row g-3">
                <div class="col-lg-3">
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
                <div class="col-lg-3">
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
                <div class="col-lg-3">
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
                <div class="col-lg-3">
                    <label class="form-label">Establecimiento</label>
                    <select id="filterEstablecimiento" class="form-select" disabled>
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-lg-3">
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
                <div class="col-lg-3">
                    <label class="form-label">Búsqueda</label>
                    <input type="text" id="filterBusqueda" class="form-control" placeholder="Buscar en detalles..." />
                </div>
                <div class="col-12">
                    <div class="btn-list">
                        <button id="btnApplyFilters" class="btn btn-primary">Aplicar Filtros</button>
                        <button id="btnClearFilters" class="btn btn-outline-secondary">Limpiar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Observaciones -->
    <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-800">
                Observaciones <span id="obsCount" class="text-slate-500"></span>
            </h3>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="selectAll" class="form-checkbox">
                <label for="selectAll" class="text-sm text-slate-600">Seleccionar Todas</label>
            </div>
        </div>

        <div id="loadingIndicator" class="text-center py-8 text-slate-500">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
            <p class="mt-2">Cargando observaciones...</p>
        </div>

        <div id="observationsTable" class="hidden table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th class="w-12"></th>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Establecimiento</th>
                        <th>Mes</th>
                        <th>Tipo Error</th>
                        <th>Estado</th>
                        <th>Registrador</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="observationsBody">
                    <!-- Se llenará dinámicamente -->
                </tbody>
            </table>
        </div>

        <div id="emptyState" class="hidden text-center py-8 text-slate-500">
            <p>No se encontraron observaciones con los filtros aplicados.</p>
        </div>
    </div>
</div>

<!-- Modal de Detalle -->
<div id="detailModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Observación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="detailContent">
                <!-- Se llenará dinámicamente -->
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación -->
<div id="confirmModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmTitle">Confirmar Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
                <div class="mt-4">
                    <label class="form-label">Comentario (opcional)</label>
                    <textarea id="confirmComment" class="form-control" rows="3"></textarea>
                </div>
                <!-- Campos de clasificación y detalle - solo visibles al aprobar -->
                <div id="approveExtraFields" class="d-none mt-4">
                    <div class="mb-3">
                        <label class="form-label">Clasificación de Respuesta *</label>
                        <div class="mt-2">
                            <label class="form-check form-check-inline">
                                <input type="radio" name="estadoResultante" value="sin_observacion" class="form-check-input">
                                <span class="form-check-label">Sin Observación</span>
                            </label>
                            <label class="form-check form-check-inline">
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
                    <div>
                        <label class="form-label">Detalle Error</label>
                        <input type="text" id="approveDetalleError" class="form-control" placeholder="Descripción del error si aplica...">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirmar</button>
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

        loadingIndicator.classList.remove('hidden');
        observationsTable.classList.add('hidden');
        emptyState.classList.add('hidden');

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
                    observationsTable.classList.remove('hidden');
                } else {
                    emptyState.classList.remove('hidden');
                }
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error al cargar observaciones:', error);
            showError('Error al cargar observaciones: ' + error.message);
        } finally {
            loadingIndicator.classList.add('hidden');
        }
    }

    function renderObservations(observations) {
        const tbody = document.getElementById('observationsBody');
        tbody.innerHTML = '';

        observations.forEach(obs => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td>
                <input type="checkbox" class="form-checkbox obs-checkbox" value="${obs.id}">
            </td>
            <td>#${obs.id}</td>
            <td>${formatDate(obs.fecha_registro)}</td>
            <td>
                <div class="font-medium">${escapeHtml(obs.nombre_corto)}</div>
                <div class="text-xs text-slate-500">${escapeHtml(obs.comuna)}</div>
            </td>
            <td>${escapeHtml(obs.mes)}</td>
            <td><span class="text-xs">${escapeHtml(obs.tipo_error)}</span></td>
            <td>${getEstadoBadge(obs.estado_actual)}</td>
            <td class="text-sm">${escapeHtml(obs.nombre_registro)}</td>
            <td class="text-right">
                <div class="flex justify-end gap-2">
                    <button class="btn-icon" onclick="viewDetail(${obs.id})" title="Ver Detalle">
                        <i class="ti ti-eye"></i>
                    </button>
                    ${obs.estado_actual === '<?php echo ESTADO_PENDIENTE; ?>' ? `
                        <button class="btn-icon text-green-600" onclick="approveSingle(${obs.id})" title="Aprobar">
                            <i class="ti ti-check"></i>
                        </button>
                        <button class="btn-icon text-amber-600" onclick="cancelSingle(${obs.id})" title="Cancelar">
                            <i class="ti ti-x"></i>
                        </button>
                        <button class="btn-icon text-rose-600" onclick="deleteSingle(${obs.id})" title="Eliminar">
                            <i class="ti ti-trash"></i>
                        </button>
                    ` : ''}
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
            countDisplay.classList.remove('hidden');
            countDisplay.querySelector('.font-medium').textContent = selectedIds.length;
        } else {
            countDisplay.classList.add('hidden');
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
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-sm text-slate-600">Establecimiento</p>
                <p class="font-bold">${escapeHtml(obs.establecimiento)}</p>
                ${obs.codigo_establecimiento ? `<p class="text-xs text-slate-400">Código: ${escapeHtml(obs.codigo_establecimiento)}</p>` : ''}
            </div>
            <div>
                <p class="text-sm text-slate-600">Estado</p>
                <p>${getEstadoBadge(obs.estado_actual)}</p>
            </div>
            <div>
                <p class="text-sm text-slate-600">Año/Mes</p>
                <p>${obs.anio} - ${escapeHtml(obs.mes)}</p>
            </div>
            <div>
                <p class="text-sm text-slate-600">Registrador</p>
                <p>${escapeHtml(obs.nombre_registro)}</p>
            </div>
            <div class="col-span-2">
                <p class="text-sm text-slate-600">Tipo de Error</p>
                <p>${escapeHtml(obs.tipo_error)}</p>
            </div>
            <div>
                <p class="text-sm text-slate-600">Plazo Entrega</p>
                <p>${obs.plazo_entrega ? escapeHtml(obs.plazo_entrega.replace('_', ' ')) : '-'}</p>
            </div>
            <div>
                <p class="text-sm text-slate-600">Usa Validador</p>
                <p>${obs.usa_validador ? escapeHtml(obs.usa_validador) : '-'}</p>
            </div>
            <div>
                <p class="text-sm text-slate-600">Serie REM</p>
                <p>${obs.codigo_serie ? escapeHtml(obs.codigo_serie) : '-'}</p>
            </div>
            <div>
                <p class="text-sm text-slate-600">Hoja REM</p>
                <p>${obs.codigo_hoja ? escapeHtml(obs.codigo_hoja) : '-'}</p>
            </div>
            <div class="col-span-2">
                <p class="text-sm text-slate-600">Detalle de Observación</p>
                <p class="whitespace-pre-wrap">${escapeHtml(obs.detalle_observacion)}</p>
            </div>
            ${obs.respuesta_establecimiento ? `
            <div class="col-span-2">
                <p class="text-sm text-slate-600">Respuesta del Establecimiento</p>
                <p class="whitespace-pre-wrap">${escapeHtml(obs.respuesta_establecimiento)}</p>
            </div>
            ` : ''}
            ${obs.clasificacion ? `
            <div class="col-span-2 p-3 bg-sky-50 rounded border border-sky-200">
                <p class="text-sm text-sky-700 font-semibold">Clasificación de Respuesta</p>
                <p class="text-sm text-sky-900">${escapeHtml(obs.clasificacion)}</p>
            </div>
            ` : ''}
            ${obs.detalle_error ? `
            <div class="col-span-2 p-3 bg-sky-50 rounded border border-sky-200">
                <p class="text-sm text-sky-700 font-semibold">Detalle Error</p>
                <p class="text-sm text-sky-900">${escapeHtml(obs.detalle_error)}</p>
            </div>
            ` : ''}
            ${obs.fecha_actualizacion ? `
            <div class="col-span-2 text-xs text-slate-400 text-right border-t pt-2 mt-2">
                Última modificación: ${formatDate(obs.fecha_actualizacion)}
            </div>
            ` : ''}
        </div>

        <h4 class="font-bold mb-3">Historial de Cambios</h4>
        <div class="space-y-2">
            ${historial.map(h => `
                <div class="p-3 bg-slate-50 rounded border border-slate-200">
                    <div class="flex justify-between text-sm">
                        <span class="font-medium">${escapeHtml(h.usuario_nombre)}</span>
                        <span class="text-slate-500">${formatDate(h.fecha_cambio)}</span>
                    </div>
                    <p class="text-sm mt-1">
                        ${h.estado_anterior ? escapeHtml(h.estado_anterior) : '<em>inicial</em>'} 
                        → <strong>${escapeHtml(h.estado_nuevo)}</strong>
                    </p>
                    ${h.comentario ? `<p class="text-sm text-slate-600 mt-1">${escapeHtml(h.comentario)}</p>` : ''}
                </div>
            `).join('')}
        </div>
    `;

        new bootstrap.Modal('#detailModal').show();
    }

    function closeDetailModal() {
        bootstrap.Modal.getInstance('#detailModal')?.hide();
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

        new bootstrap.Modal('#confirmModal').show();

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
            showError('Error: ' + error.message);
        }
    }

    function closeConfirmModal() {
        bootstrap.Modal.getInstance('#confirmModal')?.hide();
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
    .hidden { display: none !important; }

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