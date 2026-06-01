<?php
/**
 * Vista de Supervisión - Fase 6
 * Tabla filtrable con paginación (50/page), modales de aprobación/cancelación/eliminación
 * Solo accesible para rol Supervisor
 */

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    echo '<div class="p-6 text-center"><h2 class="text-xl font-bold text-rose-600">Acceso Denegado</h2><p>Solo los supervisores pueden acceder a esta sección.</p></div>';
    return;
}

require_once 'config/constants.php';
require_once 'config/database.php';

$usuarioId = $_SESSION['usuario_id'];
$anioActual = $_SESSION['year'] ?? date('Y');

global $TIPOS_ERROR, $MESES;

$db = Database::obtenerInstancia();
$registradores = $db->consultar(
    "SELECT id, nombre_completo FROM usuarios WHERE rol = ? ORDER BY nombre_completo",
    [ROL_REGISTRADOR]
);
$comunas = $db->consultar("SELECT id, nombre FROM comunas ORDER BY nombre");
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
                <span class="fw-semibold" id="selectedNumber">0</span> seleccionadas
            </span>
            <button id="btnAprobarSeleccion" class="btn btn-success" disabled>
                <?php echo tablerIcon('check'); ?>
                Aprobar
            </button>
            <button id="btnCancelarSeleccion" class="btn btn-warning" disabled>
                <?php echo tablerIcon('x'); ?>
                Cancelar
            </button>
            <button id="btnEliminarSeleccion" class="btn btn-danger" disabled>
                <?php echo tablerIcon('trash'); ?>
                Eliminar
            </button>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="row g-3" id="statsContainer">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-primary text-white avatar"><?php echo tablerIcon('file-text'); ?></span></div>
                        <div class="col"><div class="font-weight-medium" id="statTotal">-</div><div class="text-secondary">Total</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-yellow text-white avatar"><?php echo tablerIcon('clock'); ?></span></div>
                        <div class="col"><div class="font-weight-medium" id="statPendiente">-</div><div class="text-secondary">Pendientes</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-green text-white avatar"><?php echo tablerIcon('check-circle'); ?></span></div>
                        <div class="col"><div class="font-weight-medium" id="statAprobado">-</div><div class="text-secondary">Aprobados</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-red text-white avatar"><?php echo tablerIcon('alert-circle'); ?></span></div>
                        <div class="col"><div class="font-weight-medium" id="statError">-</div><div class="text-secondary">Errores</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><?php echo tablerIcon('filter'); ?> Filtros</h3>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Buscar</label>
                    <input type="text" id="filtroBusqueda" class="form-control" placeholder="Establecimiento o detalle...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Mes</label>
                    <select id="filtroMes" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($MESES as $mes): ?>
                            <option value="<?php echo $mes; ?>"><?php echo $mes; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select id="filtroEstado" class="form-select">
                        <option value="">Todos</option>
                        <option value="<?php echo ESTADO_PENDIENTE; ?>">Pendiente</option>
                        <option value="<?php echo ESTADO_APROBADO; ?>">Aprobado</option>
                        <option value="<?php echo ESTADO_RECHAZADO; ?>">Rechazado</option>
                        <option value="<?php echo ESTADO_ERROR; ?>">Error</option>
                        <option value="<?php echo ESTADO_JUSTIFICADO; ?>">Justificado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tipo Error</label>
                    <select id="filtroTipoError" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($TIPOS_ERROR as $tipo): ?>
                            <option value="<?php echo htmlspecialchars($tipo); ?>"><?php echo htmlspecialchars($tipo); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Comuna</label>
                    <select id="filtroComuna" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($comunas as $comuna): ?>
                            <option value="<?php echo $comuna['id']; ?>"><?php echo htmlspecialchars($comuna['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Establecimiento</label>
                    <select id="filtroEstablecimiento" class="form-select" disabled>
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Registrador</label>
                    <select id="filtroRegistrador" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($registradores as $reg): ?>
                            <option value="<?php echo $reg['id']; ?>"><?php echo htmlspecialchars($reg['nombre_completo']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button id="btnAplicarFiltros" class="btn btn-primary flex-fill">
                        <?php echo tablerIcon('search'); ?>
                        Buscar
                    </button>
                    <button id="btnLimpiarFiltros" class="btn btn-secondary">Limpiar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de observaciones -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Observaciones</h3>
            <div class="d-flex align-items-center gap-2">
                <input type="checkbox" id="seleccionarTodas" class="form-check-input">
                <label for="seleccionarTodas" class="form-check-label">Seleccionar todas</label>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped" id="tablaSupervision">
                <thead>
                    <tr>
                        <th class="w-1"></th>
                        <th>ID</th>
                        <th>Establecimiento</th>
                        <th>Mes</th>
                        <th>Serie / Hoja</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Registrado por</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTablaSupervision">
                    <tr><td colspan="10" class="text-center text-muted py-4">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
        <!-- Paginación -->
        <div class="card-footer d-flex justify-content-between align-items-center" id="paginacionContainer">
            <span class="text-muted" id="paginacionInfo"></span>
            <nav id="paginacionNav"></nav>
        </div>
    </div>
</div>

<!-- Modal de Detalle con Historial -->
<div class="modal fade" id="modalDetalle" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de Observación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center justify-content-between mb-4 p-3 rounded bg-light">
                    <div>
                        <h4 class="h5 mb-1" id="detEstablecimiento">-</h4>
                        <p class="text-secondary mb-0" id="detComuna">-</p>
                    </div>
                    <span id="detBadge" class="badge">-</span>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-primary-lt">
                            <div class="small text-primary fw-bold mb-1">Mes / Año</div>
                            <div id="detMesAnio" class="fw-semibold">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-purple-lt">
                            <div class="small text-purple fw-bold mb-1">Serie / Hoja</div>
                            <div id="detReferencia" class="fw-semibold">-</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded bg-amber-lt">
                            <div class="small text-amber fw-bold mb-1">Tipo de Error</div>
                            <div id="detTipo" class="fw-semibold">-</div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="small fw-bold mb-1">Detalle de la Observación</div>
                    <div id="detDetalle" class="p-3 bg-light rounded">-</div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="small fw-bold mb-1">Plazo de Entrega</div>
                        <div id="detPlazo">-</div>
                    </div>
                    <div class="col-md-6">
                        <div class="small fw-bold mb-1">Clasificación</div>
                        <div id="detClasificacion">-</div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="small text-muted">Registrado por: <span id="detRegistradoPor">-</span></div>
                    <div class="small text-muted">Fecha creación: <span id="detFechaCreacion">-</span></div>
                    <div class="small text-muted">Última actualización: <span id="detFechaActualizacion">-</span></div>
                </div>

                <!-- Historial -->
                <div class="mt-4">
                    <h6 class="fw-bold mb-3">Historial de Cambios</h6>
                    <div id="detHistorial" class="timeline">
                        <div class="text-muted text-center">Cargando historial...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Aprobación -->
<div class="modal fade" id="modalAprobar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><?php echo tablerIcon('check'); ?> Aprobar Observación(es)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAprobar">
                <div class="modal-body">
                    <p id="aprobarMensaje">¿Aprobar la(s) observación(es) seleccionada(s)?</p>
                    <input type="hidden" id="aprobarIds">

                    <div class="mb-3">
                        <label class="form-label required">Clasificación de Respuesta</label>
                        <div class="mt-2">
                            <label class="form-check form-check-inline">
                                <input type="radio" name="estado_resultante" value="sin_observacion" class="form-check-input" required>
                                <span class="form-check-label">Sin Observación</span>
                            </label>
                            <label class="form-check form-check-inline">
                                <input type="radio" name="estado_resultante" value="error" class="form-check-input">
                                <span class="form-check-label">Error</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Clasificación</label>
                        <select id="aprobarClasificacion" class="form-select">
                            <option value="">Sin clasificar</option>
                            <option value="corregido">Corregido</option>
                            <option value="error">Error</option>
                            <option value="sin_respuesta">Sin respuesta del Establecimiento</option>
                            <option value="respuesta_incorrecta">Respuesta incorrecta de Establecimiento</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Detalle Error</label>
                        <input type="text" id="aprobarDetalleError" class="form-control" placeholder="Descripción del error si aplica...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Comentario (opcional)</label>
                        <textarea id="aprobarComentario" class="form-control" rows="2" placeholder="Comentario adicional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success" id="btnConfirmarAprobar">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="btnAprobarSpinner"></span>
                        Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Cancelación -->
<div class="modal fade" id="modalCancelar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><?php echo tablerIcon('x'); ?> Cancelar Observación(es)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCancelar">
                <div class="modal-body">
                    <p id="cancelarMensaje">¿Cancelar la(s) observación(es) seleccionada(s)?</p>
                    <input type="hidden" id="cancelarIds">

                    <div class="mb-3">
                        <label class="form-label">Comentario (opcional)</label>
                        <textarea id="cancelarComentario" class="form-control" rows="3" placeholder="Motivo de la cancelación..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning" id="btnConfirmarCancelar">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="btnCancelarSpinner"></span>
                        Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Eliminación (Soft Delete) -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><?php echo tablerIcon('trash'); ?> Eliminar Observación(es)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEliminar">
                <div class="modal-body">
                    <p id="eliminarMensaje">¿Eliminar la(s) observación(es) seleccionada(s)?</p>
                    <p class="text-danger fw-bold">Esta acción moverá los registros a la papelera de reciclaje.</p>
                    <input type="hidden" id="eliminarIds">

                    <div class="mb-3">
                        <label class="form-label">Motivo de eliminación</label>
                        <textarea id="eliminarMotivo" class="form-control" rows="3" placeholder="Motivo de la eliminación..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger" id="btnConfirmarEliminar">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="btnEliminarSpinner"></span>
                        Confirmar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const TIPOS_ERROR = <?php echo json_encode($TIPOS_ERROR); ?>;
const MESES = <?php echo json_encode($MESES); ?>;
</script>
<script src="assets/js/supervision.js"></script>
