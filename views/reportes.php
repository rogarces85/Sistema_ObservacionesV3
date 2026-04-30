<?php
/**
 * Vista de Reportes
 * Generación y visualización de reportes con exportación
 */

require_once 'models/Observation.php';
require_once 'models/Location.php';

$obsModel = new Observation();
$locationModel = new Location();
$currentYear = $_SESSION['year'] ?? date('Y');
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['rol'];

$stats = $obsModel->getStats($currentYear, $userId, $userRole);
$comunas = $locationModel->getComunas();
?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Reportes y Análisis <?php echo $currentYear; ?></h2>
            <p class="text-slate-600">Visualización y exportación de datos estadísticos</p>
        </div>
    </div>

    <!-- Resumen rápido en cards horizontales -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4 text-center">
            <div class="text-2xl font-bold text-slate-800"><?php echo $stats['total']; ?></div>
            <div class="text-xs text-slate-500 uppercase">Total Observaciones</div>
        </div>
        <?php
        $colors = ['pendiente' => 'amber', 'aprobado' => 'emerald', 'rechazado' => 'rose'];
        foreach ($stats['por_estado'] as $estado):
            $color = $colors[$estado['estado_actual']] ?? 'slate';
            ?>
            <div class="card p-4 text-center">
                <div class="text-2xl font-bold text-<?php echo $color; ?>-700"><?php echo $estado['total']; ?></div>
                <div class="text-xs text-slate-500 uppercase"><?php echo ucfirst($estado['estado_actual']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Gráficos en grid adaptativo -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Gráfico de Barras - Por Estado -->
        <div class="card p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">📊 Observaciones por Estado</h3>
            <div style="height: 280px; position: relative;">
                <canvas id="chartEstado"></canvas>
            </div>
        </div>

        <!-- Gráfico de Líneas - Tendencia Mensual -->
        <div class="card p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">📈 Tendencia Mensual</h3>
            <div style="height: 280px; position: relative;">
                <canvas id="chartTendencia"></canvas>
            </div>
        </div>
    </div>

    <!-- Distribución por tipo de error - Ancho completo -->
    <?php if (!empty($stats['por_tipo_error'])): ?>
        <div class="card p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4">🎯 Distribución por Tipo de Error</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div style="height: 300px; position: relative;">
                    <canvas id="chartTipoError"></canvas>
                </div>
                <div class="space-y-2">
                    <?php foreach ($stats['por_tipo_error'] as $index => $tipo):
                        $bgColors = ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#ec4899'];
                        $color = $bgColors[$index % count($bgColors)];
                        ?>
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-slate-50">
                            <div class="w-4 h-4 rounded" style="background: <?php echo $color; ?>;"></div>
                            <span
                                class="flex-1 text-sm text-slate-700"><?php echo htmlspecialchars($tipo['tipo_error']); ?></span>
                            <span class="font-bold text-slate-800"><?php echo $tipo['total']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Exportar Reporte - Ancho completo -->
    <div class="card p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-2">📥 Exportar Reporte</h3>
        <p class="text-sm text-slate-600 mb-6">Configure los filtros y seleccione el formato de exportación deseado</p>

        <form id="exportForm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div>
                    <label class="form-label">Año</label>
                    <select name="year" class="form-select" required>
                        <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $y == $currentYear ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div>
                    <label class="form-label">Mes</label>
                    <select name="month" class="form-select">
                        <option value="">Todos</option>
                        <?php
                        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                        foreach ($meses as $mes): ?>
                            <option value="<?php echo $mes; ?>"><?php echo $mes; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="<?php echo ESTADO_PENDIENTE; ?>">Pendiente</option>
                        <option value="<?php echo ESTADO_APROBADO; ?>">Aprobado</option>
                        <option value="<?php echo ESTADO_RECHAZADO; ?>">Rechazado</option>
                        <option value="<?php echo ESTADO_ERROR; ?>">Error</option>
                        <option value="<?php echo ESTADO_JUSTIFICADO; ?>">Justificado</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Comuna</label>
                    <select id="exportComuna" name="comuna_id" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($comunas as $comuna): ?>
                            <option value="<?php echo $comuna['id']; ?>">
                                <?php echo htmlspecialchars($comuna['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="form-label">Establecimiento</label>
                    <select id="exportEstablecimiento" name="establecimiento_id" class="form-select" disabled>
                        <option value="">Todos</option>
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="button" class="btn btn-primary" onclick="exportData('excel')">
                    📊 Exportar Excel
                </button>
                <button type="button" class="btn btn-danger" onclick="exportData('pdf')">
                    📄 Exportar PDF
                </button>
                <button type="button" class="btn btn-secondary" onclick="exportData('csv')">
                    📋 Exportar CSV
                </button>
            </div>
        </form>
    </div>

    <!-- Desglose por Mes - Tabla compacta -->
    <div class="card p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4">📅 Desglose por Mes</h3>
        <div class="grid grid-cols-3 md:grid-cols-6 lg:grid-cols-12 gap-2">
            <?php
            $mesesData = [];
            foreach ($stats['por_mes'] as $m) {
                $mesesData[$m['mes']] = $m['total'];
            }
            $mesesNombres = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            foreach ($mesesNombres as $mes):
                $total = $mesesData[$mes] ?? 0;
                $hasData = $total > 0;
                ?>
                <div class="text-center p-3 rounded-lg <?php echo $hasData ? 'bg-sky-50' : 'bg-slate-50'; ?>">
                    <div class="text-lg font-bold <?php echo $hasData ? 'text-sky-700' : 'text-slate-300'; ?>">
                        <?php echo $total; ?>
                    </div>
                    <div class="text-xs text-slate-500"><?php echo substr($mes, 0, 3); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    // Cargar establecimientos cuando se selecciona una comuna
    document.getElementById('exportComuna').addEventListener('change', async function () {
        const comunaId = this.value;
        const select = document.getElementById('exportEstablecimiento');

        select.innerHTML = '<option value="">Todos</option>';
        select.disabled = !comunaId;

        if (comunaId) {
            try {
                const response = await fetch(`api/locations.php?action=get_establecimientos&comuna_id=${comunaId}`);
                const data = await response.json();

                if (data.success) {
                    data.data.forEach(est => {
                        const option = document.createElement('option');
                        option.value = est.id;
                        option.textContent = est.nombre_corto || est.nombre;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error al cargar establecimientos:', error);
            }
        }
    });

    function exportData(format) {
        const form = document.getElementById('exportForm');
        const formData = new FormData(form);

        const params = new URLSearchParams();
        params.append('format', format);

        for (let [key, value] of formData.entries()) {
            if (value) params.append(key, value);
        }

        window.location.href = 'api/export.php?' + params.toString();
    }
</script>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="assets/js/charts.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const statsData = <?php echo json_encode($stats); ?>;
        try {
            initializeCharts(statsData);
        } catch (error) {
            console.error('Error al inicializar gráficos:', error);
        }
    });
</script>