/**
 * app.js - Utilidades principales del Sistema de Observaciones REM
 * Manejo de fetch API, CSRF tokens, utilidades generales
 */

'use strict';

// Calcular API_BASE desde window.location.pathname (sin rutas hardcodeadas)
const API_BASE = (() => {
    const ruta = window.location.pathname;
    const indice = ruta.lastIndexOf('/');
    return ruta.substring(0, indice + 1);
})();

/**
 * Realizar peticiones fetch con manejo de CSRF y errores
 * @param {string} url - URL relativa del endpoint
 * @param {object} opciones - Opciones de fetch
 * @returns {Promise<object>} Respuesta JSON parseada
 */
async function fetchAPI(url, opciones = {}) {
    const config = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...opciones.headers
        },
        ...opciones
    };

    // Agregar token CSRF si existe y es una petición que lo requiere
    const csrfToken = localStorage.getItem('csrf_token');
    if (csrfToken && ['POST', 'PUT', 'DELETE'].includes(config.method?.toUpperCase())) {
        config.headers['X-CSRF-TOKEN'] = csrfToken;
    }

    // Construir URL completa si es relativa
    const urlCompleta = url.startsWith('http') ? url : API_BASE + url;

    try {
        const respuesta = await fetch(urlCompleta, config);
        const datos = await respuesta.json();

        // Si la sesión expiró o no está autenticado, redirigir al login
        if (respuesta.status === 401 && !datos.success) {
            localStorage.removeItem('csrf_token');
            window.location.href = API_BASE + 'index.php';
            throw new Error(datos.error || 'Sesión no válida');
        }

        // Si el CSRF es inválido
        if (respuesta.status === 403 && !datos.success) {
            localStorage.removeItem('csrf_token');
            throw new Error(datos.error || 'Token CSRF inválido');
        }

        // Actualizar CSRF token si viene en la respuesta
        if (datos.data && datos.data.csrf_token) {
            localStorage.setItem('csrf_token', datos.data.csrf_token);
        }

        return datos;
    } catch (error) {
        console.error('Error en fetchAPI:', error);
        throw error;
    }
}

/**
 * Obtener token CSRF almacenado
 * @returns {string|null} Token CSRF o null
 */
function obtenerCsrfToken() {
    return localStorage.getItem('csrf_token');
}

/**
 * Formatear fecha a formato chileno DD/MM/YYYY
 * @param {string|Date} fecha - Fecha a formatear
 * @returns {string} Fecha formateada
 */
function formatearFecha(fecha) {
    if (!fecha) return '';
    const f = new Date(fecha);
    if (isNaN(f.getTime())) return fecha;
    return f.toLocaleDateString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

/**
 * Formatear fecha y hora a formato chileno
 * @param {string|Date} fecha - Fecha a formatear
 * @returns {string} Fecha y hora formateadas
 */
function formatearFechaHora(fecha) {
    if (!fecha) return '';
    const f = new Date(fecha);
    if (isNaN(f.getTime())) return fecha;
    return f.toLocaleDateString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }) + ' ' + f.toLocaleTimeString('es-CL', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Mostrar notificación tipo toast
 * @param {string} mensaje - Mensaje a mostrar
 * @param {string} tipo - Tipo de notificación: success, danger, warning, info
 * @param {number} duracion - Duración en milisegundos (default 4000)
 */
function mostrarNotificacion(mensaje, tipo = 'info', duracion = 4000) {
    const contenedor = document.getElementById('contenedorNotificaciones') || crearContenedorNotificaciones();

    const toast = document.createElement('div');
    toast.className = `alert alert-${tipo} alert-dismissible`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <h4 class="alert-title">${mensaje}</h4>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    contenedor.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, duracion);
}

/**
 * Crear contenedor de notificaciones si no existe
 * @returns {HTMLElement} Contenedor de notificaciones
 */
function crearContenedorNotificaciones() {
    const contenedor = document.createElement('div');
    contenedor.id = 'contenedorNotificaciones';
    contenedor.style.position = 'fixed';
    contenedor.style.top = '1rem';
    contenedor.style.right = '1rem';
    contenedor.style.zIndex = '9999';
    contenedor.style.maxWidth = '400px';
    document.body.appendChild(contenedor);
    return contenedor;
}

/**
 * Confirmar acción con modal
 * @param {string} mensaje - Mensaje de confirmación
 * @returns {Promise<boolean>} true si confirma, false si cancela
 */
function confirmarAccion(mensaje) {
    return new Promise((resolver) => {
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop show';
        document.body.appendChild(backdrop);

        const iconAlert = '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-alert-triangle" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.875h16.214a1.914 1.914 0 0 0 1.636 -2.875l-8.106 -13.534a1.914 1.914 0 0 0 -3.274 0z"/><path d="M12 16h.01"/></svg>';
        const iconX = '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-x" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>';
        const iconClock = '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-clock" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/><path d="M12 7v5l3 3"/></svg>';

        const modal = document.createElement('div');
        modal.className = 'modal-container show';
        modal.innerHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h3>${iconAlert} Confirmar acción</h3>
                    <button class="modal-close" onclick="document.body.removeChild(backdrop); modal.remove(); resolver(false);">${iconX}</button>
                </div>
                <div class="modal-body">
                    <p>${mensaje}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-ghost" onclick="document.body.removeChild(backdrop); modal.remove(); resolver(false);">Cancelar</button>
                    <button class="btn btn-primary" id="btnConfirmarSi">Confirmar</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        document.body.style.overflow = 'hidden';

        modal.querySelector('#btnConfirmarSi').addEventListener('click', () => {
            document.body.removeChild(backdrop);
            modal.remove();
            document.body.style.overflow = '';
            resolver(true);
        });

        backdrop.addEventListener('click', () => {
            document.body.removeChild(backdrop);
            modal.remove();
            document.body.style.overflow = '';
            resolver(false);
        });
    });
}

/**
 * Obtener nombre del mes en español
 * @param {number} mes - Número de mes (1-12)
 * @returns {string} Nombre del mes
 */
function obtenerNombreMes(mes) {
    const meses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    return meses[mes - 1] || '';
}

/**
 * Capitalizar primera letra de cada palabra
 * @param {string} texto - Texto a capitalizar
 * @returns {string} Texto capitalizado
 */
function capitalizar(texto) {
    if (!texto) return '';
    return texto.replace(/\b\w/g, (letra) => letra.toUpperCase());
}

/**
 * Debounce para evitar llamadas repetidas
 * @param {Function} funcion - Función a ejecutar
 * @param {number} espera - Tiempo de espera en ms
 * @returns {Function} Función con debounce
 */
function debounce(funcion, espera = 300) {
    let temporizador;
    return function(...args) {
        clearTimeout(temporizador);
        temporizador = setTimeout(() => funcion.apply(this, args), espera);
    };
}

/**
 * Clase para gestión de sesión y expiración
 */
class GestorSesion {
    constructor() {
        this.tiempoExpiracion = 30 * 60 * 1000; // 30 minutos
        this.tiempoAdvertencia = 25 * 60 * 1000; // 25 minutos
        this.temporizador = null;
        this.temporizadorAdvertencia = null;
        this.modalAdvertencia = null;
    }

    /**
     * Iniciar monitoreo de sesión
     */
    iniciar() {
        this.detener();
        this.programarAdvertencia();
        this.programarExpiracion();
    }

    /**
     * Reiniciar temporizadores (al hacer actividad)
     */
    reiniciar() {
        this.iniciar();
    }

    /**
     * Detener temporizadores
     */
    detener() {
        if (this.temporizador) clearTimeout(this.temporizador);
        if (this.temporizadorAdvertencia) clearTimeout(this.temporizadorAdvertencia);
    }

    /**
     * Programar advertencia a los 25 minutos
     */
    programarAdvertencia() {
        this.temporizadorAdvertencia = setTimeout(() => {
            this.mostrarModalAdvertencia();
        }, this.tiempoAdvertencia);
    }

    /**
     * Programar expiración a los 30 minutos
     */
    programarExpiracion() {
        this.temporizador = setTimeout(() => {
            this.cerrarSesion();
        }, this.tiempoExpiracion);
    }

    /**
     * Mostrar modal de advertencia de expiración
     */
    mostrarModalAdvertencia() {
        if (this.modalAdvertencia) return;

        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop show';

        const iconClock = '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-clock" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/><path d="M12 7v5l3 3"/></svg>';

        this.modalAdvertencia = document.createElement('div');
        this.modalAdvertencia.className = 'modal-container show';
        this.modalAdvertencia.innerHTML = `
            <div class="modal">
                <div class="modal-header modal-header-warning">
                    <h3>${iconClock} Sesión por expirar</h3>
                </div>
                <div class="modal-body">
                    <p>Su sesión expirará en <strong id="tiempoRestante">5 minutos</strong> por inactividad.</p>
                    <p>¿Desea mantener la sesión activa?</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" id="btnMantenerSesion">Mantener sesión activa</button>
                </div>
            </div>
        `;

        document.body.appendChild(backdrop);
        document.body.appendChild(this.modalAdvertencia);
        document.body.style.overflow = 'hidden';

        this.modalAdvertencia.querySelector('#btnMantenerSesion').addEventListener('click', async () => {
            try {
                await fetchAPI('api/auth.php?action=check');
                document.body.removeChild(backdrop);
                this.modalAdvertencia.remove();
                this.modalAdvertencia = null;
                document.body.style.overflow = '';
                this.reiniciar();
                mostrarNotificacion('Sesión renovada exitosamente', 'success');
            } catch (error) {
                this.cerrarSesion();
            }
        });
    }

    /**
     * Cerrar sesión por expiración
     */
    async cerrarSesion() {
        localStorage.removeItem('csrf_token');
        mostrarNotificacion('Sesión expirada por inactividad', 'warning');

        setTimeout(() => {
            window.location.href = API_BASE + 'index.php';
        }, 2000);
    }
}

// Exportar para uso global
window.fetchAPI = fetchAPI;
window.obtenerCsrfToken = obtenerCsrfToken;
window.formatearFecha = formatearFecha;
window.formatearFechaHora = formatearFechaHora;
window.mostrarNotificacion = mostrarNotificacion;
window.confirmarAccion = confirmarAccion;
window.obtenerNombreMes = obtenerNombreMes;
window.capitalizar = capitalizar;
window.debounce = debounce;
window.GestorSesion = GestorSesion;
window.API_BASE = API_BASE;

window.logout = async function () {
    try {
        await fetchAPI('api/auth.php?action=logout', { method: 'POST' });
    } catch (_) {}
    localStorage.removeItem('csrf_token');
    window.location.href = 'index.php';
};

window.changeYear = async function (anio) {
    try {
        const r = await fetchAPI('api/auth.php?action=change_year', {
            method: 'POST',
            body: JSON.stringify({ year: parseInt(anio) })
        });
        if (r.success) {
            window.location.reload();
        }
    } catch (_) {
        window.location.reload();
    }
};

window.tablerIcon = function (name, size) {
    const cls = size ? ' icon icon-' + size : ' icon';
    const icons = {
        'alert-circle': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' + cls + '"><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>',
        'alert-triangle': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' + cls + '"><path d="M12 9v4"/><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.875h16.214a1.914 1.914 0 0 0 1.636 -2.875l-8.106 -13.534a1.914 1.914 0 0 0 -3.274 0z"/><path d="M12 16h.01"/></svg>',
        'history': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' + cls + '"><path d="M12 8l0 4l2 2"/><path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5"/></svg>',
        'check': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' + cls + '"><path d="M5 12l5 5l10 -10"/></svg>',
        'x': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' + cls + '"><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>',
        'edit': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' + cls + '"><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/></svg>',
        'trash': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' + cls + '"><path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/></svg>',
        'eye': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' + cls + '"><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>',
        'user': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="' + cls + '"><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>',
    };
    return icons[name] || ('<i class="ti ti-' + name + '"></i>');
};

window.showMessage = function (mensaje, tipo) {
    const mapa = { error: 'danger', success: 'success', warning: 'warning', info: 'info' };
    mostrarNotificacion(mensaje, mapa[tipo] || 'info');
};

window.showLoading = function () {
    document.getElementById('loading-spinner')?.classList.remove('d-none');
};

window.hideLoading = function () {
    document.getElementById('loading-spinner')?.classList.add('d-none');
};
