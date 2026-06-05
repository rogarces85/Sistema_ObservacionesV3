<?php
require_once 'config/constants.php';

$usuarioId = $_SESSION['usuario_id'];
$rol = $_SESSION['rol'];
$anioActual = $_SESSION['anio_trabajo'] ?? date('Y');

global $TIPOS_ERROR, $MESES, $SERIES_REM, $HOJAS_POR_SERIE;
?>

<div class="page-observaciones">
    <div class="page-header">
        <div class="header-left">
            <h1>Observaciones</h1>
            <p>Gestión de observaciones REM - Año <?php echo $anioActual; ?></p>
        </div>
        <div class="header-right">
            <?php if ($rol === ROL_REGISTRADOR): ?>
            <button onclick="ObsApp.abrirCrear()" class="btn btn-primary">
                <?php echo tablerIcon('plus'); ?>
                Nueva Observación
            </button>
            <?php endif; ?>
        </div>
    </div>

    <div class="stats-row" id="statsRow">
        <div class="stat-mini">
            <div class="stat-mini-value" id="statTotal">-</div>
            <div class="stat-mini-label">Total</div>
        </div>
        <div class="stat-mini">
            <div class="stat-mini-value" id="statPendiente">-</div>
            <div class="stat-mini-label">Pendientes</div>
        </div>
        <div class="stat-mini">
            <div class="stat-mini-value" id="statAprobado">-</div>
            <div class="stat-mini-label">Aprobados</div>
        </div>
        <div class="stat-mini">
            <div class="stat-mini-value" id="statError">-</div>
            <div class="stat-mini-label">Errores</div>
        </div>
    </div>

    <div class="filters-card">
        <div class="filters-header">Filtros</div>
        <div class="filters-body">
            <div class="filter-group">
                <label>Buscar</label>
                <input type="text" id="filtroBusqueda" placeholder="Establecimiento o detalle...">
            </div>
            <div class="filter-group">
                <label>Mes</label>
                <select id="filtroMes">
                    <option value="">Todos</option>
                    <?php foreach ($MESES as $mes): ?>
                        <option value="<?php echo $mes; ?>"><?php echo $mes; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Estado</label>
                <select id="filtroEstado">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="aprobado">Aprobado</option>
                    <option value="rechazado">Rechazado</option>
                    <option value="error">Error</option>
                    <option value="justificado">Justificado</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Tipo Error</label>
                <select id="filtroTipoError">
                    <option value="">Todos</option>
                    <?php foreach ($TIPOS_ERROR as $tipo): ?>
                        <option value="<?php echo htmlspecialchars($tipo); ?>"><?php echo htmlspecialchars($tipo); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-actions">
                <button onclick="ObsApp.cargar(1)" class="btn btn-primary">Buscar</button>
                <button onclick="ObsApp.limpiarFiltros()" class="btn btn-outline">Limpiar</button>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Listado de Observaciones</h3>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Establecimiento</th>
                        <th>Mes</th>
                        <th>Serie/Hoja</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Registrado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tablaCuerpo">
                    <tr><td colspan="7" class="loading-cell">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span id="paginacionInfo">-</span>
            <div id="paginacionNav"></div>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="modalFormBackdrop" onclick="if(event.target===this)ObsApp.cerrarModal()"></div>
<div class="modal-container" id="modalForm">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3><?php echo tablerIcon('clipboard-text'); ?> <span id="modalTitulo">Nueva Observación</span></h3>
            <button onclick="ObsApp.cerrarModal()" class="modal-close">
                <?php echo tablerIcon('x'); ?>
            </button>
        </div>
        <form id="formObs" onsubmit="ObsApp.guardar(event)">
            <div class="modal-body">
                <input type="hidden" id="obsId">
                <input type="hidden" id="obsFechaUpd">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Mes</label>
                        <select id="frmMes" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($MESES as $mes): ?>
                                <option value="<?php echo $mes; ?>"><?php echo $mes; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="required">Establecimiento</label>
                        <select id="frmEst" required>
                            <option value="">Seleccione...</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="required">Tipo</label>
                        <select id="frmTipo" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($TIPOS_ERROR as $tipo): ?>
                                <option value="<?php echo htmlspecialchars($tipo); ?>"><?php echo htmlspecialchars($tipo); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="required">Serie</label>
                        <select id="frmSerie" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($SERIES_REM as $serie): ?>
                                <option value="<?php echo htmlspecialchars($serie); ?>"><?php echo htmlspecialchars($serie); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row" id="rowHoja">
                    <div class="form-group">
                        <label>Hoja REM</label>
                        <select id="frmHoja">
                            <option value="">Seleccione serie primero</option>
                        </select>
                    </div>
                    <div class="form-group"></div>
                </div>
                
                <div class="form-group">
                    <label class="required">Detalle</label>
                    <textarea id="frmDetalle" rows="3" required placeholder="Descripción de la observación..."></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Clasificación</label>
                        <input type="text" id="frmClasif" placeholder="Clasificación">
                    </div>
                    <div class="form-group">
                        <label>Usa Validador</label>
                        <select id="frmValidador">
                            <option value="">Seleccione...</option>
                            <option value="si">Sí</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Plazo</label>
                        <select id="frmPlazo">
                            <option value="">Seleccione...</option>
                            <option value="dentro_plazo">Dentro de Plazo</option>
                            <option value="fuera_plazo">Fuera de Plazo</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="ObsApp.cerrarModal()" class="btn btn-outline">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="modalVerBackdrop" onclick="if(event.target===this)ObsApp.cerrarVer()"></div>
<div class="modal-container" id="modalVer">
    <div class="modal modal-xl">
        <div class="modal-header">
            <h3><?php echo tablerIcon('eye'); ?> Detalle de Observación</h3>
            <button onclick="ObsApp.cerrarVer()" class="modal-close">
                <?php echo tablerIcon('x'); ?>
            </button>
        </div>
        <div class="modal-body" id="detalleContenido">
        </div>
    </div>
</div>

<style>
.page-observaciones {
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.header-left h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 0.25rem;
}

.header-left p {
    color: #64748b;
    margin: 0;
    font-size: 0.875rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-primary {
    background: #0ea5e9;
    color: white;
}

.btn-primary:hover {
    background: #0284c7;
}

.btn-outline {
    background: transparent;
    border: 1px solid #e2e8f0;
    color: #64748b;
}

.btn-outline:hover {
    background: #f8fafc;
}

.btn-danger {
    background: #dc2626;
    color: white;
}

.btn-danger:hover {
    background: #b91c1c;
}

.btn-ghost {
    background: transparent;
    border: 1px solid #e2e8f0;
    color: #475569;
}

.btn-ghost:hover {
    background: #f1f5f9;
}

.btn .ti {
    width: 18px;
    height: 18px;
}

.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-mini {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
    text-align: center;
}

.stat-mini-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}

.stat-mini-label {
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.filters-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.filters-header {
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
    color: #1e293b;
}

.filters-body {
    padding: 1rem 1.25rem;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 150px;
}

.filter-group label {
    display: block;
    font-size: 0.75rem;
    font-weight: 500;
    color: #64748b;
    margin-bottom: 0.25rem;
}

.filter-group input,
.filter-group select {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.875rem;
}

.filter-group input:focus,
.filter-group select:focus {
    outline: none;
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
}

.filter-actions {
    display: flex;
    gap: 0.5rem;
}

.table-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.table-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
}

.table-header h3 {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin: 0;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.data-table th {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    background: #f8fafc;
}

.data-table tbody tr:hover {
    background: #f8fafc;
}

.data-table td {
    font-size: 0.875rem;
    color: #1e293b;
}

.loading-cell {
    text-align: center;
    color: #94a3b8;
    padding: 2rem !important;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 500;
}

.badge-pendiente { background: #fef9c3; color: #ca8a04; }
.badge-aprobado { background: #dcfce7; color: #16a34a; }
.badge-rechazado, .badge-error { background: #fee2e2; color: #dc2626; }
.badge-justificado { background: #e0f2fe; color: #0ea5e9; }

.table-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.8rem;
    color: #64748b;
}

.pagination {
    display: flex;
    gap: 0.25rem;
    list-style: none;
    padding: 0;
    margin: 0;
}

.pagination a {
    display: block;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    color: #64748b;
    text-decoration: none;
    font-size: 0.8rem;
}

.pagination a:hover {
    background: #f1f5f9;
}

.pagination .active a {
    background: #0ea5e9;
    color: white;
}

.form-row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    flex: 1;
}

.form-group label {
    display: block;
    font-size: 0.8rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.25rem;
}

.form-group label.required::after {
    content: ' *';
    color: #dc2626;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 0.875rem;
    font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #0ea5e9;
    box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
}

.form-group textarea {
    resize: vertical;
}

.action-btn {
    padding: 0.375rem;
    border: none;
    background: transparent;
    color: #64748b;
    cursor: pointer;
    border-radius: 4px;
}

.action-btn:hover {
    background: #f1f5f9;
    color: #0ea5e9;
}

.empty-row td {
    text-align: center;
    color: #94a3b8;
    padding: 2rem !important;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .filters-body {
        flex-direction: column;
    }
    
    .filter-group {
        min-width: 100%;
    }
}
</style>

<script>
const HOJAS_POR_SERIE = <?php echo json_encode($HOJAS_POR_SERIE); ?>;
const USUARIO_ROL = '<?php echo $rol; ?>';
const USUARIO_ID = <?php echo $usuarioId; ?>;

const ObsApp = (() => {
    let pagina = 1;
    
    const init = () => {
        cargarEstablecimientos();
        cargarStats();
        cargar();
        
        document.getElementById('frmTipo').addEventListener('change', cambiarTipo);
        document.getElementById('frmSerie').addEventListener('change', cargarHojas);
    };
    
    const cargarEstablecimientos = async () => {
        try {
            const r = await fetchAPI('api/establecimientos.php?accion=listar&activo=1');
            const sel = document.getElementById('frmEst');
            if (r.success && r.data) {
                r.data.forEach(e => {
                    const opt = document.createElement('option');
                    opt.value = e.id;
                    opt.textContent = e.nombre;
                    sel.appendChild(opt);
                });
            }
        } catch (e) {
            console.error(e);
        }
    };
    
    const cargarStats = async () => {
        const anio = document.getElementById('year-selector')?.value || new Date().getFullYear();
        try {
            const r = await fetchAPI(`api/observaciones.php?accion=stats&anio=${anio}`);
            if (r.success) {
                const s = r.data;
                document.getElementById('statTotal').textContent = s.total || 0;
                const porEst = {};
                (s.por_estado || []).forEach(e => porEst[e.estado_actual] = parseInt(e.total));
                document.getElementById('statPendiente').textContent = porEst['pendiente'] || 0;
                document.getElementById('statAprobado').textContent = porEst['aprobado'] || 0;
                document.getElementById('statError').textContent = (porEst['error'] || 0);
            }
        } catch (e) {
            console.error(e);
        }
    };
    
    const cargar = async (pag = 1) => {
        pagina = pag;
        const filtros = {
            busqueda: document.getElementById('filtroBusqueda').value.trim(),
            mes: document.getElementById('filtroMes').value,
            estado: document.getElementById('filtroEstado').value,
            tipo_error: document.getElementById('filtroTipoError').value
        };
        
        const anio = document.getElementById('year-selector')?.value || new Date().getFullYear();
        const params = new URLSearchParams({ accion: 'listar', pagina, anio, ...filtros });
        
        try {
            const r = await fetchAPI('api/observaciones.php?' + params.toString());
            if (r.success) {
                renderTabla(r.data);
                renderPag(r.data);
            } else {
                mostrarError(r.error);
            }
        } catch (e) {
            mostrarError(e.message);
        }
    };
    
    const renderTabla = (d) => {
        const tbody = document.getElementById('tablaCuerpo');
        const datos = d.datos || [];
        
        if (datos.length === 0) {
            tbody.innerHTML = '<tr class="empty-row"><td colspan="7">No se encontraron observaciones</td></tr>';
            return;
        }
        
        tbody.innerHTML = datos.map(o => {
            const puedeEditar = USUARIO_ROL === 'supervisor' || (o.usuario_registro_id == USUARIO_ID && o.estado_actual === 'pendiente');
            const badgeClass = `badge badge-${o.estado_actual}`;
            
            return `<tr>
                <td><strong>${esc(o.nombre_corto || o.establecimiento_nombre)}</strong><br><small style="color:#94a3b8">${esc(o.comuna_nombre)}</small></td>
                <td>${esc(o.mes)}</td>
                <td>${esc(o.codigo_serie || '-')}<br><small style="color:#94a3b8">${esc(o.codigo_hoja || '-')}</small></td>
                <td><span class="badge" style="background:#f1f5f9;color:#475569">${esc(o.tipo_error)}</span></td>
                <td><span class="${badgeClass}">${cap(o.estado_actual)}</span></td>
                <td><small>${esc(o.usuario_registro_nombre)}</small></td>
                <td>
                    <button class="action-btn" onclick="ObsApp.ver(${o.id})" title="Ver"><?php echo tablerIcon('eye'); ?></button>
                    ${puedeEditar ? `<button class="action-btn" onclick="ObsApp.editar(${o.id})" title="Editar"><?php echo tablerIcon('edit'); ?></button>` : ''}
                    ${USUARIO_ROL === 'supervisor' ? `<button class="action-btn" onclick="ObsApp.eliminar(${o.id})" title="Eliminar"><?php echo tablerIcon('trash'); ?></button>` : ''}
                </td>
            </tr>`;
        }).join('');
    };
    
    const renderPag = (d) => {
        document.getElementById('paginacionInfo').textContent = d.total > 0 
            ? `${d.total} registros` 
            : 'Sin registros';
        
        const nav = document.getElementById('paginacionNav');
        if (d.totalPaginas <= 1) {
            nav.innerHTML = '';
            return;
        }
        
        let html = '<ul class="pagination">';
        html += `<li><a href="#" onclick="ObsApp.cargar(${d.pagina - 1});return false" ${d.pagina <= 1 ? 'class="disabled"' : ''}>Ant</a></li>`;
        
        for (let p = 1; p <= d.totalPaginas; p++) {
            if (p === d.pagina) {
                html += `<li class="active"><a href="#">${p}</a></li>`;
            } else {
                html += `<li><a href="#" onclick="ObsApp.cargar(${p});return false">${p}</a></li>`;
            }
        }
        
        html += `<li><a href="#" onclick="ObsApp.cargar(${d.pagina + 1});return false" ${d.pagina >= d.totalPaginas ? 'class="disabled"' : ''}>Sig</a></li>`;
        html += '</ul>';
        nav.innerHTML = html;
    };
    
    const cambiarTipo = () => {
        const tipo = document.getElementById('frmTipo').value;
        document.getElementById('rowHoja').style.display = tipo === 'S/OBSERVACION' ? 'none' : 'flex';
    };
    
    const cargarHojas = () => {
        const serie = document.getElementById('frmSerie').value;
        const sel = document.getElementById('frmHoja');
        sel.innerHTML = '<option value="">Seleccione...</option>';
        
        if (!serie) {
            sel.innerHTML = '<option value="">Seleccione serie primero</option>';
            return;
        }
        
        const hojas = HOJAS_POR_SERIE[serie] || [];
        hojas.forEach(h => {
            const opt = document.createElement('option');
            opt.value = h.codigo;
            opt.textContent = h.nombre;
            sel.appendChild(opt);
        });
    };
    
    const abrirCrear = () => {
        document.getElementById('obsId').value = '';
        document.getElementById('modalTitulo').textContent = 'Nueva Observación';
        document.getElementById('formObs').reset();
        document.getElementById('rowHoja').style.display = 'flex';
        document.getElementById('modalFormBackdrop').classList.add('show');
        document.getElementById('modalForm').classList.add('show');
        document.body.style.overflow = 'hidden';
    };
    
    const cerrarModal = () => {
        document.getElementById('modalFormBackdrop').classList.remove('show');
        document.getElementById('modalForm').classList.remove('show');
        document.body.style.overflow = '';
    };
    
    const editar = async (id) => {
        try {
            const r = await fetchAPI(`api/observaciones.php?accion=detalle&id=${id}`);
            if (!r.success) {
                mostrarError(r.error);
                return;
            }
            
            const o = r.data;
            document.getElementById('obsId').value = o.id;
            document.getElementById('obsFechaUpd').value = o.fecha_actualizacion || '';
            document.getElementById('modalTitulo').textContent = 'Editar Observación';
            
            document.getElementById('frmMes').value = o.mes;
            document.getElementById('frmEst').value = o.establecimiento_id;
            document.getElementById('frmTipo').value = o.tipo_error;
            cambiarTipo();
            document.getElementById('frmSerie').value = o.codigo_serie;
            cargarHojas();
            document.getElementById('frmHoja').value = o.codigo_hoja || '';
            document.getElementById('frmDetalle').value = o.detalle_observacion || '';
            document.getElementById('frmPlazo').value = o.plazo_entrega || '';
            document.getElementById('frmClasif').value = o.clasificacion || '';
            document.getElementById('frmValidador').value = o.usa_validador || '';
            
            document.getElementById('modalFormBackdrop').classList.add('show');
            document.getElementById('modalForm').classList.add('show');
            document.body.style.overflow = 'hidden';
        } catch (e) {
            mostrarError(e.message);
        }
    };
    
    const guardar = async (e) => {
        e.preventDefault();
        
        const datos = {
            mes: document.getElementById('frmMes').value,
            establecimiento_id: parseInt(document.getElementById('frmEst').value),
            tipo_error: document.getElementById('frmTipo').value,
            codigo_serie: document.getElementById('frmSerie').value,
            codigo_hoja: document.getElementById('frmHoja').value || null,
            detalle_observacion: document.getElementById('frmDetalle').value,
            plazo_entrega: document.getElementById('frmPlazo').value || null,
            clasificacion: document.getElementById('frmClasif').value || null,
            usa_validador: document.getElementById('frmValidador').value || null
        };
        
        const id = document.getElementById('obsId').value;
        const fechaUpd = document.getElementById('obsFechaUpd').value;
        if (fechaUpd) datos.fecha_actualizacion = fechaUpd;
        
        const btn = document.getElementById('btnGuardar');
        btn.disabled = true;
        btn.textContent = 'Guardando...';
        
        try {
            let r;
            if (id) {
                r = await fetchAPI(`api/observaciones.php?id=${id}`, {
                    method: 'PUT',
                    body: JSON.stringify(datos)
                });
            } else {
                r = await fetchAPI('api/observaciones.php?accion=crear', {
                    method: 'POST',
                    body: JSON.stringify(datos)
                });
            }
            
            if (r.success) {
                mostrarExito(id ? 'Observación actualizada' : 'Observación creada');
                cerrarModal();
                cargar(pagina);
                cargarStats();
            } else {
                mostrarError(r.error);
            }
        } catch (e) {
            mostrarError(e.message);
        } finally {
            btn.disabled = false;
            btn.textContent = 'Guardar';
        }
    };
    
    const ver = async (id) => {
        try {
            const [obsR, histR] = await Promise.all([
                fetchAPI(`api/observaciones.php?accion=detalle&id=${id}`),
                fetchAPI(`api/observaciones.php?accion=historial&id=${id}`)
            ]);
            
            if (!obsR.success) {
                mostrarError(obsR.error);
                return;
            }
            
            const o = obsR.data;
            const hist = histR.success ? histR.data : [];
            const badgeClass = `badge badge-${o.estado_actual}`;
            
            document.getElementById('detalleContenido').innerHTML = `
                <div class="modal-section">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="mb-1">${esc(o.nombre_corto || o.establecimiento_nombre)}</h4>
                            <p class="text-secondary mb-0" style="font-size:0.875rem">${esc(o.comuna_nombre)}</p>
                        </div>
                        <span class="modal-badge ${o.estado_actual}">${cap(o.estado_actual)}</span>
                    </div>
                </div>
                
                <div class="modal-info-grid">
                    <div class="modal-info-item">
                        <label>Mes/Año</label>
                        <span>${esc(o.mes)} ${o.anio}</span>
                    </div>
                    <div class="modal-info-item">
                        <label>Serie/Hoja</label>
                        <span>${esc(o.codigo_serie || '-')} / ${esc(o.codigo_hoja || '-')}</span>
                    </div>
                    <div class="modal-info-item">
                        <label>Tipo Error</label>
                        <span>${esc(o.tipo_error)}</span>
                    </div>
                    <div class="modal-info-item">
                        <label>Plazo</label>
                        <span>${o.plazo_entrega ? cap(o.plazo_entrega.replace('_', ' ')) : '-'}</span>
                    </div>
                </div>
                
                <div class="modal-section">
                    <div class="modal-section-title">Detalle</div>
                    <div class="modal-content-box">${esc(o.detalle_observacion) || '-'}</div>
                </div>
                
                <div class="modal-section">
                    <small class="text-secondary">Registrado por: ${esc(o.usuario_registro_nombre)} | Fecha: ${formatoFecha(o.fecha_registro)}</small>
                </div>
                
                ${hist.length > 0 ? `
                <div class="modal-section">
                    <div class="modal-section-title">Historial de Cambios</div>
                    <div class="modal-timeline">
                        ${hist.map(h => `<div class="modal-timeline-item">
                            <div class="modal-timeline-dot">${tablerIconSvg('arrow-right', 12)}</div>
                            <div class="modal-timeline-content">
                                <div class="modal-timeline-header">
                                    <span class="modal-timeline-title">${esc(h.estado_anterior || 'Inicio')} → ${esc(h.estado_nuevo)}</span>
                                    <span class="modal-timeline-date">${formatoFecha(h.fecha_creacion)}</span>
                                </div>
                                <div class="modal-timeline-user">${esc(h.usuario_nombre)}</div>
                            </div>
                        </div>`).join('')}
                    </div>
                </div>` : ''}
            `;
            
            document.getElementById('modalVerBackdrop').classList.add('show');
            document.getElementById('modalVer').classList.add('show');
            document.body.style.overflow = 'hidden';
        } catch (e) {
            mostrarError(e.message);
        }
    };
    
    const cerrarVer = () => {
        document.getElementById('modalVerBackdrop').classList.remove('show');
        document.getElementById('modalVer').classList.remove('show');
        document.body.style.overflow = '';
    };
    
    const eliminar = async (id) => {
        const confirmado = await confirmarAccion({
            titulo: 'Eliminar Observación',
            mensaje: '¿Está seguro de eliminar esta observación? Esta acción no se puede deshacer.',
            tipo: 'danger',
            textoConfirmar: 'Eliminar',
            textoCancelar: 'Cancelar'
        });
        if (!confirmado) return;

        try {
            const r = await fetchAPI(`api/observaciones.php?id=${id}`, { method: 'DELETE' });
            if (r.success) {
                mostrarExito('Observación eliminada');
                cargar(pagina);
                cargarStats();
            } else {
                mostrarError(r.error);
            }
        } catch (e) {
            mostrarError(e.message);
        }
    };
    
    const limpiarFiltros = () => {
        document.getElementById('filtroBusqueda').value = '';
        document.getElementById('filtroMes').value = '';
        document.getElementById('filtroEstado').value = '';
        document.getElementById('filtroTipoError').value = '';
        cargar(1);
    };
    
    const esc = (t) => {
        if (!t) return '';
        const d = document.createElement('div');
        d.textContent = t;
        return d.innerHTML;
    };
    
    const cap = (t) => t ? t.charAt(0).toUpperCase() + t.slice(1) : '';
    
    const formatoFecha = (f) => {
        if (!f) return '-';
        const d = new Date(f);
        if (isNaN(d)) return f;
        return d.toLocaleDateString('es-CL', { day: '2-digit', month: '2-digit', year: 'numeric' });
    };
    
    const mostrarExito = (m) => typeof showSuccess === 'function' ? showSuccess(m) : alert(m);
    const mostrarError = (m) => typeof showError === 'function' ? showError(m) : alert(m);
    
    document.addEventListener('DOMContentLoaded', init);
    
    return { cargar, abrirCrear, editar, ver, eliminar, cerrarModal, cerrarVer, limpiarFiltros, guardar };
})();
</script>