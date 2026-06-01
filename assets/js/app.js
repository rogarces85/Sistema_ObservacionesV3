const API_BASE = window.location.pathname.split('/').slice(0, -1).join('/') + '/api';
let currentYear = new Date().getFullYear();
let currentUser = null;

async function fetchAPI(endpoint, options = {}) {
    try {
        const headers = {
            'Content-Type': 'application/json',
            ...options.headers
        };

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

function getCsrfToken() {
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    return metaTag ? metaTag.getAttribute('content') : null;
}

function showLoading() {
    const spinner = document.getElementById('loading-spinner');
    if (spinner) spinner.classList.remove('d-none');
}

function hideLoading() {
    const spinner = document.getElementById('loading-spinner');
    if (spinner) spinner.classList.add('d-none');
}

function showMessage(message, type = 'success') {
    if (type === 'success') showSuccess(message);
    else if (type === 'error') showError(message);
    else if (type === 'warning') showWarning(message);
    else showInfo(message);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('es-CL', {
        year: 'numeric', month: '2-digit', day: '2-digit'
    });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('es-CL', {
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit'
    });
}

async function changeYear(year) {
    try {
        await fetchAPI('auth.php?action=change_year', {
            method: 'POST',
            body: JSON.stringify({ year: parseInt(year) })
        });
        currentYear = year;
        window.location.href = `?page=${getCurrentPage()}&year=${year}`;
    } catch (error) {
        console.error('Error al cambiar año:', error);
        window.location.href = `?page=${getCurrentPage()}&year=${year}`;
    }
}

function getCurrentPage() {
    const params = new URLSearchParams(window.location.search);
    return params.get('page') || 'dashboard';
}

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

function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const inputs = form.querySelectorAll('[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('is-invalid');
        } else {
            input.classList.remove('is-invalid');
        }
    });

    if (!isValid) {
        showMessage('Por favor, complete todos los campos requeridos', 'error');
    }

    return isValid;
}

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

document.addEventListener('DOMContentLoaded', function () {
    console.log('Sistema REM cargado correctamente');
});
