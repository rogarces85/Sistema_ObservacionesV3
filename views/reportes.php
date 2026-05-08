<?php
/**
 * Vista de Reportes Mejorada
 * Dashboard de reportes interactivos con gráficos, tablas y filtros dinámicos
 */

$currentYear = $_SESSION['year'] ?? date('Y');
$userRole = $_SESSION['rol'];
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Reportes y Estadísticas</h2>
            <p class="text-slate-600">Análisis detallado de observaciones REM — Año <span id="reportYearLabel"><?php echo $currentYear; ?></span></p>
        </div>
        <div class="flex gap-3 items-center flex-wrap">
            <label class="text-sm font-semibold text-slate-700">Año:</label>
            <select id="reportYearSelector" class="form-select w-28" onchange="changeReportYear(this.value)">
                <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $currentYear ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
            <button onclick="exportDetalladoPDF()" class="btn btn-secondary text-sm" title="Reporte jerárquico para impresión">
                📄 PDF Detallado
            </button>
            <button onclick="exportData('excel')" class="btn btn-primary text-sm">
                📊 Excel General
            </button>
        </div>
    </div>

    <!-- KPIs Globales -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4" id="kpiContainer">
        <div class="card p-4 text-center">
            <div class="text-3xl font-bold text-slate-800" id="kpiTotal">—</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Total Observaciones</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-3xl font-bold text-rose-600" id="kpiErrores">—</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Errores</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-3xl font-bold text-amber-600" id="kpiFueraPlazo">—</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Fuera de Plazo</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-3xl font-bold text-sky-600" id="kpiConValidador">—</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Usan Validador</div>
        </div>
        <div class="card p-4 text-center">
            <div class="text-3xl font-bold text-emerald-600" id="kpiSeries">—</div>
            <div class="text-xs text-slate-500 uppercase tracking-wide mt-1">Series REM</div>
        </div>
    </div>

    <!-- Tabs de navegación -->
    <div class="card p-0 overflow-hidden">
        <div class="report-tabs" id="reportTabs">
            <button class="report-tab active" data-tab="general" onclick="switchTab('general')">General</button>
            <button class="report-tab" data-tab="errores" onclick="switchTab('errores')">Errores</button>
            <button class="report-tab" data-tab="fuera-plazo" onclick="switchTab('fuera-plazo')">Fuera de Plazo</button>
            <button class="report-tab" data-tab="validador" onclick="switchTab('validador')">Validador</button>
            <button class="report-tab" data-tab="serie-hoja" onclick="switchTab('serie-hoja')">Serie / Hoja</button>
            <button class="report-tab" data-tab="detallado" onclick="switchTab('detallado')">PDF Detallado</button>
        </div>

        <!-- Tab: General -->
        <div class="report-tab-content active" id="tab-general">
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <!-- Por Mes -->
                    <div class="card p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">📅 Por Mes</h3>
                            <button onclick="exportSpecificReport('errores_mes')" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Exportar ↓</button>
                        </div>
                        <div class="relative" style="height: 280px;"><canvas id="chartMes"></canvas></div>
                        <div class="mt-4 overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Mes</th><th class="pb-2 font-medium text-right">Total</th><th class="pb-2 font-medium text-right">%</th></tr></thead><tbody id="tableMes"></tbody></table></div>
                    </div>
                    <!-- Por Comuna -->
                    <div class="card p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">📍 Por Comuna</h3>
                            <button onclick="exportSpecificReport('errores_comuna')" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Exportar ↓</button>
                        </div>
                        <div class="relative" style="height: 280px;"><canvas id="chartComuna"></canvas></div>
                        <div class="mt-4 overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Comuna</th><th class="pb-2 font-medium text-right">Total</th><th class="pb-2 font-medium text-right">%</th></tr></thead><tbody id="tableComuna"></tbody></table></div>
                    </div>
                </div>
                <!-- Por Establecimiento -->
                <div class="card p-6 border border-slate-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-slate-800">🏥 Por Establecimiento</h3>
                        <button onclick="exportSpecificReport('errores_establecimiento')" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Exportar ↓</button>
                    </div>
                    <div class="relative" style="height: 340px;"><canvas id="chartEstablecimiento"></canvas></div>
                    <div class="mt-4 overflow-x-auto max-h-60"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Establecimiento</th><th class="pb-2 font-medium text-right">Total</th><th class="pb-2 font-medium text-right">%</th></tr></thead><tbody id="tableEstablecimiento"></tbody></table></div>
                </div>
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <!-- Por Serie REM -->
                    <div class="card p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">📄 Por Serie</h3>
                        </div>
                        <div class="relative" style="height: 260px;"><canvas id="chartSerie"></canvas></div>
                        <div class="mt-4 overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Serie</th><th class="pb-2 font-medium text-right">Total</th></tr></thead><tbody id="tableSerie"></tbody></table></div>
                    </div>
                    <!-- Por Plazo -->
                    <div class="card p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">⏱️ Por Plazo</h3>
                        </div>
                        <div class="relative" style="height: 260px;"><canvas id="chartPlazo"></canvas></div>
                        <div class="mt-4 overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Plazo</th><th class="pb-2 font-medium text-right">Total</th></tr></thead><tbody id="tablePlazo"></tbody></table></div>
                    </div>
                    <!-- Por Validador -->
                    <div class="card p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">✅ Por Validador</h3>
                        </div>
                        <div class="relative" style="height: 260px;"><canvas id="chartValidador"></canvas></div>
                        <div class="mt-4 overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Validador</th><th class="pb-2 font-medium text-right">Total</th></tr></thead><tbody id="tableValidador"></tbody></table></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: Errores -->
        <div class="report-tab-content" id="tab-errores">
            <div class="p-6 space-y-6">
                <div class="bg-rose-50 border border-rose-200 rounded-lg p-4 mb-2">
                    <p class="text-sm text-rose-700 font-medium">Reportes filtrados por tipo de error: <strong>ERROR</strong></p>
                </div>
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="card p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">📅 Errores por Mes</h3>
                            <button onclick="exportSpecificReport('errores_mes')" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Exportar Excel ↓</button>
                        </div>
                        <div class="relative" style="height: 280px;"><canvas id="chartErroresMes"></canvas></div>
                        <div class="mt-4 overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Mes</th><th class="pb-2 font-medium text-right">Total</th><th class="pb-2 font-medium text-right">%</th></tr></thead><tbody id="tableErroresMes"></tbody></table></div>
                    </div>
                    <div class="card p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">📍 Errores por Comuna</h3>
                            <button onclick="exportSpecificReport('errores_comuna')" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Exportar Excel ↓</button>
                        </div>
                        <div class="relative" style="height: 280px;"><canvas id="chartErroresComuna"></canvas></div>
                        <div class="mt-4 overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Comuna</th><th class="pb-2 font-medium text-right">Total</th><th class="pb-2 font-medium text-right">%</th></tr></thead><tbody id="tableErroresComuna"></tbody></table></div>
                    </div>
                </div>
                <div class="card p-6 border border-slate-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-slate-800">🏥 Errores por Establecimiento</h3>
                        <button onclick="exportSpecificReport('errores_establecimiento')" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Exportar Excel ↓</button>
                    </div>
                    <div class="relative" style="height: 340px;"><canvas id="chartErroresEstablecimiento"></canvas></div>
                    <div class="mt-4 overflow-x-auto max-h-60"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Establecimiento</th><th class="pb-2 font-medium text-right">Total</th><th class="pb-2 font-medium text-right">%</th></tr></thead><tbody id="tableErroresEstablecimiento"></tbody></table></div>
                </div>
            </div>
        </div>

        <!-- Tab: Fuera de Plazo -->
        <div class="report-tab-content" id="tab-fuera-plazo">
            <div class="p-6 space-y-6">
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-2">
                    <p class="text-sm text-amber-700 font-medium">Reportes filtrados por envíos <strong>fuera de plazo</strong></p>
                </div>
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="card p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">📅 Fuera de Plazo por Mes</h3>
                            <button onclick="exportSpecificReport('fuera_plazo_mes')" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Exportar Excel ↓</button>
                        </div>
                        <div class="relative" style="height: 280px;"><canvas id="chartFueraPlazoMes"></canvas></div>
                        <div class="mt-4 overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Mes</th><th class="pb-2 font-medium text-right">Total</th><th class="pb-2 font-medium text-right">%</th></tr></thead><tbody id="tableFueraPlazoMes"></tbody></table></div>
                    </div>
                    <div class="card p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">📍 Fuera de Plazo por Comuna</h3>
                            <button onclick="exportSpecificReport('fuera_plazo_comuna')" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Exportar Excel ↓</button>
                        </div>
                        <div class="relative" style="height: 280px;"><canvas id="chartFueraPlazoComuna"></canvas></div>
                        <div class="mt-4 overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Comuna</th><th class="pb-2 font-medium text-right">Total</th><th class="pb-2 font-medium text-right">%</th></tr></thead><tbody id="tableFueraPlazoComuna"></tbody></table></div>
                    </div>
                </div>
                <div class="card p-6 border border-slate-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-slate-800">🏥 Fuera de Plazo por Establecimiento</h3>
                        <button onclick="exportSpecificReport('fuera_plazo_establecimiento')" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Exportar Excel ↓</button>
                    </div>
                    <div class="relative" style="height: 340px;"><canvas id="chartFueraPlazoEstablecimiento"></canvas></div>
                    <div class="mt-4 overflow-x-auto max-h-60"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Establecimiento</th><th class="pb-2 font-medium text-right">Total</th><th class="pb-2 font-medium text-right">%</th></tr></thead><tbody id="tableFueraPlazoEstablecimiento"></tbody></table></div>
                </div>
            </div>
        </div>

        <!-- Tab: Validador -->
        <div class="report-tab-content" id="tab-validador">
            <div class="p-6 space-y-6">
                <div class="bg-sky-50 border border-sky-200 rounded-lg p-4 mb-2">
                    <p class="text-sm text-sky-700 font-medium">Reportes de uso del <strong>validador REM</strong> (usa_validador = 'si')</p>
                </div>
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <div class="card p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">📅 Uso Validador por Mes</h3>
                            <button onclick="exportSpecificReport('validador_mes')" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Exportar Excel ↓</button>
                        </div>
                        <div class="relative" style="height: 280px;"><canvas id="chartValidadorMes"></canvas></div>
                        <div class="mt-4 overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Mes</th><th class="pb-2 font-medium text-right">Total</th><th class="pb-2 font-medium text-right">%</th></tr></thead><tbody id="tableValidadorMes"></tbody></table></div>
                    </div>
                    <div class="card p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">📍 Uso Validador por Comuna</h3>
                            <button onclick="exportSpecificReport('validador_comuna')" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Exportar Excel ↓</button>
                        </div>
                        <div class="relative" style="height: 280px;"><canvas id="chartValidadorComuna"></canvas></div>
                        <div class="mt-4 overflow-x-auto"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Comuna</th><th class="pb-2 font-medium text-right">Total</th><th class="pb-2 font-medium text-right">%</th></tr></thead><tbody id="tableValidadorComuna"></tbody></table></div>
                    </div>
                </div>
                <div class="card p-6 border border-slate-200">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-slate-800">🏥 Uso Validador por Establecimiento</h3>
                        <button onclick="exportSpecificReport('validador_establecimiento')" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Exportar Excel ↓</button>
                    </div>
                    <div class="relative" style="height: 340px;"><canvas id="chartValidadorEstablecimiento"></canvas></div>
                    <div class="mt-4 overflow-x-auto max-h-60"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Establecimiento</th><th class="pb-2 font-medium text-right">Total</th><th class="pb-2 font-medium text-right">%</th></tr></thead><tbody id="tableValidadorEstablecimiento"></tbody></table></div>
                </div>
            </div>
        </div>

        <!-- Tab: Serie / Hoja -->
        <div class="report-tab-content" id="tab-serie-hoja">
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                    <!-- Por Serie REM × Tipo Error -->
                    <div class="card p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">📄 Por Serie REM × Tipo Error</h3>
                            <button onclick="exportSpecificReport('serie_detalle')" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Exportar Excel ↓</button>
                        </div>
                        <div class="relative" style="height: 280px;"><canvas id="chartSerieDetalle"></canvas></div>
                        <div class="mt-4 overflow-x-auto max-h-64"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Serie</th><th class="pb-2 font-medium">Tipo Error</th><th class="pb-2 font-medium text-right">Cantidad</th></tr></thead><tbody id="tableSerieDetalle"></tbody></table></div>
                    </div>
                    <!-- Por Hoja REM -->
                    <div class="card p-6 border border-slate-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-slate-800">📋 Por Hoja REM</h3>
                            <button onclick="exportSpecificReport('hoja_detalle')" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Exportar Excel ↓</button>
                        </div>
                        <div class="relative" style="height: 280px;"><canvas id="chartHojaDetalle"></canvas></div>
                        <div class="mt-4 overflow-x-auto max-h-64"><table class="w-full text-sm"><thead><tr class="text-left text-slate-500 border-b"><th class="pb-2 font-medium">Hoja</th><th class="pb-2 font-medium">Tipo Error</th><th class="pb-2 font-medium text-right">Frecuencia</th></tr></thead><tbody id="tableHojaDetalle"></tbody></table></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab: PDF Detallado -->
        <div class="report-tab-content" id="tab-detallado">
            <div class="p-6">
                <div class="max-w-2xl mx-auto">
                    <div class="card p-8 border-2 border-slate-200">
                        <div class="text-center mb-6">
                            <div class="text-5xl mb-3">📄</div>
                            <h3 class="text-xl font-bold text-slate-800">Reporte Detallado para Impresión</h3>
                            <p class="text-sm text-slate-500 mt-1">Genera un PDF jerárquico agrupado por Comuna → Establecimiento → Mes, optimizado para impresión y firma física.</p>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Comuna</label>
                                    <select id="pdfComuna" class="form-select w-full" onchange="updatePdfEstablecimientos()">
                                        <option value="">Todas</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Establecimiento</label>
                                    <select id="pdfEstablecimiento" class="form-select w-full">
                                        <option value="">Todos</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Mes</label>
                                    <select id="pdfMes" class="form-select w-full">
                                        <option value="">Todos</option>
                                        <option>Enero</option><option>Febrero</option><option>Marzo</option>
                                        <option>Abril</option><option>Mayo</option><option>Junio</option>
                                        <option>Julio</option><option>Agosto</option><option>Septiembre</option>
                                        <option>Octubre</option><option>Noviembre</option><option>Diciembre</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
                                    <select id="pdfEstado" class="form-select w-full">
                                        <option value="">Todos</option>
                                        <option value="pendiente">Pendiente</option>
                                        <option value="aprobado">Aprobado</option>
                                        <option value="rechazado">Rechazado</option>
                                        <option value="error">Error</option>
                                        <option value="justificado">Justificado</option>
                                    </select>
                                </div>
                            </div>

                            <div class="bg-slate-50 rounded-lg p-4 text-xs text-slate-500 mt-4">
                                <p class="font-medium text-slate-700 mb-1">Estructura del PDF:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Agrupamiento: <strong>COMUNA → ESTABLECIMIENTO → MES</strong></li>
                                    <li>Columnas: Comuna, Establecimiento, Mes, Detalle, Detalle Error, Estado, Errores</li>
                                    <li>Código de colores por estado</li>
                                    <li>Formato horizontal (landscape) optimizado para impresión</li>
                                </ul>
                            </div>

                            <button onclick="exportDetalladoPDF()" class="btn btn-secondary w-full py-3 text-base font-semibold">
                                📄 Generar PDF Detallado
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    let charts = {};
    let currentYear = <?php echo $currentYear; ?>;
    let allData = {};
    let filterData = { comunas: [], establecimientos: [] };

    const PALETTE = [
        '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
        '#ec4899', '#14b8a6', '#f97316', '#06b6d4', '#84cc16',
        '#6366f1', '#d946ef', '#22c55e', '#eab308', '#3b82f6'
    ];

    const PLAZO_COLORS = { 'dentro_plazo': '#10b981', 'fuera_plazo': '#ef4444' };
    const VALIDADOR_COLORS = { 'si': '#0ea5e9', 'no': '#94a3b8' };

    document.addEventListener('DOMContentLoaded', function () {
        loadAllReports();
        loadFilters();
    });

    async function loadAllReports() {
        try {
            showLoadingAll();
            const response = await fetch(`api/reports.php?report=all&year=${currentYear}`);
            const result = await response.json();

            if (result.success) {
                allData = result.data;
                renderKPIs(result.data);
                renderGeneral(result.data);
                renderErrores(result.data);
                renderFueraPlazo(result.data);
                renderValidador(result.data);
                renderSerieHoja(result.data);
            } else {
                showError(result.message || 'Error al cargar reportes');
            }
        } catch (error) {
            console.error(error);
            showError('Error al cargar reportes');
        }
    }

    async function loadFilters() {
        try {
            const response = await fetch(`api/reports.php?report=filtros&year=${currentYear}`);
            const result = await response.json();
            if (result.success) {
                filterData = result.data;
                populatePdfFilters();
            }
        } catch (e) { console.error(e); }
    }

    function showLoadingAll() {
        ['kpiTotal','kpiErrores','kpiFueraPlazo','kpiConValidador','kpiSeries'].forEach(id => {
            document.getElementById(id).textContent = '—';
        });
    }

    function renderKPIs(data) {
        const total = (data.mes || []).reduce((sum, m) => sum + parseInt(m.total), 0);
        const erroresTotal = (data.errores_mes || []).reduce((sum, m) => sum + parseInt(m.total), 0);
        const fueraPlazo = (data.plazo || []).find(p => p.plazo_entrega === 'fuera_plazo');
        const conValidador = (data.validador || []).find(v => v.usa_validador === 'si');
        const seriesCount = (data.serie || []).length;

        document.getElementById('kpiTotal').textContent = total;
        document.getElementById('kpiErrores').textContent = erroresTotal;
        document.getElementById('kpiFueraPlazo').textContent = fueraPlazo ? fueraPlazo.total : 0;
        document.getElementById('kpiConValidador').textContent = conValidador ? conValidador.total : 0;
        document.getElementById('kpiSeries').textContent = seriesCount;
    }

    function renderTable(tbodyId, data, labelKey, totalGlobal) {
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-slate-400 py-3">Sin datos</td></tr>';
            return;
        }
        const total = totalGlobal || data.reduce((sum, r) => sum + parseInt(r.total), 0);
        data.forEach(row => {
            const val = parseInt(row.total);
            const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
            const label = row[labelKey] || 'Sin especificar';
            tbody.innerHTML += `<tr class="border-b border-slate-50 hover:bg-slate-50"><td class="py-2 text-slate-700">${escapeHtml(label)}</td><td class="py-2 text-right font-semibold text-slate-800">${val}</td><td class="py-2 text-right text-slate-500">${pct}%</td></tr>`;
        });
    }

    function renderTableMulti(tbodyId, data, keys, totalGlobal) {
        const tbody = document.getElementById(tbodyId);
        if (!tbody) return;
        tbody.innerHTML = '';
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-slate-400 py-3">Sin datos</td></tr>';
            return;
        }
        const total = totalGlobal || data.reduce((sum, r) => sum + parseInt(r.total), 0);
        data.forEach(row => {
            const val = parseInt(row.total);
            const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
            let cells = '';
            keys.forEach(k => { cells += `<td class="py-2 text-slate-700">${escapeHtml(row[k] || '—')}</td>`; });
            tbody.innerHTML += `<tr class="border-b border-slate-50 hover:bg-slate-50">${cells}<td class="py-2 text-right font-semibold text-slate-800">${val}</td><td class="py-2 text-right text-slate-500">${pct}%</td></tr>`;
        });
    }

    function destroyChart(name) { if (charts[name]) { charts[name].destroy(); delete charts[name]; } }

    // ====== GENERAL TAB ======
    function renderGeneral(data) {
        // Mes
        renderTable('tableMes', data.mes, 'mes');
        destroyChart('mes');
        charts.mes = new Chart(document.getElementById('chartMes'), {
            type: 'bar',
            data: { labels: data.mes.map(d => d.mes), datasets: [{ data: data.mes.map(d => parseInt(d.total)), backgroundColor: '#0ea5e9', borderRadius: 6, barThickness: 'flex', maxBarThickness: 32 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' } }, x: { grid: { display: false }, ticks: { font: { size: 11 } } } } }
        });

        // Comuna
        renderTable('tableComuna', data.comuna, 'nombre');
        destroyChart('comuna');
        charts.comuna = new Chart(document.getElementById('chartComuna'), {
            type: 'doughnut',
            data: { labels: data.comuna.map(d => d.nombre), datasets: [{ data: data.comuna.map(d => parseInt(d.total)), backgroundColor: PALETTE.slice(0, data.comuna.length), borderWidth: 2, borderColor: '#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '55%', plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });

        // Establecimiento
        const topEst = data.establecimiento.slice(0, 15);
        renderTable('tableEstablecimiento', data.establecimiento, 'nombre');
        destroyChart('establecimiento');
        charts.establecimiento = new Chart(document.getElementById('chartEstablecimiento'), {
            type: 'bar',
            data: { labels: topEst.map(d => d.nombre_corto || d.nombre), datasets: [{ data: topEst.map(d => parseInt(d.total)), backgroundColor: '#10b981', borderRadius: 6, barThickness: 'flex', maxBarThickness: 24 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' } }, y: { grid: { display: false }, ticks: { font: { size: 11 } } } } }
        });

        // Serie
        renderTable('tableSerie', data.serie, 'codigo_serie');
        destroyChart('serie');
        charts.serie = new Chart(document.getElementById('chartSerie'), {
            type: 'bar',
            data: { labels: data.serie.map(d => d.codigo_serie), datasets: [{ data: data.serie.map(d => parseInt(d.total)), backgroundColor: '#8b5cf6', borderRadius: 6, barThickness: 'flex', maxBarThickness: 32 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' } }, x: { grid: { display: false }, ticks: { font: { size: 11 } } } } }
        });

        // Plazo
        const plazoLabels = data.plazo.map(d => d.plazo_entrega === 'fuera_plazo' ? 'Fuera de Plazo' : 'Dentro de Plazo');
        renderTable('tablePlazo', data.plazo.map(d => ({...d, plazo_entrega: d.plazo_entrega === 'fuera_plazo' ? 'Fuera de Plazo' : 'Dentro de Plazo'})), 'plazo_entrega');
        destroyChart('plazo');
        charts.plazo = new Chart(document.getElementById('chartPlazo'), {
            type: 'doughnut',
            data: { labels: plazoLabels, datasets: [{ data: data.plazo.map(d => parseInt(d.total)), backgroundColor: data.plazo.map(d => PLAZO_COLORS[d.plazo_entrega] || '#94a3b8'), borderWidth: 2, borderColor: '#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '55%', plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });

        // Validador
        const valLabels = data.validador.map(d => d.usa_validador === 'si' ? 'Sí usa' : 'No usa');
        renderTable('tableValidador', data.validador.map(d => ({...d, usa_validador: d.usa_validador === 'si' ? 'Sí' : 'No'})), 'usa_validador');
        destroyChart('validador');
        charts.validador = new Chart(document.getElementById('chartValidador'), {
            type: 'doughnut',
            data: { labels: valLabels, datasets: [{ data: data.validador.map(d => parseInt(d.total)), backgroundColor: data.validador.map(d => VALIDADOR_COLORS[d.usa_validador] || '#94a3b8'), borderWidth: 2, borderColor: '#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '55%', plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });
    }

    // ====== ERRORES TAB ======
    function renderErrores(data) {
        renderTable('tableErroresMes', data.errores_mes, 'mes');
        destroyChart('erroresMes');
        charts.erroresMes = new Chart(document.getElementById('chartErroresMes'), {
            type: 'bar',
            data: { labels: data.errores_mes.map(d => d.mes), datasets: [{ data: data.errores_mes.map(d => parseInt(d.total)), backgroundColor: '#ef4444', borderRadius: 6, barThickness: 'flex', maxBarThickness: 32 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => `${ctx.parsed.y} errores` } } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' } }, x: { grid: { display: false }, ticks: { font: { size: 11 } } } } }
        });

        renderTable('tableErroresComuna', data.errores_comuna, 'nombre');
        destroyChart('erroresComuna');
        charts.erroresComuna = new Chart(document.getElementById('chartErroresComuna'), {
            type: 'doughnut',
            data: { labels: data.errores_comuna.map(d => d.nombre), datasets: [{ data: data.errores_comuna.map(d => parseInt(d.total)), backgroundColor: PALETTE.slice(0, data.errores_comuna.length), borderWidth: 2, borderColor: '#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '55%', plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });

        const topErrEst = data.errores_establecimiento.slice(0, 15);
        renderTable('tableErroresEstablecimiento', data.errores_establecimiento, 'nombre');
        destroyChart('erroresEstablecimiento');
        charts.erroresEstablecimiento = new Chart(document.getElementById('chartErroresEstablecimiento'), {
            type: 'bar',
            data: { labels: topErrEst.map(d => d.nombre_corto || d.nombre), datasets: [{ data: topErrEst.map(d => parseInt(d.total)), backgroundColor: '#ef4444', borderRadius: 6, barThickness: 'flex', maxBarThickness: 24 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' } }, y: { grid: { display: false }, ticks: { font: { size: 11 } } } } }
        });
    }

    // ====== FUERA DE PLAZO TAB ======
    function renderFueraPlazo(data) {
        renderTable('tableFueraPlazoMes', data.fuera_plazo_mes, 'mes');
        destroyChart('fueraPlazoMes');
        charts.fueraPlazoMes = new Chart(document.getElementById('chartFueraPlazoMes'), {
            type: 'bar',
            data: { labels: data.fuera_plazo_mes.map(d => d.mes), datasets: [{ data: data.fuera_plazo_mes.map(d => parseInt(d.total)), backgroundColor: '#f59e0b', borderRadius: 6, barThickness: 'flex', maxBarThickness: 32 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' } }, x: { grid: { display: false }, ticks: { font: { size: 11 } } } } }
        });

        renderTable('tableFueraPlazoComuna', data.fuera_plazo_comuna, 'nombre');
        destroyChart('fueraPlazoComuna');
        charts.fueraPlazoComuna = new Chart(document.getElementById('chartFueraPlazoComuna'), {
            type: 'doughnut',
            data: { labels: data.fuera_plazo_comuna.map(d => d.nombre), datasets: [{ data: data.fuera_plazo_comuna.map(d => parseInt(d.total)), backgroundColor: PALETTE.slice(0, data.fuera_plazo_comuna.length), borderWidth: 2, borderColor: '#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '55%', plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });

        const topFPEst = data.fuera_plazo_establecimiento.slice(0, 15);
        renderTable('tableFueraPlazoEstablecimiento', data.fuera_plazo_establecimiento, 'nombre');
        destroyChart('fueraPlazoEstablecimiento');
        charts.fueraPlazoEstablecimiento = new Chart(document.getElementById('chartFueraPlazoEstablecimiento'), {
            type: 'bar',
            data: { labels: topFPEst.map(d => d.nombre_corto || d.nombre), datasets: [{ data: topFPEst.map(d => parseInt(d.total)), backgroundColor: '#f59e0b', borderRadius: 6, barThickness: 'flex', maxBarThickness: 24 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' } }, y: { grid: { display: false }, ticks: { font: { size: 11 } } } } }
        });
    }

    // ====== VALIDADOR TAB ======
    function renderValidador(data) {
        renderTable('tableValidadorMes', data.validador_mes, 'mes');
        destroyChart('validadorMes');
        charts.validadorMes = new Chart(document.getElementById('chartValidadorMes'), {
            type: 'bar',
            data: { labels: data.validador_mes.map(d => d.mes), datasets: [{ data: data.validador_mes.map(d => parseInt(d.total)), backgroundColor: '#0ea5e9', borderRadius: 6, barThickness: 'flex', maxBarThickness: 32 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' } }, x: { grid: { display: false }, ticks: { font: { size: 11 } } } } }
        });

        renderTable('tableValidadorComuna', data.validador_comuna, 'nombre');
        destroyChart('validadorComuna');
        charts.validadorComuna = new Chart(document.getElementById('chartValidadorComuna'), {
            type: 'doughnut',
            data: { labels: data.validador_comuna.map(d => d.nombre), datasets: [{ data: data.validador_comuna.map(d => parseInt(d.total)), backgroundColor: PALETTE.slice(0, data.validador_comuna.length), borderWidth: 2, borderColor: '#fff' }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '55%', plugins: { legend: { position: 'right', labels: { boxWidth: 12, font: { size: 11 } } } } }
        });

        const valTopEst = data.validador_establecimiento.slice(0, 15);
        renderTable('tableValidadorEstablecimiento', data.validador_establecimiento, 'nombre');
        destroyChart('validadorEstablecimiento');
        charts.validadorEstablecimiento = new Chart(document.getElementById('chartValidadorEstablecimiento'), {
            type: 'bar',
            data: { labels: valTopEst.map(d => d.nombre_corto || d.nombre), datasets: [{ data: valTopEst.map(d => parseInt(d.total)), backgroundColor: '#0ea5e9', borderRadius: 6, barThickness: 'flex', maxBarThickness: 24 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' } }, y: { grid: { display: false }, ticks: { font: { size: 11 } } } } }
        });
    }

    // ====== SERIE / HOJA TAB ======
    function renderSerieHoja(data) {
        renderTableMulti('tableSerieDetalle', data.serie_detalle, ['codigo_serie', 'tipo_error']);
        destroyChart('serieDetalle');
        const serieAgg = {};
        data.serie_detalle.forEach(d => { serieAgg[d.codigo_serie] = (serieAgg[d.codigo_serie] || 0) + parseInt(d.total); });
        charts.serieDetalle = new Chart(document.getElementById('chartSerieDetalle'), {
            type: 'bar',
            data: { labels: Object.keys(serieAgg), datasets: [{ data: Object.values(serieAgg), backgroundColor: PALETTE.slice(0, Object.keys(serieAgg).length), borderRadius: 6, barThickness: 'flex', maxBarThickness: 32 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' } }, x: { grid: { display: false }, ticks: { font: { size: 11 } } } } }
        });

        renderTableMulti('tableHojaDetalle', data.hoja_detalle, ['codigo_hoja', 'tipo_error']);
        destroyChart('hojaDetalle');
        const hojaTop = data.hoja_detalle.slice(0, 15);
        charts.hojaDetalle = new Chart(document.getElementById('chartHojaDetalle'), {
            type: 'bar',
            data: { labels: hojaTop.map(d => d.codigo_hoja), datasets: [{ data: hojaTop.map(d => parseInt(d.total)), backgroundColor: '#6366f1', borderRadius: 6, barThickness: 'flex', maxBarThickness: 28 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: 'rgba(226,232,240,0.6)' } }, x: { grid: { display: false }, ticks: { font: { size: 10 } } } } }
        });
    }

    // ====== TABS ======
    function switchTab(tabId) {
        document.querySelectorAll('.report-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.report-tab-content').forEach(c => c.classList.remove('active'));
        document.querySelector(`[data-tab="${tabId}"]`).classList.add('active');
        document.getElementById(`tab-${tabId}`).classList.add('active');
    }

    // ====== FILTERS ======
    function populatePdfFilters() {
        const comunaSelect = document.getElementById('pdfComuna');
        comunaSelect.innerHTML = '<option value="">Todas</option>';
        filterData.comunas.forEach(c => {
            comunaSelect.innerHTML += `<option value="${c.id}">${escapeHtml(c.nombre)}</option>`;
        });
        updatePdfEstablecimientos();
    }

    function updatePdfEstablecimientos() {
        const comunaId = document.getElementById('pdfComuna').value;
        const estSelect = document.getElementById('pdfEstablecimiento');
        estSelect.innerHTML = '<option value="">Todos</option>';
        const filtered = comunaId ? filterData.establecimientos.filter(e => e.comuna_id == comunaId) : filterData.establecimientos;
        filtered.forEach(e => {
            estSelect.innerHTML += `<option value="${e.id}">${escapeHtml(e.nombre_corto || e.nombre)}</option>`;
        });
    }

    // ====== EXPORT ======
    function changeReportYear(year) {
        currentYear = parseInt(year);
        document.getElementById('reportYearLabel').textContent = currentYear;
        loadAllReports();
        loadFilters();
    }

    function exportData(format) {
        const params = new URLSearchParams();
        params.append('format', format);
        params.append('year', currentYear);
        window.location.href = 'api/export.php?' + params.toString();
    }

    function exportSpecificReport(reportType) {
        const params = new URLSearchParams();
        params.append('format', 'excel');
        params.append('year', currentYear);
        params.append('report_type', reportType);
        window.location.href = 'api/export.php?' + params.toString();
    }

    function exportDetalladoPDF() {
        const params = new URLSearchParams();
        params.append('format', 'pdf');
        params.append('year', currentYear);
        params.append('report_type', 'detallado');
        const comuna = document.getElementById('pdfComuna').value;
        const est = document.getElementById('pdfEstablecimiento').value;
        const mes = document.getElementById('pdfMes').value;
        const estado = document.getElementById('pdfEstado').value;
        if (comuna) params.append('comuna_id', comuna);
        if (est) params.append('establecimiento_id', est);
        if (mes) params.append('mes', mes);
        if (estado) params.append('estado', estado);
        window.location.href = 'api/export.php?' + params.toString();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }
</script>
