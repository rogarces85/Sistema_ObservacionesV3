<?php
/**
 * Vista de Gestión de Establecimientos
 * Solo accesible para supervisores
 */
?>

<div class="row row-cards">

    <!-- Encabezado -->
    <div class="col-12">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <div class="page-pretitle">Agregar, editar y activar/desactivar establecimientos de salud</div>
                <h2 class="page-title">Gestión de Establecimientos</h2>
            </div>
            <button onclick="gestorEst.abrirCrear()" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                Nuevo Establecimiento
            </button>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="col-12">
        <div class="row g-3">
            <div class="col-sm-4">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="h1 text-green" id="statActivos">—</div>
                        <div class="text-secondary text-uppercase small">Activos</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="h1 text-secondary" id="statInactivos">—</div>
                        <div class="text-secondary text-uppercase small">Inactivos</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="card card-sm">
                    <div class="card-body text-center">
                        <div class="h1" id="statTotal">—</div>
                        <div class="text-secondary text-uppercase small">Total</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Buscar por nombre o código:</label>
                        <input type="text" id="busqueda" class="form-control" placeholder="Ej: Hospital, 101...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filtrar por comuna:</label>
                        <select id="filtroComuna" class="form-select">
                            <option value="">Todas las comunas</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-check">
                            <input type="checkbox" class="form-check-input" id="incluirInactivos">
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
            <div class="card-header">
                <h3 class="card-title">Listado de Establecimientos</h3>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover">
                    <thead>
                        <tr>
                            <th class="w-15">Código DEIS</th>
                            <th>Nombre</th>
                            <th class="w-15">Nombre Corto</th>
                            <th class="w-20">Comuna</th>
                            <th class="w-10 text-center">Estado</th>
                            <th class="w-10 text-center">Referentes</th>
                            <th class="w-20 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tablaEstablecimientos">
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-4">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                Cargando establecimientos...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear/Editar Establecimiento -->
<div id="modalEstablecimiento" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEstablecimientoTitulo">Nuevo Establecimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEstablecimiento">
                <div class="modal-body">
                    <input type="hidden" id="estId" value="">
                    <div class="mb-3">
                        <label class="form-label required">Código de Establecimiento</label>
                        <input type="number" id="estCodigo" class="form-control" required placeholder="Ej: 101">
                        <div class="form-hint">Código numérico único del establecimiento (DEIS)</div>
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
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarEst">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Gestión de Referentes -->
<div id="modalReferentes" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="referentesTitulo">Referentes del Establecimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="refEstablecimientoId" value="">
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <p class="text-secondary mb-0">Personas de contacto para observaciones REM</p>
                    <button onclick="gestorEst.abrirCrearReferente()" class="btn btn-sm btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
                        Nuevo Referente
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-vcenter table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Cargo</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaReferentes">
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    Cargando referentes...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Crear/Editar Referente -->
<div id="modalReferenteForm" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReferenteFormTitulo">Nuevo Referente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formReferente">
                <div class="modal-body">
                    <input type="hidden" id="refId" value="">
                    <div class="mb-3">
                        <label class="form-label required">Nombre Completo</label>
                        <input type="text" id="refNombre" class="form-control" required placeholder="Ej: María González López">
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Cargo</label>
                        <select id="refCargo" class="form-select" required>
                            <option value="">Seleccionar cargo...</option>
                            <option value="Encargado de Estadísticas">Encargado de Estadísticas</option>
                            <option value="Digitador de Estadísticas">Digitador de Estadísticas</option>
                            <option value="Jefe de Servicio">Jefe de Servicio</option>
                            <option value="Administrativo">Administrativo</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" id="refTelefono" class="form-control" placeholder="Ej: +56912345678">
                        <div class="form-hint">Formato: +569XXXXXXXX o XXXXXXXX</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" id="refEmail" class="form-control" placeholder="Ej: maria.gonzalez@ssor.cl">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarRef">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="assets/js/establecimientos.js"></script>
