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
                    <div class="page-header">
                        <div>
                            <h1 class="page-title"><i class="ti ti-building me-2 text-primary"></i>Gestión de Establecimientos</h1>
                            <p class="page-subtitle">Agregar, editar y activar/desactivar establecimientos de salud</p>
                        </div>
                        <div class="page-actions">
                            <button onclick="openCreateModal()" class="btn btn-primary">
                                <i class="ti ti-plus me-1"></i>Nuevo Establecimiento
                            </button>
                        </div>
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
                            <table class="table table-vcenter card-table table-hover">
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
                                                <label class="form-check form-switch mb-0">
                                                    <input type="checkbox" class="form-check-input" <?php echo $est['activo'] ? 'checked' : ''; ?>
                                                        onchange="toggleEstablecimiento(<?php echo $est['id']; ?>, this.checked ? 1 : 0, this)">
                                                    <span class="form-check-label"><?php echo $est['activo'] ? 'Activo' : 'Inactivo'; ?></span>
                                                </label>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-list justify-content-center">
                                                <button onclick='openEditModal(<?php echo json_encode($est); ?>)'
                                                        class="btn btn-ghost-secondary btn-icon" title="Editar"
                                                        data-bs-toggle="tooltip" aria-label="Editar">
                                                        <i class="ti ti-edit"></i>
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
                <h5 class="modal-title" id="estModalTitle"><i class="ti ti-building-plus me-2 text-primary"></i>Nuevo Establecimiento</h5>
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
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="estSubmitBtn">
                        <i class="ti ti-device-floppy me-1"></i>Guardar
                    </button>
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
        const submitBtn = document.getElementById('estSubmitBtn');
        if (submitBtn) submitBtn.disabled = false;
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
        const submitBtn = document.getElementById('estSubmitBtn');
        if (submitBtn && submitBtn.disabled) return;

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
            if (submitBtn) submitBtn.disabled = true;

            const response = await fetch('api/locations.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                body: JSON.stringify(body)
            });
            const result = await parseJsonResponse(response);
            if (result.success) {
                showSuccess(result.message);
                estModal.hide();
                setTimeout(() => location.reload(), 800);
            } else {
                showError(result.message);
            }
        } catch (error) {
            showError(error.message || 'Error de conexión');
        } finally {
            hideLoading();
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    async function toggleEstablecimiento(id, nuevoEstado, control = null) {
        try {
            if (control) control.disabled = true;
            const response = await fetch('api/locations.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                body: JSON.stringify({ action: 'toggle', id: id, activo: nuevoEstado })
            });
            const result = await parseJsonResponse(response);
            if (result.success) {
                showSuccess(result.message);
                setTimeout(() => location.reload(), 800);
            } else {
                showError(result.message);
                if (control) {
                    control.checked = !nuevoEstado;
                }
            }
        } catch (error) {
            showError(error.message || 'Error de conexión');
            if (control) {
                control.checked = !nuevoEstado;
            }
        } finally {
            if (control) control.disabled = false;
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
