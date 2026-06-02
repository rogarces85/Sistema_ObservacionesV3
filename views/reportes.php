<?php
/**
 * Vista de Reportes y Exportación
 * Filtros (año, mes, estado, establecimiento) + botones de exportación
 * Tabla paginada (20/page) para vista web
 */

require_once 'models/Location.php';

$anioActual = $_SESSION['anio_trabajo'] ?? date('Y');
$rolUsuario = $_SESSION['rol'];

$modeloUbicacion = new Location();
$comunas = $modeloUbicacion->getComunas();

$listaMeses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$listaEstados = [
    'pendiente' => 'Pendiente',
    'aprobado' => 'Aprobado',
    'error' => 'Error',
    'rechazado' => 'Rechazado'
];
?>

<div class="row row-cards">

    <div class="col-12">
        <div class="page-header">
            <div class="page-pretitle">Exportación y análisis de observaciones REM</div>
            <h2 class="page-title">Reportes y Exportación</h2>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title mb-3">
                    <?php echo tablerIcon('filter'); ?>
                    Filtros
                </h3>
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Año</label>
                        <select id="filtroAnio" class="form-select">
                            <?php for ($a = date('Y') + 1; $a >= 2020; $a--): ?>
                                <option value="<?php echo $a; ?>" <?php echo $a == $anioActual ? 'selected' : ''; ?>><?php echo $a; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Mes</label>
                        <select id="filtroMes" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($listaMeses as $mes): ?>
                                <option value="<?php echo $mes; ?>"><?php echo $mes; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Estado</label>
                        <select id="filtroEstado" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($listaEstados as $valor => $etiqueta): ?>
                                <option value="<?php echo $valor; ?>"><?php echo $etiqueta; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Comuna</label>
                        <select id="filtroComuna" class="form-select">
                            <option value="">Todas</option>
                            <?php foreach ($comunas as $comuna): ?>
                                <option value="<?php echo $comuna['id']; ?>"><?php echo htmlspecialchars($comuna['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Establecimiento</label>
                        <select id="filtroEstablecimiento" class="form-select" disabled>
                            <option value="">Todos</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Tipo Error</label>
                        <select id="filtroTipoError" class="form-select">
                            <option value="">Todos</option>
                            <option value="ERROR">Error</option>
                            <option value="S/OBSERVACION">S/Observación</option>
                            <option value="REVISAR">Revisar</option>
                            <option value="F/PLAZO">Fuera de Plazo</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="btn-list">
                            <button id="btnAplicarFiltros" class="btn btn-primary">
                                <?php echo tablerIcon('search'); ?>
                                Aplicar Filtros
                            </button>
                            <button id="btnLimpiarFiltros" class="btn btn-outline-secondary">
                                <?php echo tablerIcon('x'); ?>
                                Limpiar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <?php echo tablerIcon('download'); ?>
                    Exportar Datos
                </h3>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Formato</label>
                        <select id="exportFormato" class="form-select">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tipo de Reporte</label>
                        <select id="exportTipoReporte" class="form-select">
                            <option value="general">General (tabla plana)</option>
                            <option value="detallado">Detallado (jerárquico Comuna→Establecimiento→Mes)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div id="exportInfo" class="text-muted small">
                            Aplique filtros para ver el conteo
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button id="btnExportar" class="btn btn-success w-100" disabled>
                            <?php echo tablerIcon('file-export'); ?>
                            Exportar
                        </button>
                    </div>
                </div>
                <div id="exportColaMensaje" class="mt-3 d-none">
                    <div class="alert alert-info">
                        <h4 class="alert-title">
                            <?php echo tablerIcon('clock'); ?>
                            Reporte en proceso
                        </h4>
                        <p class="text-muted">El reporte se está generando en segundo plano. Se notificará cuando esté listo.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($rolUsuario === ROL_SUPERVISOR): ?>
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <?php echo tablerIcon('report'); ?>
                    Informe de Errores REM (Solo Supervisor)
                </h3>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Tipo</label>
                        <select id="informeTipo" class="form-select">
                            <option value="trimestral">Trimestral</option>
                            <option value="anual">Anual</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Trimestre</label>
                        <select id="informeTrimestre" class="form-select">
                            <option value="1">1° Trimestre</option>
                            <option value="2">2° Trimestre</option>
                            <option value="3">3° Trimestre</option>
                            <option value="4">4° Trimestre</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Año</label>
                        <select id="informeAnio" class="form-select">
                            <?php for ($a = date('Y'); $a >= 2020; $a--): ?>
                                <option value="<?php echo $a; ?>" <?php echo $a == $anioActual ? 'selected' : ''; ?>><?php echo $a; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Formato</label>
                        <select id="informeFormato" class="form-select">
                            <option value="json">JSON (vista web)</option>
                            <option value="pdf">PDF (descarga)</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button id="btnGenerarInforme" class="btn btn-purple w-100">
                            <?php echo tablerIcon('file-invoice'); ?>
                            Generar Informe
                        </button>
                    </div>
                </div>
                <div id="informeResultado" class="mt-3 d-none"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <?php echo tablerIcon('table'); ?>
                    Vista Previa de Observaciones
                </h3>
                <div class="ms-auto">
                    <span id="previewTotal" class="badge bg-muted">0 registros</span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-vcenter card-table table-striped" id="tablaPreview">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Año</th>
                                <th>Mes</th>
                                <th>Comuna</th>
                                <th>Establecimiento</th>
                                <th>Serie</th>
                                <th>Hoja</th>
                                <th>Tipo</th>
                                <th>Detalle</th>
                                <th>Plazo</th>
                                <th>Estado</th>
                                <th>Clasificación</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpoTablaPreview">
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">
                                    Aplique filtros para ver la vista previa
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <nav id="paginacionPreview" class="d-flex justify-content-between align-items-center mt-3" style="display: none !important;">
                    <span id="infoPaginacion" class="text-muted small"></span>
                    <ul class="pagination pagination-sm m-0" id="listaPaginacion"></ul>
                </nav>
            </div>
        </div>
    </div>

</div>

<script src="assets/js/reportes.js"></script>
