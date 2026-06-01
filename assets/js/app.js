/**
 * JavaScript del Sistema de Observaciones REM
 * Manejo de llamadas AJAX, modales, formularios, y UI
 */

// Configuración global
const API_BASE = window.location.pathname.split('/').slice(0, -1).join('/') + '/api';
let currentYear = new Date().getFullYear();
let currentUser = null;

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
});
