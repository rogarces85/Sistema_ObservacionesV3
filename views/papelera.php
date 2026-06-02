<?php
/**
 * Vista de Papelera de Eliminadas
 * Papelera de reciclaje - Restaurar o eliminar permanentemente
 * Solo accesible para supervisores
 */

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    echo '<div class="empty"><div class="empty-header text-danger">403</div><p class="empty-title">Acceso Denegado</p><p class="empty-subtitle text-secondary">Solo los supervisores pueden acceder a esta sección.</p></div>';
    return;
}

require_once 'models/User.php';
require_once 'models/Location.php';

$userModel = new User();
$locationModel = new Location();
$anioActual = $_SESSION['anio_trabajo'] ?? date('Y');

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
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-history"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 8l0 4l2 2" /><path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" /></svg>
                    Restaurar
                </button>
                <button id="btnDeletePermanentSelected" class="btn btn-danger" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
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
                    <div class="col-md-2">
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
                    <div class="col-md-2">
                        <label class="form-label">Comuna</label>
                        <select id="filterComuna" class="form-select">
                            <option value="">Todas</option>
                            <?php foreach ($comunas as $comuna): ?>
                                <option value="<?php echo htmlspecialchars($comuna['id']); ?>"><?php echo htmlspecialchars($comuna['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Establecimiento</label>
                        <select id="filterEstablecimiento" class="form-select" disabled>
                            <option value="">Todos</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Registrador</label>
                        <select id="filterRegistrador" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($registradores as $reg): ?>
                                <option value="<?php echo htmlspecialchars($reg['id']); ?>"><?php echo htmlspecialchars($reg['nombre_completo']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Búsqueda</label>
                        <input type="text" id="filterBusqueda" class="form-control" placeholder="Buscar en detalles, motivo...">
                    </div>
                    <div class="col-12">
                        <div class="btn-list">
                            <button id="btnApplyFilters" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-search"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                                Aplicar Filtros
                            </button>
                            <button id="btnClearFilters" class="btn btn-outline-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-x"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>
                                Limpiar
                            </button>
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
                            <th>Serie / Hoja</th>
                            <th>Mes</th>
                            <th>Estado Original</th>
                            <th>Eliminado Por</th>
                            <th>Motivo</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="observationsBody"></tbody>
                </table>
            </div>
            <div id="emptyState" class="d-none card-body text-center py-8 text-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash-x text-secondary mb-3"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7h16" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /><path d="M10 11l4 4" /><path d="M14 11l-4 4" /></svg>
                <p class="h3">No hay observaciones eliminadas</p>
                <p class="text-secondary mt-2">La papelera está vacía</p>
            </div>
            <!-- Paginación -->
            <div id="paginationContainer" class="d-none card-footer">
                <nav class="d-flex align-items-center justify-content-between">
                    <div class="text-secondary" id="paginationInfo"></div>
                    <ul class="pagination m-0" id="paginationButtons"></ul>
                </nav>
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

<script src="assets/js/papelera.js"></script>
<script>
    window.PAPELERA_CONFIG = {
        anio: <?php echo $anioActual; ?>
    };
</script>
