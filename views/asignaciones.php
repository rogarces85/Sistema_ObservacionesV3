<?php
/**
 * Vista de Asignaciones
 * Solo accesible para supervisores - Gestión de asignaciones anuales y temporales
 */

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    echo '<div class="empty"><div class="empty-header text-danger">403</div><p class="empty-title">Acceso Denegado</p><p class="empty-subtitle text-secondary">Solo los supervisores pueden acceder a esta sección.</p></div>';
    return;
}

$anioSeleccionado = $_SESSION['year'] ?? date('Y');
?>

<div class="row row-cards">
    <!-- Encabezado -->
    <div class="col-12">
        <div class="page-header d-flex align-items-center justify-content-between">
            <div>
                <div class="page-pretitle">Gestione los establecimientos y referentes por año</div>
                <h2 class="page-title">Asignación de Establecimientos</h2>
            </div>
            <div class="btn-list">
                <label class="form-label d-inline me-2 mb-0">Año:</label>
                <select id="selectorAnio" class="form-select d-inline w-auto">
                    <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $anioSeleccionado ? 'selected' : ''; ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <button id="btnCopiarAnio" class="btn btn-outline-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /><path d="M16 5l3 3" /></svg>
                    Copiar Año Anterior
                </button>
            </div>
        </div>
    </div>

    <!-- Estadísticas rápidas -->
    <div class="col-12">
        <div class="row g-3" id="estadisticasContainer">
            <div class="col-sm-3">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="h1 text-primary" id="statRegistradores">—</div>
                        <div class="text-secondary text-uppercase small">Registradores</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="h1 text-green" id="statAsignados">—</div>
                        <div class="text-secondary text-uppercase small">Con Asignación</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="h1 text-orange" id="statTemporales">—</div>
                        <div class="text-secondary text-uppercase small">Temporales Activas</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="h1 text-secondary" id="statSinAsignar">—</div>
                        <div class="text-secondary text-uppercase small">Sin Asignar</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Layout de dos columnas -->
    <div class="col-12">
        <div class="row g-4">
            <!-- Panel izquierdo: Lista de registradores -->
            <div class="col-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Registradores</h3>
                        <div class="card-subtitle">Seleccione un registrador</div>
                    </div>
                    <div class="list-group list-group-flush" id="listaRegistradores" style="max-height: 600px; overflow-y: auto;">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="mt-2 text-secondary">Cargando registradores...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel derecho: Establecimientos asignados + Contactos -->
            <div class="col-12 col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">Establecimientos y Contactos</h3>
                            <p class="card-subtitle" id="registradorSeleccionadoTexto">Seleccione un registrador</p>
                        </div>
                        <div id="accionesAsignacion" class="d-none">
                            <button id="btnAsignar" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                                Asignar
                            </button>
                        </div>
                    </div>
                    <div class="card-body" id="establecimientosContainer">
                        <div class="empty">
                            <div class="empty-icon"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 7v1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1m0 1a3 3 0 0 0 6 0v-1h-18l2 -4h14l-2 4" /><path d="M5 21l0 -10.15" /><path d="M19 21l0 -10.15" /><path d="M9 21v-4a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v4" /></svg></div>
                            <p class="empty-title">Seleccione un registrador</p>
                            <p class="empty-subtitle text-secondary">Para ver sus establecimientos y datos de contacto</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Reasignaciones Temporales Activas -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Reasignaciones Temporales Activas</h3>
                <div class="card-subtitle" id="temporalesSubtitulo">Establecimientos reasignados temporalmente</div>
            </div>
            <div class="card-body" id="reasignacionesTemporalesContainer">
                <div class="text-center py-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2 text-secondary">Cargando reasignaciones...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar establecimiento -->
<div id="modalAsignar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">Asignar Establecimientos</h5>
                    <div class="text-secondary" id="modalAsignarInfo"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tipo de Asignación</label>
                    <div class="space-y-2">
                        <label class="form-selectgroup-item p-3 border rounded cursor-pointer">
                            <input type="radio" name="tipoAsignacion" value="anual" class="form-check-input me-2" checked>
                            Anual <span class="text-secondary">— Asignación base para todo el año</span>
                        </label>
                        <label class="form-selectgroup-item p-3 border rounded cursor-pointer">
                            <input type="radio" name="tipoAsignacion" value="temporal" class="form-check-input me-2">
                            Temporal <span class="text-secondary">— Reasignación por meses específicos</span>
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Buscar Establecimiento</label>
                    <input type="text" id="buscarEstablecimiento" class="form-control" placeholder="Escriba para buscar por nombre o comuna...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Establecimientos Disponibles</label>
                    <div id="listaEstablecimientosDisponibles" class="border rounded" style="max-height: 260px; overflow-y: auto;"></div>
                </div>
                <div id="periodoContainer" class="mb-3">
                    <label class="form-label">Periodo de validez</label>
                    <div class="space-y-2">
                        <label class="form-check">
                            <input type="radio" name="periodoAsignacion" value="ALL" class="form-check-input" checked>
                            <span class="form-check-label">Todo el año <span class="text-secondary" id="anioPeriodoLabel"></span></span>
                        </label>
                        <label class="form-check">
                            <input type="radio" name="periodoAsignacion" value="MESES" class="form-check-input">
                            <span class="form-check-label">Meses específicos</span>
                        </label>
                        <div id="mesesEspecificosContainer" class="d-none ms-4 mt-2">
                            <div class="row g-2">
                                <?php
                                $nombresMeses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                                foreach ($nombresMeses as $i => $nombre):
                                    $numero = $i + 1;
                                ?>
                                <div class="col-3">
                                    <label class="form-check">
                                        <input type="checkbox" class="form-check-input mes-checkbox" value="<?php echo $numero; ?>">
                                        <span class="form-check-label"><?php echo $nombre; ?></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="btnGuardarAsignaciones" class="btn btn-primary">Guardar Asignaciones</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de referentes -->
<div id="modalReferentes" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReferentesTitulo">Referentes del Establecimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalReferentesBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/asignaciones.js"></script>
