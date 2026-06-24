<?php
/**
 * Vista de Asignación de Establecimientos
 * Solo accesible para supervisores - Asignación por año con datos de contacto
 */

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    echo '<div class="page-wrapper"><div class="page-body"><div class="container-xl"><div class="empty"><div class="empty-header text-danger">403</div><p class="empty-title">Acceso Denegado</p><p class="empty-subtitle text-secondary">Solo los supervisores pueden acceder a esta sección.</p></div></div></div></div>';
    return;
}

require_once 'models/EstablecimientoAsignacion.php';

$asignacionModel = new EstablecimientoAsignacion();
$anioSeleccionado = $_SESSION['year'] ?? date('Y');
$registradores = $asignacionModel->getEstadisticasAsignaciones($anioSeleccionado);
?>

<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">

                <!-- Header -->
                <div class="col-12">
                    <div class="mb-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="page-title"><i class="ti ti-package me-2 text-primary"></i>Asignación de Establecimientos</h2>
                            <div class="text-secondary">Gestione los establecimientos y referentes por año</div>
                        </div>
                        <div class="btn-list">
                            <label class="form-label d-inline me-2 mb-0">Año:</label>
                            <select id="selectorAnio" class="form-select d-inline w-auto" onchange="cambiarAnio(this.value)">
                                <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                                    <option value="<?php echo $y; ?>" <?php echo $y == $anioSeleccionado ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                            <button onclick="copiarAnioAnterior()" class="btn btn-outline-secondary" title="Copiar asignaciones del año anterior">
                                <i class="ti ti-copy me-1"></i>Copiar Año Anterior
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Layout de dos columnas -->
                <div class="col-12">
                    <div class="row g-4">
                        <!-- Panel izquierdo: Lista de registradores -->
                        <div class="col-12 col-lg-4">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Registradores</h3>
                                    <div class="card-subtitle">Seleccione un registrador</div>
                                </div>
                                <div class="list-group list-group-flush" style="max-height: 600px; overflow-y: auto;" id="listaRegistradores">
                                    <?php if (!empty($registradores)): ?>
                                        <?php foreach ($registradores as $reg): ?>
                                            <div class="list-group-item list-group-item-action registrador-item"
                                                 onclick="seleccionarRegistrador(<?php echo $reg['id']; ?>, '<?php echo htmlspecialchars($reg['nombre_completo']); ?>')"
                                                 data-registrador-id="<?php echo $reg['id']; ?>">
                                                <div class="row align-items-center">
                                                    <div class="col">
                                                        <div class="fw-semibold text-truncate"><?php echo htmlspecialchars($reg['nombre_completo']); ?></div>
                                                        <div class="text-secondary text-sm"><?php echo htmlspecialchars($reg['username']); ?></div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <span class="badge bg-azure-lt px-2 py-1"><?php echo $reg['total_establecimientos']; ?></span>
                                                        <div class="text-secondary text-xs mt-1">establecimientos</div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="empty p-6">
                                            <p class="empty-title">No hay registradores activos</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Panel derecho: Establecimientos asignados + Contactos -->
                        <div class="col-12 col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <div>
                                        <h3 class="card-title">Establecimientos y Contactos</h3>
                                        <p class="card-subtitle" id="registradorSeleccionadoTexto">Seleccione un registrador</p>
                                    </div>
                                    <div id="accionesAsignacion" class="d-none">
                                        <button onclick="abrirModalAsignar()" class="btn btn-primary">
                                            <i class="ti ti-plus me-1"></i>Asignar / Reasignar
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body" id="establecimientosContainer">
                                    <div class="empty-state">
                                        <div class="empty-icon"><i class="ti ti-building"></i></div>
                                        <h3>Seleccione un registrador</h3>
                                        <p>Para ver sus establecimientos y datos de contacto</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sección: Reasignaciones Temporales Activas -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Reasignaciones Temporales Activas</h3>
                            <div class="card-subtitle">Establecimientos reasignados temporalmente para <?php echo $anioSeleccionado; ?></div>
                        </div>
                        <div class="card-body" id="reasignacionesTemporalesContainer">
                            <div class="placeholder-glow">
                                <span class="placeholder col-12 mb-2"></span>
                                <span class="placeholder col-10 mb-2"></span>
                                <span class="placeholder col-11"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar establecimiento (Bootstrap) -->
<div id="modalAsignar" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title"><i class="ti ti-package me-2 text-primary"></i>Asignar / Reasignar Establecimientos</h5>
                    <div class="text-secondary" id="modalAsignarInfo"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Tipo de Asignación</label>
                    <div class="space-y-2">
                        <label class="form-selectgroup-item p-3 border rounded cursor-pointer">
                            <input type="radio" name="tipoAsignacion" value="anual" class="form-check-input me-2" checked onchange="toggleTipoAsignacion()">
                            Anual <span class="text-secondary">— Asignación base para todo el año</span>
                        </label>
                        <label class="form-selectgroup-item p-3 border rounded cursor-pointer">
                            <input type="radio" name="tipoAsignacion" value="temporal" class="form-check-input me-2" onchange="toggleTipoAsignacion()">
                            Temporal <span class="text-secondary">— Reasignación por meses específicos</span>
                        </label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Buscar Establecimiento</label>
                    <input type="text" id="buscarEstablecimiento" class="form-control"
                           placeholder="Escriba para buscar por nombre o comuna..."
                           oninput="filtrarEstablecimientos()">
                </div>
                <div class="mb-3">
                    <label class="form-label">Establecimientos Disponibles</label>
                    <div id="listaEstablecimientosDisponibles" class="border rounded" style="max-height: 260px; overflow-y: auto;"></div>
                </div>
                <div id="periodoContainer" class="mb-3">
                    <label class="form-label">Periodo de validez</label>
                    <div class="space-y-2">
                        <label class="form-check">
                            <input type="radio" name="periodoAsignacion" value="ALL" class="form-check-input" checked onchange="toggleMesesAsignacion()">
                            <span class="form-check-label">Todo el año <span class="text-secondary" id="anioPeriodoLabel"></span></span>
                        </label>
                        <label class="form-check">
                            <input type="radio" name="periodoAsignacion" value="MESES" class="form-check-input" onchange="toggleMesesAsignacion()">
                            <span class="form-check-label">Meses específicos</span>
                        </label>
                        <div id="mesesEspecificosContainer" class="d-none ms-4 mt-2">
                            <div class="row g-2">
                                <?php
                                $nombresMeses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                                foreach ($nombresMeses as $i => $nombre):
                                    $numero = $i + 1;
                                ?>
                                <div class="col-3">
                                    <label class="form-check">
                                        <input type="checkbox" class="form-check-input mes-checkbox" value="<?php echo $numero; ?>">
                                        <span class="form-check-label"><?php echo $nombre; ?></span>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal" onclick="cerrarModalAsignar()">
                    <i class="ti ti-x me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="guardarAsignaciones()">
                    <i class="ti ti-device-floppy me-1"></i>Guardar Asignaciones
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let registradorSeleccionadoId = null;
    let registradorSeleccionadoNombre = '';
    let anioActual = <?php echo $anioSeleccionado; ?>;
    let establecimientosDisponibles = [];
    let establecimientosAsignados = [];
    const modalAsignar = new bootstrap.Modal(document.getElementById('modalAsignar'));

    document.addEventListener('DOMContentLoaded', function() {
        cargarReasignacionesTemporales();
    });

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

        // Recargar reasignaciones temporales
        await cargarReasignacionesTemporales();
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
        registradorSeleccionadoNombre = nombre || '';

        document.querySelectorAll('.registrador-item').forEach(item => {
            item.classList.remove('bg-sky-50', 'border-l-4', 'border-sky-500');
        });
        const selectedItem = document.querySelector(`[data-registrador-id="${id}"]`);
        if (selectedItem) {
            selectedItem.classList.add('bg-sky-50', 'border-l-4', 'border-sky-500');
        }

        document.getElementById('registradorSeleccionadoTexto').textContent = `Gestionando: ${nombre} — Año ${anioActual}`;
        document.getElementById('accionesAsignacion').classList.remove('d-none');

        await cargarEstablecimientosAsignados(id);
    }

    async function cargarEstablecimientosAsignados(registradorId) {
        const container = document.getElementById('establecimientosContainer');
        container.innerHTML = '<div class="placeholder-glow p-3"><span class="placeholder col-12 mb-2"></span><span class="placeholder col-10 mb-2"></span><span class="placeholder col-8"></span></div>';

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
                // Determinar badge de tipo
                let tipoBadge = '';
                if (est.tipo_asignacion === 'temporal' || (est.meses && est.meses !== 'ALL')) {
                    const mesesTexto = est.meses && est.meses !== 'ALL' ? formatearMeses(est.meses) : 'Todo el año';
                    tipoBadge = `<span class="text-xs font-semibold text-amber-700 bg-amber-100 px-2 py-0.5 rounded" title="Reasignación temporal: ${mesesTexto}">⏱️ Temporal</span>`;
                } else {
                    tipoBadge = `<span class="text-xs font-semibold text-sky-700 bg-sky-100 px-2 py-0.5 rounded" title="Asignación anual">📅 Anual</span>`;
                }

                html += `
                    <div class="border border-slate-200 border-t-0 bg-white">
                        <div class="flex items-center justify-between p-3 border-b border-slate-100">
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-800">${escapeHtml(est.nombre)}</p>
                                <p class="text-xs text-slate-500">${escapeHtml(est.nombre_corto)}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                ${tipoBadge}
                                <button onclick="removerAsignacion(${est.id}, '${est.tipo_asignacion || 'anual'}')"
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
                let html = '<div class="table-responsive"><table class="table table-vcenter table-hover"><thead><tr><th>Cargo</th><th>Nombre</th><th>Teléfono</th><th>Email</th></tr></thead><tbody>';
                
                response.data.forEach(ref => {
                    html += `
                        <tr>
                            <td class="fw-semibold">${escapeHtml(ref.cargo)}</td>
                            <td>${escapeHtml(ref.nombre)}</td>
                            <td class="text-secondary">${escapeHtml(ref.telefono) || '-'}</td>
                            <td class="text-primary">${escapeHtml(ref.email) || '-'}</td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table></div>';
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

        let nombre = registradorSeleccionadoNombre;
        if (!nombre) {
            const selectedItem = document.querySelector(`[data-registrador-id="${registradorSeleccionadoId}"]`);
            if (selectedItem) {
                const nameEl = selectedItem.querySelector('.fw-semibold') || selectedItem.querySelector('.font-semibold');
                nombre = nameEl ? nameEl.textContent.trim() : 'Registrador';
            } else {
                nombre = 'Registrador';
            }
            registradorSeleccionadoNombre = nombre;
        }
        document.getElementById('modalAsignarInfo').textContent = `Para: ${nombre} — Año ${anioActual}`;
        document.getElementById('anioPeriodoLabel').textContent = anioActual;

        await cargarEstablecimientosDisponibles();
        modalAsignar.show();
    }

    function toggleMesesAsignacion() {
        const esMeses = document.querySelector('input[name="periodoAsignacion"]:checked').value === 'MESES';
        document.getElementById('mesesEspecificosContainer').classList.toggle('d-none', !esMeses);
    }

    function toggleTipoAsignacion() {
        const esTemporal = document.querySelector('input[name="tipoAsignacion"]:checked').value === 'temporal';
        const periodoContainer = document.getElementById('periodoContainer');
        
        if (esTemporal) {
            // Forzar selección de meses específicos para temporal
            document.querySelector('input[name="periodoAsignacion"][value="MESES"]').checked = true;
            document.getElementById('mesesEspecificosContainer').classList.remove('d-none');
            // Deshabilitar opción "Todo el año" para temporal
            document.querySelector('input[name="periodoAsignacion"][value="ALL"]').disabled = true;
            document.querySelector('input[name="periodoAsignacion"][value="ALL"]').parentElement.style.opacity = '0.5';
        } else {
            // Habilitar opción "Todo el año" para anual
            document.querySelector('input[name="periodoAsignacion"][value="ALL"]').disabled = false;
            document.querySelector('input[name="periodoAsignacion"][value="ALL"]').parentElement.style.opacity = '1';
        }
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
                    const tipoMi = est.tipo_asignacion_mi || 'anual';
                    const mesesTexto = est.meses_mios && est.meses_mios !== 'ALL' ? ` (${formatearMeses(est.meses_mios)})` : '';
                    const tipoIcon = tipoMi === 'temporal' ? '⏱️' : '📅';
                    const tipoLabel = tipoMi === 'temporal' ? 'Temporal' : 'Anual';
                    badge = `<span class="text-xs font-semibold text-sky-700 bg-sky-100 px-1.5 py-0.5 rounded">${tipoIcon} ${tipoLabel}${mesesTexto}</span>`;
                } else if (asignadoAOtroTotal) {
                    bgClass = 'bg-rose-50';
                    const tipoOtro = est.tipo_asignacion_otro || 'anual';
                    const tipoIcon = tipoOtro === 'temporal' ? '⏱️' : '📅';
                    badge = `<span class="text-xs font-semibold text-rose-600 bg-rose-100 px-1.5 py-0.5 rounded">${tipoIcon} Asignado a: ${escapeHtml(est.asignado_a_nombre || 'Otro')}</span>`;
                } else if (asignadoAOtroParcial) {
                    bgClass = 'bg-amber-50';
                    const tipoOtro = est.tipo_asignacion_otro || 'anual';
                    const tipoIcon = tipoOtro === 'temporal' ? '⏱️' : '📅';
                    badge = `<span class="text-xs font-semibold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded" title="Puedes asignar meses que no se solapen">${tipoIcon} Parcial: ${escapeHtml(est.asignado_a_nombre || 'Otro')} (${formatearMeses(est.meses_otro)})</span>`;
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
        const saveButton = document.querySelector('#modalAsignar .modal-footer .btn-primary');
        if (saveButton && saveButton.disabled) return;

        // Solo guardar los checkeados que NO estén deshabilitados (asignados a otros con ALL)
        const checkboxes = document.querySelectorAll('.establecimiento-checkbox:checked:not(:disabled)');
        const establecimientoIds = Array.from(checkboxes).map(cb => parseInt(cb.value));

        if (establecimientoIds.length === 0) {
            showMessage('Seleccione al menos un establecimiento', 'warning');
            return;
        }

        // Obtener tipo de asignación
        const tipoAsignacion = document.querySelector('input[name="tipoAsignacion"]:checked').value;

        // Obtener meses
        const meses = obtenerMesesSeleccionados();

        // Validar que temporal tenga meses específicos
        if (tipoAsignacion === 'temporal' && (meses === 'ALL' || !meses)) {
            showMessage('Para asignación temporal debe seleccionar meses específicos', 'warning');
            return;
        }

        if (meses !== 'ALL') {
            const mesesArray = meses.split(',');
            if (mesesArray.length === 0) {
                showMessage('Seleccione al menos un mes', 'warning');
                return;
            }
        }

        try {
            showLoading();
            if (saveButton) saveButton.disabled = true;

            const response = await fetchAPI('assignments.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'asignar_multiple',
                    usuario_id: registradorSeleccionadoId,
                    establecimiento_ids: establecimientoIds,
                    anio: anioActual,
                    meses: meses,
                    tipo_asignacion: tipoAsignacion
                })
            });

            if (response.success) {
                showMessage(response.message, 'success');
                cerrarModalAsignar();
                await cargarEstablecimientosAsignados(registradorSeleccionadoId);
                await cambiarAnio(anioActual);
            } else {
                showMessage(response.message || 'No se pudieron guardar las asignaciones', 'error');
            }
        } catch (error) {
            showMessage(error.message, 'error');
        } finally {
            hideLoading();
            if (saveButton) saveButton.disabled = false;
        }
    }

    async function removerAsignacion(establecimientoId, tipo = 'anual') {
        const tipoLabel = tipo === 'temporal' ? 'reasignación temporal' : 'asignación';
        const confirmed = await remConfirm({
            title: 'Remover asignación',
            message: `¿Remover esta ${tipoLabel} del registrador?`,
            confirmText: 'Remover',
            cancelText: 'Cancelar',
            danger: true,
        });
        if (!confirmed) return;

        try {
            showLoading();

            const response = await fetchAPI('assignments.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'remover',
                    usuario_id: registradorSeleccionadoId,
                    establecimiento_id: establecimientoId,
                    anio: anioActual,
                    tipo_asignacion: tipo
                })
            });

            hideLoading();

            if (response.success) {
                showMessage(response.message, 'success');
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
        const confirmed = await remConfirm({
            title: 'Copiar año anterior',
            message: `¿Copiar todas las asignaciones del año ${anioOrigen} al año ${anioActual}? Esta operacion no afecta asignaciones del año origen.`,
            confirmText: 'Copiar',
            cancelText: 'Cancelar',
        });
        if (!confirmed) return;

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

    async function cargarReasignacionesTemporales() {
        const container = document.getElementById('reasignacionesTemporalesContainer');
        
        try {
            const response = await fetchAPI(`assignments.php?action=temporales&anio=${anioActual}`);
            
            if (response.success && response.data.length > 0) {
                    let html = `
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table table-hover">
                                <thead>
                                    <tr>
                                        <th>Establecimiento</th>
                                        <th>Comuna</th>
                                        <th>Titular Anual</th>
                                        <th>Reasignado a</th>
                                        <th>Meses</th>
                                        <th>Fecha</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                
                response.data.forEach(temp => {
                    const titularNombre = temp.titular_anual ? escapeHtml(temp.titular_anual.nombre_completo) : '<span class="text-slate-400 italic">Sin titular anual</span>';
                    const mesesTexto = formatearMeses(temp.meses);
                    const fecha = new Date(temp.fecha_asignacion).toLocaleDateString('es-CL');
                    
                    html += `
                        <tr>
                            <td>
                                <div class="fw-semibold">${escapeHtml(temp.establecimiento_nombre)}</div>
                                <div class="text-secondary text-sm">${escapeHtml(temp.codigo_establecimiento)}</div>
                            </td>
                            <td class="text-secondary">${escapeHtml(temp.comuna_nombre)}</td>
                            <td>${titularNombre}</td>
                            <td><span class="fw-semibold text-warning">${escapeHtml(temp.registrador_nombre)}</span></td>
                            <td>
                                <span class="badge bg-warning-lt">${mesesTexto}</span>
                            </td>
                            <td class="text-secondary">${fecha}</td>
                            <td class="text-end">
                                <button onclick="removerReasignacionTemporal(${temp.id}, ${temp.establecimiento_id}, ${temp.registrador_id})"
                                        class="btn btn-sm btn-outline-danger">
                                    Remover
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                html += `
                            </tbody>
                        </table>
                    </div>
                `;
                
                container.innerHTML = html;
            } else {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-4xl mb-3">✅</div>
                        <p class="text-slate-600 font-medium">No hay reasignaciones temporales activas</p>
                        <p class="text-sm text-slate-400 mt-1">Todas las asignaciones son anuales</p>
                    </div>
                `;
            }
        } catch (error) {
            container.innerHTML = '<div class="text-center py-8 text-rose-600">Error al cargar reasignaciones</div>';
        }
    }

    async function removerReasignacionTemporal(asignacionId, establecimientoId, registradorId) {
        const confirmed = await remConfirm({
            title: 'Remover reasignación temporal',
            message: '¿Remover esta reasignación temporal? El establecimiento volverá al titular anual.',
            confirmText: 'Remover',
            cancelText: 'Cancelar',
            danger: true,
        });
        if (!confirmed) return;

        try {
            showLoading();

            const response = await fetchAPI('assignments.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'remover',
                    usuario_id: registradorId,
                    establecimiento_id: establecimientoId,
                    anio: anioActual,
                    tipo_asignacion: 'temporal'
                })
            });

            hideLoading();

            if (response.success) {
                showMessage(response.message, 'success');
                await cargarReasignacionesTemporales();
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
        modalAsignar.hide();
        const buscar = document.getElementById('buscarEstablecimiento');
        if (buscar) buscar.value = '';
        const radioAll = document.querySelector('input[name="periodoAsignacion"][value="ALL"]');
        if (radioAll) {
            radioAll.checked = true;
            radioAll.disabled = false;
            if (radioAll.parentElement) radioAll.parentElement.style.opacity = '1';
        }
        document.querySelectorAll('.mes-checkbox').forEach(cb => cb.checked = false);
        const radioTipo = document.querySelector('input[name="tipoAsignacion"][value="anual"]');
        if (radioTipo) radioTipo.checked = true;
        const mesesContainer = document.getElementById('mesesEspecificosContainer');
        if (mesesContainer) mesesContainer.classList.add('d-none');
        const lista = document.getElementById('listaEstablecimientosDisponibles');
        if (lista) lista.innerHTML = '';
        establecimientosDisponibles = [];
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
