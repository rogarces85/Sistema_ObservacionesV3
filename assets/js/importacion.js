/**
 * importacion.js - Importación de observaciones desde Excel
 * Maneja drag & drop, vista previa y confirmación de importación
 */

'use strict';

const ImportacionApp = (() => {
    let archivoSeleccionado = null;
    let datosPreview = null;

    const inicializar = () => {
        const zonaCarga = document.getElementById('zonaCarga');
        const inputArchivo = document.getElementById('inputArchivo');
        const btnGenerarPreview = document.getElementById('btnGenerarPreview');
        const btnCambiarArchivo = document.getElementById('btnCambiarArchivo');
        const btnConfirmar = document.getElementById('btnConfirmar');
        const btnCancelar = document.getElementById('btnCancelar');

        // Click en zona de carga abre selector de archivo
        zonaCarga.addEventListener('click', (e) => {
            if (e.target !== btnCambiarArchivo && !btnCambiarArchivo.contains(e.target)) {
                inputArchivo.click();
            }
        });

        // Drag & drop
        zonaCarga.addEventListener('dragover', (e) => {
            e.preventDefault();
            zonaCarga.classList.add('border-primary', 'bg-light');
        });

        zonaCarga.addEventListener('dragleave', () => {
            zonaCarga.classList.remove('border-primary', 'bg-light');
        });

        zonaCarga.addEventListener('drop', (e) => {
            e.preventDefault();
            zonaCarga.classList.remove('border-primary', 'bg-light');
            const archivos = e.dataTransfer.files;
            if (archivos.length > 0) {
                procesarArchivo(archivos[0]);
            }
        });

        // Selección de archivo
        inputArchivo.addEventListener('change', () => {
            if (inputArchivo.files.length > 0) {
                procesarArchivo(inputArchivo.files[0]);
            }
        });

        // Cambiar archivo
        btnCambiarArchivo.addEventListener('click', (e) => {
            e.stopPropagation();
            inputArchivo.value = '';
            inputArchivo.click();
        });

        // Generar preview
        btnGenerarPreview.addEventListener('click', generarPreview);

        // Confirmar importación
        btnConfirmar.addEventListener('click', confirmarImportacion);

        // Cancelar
        btnCancelar.addEventListener('click', reiniciar);
    };

    const procesarArchivo = (archivo) => {
        const extensionesValidas = ['xlsx', 'xls'];
        const extension = archivo.name.split('.').pop().toLowerCase();

        if (!extensionesValidas.includes(extension)) {
            mostrarNotificacion('Solo se permiten archivos Excel (.xlsx, .xls)', 'danger');
            return;
        }

        archivoSeleccionado = archivo;

        // Mostrar nombre y tamaño
        document.getElementById('nombreArchivo').textContent = archivo.name;
        document.getElementById('tamanoArchivo').textContent = formatearTamano(archivo.size);
        document.getElementById('mensajeCarga').classList.add('d-none');
        document.getElementById('archivoSeleccionado').classList.remove('d-none');
        document.getElementById('btnGenerarPreview').disabled = false;
    };

    const formatearTamano = (bytes) => {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    };

    const generarPreview = async () => {
        if (!archivoSeleccionado) {
            mostrarNotificacion('Seleccione un archivo primero', 'warning');
            return;
        }

        const anio = document.getElementById('importAnio').value;
        const formData = new FormData();
        formData.append('archivo', archivoSeleccionado);
        formData.append('anio', anio);

        mostrarCargando(true);
        document.getElementById('btnGenerarPreview').disabled = true;

        try {
            const csrfToken = obtenerCsrfToken();
            const url = API_BASE + 'api/import.php?accion=preview';

            const respuesta = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });

            const datos = await respuesta.json();

            if (!datos.success) {
                mostrarNotificacion(datos.error || 'Error al procesar el archivo', 'danger');
                return;
            }

            datosPreview = datos.data;
            renderizarResultados(datosPreview);
            mostrarNotificacion(`Vista previa generada: ${datosPreview.validas} válidas, ${datosPreview.con_errores} con errores`, 'info');
        } catch (error) {
            console.error('Error al generar preview:', error);
            mostrarNotificacion('Error de conexión al procesar el archivo', 'danger');
        } finally {
            mostrarCargando(false);
            document.getElementById('btnGenerarPreview').disabled = false;
        }
    };

    const renderizarResultados = (datos) => {
        // Resumen
        document.getElementById('resumenTotal').textContent = datos.total_filas;
        document.getElementById('resumenValidas').textContent = datos.validas;
        document.getElementById('resumenErrores').textContent = datos.con_errores;
        document.getElementById('resumenDuplicados').textContent = datos.duplicados_internos + datos.duplicados_bd;
        document.getElementById('seccionResumen').classList.remove('d-none');

        // Tabla de preview
        const cuerpoPreview = document.getElementById('cuerpoPreview');
        if (datos.preview && datos.preview.length > 0) {
            cuerpoPreview.innerHTML = datos.preview.map(fila => `
                <tr>
                    <td><span class="badge bg-secondary">${fila.fila}</span></td>
                    <td>${escapeHtml(fila.establecimiento_nombre)}</td>
                    <td>${obtenerNombreMes(fila.mes)}</td>
                    <td>${escapeHtml(fila.codigo_serie || '-')}</td>
                    <td>${escapeHtml(fila.codigo_hoja || '-')}</td>
                    <td><span class="badge ${obtenerBadgeTipo(fila.tipo_error)}">${escapeHtml(fila.tipo_error)}</span></td>
                    <td class="text-muted text-truncate" style="max-width: 250px;">${escapeHtml(fila.detalle_observacion || '-')}</td>
                    <td>${escapeHtml(fila.plazo_entrega || '-')}</td>
                </tr>
            `).join('');
            document.getElementById('seccionPreview').classList.remove('d-none');
        } else {
            cuerpoPreview.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No hay filas válidas</td></tr>';
            document.getElementById('seccionPreview').classList.remove('d-none');
        }

        // Tabla de errores
        const cuerpoErrores = document.getElementById('cuerpoErrores');
        if (datos.errores && datos.errores.length > 0) {
            cuerpoErrores.innerHTML = datos.errores.map(err => `
                <tr>
                    <td><span class="badge bg-danger">${err.fila}</span></td>
                    <td class="text-danger">${err.errores.map(e => escapeHtml(e)).join('<br>')}</td>
                </tr>
            `).join('');
            document.getElementById('seccionErrores').classList.remove('d-none');
        } else {
            document.getElementById('seccionErrores').classList.add('d-none');
        }

        // Duplicados
        const listaDuplicados = document.getElementById('listaDuplicados');
        const todosDuplicados = [
            ...(datos.duplicados || []).map(d => `<li>Fila ${d.fila}: ${escapeHtml(d.mensaje)}</li>`),
            ...(datos.duplicados_bd || []).map(d => `<li>Fila ${d.fila}: ${escapeHtml(d.mensaje)}</li>`)
        ];

        if (todosDuplicados.length > 0) {
            listaDuplicados.innerHTML = `<ul class="mb-0">${todosDuplicados.join('')}</ul>`;
            document.getElementById('seccionDuplicados').classList.remove('d-none');
        } else {
            document.getElementById('seccionDuplicados').classList.add('d-none');
        }

        // Botones de acción
        document.getElementById('seccionAcciones').classList.remove('d-none');
    };

    const confirmarImportacion = async () => {
        if (!archivoSeleccionado || !datosPreview) {
            mostrarNotificacion('Primero genere la vista previa', 'warning');
            return;
        }

        const confirmado = await confirmarAccion('¿Está seguro de importar las observaciones válidas? Esta acción no se puede deshacer.');
        if (!confirmado) return;

        const anio = document.getElementById('importAnio').value;
        const omitirDuplicados = document.getElementById('chkOmitirDuplicados').checked ? '1' : '0';
        const formData = new FormData();
        formData.append('archivo', archivoSeleccionado);
        formData.append('anio', anio);
        formData.append('omitir_duplicados', omitirDuplicados);

        mostrarCargando(true);
        document.getElementById('btnConfirmar').disabled = true;

        try {
            const csrfToken = obtenerCsrfToken();
            const url = API_BASE + 'api/import.php?accion=confirm';

            const respuesta = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });

            const datos = await respuesta.json();

            if (!datos.success) {
                mostrarNotificacion(datos.error || 'Error al importar', 'danger');
                return;
            }

            mostrarNotificacion(datos.data.mensaje, 'success');
            setTimeout(() => {
                window.location.href = `?page=observaciones&year=${anio}`;
            }, 2000);
        } catch (error) {
            console.error('Error al confirmar importación:', error);
            mostrarNotificacion('Error de conexión al importar', 'danger');
        } finally {
            mostrarCargando(false);
            document.getElementById('btnConfirmar').disabled = false;
        }
    };

    const reiniciar = () => {
        archivoSeleccionado = null;
        datosPreview = null;
        document.getElementById('inputArchivo').value = '';
        document.getElementById('mensajeCarga').classList.remove('d-none');
        document.getElementById('archivoSeleccionado').classList.add('d-none');
        document.getElementById('btnGenerarPreview').disabled = true;
        document.getElementById('seccionResumen').classList.add('d-none');
        document.getElementById('seccionPreview').classList.add('d-none');
        document.getElementById('seccionErrores').classList.add('d-none');
        document.getElementById('seccionDuplicados').classList.add('d-none');
        document.getElementById('seccionAcciones').classList.add('d-none');
    };

    const obtenerBadgeTipo = (tipo) => {
        switch (tipo) {
            case 'S/OBSERVACION': return 'bg-green';
            case 'ERROR': return 'bg-red';
            case 'REVISAR': return 'bg-yellow';
            case 'F/PLAZO': return 'bg-orange';
            default: return 'bg-secondary';
        }
    };

    const escapeHtml = (texto) => {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    };

    const mostrarCargando = (mostrar) => {
        const spinner = document.getElementById('loading-spinner');
        if (mostrar) {
            spinner.classList.remove('d-none');
        } else {
            spinner.classList.add('d-none');
        }
    };

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', inicializar);
    } else {
        inicializar();
    }

    return { reiniciar };
})();
