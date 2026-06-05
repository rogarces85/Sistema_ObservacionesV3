/**
 * versionado.js - Módulo de Versionado y Snapshots
 * Fase 11 - Crear snapshots, ver detalle, restaurar versiones
 * Solo accesible para rol Supervisor
 */

'use strict';

const VersionadoApp = (() => {
    'use strict';

    let versiones = [];
    let versionSeleccionada = null;

    function iniciar() {
        configurarEventos();
        cargarVersiones();
    }

    function configurarEventos() {
        document.getElementById('btnCrearVersion').addEventListener('click', abrirModalCrear);
        document.getElementById('formCrearVersion').addEventListener('submit', confirmarCreacion);
        document.getElementById('btnConfirmarRestaurar').addEventListener('click', confirmarRestauracion);
        document.getElementById('confirmarRestauracion').addEventListener('change', function() {
            document.getElementById('btnConfirmarRestaurar').disabled = !this.checked;
        });
    }

    async function cargarVersiones() {
        const cuerpo = document.getElementById('cuerpoTablaVersiones');
        cuerpo.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Cargando versiones...</td></tr>';

        try {
            const respuesta = await fetchAPI('api/versiones.php?accion=listar');
            if (respuesta.success) {
                versiones = respuesta.data || [];
                renderizarTabla(versiones);
                actualizarEstadisticas(versiones);
            } else {
                throw new Error(respuesta.error || 'Error al cargar versiones');
            }
        } catch (error) {
            console.error('Error al cargar versiones:', error);
            cuerpo.innerHTML = `<tr><td colspan="6" class="text-center text-danger py-4">${escaparHtml(error.message)}</td></tr>`;
        }
    }

    function renderizarTabla(listaVersiones) {
        const cuerpo = document.getElementById('cuerpoTablaVersiones');

        if (listaVersiones.length === 0) {
            cuerpo.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hay versiones creadas aún. Cree el primer snapshot.</td></tr>';
            return;
        }

        cuerpo.innerHTML = listaVersiones.map(v => {
            const manifiesto = v.archivos_json ? JSON.parse(v.archivos_json) : {};
            const totalArchivos = Object.keys(manifiesto).length;
            const esRollback = v.descripcion && v.descripcion.toLowerCase().includes('rollback');

            return `
                <tr>
                    <td>
                        <span class="badge ${esRollback ? 'bg-warning-lt' : 'bg-primary-lt'}">
                            ${escaparHtml(v.version_tag)}
                        </span>
                        ${esRollback ? '<span class="badge bg-warning-lt ms-1">Rollback</span>' : ''}
                    </td>
                    <td>
                        <div class="fw-medium">${escaparHtml(v.descripcion || '-')}</div>
                    </td>
                    <td>${escaparHtml(v.autor_nombre || 'Desconocido')}</td>
                    <td>
                        <span class="badge bg-secondary-lt">${totalArchivos} archivos</span>
                    </td>
                    <td>
                        <span class="text-muted small">${formatearFechaHora(v.fecha_creacion)}</span>
                    </td>
                    <td class="text-end">
                        <div class="btn-list flex-nowrap justify-content-end">
                            <button class="btn btn-icon btn-sm" onclick="VersionadoApp.verDetalle(${v.id})" title="Ver Detalle">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"/><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"/></svg>
                            </button>
                            <button class="btn btn-icon btn-sm btn-warning" onclick="VersionadoApp.abrirModalRestaurar(${v.id})" title="Restaurar esta versión">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 13l-4 -4l4 -4m-4 4h11a4 4 0 0 1 0 8m-1 0l1 1"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    function abrirModal(nombre) {
        document.getElementById(`modal${nombre}Backdrop`).classList.add('show');
        document.getElementById(`modal${nombre}`).classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModal(nombre) {
        document.getElementById(`modal${nombre}Backdrop`).classList.remove('show');
        document.getElementById(`modal${nombre}`).classList.remove('show');
        document.body.style.overflow = '';
    }

    function actualizarEstadisticas(listaVersiones) {
        document.getElementById('statTotalVersiones').textContent = listaVersiones.length;

        if (listaVersiones.length > 0) {
            const ultima = listaVersiones[0];
            document.getElementById('statUltimaVersion').textContent = ultima.version_tag;

            const manifiesto = ultima.archivos_json ? JSON.parse(ultima.archivos_json) : {};
            const totalArchivos = Object.keys(manifiesto).length;
            document.getElementById('statTotalArchivos').textContent = totalArchivos;
        } else {
            document.getElementById('statUltimaVersion').textContent = '-';
            document.getElementById('statTotalArchivos').textContent = '-';
        }
    }

    function abrirModalCrear() {
        document.getElementById('crearDescripcion').value = '';
        abrirModal('CrearVersion');
    }

    async function confirmarCreacion(e) {
        e.preventDefault();

        const descripcion = document.getElementById('crearDescripcion').value.trim();
        if (!descripcion) {
            showError('La descripción es obligatoria');
            return;
        }

        const btn = document.getElementById('btnConfirmarCrear');
        btn.disabled = true;

        try {
            const respuesta = await fetchAPI('api/versiones.php?accion=crear', {
                method: 'POST',
                body: JSON.stringify({ descripcion })
            });

            if (respuesta.success) {
                showSuccess(respuesta.data ? `Snapshot ${respuesta.data.version_tag} creado exitosamente (${respuesta.data.archivos_copiados} archivos)` : 'Snapshot creado exitosamente');
                cerrarModal('CrearVersion');
                cargarVersiones();
            } else {
                throw new Error(respuesta.error || 'Error al crear el snapshot');
            }
        } catch (error) {
            showError('Error: ' + error.message);
        } finally {
            btn.disabled = false;
        }
    }

    async function verDetalle(id) {
        try {
            const respuesta = await fetchAPI(`api/versiones.php?accion=detalle&id=${id}`);
            if (respuesta.success) {
                mostrarModalDetalle(respuesta.data);
            } else {
                throw new Error(respuesta.error || 'Error al cargar detalle');
            }
        } catch (error) {
            showError('Error al cargar detalle: ' + error.message);
        }
    }

    function mostrarModalDetalle(version) {
        document.getElementById('detVersionTag').textContent = version.version_tag;
        document.getElementById('detDescripcion').textContent = version.descripcion || '-';
        document.getElementById('detAutor').textContent = version.autor_nombre || 'Desconocido';
        document.getElementById('detFechaCreacion').textContent = formatearFechaHora(version.fecha_creacion);

        const manifiesto = version.manifiesto || {};
        const totalArchivos = Object.keys(manifiesto).length;
        document.getElementById('detTotalArchivos').textContent = `${totalArchivos} archivos`;

        const cuerpoManifiesto = document.getElementById('cuerpoManifiesto');

        if (totalArchivos === 0) {
            cuerpoManifiesto.innerHTML = '<tr><td colspan="3" class="text-center text-muted">Sin archivos en el manifiesto</td></tr>';
        } else {
            const archivosOrdenados = Object.values(manifiesto).sort((a, b) => a.ruta.localeCompare(b.ruta));

            cuerpoManifiesto.innerHTML = archivosOrdenados.map(archivo => {
                const tamano = formatearTamano(archivo.tamano || 0);
                return `
                    <tr>
                        <td><code class="small">${escaparHtml(archivo.ruta)}</code></td>
                        <td><code class="small text-muted">${escaparHtml(archivo.md5)}</code></td>
                        <td class="text-muted small">${tamano}</td>
                    </tr>
                `;
            }).join('');
        }

        abrirModal('DetalleVersion');
    }

    function abrirModalRestaurar(id) {
        const version = versiones.find(v => v.id === id);
        if (!version) {
            showError('Versión no encontrada');
            return;
        }

        versionSeleccionada = version;
        document.getElementById('restVersionTag').textContent = version.version_tag;
        document.getElementById('confirmarRestauracion').checked = false;
        document.getElementById('btnConfirmarRestaurar').disabled = true;

        abrirModal('Restaurar');
    }

    async function confirmarRestauracion() {
        if (!versionSeleccionada) {
            showError('No hay versión seleccionada');
            return;
        }

        const btn = document.getElementById('btnConfirmarRestaurar');
        btn.disabled = true;

        try {
            const respuesta = await fetchAPI(`api/versiones.php?accion=restaurar&id=${versionSeleccionada.id}`, {
                method: 'POST'
            });

            if (respuesta.success) {
                const datos = respuesta.data;
                let mensaje = `Restauración desde ${datos.version_tag_origen} completada (${datos.archivos_restaurados} archivos restaurados)`;

                if (datos.archivos_fallidos && datos.archivos_fallidos.length > 0) {
                    mensaje += `. ADVERTENCIA: ${datos.archivos_fallidos.length} archivo(s) no se pudieron restaurar. El supervisor debe verificar y reintentar manualmente.`;
                    showWarning(mensaje);
                } else {
                    showSuccess(mensaje);
                }

                if (datos.advertencia_bd) {
                    showWarning('Recordatorio: ' + datos.advertencia_bd);
                }

                cerrarModal('Restaurar');
                cargarVersiones();
            } else {
                throw new Error(respuesta.error || 'Error al ejecutar la restauración');
            }
        } catch (error) {
            showError('Error: ' + error.message);
        } finally {
            btn.disabled = false;
        }
    }

    function formatearTamano(bytes) {
        if (bytes === 0) return '0 B';
        const unidades = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + unidades[i];
    }

    function escaparHtml(texto) {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    return {
        iniciar,
        cargarVersiones,
        verDetalle,
        abrirModalRestaurar,
        cerrarModal
    };
})();

window.VersionadoApp = VersionadoApp;

document.addEventListener('DOMContentLoaded', () => {
    VersionadoApp.iniciar();
});
