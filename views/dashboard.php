<?php
$usuarioId = $_SESSION['usuario_id'] ?? 0;
$rol = $_SESSION['rol'] ?? '';
$anio = $_SESSION['anio_trabajo'] ?? date('Y');
$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
$partesNombre = explode(' ', $nombreUsuario);
$primerNombre = $partesNombre[0];
?>

<div class="dashboard">
    <div class="welcome-card">
        <div class="welcome-content">
            <div class="welcome-text">
                <span class="welcome-badge">Panel Principal</span>
                <h1 class="welcome-title">¡Bienvenido, <?php echo htmlspecialchars($primerNombre); ?>!</h1>
                <p class="welcome-subtitle">Aquí tienes el resumen del sistema de observaciones REM para el año <?php echo $anio; ?>.</p>
            </div>
            <div class="welcome-progress">
                <div class="progress-ring" data-tipo="aprobado">
                    <svg viewBox="0 0 80 80" class="ring-svg">
                        <circle cx="40" cy="40" r="34" class="ring-bg"></circle>
                        <circle cx="40" cy="40" r="34" class="ring-fill ring-fill-aprobado" id="ring-aprobado"></circle>
                    </svg>
                    <div class="ring-center">
                        <span class="ring-value" id="ring-aprobado-value">0%</span>
                        <span class="ring-label">Aprobados</span>
                    </div>
                </div>
                <div class="progress-ring" data-tipo="pendiente">
                    <svg viewBox="0 0 80 80" class="ring-svg">
                        <circle cx="40" cy="40" r="34" class="ring-bg"></circle>
                        <circle cx="40" cy="40" r="34" class="ring-fill ring-fill-pendiente" id="ring-pendiente"></circle>
                    </svg>
                    <div class="ring-center">
                        <span class="ring-value" id="ring-pendiente-value">0%</span>
                        <span class="ring-label">Pendientes</span>
                    </div>
                </div>
            </div>
            <div class="welcome-illustration">
                <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="illust-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#ffffff;stop-opacity:0.3"/>
                            <stop offset="100%" style="stop-color:#ffffff;stop-opacity:0.05"/>
                        </linearGradient>
                    </defs>
                    <circle cx="100" cy="100" r="85" fill="url(#illust-grad)"/>
                    <circle cx="100" cy="100" r="65" fill="none" stroke="#ffffff" stroke-opacity="0.2" stroke-width="1"/>
                    <g transform="translate(50, 50)">
                        <rect x="20" y="10" width="60" height="80" rx="6" fill="#ffffff" fill-opacity="0.95"/>
                        <rect x="32" y="20" width="36" height="6" rx="2" fill="#0ea5e9"/>
                        <rect x="32" y="32" width="36" height="3" rx="1.5" fill="#cbd5e1"/>
                        <rect x="32" y="40" width="28" height="3" rx="1.5" fill="#cbd5e1"/>
                        <rect x="32" y="48" width="32" height="3" rx="1.5" fill="#cbd5e1"/>
                        <rect x="32" y="56" width="24" height="3" rx="1.5" fill="#cbd5e1"/>
                        <path d="M50 68 c -2 0 -3 1 -3 3 c 0 4 6 6 6 6 c 0 0 6 -2 6 -6 c 0 -2 -1 -3 -3 -3 c -1 0 -2 1 -3 2 c -1 -1 -2 -2 -3 -2 z" fill="#dc2626"/>
                        <path d="M50 75 l 0 8" stroke="#dc2626" stroke-width="2" stroke-linecap="round"/>
                    </g>
                </svg>
            </div>
        </div>
    </div>

    <div id="alertas-container"></div>

    <div class="stats-grid">
        <div class="stat-card stat-total">
            <div class="stat-accent stat-accent-total"></div>
            <div class="stat-body">
                <div class="stat-icon">
                    <?php echo tablerIcon('file-text'); ?>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Total Registradas</div>
                    <div class="stat-value" id="stat-total">
                        <span class="loading"></span>
                    </div>
                    <div class="stat-meta">
                        <span class="stat-trend trend-neutral" id="stat-total-meta">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l14 0"/><path d="M13 18l6 -6"/><path d="M13 6l6 6"/></svg>
                            En el año
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="stat-card stat-pending">
            <div class="stat-accent stat-accent-pending"></div>
            <div class="stat-body">
                <div class="stat-icon">
                    <?php echo tablerIcon('clock'); ?>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Pendientes</div>
                    <div class="stat-value" id="stat-pendientes">
                        <span class="loading"></span>
                    </div>
                    <div class="stat-meta">
                        <span class="stat-trend trend-warning" id="stat-pendiente-meta">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                            Requieren atención
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="stat-card stat-approved">
            <div class="stat-accent stat-accent-approved"></div>
            <div class="stat-body">
                <div class="stat-icon">
                    <?php echo tablerIcon('check-circle'); ?>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Aprobadas</div>
                    <div class="stat-value" id="stat-aprobadas">
                        <span class="loading"></span>
                    </div>
                    <div class="stat-meta">
                        <span class="stat-trend trend-success" id="stat-aprobado-meta">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5l10 -10"/></svg>
                            Resueltas
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="stat-card stat-errors">
            <div class="stat-accent stat-accent-errors"></div>
            <div class="stat-body">
                <div class="stat-icon">
                    <?php echo tablerIcon('alert-triangle'); ?>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Con Problemas</div>
                    <div class="stat-value" id="stat-problemas">
                        <span class="loading"></span>
                    </div>
                    <div class="stat-meta">
                        <span class="stat-trend trend-danger" id="stat-problema-meta">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.875h16.214a1.914 1.914 0 0 0 1.636 -2.875l-8.106 -13.534a1.914 1.914 0 0 0 -3.274 0z"/><path d="M12 16h.01"/></svg>
                            Requieren revisión
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-header-left">
                    <div class="chart-icon chart-icon-donut">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"/><path d="M12 3a9 9 0 0 1 9 9h-9z"/></svg>
                    </div>
                    <div>
                        <h3>Distribución por Estado</h3>
                        <p>Proporción actual de observaciones</p>
                    </div>
                </div>
                <span class="chart-badge">Tiempo real</span>
            </div>
            <div class="chart-body">
                <div id="chart-donut"></div>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <div class="chart-header-left">
                    <div class="chart-icon chart-icon-area">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l4 -8l4 8l4 -4l4 4l0 4l-16 0z"/></svg>
                    </div>
                    <div>
                        <h3>Observaciones por Mes</h3>
                        <p>Tendencia mensual del año</p>
                    </div>
                </div>
                <span class="chart-badge">Anual</span>
            </div>
            <div class="chart-body">
                <div id="chart-lineas"></div>
            </div>
        </div>
    </div>

    <div class="breakdown-card">
        <div class="breakdown-header">
            <div>
                <h3>Desglose de Observaciones</h3>
                <p>Detalle por tipo de error y mes</p>
            </div>
            <div class="breakdown-tabs">
                <button class="breakdown-tab active" data-tab="tipo">Por Tipo de Error</button>
                <button class="breakdown-tab" data-tab="mes">Por Mes</button>
            </div>
        </div>
        <div class="breakdown-content">
            <div class="breakdown-pane active" data-pane="tipo">
                <table class="breakdown-table" id="tabla-tipo">
                    <thead>
                        <tr>
                            <th>Tipo de Error</th>
                            <th class="num">Cantidad</th>
                            <th class="num">% del Total</th>
                            <th class="bar-col">Distribución</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-tipo">
                        <tr><td colspan="4" class="loading-cell">Cargando datos...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="breakdown-pane" data-pane="mes">
                <table class="breakdown-table" id="tabla-mes">
                    <thead>
                        <tr>
                            <th>Mes</th>
                            <th class="num">Cantidad</th>
                            <th class="num">% del Total</th>
                            <th class="bar-col">Distribución</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-mes">
                        <tr><td colspan="4" class="loading-cell">Cargando datos...</td></tr>
                    </tbody>
                </table>
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
    position: relative;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.06);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid rgba(226, 232, 240, 0.6);
}

.stat-card:hover {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08), 0 4px 10px rgba(0, 0, 0, 0.04);
    transform: translateY(-3px);
}

.stat-accent {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, transparent, currentColor, transparent);
    opacity: 0.85;
}

.stat-accent-total {
    color: #0ea5e9;
    background: linear-gradient(90deg, #38bdf8, #0ea5e9, #0284c7);
}

.stat-accent-pending {
    color: #f59e0b;
    background: linear-gradient(90deg, #fbbf24, #f59e0b, #d97706);
}

.stat-accent-approved {
    color: #16a34a;
    background: linear-gradient(90deg, #4ade80, #16a34a, #15803d);
}

.stat-accent-errors {
    color: #dc2626;
    background: linear-gradient(90deg, #f87171, #dc2626, #b91c1c);
}

.stat-body {
    padding: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    color: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stat-total .stat-icon {
    background: linear-gradient(135deg, #38bdf8, #0284c7);
    box-shadow: 0 4px 12px rgba(14, 165, 233, 0.3);
}

.stat-pending .stat-icon {
    background: linear-gradient(135deg, #fbbf24, #d97706);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.stat-approved .stat-icon {
    background: linear-gradient(135deg, #4ade80, #15803d);
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

.stat-errors .stat-icon {
    background: linear-gradient(135deg, #f87171, #b91c1c);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.stat-icon .ti {
    width: 26px;
    height: 26px;
}

.stat-content {
    flex: 1;
    min-width: 0;
}

.stat-label {
    font-size: 0.75rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 0.35rem;
}

.stat-value {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, #1e293b, #475569);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    font-variant-numeric: tabular-nums;
}

.stat-total .stat-value {
    background: linear-gradient(135deg, #0284c7, #0ea5e9);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-pending .stat-value {
    background: linear-gradient(135deg, #d97706, #f59e0b);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-approved .stat-value {
    background: linear-gradient(135deg, #15803d, #16a34a);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-errors .stat-value {
    background: linear-gradient(135deg, #b91c1c, #dc2626);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
}

.stat-value .loading {
    display: inline-block;
    width: 60px;
    height: 28px;
    background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    border-radius: 6px;
    -webkit-text-fill-color: initial;
    background-clip: border-box;
    -webkit-background-clip: border-box;
}

@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.stat-meta {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.7rem;
    font-weight: 600;
}

.stat-trend {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    background: #f1f5f9;
    color: #64748b;
}

.stat-trend svg {
    flex-shrink: 0;
}

.trend-neutral {
    background: #f1f5f9;
    color: #475569;
}

.trend-warning {
    background: #fef9c3;
    color: #a16207;
}

.trend-success {
    background: #dcfce7;
    color: #15803d;
}

.trend-danger {
    background: #fee2e2;
    color: #b91c1c;
}

.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.chart-card {
    background: white;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.6);
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    transition: box-shadow 0.3s;
}

.chart-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
}

.chart-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.chart-header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.chart-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.chart-icon-donut {
    background: linear-gradient(135deg, #ddd6fe, #8b5cf6);
    color: white;
    box-shadow: 0 4px 10px rgba(139, 92, 246, 0.25);
}

.chart-icon-area {
    background: linear-gradient(135deg, #bae6fd, #0ea5e9);
    color: white;
    box-shadow: 0 4px 10px rgba(14, 165, 233, 0.25);
}

.chart-header h3 {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    line-height: 1.2;
}

.chart-header p {
    font-size: 0.75rem;
    color: #94a3b8;
    margin: 0.15rem 0 0;
}

.chart-badge {
    padding: 0.25rem 0.65rem;
    background: #f1f5f9;
    color: #475569;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
}

.chart-body {
    padding: 1.5rem;
    height: 300px;
}

#chart-donut, #chart-lineas {
    width: 100%;
    height: 100%;
}

.breakdown-card {
    background: white;
    border-radius: 16px;
    border: 1px solid rgba(226, 232, 240, 0.6);
    margin-top: 1.5rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.breakdown-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.breakdown-header h3 {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.breakdown-header p {
    font-size: 0.8rem;
    color: #94a3b8;
    margin: 0.15rem 0 0;
}

.breakdown-tabs {
    display: flex;
    gap: 0.4rem;
    background: #f1f5f9;
    padding: 0.25rem;
    border-radius: 10px;
}

.breakdown-tab {
    padding: 0.45rem 0.9rem;
    border: none;
    background: transparent;
    color: #64748b;
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 7px;
    cursor: pointer;
    transition: all 0.2s;
}

.breakdown-tab:hover {
    color: #1e293b;
}

.breakdown-tab.active {
    background: white;
    color: #0ea5e9;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.breakdown-content {
    padding: 0;
}

.breakdown-pane {
    display: none;
}

.breakdown-pane.active {
    display: block;
}

.breakdown-table {
    width: 100%;
    border-collapse: collapse;
}

.breakdown-table thead {
    background: #f8fafc;
}

.breakdown-table th {
    text-align: left;
    padding: 0.85rem 1.5rem;
    font-size: 0.7rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #f1f5f9;
}

.breakdown-table th.num {
    text-align: right;
}

.breakdown-table th.bar-col {
    width: 35%;
}

.breakdown-table td {
    padding: 0.9rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.875rem;
    color: #1e293b;
}

.breakdown-table td.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-weight: 600;
}

.breakdown-table tr:last-child td {
    border-bottom: none;
}

.breakdown-table tr:hover {
    background: #f8fafc;
}

.breakdown-table .breakdown-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.breakdown-table .breakdown-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    flex-shrink: 0;
}

.breakdown-bar {
    width: 100%;
    height: 6px;
    background: #f1f5f9;
    border-radius: 3px;
    overflow: hidden;
}

.breakdown-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #38bdf8, #0ea5e9);
    border-radius: 3px;
    transition: width 0.6s ease;
}

.breakdown-bar-fill.tipo {
    background: linear-gradient(90deg, #fbbf24, #f59e0b);
}

.breakdown-bar-fill.estado-aprobado {
    background: linear-gradient(90deg, #4ade80, #16a34a);
}

.breakdown-bar-fill.estado-pendiente {
    background: linear-gradient(90deg, #fbbf24, #d97706);
}

.breakdown-bar-fill.estado-error {
    background: linear-gradient(90deg, #f87171, #dc2626);
}

.breakdown-bar-fill.estado-rechazado {
    background: linear-gradient(90deg, #f87171, #b91c1c);
}

.breakdown-pct {
    color: #0ea5e9;
    font-weight: 700;
}

.loading-cell {
    text-align: center;
    color: #94a3b8;
    padding: 2.5rem 1rem;
    font-size: 0.85rem;
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

.empty-state {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #94a3b8;
    font-size: 0.9rem;
    text-align: center;
}

.welcome-card {
    position: relative;
    background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 50%, #8b5cf6 100%);
    border-radius: 20px;
    padding: 2.5rem 2.75rem;
    margin-bottom: 2rem;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(14, 165, 233, 0.25);
    color: white;
}

.welcome-content {
    display: grid;
    grid-template-columns: 1fr auto 180px;
    gap: 2rem;
    align-items: center;
    position: relative;
    z-index: 2;
}

.welcome-text {
    max-width: 480px;
}

.welcome-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin-bottom: 0.75rem;
}

.welcome-title {
    font-size: 1.875rem;
    font-weight: 700;
    margin: 0 0 0.5rem;
    color: white;
    line-height: 1.2;
}

.welcome-subtitle {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.85);
    margin: 0;
    line-height: 1.5;
}

.welcome-progress {
    display: flex;
    gap: 1.25rem;
}

.progress-ring {
    position: relative;
    width: 90px;
    height: 90px;
}

.ring-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.ring-bg {
    fill: none;
    stroke: rgba(255, 255, 255, 0.2);
    stroke-width: 8;
}

.ring-fill {
    fill: none;
    stroke-width: 8;
    stroke-linecap: round;
    stroke-dasharray: 213.6;
    stroke-dashoffset: 213.6;
    transition: stroke-dashoffset 1.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.ring-fill-aprobado {
    stroke: #86efac;
}

.ring-fill-pendiente {
    stroke: #fde047;
}

.ring-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    display: flex;
    flex-direction: column;
    line-height: 1.1;
}

.ring-value {
    font-size: 1.125rem;
    font-weight: 700;
    color: white;
}

.ring-label {
    font-size: 0.65rem;
    color: rgba(255, 255, 255, 0.8);
    text-transform: uppercase;
    letter-spacing: 0.03em;
    margin-top: 0.15rem;
}

.welcome-illustration {
    display: flex;
    align-items: center;
    justify-content: center;
}

.welcome-illustration svg {
    width: 100%;
    height: auto;
    max-width: 180px;
}

@media (max-width: 1024px) {
    .welcome-content {
        grid-template-columns: 1fr auto;
    }
    .welcome-illustration {
        display: none;
    }
}

@media (max-width: 640px) {
    .welcome-card {
        padding: 1.75rem 1.5rem;
    }
    .welcome-content {
        grid-template-columns: 1fr;
    }
    .welcome-title {
        font-size: 1.5rem;
    }
    .welcome-progress {
        justify-content: flex-start;
    }
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