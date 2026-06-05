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
$anioActual = $_SESSION['anio_trabajo'] ?? date('Y');

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

<div class="modal-backdrop" id="modalDetalleBackdrop" onclick="if(event.target===this)SupervisionApp.cerrarModal('Detalle')"></div>
<div class="modal-container" id="modalDetalle">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3><?php echo tablerIcon('eye'); ?> Detalle de Observación</h3>
            <button onclick="SupervisionApp.cerrarModal('Detalle')" class="modal-close"><?php echo tablerIcon('x'); ?></button>
        </div>
        <div class="modal-body">
            <div class="modal-section">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h4 class="mb-1" id="detEstablecimiento">-</h4>
                        <p class="text-secondary mb-0" id="detComuna" style="font-size:0.875rem">-</p>
                    </div>
                    <span id="detBadge" class="modal-badge">-</span>
                </div>
            </div>
            <div class="modal-info-grid">
                <div class="modal-info-item"><label>Mes / Año</label><span id="detMesAnio">-</span></div>
                <div class="modal-info-item"><label>Serie / Hoja</label><span id="detReferencia">-</span></div>
                <div class="modal-info-item"><label>Tipo Error</label><span id="detTipo">-</span></div>
                <div class="modal-info-item"><label>Plazo</label><span id="detPlazo">-</span></div>
            </div>
            <div class="modal-section">
                <div class="modal-section-title">Detalle</div>
                <div class="modal-content-box" id="detDetalle">-</div>
            </div>
            <div class="modal-section">
                <div class="modal-info-grid" style="grid-template-columns:1fr 1fr">
                    <div class="modal-info-item"><label>Clasificación</label><span id="detClasificacion">-</span></div>
                    <div class="modal-info-item"><label>Registrado por</label><span id="detRegistradoPor">-</span></div>
                </div>
                <small class="text-secondary">Creación: <span id="detFechaCreacion">-</span> | Actualización: <span id="detFechaActualizacion">-</span></small>
            </div>
            <div class="modal-section">
                <div class="modal-section-title">Historial de Cambios</div>
                <div id="detHistorial"><div class="text-muted text-center">Cargando...</div></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="SupervisionApp.cerrarModal('Detalle')">Cerrar</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="modalAprobarBackdrop" onclick="if(event.target===this)SupervisionApp.cerrarModal('Aprobar')"></div>
<div class="modal-container" id="modalAprobar">
    <div class="modal">
        <div class="modal-header modal-header-success">
            <h3><?php echo tablerIcon('check'); ?> Aprobar Observación(es)</h3>
            <button onclick="SupervisionApp.cerrarModal('Aprobar')" class="modal-close"><?php echo tablerIcon('x'); ?></button>
        </div>
        <form id="formAprobar">
            <div class="modal-body">
                <p id="aprobarMensaje">¿Aprobar la(s) observación(es) seleccionada(s)?</p>
                <input type="hidden" id="aprobarIds">
                <div class="mb-3">
                    <label class="form-label required">Clasificación de Respuesta</label>
                    <div class="d-flex gap-4">
                        <label class="form-check">
                            <input type="radio" name="estado_resultante" value="sin_observacion" class="form-check-input" required>
                            <span class="form-check-label">Sin Observación</span>
                        </label>
                        <label class="form-check">
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
                <button type="button" class="btn btn-ghost" onclick="SupervisionApp.cerrarModal('Aprobar')">Cancelar</button>
                <button type="submit" class="btn btn-success" id="btnConfirmarAprobar">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="modalCancelarBackdrop" onclick="if(event.target===this)SupervisionApp.cerrarModal('Cancelar')"></div>
<div class="modal-container" id="modalCancelar">
    <div class="modal">
        <div class="modal-header modal-header-warning">
            <h3><?php echo tablerIcon('x'); ?> Cancelar Observación(es)</h3>
            <button onclick="SupervisionApp.cerrarModal('Cancelar')" class="modal-close"><?php echo tablerIcon('x'); ?></button>
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
                <button type="button" class="btn btn-ghost" onclick="SupervisionApp.cerrarModal('Cancelar')">Cancelar</button>
                <button type="submit" class="btn btn-warning" id="btnConfirmarCancelar">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="modalEliminarBackdrop" onclick="if(event.target===this)SupervisionApp.cerrarModal('Eliminar')"></div>
<div class="modal-container" id="modalEliminar">
    <div class="modal">
        <div class="modal-header modal-header-danger">
            <h3><?php echo tablerIcon('trash'); ?> Eliminar Observación(es)</h3>
            <button onclick="SupervisionApp.cerrarModal('Eliminar')" class="modal-close"><?php echo tablerIcon('x'); ?></button>
        </div>
        <form id="formEliminar">
            <div class="modal-body">
                <p id="eliminarMensaje">¿Eliminar la(s) observación(es) seleccionada(s)?</p>
                <div class="modal-alert modal-alert-danger">
                    <div class="modal-alert-icon"><?php echo tablerIcon('alert-circle', 20); ?></div>
                    <div class="modal-alert-content">Esta acción moverá los registros a la papelera de reciclaje.</div>
                </div>
                <input type="hidden" id="eliminarIds">
                <div class="mb-3 mt-3">
                    <label class="form-label">Motivo de eliminación</label>
                    <textarea id="eliminarMotivo" class="form-control" rows="3" placeholder="Motivo de la eliminación..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="SupervisionApp.cerrarModal('Eliminar')">Cancelar</button>
                <button type="submit" class="btn btn-danger" id="btnConfirmarEliminar">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<script>
const TIPOS_ERROR = <?php echo json_encode($TIPOS_ERROR); ?>;
const MESES = <?php echo json_encode($MESES); ?>;
</script>
<script src="assets/js/supervision.js"></script>
