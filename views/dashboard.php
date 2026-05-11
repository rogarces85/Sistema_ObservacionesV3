<?php
/**
 * Dashboard - Panel de Control
 * Muestra estadísticas y resumen del sistema
 */

require_once 'models/Observation.php';
require_once 'models/EstablecimientoAsignacion.php';

$obsModel = new Observation();
$asigModel = new EstablecimientoAsignacion();
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['rol'];
$currentYear = $_SESSION['year'] ?? date('Y');

// Verificar asignaciones
$tieneAsignaciones = false;
$registradoresSinAsignaciones = [];

if ($userRole === ROL_REGISTRADOR) {
    $tieneAsignaciones = $asigModel->tieneAsignaciones($userId, $currentYear);
} elseif ($userRole === ROL_SUPERVISOR) {
    $registradoresSinAsignaciones = $asigModel->getRegistradoresSinAsignaciones($currentYear);
}

// Obtener estadísticas
$stats = $obsModel->getStats($currentYear, $userId, $userRole);

// Obtener últimas observaciones
$recentObs = $obsModel->getAll($currentYear, $userId, $userRole);
$recentObs = array_slice($recentObs, 0, 5); // Solo las últimas 5

// Calcular estadísticas por estado
$pendientes = 0;
$aprobados = 0;
$problemas = 0;
$justificados = 0;

foreach ($stats['por_estado'] as $estado) {
    switch ($estado['estado_actual']) {
        case ESTADO_PENDIENTE:
            $pendientes = $estado['total'];
            break;
        case ESTADO_APROBADO:
            $aprobados = $estado['total'];
            break;
        case ESTADO_RECHAZADO:
        case ESTADO_ERROR:
            $problemas += $estado['total'];
            break;
        case ESTADO_JUSTIFICADO:
            $justificados = $estado['total'];
            break;
    }
}

// Preparar datos para gráfico de meses
$mesesData = [];
foreach ($stats['por_mes'] as $mes) {
    $mesesData[$mes['mes']] = $mes['total'];
}
global $MESES;
$maxValue = !empty($mesesData) ? max(array_values($mesesData)) : 1;
?>

<div class="space-y-6">
    <!-- Header con título y acciones rápidas -->
    <div class="flex flex-wrap justify-between items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Panel de Control <?php echo $currentYear; ?></h2>
            <p class="text-slate-600">Resumen estadístico del sistema de observaciones REM</p>
        </div>
        <div class="flex gap-2">
            <?php if ($userRole === ROL_REGISTRADOR): ?>
                <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="btn btn-primary">
                    📝 Nueva Observación
                </a>
            <?php endif; ?>
            <?php if ($userRole === ROL_SUPERVISOR): ?>
                <a href="?page=supervision&year=<?php echo $currentYear; ?>" class="btn btn-secondary">
                    👁️ Supervisar
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alertas de asignaciones -->
    <?php if ($userRole === ROL_REGISTRADOR && !$tieneAsignaciones): ?>
        <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-4">
            <div class="text-2xl">⚠️</div>
            <div class="flex-1">
                <p class="font-bold text-amber-800">No tiene establecimientos asignados</p>
                <p class="text-sm text-amber-700">
                    No tiene establecimientos asignados para el año <strong><?php echo $currentYear; ?></strong>. 
                    Contacte a su supervisor para que le asigne los establecimientos correspondientes.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($userRole === ROL_SUPERVISOR && !empty($registradoresSinAsignaciones)): ?>
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 flex items-start gap-4">
            <div class="text-2xl">🚨</div>
            <div class="flex-1">
                <p class="font-bold text-rose-800">
                    <?php echo count($registradoresSinAsignaciones); ?> registrador(es) sin establecimientos asignados
                </p>
                <p class="text-sm text-rose-700 mb-2">
                    Los siguientes registradores no tienen establecimientos asignados para el año 
                    <strong><?php echo $currentYear; ?></strong>:
                </p>
                <ul class="text-sm text-rose-700 list-disc list-inside">
                    <?php foreach ($registradoresSinAsignaciones as $reg): ?>
                        <li><?php echo htmlspecialchars($reg['nombre_completo']); ?> (<?php echo htmlspecialchars($reg['username']); ?>)</li>
                    <?php endforeach; ?>
                </ul>
                <a href="?page=asignaciones&year=<?php echo $currentYear; ?>" class="inline-block mt-2 text-sm font-semibold text-rose-800 hover:underline">
                    → Ir a Asignación de Establecimientos
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Cards de estadísticas principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total de observaciones -->
        <div class="card p-5"
            style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-color: #bae6fd;">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);">
                    <span class="text-2xl">📊</span>
                </div>
                <div>
                    <div class="text-3xl font-bold text-slate-800"><?php echo $stats['total']; ?></div>
                    <div class="text-sm font-semibold text-slate-600">Total Registradas</div>
                </div>
            </div>
        </div>

        <!-- Pendientes -->
        <div class="card p-5"
            style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-color: #fde68a;">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <span class="text-2xl">⏳</span>
                </div>
                <div>
                    <div class="text-3xl font-bold text-amber-700"><?php echo $pendientes; ?></div>
                    <div class="text-sm font-semibold text-amber-600">Pendientes</div>
                </div>
            </div>
        </div>

        <!-- Aprobados -->
        <div class="card p-5"
            style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-color: #a7f3d0;">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <span class="text-2xl">✅</span>
                </div>
                <div>
                    <div class="text-3xl font-bold text-emerald-700"><?php echo $aprobados; ?></div>
                    <div class="text-sm font-semibold text-emerald-600">Aprobados</div>
                </div>
            </div>
        </div>

        <!-- Con Problemas -->
        <div class="card p-5"
            style="background: linear-gradient(135deg, #fff1f2 0%, #ffe4e6 100%); border-color: #fecdd3;">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-xl" style="background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);">
                    <span class="text-2xl">⚠️</span>
                </div>
                <div>
                    <div class="text-3xl font-bold text-rose-700"><?php echo $problemas; ?></div>
                    <div class="text-sm font-semibold text-rose-600">Con Problemas</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Distribución por Estado (Gráfico de dona visual) -->
        <div class="card p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span>📈</span> Distribución por Estado
            </h3>
            <div class="space-y-4">
                <?php if (!empty($stats['por_estado'])): ?>
                    <?php foreach ($stats['por_estado'] as $estado): ?>
                        <?php
                        $percentage = $stats['total'] > 0 ? ($estado['total'] / $stats['total']) * 100 : 0;
                        $colors = [
                            'pendiente' => ['bg' => '#fbbf24', 'light' => '#fef3c7'],
                            'aprobado' => ['bg' => '#10b981', 'light' => '#d1fae5'],
                            'rechazado' => ['bg' => '#ef4444', 'light' => '#fee2e2'],
                            'error' => ['bg' => '#f97316', 'light' => '#ffedd5'],
                            'justificado' => ['bg' => '#0ea5e9', 'light' => '#e0f2fe']
                        ];
                        $color = $colors[$estado['estado_actual']] ?? ['bg' => '#64748b', 'light' => '#f1f5f9'];
                        ?>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full" style="background: <?php echo $color['bg']; ?>;"></div>
                                    <span
                                        class="text-sm font-medium text-slate-700"><?php echo ucfirst($estado['estado_actual']); ?></span>
                                </div>
                                <span class="text-sm font-bold text-slate-800">
                                    <?php echo $estado['total']; ?>
                                    <span class="text-slate-400">(<?php echo number_format($percentage, 0); ?>%)</span>
                                </span>
                            </div>
                            <div class="w-full h-2 rounded-full" style="background: <?php echo $color['light']; ?>;">
                                <div class="h-2 rounded-full transition-all duration-500"
                                    style="width: <?php echo $percentage; ?>%; background: <?php echo $color['bg']; ?>;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-slate-400 py-8">No hay datos para mostrar</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Tipos de Error -->
        <div class="card p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span>🔍</span> Top Tipos de Error
            </h3>
            <div class="space-y-3">
                <?php if (!empty($stats['por_tipo_error'])): ?>
                    <?php foreach (array_slice($stats['por_tipo_error'], 0, 5) as $index => $tipo): ?>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-white text-sm"
                                style="background: <?php echo ['#0ea5e9', '#6366f1', '#8b5cf6', '#ec4899', '#f97316'][$index] ?? '#64748b'; ?>;">
                                <?php echo $index + 1; ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-700 truncate">
                                    <?php echo htmlspecialchars($tipo['tipo_error']); ?>
                                </p>
                            </div>
                            <div class="text-lg font-bold text-slate-800"><?php echo $tipo['total']; ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center text-slate-400 py-8">No hay errores registrados</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="card p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span>⚡</span> Acciones Rápidas
            </h3>
            <div class="space-y-3">
                <?php if ($userRole === ROL_REGISTRADOR): ?>
                    <a href="?page=observaciones&year=<?php echo $currentYear; ?>"
                        class="flex items-center gap-3 p-4 rounded-xl bg-sky-50 hover:bg-sky-100 transition-all cursor-pointer group">
                        <div class="p-2 rounded-lg bg-sky-500 text-white">📝</div>
                        <div class="flex-1">
                            <p class="font-semibold text-slate-800 group-hover:text-sky-700">Nueva Observación</p>
                            <p class="text-xs text-slate-500">Registrar una nueva observación</p>
                        </div>
                        <span class="text-sky-500">→</span>
                    </a>
                <?php endif; ?>

                <a href="api/import_template.php"
                    class="flex items-center gap-3 p-4 rounded-xl bg-emerald-50 hover:bg-emerald-100 transition-all cursor-pointer group">
                    <div class="p-2 rounded-lg bg-emerald-500 text-white">📥</div>
                    <div class="flex-1">
                        <p class="font-semibold text-slate-800 group-hover:text-emerald-700">Descargar Plantilla</p>
                        <p class="text-xs text-slate-500">CSV para importación masiva</p>
                    </div>
                    <span class="text-emerald-500">→</span>
                </a>

                <a href="?page=reportes&year=<?php echo $currentYear; ?>"
                    class="flex items-center gap-3 p-4 rounded-xl bg-violet-50 hover:bg-violet-100 transition-all cursor-pointer group">
                    <div class="p-2 rounded-lg bg-violet-500 text-white">📊</div>
                    <div class="flex-1">
                        <p class="font-semibold text-slate-800 group-hover:text-violet-700">Generar Reportes</p>
                        <p class="text-xs text-slate-500">Exportar datos a Excel</p>
                    </div>
                    <span class="text-violet-500">→</span>
                </a>

                <?php if ($userRole === ROL_SUPERVISOR): ?>
                    <a href="?page=supervision&year=<?php echo $currentYear; ?>"
                        class="flex items-center gap-3 p-4 rounded-xl bg-amber-50 hover:bg-amber-100 transition-all cursor-pointer group">
                        <div class="p-2 rounded-lg bg-amber-500 text-white">👁️</div>
                        <div class="flex-1">
                            <p class="font-semibold text-slate-800 group-hover:text-amber-700">Supervisar</p>
                            <p class="text-xs text-slate-500"><?php echo $pendientes; ?> pendientes de revisión</p>
                        </div>
                        <span class="text-amber-500">→</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gráfico de Observaciones por Mes -->
    <div class="card p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
            <span>📅</span> Observaciones por Mes - <?php echo $currentYear; ?>
        </h3>
        <div class="flex items-end justify-between gap-2" style="height: 200px;">
            <?php foreach ($MESES as $mes):
                $value = $mesesData[$mes] ?? 0;
                $height = $maxValue > 0 ? ($value / $maxValue) * 100 : 0;
                $isEmpty = $value === 0;
                ?>
                <div class="flex-1 flex flex-col items-center gap-2">
                    <div class="text-xs font-bold <?php echo $isEmpty ? 'text-slate-300' : 'text-slate-700'; ?>">
                        <?php echo $value; ?>
                    </div>
                    <div class="w-full flex items-end justify-center" style="height: 140px;">
                        <div class="w-full max-w-[40px] rounded-t-lg transition-all duration-300 hover:opacity-80"
                            style="height: <?php echo max($height, 4); ?>%; 
                                background: <?php echo $isEmpty ? '#e2e8f0' : 'linear-gradient(180deg, #0ea5e9 0%, #0284c7 100%)'; ?>;" title="<?php echo $mes; ?>: <?php echo $value; ?> observaciones">
                        </div>
                    </div>
                    <div class="text-xs font-medium text-slate-500 transform -rotate-45 origin-center"
                        style="white-space: nowrap;">
                        <?php echo substr($mes, 0, 3); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Últimas Observaciones -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <span>📋</span> Últimas Observaciones Registradas
            </h3>
            <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="text-sm text-sky-600 hover:underline">
                Ver todas →
            </a>
        </div>
        <?php if (!empty($recentObs)): ?>
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Establecimiento</th>
                            <th>Mes</th>
                            <th>Tipo de Error</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentObs as $obs): ?>
                            <tr class="hover:bg-slate-50">
                                <td>
                                    <div class="font-semibold text-slate-800">
                                        <?php echo htmlspecialchars($obs['nombre_corto']); ?></div>
                                    <div class="text-xs text-slate-400"><?php echo htmlspecialchars($obs['comuna']); ?></div>
                                </td>
                                <td class="text-sm text-slate-600"><?php echo htmlspecialchars($obs['mes']); ?></td>
                                <td class="text-sm text-slate-600"><?php echo htmlspecialchars($obs['tipo_error']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $obs['estado_actual']; ?>">
                                        <?php echo ucfirst($obs['estado_actual']); ?>
                                    </span>
                                </td>
                                <td class="text-sm text-slate-500">
                                    <?php echo $obs['fecha_registro'] ? date('d/m/Y', strtotime($obs['fecha_registro'])) : '-'; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="p-12 text-center">
                <div class="text-4xl mb-3">📭</div>
                <p class="text-slate-600 font-medium">No hay observaciones registradas</p>
                <?php if ($userRole === ROL_REGISTRADOR): ?>
                    <p class="text-sm text-slate-400 mb-4">Comienza creando tu primera observación</p>
                    <a href="?page=observaciones&year=<?php echo $currentYear; ?>" class="btn btn-primary">
                        ➕ Crear Observación
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>