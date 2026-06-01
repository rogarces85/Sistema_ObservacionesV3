/**
 * Sistema de Toast con Bootstrap
 * Reemplaza notifications.js
 */
const ToastSystem = {
    show(message, type = 'info', duration = 4000) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const icons = {
            success: 'ti ti-check-circle',
            error: 'ti ti-x-circle',
            warning: 'ti ti-alert-triangle',
            info: 'ti ti-info-circle'
        };

        const bgClasses = {
            success: 'bg-success text-white',
            error: 'bg-danger text-white',
            warning: 'bg-warning',
            info: 'bg-info text-white'
        };

        const toast = document.createElement('div');
        toast.className = `toast ${bgClasses[type] || 'bg-info text-white'}`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        toast.style.animation = 'slideIn 0.3s ease';

        toast.innerHTML = `
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="${icons[type] || icons.info}"></i>
                <span class="flex-grow-1">${message}</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        `;

        container.appendChild(toast);

        const bsToast = new bootstrap.Toast(toast, {
            autohide: true,
            delay: duration
        });
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    },

    success(message) { this.show(message, 'success'); },
    error(message) { this.show(message, 'error', 6000); },
    warning(message) { this.show(message, 'warning'); },
    info(message) { this.show(message, 'info'); }
};

window.showToast = (message, type, duration) => ToastSystem.show(message, type, duration);
window.showSuccess = (message) => ToastSystem.success(message);
window.showError = (message) => ToastSystem.error(message);
window.showWarning = (message) => ToastSystem.warning(message);
window.showInfo = (message) => ToastSystem.info(message);
