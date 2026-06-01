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
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar acción</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>${mensaje}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="btnConfirmarSi">Confirmar</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        const bootstrapModal = new bootstrap.Modal(modal);
        bootstrapModal.show();

        modal.querySelector('#btnConfirmarSi').addEventListener('click', () => {
            bootstrapModal.hide();
            resolver(true);
        });

        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
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

        this.modalAdvertencia = document.createElement('div');
        this.modalAdvertencia.className = 'modal fade';
        this.modalAdvertencia.setAttribute('tabindex', '-1');
        this.modalAdvertencia.setAttribute('data-bs-backdrop', 'static');
        this.modalAdvertencia.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                 viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M12 9v2m0 4v.01"/>
                                <path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"/>
                            </svg>
                            Sesión por expirar
                        </h5>
                    </div>
                    <div class="modal-body">
                        <p>Su sesión expirará en <strong id="tiempoRestante">5 minutos</strong> por inactividad.</p>
                        <p>¿Desea mantener la sesión activa?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="btnMantenerSesion">Mantener sesión activa</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(this.modalAdvertencia);

        const bootstrapModal = new bootstrap.Modal(this.modalAdvertencia);
        bootstrapModal.show();

        this.modalAdvertencia.querySelector('#btnMantenerSesion').addEventListener('click', async () => {
            try {
                await fetchAPI('api/auth.php?action=check');
                bootstrapModal.hide();
                this.reiniciar();
                mostrarNotificacion('Sesión renovada exitosamente', 'success');
            } catch (error) {
                this.cerrarSesion();
            }
        });

        this.modalAdvertencia.addEventListener('hidden.bs.modal', () => {
            this.modalAdvertencia = null;
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
