<?php
$usuarioId = $_SESSION['usuario_id'] ?? 0;
$rol = $_SESSION['rol'] ?? '';
$anio = $_SESSION['anio_trabajo'] ?? date('Y');
$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
$partesNombre = explode(' ', $nombreUsuario);
$primerNombre = $partesNombre[0];
?>

<div class="dashboard">
    <div class="dashboard-header">
        <div class="greeting">
            <h1>Hola, <?php echo htmlspecialchars($primerNombre); ?></h1>
            <p>Resumen de observaciones REM - Año <?php echo $anio; ?></p>
        </div>
        <div class="header-actions">
            <?php if ($rol === ROL_REGISTRADOR): ?>
            <a href="?pagina=observaciones&anio=<?php echo $anio; ?>" class="btn btn-primary">
                <?php echo tablerIcon('plus'); ?>
                Nueva Observación
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div id="alertas-container"></div>

    <div class="stats-grid">
        <div class="stat-card stat-total">
            <div class="stat-icon">
                <?php echo tablerIcon('file-text'); ?>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="stat-total">
                    <span class="loading"></span>
                </div>
                <div class="stat-label">Total Registradas</div>
            </div>
        </div>

        <div class="stat-card stat-pending">
            <div class="stat-icon">
                <?php echo tablerIcon('clock'); ?>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="stat-pendientes">
                    <span class="loading"></span>
                </div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>

        <div class="stat-card stat-approved">
            <div class="stat-icon">
                <?php echo tablerIcon('check-circle'); ?>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="stat-aprobadas">
                    <span class="loading"></span>
                </div>
                <div class="stat-label">Aprobadas</div>
            </div>
        </div>

        <div class="stat-card stat-errors">
            <div class="stat-icon">
                <?php echo tablerIcon('alert-triangle'); ?>
            </div>
            <div class="stat-content">
                <div class="stat-value" id="stat-problemas">
                    <span class="loading"></span>
                </div>
                <div class="stat-label">Con Problemas</div>
            </div>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-header">
                <h3>Distribución por Estado</h3>
            </div>
            <div class="chart-body">
                <div id="chart-donut"></div>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Observaciones por Mes</h3>
            </div>
            <div class="chart-body">
                <div id="chart-lineas"></div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard {
    max-width: 1400px;
    margin: 0 auto;
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.greeting h1 {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.25rem;
}

.greeting p {
    color: #64748b;
    margin: 0;
    font-size: 0.95rem;
}

.header-actions {
    display: flex;
    gap: 0.75rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
    text-decoration: none;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #0ea5e9;
    color: white;
}

.btn-primary:hover {
    background: #0284c7;
    transform: translateY(-1px);
}

.btn .ti {
    width: 18px;
    height: 18px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
}

.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon .ti {
    width: 28px;
    height: 28px;
}

.stat-total .stat-icon {
    background: #e0f2fe;
    color: #0ea5e9;
}

.stat-pending .stat-icon {
    background: #fef9c3;
    color: #ca8a04;
}

.stat-approved .stat-icon {
    background: #dcfce7;
    color: #16a34a;
}

.stat-errors .stat-icon {
    background: #fee2e2;
    color: #dc2626;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.2;
}

.stat-value .loading {
    display: inline-block;
    width: 48px;
    height: 32px;
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 6px;
}

@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.stat-label {
    font-size: 0.85rem;
    color: #64748b;
    font-weight: 500;
}

.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.chart-card {
    background: white;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.chart-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
}

.chart-header h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.chart-body {
    padding: 1.5rem;
    height: 300px;
}

#chart-donut, #chart-lineas {
    width: 100%;
    height: 100%;
}

@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .charts-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }
}

#alertas-container {
    margin-bottom: 1.5rem;
}

#alertas-container .alert {
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
}

#alertas-container .alert-danger {
    background: #fee2e2;
    color: #dc2626;
    border: none;
}
</style>

<script>
window.DASHBOARD_CONFIG = {
    anio: <?php echo $anio; ?>,
    rol: '<?php echo $rol; ?>',
    usuarioId: <?php echo $usuarioId; ?>
};
</script>
<script src="assets/js/dashboard.js"></script>