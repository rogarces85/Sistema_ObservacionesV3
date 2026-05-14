<?php
/**
 * Vista de Asignación de Establecimientos
 * Solo accesible para supervisores - Asignación por año con datos de contacto
 */

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    echo '<div class="p-6 text-center"><h2 class="text-xl font-bold text-rose-600">Acceso Denegado</h2><p>Solo los supervisores pueden acceder a esta sección.</p></div>';
    return;
}

require_once 'models/EstablecimientoAsignacion.php';

$asignacionModel = new EstablecimientoAsignacion();
$anioSeleccionado = $_SESSION['year'] ?? date('Y');
$registradores = $asignacionModel->getEstadisticasAsignaciones($anioSeleccionado);
?>

<div class="space-y-6">
    <!-- Header con selector de año -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Asignación de Establecimientos</h2>
            <p class="text-slate-600">Gestione los establecimientos y referentes por año</p>
        </div>
        <div class="flex gap-3 items-center">
            <label class="text-sm font-semibold text-slate-700">Año:</label>
            <select id="selectorAnio" class="form-select w-28" onchange="cambiarAnio(this.value)">
                <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $anioSeleccionado ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
            <button onclick="copiarAnioAnterior()" class="btn btn-secondary text-sm" title="Copiar asignaciones del año anterior">
                📋 Copiar Año Anterior
            </button>
        </div>
    </div>

    <!-- Layout de dos columnas -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Panel izquierdo: Lista de registradores -->
        <div class="lg:col-span-1">
            <div class="card overflow-hidden">
                <div class="p-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <span>👥</span> Registradores
                    </h3>
                    <p class="text-sm text-slate-500 mt-1">Seleccione un registrador</p>
                </div>
                <div class="overflow-y-auto" style="max-height: 600px;" id="listaRegistradores">
                    <?php if (!empty($registradores)): ?>
                        <?php foreach ($registradores as $reg): ?>
                            <div class="p-4 border-b border-slate-50 hover:bg-slate-50 cursor-pointer transition-colors registrador-item"
                                 onclick="seleccionarRegistrador(<?php echo $reg['id']; ?>, '<?php echo htmlspecialchars($reg['nombre_completo']); ?>')"
                                 data-registrador-id="<?php echo $reg['id']; ?>">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-slate-800 truncate">
                                            <?php echo htmlspecialchars($reg['nombre_completo']); ?>
                                        </p>
                                        <p class="text-xs text-slate-500">
                                            <?php echo htmlspecialchars($reg['username']); ?>
                                        </p>
                                    </div>
                                    <div class="ml-3 text-right">
                                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold rounded-full bg-sky-100 text-sky-700 contador-est">
                                            <?php echo $reg['total_establecimientos']; ?>
                                        </span>
                                        <p class="text-xs text-slate-400 mt-1">establecimientos</p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-8 text-center">
                            <div class="text-4xl mb-3">👤</div>
                            <p class="text-slate-600 font-medium">No hay registradores activos</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel derecho: Establecimientos asignados + Contactos -->
        <div class="lg:col-span-2">
            <div class="card overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <span>🏥</span> Establecimientos y Contactos
                        </h3>
                        <p class="text-sm text-slate-500 mt-1" id="registradorSeleccionadoTexto">
                            Seleccione un registrador
                        </p>
                    </div>
                    <div id="accionesAsignacion" class="hidden gap-2">
                        <button onclick="abrirModalAsignar()" class="btn btn-primary text-sm">
                            ➕ Asignar / Reasignar
                        </button>
                    </div>
                </div>
                
                <div id="establecimientosContainer" class="p-6">
                    <div class="text-center py-12">
                        <div class="text-5xl mb-4">🏢</div>
                        <p class="text-slate-600 font-medium">Seleccione un registrador</p>
                        <p class="text-sm text-slate-400 mt-2">Para ver sus establecimientos y datos de contacto</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar establecimiento -->
<div id="modalAsignar" class="modal-overlay hidden">
    <div class="modal-content" style="max-width: 650px;">
        <div class="modal-header">
            <div>
                <h3 class="text-xl font-bold text-slate-800">Asignar / Reasignar Establecimientos</h3>
                <p class="text-sm text-slate-500" id="modalAsignarInfo"></p>
            </div>
            <button onclick="cerrarModalAsignar()" class="btn-secondary px-3 py-2">✕</button>
        </div>
        <div class="modal-body">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Buscar Establecimiento</label>
                    <input type="text" id="buscarEstablecimiento" 
                           placeholder="Escriba para buscar por nombre o comuna..."
                           oninput="filtrarEstablecimientos()"
                           class="w-full px-4 py-2 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Establecimientos Disponibles</label>
                    <div id="listaEstablecimientosDisponibles" class="border border-slate-200 rounded-lg max-h-64 overflow-y-auto">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Periodo de validez</label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="periodoAsignacion" value="ALL" checked onchange="toggleMesesAsignacion()">
                            <span class="text-sm text-slate-700">Todo el año <span class="text-slate-400" id="anioPeriodoLabel"></span></span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="periodoAsignacion" value="MESES" onchange="toggleMesesAsignacion()">
                            <span class="text-sm text-slate-700">Meses específicos</span>
                        </label>
                        <div id="mesesEspecificosContainer" class="hidden pl-6 pt-1">
                            <div class="grid grid-cols-4 gap-2">
                                <?php
                                $nombresMeses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                                foreach ($nombresMeses as $i => $nombre):
                                    $numero = $i + 1;
                                ?>
                                <label class="flex items-center gap-1.5 text-sm text-slate-700 cursor-pointer hover:bg-slate-50 rounded px-1 py-0.5">
                                    <input type="checkbox" class="mes-checkbox rounded" value="<?php echo $numero; ?>">
                                    <?php echo $nombre; ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <button onclick="guardarAsignaciones()" class="btn btn-primary flex-1">Guardar Asignaciones</button>
                    <button onclick="cerrarModalAsignar()" class="btn btn-secondary">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let registradorSeleccionadoId = null;
    let anioActual = <?php echo $anioSeleccionado; ?>;
    let establecimientosDisponibles = [];
    let establecimientosAsignados = [];

    async function cambiarAnio(anio) {
        anioActual = parseInt(anio);
        
        // Recargar lista de registradores con nuevo año
        try {
            const response = await fetchAPI(`assignments.php?action=list&anio=${anioActual}`);
            if (response.success) {
                renderizarListaRegistradores(response.data);
            }
        } catch (error) {
            console.error('Error al cambiar año:', error);
        }

        // Si hay registrador seleccionado, recargar sus establecimientos
        if (registradorSeleccionadoId) {
            await cargarEstablecimientosAsignados(registradorSeleccionadoId);
        }
    }

    function renderizarListaRegistradores(registradores) {
        const container = document.getElementById('listaRegistradores');
        if (registradores.length === 0) {
            container.innerHTML = '<div class="p-8 text-center"><div class="text-4xl mb-3">👤</div><p class="text-slate-600 font-medium">No hay registradores activos</p></div>';
            return;
        }

        let html = '';
        registradores.forEach(reg => {
            html += `
                <div class="p-4 border-b border-slate-50 hover:bg-slate-50 cursor-pointer transition-colors registrador-item"
                     onclick="seleccionarRegistrador(${reg.id}, '${escapeHtml(reg.nombre_completo)}')"
                     data-registrador-id="${reg.id}">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-slate-800 truncate">${escapeHtml(reg.nombre_completo)}</p>
                            <p class="text-xs text-slate-500">${escapeHtml(reg.username)}</p>
                        </div>
                        <div class="ml-3 text-right">
                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold rounded-full bg-sky-100 text-sky-700 contador-est">
                                ${reg.total_establecimientos}
                            </span>
                            <p class="text-xs text-slate-400 mt-1">establecimientos</p>
                        </div>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    async function seleccionarRegistrador(id, nombre) {
        registradorSeleccionadoId = id;
        
        document.querySelectorAll('.registrador-item').forEach(item => {
            item.classList.remove('bg-sky-50', 'border-l-4', 'border-sky-500');
        });
        const selectedItem = document.querySelector(`[data-registrador-id="${id}"]`);
        if (selectedItem) {
            selectedItem.classList.add('bg-sky-50', 'border-l-4', 'border-sky-500');
        }

        document.getElementById('registradorSeleccionadoTexto').textContent = `Gestionando: ${nombre} — Año ${anioActual}`;
        document.getElementById('accionesAsignacion').classList.remove('hidden');
        document.getElementById('accionesAsignacion').classList.add('flex');

        await cargarEstablecimientosAsignados(id);
    }

    async function cargarEstablecimientosAsignados(registradorId) {
        const container = document.getElementById('establecimientosContainer');
        container.innerHTML = '<div class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-sky-500"></div><p class="mt-2 text-slate-500">Cargando...</p></div>';

        try {
            const response = await fetchAPI(`assignments.php?action=asignados&registrador_id=${registradorId}&anio=${anioActual}`);
            
            if (response.success) {
                establecimientosAsignados = response.data;
                mostrarEstablecimientosConContactos();
            }
        } catch (error) {
            container.innerHTML = '<div class="text-center py-8 text-rose-600">Error al cargar</div>';
        }
    }

    async function mostrarEstablecimientosConContactos() {
        const container = document.getElementById('establecimientosContainer');
        
        if (establecimientosAsignados.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <div class="text-5xl mb-4">📋</div>
                    <p class="text-slate-600 font-medium">No hay establecimientos asignados para ${anioActual}</p>
                    <p class="text-sm text-slate-400 mt-2">Haga clic en "Asignar / Reasignar" para agregar</p>
                </div>
            `;
            return;
        }

        let html = '';
        
        // Agrupar por comuna
        const porComuna = {};
        establecimientosAsignados.forEach(est => {
            if (!porComuna[est.comuna_nombre]) porComuna[est.comuna_nombre] = [];
            porComuna[est.comuna_nombre].push(est);
        });

        Object.keys(porComuna).sort().forEach(comuna => {
            html += `<div class="mb-4">
                <div class="bg-slate-100 px-4 py-2 font-bold text-slate-700 text-sm rounded-t-lg">
                    📍 ${escapeHtml(comuna)}
                </div>`;
            
            porComuna[comuna].forEach(est => {
                html += `
                    <div class="border border-slate-200 border-t-0 bg-white">
                        <div class="flex items-center justify-between p-3 border-b border-slate-100">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-800">${escapeHtml(est.nombre)}</p>
                                <p class="text-xs text-slate-500">${escapeHtml(est.nombre_corto)}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                ${est.meses && est.meses !== 'ALL' ? `<span class="text-xs font-semibold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded" title="${formatearMeses(est.meses)}">⏱️ Temporal</span>` : ''}
                                <button onclick="removerAsignacion(${est.id})"
                                        class="btn-secondary px-3 py-1 text-xs bg-rose-50 hover:bg-rose-100 text-rose-600"
                                        title="Remover">
                                    ✕
                                </button>
                            </div>
                        </div>
                        <div class="referentes-container" data-establecimiento-id="${est.id}">
                            <div class="p-3 text-sm text-slate-400">Cargando contactos...</div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
        });
        
        container.innerHTML = html;

        // Cargar referentes para cada establecimiento
        establecimientosAsignados.forEach(est => {
            cargarReferentes(est.id);
        });
    }

    async function cargarReferentes(establecimientoId) {
        const container = document.querySelector(`.referentes-container[data-establecimiento-id="${establecimientoId}"]`);
        if (!container) return;

        try {
            const response = await fetchAPI(`assignments.php?action=referentes&establecimiento_id=${establecimientoId}`);
            
            if (response.success && response.data.length > 0) {
                let html = '<table class="w-full text-xs"><thead><tr class="text-slate-500"><th class="text-left py-1 px-2">Cargo</th><th class="text-left py-1 px-2">Nombre</th><th class="text-left py-1 px-2">Teléfono</th><th class="text-left py-1 px-2">Email</th></tr></thead><tbody>';
                
                response.data.forEach(ref => {
                    html += `
                        <tr class="border-t border-slate-50 hover:bg-slate-50">
                            <td class="py-1.5 px-2 font-medium text-slate-700">${escapeHtml(ref.cargo)}</td>
                            <td class="py-1.5 px-2 text-slate-800">${escapeHtml(ref.nombre)}</td>
                            <td class="py-1.5 px-2 text-slate-600">${escapeHtml(ref.telefono) || '-'}</td>
                            <td class="py-1.5 px-2 text-sky-600">${escapeHtml(ref.email) || '-'}</td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="p-3 text-xs text-slate-400 italic">Sin contactos registrados</div>';
            }
        } catch (error) {
            container.innerHTML = '';
        }
    }

    async function abrirModalAsignar() {
        if (!registradorSeleccionadoId) {
            showMessage('Seleccione un registrador primero', 'warning');
            return;
        }

        const selectedItem = document.querySelector(`[data-registrador-id="${registradorSeleccionadoId}"]`);
        const nombre = selectedItem.querySelector('.font-semibold').textContent;
        document.getElementById('modalAsignarInfo').textContent = `Para: ${nombre} — Año ${anioActual}`;
        document.getElementById('anioPeriodoLabel').textContent = anioActual;

        await cargarEstablecimientosDisponibles();
        openModal('modalAsignar');
    }

    function toggleMesesAsignacion() {
        const esMeses = document.querySelector('input[name="periodoAsignacion"]:checked').value === 'MESES';
        document.getElementById('mesesEspecificosContainer').classList.toggle('hidden', !esMeses);
    }

    function obtenerMesesSeleccionados() {
        const esMeses = document.querySelector('input[name="periodoAsignacion"]:checked').value === 'MESES';
        if (!esMeses) return 'ALL';
        const checkboxes = document.querySelectorAll('.mes-checkbox:checked');
        const meses = Array.from(checkboxes).map(cb => cb.value);
        return meses.length > 0 ? meses.join(',') : 'ALL';
    }

    function formatearMeses(meses) {
        if (!meses || meses === 'ALL') return 'Todo el año';
        const nombres = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        const nums = meses.split(',').map(m => parseInt(m.trim())).filter(m => m >= 1 && m <= 12).sort((a,b)=>a-b);
        if (nums.length === 0) return 'Todo el año';
        if (nums.length === 12) return 'Todo el año';
        return nums.map(n => nombres[n-1]).join(', ');
    }

    async function cargarEstablecimientosDisponibles() {
        try {
            const response = await fetchAPI(`assignments.php?action=establecimientos&registrador_id=${registradorSeleccionadoId}&anio=${anioActual}`);
            if (response.success) {
                establecimientosDisponibles = response.data;
                mostrarEstablecimientosDisponiblesAgrupados();
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }

    function mostrarEstablecimientosDisponiblesAgrupados(filtro = '') {
        const container = document.getElementById('listaEstablecimientosDisponibles');
        const filtroLower = filtro.toLowerCase();
        
        const agrupados = {};
        establecimientosDisponibles.forEach(est => {
            if (filtro && !est.nombre.toLowerCase().includes(filtroLower) && 
                !est.nombre_corto.toLowerCase().includes(filtroLower) &&
                !est.comuna_nombre.toLowerCase().includes(filtroLower)) {
                return;
            }

            if (!agrupados[est.comuna_nombre]) agrupados[est.comuna_nombre] = [];
            agrupados[est.comuna_nombre].push(est);
        });

        let html = '';
        Object.keys(agrupados).sort().forEach(comuna => {
            html += `<div class="border-b border-slate-200 last:border-b-0">
                <div class="bg-slate-100 px-3 py-2 font-semibold text-slate-700 text-sm sticky top-0">
                    📍 ${escapeHtml(comuna)}
                </div>`;
            
            // Ordenar: libres primero, luego asignados a mi, luego asignados a otros
            agrupados[comuna].sort((a, b) => {
                const score = (est) => {
                    if (est.asignado_a_mi == 1) return 1;
                    if (est.asignado_a_usuario_id) return 2;
                    return 0;
                };
                return score(a) - score(b);
            });

            agrupados[comuna].forEach(est => {
                const asignadoAMi = est.asignado_a_mi == 1;
                const asignadoAOtroTotal = est.asignado_a_usuario_id && (!est.meses_otro || est.meses_otro === 'ALL');
                const asignadoAOtroParcial = est.asignado_a_usuario_id && est.meses_otro && est.meses_otro !== 'ALL';

                // Ya asignado a mi: mostrar marcado pero deshabilitado (no se puede quitar desde este modal)
                const checked = asignadoAMi ? 'checked' : '';
                const disabled = (asignadoAMi || asignadoAOtroTotal) ? 'disabled' : '';

                let bgClass = '';
                let badge = '';
                if (asignadoAMi) {
                    bgClass = 'bg-sky-50';
                    const mesesTexto = est.meses_mios && est.meses_mios !== 'ALL' ? ` (${formatearMeses(est.meses_mios)})` : '';
                    badge = `<span class="text-xs font-semibold text-sky-600 bg-sky-100 px-1.5 py-0.5 rounded">Asignado a ti${mesesTexto}</span>`;
                } else if (asignadoAOtroTotal) {
                    bgClass = 'bg-rose-50';
                    badge = `<span class="text-xs font-semibold text-rose-600 bg-rose-100 px-1.5 py-0.5 rounded">Asignado a: ${escapeHtml(est.asignado_a_nombre || 'Otro')}</span>`;
                } else if (asignadoAOtroParcial) {
                    bgClass = 'bg-amber-50';
                    badge = `<span class="text-xs font-semibold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded" title="Puedes asignar meses que no se solapen">Parcial: ${escapeHtml(est.asignado_a_nombre || 'Otro')} (${formatearMeses(est.meses_otro)})</span>`;
                }

                const allowHover = !asignadoAMi && !asignadoAOtroTotal;
                html += `
                    <label class="flex items-center gap-3 p-3 border-b border-slate-50 ${bgClass} ${allowHover ? 'hover:bg-slate-50 cursor-pointer' : ''}">
                        <input type="checkbox" value="${est.id}" class="establecimiento-checkbox rounded" ${checked} ${disabled}>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-slate-800 text-sm">${escapeHtml(est.nombre)}</p>
                            <p class="text-xs text-slate-500">${escapeHtml(est.nombre_corto)}</p>
                        </div>
                        ${badge}
                    </label>
                `;
            });
            
            html += '</div>';
        });

        container.innerHTML = html || '<p class="p-4 text-center text-slate-500">No se encontraron</p>';
    }

    function filtrarEstablecimientos() {
        const filtro = document.getElementById('buscarEstablecimiento').value;
        mostrarEstablecimientosDisponiblesAgrupados(filtro);
    }

    async function guardarAsignaciones() {
        // Solo guardar los checkeados que NO estén deshabilitados (asignados a otros con ALL)
        const checkboxes = document.querySelectorAll('.establecimiento-checkbox:checked:not(:disabled)');
        const establecimientoIds = Array.from(checkboxes).map(cb => parseInt(cb.value));

        if (establecimientoIds.length === 0) {
            showMessage('Seleccione al menos un establecimiento', 'warning');
            return;
        }

        const meses = obtenerMesesSeleccionados();
        if (meses !== 'ALL') {
            const mesesArray = meses.split(',');
            if (mesesArray.length === 0) {
                showMessage('Seleccione al menos un mes o elija "Todo el año"', 'warning');
                return;
            }
        }

        try {
            showLoading();

            const response = await fetchAPI('assignments.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'asignar_multiple',
                    usuario_id: registradorSeleccionadoId,
                    establecimiento_ids: establecimientoIds,
                    anio: anioActual,
                    meses: meses
                })
            });

            hideLoading();

            if (response.success) {
                showMessage('Establecimientos asignados exitosamente', 'success');
                cerrarModalAsignar();
                await cargarEstablecimientosAsignados(registradorSeleccionadoId);
                await cambiarAnio(anioActual);
            }
        } catch (error) {
            hideLoading();
            showMessage(error.message, 'error');
        }
    }

    async function removerAsignacion(establecimientoId) {
        if (!confirm('¿Remover este establecimiento del registrador?')) return;

        try {
            showLoading();

            const response = await fetchAPI('assignments.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'remover',
                    usuario_id: registradorSeleccionadoId,
                    establecimiento_id: establecimientoId,
                    anio: anioActual
                })
            });

            hideLoading();

            if (response.success) {
                showMessage('Asignación removida', 'success');
                await cargarEstablecimientosAsignados(registradorSeleccionadoId);
                await cambiarAnio(anioActual);
            }
        } catch (error) {
            hideLoading();
            showMessage(error.message, 'error');
        }
    }

    async function copiarAnioAnterior() {
        const anioOrigen = anioActual - 1;
        if (!confirm(`¿Copiar todas las asignaciones del año ${anioOrigen} al año ${anioActual}?`)) return;

        try {
            showLoading();

            const response = await fetchAPI('assignments.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'copiar_anio',
                    anio_origen: anioOrigen,
                    anio_destino: anioActual
                })
            });

            hideLoading();

            if (response.success) {
                showMessage(response.message, 'success');
                await cambiarAnio(anioActual);
                if (registradorSeleccionadoId) {
                    await cargarEstablecimientosAsignados(registradorSeleccionadoId);
                }
            }
        } catch (error) {
            hideLoading();
            showMessage(error.message, 'error');
        }
    }

    function cerrarModalAsignar() {
        closeModal('modalAsignar');
        document.getElementById('buscarEstablecimiento').value = '';
        document.querySelector('input[name="periodoAsignacion"][value="ALL"]').checked = true;
        document.querySelectorAll('.mes-checkbox').forEach(cb => cb.checked = false);
        toggleMesesAsignacion();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
