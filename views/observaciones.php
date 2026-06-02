<?php
/**
 * Vista de Observaciones - CRUD completo
 * Tabla con filtros, paginación, modal de creación/edición, detalle con historial
 */

require_once 'config/constants.php';

$usuarioId = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'];
$anioActual = $_SESSION['anio_trabajo'] ?? date('Y');

global $TIPOS_ERROR, $MESES, $SERIES_REM, $HOJAS_POR_SERIE;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <div class="page-pretitle">Gestión de observaciones REM</div>
            <h2 class="page-title">Observaciones</h2>
        </div>
        <div class="btn-list">
            <?php if ($rol === ROL_REGISTRADOR): ?>
                <button onclick="ObservacionesApp.abrirModalCrear()" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
                    Nueva Observación
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="row g-3" id="statsContainer">
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-primary text-white avatar"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 7h-11v4h2v9h3v-6h4v6h3v-9h2v-4h-3z"/><path d="M17 7v4h4v2h-2v7h-3"/></svg></span></div>
                        <div class="col"><div class="font-weight-medium" id="statTotal">-</div><div class="text-secondary">Total</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-yellow text-white avatar"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M3.607 8.604a9 9 0 1 1 12.875 12.875"/></svg></span></div>
                        <div class="col"><div class="font-weight-medium" id="statPendiente">-</div><div class="text-secondary">Pendientes</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-green text-white avatar"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg></span></div>
                        <div class="col"><div class="font-weight-medium" id="statAprobado">-</div><div class="text-secondary">Aprobados</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-auto"><span class="bg-red text-white avatar"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7h16m-10 -4v4m6 0v4m-6 0v4m6 0v4m-10 -4h10m-10 0h-4"/></svg></span></div>
                        <div class="col"><div class="font-weight-medium" id="statError">-</div><div class="text-secondary">Errores</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
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
                        <?php foreach ($MESES as $i => $mes): ?>
                            <option value="<?php echo $mes; ?>"><?php echo $mes; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Estado</label>
                    <select id="filtroEstado" class="form-select">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="rechazado">Rechazado</option>
                        <option value="error">Error</option>
                        <option value="justificado">Justificado</option>
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
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button onclick="ObservacionesApp.cargarDatos()" class="btn btn-primary flex-fill">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"/><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"/></svg>
                        Buscar
                    </button>
                    <button onclick="ObservacionesApp.limpiarFiltros()" class="btn btn-secondary">Limpiar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de observaciones -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Observaciones</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped" id="tablaObservaciones">
                <thead>
                    <tr>
                        <th>Establecimiento</th>
                        <th>Mes</th>
                        <th>Serie / Hoja</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Registrado por</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="cuerpoTabla">
                    <tr><td colspan="7" class="text-center text-muted py-4">Cargando...</td></tr>
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

<!-- Modal Crear/Editar -->
<div class="modal fade" id="modalFormulario" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Nueva Observación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formObservacion" novalidate>
                <div class="modal-body">
                    <input type="hidden" id="obsId">
                    <input type="hidden" id="obsFechaActualizacion">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required">Mes</label>
                            <select id="frmMes" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($MESES as $mes): ?>
                                    <option value="<?php echo $mes; ?>"><?php echo $mes; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Establecimiento</label>
                            <select id="frmEstablecimiento" class="form-select" required>
                                <option value="">Seleccione...</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label required">Tipo</label>
                            <select id="frmTipoError" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($TIPOS_ERROR as $tipo): ?>
                                    <option value="<?php echo htmlspecialchars($tipo); ?>"><?php echo htmlspecialchars($tipo); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label required">Serie</label>
                            <select id="frmSerie" class="form-select" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($SERIES_REM as $serie): ?>
                                    <option value="<?php echo htmlspecialchars($serie); ?>"><?php echo htmlspecialchars($serie); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6" id="contenedorHoja">
                            <label class="form-label">Hoja REM</label>
                            <select id="frmHoja" class="form-select">
                                <option value="">Seleccione serie primero</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label required">Detalle</label>
                        <textarea id="frmDetalle" class="form-control" rows="4" required placeholder="Descripción de la observación..."></textarea>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Clasificación</label>
                            <input type="text" id="frmClasificacion" class="form-control" placeholder="Clasificación de respuesta">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Usa Validador</label>
                            <select id="frmUsaValidador" class="form-select">
                                <option value="">Seleccione...</option>
                                <option value="si">Sí</option>
                                <option value="no">No</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Plazo de Entrega</label>
                            <select id="frmPlazo" class="form-select">
                                <option value="">Seleccione...</option>
                                <option value="dentro_plazo">Dentro de Plazo</option>
                                <option value="fuera_plazo">Fuera de Plazo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="btnGuardarSpinner"></span>
                        <span id="btnGuardarTexto">Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Detalle con Historial -->
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
            <div class="modal-footer" id="detalleFooter">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminación -->
<div class="modal fade" id="modalEliminar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar esta observación?</p>
                <p class="text-danger fw-bold">Esta acción no se puede deshacer.</p>
                <input type="hidden" id="eliminarId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="ObservacionesApp.confirmarEliminacion()">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
const HOJAS_POR_SERIE = <?php echo json_encode($HOJAS_POR_SERIE); ?>;
const USUARIO_ROL = '<?php echo $rol; ?>';
</script>
<script src="assets/js/observaciones.js"></script>
