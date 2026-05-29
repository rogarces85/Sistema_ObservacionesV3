<?php
/**
 * Vista de Observaciones
 * CRUD completo de observaciones REM
 */

require_once 'models/Observation.php';
require_once 'models/Location.php';
require_once 'models/EstablecimientoAsignacion.php';
require_once 'config/constants.php';

$obsModel = new Observation();
$locModel = new Location();
$asigModel = new EstablecimientoAsignacion();
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['rol'];
$currentYear = $_SESSION['year'] ?? date('Y');

// Obtener datos necesarios
$observations = $obsModel->getAll($currentYear, $userId, $userRole);
$comunas = $locModel->getAllComunas();

// Establecimientos según rol
$tieneAsignaciones = false;
if ($userRole === ROL_REGISTRADOR) {
    $tieneAsignaciones = $asigModel->tieneAsignaciones($userId, $currentYear);
    if ($tieneAsignaciones) {
        $establecimientos = $asigModel->getEstablecimientosByRegistrador($userId, $currentYear);
    } else {
        $establecimientos = [];
    }
} else {
    $establecimientos = $locModel->getAllEstablecimientos();
}

global $TIPOS_ERROR, $MESES;
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Listado de Observaciones</h2>
            <p class="text-slate-600">Gestiona y realiza seguimiento de tus registros REM</p>
        </div>
        <div class="flex gap-2">
            <?php if ($userRole === ROL_REGISTRADOR): ?>
                <?php if (!$tieneAsignaciones): ?>
                    <!-- Sin botones de acción si no tiene asignaciones -->
                <?php else: ?>
                    <button onclick="openImportModal()" class="btn btn-secondary">
                        📥 Importar
                    </button>
                    <button onclick="openCreateModal()" class="btn btn-primary">
                        ➕ Nueva Observación
                    </button>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($userRole === ROL_REGISTRADOR && !$tieneAsignaciones): ?>
        <div class="p-6 rounded-xl bg-amber-50 border border-amber-200 text-center">
            <div class="text-4xl mb-3">⚠️</div>
            <p class="font-bold text-amber-800 text-lg">No tiene establecimientos asignados</p>
            <p class="text-sm text-amber-700 mt-2">
                No tiene establecimientos asignados para el año <strong><?php echo $currentYear; ?></strong>. 
                No podrá registrar observaciones hasta que su supervisor le asigne establecimientos.
            </p>
            <p class="text-xs text-amber-600 mt-4">
                Contacte a su supervisor para solicitar la asignación de establecimientos.
            </p>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card p-4">
        <div class="flex flex-wrap gap-4 items-center">
            <div class="flex-1" style="min-width: 250px;">
                <input type="text" id="searchInput" placeholder="🔍 Buscar por establecimiento o detalle..."
                    class="w-full" oninput="filterTable()">
            </div>
            <select id="filterEstado" class="px-4 py-3" style="min-width: 160px;" onchange="filterTable()">
                <option value="">Todos los estados</option>
                <option value="pendiente">🟡 Pendiente</option>
                <option value="aprobado">🟢 Aprobado</option>
                <option value="rechazado">🔴 Rechazado</option>
                <option value="error">⚠️ Error</option>
                <option value="justificado">🔵 Justificado</option>
            </select>
            <select id="filterMes" class="px-4 py-3" style="min-width: 140px;" onchange="filterTable()">
                <option value="">Todos los meses</option>
                <?php foreach ($MESES as $mes): ?>
                    <option value="<?php echo $mes; ?>">
                        <?php echo $mes; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table id="observationsTable">
                <thead>
                    <tr>
                        <th>Establecimiento</th>
                        <th>Referencia</th>
                        <th>Tipo de Error</th>
                        <th>Estado</th>
                        <th>Registrado por</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($observations as $obs): ?>
                        <tr data-estado="<?php echo $obs['estado_actual']; ?>" data-mes="<?php echo $obs['mes']; ?>">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div>
                                        <div class="text-sm font-bold text-slate-800">
                                            <?php echo htmlspecialchars($obs['nombre_corto']); ?>
                                        </div>
                                        <div class="text-xs text-slate-400">
                                            <?php echo htmlspecialchars($obs['comuna']) . ' • ' . htmlspecialchars($obs['mes']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-xs font-semibold text-slate-500">Serie
                                    <?php echo htmlspecialchars($obs['codigo_serie']); ?>
                                </div>
                                <div class="text-xs text-slate-400">Hoja
                                    <?php echo htmlspecialchars($obs['codigo_hoja']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="text-xs font-medium text-slate-600">
                                    <?php echo htmlspecialchars($obs['tipo_error']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $obs['estado_actual']; ?>">
                                    <?php echo ucfirst($obs['estado_actual']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-sm text-slate-700">
                                    <?php echo htmlspecialchars($obs['nombre_registro']); ?>
                                </div>
                                <div class="text-xs text-slate-400">
                                    <?php echo $obs['fecha_registro'] ? date('d/m/Y', strtotime($obs['fecha_registro'])) : 'Sin fecha'; ?>
                                </div>
                            </td>
                            <td class="text-right">
                                <button onclick="viewObservation(<?php echo $obs['id']; ?>)"
                                    class="btn-secondary px-3 py-1 text-xs" title="Ver detalle">
                                    👁️
                                </button>
                                <?php
                                $canEdit = ($userRole === ROL_SUPERVISOR) ||
                                    ($userRole === ROL_REGISTRADOR && $obs['usuario_registro_id'] == $userId && $obs['estado_actual'] === ESTADO_PENDIENTE);
                                if ($canEdit):
                                    ?>
                                    <button onclick="editObservation(<?php echo $obs['id']; ?>)"
                                        class="btn-secondary px-3 py-1 text-xs" title="Editar">
                                        ✏️
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($observations)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-slate-400 py-8">
                                No se encontraron observaciones para el año
                                <?php echo $currentYear; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Crear/Editar -->
<div id="modalObservation" class="modal-overlay hidden">
    <div class="modal-content">
        <div class="modal-header">
            <div>
                <h3 id="modalTitle" class="text-xl font-bold text-slate-800">Nueva Observación</h3>
                <p class="text-sm text-slate-500">Complete los datos de la observación</p>
            </div>
            <button onclick="closeModal('modalObservation')" class="btn-secondary px-3 py-2" type="button">✕</button>
        </div>
        <div class="modal-body">
            <form id="formObservation" onsubmit="saveObservation(event)" class="space-y-4">
                <input type="hidden" id="obsId" value="">

                <!-- Información del Registrador (solo lectura) -->
                <div class="bg-slate-100 p-3 rounded-lg mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Registrado por:</label>
                    <p class="text-lg font-bold text-primary-600">
                        <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario'); ?>
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Mes *</label>
                        <select id="mes" name="mes" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($MESES as $mes): ?>
                                <option value="<?php echo $mes; ?>">
                                    <?php echo $mes; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Establecimiento *</label>
                        <select id="establecimiento_id" name="establecimiento_id" onchange="loadEstablecimientoCodigo()"
                            required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($establecimientos as $est): ?>
                                <option value="<?php echo $est['id']; ?>"
                                    data-codigo="<?php echo htmlspecialchars($est['codigo_establecimiento'] ?? $est['nombre_corto']); ?>">
                                    <?php echo htmlspecialchars($est['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Código Establecimiento</label>
                        <input type="text" id="codigo_establecimiento" name="codigo_establecimiento" readonly
                            class="bg-slate-50" placeholder="Se cargará automáticamente">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Tipo *</label>
                        <select id="tipo_error" name="tipo_error" required onchange="handleTipoChange()">
                            <option value="">Seleccione...</option>
                            <?php foreach ($TIPOS_ERROR as $tipo): ?>
                                <option value="<?php echo htmlspecialchars($tipo); ?>">
                                    <?php echo htmlspecialchars($tipo); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Serie</label>
                        <select id="codigo_serie" name="codigo_serie" onchange="loadHojasREM()">
                            <option value="">Seleccione...</option>
                            <?php foreach ($SERIES_REM as $serie): ?>
                                <option value="<?php echo htmlspecialchars($serie); ?>">
                                    <?php echo htmlspecialchars($serie); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="hojaRemContainer">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">REM (Hoja)</label>
                        <select id="codigo_hoja" name="codigo_hoja" disabled>
                            <option value="">Primero seleccione una Serie</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Detalle de la Observación</label>
                    <textarea id="detalle_observacion" name="detalle_observacion" rows="4"
                        placeholder="Descripción de la observación..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Plazo de Entrega</label>
                        <select id="plazo_entrega" name="plazo_entrega">
                            <option value="">Seleccione...</option>
                            <option value="dentro_plazo">Dentro de Plazo</option>
                            <option value="fuera_plazo">Fuera de Plazo</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Usa Validador</label>
                        <select id="usa_validador" name="usa_validador">
                            <option value="">Seleccione...</option>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                            <option value="n/a">N/A</option>
                        </select>
                    </div>
                </div>

                <div id="respuestaContainer">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Respuesta del Establecimiento</label>
                    <textarea id="respuesta_establecimiento" name="respuesta_establecimiento" rows="3"
                        placeholder="Respuesta recibida del establecimiento..."></textarea>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="btn btn-primary flex-1">Guardar</button>
                    <button type="button" onclick="closeModal('modalObservation')"
                        class="btn btn-secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Importar -->
<div id="modalImport" class="modal-overlay hidden">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Importar Observaciones</h3>
                <p class="text-sm text-slate-500">Carga masiva de observaciones desde archivo Excel (XLSX)</p>
            </div>
            <button onclick="closeModal('modalImport')" class="btn-secondary px-3 py-2" type="button">✕</button>
        </div>
        <div class="modal-body">
            <!-- Paso 1: Subir archivo -->
            <div id="importStep1">
                <div class="text-center p-6 border-2 border-dashed border-slate-300 rounded-xl mb-4">
                    <div class="text-4xl mb-2">�</div>
                    <p class="text-slate-600 mb-4">Seleccione un archivo Excel (.xlsx) o CSV con las observaciones</p>
                    <input type="file" id="csvFile" accept=".xlsx,.xls,.csv" class="hidden" onchange="previewImport()">
                    <button onclick="document.getElementById('csvFile').click()" class="btn btn-primary">
                        📁 Seleccionar Archivo Excel
                    </button>
                </div>

                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl">
                    <div>
                        <p class="font-semibold text-slate-700">¿No tiene la plantilla?</p>
                        <p class="text-sm text-slate-500">Descargue la plantilla Excel (.xlsx) con ejemplos</p>
                    </div>
                    <a href="api/import_template.php" class="btn btn-secondary">
                        📥 Descargar Plantilla Excel
                    </a>
                </div>
            </div>

            <!-- Paso 2: Preview -->
            <div id="importStep2" class="hidden">
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-slate-700">Resumen de importación:</span>
                        <button onclick="resetImport()" class="text-sm text-sky-600 hover:underline">←
                            Volver</button>
                    </div>
                    <div id="importSummary" class="grid grid-cols-3 gap-4 text-center">
                        <div class="p-3 bg-slate-100 rounded-xl">
                            <div id="totalRows" class="text-2xl font-bold text-slate-800">0</div>
                            <div class="text-xs text-slate-500">Total filas</div>
                        </div>
                        <div class="p-3 bg-emerald-100 rounded-xl">
                            <div id="validRows" class="text-2xl font-bold text-emerald-600">0</div>
                            <div class="text-xs text-emerald-600">Válidas</div>
                        </div>
                        <div class="p-3 bg-rose-100 rounded-xl">
                            <div id="errorRows" class="text-2xl font-bold text-rose-600">0</div>
                            <div class="text-xs text-rose-600">Con errores</div>
                        </div>
                    </div>
                </div>

                <!-- Errores -->
                <div id="importErrors" class="hidden mb-4 max-h-32 overflow-y-auto">
                    <p class="text-sm font-semibold text-rose-600 mb-2">Errores encontrados:</p>
                    <ul id="errorList" class="text-xs text-rose-600 space-y-1"></ul>
                </div>

                <!-- Preview de datos -->
                <div id="importPreview" class="mb-4 max-h-48 overflow-y-auto">
                    <p class="text-sm font-semibold text-slate-700 mb-2">Vista previa:</p>
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="bg-slate-100">
                                <th class="p-2 text-left">Mes</th>
                                <th class="p-2 text-left">Establecimiento</th>
                                <th class="p-2 text-left">Tipo</th>
                                <th class="p-2 text-left">Serie</th>
                                <th class="p-2 text-left">REM</th>
                                <th class="p-2 text-left">Plazo</th>
                                <th class="p-2 text-left">Validador</th>
                                <th class="p-2 text-left">Detalle</th>
                            </tr>
                        </thead>
                        <tbody id="previewBody"></tbody>
                    </table>
                </div>

                <div class="flex gap-3">
                    <button onclick="confirmImport()" class="btn btn-primary flex-1" id="confirmImportBtn">
                        ✅ Confirmar Importación
                    </button>
                    <button onclick="closeModal('modalImport')" class="btn btn-secondary">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Detalles -->
<div id="modalDetails" class="modal-overlay hidden">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <div>
                <h3 class="text-xl font-bold text-slate-800">📋 Detalle de Observación</h3>
                <p class="text-sm text-slate-500">Resumen completo del registro</p>
            </div>
            <button onclick="closeModal('modalDetails')" class="btn-secondary px-3 py-2" type="button">✕</button>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
            <!-- Header con estado -->
            <div class="flex items-center justify-between mb-6 p-4 rounded-xl bg-slate-50">
                <div>
                    <h4 id="detailEstablecimiento" class="text-lg font-bold text-slate-800">-</h4>
                    <p id="detailComuna" class="text-sm text-slate-500">-</p>
                    <p id="detailCodigoEst" class="text-xs text-slate-400 mt-1">-</p>
                </div>
                <span id="detailBadge" class="badge">-</span>
            </div>

            <!-- Grid de información -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div class="p-4 rounded-xl bg-sky-50">
                    <div class="text-xs text-sky-600 uppercase font-bold mb-1">📅 Mes / Año</div>
                    <div id="detailMesAnio" class="font-semibold text-slate-800">-</div>
                </div>
                <div class="p-4 rounded-xl bg-violet-50">
                    <div class="text-xs text-violet-600 uppercase font-bold mb-1">📄 Referencia</div>
                    <div id="detailReferencia" class="font-semibold text-slate-800">-</div>
                </div>
                <div class="p-4 rounded-xl bg-amber-50">
                    <div class="text-xs text-amber-600 uppercase font-bold mb-1">⚠️ Tipo de Error</div>
                    <div id="detailTipoError" class="font-semibold text-slate-800">-</div>
                </div>
                <div class="p-4 rounded-xl bg-emerald-50">
                    <div class="text-xs text-emerald-600 uppercase font-bold mb-1">📆 Plazo Entrega</div>
                    <div id="detailPlazo" class="font-semibold text-slate-800">-</div>
                </div>
                <div class="p-4 rounded-xl bg-teal-50">
                    <div class="text-xs text-teal-600 uppercase font-bold mb-1">✅ Usa Validador</div>
                    <div id="detailValidador" class="font-semibold text-slate-800">-</div>
                </div>
            </div>

            <!-- Detalle de la observación -->
            <div class="mb-6">
                <div class="text-sm font-bold text-slate-700 mb-2">📝 Detalle de la Observación</div>
                <div id="detailObservacion" class="p-4 bg-slate-100 rounded-xl text-sm text-slate-700 min-h-[80px]">-
                </div>
            </div>

            <!-- Respuesta (si existe) -->
            <div id="detailRespuestaSection" class="mb-6 hidden">
                <div class="text-sm font-bold text-slate-700 mb-2">💬 Respuesta / Justificación</div>
                <div id="detailRespuesta" class="p-4 bg-emerald-50 rounded-xl text-sm text-slate-700 min-h-[60px]">-
                </div>
            </div>

            <!-- Clasificación y Detalle Error (solo visibles si el supervisor los completó) -->
            <div id="detailClasificacionSection" class="mb-6 hidden">
                <div class="text-sm font-bold text-slate-700 mb-2">📋 Clasificación de Respuesta</div>
                <div id="detailClasificacion" class="p-4 bg-sky-50 rounded-xl text-sm text-slate-700">-</div>
            </div>
            <div id="detailDetalleErrorSection" class="mb-6 hidden">
                <div class="text-sm font-bold text-slate-700 mb-2">🔍 Detalle Error</div>
                <div id="detailDetalleError" class="p-4 bg-sky-50 rounded-xl text-sm text-slate-700">-</div>
            </div>

            <!-- Info de registro -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-xl border border-slate-200">
                <div>
                    <div class="text-xs text-slate-400 uppercase">Registrado por</div>
                    <div id="detailRegistradoPor" class="font-semibold text-slate-700">-</div>
                    <div id="detailFechaRegistro" class="text-xs text-slate-400">-</div>
                    <div id="detailFechaActualizacion" class="text-xs text-slate-400 mt-1">-</div>
                </div>
                <div id="detailSupervisorInfo" class="hidden">
                    <div class="text-xs text-slate-400 uppercase">Supervisado por</div>
                    <div id="detailSupervisadoPor" class="font-semibold text-slate-700">-</div>
                    <div id="detailFechaSupervision" class="text-xs text-slate-400">-</div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="mt-4 flex flex-wrap gap-2">
                <span id="detailId" class="text-xs text-slate-400">ID: -</span>
            </div>
        </div>
    </div>
</div>

<script>
    // Variables para importación
    let importPreviewData = null;

    // Filtrar tabla
    function filterTable() {
        const search = document.getElementById('searchInput').value.toLowerCase();
        const estado = document.getElementById('filterEstado').value;
        const mes = document.getElementById('filterMes').value;
        const rows = document.querySelectorAll('#observationsTable tbody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const rowEstado = row.dataset.estado;
            const rowMes = row.dataset.mes;

            const matchSearch = text.includes(search);
            const matchEstado = !estado || rowEstado === estado;
            const matchMes = !mes || rowMes === mes;

            row.style.display = (matchSearch && matchEstado && matchMes) ? '' : 'none';
        });
    }

    // Cargar código del establecimiento seleccionado
    function loadEstablecimientoCodigo() {
        const select = document.getElementById('establecimiento_id');
        const selectedOption = select.options[select.selectedIndex];
        const codigo = selectedOption.getAttribute('data-codigo');

        if (codigo) {
            document.getElementById('codigo_establecimiento').value = codigo;
        } else {
            document.getElementById('codigo_establecimiento').value = '';
        }
    }

    // Datos de hojas REM por serie (generado desde PHP)
    const hojasPorSerie = <?php echo json_encode($HOJAS_POR_SERIE); ?>;

    // Cargar hojas REM según la serie seleccionada
    function loadHojasREM() {
        const serieSelect = document.getElementById('codigo_serie');
        const hojaSelect = document.getElementById('codigo_hoja');
        const serieSeleccionada = serieSelect.value;
        const tipoError = document.getElementById('tipo_error').value;

        // Limpiar opciones actuales
        hojaSelect.innerHTML = '';

        // Si es S/OBSERVACION, ocultar el campo de hoja
        if (tipoError === 'S/OBSERVACION') {
            document.getElementById('hojaRemContainer').style.display = 'none';
            hojaSelect.value = '';
            return;
        }

        // Mostrar el campo de hoja para otros tipos
        document.getElementById('hojaRemContainer').style.display = '';

        if (!serieSeleccionada) {
            hojaSelect.innerHTML = '<option value="">Primero seleccione una Serie</option>';
            hojaSelect.disabled = true;
            return;
        }

        // Obtener hojas para la serie seleccionada
        const hojas = hojasPorSerie[serieSeleccionada] || [];

        if (hojas.length > 0) {
            hojaSelect.innerHTML = '<option value="">Seleccione...</option>';
            hojas.forEach(hoja => {
                const option = document.createElement('option');
                option.value = hoja.codigo;
                option.textContent = hoja.nombre;
                hojaSelect.appendChild(option);
            });
            hojaSelect.disabled = false;
        } else {
            hojaSelect.innerHTML = '<option value="">No hay hojas disponibles</option>';
            hojaSelect.disabled = true;
        }
    }

    // Manejar cambio de tipo de error
    function handleTipoChange() {
        const tipoError = document.getElementById('tipo_error').value;
        const hojaContainer = document.getElementById('hojaRemContainer');
        const respuestaContainer = document.getElementById('respuestaContainer');
        const detalleObs = document.getElementById('detalle_observacion');

        if (tipoError === 'S/OBSERVACION') {
            // Ocultar hoja REM
            hojaContainer.style.display = 'none';
            document.getElementById('codigo_hoja').value = '';
            
            // Ocultar respuesta del establecimiento
            respuestaContainer.style.display = 'none';
            document.getElementById('respuesta_establecimiento').value = '';
        } else {
            // Mostrar hoja REM
            hojaContainer.style.display = '';
            
            // Mostrar respuesta del establecimiento
            respuestaContainer.style.display = '';
            
            // Recargar hojas si hay serie seleccionada
            loadHojasREM();
        }
    }

    // Abrir modal para crear
    function openCreateModal() {
        document.getElementById('obsId').value = '';
        document.getElementById('modalTitle').textContent = 'Nueva Observación';
        document.getElementById('formObservation').reset();
        // Resetear campo de código establecimiento
        document.getElementById('codigo_establecimiento').value = '';
        // Resetear hojas REM
        const hojaSelect = document.getElementById('codigo_hoja');
        hojaSelect.innerHTML = '<option value="">Primero seleccione una Serie</option>';
        hojaSelect.disabled = true;
        // Mostrar todos los campos
        document.getElementById('hojaRemContainer').style.display = '';
        document.getElementById('respuestaContainer').style.display = '';
        openModal('modalObservation');
    }

    // Abrir modal de importación
    function openImportModal() {
        resetImport();
        openModal('modalImport');
    }

    // Resetear estado de importación
    function resetImport() {
        document.getElementById('importStep1').classList.remove('hidden');
        document.getElementById('importStep2').classList.add('hidden');
        document.getElementById('csvFile').value = '';
        importPreviewData = null;
    }

    // Preview de importación
    async function previewImport() {
        const fileInput = document.getElementById('csvFile');
        if (!fileInput.files || fileInput.files.length === 0) return;

        const file = fileInput.files[0];
        const formData = new FormData();
        formData.append('csv_file', file);
        formData.append('preview', '1');
        formData.append('year', <?php echo $currentYear; ?>);

        try {
            showLoading();

            const response = await fetch('api/import.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            hideLoading();

            if (data.success) {
                importPreviewData = data.data;
                showImportPreview(data.data);
            } else {
                showError(data.message || 'Error al procesar archivo');
            }
        } catch (error) {
            hideLoading();
            showError('Error al procesar archivo: ' + error.message);
        }
    }

    // Mostrar preview
    function showImportPreview(data) {
        document.getElementById('importStep1').classList.add('hidden');
        document.getElementById('importStep2').classList.remove('hidden');

        // Actualizar contadores
        document.getElementById('totalRows').textContent = data.total;
        document.getElementById('validRows').textContent = data.valid;
        document.getElementById('errorRows').textContent = data.errors.length;

        // Mostrar errores si hay
        const errorsDiv = document.getElementById('importErrors');
        const errorList = document.getElementById('errorList');
        if (data.errors.length > 0) {
            errorsDiv.classList.remove('hidden');
            errorList.innerHTML = data.errors.map(e =>
                `<li>Fila ${e.row}: ${e.message}</li>`
            ).join('');
        } else {
            errorsDiv.classList.add('hidden');
        }

        // Mostrar preview (primeros 5)
        const previewBody = document.getElementById('previewBody');
        const previewItems = data.preview.slice(0, 5);
        previewBody.innerHTML = previewItems.map(item => `
            <tr class="border-b border-slate-100">
                <td class="p-2">${item.mes}</td>
                <td class="p-2">${item.establecimiento_nombre}</td>
                <td class="p-2">${item.tipo_error}</td>
                <td class="p-2">${item.codigo_serie || '-'}</td>
                <td class="p-2">${item.codigo_hoja || '-'}</td>
                <td class="p-2">${item.plazo_entrega || '-'}</td>
                <td class="p-2">${item.usa_validador || '-'}</td>
                <td class="p-2">${item.detalle_observacion ? item.detalle_observacion.substring(0, 40) + (item.detalle_observacion.length > 40 ? '...' : '') : '-'}</td>
            </tr>
        `).join('');

        if (data.preview.length > 5) {
            previewBody.innerHTML += `
                <tr class="border-b border-slate-100">
                    <td colspan="8" class="p-2 text-center text-slate-400">
                        ... y ${data.preview.length - 5} más
                    </td>
                </tr>
            `;
        }

        // Deshabilitar botón si no hay registros válidos
        const confirmBtn = document.getElementById('confirmImportBtn');
        confirmBtn.disabled = data.valid === 0;
        confirmBtn.classList.toggle('opacity-50', data.valid === 0);
    }

    // Confirmar importación
    async function confirmImport() {
        if (!importPreviewData || importPreviewData.valid === 0) {
            showError('No hay registros válidos para importar');
            return;
        }

        const fileInput = document.getElementById('csvFile');
        if (!fileInput.files || fileInput.files.length === 0) {
            showError('Por favor seleccione el archivo nuevamente');
            resetImport();
            return;
        }

        const file = fileInput.files[0];
        const formData = new FormData();
        formData.append('csv_file', file);
        formData.append('confirm', '1');
        formData.append('year', <?php echo $currentYear; ?>);

        try {
            showLoading();

            const response = await fetch('api/import.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            hideLoading();

            if (data.success) {
                showSuccess(`Se importaron ${data.imported} observaciones correctamente`);
                closeModal('modalImport');
                setTimeout(() => location.reload(), 1500);
            } else {
                showError(data.message || 'Error al importar');
            }
        } catch (error) {
            hideLoading();
            showError('Error al importar: ' + error.message);
        }
    }

    // Editar observación
    async function editObservation(id) {
        try {
            showLoading();
            const response = await fetchAPI(`observations.php?id=${id}`);

            if (response.success) {
                const obs = response.data;
                document.getElementById('obsId').value = obs.id;
                document.getElementById('mes').value = obs.mes;
                document.getElementById('establecimiento_id').value = obs.establecimiento_id;

                // Cargar código del establecimiento
                loadEstablecimientoCodigo();

                // Establecer el tipo primero para manejar la visibilidad
                document.getElementById('tipo_error').value = obs.tipo_error;
                handleTipoChange();

                // Establecer la serie
                document.getElementById('codigo_serie').value = obs.codigo_serie;

                // Cargar las hojas REM para esa serie (si no es S/OBSERVACION)
                if (obs.tipo_error !== 'S/OBSERVACION') {
                    loadHojasREM();
                    // Ahora establecer la hoja seleccionada
                    document.getElementById('codigo_hoja').value = obs.codigo_hoja;
                }

                document.getElementById('detalle_observacion').value = obs.detalle_observacion;
                document.getElementById('plazo_entrega').value = obs.plazo_entrega;
                
                // Manejar usa_validador - si es 'no' mostrar como 'no' (N/A se guarda como 'no')
                document.getElementById('usa_validador').value = obs.usa_validador || '';
                
                document.getElementById('respuesta_establecimiento').value = obs.respuesta_establecimiento || '';

                document.getElementById('modalTitle').textContent = 'Editar Observación';
                openModal('modalObservation');
            }

            hideLoading();
        } catch (error) {
            hideLoading();
            showError('Error al cargar la observación: ' + error.message);
        }
    }

    // Guardar observación
    async function saveObservation(event) {
        event.preventDefault();

        if (!validateForm('formObservation')) return;

        const obsId = document.getElementById('obsId').value;
        const tipoError = document.getElementById('tipo_error').value;
        let usaValidador = document.getElementById('usa_validador').value;
        
        // Convertir 'n/a' a 'no' para guardar en BD
        if (usaValidador === 'n/a') {
            usaValidador = 'no';
        }

        const formData = {
            mes: document.getElementById('mes').value,
            establecimiento_id: parseInt(document.getElementById('establecimiento_id').value),
            codigo_serie: document.getElementById('codigo_serie').value,
            codigo_hoja: tipoError === 'S/OBSERVACION' ? '' : document.getElementById('codigo_hoja').value,
            tipo_error: tipoError,
            detalle_observacion: document.getElementById('detalle_observacion').value,
            plazo_entrega: document.getElementById('plazo_entrega').value,
            usa_validador: usaValidador,
            respuesta_establecimiento: tipoError === 'S/OBSERVACION' ? '' : document.getElementById('respuesta_establecimiento').value
        };

        try {
            showLoading();

            let response;
            if (obsId) {
                // Actualizar
                response = await fetchAPI(`observations.php?id=${obsId}`, {
                    method: 'PUT',
                    body: JSON.stringify(formData)
                });
            } else {
                // Crear
                response = await fetchAPI('observations.php', {
                    method: 'POST',
                    body: JSON.stringify(formData)
                });
            }

            hideLoading();

            if (response.success) {
                showSuccess(obsId ? 'Observación actualizada correctamente' : 'Observación creada correctamente');
                closeModal('modalObservation');
                setTimeout(() => location.reload(), 1500);
            }
        } catch (error) {
            hideLoading();
            showError(error.message || 'Error al guardar la observación');
        }
    }

    // Ver detalle de observación
    async function viewObservation(id) {
        try {
            showLoading();
            const response = await fetchAPI('observations.php?id=' + id);

            if (response.success) {
                const obs = response.data;

                // Poblar modal con datos
                document.getElementById('detailEstablecimiento').textContent = obs.nombre_corto || obs.nombre || '-';
                document.getElementById('detailComuna').textContent = obs.comuna || '-';
                document.getElementById('detailCodigoEst').textContent = obs.codigo_establecimiento ? 'Código: ' + obs.codigo_establecimiento : '-';

                // Badge de estado
                const badge = document.getElementById('detailBadge');
                badge.textContent = obs.estado_actual ? obs.estado_actual.charAt(0).toUpperCase() + obs.estado_actual.slice(1) : '-';
                badge.className = 'badge badge-' + (obs.estado_actual || 'pendiente');

                // Información principal
                document.getElementById('detailMesAnio').textContent = (obs.mes || '-') + ' ' + (obs.anio || '');
                document.getElementById('detailReferencia').textContent = 'Serie ' + (obs.codigo_serie || '-') + ' / Hoja ' + (obs.codigo_hoja || '-');
                document.getElementById('detailTipoError').textContent = obs.tipo_error || '-';
                document.getElementById('detailPlazo').textContent = obs.plazo_entrega || 'No especificado';

                // Detalle de observación
                document.getElementById('detailObservacion').textContent = obs.detalle_observacion || 'Sin detalle registrado';

                // Respuesta/Justificación
                const respuestaSection = document.getElementById('detailRespuestaSection');
                if (obs.respuesta) {
                    document.getElementById('detailRespuesta').textContent = obs.respuesta;
                    respuestaSection.classList.remove('hidden');
                } else {
                    respuestaSection.classList.add('hidden');
                }

                // Clasificación y Detalle Error (supervisor)
                const clasifSection = document.getElementById('detailClasificacionSection');
                if (obs.clasificacion) {
                    document.getElementById('detailClasificacion').textContent = obs.clasificacion;
                    clasifSection.classList.remove('hidden');
                } else {
                    clasifSection.classList.add('hidden');
                }
                const detErrorSection = document.getElementById('detailDetalleErrorSection');
                if (obs.detalle_error) {
                    document.getElementById('detailDetalleError').textContent = obs.detalle_error;
                    detErrorSection.classList.remove('hidden');
                } else {
                    detErrorSection.classList.add('hidden');
                }

                // Info de registro
                document.getElementById('detailRegistradoPor').textContent = obs.nombre_registro || '-';
                document.getElementById('detailFechaRegistro').textContent = obs.fecha_registro ? formatDate(obs.fecha_registro) : '-';
                document.getElementById('detailFechaActualizacion').textContent = obs.fecha_actualizacion ? 'Última modificación: ' + formatDate(obs.fecha_actualizacion) : '';

                // Info de supervisor
                const supervisorInfo = document.getElementById('detailSupervisorInfo');
                if (obs.nombre_supervisor) {
                    document.getElementById('detailSupervisadoPor').textContent = obs.nombre_supervisor;
                    document.getElementById('detailFechaSupervision').textContent = obs.fecha_supervision ? formatDate(obs.fecha_supervision) : '-';
                    supervisorInfo.classList.remove('hidden');
                } else {
                    supervisorInfo.classList.add('hidden');
                }

                // Validador
                const validadorEl = document.getElementById('detailValidador');
                if (obs.tipo_error === 'S/OBSERVACION') {
                    validadorEl.textContent = 'N/A';
                } else if (obs.usa_validador && obs.usa_validador !== 'no') {
                    validadorEl.textContent = 'Sí';
                } else {
                    validadorEl.textContent = 'No';
                }

                document.getElementById('detailId').textContent = 'ID: ' + obs.id;

                // Abrir modal
                openModal('modalDetails');
            }

            hideLoading();
        } catch (error) {
            hideLoading();
            showError('Error al cargar detalles: ' + error.message);
        }
    }

    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-CL');
    }
</script>