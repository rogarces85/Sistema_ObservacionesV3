<?php
/**
 * Vista de Importación de Observaciones desde Excel
 * Solo accesible para rol Registrador
 */

if (($_SESSION['rol'] ?? '') !== ROL_REGISTRADOR) {
    header('Location: ?page=dashboard&year=' . ($_SESSION['year'] ?? date('Y')));
    exit;
}

$anioActual = $_SESSION['year'] ?? date('Y');
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="page-header d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <div class="page-pretitle">Carga masiva de observaciones</div>
            <h2 class="page-title">Importar desde Excel</h2>
        </div>
        <div class="btn-list">
            <a href="api/import_template.php" class="btn btn-outline-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-download" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"/><polyline points="7 11 12 16 17 11"/><line x1="12" y1="4" x2="12" y2="16"/></svg>
                Descargar Plantilla
            </a>
        </div>
    </div>

    <!-- Zona de carga -->
    <div class="card" id="cardCarga">
        <div class="card-header">
            <h3 class="card-title">Subir archivo Excel</h3>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label required">Año</label>
                    <select id="importAnio" class="form-select">
                        <?php
                        $anioInicio = 2020;
                        $anioFin = date('Y') + 1;
                        for ($a = $anioFin; $a >= $anioInicio; $a--) {
                            $sel = ($a == $anioActual) ? 'selected' : '';
                            echo "<option value=\"{$a}\" {$sel}>{$a}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Zona drag & drop -->
            <div id="zonaCarga" class="border border-dashed rounded p-5 text-center" style="min-height: 200px; cursor: pointer;">
                <input type="file" id="inputArchivo" accept=".xlsx,.xls" class="d-none">
                <div id="mensajeCarga">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon text-muted mb-3" width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><path d="M12 11v6"/><path d="M9.5 14.5l2.5 -2.5l2.5 2.5"/></svg>
                    <p class="fs-5 mb-1">Arrastre su archivo Excel aquí</p>
                    <p class="text-muted mb-3">o haga clic para seleccionar</p>
                    <p class="text-muted small">Formatos aceptados: .xlsx, .xls</p>
                </div>
                <div id="archivoSeleccionado" class="d-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon text-success mb-2" width="36" height="36" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4"/><path d="M5 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6m4 0h1.5a1.5 1.5 0 0 0 0 -3h-1.5v3m4 0h1.5a1.5 1.5 0 0 0 0 -3h-1.5v3m4 0h.5a1.5 1.5 0 0 0 0 -3h-.5v3"/></svg>
                    <p class="fs-5 mb-1" id="nombreArchivo"></p>
                    <p class="text-muted small" id="tamanoArchivo"></p>
                    <button type="button" id="btnCambiarArchivo" class="btn btn-sm btn-outline-secondary mt-2">Cambiar archivo</button>
                </div>
            </div>

            <!-- Botón generar preview -->
            <div class="mt-4 text-end">
                <button type="button" id="btnGenerarPreview" class="btn btn-primary" disabled>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                    Generar Vista Previa
                </button>
            </div>
        </div>
    </div>

    <!-- Resumen de resultados -->
    <div id="seccionResumen" class="d-none">
        <div class="row g-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="bg-blue text-white avatar"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 7h-11v4h2v9h3v-6h4v6h3v-9h2v-4h-3z"/></svg></span></div>
                            <div class="col"><div class="font-weight-medium" id="resumenTotal">0</div><div class="text-secondary">Total filas</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="bg-green text-white avatar"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg></span></div>
                            <div class="col"><div class="font-weight-medium" id="resumenValidas">0</div><div class="text-secondary">Válidas</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="bg-red text-white avatar"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M3.607 8.604a9 9 0 1 1 12.875 12.875"/></svg></span></div>
                            <div class="col"><div class="font-weight-medium" id="resumenErrores">0</div><div class="text-secondary">Con errores</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto"><span class="bg-yellow text-white avatar"><svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M3.607 8.604a9 9 0 1 1 12.875 12.875"/></svg></span></div>
                            <div class="col"><div class="font-weight-medium" id="resumenDuplicados">0</div><div class="text-secondary">Duplicados</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de vista previa (filas válidas) -->
    <div id="seccionPreview" class="card d-none">
        <div class="card-header">
            <h3 class="card-title">Vista Previa - Filas Válidas</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th># Fila</th>
                        <th>Establecimiento</th>
                        <th>Mes</th>
                        <th>Serie</th>
                        <th>Hoja</th>
                        <th>Tipo</th>
                        <th>Detalle</th>
                        <th>Plazo</th>
                    </tr>
                </thead>
                <tbody id="cuerpoPreview"></tbody>
            </table>
        </div>
    </div>

    <!-- Tabla de errores -->
    <div id="seccionErrores" class="card d-none">
        <div class="card-header">
            <h3 class="card-title text-danger">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M3.607 8.604a9 9 0 1 1 12.875 12.875"/></svg>
                Filas con Errores
            </h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table table-striped">
                <thead>
                    <tr>
                        <th># Fila</th>
                        <th>Errores</th>
                    </tr>
                </thead>
                <tbody id="cuerpoErrores"></tbody>
            </table>
        </div>
    </div>

    <!-- Alerta de duplicados -->
    <div id="seccionDuplicados" class="d-none">
        <div class="alert alert-warning alert-dismissible">
            <h4 class="alert-title">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M3.607 8.604a9 9 0 1 1 12.875 12.875"/></svg>
                Duplicados Detectados
            </h4>
            <div id="listaDuplicados"></div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div id="seccionAcciones" class="d-none">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input type="checkbox" id="chkOmitirDuplicados" class="form-check-input">
                    <label for="chkOmitirDuplicados" class="form-check-label">Omitir registros duplicados (solo importar nuevos)</label>
                </div>
                <div class="btn-list">
                    <button type="button" id="btnCancelar" class="btn btn-secondary">Cancelar</button>
                    <button type="button" id="btnConfirmar" class="btn btn-success">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10"/></svg>
                        Confirmar Importación
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/importacion.js"></script>
