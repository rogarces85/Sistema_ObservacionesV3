<?php
/**
 * Vista de Gestión de Establecimientos
 * Solo accesible para supervisores
 */

require_once __DIR__ . '/../models/Location.php';
$locationModel = new Location();
$comunas = $locationModel->getAllComunas();
$establecimientos = $locationModel->getAllEstablecimientosConInactivos();
?>

<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">

                <div class="col-12">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="page-title">Gestión de Establecimientos</h2>
                            <div class="text-secondary">Agregar, editar y activar/desactivar establecimientos de salud</div>
                        </div>
                        <button onclick="openCreateModal()" class="btn btn-primary">
                            Nuevo Establecimiento
                        </button>
                    </div>
                </div>

                <!-- Stats -->
                <div class="col-12">
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="card card-sm">
                                <div class="card-body text-center">
                                    <div class="h1 text-green" id="statActivos">—</div>
                                    <div class="text-secondary text-uppercase small">Activos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card card-sm">
                                <div class="card-body text-center">
                                    <div class="h1 text-secondary" id="statInactivos">—</div>
                                    <div class="text-secondary text-uppercase small">Inactivos</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card card-sm">
                                <div class="card-body text-center">
                                    <div class="h1" id="statTotal">—</div>
                                    <div class="text-secondary text-uppercase small">Total</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtro por comuna -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-auto">
                                    <label class="form-label">Filtrar por comuna:</label>
                                    <select id="filterComuna" class="form-select" onchange="filterTable()">
                                        <option value="">Todas las comunas</option>
                                        <?php foreach ($comunas as $c): ?>
                                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-auto">
                                    <label class="form-check">
                                        <input type="checkbox" class="form-check-input" id="showInactivos" onchange="filterTable()">
                                        <span class="form-check-label">Mostrar inactivos</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de establecimientos -->
                <div class="col-12">
                    <div class="card">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Nombre Corto</th>
                                        <th>Comuna</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="establecimientosTable">
                                    <?php foreach ($establecimientos as $est): ?>
                                        <tr class="establecimiento-row" 
                                            data-comuna="<?php echo $est['comuna_id']; ?>" 
                                            data-activo="<?php echo $est['activo'] ? 1 : 0; ?>">
                                            <td class="font-mono text-secondary"><?php echo htmlspecialchars($est['codigo_establecimiento']); ?></td>
                                            <td class="fw-semibold <?php echo !$est['activo'] ? 'text-decoration-line-through text-secondary' : ''; ?>">
                                                <?php echo htmlspecialchars($est['nombre']); ?>
                                            </td>
                                            <td class="text-secondary"><?php echo htmlspecialchars($est['nombre_corto']); ?></td>
                                            <td class="text-secondary"><?php echo htmlspecialchars($est['comuna_nombre']); ?></td>
                                            <td class="text-center">
                                                <?php if ($est['activo']): ?>
                                                    <span class="badge bg-green text-green-fg">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary text-secondary-fg">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-list justify-content-center">
                                                    <button onclick='openEditModal(<?php echo json_encode($est); ?>)' 
                                                            class="btn btn-ghost-secondary btn-icon" title="Editar"
                                                            data-bs-toggle="tooltip">
                                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-edit"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                                                    </button>
                                                    <button onclick="toggleEstablecimiento(<?php echo $est['id']; ?>, <?php echo $est['activo'] ? 0 : 1; ?>)" 
                                                            class="btn btn-ghost-secondary btn-icon"
                                                            title="<?php echo $est['activo'] ? 'Desactivar' : 'Activar'; ?>"
                                                            data-bs-toggle="tooltip">
                                                        <?php if ($est['activo']): ?>
                                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-player-pause"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M6 5m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z" /><path d="M14 5m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v12a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z" /></svg>
                                                        <?php else: ?>
                                                            <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-player-play"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 4v16l13 -8z" /></svg>
                                                        <?php endif; ?>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear/Editar (Bootstrap) -->
<div id="estModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="estModalTitle">Nuevo Establecimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="estForm" onsubmit="saveEstablecimiento(event)">
                <div class="modal-body">
                    <input type="hidden" id="estId" value="">
                    <div class="mb-3">
                        <label class="form-label required">Código de Establecimiento</label>
                        <input type="number" id="estCodigo" class="form-control" required placeholder="Ej: 101">
                        <div class="form-hint">Código numérico único del establecimiento</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Nombre Completo</label>
                        <input type="text" id="estNombre" class="form-control" required placeholder="Ej: Hospital Base San José de Osorno">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Nombre Corto</label>
                        <input type="text" id="estNombreCorto" class="form-control" required placeholder="Ej: HBSJO" maxlength="50">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Comuna</label>
                        <select id="estComuna" class="form-select" required>
                            <option value="">Seleccionar comuna...</option>
                            <?php foreach ($comunas as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="estSubmitBtn">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const estModal = new bootstrap.Modal(document.getElementById('estModal'));

    function openCreateModal() {
        document.getElementById('estModalTitle').textContent = 'Nuevo Establecimiento';
        document.getElementById('estSubmitBtn').textContent = 'Crear Establecimiento';
        document.getElementById('estForm').reset();
        document.getElementById('estId').value = '';
        document.getElementById('estCodigo').disabled = false;
        estModal.show();
    }

    function openEditModal(est) {
        document.getElementById('estModalTitle').textContent = 'Editar Establecimiento';
        document.getElementById('estSubmitBtn').textContent = 'Guardar Cambios';
        document.getElementById('estId').value = est.id;
        document.getElementById('estCodigo').value = est.codigo_establecimiento;
        document.getElementById('estCodigo').disabled = true;
        document.getElementById('estNombre').value = est.nombre;
        document.getElementById('estNombreCorto').value = est.nombre_corto;
        document.getElementById('estComuna').value = est.comuna_id;
        estModal.show();
    }

    async function saveEstablecimiento(e) {
        e.preventDefault();
        const id = document.getElementById('estId').value;
        const action = id ? 'update' : 'create';
        const body = {
            action: action,
            codigo_establecimiento: parseInt(document.getElementById('estCodigo').value),
            nombre: document.getElementById('estNombre').value.trim(),
            nombre_corto: document.getElementById('estNombreCorto').value.trim(),
            comuna_id: parseInt(document.getElementById('estComuna').value)
        };
        if (id) body.id = parseInt(id);

        try {
            showLoading();
            const response = await fetch('api/locations.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                body: JSON.stringify(body)
            });
            const result = await response.json();
            hideLoading();
            if (result.success) {
                showSuccess(result.message);
                estModal.hide();
                setTimeout(() => location.reload(), 800);
            } else {
                showError(result.message);
            }
        } catch (error) {
            hideLoading();
            showError('Error de conexión');
        }
    }

    async function toggleEstablecimiento(id, nuevoEstado) {
        const accion = nuevoEstado ? 'activar' : 'desactivar';
        if (!confirm(`¿Está seguro de ${accion} este establecimiento?`)) return;

        try {
            showLoading();
            const response = await fetch('api/locations.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                body: JSON.stringify({ action: 'toggle', id: id, activo: nuevoEstado })
            });
            const result = await response.json();
            hideLoading();
            if (result.success) {
                showSuccess(result.message);
                setTimeout(() => location.reload(), 800);
            } else {
                showError(result.message);
            }
        } catch (error) {
            hideLoading();
            showError('Error de conexión');
        }
    }

    function filterTable() {
        const comunaFilter = document.getElementById('filterComuna').value;
        const showInactivos = document.getElementById('showInactivos').checked;
        const rows = document.querySelectorAll('.establecimiento-row');
        let activos = 0, inactivos = 0;

        rows.forEach(row => {
            const comunaMatch = !comunaFilter || row.dataset.comuna === comunaFilter;
            const activoMatch = showInactivos || row.dataset.activo === '1';
            const visible = comunaMatch && activoMatch;
            row.style.display = visible ? '' : 'none';

            if (row.dataset.activo === '1') activos++;
            else inactivos++;
        });

        document.getElementById('statActivos').textContent = activos;
        document.getElementById('statInactivos').textContent = inactivos;
        document.getElementById('statTotal').textContent = activos + inactivos;
    }

    // Init stats
    document.addEventListener('DOMContentLoaded', filterTable);
</script>
