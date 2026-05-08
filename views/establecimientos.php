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

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Gestión de Establecimientos</h2>
            <p class="text-slate-600">Agregar, editar y activar/desactivar establecimientos de salud</p>
        </div>
        <button onclick="openCreateModal()" class="btn btn-primary">
            + Nuevo Establecimiento
        </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4">
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-emerald-600" id="statActivos">—</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Activos</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-slate-400" id="statInactivos">—</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Inactivos</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-slate-800" id="statTotal">—</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Total</div>
        </div>
    </div>

    <!-- Filtro por comuna -->
    <div class="card p-4">
        <div class="flex items-center gap-4">
            <label class="text-sm font-semibold text-slate-700 whitespace-nowrap">Filtrar por comuna:</label>
            <select id="filterComuna" class="form-select w-64" onchange="filterTable()">
                <option value="">Todas las comunas</option>
                <?php foreach ($comunas as $c): ?>
                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
            <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                <input type="checkbox" id="showInactivos" onchange="filterTable()"> Mostrar inactivos
            </label>
        </div>
    </div>

    <!-- Tabla de establecimientos -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-slate-500">
                        <th class="p-3 font-medium">Código</th>
                        <th class="p-3 font-medium">Nombre</th>
                        <th class="p-3 font-medium">Nombre Corto</th>
                        <th class="p-3 font-medium">Comuna</th>
                        <th class="p-3 font-medium text-center">Estado</th>
                        <th class="p-3 font-medium text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="establecimientosTable">
                    <?php foreach ($establecimientos as $est): ?>
                        <tr class="border-t border-slate-100 hover:bg-slate-50 establecimiento-row" 
                            data-comuna="<?php echo $est['comuna_id']; ?>" 
                            data-activo="<?php echo $est['activo'] ? 1 : 0; ?>">
                            <td class="p-3 font-mono text-slate-600"><?php echo htmlspecialchars($est['codigo_establecimiento']); ?></td>
                            <td class="p-3 text-slate-800 font-medium <?php echo !$est['activo'] ? 'line-through text-slate-400' : ''; ?>">
                                <?php echo htmlspecialchars($est['nombre']); ?>
                            </td>
                            <td class="p-3 text-slate-600"><?php echo htmlspecialchars($est['nombre_corto']); ?></td>
                            <td class="p-3 text-slate-600"><?php echo htmlspecialchars($est['comuna_nombre']); ?></td>
                            <td class="p-3 text-center">
                                <?php if ($est['activo']): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick='openEditModal(<?php echo json_encode($est); ?>)' 
                                            class="text-sky-600 hover:text-sky-800 text-xs font-medium" title="Editar">
                                        ✏️ Editar
                                    </button>
                                    <button onclick="toggleEstablecimiento(<?php echo $est['id']; ?>, <?php echo $est['activo'] ? 0 : 1; ?>)" 
                                            class="text-xs font-medium <?php echo $est['activo'] ? 'text-amber-600 hover:text-amber-800' : 'text-emerald-600 hover:text-emerald-800'; ?>" 
                                            title="<?php echo $est['activo'] ? 'Desactivar' : 'Activar'; ?>">
                                        <?php echo $est['activo'] ? '⏸️ Desactivar' : '▶️ Activar'; ?>
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

<!-- Modal Crear/Editar -->
<div id="estModal" class="modal-overlay hidden">
    <div class="modal-content" style="max-width: 520px;">
        <div class="modal-header">
            <h3 class="text-lg font-bold text-slate-800" id="estModalTitle">Nuevo Establecimiento</h3>
            <button onclick="closeEstModal()" class="btn-secondary px-3 py-2" type="button">✕</button>
        </div>
        <form id="estForm" onsubmit="saveEstablecimiento(event)">
            <input type="hidden" id="estId" value="">
            <div class="space-y-4 p-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Código de Establecimiento *</label>
                    <input type="number" id="estCodigo" class="form-input w-full" required placeholder="Ej: 101">
                    <p class="text-xs text-slate-400 mt-1">Código numérico único del establecimiento</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nombre Completo *</label>
                    <input type="text" id="estNombre" class="form-input w-full" required placeholder="Ej: Hospital Base San José de Osorno">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nombre Corto *</label>
                    <input type="text" id="estNombreCorto" class="form-input w-full" required placeholder="Ej: HBSJO" maxlength="50">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Comuna *</label>
                    <select id="estComuna" class="form-select w-full" required>
                        <option value="">Seleccionar comuna...</option>
                        <?php foreach ($comunas as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid var(--color-slate-100); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeEstModal()" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="estSubmitBtn">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('estModalTitle').textContent = 'Nuevo Establecimiento';
        document.getElementById('estSubmitBtn').textContent = 'Crear Establecimiento';
        document.getElementById('estForm').reset();
        document.getElementById('estId').value = '';
        document.getElementById('estCodigo').disabled = false;
        openModal('estModal');
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
        openModal('estModal');
    }

    function closeEstModal() {
        closeModal('estModal');
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
                closeEstModal();
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
