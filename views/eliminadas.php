<?php
/**
 * Vista de Observaciones Eliminadas
 * Papelera de reciclaje - Solo accesible para supervisores
 */

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    echo '<div class="empty"><div class="empty-header text-danger">403</div><p class="empty-title">Acceso Denegado</p><p class="empty-subtitle text-secondary">Solo los supervisores pueden acceder a esta sección.</p></div>';
    return;
}

require_once 'models/User.php';
require_once 'models/Location.php';

$userModel = new User();
$locationModel = new Location();
$currentYear = $_SESSION['anio_trabajo'] ?? date('Y');

$registradores = $userModel->getByRole(ROL_REGISTRADOR);
$comunas = $locationModel->getComunas();
?>

<div class="row row-cards">

                <!-- Header -->
                <div class="col-12">
                    <div class="page-header d-flex align-items-center justify-content-between">
                        <div>
                            <div class="page-pretitle">Papelera de reciclaje — Restaurar o eliminar permanentemente</div>
                            <h2 class="page-title">Observaciones Eliminadas</h2>
                        </div>
                        <div class="btn-list">
                            <span id="selectedCount" class="text-secondary d-none">
                                <span class="fw-medium text-primary">0</span> seleccionadas
                            </span>
                            <button id="btnRestoreSelected" class="btn btn-primary" disabled>
                                Restaurar
                            </button>
                            <button id="btnDeletePermanentSelected" class="btn btn-danger" disabled>
                                Eliminar Permanentemente
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Estadísticas rápidas -->
                <div class="col-12">
                    <div id="statsContainer" class="row g-3"></div>
                </div>

                <!-- Filtros -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title mb-3">Filtros</h3>
                            <div class="row g-3">
                                <div class="col-lg">
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
                                <div class="col-lg">
                                    <label class="form-label">Comuna</label>
                                    <select id="filterComuna" class="form-select">
                                        <option value="">Todas</option>
                                        <?php foreach ($comunas as $comuna): ?>
                                            <option value="<?php echo htmlspecialchars($comuna['nombre']); ?>"><?php echo htmlspecialchars($comuna['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-lg">
                                    <label class="form-label">Establecimiento</label>
                                    <select id="filterEstablecimiento" class="form-select" disabled>
                                        <option value="">Todos</option>
                                    </select>
                                </div>
                                <div class="col-lg">
                                    <label class="form-label">Registrador</label>
                                    <select id="filterRegistrador" class="form-select">
                                        <option value="">Todos</option>
                                        <?php foreach ($registradores as $reg): ?>
                                            <option value="<?php echo $reg['id']; ?>"><?php echo htmlspecialchars($reg['nombre_completo']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-lg">
                                    <label class="form-label">Búsqueda</label>
                                    <input type="text" id="filterBusqueda" class="form-control" placeholder="Buscar en detalles...">
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
                </div>

                <!-- Tabla -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Observaciones Eliminadas <span id="obsCount" class="text-secondary"></span></h3>
                            <div class="card-actions">
                                <label class="form-check">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                    <span class="form-check-label">Seleccionar Todas</span>
                                </label>
                            </div>
                        </div>
                        <div id="loadingIndicator" class="card-body text-center py-8 text-secondary">
                            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div>
                            <p class="mt-2">Cargando observaciones eliminadas...</p>
                        </div>
                        <div id="observationsTable" class="d-none table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th class="w-1"></th>
                                        <th>ID Original</th>
                                        <th>Fecha Eliminación</th>
                                        <th>Establecimiento</th>
                                        <th>Mes</th>
                                        <th>Tipo Error</th>
                                        <th>Estado Original</th>
                                        <th>Registrador</th>
                                        <th>Motivo</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="observationsBody"></tbody>
                            </table>
                        </div>
                        <div id="emptyState" class="d-none card-body text-center py-8 text-secondary">
                            <p>No hay observaciones eliminadas.</p>
                        </div>
                    </div>
                </div>

<!-- Modal de Confirmación (Bootstrap) -->
<div id="confirmModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmTitle">Confirmar Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage"></p>
                <div class="mb-3">
                    <label class="form-label">Comentario (opcional)</label>
                    <textarea id="confirmComment" class="form-control" rows="3"></textarea>
                </div>
                <div id="confirmCheckboxContainer" class="d-none">
                    <label class="form-check">
                        <input type="checkbox" id="confirmIrreversible" class="form-check-input">
                        <span class="form-check-label text-danger">Entiendo que esta acción no se puede deshacer</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmActionBtn">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentObservations = [];
    let selectedIds = [];
    const confirmModalEl = document.getElementById('confirmModal');
    const confirmModal = new bootstrap.Modal(confirmModalEl);

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

        loadingIndicator.classList.add('d-none');
        observationsTable.classList.add('d-none');
        emptyState.classList.add('d-none');

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
                    observationsTable.classList.remove('d-none');
                } else {
                    emptyState.classList.remove('d-none');
                }
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Error al cargar observaciones eliminadas:', error);
            showError('Error al cargar: ' + error.message);
        } finally {
            loadingIndicator.classList.add('d-none');
        }
    }

    async function loadStats() {
        try {
            const response = await fetch(`api/deleted.php?action=stats&anio=<?php echo $currentYear; ?>`);
            const data = await response.json();
            if (data.success) renderStats(data.data);
        } catch (error) {
            console.error('Error al cargar estadísticas:', error);
        }
    }

    function renderStats(stats) {
        const container = document.getElementById('statsContainer');
        const topEliminador = stats.por_eliminador && stats.por_eliminador.length > 0
            ? stats.por_eliminador[0].nombre_completo : 'N/A';
        const topEliminadorCount = stats.por_eliminador && stats.por_eliminador.length > 0
            ? stats.por_eliminador[0].total : 0;
        const estadoBreakdown = stats.por_estado && stats.por_estado.length > 0
            ? stats.por_estado.map(e => `${e.estado_actual}: ${e.total}`).join(', ') : 'Sin datos';

        container.innerHTML = `
            <div class="col-md-4">
                <div class="card card-sm" style="background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%);">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="p-2 rounded bg-danger text-white fs-2"><i class="ti ti-trash"></i></div>
                        <div>
                            <div class="h1 mb-0 text-danger">${stats.total}</div>
                            <div class="text-danger small fw-semibold">Total Eliminadas</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-sm" style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="p-2 rounded bg-primary text-white fs-2"><i class="ti ti-chart-bar"></i></div>
                        <div>
                            <div class="fw-bold text-primary">Por Estado</div>
                            <div class="text-primary small">${estadoBreakdown}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-sm" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="p-2 rounded bg-warning text-white fs-2"><i class="ti ti-user"></i></div>
                        <div>
                            <div class="fw-bold text-warning">Mayor Eliminador</div>
                            <div class="text-warning small">${escapeHtml(topEliminador)} (${topEliminadorCount})</div>
                        </div>
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
                <td><input type="checkbox" class="form-check-input obs-checkbox" value="${obs.id}"></td>
                <td>#${obs.observacion_id}</td>
                <td>${formatDate(obs.fecha_eliminacion)}</td>
                <td>
                    <div class="fw-semibold">${escapeHtml(obs.establecimiento_nombre_corto)}</div>
                    <div class="text-secondary text-sm">${escapeHtml(obs.comuna)}</div>
                </td>
                <td>${escapeHtml(obs.mes)}</td>
                <td>${escapeHtml(obs.tipo_error)}</td>
                <td>${getEstadoBadge(obs.estado_actual)}</td>
                <td class="text-secondary">${escapeHtml(obs.nombre_registro)}</td>
                <td class="text-secondary text-truncate" style="max-width: 150px;" title="${escapeHtml(obs.motivo_eliminacion)}">
                    ${escapeHtml(obs.motivo_eliminacion) || '-'}
                </td>
                <td class="text-end">
                    <div class="btn-list justify-content-end">
                        <button class="btn btn-ghost-primary btn-icon" onclick="restoreSingle(${obs.id})" title="Restaurar" data-bs-toggle="tooltip">
                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-history"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 8l0 4l2 2" /><path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" /></svg>
                        </button>
                        <button class="btn btn-ghost-danger btn-icon" onclick="deletePermanentSingle(${obs.id})" title="Eliminar permanentemente" data-bs-toggle="tooltip">
                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
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
        countDisplay.classList.toggle('d-none', !hasSelection);
        if (hasSelection) countDisplay.querySelector('span').textContent = selectedIds.length;
    }

    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll').checked;
        document.querySelectorAll('.obs-checkbox').forEach(cb => cb.checked = selectAll);
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

        const isPermanentDelete = action.includes('permanent_delete');
        const checkboxContainer = document.getElementById('confirmCheckboxContainer');
        const confirmCheckbox = document.getElementById('confirmIrreversible');
        const confirmBtn = document.getElementById('confirmActionBtn');

        if (isPermanentDelete) {
            checkboxContainer.classList.remove('d-none');
            confirmCheckbox.checked = false;
            confirmBtn.disabled = true;
        } else {
            checkboxContainer.classList.add('d-none');
            confirmBtn.disabled = false;
        }

        confirmCheckbox.onchange = () => { confirmBtn.disabled = !confirmCheckbox.checked; };

        confirmBtn.onclick = async () => {
            if (isPermanentDelete && !confirmCheckbox.checked) {
                showError('Debe confirmar que entiende que esta acción es irreversible.');
                return;
            }
            confirmModal.hide();
            const comment = document.getElementById('confirmComment').value;
            await executeAction(action, ids, comment);
        };

        confirmModal.show();
    }

    async function executeAction(action, ids, comment) {
        try {
            showLoading();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const payload = action.includes('multiple')
                ? { action, deleted_ids: ids, comment }
                : { action, deleted_id: ids[0], comment };

            const response = await fetch('api/deleted.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
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
                const response = await fetch(`api/locations.php?action=establecimientos&comuna_nombre=${encodeURIComponent(comunaNombre)}`);
                const data = await response.json();
                if (data.success) {
                    data.data.forEach(est => {
                        const option = document.createElement('option');
                        option.value = est.id;
                        option.textContent = est.nombre_corto || est.nombre;
                        select.appendChild(option);
                    });
                }
            } catch (error) { console.error('Error al cargar establecimientos:', error); }
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
            'pendiente': '<span class="badge bg-yellow text-yellow-fg">Pendiente</span>',
            'aprobado': '<span class="badge bg-green text-green-fg">Aprobado</span>',
            'rechazado': '<span class="badge bg-red text-red-fg">Rechazado</span>',
            'error': '<span class="badge bg-red text-red-fg">Error</span>',
            'justificado': '<span class="badge bg-blue text-blue-fg">Justificado</span>'
        };
        return badges[estado] || `<span class="badge">${estado}</span>`;
    }
</script>
