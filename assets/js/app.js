/**
 * JavaScript del Sistema de Observaciones REM
 * Manejo de llamadas AJAX, modales, formularios, y UI
 */

// Configuración global
const API_BASE = window.location.pathname.split('/').slice(0, -1).join('/') + '/api';
let currentYear = new Date().getFullYear();
let currentUser = null;

// === CountUp helper for [data-countup] elements ===
function initCountUp(el) {
    if (!el) return;
    const target = parseFloat(el.getAttribute('data-countup')) || 0;
    if (target === 0) {
        el.textContent = '0';
        return;
    }
    const duration = 900;
    const start = performance.now();
    function step(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const value = Math.round(target * eased);
        el.textContent = value.toLocaleString('es-CL');
        if (progress < 1) {
            requestAnimationFrame(step);
        } else {
            el.textContent = target.toLocaleString('es-CL');
        }
    }
    requestAnimationFrame(step);
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-countup]').forEach(initCountUp);
});

// Función auxiliar para hacer peticiones fetch
async function fetchAPI(endpoint, options = {}) {
    try {
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };

        // Agregar token CSRF para métodos modificadores
        const method = options.method || 'GET';
        if (['POST', 'PUT', 'DELETE'].includes(method.toUpperCase())) {
            const csrfToken = getCsrfToken();
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }
        }

        const response = await fetch(`${API_BASE}/${endpoint}`, {
            ...options,
            headers
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Error en la petición');
        }

        return data;
    } catch (error) {
        console.error('Error en fetchAPI:', error);
        throw error;
    }
}

// Obtener token CSRF del meta tag
function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : null;
}

// Modales gestionados por Bootstrap 5 (data-bs-toggle, data-bs-target)
// Loading state
function showLoading(containerId = 'loading-overlay') {
    const overlay = document.getElementById(containerId);
    if (overlay) {
        overlay.classList.remove('hidden');
    }
}

function hideLoading(containerId = 'loading-overlay') {
    const overlay = document.getElementById(containerId);
    if (overlay) {
        overlay.classList.add('hidden');
    }
}

// Mostrar mensajes de éxito/error - usando toasts nativos de Bootstrap 5
function showMessage(message, type = 'success') {
    if (type === 'success') {
        showSuccess(message);
    } else if (type === 'error') {
        showError(message);
    } else if (type === 'warning') {
        showWarning(message);
    } else {
        showInfo(message);
    }
}

function _showToast(message, type) {
    const config = {
        success: { bg: 'bg-success', icon: '✓' },
        error: { bg: 'bg-danger', icon: '✕' },
        warning: { bg: 'bg-warning', icon: '⚠' },
        info: { bg: 'bg-info', icon: 'ℹ' }
    };
    const c = config[type] || config.info;
    const autohide = type !== 'error';
    const delay = type === 'error' ? 0 : 4000;

    const container = document.getElementById('toastContainer');
    if (!container) return;

    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white ${c.bg} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <span class="fw-bold">${c.icon}</span>
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>`;

    container.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { autohide, delay });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

function showSuccess(message) { _showToast(message, 'success'); }
function showError(message) { _showToast(message, 'error'); }
function showWarning(message) { _showToast(message, 'warning'); }
function showInfo(message) { _showToast(message, 'info'); }

// Formatear fecha
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-CL', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

// Formatear fecha y hora
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('es-CL', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Cambiar año
async function changeYear(year) {
    try {
        // Actualizar año en la sesión del servidor
        await fetchAPI('auth.php?action=change_year', {
            method: 'POST',
            body: JSON.stringify({ year: parseInt(year) })
        });

        currentYear = year;
        // Recargar la página con el nuevo año
        window.location.href = `?page=${getCurrentPage()}&year=${year}`;
    } catch (error) {
        console.error('Error al cambiar año:', error);
        // Aún así intentar recargar
        window.location.href = `?page=${getCurrentPage()}&year=${year}`;
    }
}

// Obtener página actual
function getCurrentPage() {
    const params = new URLSearchParams(window.location.search);
    return params.get('page') || 'dashboard';
}

// Logout
async function logout() {
    if (confirm('¿Está seguro que desea cerrar sesión?')) {
        try {
            await fetchAPI('auth.php?action=logout', { method: 'POST' });
            window.location.href = './index.php';
        } catch (error) {
            showMessage('Error al cerrar sesión', 'error');
        }
    }
}

// Validar formulario
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.style.borderColor = 'var(--color-rose-500)';
        } else {
            input.style.borderColor = 'var(--color-slate-200)';
        }
    });

    if (!isValid) {
        showMessage('Por favor, complete todos los campos requeridos', 'error');
    }

    return isValid;
}

// Inicializar tooltips y otros componentes al cargar
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (el) {
        return new bootstrap.Tooltip(el);
    });
    initNotifications();
});

async function initNotifications() {
    const notifList = document.getElementById('notifList');
    const notifDot = document.querySelector('.notif-dot');
    const markRead = document.getElementById('markNotificationsRead');
    if (!notifList || !notifDot) return;

    async function loadNotifications() {
        try {
            const response = await fetchAPI('notifications.php?action=list');
            const data = response.data || { unread: 0, items: [] };
            notifDot.textContent = data.unread || 0;
            notifDot.classList.toggle('d-none', !data.unread);

            if (!data.items || data.items.length === 0) {
                notifList.innerHTML = `
                    <div class="notif-item">
                        <i class="ti ti-info-circle"></i>
                        <div>
                            <div class="notif-title">Sin notificaciones nuevas</div>
                            <div class="notif-time">Aquí aparecerán los avisos relevantes del sistema.</div>
                        </div>
                    </div>`;
                return;
            }

            notifList.innerHTML = data.items.map(item => `
                <a class="notif-item text-decoration-none ${item.leida == 0 ? 'fw-semibold' : ''}" href="${item.url || '#'}" data-notification-id="${item.id}">
                    <i class="ti ${item.leida == 0 ? 'ti-bell-ringing' : 'ti-bell'}"></i>
                    <div>
                        <div class="notif-title">${escapeHtmlGlobal(item.titulo)}</div>
                        <div class="notif-time">${escapeHtmlGlobal(item.mensaje)}</div>
                    </div>
                </a>
            `).join('');
        } catch (error) {
            notifList.innerHTML = '<div class="notif-item text-danger">No se pudieron cargar las notificaciones.</div>';
        }
    }

    notifList.addEventListener('click', async function (event) {
        const item = event.target.closest('[data-notification-id]');
        if (!item) return;
        try {
            await fetchAPI('notifications.php?action=read', {
                method: 'POST',
                body: JSON.stringify({ id: item.dataset.notificationId })
            });
        } catch (error) { /* no bloquear navegación */ }
    });

    if (markRead) {
        markRead.addEventListener('click', async function (event) {
            event.preventDefault();
            try {
                await fetchAPI('notifications.php?action=read_all', { method: 'POST', body: '{}' });
                loadNotifications();
            } catch (error) {
                showError(error.message || 'No se pudieron marcar las notificaciones');
            }
        });
    }

    loadNotifications();
}

function escapeHtmlGlobal(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}
