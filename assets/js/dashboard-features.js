/**
 * Dashboard Features Module
 * Tabler advanced components for the dashboard
 * Features: skeleton loading, auto-refresh, card tabs, dropdown filters,
 * timeline, progress steps, sparklines, kanban board
 */

// ============================================================
// Feature Flags
// ============================================================
const DASHBOARD_FEATURES = {
    skeletonLoading: true,
    autoRefresh: true,
    cardTabs: true,
    dropdownFilters: true,
    timeline: true,
    progressSteps: true,
    sparklines: true,
    kanbanBoard: true
};

// ============================================================
// Utility Functions
// ============================================================

function formatRelativeTime(date) {
    const now = new Date();
    const diff = Math.floor((now - new Date(date)) / 1000);
    if (diff < 60) return 'hace unos segundos';
    if (diff < 3600) return `hace ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `hace ${Math.floor(diff / 3600)} horas`;
    return `hace ${Math.floor(diff / 86400)} días`;
}

function animateValue(element, start, end, duration = 800) {
    if (!element || start === end) return;
    const startTime = performance.now();
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const easeProgress = 1 - Math.pow(1 - progress, 3); // easeOutCubic
        const current = Math.floor(start + (end - start) * easeProgress);
        element.textContent = current.toLocaleString('es-CL');
        if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ============================================================
// 1. Skeleton Loading Module
// ============================================================

class SkeletonLoader {
    constructor() {
        this.elements = new Map();
    }

    show(containerId, type = 'card') {
        const container = document.getElementById(containerId);
        if (!container) return;

        // Store original content
        if (!this.elements.has(containerId)) {
            this.elements.set(containerId, container.innerHTML);
        }

        let skeletonHTML = '';
        switch (type) {
            case 'card':
                skeletonHTML = `
                    <div class="skeleton-container" style="opacity:0;transition:opacity 0.3s">
                        <div class="d-flex align-items-center gap-3">
                            <div class="skeleton avatar avatar-md"></div>
                            <div class="flex-grow-1">
                                <div class="skeleton" style="width:60%;height:1.5rem;margin-bottom:0.5rem"></div>
                                <div class="skeleton" style="width:40%;height:0.875rem"></div>
                            </div>
                        </div>
                    </div>
                `;
                break;
            case 'table':
                skeletonHTML = `
                    <div class="skeleton-container" style="opacity:0;transition:opacity 0.3s">
                        <div class="table-responsive">
                            <table class="table table-vcenter">
                                ${Array(5).fill(0).map(() => `
                                    <tr>
                                        <td><div class="skeleton" style="width:80%;height:1rem"></div></td>
                                        <td><div class="skeleton" style="width:60%;height:1rem"></div></td>
                                        <td><div class="skeleton" style="width:70%;height:1rem"></div></td>
                                        <td><div class="skeleton" style="width:50%;height:1rem"></div></td>
                                        <td><div class="skeleton" style="width:40%;height:1rem"></div></td>
                                    </tr>
                                `).join('')}
                            </table>
                        </div>
                    </div>
                `;
                break;
            case 'chart':
                skeletonHTML = `
                    <div class="skeleton-container" style="opacity:0;transition:opacity 0.3s;aspect-ratio:16/9">
                        <div class="skeleton" style="width:100%;height:100%;border-radius:0.5rem"></div>
                    </div>
                `;
                break;
        }

        container.innerHTML = skeletonHTML;
        requestAnimationFrame(() => {
            const el = container.querySelector('.skeleton-container');
            if (el) el.style.opacity = '1';
        });
    }

    hide(containerId, restoreCallback = null) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const skeletonContainer = container.querySelector('.skeleton-container');
        if (skeletonContainer) {
            skeletonContainer.style.opacity = '0';
            setTimeout(() => {
                if (restoreCallback) {
                    restoreCallback();
                } else if (this.elements.has(containerId)) {
                    container.innerHTML = this.elements.get(containerId);
                    this.elements.delete(containerId);
                }
            }, 300);
        }
    }

    hideAll() {
        this.elements.forEach((content, id) => {
            this.hide(id);
        });
    }
}

// ============================================================
// 2. Auto Refresh Module
// ============================================================

class DashboardAutoRefresh {
    constructor(options = {}) {
        this.interval = options.interval || 120000; // 2 minutos
        this.timer = null;
        this.isActive = false;
        this.lastUpdate = null;
        this.statsData = null;
        this.badgeElement = null;
        this.toggleElement = null;
        this.onUpdate = options.onUpdate || null;
        this.visibilityPaused = false;
    }

    init() {
        // Load preference from localStorage
        const savedState = localStorage.getItem('dashboardAutoRefresh');
        this.isActive = savedState === null ? true : savedState === 'true';

        this.createToggle();
        this.createBadge();
        this.setupVisibilityAPI();

        if (this.isActive) this.start();
    }

    createToggle() {
        const headerActions = document.querySelector('.page-header .btn-list');
        if (!headerActions) return;

        const toggleHTML = `
            <label class="form-check form-switch form-check-single ms-2" title="Actualización automática">
                <input class="form-check-input" type="checkbox" id="autoRefreshToggle" ${this.isActive ? 'checked' : ''}>
                <span class="form-check-label d-none">Auto</span>
            </label>
        `;
        headerActions.insertAdjacentHTML('beforeend', toggleHTML);

        this.toggleElement = document.getElementById('autoRefreshToggle');
        this.toggleElement.addEventListener('change', (e) => {
            this.isActive = e.target.checked;
            localStorage.setItem('dashboardAutoRefresh', this.isActive);
            if (this.isActive) {
                this.start();
                showInfo('Auto-refresh activado');
            } else {
                this.stop();
                showInfo('Auto-refresh desactivado');
            }
        });
    }

    createBadge() {
        const pageHeader = document.querySelector('.page-header');
        if (!pageHeader) return;

        const badgeHTML = `
            <span id="refreshBadge" class="badge bg-secondary-lt text-secondary ms-2" style="font-size:0.75rem">
                Actualizado hace 0 seg
            </span>
        `;
        const pretitle = pageHeader.querySelector('.page-pretitle');
        if (pretitle) {
            pretitle.insertAdjacentHTML('afterend', badgeHTML);
        }

        this.badgeElement = document.getElementById('refreshBadge');
        this.lastUpdate = Date.now();
        this.updateBadge();
    }

    updateBadge() {
        if (!this.badgeElement || !this.lastUpdate) return;

        const seconds = Math.floor((Date.now() - this.lastUpdate) / 1000);
        let text = '';
        if (seconds < 60) text = `Actualizado hace ${seconds} seg`;
        else if (seconds < 3600) text = `Actualizado hace ${Math.floor(seconds / 60)} min`;
        else text = `Actualizado hace ${Math.floor(seconds / 3600)}h`;

        this.badgeElement.textContent = text;

        // Update every second
        setTimeout(() => this.updateBadge(), 1000);
    }

    setupVisibilityAPI() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.visibilityPaused = true;
                this.stop();
            } else if (this.isActive && this.visibilityPaused) {
                this.visibilityPaused = false;
                this.start();
            }
        });
    }

    start() {
        this.stop();
        this.timer = setInterval(() => this.refresh(), this.interval);
    }

    stop() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    }

    async refresh() {
        try {
            const response = await fetch('api/dashboard_data.php');
            if (!response.ok) throw new Error('Network error');
            const data = await response.json();

            if (data.success) {
                this.lastUpdate = Date.now();
                this.updateStats(data.stats);
                if (this.onUpdate) this.onUpdate(data);
            }
        } catch (error) {
            console.warn('Auto-refresh failed:', error);
        }
    }

    updateStats(newStats) {
        // Update stat cards with animation
        const statMappings = {
            'total': newStats.total,
            'pendientes': this.getEstadoCount(newStats.por_estado, 'pendiente'),
            'aprobados': this.getEstadoCount(newStats.por_estado, 'aprobado'),
            'problemas': this.getEstadoCount(newStats.por_estado, 'rechazado') +
                         this.getEstadoCount(newStats.por_estado, 'error')
        };

        document.querySelectorAll('.card-sm .h1').forEach((el, index) => {
            const keys = ['total', 'pendientes', 'aprobados', 'problemas'];
            const newValue = statMappings[keys[index]];
            if (newValue !== undefined) {
                const current = parseInt(el.textContent.replace(/[^0-9]/g, '')) || 0;
                animateValue(el, current, newValue);
            }
        });

        // Update charts
        if (typeof initializeCharts === 'function' && newStats) {
            initializeCharts(newStats);
        }
    }

    getEstadoCount(porEstado, estado) {
        if (!porEstado) return 0;
        const found = porEstado.find(e => e.estado_actual === estado);
        return found ? parseInt(found.total) : 0;
    }
}

// ============================================================
// 3. Card Tabs Module
// ============================================================

class CardTabs {
    constructor() {
        this.storageKey = 'dashboardCardTabs';
    }

    init(containerId, tabs) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const cardHeader = container.querySelector('.card-header');
        if (!cardHeader) return;

        // Get saved tab
        const savedTabs = JSON.parse(localStorage.getItem(this.storageKey) || '{}');
        const activeTab = savedTabs[containerId] || tabs[0].id;

        // Create tabs HTML
        const tabsHTML = `
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                ${tabs.map(tab => `
                    <li class="nav-item" role="presentation">
                        <button class="nav-link ${tab.id === activeTab ? 'active' : ''}" 
                                data-bs-toggle="tab" 
                                data-bs-target="#${tab.id}-${containerId}" 
                                type="button" role="tab"
                                onclick="cardTabs.saveTab('${containerId}', '${tab.id}')">
                            ${tab.label}
                        </button>
                    </li>
                `).join('')}
            </ul>
        `;

        // Insert tabs into card header
        const title = cardHeader.querySelector('.card-title');
        if (title) {
            title.insertAdjacentHTML('afterend', tabsHTML);
        } else {
            cardHeader.insertAdjacentHTML('beforeend', tabsHTML);
        }

        // Wrap content in tab panes
        const cardBody = container.querySelector('.card-body');
        if (cardBody) {
            const originalContent = cardBody.innerHTML;
            cardBody.innerHTML = `
                <div class="tab-content">
                    <div class="tab-pane show active" id="${tabs[0].id}-${containerId}" role="tabpanel">
                        ${originalContent}
                    </div>
                </div>
            `;
        }
    }

    saveTab(containerId, tabId) {
        const savedTabs = JSON.parse(localStorage.getItem(this.storageKey) || '{}');
        savedTabs[containerId] = tabId;
        localStorage.setItem(this.storageKey, JSON.stringify(savedTabs));
    }

    filterTable(tabId, filter) {
        const rows = document.querySelectorAll('#observationsTable tbody tr');
        rows.forEach(row => {
            if (tabId === 'recientes') {
                row.style.display = '';
            } else if (tabId === 'pendientes') {
                row.style.display = row.dataset.estado === 'pendiente' ? '' : 'none';
            } else if (tabId === 'problemas') {
                row.style.display = ['error', 'rechazado'].includes(row.dataset.estado) ? '' : 'none';
            }
        });
    }
}

// ============================================================
// 4. Dropdown Filters Module
// ============================================================

class DropdownFilters {
    constructor() {
        this.filters = {};
        this.storageKey = 'dashboardFilters';
    }

    addToCard(containerId, filterConfig) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const cardHeader = container.querySelector('.card-header');
        if (!cardHeader) return;

        const dropdownHTML = `
            <div class="dropdown ms-auto">
                <button class="btn btn-sm btn-ghost-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="ti ti-filter"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    ${filterConfig.map(filter => `
                        <div class="px-3 py-2">
                            <label class="form-label small">${filter.label}</label>
                            <select class="form-select form-select-sm" id="filter-${containerId}-${filter.name}" onchange="dropdownFilters.apply('${containerId}')">
                                ${filter.options.map(opt => `
                                    <option value="${opt.value}" ${opt.selected ? 'selected' : ''}>${opt.label}</option>
                                `).join('')}
                            </select>
                        </div>
                    `).join('')}
                    <div class="dropdown-divider"></div>
                    <button class="dropdown-item text-danger" onclick="dropdownFilters.clear('${containerId}')">
                        <i class="ti ti-x"></i> Limpiar filtros
                    </button>
                </div>
            </div>
        `;

        cardHeader.insertAdjacentHTML('beforeend', dropdownHTML);
    }

    apply(containerId) {
        // Implementation depends on specific card
        console.log('Applying filters for', containerId);
    }

    clear(containerId) {
        const selects = document.querySelectorAll(`[id^="filter-${containerId}-"]`);
        selects.forEach(select => select.value = '');
        this.apply(containerId);
    }
}

// ============================================================
// 5. Timeline Module
// ============================================================

class TimelineModule {
    constructor(containerId) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        this.events = [];
    }

    async load() {
        if (!this.container) return;

        try {
            const response = await fetch('api/timeline.php');
            const data = await response.json();

            if (data.success && data.events) {
                this.events = data.events;
                this.render();
            } else {
                this.renderEmpty();
            }
        } catch (error) {
            console.warn('Failed to load timeline:', error);
            this.renderEmpty();
        }
    }

    render() {
        if (!this.events.length) {
            this.renderEmpty();
            return;
        }

        const timelineHTML = `
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-history"></i> Actividad Reciente</h3>
                </div>
                <div class="card-body">
                    <div class="timeline" style="max-height:400px;overflow-y:auto">
                        ${this.events.slice(0, 20).map(event => `
                            <div class="timeline-item">
                                <div class="timeline-item-icon bg-${event.color || 'primary'} text-${event.color || 'primary'}-fg">
                                    <i class="ti ti-${event.icon || 'circle'}"></i>
                                </div>
                                <div class="timeline-item-content">
                                    <div class="text-muted small">${formatRelativeTime(event.fecha)}</div>
                                    <div>${event.descripcion}</div>
                                    <div class="text-secondary small">${event.usuario || ''}</div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        `;

        this.container.innerHTML = timelineHTML;
    }

    renderEmpty() {
        this.container.innerHTML = `
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-history"></i> Actividad Reciente</h3>
                </div>
                <div class="card-body">
                    <div class="empty">
                        <div class="empty-icon"><i class="ti ti-history"></i></div>
                        <p class="empty-title">No hay actividad reciente</p>
                        <p class="empty-subtitle text-secondary">Los eventos aparecerán aquí</p>
                    </div>
                </div>
            </div>
        `;
    }
}

// ============================================================
// 6. Progress Steps Module
// ============================================================

class ProgressSteps {
    constructor(containerId) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        this.steps = [
            { id: 'registrada', label: 'Registrada', icon: 'file-plus' },
            { id: 'revision', label: 'En Revisión', icon: 'eye' },
            { id: 'resuelta', label: 'Aprobada/Rechazada', icon: 'check' },
            { id: 'cerrada', label: 'Resuelta', icon: 'lock' }
        ];
    }

    render(counts = {}) {
        if (!this.container) return;

        const stepsHTML = `
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-route"></i> Flujo de Trabajo</h3>
                </div>
                <div class="card-body">
                    <div class="steps steps-counter">
                        ${this.steps.map((step, index) => {
                            const count = counts[step.id] || 0;
                            const isActive = count > 0;
                            return `
                                <a href="#" class="step-item ${isActive ? 'active' : ''}" 
                                   onclick="progressSteps.filterByStep('${step.id}'); return false;"
                                   title="${count} observaciones">
                                    <span class="step-item-icon">
                                        <i class="ti ti-${step.icon}"></i>
                                    </span>
                                    <span class="step-item-label">${step.label}</span>
                                    <span class="step-item-count badge bg-${isActive ? 'primary' : 'secondary'} ms-1">${count}</span>
                                </a>
                            `;
                        }).join('')}
                    </div>
                </div>
            </div>
        `;

        this.container.innerHTML = stepsHTML;
    }

    filterByStep(stepId) {
        // Map step to estado filter
        const stepToEstado = {
            'registrada': 'pendiente',
            'revision': 'pendiente',
            'resuelta': 'aprobado',
            'cerrada': 'justificado'
        };

        const estado = stepToEstado[stepId];
        if (estado && window.cardTabs) {
            cardTabs.filterTable('observations', estado);
        }
    }
}

// ============================================================
// 7. Sparklines Module
// ============================================================

class SparklineModule {
    constructor() {
        this.charts = {};
    }

    async render(containerId, data, color = 'primary') {
        const container = document.getElementById(containerId);
        if (!container || !data || data.length === 0) return;

        // Destroy existing chart if any
        if (this.charts[containerId]) {
            this.charts[containerId].destroy();
        }

        const options = {
            chart: {
                type: 'area',
                height: 40,
                width: 120,
                sparkline: { enabled: true }
            },
            series: [{ data: data }],
            colors: [getComputedStyle(document.documentElement).getPropertyValue(`--tblr-${color}`).trim() || '#0ea5e9'],
            stroke: { width: 2, curve: 'smooth' },
            fill: { opacity: 0.2 },
            tooltip: { fixed: { enabled: false }, x: { show: false }, y: { title: { formatter: () => '' } }, marker: { show: false } }
        };

        this.charts[containerId] = new ApexCharts(container, options);
        this.charts[containerId].render();
    }

    async loadAll() {
        try {
            const response = await fetch('api/sparkline_data.php');
            const data = await response.json();

            if (data.success) {
                const mappings = [
                    { id: 'sparkline-total', key: 'total', color: 'primary' },
                    { id: 'sparkline-pendientes', key: 'pendientes', color: 'warning' },
                    { id: 'sparkline-aprobados', key: 'aprobados', color: 'success' },
                    { id: 'sparkline-problemas', key: 'problemas', color: 'danger' }
                ];

                mappings.forEach(mapping => {
                    if (data[mapping.key] && data[mapping.key].length > 1) {
                        this.render(mapping.id, data[mapping.key], mapping.color);
                    }
                });
            }
        } catch (error) {
            console.warn('Sparkline load failed:', error);
        }
    }
}

// ============================================================
// 8. Kanban Board Module
// ============================================================

class KanbanBoard {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        this.columns = options.columns || [
            { id: 'pendiente', label: 'Pendiente', color: 'yellow' },
            { id: 'aprobado', label: 'Aprobado', color: 'green' },
            { id: 'rechazado', label: 'Rechazado', color: 'red' },
            { id: 'error', label: 'Error', color: 'danger' },
            { id: 'justificado', label: 'Justificado', color: 'blue' }
        ];
        this.canDrag = options.canDrag || false;
        this.items = [];
    }

    async load() {
        if (!this.container) return;

        try {
            const response = await fetch('api/kanban_data.php');
            const data = await response.json();

            if (data.success && data.items) {
                this.items = data.items;
                this.render();
            }
        } catch (error) {
            console.warn('Kanban load failed:', error);
        }
    }

    render() {
        if (!this.container) return;

        const kanbanHTML = `
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="ti ti-layout-kanban"></i> Tablero Kanban</h3>
                </div>
                <div class="card-body">
                    <div class="kanban-board" style="display:flex;gap:1rem;overflow-x:auto;padding-bottom:1rem">
                        ${this.columns.map(col => {
                            const colItems = this.items.filter(item => item.estado_actual === col.id);
                            return `
                                <div class="kanban-column" style="min-width:280px;flex-shrink:0"
                                     data-column="${col.id}">
                                    <div class="card-header bg-${col.color}-lt">
                                        <h4 class="card-title">${col.label}</h4>
                                        <span class="badge bg-${col.color}">${colItems.length}</span>
                                    </div>
                                    <div class="card-body" style="min-height:100px"
                                         ondrop="kanbanBoard.handleDrop(event, '${col.id}')"
                                         ondragover="kanbanBoard.handleDragOver(event)">
                                        ${colItems.length === 0 ? `
                                            <div class="empty">
                                                <p class="empty-subtitle text-secondary">Sin observaciones</p>
                                            </div>
                                        ` : ''}
                                        ${colItems.map(item => `
                                            <div class="card card-sm mb-2 kanban-card" draggable="${this.canDrag}"
                                                 data-id="${item.id}" data-estado="${item.estado_actual}"
                                                 ondragstart="kanbanBoard.handleDragStart(event, '${item.id}')">
                                                <div class="card-body">
                                                    <div class="fw-semibold small">${item.nombre_corto}</div>
                                                    <div class="text-secondary small">${item.mes}</div>
                                                    <div class="mt-1">
                                                        <span class="badge bg-${col.color}-lt text-${col.color}-fg small">${item.tipo_error}</span>
                                                    </div>
                                                    ${this.canDrag ? `
                                                        <div class="mt-2 btn-list">
                                                            ${this.getAdjacentStates(item.estado_actual).map(next => `
                                                                <button class="btn btn-sm btn-ghost-secondary" 
                                                                        onclick="kanbanBoard.moveItem(${item.id}, '${next.id}')">
                                                                    ${next.label}
                                                                </button>
                                                            `).join('')}
                                                        </div>
                                                    ` : ''}
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            </div>
        `;

        this.container.innerHTML = kanbanHTML;
    }

    getAdjacentStates(currentState) {
        const flow = {
            'pendiente': [{ id: 'aprobado', label: 'Aprobar' }, { id: 'rechazado', label: 'Rechazar' }],
            'aprobado': [{ id: 'justificado', label: 'Justificar' }],
            'rechazado': [{ id: 'error', label: 'Marcar Error' }],
            'error': [{ id: 'justificado', label: 'Justificar' }],
            'justificado': []
        };
        return flow[currentState] || [];
    }

    handleDragStart(event, itemId) {
        event.dataTransfer.setData('text/plain', itemId);
        event.dataTransfer.effectAllowed = 'move';
    }

    handleDragOver(event) {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
    }

    async handleDrop(event, newState) {
        event.preventDefault();
        const itemId = event.dataTransfer.getData('text/plain');
        if (itemId) await this.moveItem(parseInt(itemId), newState);
    }

    async moveItem(itemId, newState) {
        try {
            const response = await fetch('api/update_estado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: itemId, estado_actual: newState })
            });

            const data = await response.json();
            if (data.success) {
                showSuccess('Estado actualizado correctamente');
                await this.load(); // Reload
            } else {
                showError(data.message || 'Error al actualizar estado');
            }
        } catch (error) {
            showError('Error al actualizar estado: ' + error.message);
        }
    }
}

// ============================================================
// Global Instances
// ============================================================

let skeletonLoader;
let autoRefresh;
let cardTabs;
let dropdownFilters;
let timelineModule;
let progressSteps;
let sparklineModule;
let kanbanBoard;

// ============================================================
// Dashboard Filters
// ============================================================

function applyDashboardFilter(type, value) {
    const filters = JSON.parse(sessionStorage.getItem('dashboardFilters') || '{}');
    filters[type] = value;
    sessionStorage.setItem('dashboardFilters', JSON.stringify(filters));
    
    // Reload page with new year filter
    if (type === 'year' && value) {
        window.location.href = `?page=dashboard&year=${value}`;
    }
}

function clearDashboardFilters() {
    sessionStorage.removeItem('dashboardFilters');
    document.querySelectorAll('[id^="filter-tendencia-"]').forEach(el => {
        if (el.tagName === 'SELECT') el.selectedIndex = 0;
    });
    window.location.href = '?page=dashboard';
}

// Sync filters with reportes page navigation
document.querySelectorAll('a[href*="page=reportes"]').forEach(link => {
    link.addEventListener('click', (e) => {
        const filters = JSON.parse(sessionStorage.getItem('dashboardFilters') || '{}');
        if (filters.year) {
            e.preventDefault();
            window.location.href = `${link.getAttribute('href')}&year=${filters.year}`;
        }
    });
});

// ============================================================
// Initialization
// ============================================================

document.addEventListener('DOMContentLoaded', () => {
    // 1. Skeleton Loading - hide overlays after charts render
    function hideSkeletons() {
        document.querySelectorAll('.skeleton-overlay, .skeleton-overlay-chart').forEach(el => {
            el.style.opacity = '0';
            setTimeout(() => el.style.display = 'none', 300);
        });
        document.querySelectorAll('.real-content, .real-content-chart').forEach(el => {
            el.style.opacity = '1';
        });
    }

    if (DASHBOARD_FEATURES.skeletonLoading) {
        // Hide skeletons after charts initialize
        const originalInitCharts = window.initializeCharts;
        window.initializeCharts = function(statsData) {
            if (originalInitCharts) originalInitCharts(statsData);
            setTimeout(hideSkeletons, 300);
        };
    }

    // 2. Auto Refresh
    if (DASHBOARD_FEATURES.autoRefresh) {
        autoRefresh = new DashboardAutoRefresh({
            onUpdate: (data) => {
                if (DASHBOARD_FEATURES.sparklines && sparklineModule) {
                    sparklineModule.loadAll();
                }
            }
        });
        autoRefresh.init();
    }

    // 3. Card Tabs - persistence and enhanced behavior
    if (DASHBOARD_FEATURES.cardTabs) {
        const storageKey = 'dashboardCardTabs';
        const savedTabs = JSON.parse(localStorage.getItem(storageKey) || '{}');

        // Restore active tabs from localStorage
        Object.keys(savedTabs).forEach(tabId => {
            const tabBtn = document.querySelector(`[data-bs-target="#${tabId}"]`);
            if (tabBtn) {
                const tabInstance = new bootstrap.Tab(tabBtn);
                tabInstance.show();
            }
        });

        // Save active tab on switch
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', (e) => {
                const target = e.target.getAttribute('data-bs-target');
                if (target) {
                    const saved = JSON.parse(localStorage.getItem(storageKey) || '{}');
                    saved[target.replace('#', '')] = true;
                    localStorage.setItem(storageKey, JSON.stringify(saved));
                }
            });
        });
    }

    // 4. Dropdown Filters
    if (DASHBOARD_FEATURES.dropdownFilters) {
        dropdownFilters = new DropdownFilters();
        // Add to chart cards as needed
    }

    // 5. Timeline
    if (DASHBOARD_FEATURES.timeline) {
        // Create timeline container if not exists
        if (!document.getElementById('timeline-container')) {
            const tendenciaCard = document.querySelector('#chartTendencia');
            if (tendenciaCard) {
                tendenciaCard.closest('.card').insertAdjacentHTML('afterend', 
                    '<div id="timeline-container"></div>');
            }
        }
        timelineModule = new TimelineModule('timeline-container');
        timelineModule.load();
    }

    // 6. Progress Steps
    if (DASHBOARD_FEATURES.progressSteps) {
        const statsCards = document.querySelector('.row.g-3');
        if (statsCards) {
            statsCards.insertAdjacentHTML('afterend', '<div id="progress-steps-container" class="mt-3"></div>');
            progressSteps = new ProgressSteps('progress-steps-container');
            progressSteps.render({
                'registrada': parseInt(document.querySelector('.card-sm:nth-child(1) .h1')?.textContent || 0),
                'revision': parseInt(document.querySelector('.card-sm:nth-child(2) .h1')?.textContent || 0),
                'resuelta': parseInt(document.querySelector('.card-sm:nth-child(3) .h1')?.textContent || 0),
                'cerrada': parseInt(document.querySelector('.card-sm:nth-child(4) .h1')?.textContent || 0)
            });
        }
    }

    // 7. Sparklines
    if (DASHBOARD_FEATURES.sparklines) {
        sparklineModule = new SparklineModule();
        // Add sparkline containers to stat cards
        document.querySelectorAll('.card-sm .card-body > .d-flex').forEach((card, i) => {
            const sparklineId = `sparkline-${['total', 'pendientes', 'aprobados', 'problemas'][i]}`;
            card.insertAdjacentHTML('beforeend', `<div id="${sparklineId}" style="margin-left:auto;width:120px;height:40px"></div>`);
        });
        sparklineModule.loadAll();
    }

    // 8. Kanban Board
    if (DASHBOARD_FEATURES.kanbanBoard) {
        const lastCard = document.querySelector('#informeResultados');
        if (lastCard) {
            lastCard.insertAdjacentHTML('beforebegin', '<div id="kanban-container" class="mt-3"></div>');
        } else {
            document.querySelector('.container-xl').insertAdjacentHTML('beforeend', '<div id="kanban-container" class="mt-3"></div>');
        }
        const userRole = document.querySelector('meta[name="user-role"]')?.content || 'registrador';
        kanbanBoard = new KanbanBoard('kanban-container', { 
            canDrag: userRole === 'supervisor' 
        });
        kanbanBoard.load();
    }
});
