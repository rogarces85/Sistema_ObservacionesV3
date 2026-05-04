<?php
/**
 * Vista de Observaciones Eliminadas
 * Papelera de reciclaje - Solo accesible para supervisores
 */

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    echo '<div class="p-6 text-center"><h2 class="text-xl font-bold text-rose-600">Acceso Denegado</h2><p>Solo los supervisores pueden acceder a esta sección.</p></div>';
    return;
}

require_once 'models/User.php';
require_once 'models/Location.php';

$userModel = new User();
$locationModel = new Location();
$currentYear = $_SESSION['year'] ?? date('Y');

$registradores = $userModel->getByRole(ROL_REGISTRADOR);
$comunas = $locationModel->getComunas();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Observaciones Eliminadas</h2>
            <p class="text-slate-600">Papelera de reciclaje - Restaurar o eliminar permanentemente</p>
        </div>
        <div class="flex gap-3 items-center">
            <span id="selectedCount" class="text-sm text-slate-500 hidden">
                <span class="font-medium text-sky-600">0</span> seleccionadas
            </span>
            <button id="btnRestoreSelected" class="btn btn-primary" disabled>
                ♻️ Restaurar
            </button>
            <button id="btnDeletePermanentSelected" class="btn btn-secondary" disabled style="background-color: #ef4444; color: white;">
                🗑 Eliminar Permanentemente
            </button>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div id="statsContainer" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Se llena dinámicamente -->
    </div>

    <!-- Filtros -->
    <div class="card p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">🔍 Filtros</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
                        <option value="<?php echo htmlspecialchars($comuna['nombre']); ?>">
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

            <div class="lg:col-span-3 flex items-end gap-3">
                <button id="btnApplyFilters" class="btn btn-primary">
                    Aplicar Filtros
                </button>
                <button id="btnClearFilters" class="btn btn-secondary">
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla de Observaciones Eliminadas -->
    <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-800">
                Observaciones Eliminadas <span id="obsCount" class="text-slate-500"></span>
            </h3>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="selectAll" class="form-checkbox">
                <label for="selectAll" class="text-sm text-slate-600">Seleccionar Todas</label>
            </div>
        </div>

        <div id="loadingIndicator" class="text-center py-8 text-slate-500">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-sky-500"></div>
            <p class="mt-2">Cargando observaciones eliminadas...</p>
        </div>

        <div id="observationsTable" class="hidden overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-12"></th>
                        <th>ID Original</th>
                        <th>Fecha Eliminación</th>
                        <th>Establecimiento</th>
                        <th>Mes</th>
                        <th>Tipo Error</th>
                        <th>Estado Original</th>
                        <th>Registrador</th>
                        <th>Motivo</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody id="observationsBody">
                    <!-- Se llenará dinámicamente -->
                </tbody>
            </table>
        </div>

        <div id="emptyState" class="hidden text-center py-8 text-slate-500">
            <p>📋 No hay observaciones eliminadas.</p>
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

    document.addEventListener('DOMContentLoaded', function () {
        loadObservations();
        loadStats();
        setupEventListeners();
    });

    function setupEventListeners() {
        document.getElementById('btnApplyFilters').addEventListener('click', loadObservations);
        document.getElementById('btnClearFilters').addEventListener('click', clearFilters);
        document.getElementById('selectAll').addEventListener('click', toggleSelectAll);
        document.getElementById('btnRestoreSelected').addEventListener('click', () => restoreSelected());
        document.getElementById('btnDeletePermanentSelected').addEventListener('click', () => deletePermanentSelected());
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
            mes: document.getElementById('filterMes').value,
            comuna_nombre: document.getElementById('filterComuna').value,
            establecimiento_id: document.getElementById('filterEstablecimiento').value,
            usuario_registro_id: document.getElementById('filterRegistrador').value,
            busqueda: document.getElementById('filterBusqueda').value
        };

        try {
            const response = await fetch('api/deleted.php?action=list&' + new URLSearchParams(filters));
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
            console.error('Error al cargar observaciones eliminadas:', error);
            showError('Error al cargar: ' + error.message);
        } finally {
            loadingIndicator.classList.add('hidden');
        }
    }

    async function loadStats() {
        try {
            const response = await fetch(`api/deleted.php?action=stats&anio=<?php echo $currentYear; ?>`);
            const data = await response.json();

            if (data.success) {
                renderStats(data.data);
            }
        } catch (error) {
            console.error('Error al cargar estadísticas:', error);
        }
    }

    function renderStats(stats) {
        const container = document.getElementById('statsContainer');
        container.innerHTML = `
            <div class="card p-5" style="background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%); border-color: #fecdd3;">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl" style="background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);">
                        <span class="text-2xl">🗑</span>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-rose-700">${stats.total}</div>
                        <div class="text-sm font-semibold text-rose-600">Total Eliminadas</div>
                    </div>
                </div>
            </div>
            <div class="card p-5" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-color: #bae6fd;">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
                        <span class="text-2xl">♻️</span>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-sky-700">Restaurar</div>
                        <div class="text-sm font-semibold text-sky-600">Recuperar observaciones</div>
                    </div>
                </div>
            </div>
            <div class="card p-5" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-color: #fbbf24;">
                <div class="flex items-center gap-4">
                    <div class="p-3 rounded-xl" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <span class="text-2xl">⚠️</span>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-amber-700">Cuidado</div>
                        <div class="text-sm font-semibold text-amber-600">Eliminación permanente</div>
                    </div>
                </div>
            </div>
        `;
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
            <td>#${obs.observacion_id}</td>
            <td>${formatDate(obs.fecha_eliminacion)}</td>
            <td>
                <div class="font-medium">${escapeHtml(obs.establecimiento_nombre_corto)}</div>
                <div class="text-xs text-slate-500">${escapeHtml(obs.comuna)}</div>
            </td>
            <td>${escapeHtml(obs.mes)}</td>
            <td><span class="text-xs">${escapeHtml(obs.tipo_error)}</span></td>
            <td>${getEstadoBadge(obs.estado_actual)}</td>
            <td class="text-sm">${escapeHtml(obs.nombre_registro)}</td>
            <td class="text-xs max-w-xs truncate" title="${escapeHtml(obs.motivo_eliminacion)}">
                ${escapeHtml(obs.motivo_eliminacion) || '-'}
            </td>
            <td class="text-right">
                <div class="flex justify-end gap-2">
                    <button class="btn-icon text-sky-600" onclick="restoreSingle(${obs.id})" title="Restaurar">
                        ♻️
                    </button>
                    <button class="btn-icon text-rose-600" onclick="deletePermanentSingle(${obs.id})" title="Eliminar permanentemente">
                        🗑
                    </button>
                </div>
            </td>
        `;
            tbody.appendChild(tr);
        });

        document.querySelectorAll('.obs-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedIds);
        });
    }

    function updateSelectedIds() {
        selectedIds = Array.from(document.querySelectorAll('.obs-checkbox:checked')).map(cb => parseInt(cb.value));
        const btnRestore = document.getElementById('btnRestoreSelected');
        const btnDelete = document.getElementById('btnDeletePermanentSelected');
        const countDisplay = document.getElementById('selectedCount');

        const hasSelection = selectedIds.length > 0;
        btnRestore.disabled = !hasSelection;
        btnDelete.disabled = !hasSelection;

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

    function restoreSingle(id) {
        performAction('restore', [id], '¿Restaurar esta observación? Volverá a la tabla principal.', 'Restaurar Observación');
    }

    function restoreSelected() {
        performAction('restore_multiple', selectedIds, `¿Restaurar ${selectedIds.length} observaciones seleccionadas?`, 'Restaurar Observaciones');
    }

    function deletePermanentSingle(id) {
        performAction('permanent_delete', [id], '¿Eliminar permanentemente esta observación? Esta acción no se puede deshacer.', 'Eliminar Permanentemente');
    }

    function deletePermanentSelected() {
        performAction('permanent_delete_multiple', selectedIds, `¿Eliminar permanentemente ${selectedIds.length} observaciones? Esta acción no se puede deshacer.`, 'Eliminar Permanentemente');
    }

    function performAction(action, ids, message, title) {
        document.getElementById('confirmTitle').textContent = title;
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
            showLoading();

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const payload = action.includes('multiple') ? 
                { action, deleted_ids: ids, comment } : 
                { action, deleted_id: ids[0], comment };

            const response = await fetch('api/deleted.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();
            hideLoading();

            if (data.success) {
                showSuccess(data.message);
                loadObservations();
                loadStats();
                selectedIds = [];
                document.getElementById('selectAll').checked = false;
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            hideLoading();
            showError('Error: ' + error.message);
        }
    }

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.add('hidden');
    }

    function clearFilters() {
        document.getElementById('filterMes').value = '';
        document.getElementById('filterComuna').value = '';
        document.getElementById('filterEstablecimiento').value = '';
        document.getElementById('filterRegistrador').value = '';
        document.getElementById('filterBusqueda').value = '';
        loadObservations();
    }

    async function loadEstablecimientos() {
        const comunaNombre = document.getElementById('filterComuna').value;
        const select = document.getElementById('filterEstablecimiento');

        select.innerHTML = '<option value="">Todos</option>';
        select.disabled = !comunaNombre;

        if (comunaNombre) {
            try {
                const response = await fetch(`api/locations.php?action=get_establecimientos&comuna_nombre=${encodeURIComponent(comunaNombre)}`);
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

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('es-CL', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function getEstadoBadge(estado) {
        const badges = {
            'pendiente': '<span class="badge badge-warning">Pendiente</span>',
            'aprobado': '<span class="badge badge-success">Aprobado</span>',
            'rechazado': '<span class="badge badge-danger">Rechazado</span>',
            'error': '<span class="badge badge-danger">Error</span>',
            'justificado': '<span class="badge badge-info">Justificado</span>'
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
