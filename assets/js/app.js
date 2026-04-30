/**
 * JavaScript del Sistema de Observaciones REM
 * Manejo de llamadas AJAX, modales, formularios, y UI
 */

// Configuración global
const API_BASE = '/ObservacionesREM_V2/api';
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

// Modal management
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('fade-in');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Cerrar modal al hacer click fuera del contenido
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.add('hidden');
    }
});

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

// Mostrar mensajes de éxito/error - usando sistema de toast
function showMessage(message, type = 'success') {
    // Delegamos al sistema de toast global
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
            window.location.href = 'index.php';
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

// Agregar animaciones CSS necesarias
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);

// Sidebar toggle para móviles
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('open');
    }
}

// Inicializar tooltips y otros componentes al cargar
document.addEventListener('DOMContentLoaded', function () {
    // Agregar event listeners globales aquí si es necesario
    console.log('Sistema REM cargado correctamente');
});
