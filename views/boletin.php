<?php
/**
 * Vista del Boletín Informativo de Errores REM
 *
 * Documento ejecutivo agrupado Comuna > Establecimiento > Mes, pensado para
 * imprimirse y presentarse. Solo supervisores: el guard de abajo es de
 * cortesía para la UI; quien realmente autoriza es api/boletin.php (403).
 */

if (($_SESSION['rol'] ?? '') !== ROL_SUPERVISOR) {
    echo '<div class="empty"><div class="empty-header text-danger">403</div><p class="empty-title">Acceso denegado</p></div>';
    return;
}

require_once 'models/Location.php';

$currentYear = $_SESSION['year'] ?? date('Y');
$locationModel = new Location();
$comunas = $locationModel->getComunas();
$mesesList = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
?>

<div class="d-flex flex-column gap-3 rem-fade-in">
    <header class="page-header">
        <div>
            <h1 class="page-title"><i class="ti ti-report me-2 text-primary"></i>Boletín Informativo</h1>
            <p class="page-subtitle">
                Errores REM agrupados por comuna, establecimiento y mes, con estado de clasificación.
            </p>
        </div>
        <div class="page-actions">
            <span class="badge badge-soft-primary">
                <i class="ti ti-calendar-event me-1"></i><?php echo htmlspecialchars($currentYear); ?>
            </span>
        </div>
    </header>

    <!-- Filtros -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h3 class="card-title mb-0"><i class="ti ti-filter me-2 text-primary"></i>Filtros</h3>
                <span class="text-secondary small">Define el período y el alcance del boletín</span>
            </div>

            <div class="row g-3">
                <div class="col-lg">
                    <label class="form-label" for="boletinYear">Año</label>
                    <select id="boletinYear" class="form-select">
                        <?php for ($y = date('Y') + 1; $y >= 2020; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo $y == $currentYear ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-lg">
                    <label class="form-label" for="boletinPeriodo">Período</label>
                    <select id="boletinPeriodo" class="form-select">
                        <option value="anual" selected>Año completo</option>
                        <option value="trimestral">Trimestre</option>
                        <option value="mensual">Mes</option>
                    </select>
                </div>

                <div class="col-lg d-none" id="wrapBoletinTrimestre">
                    <label class="form-label" for="boletinTrimestre">Trimestre</label>
                    <select id="boletinTrimestre" class="form-select">
                        <option value="1">1er Trimestre (Ene - Mar)</option>
                        <option value="2">2do Trimestre (Abr - Jun)</option>
                        <option value="3">3er Trimestre (Jul - Sep)</option>
                        <option value="4">4to Trimestre (Oct - Dic)</option>
                    </select>
                </div>

                <div class="col-lg d-none" id="wrapBoletinMes">
                    <label class="form-label" for="boletinMes">Mes</label>
                    <select id="boletinMes" class="form-select">
                        <?php foreach ($mesesList as $m): ?>
                            <option value="<?php echo $m; ?>"><?php echo $m; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg">
                    <label class="form-label" for="boletinComuna">Comuna</label>
                    <select id="boletinComuna" class="form-select">
                        <option value="">Todas</option>
                        <?php foreach ($comunas as $comuna): ?>
                            <option value="<?php echo $comuna['id']; ?>"><?php echo htmlspecialchars($comuna['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg">
                    <label class="form-label" for="boletinEstablecimiento">Establecimiento</label>
                    <select id="boletinEstablecimiento" class="form-select" disabled>
                        <option value="">Todos</option>
                    </select>
                </div>

                <div class="col-12">
                    <div class="btn-list">
                        <button type="button" class="btn btn-primary" id="btnBoletinGenerar">
                            <i class="ti ti-refresh me-1"></i>Generar boletín
                        </button>
                        <button type="button" class="btn btn-outline-success" id="btnBoletinExcel">
                            <i class="ti ti-file-spreadsheet me-1"></i>Exportar Excel
                        </button>
                        <button type="button" class="btn btn-outline-danger" id="btnBoletinPdf">
                            <i class="ti ti-file-type-pdf me-1"></i>Exportar PDF
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btnBoletinImprimir">
                            <i class="ti ti-printer me-1"></i>Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen ejecutivo -->
    <div class="row g-2" id="boletinResumen"></div>

    <!-- Tabla -->
    <div class="card">
        <div class="table-responsive rem-boletin-wrap">
            <table class="table table-bordered table-vcenter rem-boletin-table">
                <colgroup>
                    <col class="rem-col-comuna">
                    <col class="rem-col-est">
                    <col class="rem-col-mes">
                    <col class="rem-col-rem">
                    <col class="rem-col-detalle">
                    <col class="rem-col-estado">
                    <col class="rem-col-num">
                </colgroup>
                <thead>
                    <tr>
                        <th>Comuna</th>
                        <th>Establecimiento</th>
                        <th>Mes</th>
                        <th>REM</th>
                        <th>Detalle de la observación</th>
                        <th>Estado</th>
                        <th>N.º</th>
                    </tr>
                </thead>
                <tbody id="boletinBody">
                    <tr><td colspan="7" class="text-center text-secondary py-4">Seleccione el período y presione «Generar boletín».</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// ============================================
// Boletín Informativo
// ============================================

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

async function parseJsonResponse(response) {
    const text = await response.text();
    let data = {};
    try {
        data = text ? JSON.parse(text) : {};
    } catch (error) {
        throw new Error('Respuesta inválida del servidor');
    }
    if (!response.ok) {
        throw new Error(data.message || 'Error en la petición');
    }
    return data;
}

/** Muestra el campo dependiente que corresponde al tipo de período. */
function syncPeriodoFields() {
    const tipo = document.getElementById('boletinPeriodo').value;
    document.getElementById('wrapBoletinTrimestre').classList.toggle('d-none', tipo !== 'trimestral');
    document.getElementById('wrapBoletinMes').classList.toggle('d-none', tipo !== 'mensual');
}

async function loadEstablecimientos() {
    const comunaId = document.getElementById('boletinComuna').value;
    const select = document.getElementById('boletinEstablecimiento');

    select.innerHTML = '<option value="">Todos</option>';
    select.disabled = !comunaId;
    if (!comunaId) return;

    try {
        const response = await fetch(`api/locations.php?action=establecimientos&comuna_id=${comunaId}`);
        const data = await parseJsonResponse(response);
        if (data.success) {
            data.data.forEach(est => {
                const option = document.createElement('option');
                option.value = est.id;
                option.textContent = est.nombre_corto || est.nombre;
                select.appendChild(option);
            });
        } else {
            showError(data.message || 'No se pudieron cargar los establecimientos');
        }
    } catch (error) {
        showError(error.message || 'No se pudieron cargar los establecimientos de la comuna seleccionada');
    }
}

/** Parámetros del boletín según el selector explícito de período. */
function getBoletinParams() {
    const params = new URLSearchParams();
    params.set('anio', document.getElementById('boletinYear').value);

    const tipo = document.getElementById('boletinPeriodo').value;
    params.set('periodo', tipo);
    if (tipo === 'trimestral') {
        params.set('trimestre', document.getElementById('boletinTrimestre').value);
    } else if (tipo === 'mensual') {
        params.set('mes', document.getElementById('boletinMes').value);
    }

    const comuna = document.getElementById('boletinComuna').value;
    if (comuna) params.set('comuna_id', comuna);

    const est = document.getElementById('boletinEstablecimiento').value;
    if (est) params.set('establecimiento_id', est);

    return params;
}

async function loadBoletin() {
    const tbody = document.getElementById('boletinBody');
    const params = getBoletinParams();
    params.set('format', 'json');

    tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-4">Generando boletín…</td></tr>';

    try {
        const resp = await fetch('api/boletin.php?' + params.toString());
        const json = await parseJsonResponse(resp);
        if (!json.success) throw new Error(json.message || 'No se pudo generar el boletín');
        renderBoletin(json.data);
    } catch (e) {
        tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger py-4">${escapeHtml(e.message || 'Error al generar el boletín')}</td></tr>`;
        showError(e.message || 'Error al generar el boletín');
    }
}

/** Mismo algoritmo de rowspan que Exporter::agruparJerarquia(). */
function calcularSpans(filas, niveles) {
    const n = filas.length;
    const spans = filas.map(() => ({}));

    niveles.forEach(function (nivel) {
        const clave = f => nivel.campos.map(c => String(f[c] ?? '')).join('||');
        let i = 0;
        while (i < n) {
            const k = clave(filas[i]);
            let j = i + 1;
            while (j < n && clave(filas[j]) === k) j++;
            spans[i][nivel.nombre] = j - i;
            for (let x = i + 1; x < j; x++) spans[x][nivel.nombre] = 0;
            i = j;
        }
    });

    return spans;
}


function boletinEstadoClase(row) {
    const c = String(row.clasificacion || '').toLowerCase().trim();
    if (c === 'corregido' || (!c && row.estado_actual === 'aprobado')) return 'rem-estado-ok';
    if (c === 'sin_respuesta') return 'rem-estado-pendiente';
    if (c === 'respuesta_incorrecta' || c === 'error') return 'rem-estado-error';
    return 'rem-estado-neutro';
}

/** Etiqueta legible del estado (espejo de clasificacionLabel() en PHP). */
function boletinEstadoLabel(row) {
    const mapa = {
        'corregido': 'Corregido',
        'error': 'Error',
        'sin_respuesta': 'Sin respuesta del Establecimiento',
        'respuesta_incorrecta': 'Respuesta incorrecta de Establecimiento'
    };
    const c = String(row.clasificacion || '').toLowerCase().trim();
    if (c && mapa[c]) return mapa[c];
    switch (row.estado_actual) {
        case 'aprobado': return 'Corregido';
        case 'justificado': return 'Justificado';
        case 'rechazado': return 'No corregido';
        case 'pendiente': return 'Pendiente de respuesta';
        case 'error': return 'Error';
    }
    return 'Sin clasificar';
}

function renderBoletin(data) {
    const filas = data.datos || [];
    const resumen = data.resumen || {};
    const tbody = document.getElementById('boletinBody');
    const cont = document.getElementById('boletinResumen');

    const tarjetas = [
        { label: 'Errores', valor: resumen.total_errores || 0, icon: 'ti-alert-triangle' },
        { label: 'Corregidos', valor: `${resumen.corregidos || 0} (${resumen.pct_corregidos || 0}%)`, icon: 'ti-circle-check' },
        { label: 'Sin respuesta', valor: resumen.sin_respuesta || 0, icon: 'ti-clock' },
        { label: 'Establecimientos', valor: resumen.establecimientos || 0, icon: 'ti-building-hospital' },
        { label: 'Comunas', valor: resumen.comunas || 0, icon: 'ti-map-pin' }
    ];
    cont.innerHTML = tarjetas.map(t => `
        <div class="col-6 col-md">
            <div class="rem-boletin-kpi">
                <span class="rem-boletin-kpi-label"><i class="ti ${t.icon} me-1"></i>${t.label}</span>
                <span class="rem-boletin-kpi-value">${escapeHtml(String(t.valor))}</span>
            </div>
        </div>
    `).join('') + `
        <div class="col-12">
            <p class="text-secondary small mb-0 mt-1">${escapeHtml(data.periodo || '')} · Emitido: ${escapeHtml(data.emitido || '')}</p>
        </div>`;

    if (!filas.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-secondary py-4">No se encontraron errores en el período seleccionado.</td></tr>';
        return;
    }

    const spans = calcularSpans(filas, [
        { nombre: 'comuna', campos: ['comuna'] },
        { nombre: 'establecimiento', campos: ['comuna', 'establecimiento'] },
        { nombre: 'mes', campos: ['comuna', 'establecimiento', 'mes'] }
    ]);

    tbody.innerHTML = filas.map(function (row, i) {
        const sp = spans[i];
        const rem = [row.codigo_serie, row.codigo_hoja].filter(Boolean).join(' / ');
        let html = '<tr>';

        if (sp.comuna > 0) {
            html += `<td class="rem-cell-group" rowspan="${sp.comuna}">${escapeHtml(row.comuna || '')}</td>`;
        }
        if (sp.establecimiento > 0) {
            html += `<td class="rem-cell-group" rowspan="${sp.establecimiento}">${escapeHtml(row.establecimiento || '')}</td>`;
        }
        if (sp.mes > 0) {
            html += `<td class="rem-cell-group" rowspan="${sp.mes}">${escapeHtml(row.mes || '')}</td>`;
        }

        html += `<td class="rem-cell-rem">${escapeHtml(rem)}</td>`;
        html += `<td>${escapeHtml(row.detalle_observacion || '')}</td>`;
        html += `<td class="rem-cell-estado ${boletinEstadoClase(row)}">${escapeHtml(boletinEstadoLabel(row))}</td>`;

        if (sp.establecimiento > 0) {
            html += `<td class="rem-cell-group" rowspan="${sp.establecimiento}">${sp.establecimiento}</td>`;
        }

        return html + '</tr>';
    }).join('');
}

function exportBoletin(formato) {
    const params = getBoletinParams();
    params.set('format', formato);
    window.open('api/boletin.php?' + params.toString(), '_blank');
}

document.addEventListener('DOMContentLoaded', () => {
    syncPeriodoFields();
    document.getElementById('boletinPeriodo').addEventListener('change', syncPeriodoFields);
    document.getElementById('boletinComuna').addEventListener('change', loadEstablecimientos);

    document.getElementById('btnBoletinGenerar').addEventListener('click', loadBoletin);
    document.getElementById('btnBoletinExcel').addEventListener('click', () => exportBoletin('excel'));
    document.getElementById('btnBoletinPdf').addEventListener('click', () => exportBoletin('pdf'));
    document.getElementById('btnBoletinImprimir').addEventListener('click', () => window.print());

    loadBoletin();
});
</script>
