/**
 * Sistema de Notificaciones Toast
 * Proporciona feedback visual al usuario mediante notificaciones
 */

const Toast = {
    container: null,
    duration: 4000,

    /**
     * Inicializar el contenedor de toasts
     */
    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },

    /**
     * Mostrar notificación toast
     */
    show(message, type = 'info', duration = null) {
        this.init();

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const icon = this.getIcon(type);
        const iconSpan = document.createElement('span');
        iconSpan.className = 'toast-icon';
        iconSpan.textContent = icon;

        const messageSpan = document.createElement('span');
        messageSpan.className = 'toast-message';
        messageSpan.textContent = message;

        const closeBtn = document.createElement('button');
        closeBtn.className = 'toast-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.onclick = () => this.remove(toast);

        toast.appendChild(iconSpan);
        toast.appendChild(messageSpan);
        toast.appendChild(closeBtn);

        this.container.appendChild(toast);

        // Animar entrada
        setTimeout(() => toast.classList.add('toast-show'), 10);

        // Auto-remover
        const autoRemoveDuration = duration || this.duration;
        setTimeout(() => this.remove(toast), autoRemoveDuration);

        return toast;
    },

    /**
     * Remover toast específico
     */
    remove(toast) {
        if (!toast) return;

        toast.classList.remove('toast-show');
        toast.classList.add('toast-hide');

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    },

    /**
     * Obtener icono según tipo
     */
    getIcon(type) {
        const icons = {
            success: '✓',
            error: '✕',
            warning: '⚠',
            info: 'ℹ'
        };
        return icons[type] || icons.info;
    },

    // Métodos de conveniencia
    success(message, duration) {
        return this.show(message, 'success', duration);
    },

    error(message, duration) {
        return this.show(message, 'error', duration);
    },

    warning(message, duration) {
        return this.show(message, 'warning', duration);
    },

    info(message, duration) {
        return this.show(message, 'info', duration);
    }
};

// Alias global para compatibilidad
window.showToast = (message, type, duration) => Toast.show(message, type, duration);
window.showSuccess = (message) => Toast.success(message);
window.showError = (message) => Toast.error(message);
window.showWarning = (message) => Toast.warning(message);
window.showInfo = (message) => Toast.info(message);
