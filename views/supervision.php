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
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Panel de Supervisión</h2>
            <p class="text-slate-600">Revise y gestione las observaciones registradas</p>
        </div>
        <div class="flex gap-3 items-center">
            <span id="selectedCount" class="text-sm text-slate-500 hidden">
                <span class="font-medium text-primary-600">0</span> seleccionadas
            </span>
            <button id="btnApproveSelected" class="btn btn-primary" disabled>
                ✓ Aprobar Seleccionadas
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">🔍 Filtros</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
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

            <div>
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

            <div>
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

            <div>
                <label class="form-label">Establecimiento</label>
                <select id="filterEstablecimiento" class="form-select" disabled>
                    <option value="">Todos</option>
                </select>
            </div>

            <div>
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

            <div>
                <label class="form-label">Búsqueda</label>
                <input type="text" id="filterBusqueda" class="form-input" placeholder="Buscar en detalles..." />
            </div>

            <div class="lg:col-span-2 flex items-end gap-3">
                <button id="btnApplyFilters" class="btn btn-primary">
                    Aplicar Filtros
                </button>
                <button id="btnClearFilters" class="btn btn-secondary">
                    Limpiar
                </button>
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

        <div id="observationsTable" class="hidden overflow-x-auto">
            <table class="table">
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
            <p>📋 No se encontraron observaciones con los filtros aplicados.</p>
        </div>
    </div>
</div>

<!-- Modal de Detalle -->
<div id="detailModal" class="modal hidden">
    <div class="modal-content max-w-4xl">
        <div class="modal-header">
            <h3 class="text-xl font-bold">Detalle de Observación</h3>
            <button class="modal-close" onclick="closeDetailModal()">&times;</button>
        </div>
        <div class="modal-body" id="detailContent">
            <!-- Se llenará dinámicamente -->
        </div>
    </div>
</div>

<!-- Modal de Confirmación -->
<div id="confirmModal" class="modal hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="text-xl font-bold" id="confirmTitle">Confirmar Acción</h3>
            <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p id="confirmMessage"></p>
            <div class="mt-4">
                <label class="form-label">Comentario (opcional)</label>
                <textarea id="confirmComment" class="form-textarea" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeConfirmModal()">Cancelar</button>
            <button class="btn btn-primary" id="confirmActionBtn">Confirmar</button>
        </div>
    </div>
</div>

<script>
    let currentObservations = [];
    let selectedIds = [];

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
                        👁️
                    </button>
                    ${obs.estado_actual === '<?php echo ESTADO_PENDIENTE; ?>' ? `
                        <button class="btn-icon text-green-600" onclick="approveSingle(${obs.id})" title="Aprobar">
                            ✓
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
        const countDisplay = document.getElementById('selectedCount');

        btnApprove.disabled = selectedIds.length === 0;

        // Mostrar/ocultar contador de seleccionadas
        if (selectedIds.length > 0) {
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
            <div class="col-span-2">
                <p class="text-sm text-slate-600">Detalle de Observación</p>
                <p class="whitespace-pre-wrap">${escapeHtml(obs.detalle_observacion)}</p>
            </div>
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

        document.getElementById('detailModal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('detailModal').classList.add('hidden');
    }

    function approveSingle(id) {
        performAction('approve', [id], '¿Aprobar esta observación?');
    }

    function approveSelected() {
        performAction('approve', selectedIds, `¿Aprobar ${selectedIds.length} observaciones seleccionadas?`);
    }

    function performAction(action, ids, message) {
        document.getElementById('confirmTitle').textContent = ids.length > 1 ? 'Aprobar Observaciones' : 'Aprobar Observación';
        document.getElementById('confirmMessage').textContent = message;
        document.getElementById('confirmComment').value = '';
        document.getElementById('confirmModal').classList.remove('hidden');

        document.getElementById('confirmActionBtn').onclick = async () => {
            const comment = document.getElementById('confirmComment').value;
            await executeAction(action, ids, comment);
            closeConfirmModal();
        };
    }

    async function executeAction(action, ids, comment) {
        try {
            const response = await fetch(`api/supervision.php?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: ids.length === 1 ? ids[0] : ids,
                    comment: comment || undefined,
                    reason: comment || undefined
                })
            });

            const data = await response.json();

            if (data.success) {
                const actionTexts = {
                    'approve': 'Observación(es) aprobada(s) correctamente'
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
        document.getElementById('confirmModal').classList.add('hidden');
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
            '<?php echo ESTADO_PENDIENTE; ?>': '<span class="badge badge-warning">Pendiente</span>',
            '<?php echo ESTADO_APROBADO; ?>': '<span class="badge badge-success">Aprobado</span>',
            '<?php echo ESTADO_RECHAZADO; ?>': '<span class="badge badge-danger">Rechazado</span>',
            '<?php echo ESTADO_ERROR; ?>': '<span class="badge badge-danger">Error</span>',
            '<?php echo ESTADO_JUSTIFICADO; ?>': '<span class="badge badge-info">Justificado</span>'
        };
        return badges[estado] || `<span class="badge">${estado}</span>`;
    }
</script>

<style>
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .modal.hidden {
        display: none;
    }

    .modal-content {
        background: white;
        border-radius: 0.5rem;
        max-width: 32rem;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        padding: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .modal-close {
        font-size: 1.5rem;
        font-weight: bold;
        color: #64748b;
        background: none;
        border: none;
        cursor: pointer;
    }

    .modal-close:hover {
        color: #334155;
    }

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