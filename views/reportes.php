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
                        <label class="form-label">Trimestre</label>
                        <select id="filtroTrimestre" class="form-select">
                            <option value="">Todos</option>
                            <option value="1">1° Trimestre</option>
                            <option value="2">2° Trimestre</option>
                            <option value="3">3° Trimestre</option>
                            <option value="4">4° Trimestre</option>
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
                    <?php echo tablerIcon('chart-bar'); ?>
                    Reportes Analíticos
                </h3>
                <div class="ms-auto">
                    <button id="btnActualizarAnaliticos" class="btn btn-primary btn-sm">
                        <?php echo tablerIcon('refresh'); ?>
                        Actualizar análisis
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4" id="indicadoresAnaliticos">
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm reportes-analytics__metric reportes-analytics__metric--primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="reportes-analytics__metric-icon"><?php echo tablerIcon('database'); ?></span>
                                    <div>
                                        <div class="text-muted">Observaciones analizadas</div>
                                        <div class="h2 mb-0" data-indicador="total_observaciones">0</div>
                                        <div class="small text-muted">Base del análisis</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm reportes-analytics__metric reportes-analytics__metric--danger">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="reportes-analytics__metric-icon"><?php echo tablerIcon('alert-triangle'); ?></span>
                                    <div>
                                        <div class="text-muted">Errores</div>
                                        <div class="h2 mb-0 text-danger" data-indicador="total_errores">0</div>
                                        <div class="small text-muted">Observaciones con error</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm reportes-analytics__metric reportes-analytics__metric--warning">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="reportes-analytics__metric-icon"><?php echo tablerIcon('clock-exclamation'); ?></span>
                                    <div>
                                        <div class="text-muted">Fuera de plazo</div>
                                        <div class="h2 mb-0 text-warning" data-indicador="total_fuera_plazo">0</div>
                                        <div class="small text-muted">Entregas atrasadas</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="card card-sm reportes-analytics__metric reportes-analytics__metric--purple">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="reportes-analytics__metric-icon"><?php echo tablerIcon('shield-x'); ?></span>
                                    <div>
                                        <div class="text-muted">Sin validador</div>
                                        <div class="h2 mb-0 text-purple" data-indicador="total_sin_validador">0</div>
                                        <div class="small text-muted">Registros sin validación</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs mb-3" id="reportesAnaliticosTabs" role="tablist">
                    <li class="nav-item" role="presentation"><button class="nav-link active" data-categoria="errores_establecimiento" type="button">Errores por establecimiento</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-categoria="plazos_entrega" type="button">Plazos de entrega</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-categoria="uso_validador" type="button">Uso de validador</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-categoria="errores_serie" type="button">Errores por serie</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" data-categoria="errores_hoja" type="button">Errores por hoja</button></li>
                </ul>

                <div id="reportesAnaliticosContenido">
                    <?php
                    $categoriasAnaliticas = [
                        'errores_establecimiento' => 'Errores por establecimiento',
                        'plazos_entrega' => 'Plazos de entrega',
                        'uso_validador' => 'Uso de validador',
                        'errores_serie' => 'Errores por serie',
                        'errores_hoja' => 'Errores por hoja'
                    ];
                    foreach ($categoriasAnaliticas as $categoria => $titulo):
                    ?>
                    <section class="reportes-analytics__panel <?php echo $categoria === 'errores_establecimiento' ? '' : 'd-none'; ?>" data-panel-categoria="<?php echo $categoria; ?>">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                            <div>
                                <h4 class="mb-1"><?php echo $titulo; ?></h4>
                                <p class="text-muted mb-0">Resumen visual y tabla calculados con los filtros activos.</p>
                            </div>
                            <button class="btn btn-outline-success btn-sm" data-exportar-analitico="<?php echo $categoria; ?>" disabled>
                                <?php echo tablerIcon('file-spreadsheet'); ?>
                                Exportar categoría
                            </button>
                        </div>
                        <div class="reportes-analytics__estado text-muted" data-estado-categoria="<?php echo $categoria; ?>">Aplique filtros para cargar esta categoría.</div>
                        <div class="row g-3 align-items-stretch">
                            <div class="col-lg-8">
                                <div class="reportes-analytics__chart" id="grafico-<?php echo $categoria; ?>"></div>
                            </div>
                            <div class="col-lg-4">
                                <div class="reportes-analytics__top" data-destacados-categoria="<?php echo $categoria; ?>">
                                    <div class="text-muted text-center py-4">Sin destacados</div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive mt-3">
                            <table class="table table-vcenter table-sm card-table" data-tabla-categoria="<?php echo $categoria; ?>">
                                <thead>
                                    <tr>
                                        <th>Dimensión</th>
                                        <th>Comuna</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">%</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td colspan="4" class="text-center text-muted py-4">Sin datos cargados</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <?php endforeach; ?>
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

<script src="assets/libs/apexcharts/dist/apexcharts.min.js"></script>
<script src="assets/js/reportes.js"></script>
