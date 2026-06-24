<?php
/**
 * Vista de Versionado del Sistema
 */

if ($_SESSION['rol'] !== ROL_SUPERVISOR) {
    echo '<div class="empty"><div class="empty-header text-danger">403</div><p class="empty-title">Acceso denegado</p></div>';
    return;
}
?>

<div class="d-flex flex-column gap-3 rem-fade-in">
    <header class="page-header">
        <div>
            <h1 class="page-title"><i class="ti ti-git-branch me-2 text-primary"></i>Versionado del Sistema</h1>
            <p class="page-subtitle">Snapshots de código y rollback controlado para supervisores.</p>
        </div>
        <div class="page-actions">
            <button class="btn btn-primary" type="button" onclick="createVersion()">
                <i class="ti ti-camera me-1"></i>Crear snapshot
            </button>
        </div>
    </header>

    <div class="alert alert-warning alert-icon" role="alert">
        <i class="ti ti-alert-triangle alert-icon-i"></i>
        <div>El rollback modifica archivos del sistema. Use esta herramienta solo con respaldo y ventana de mantenimiento.</div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr><th>Versión</th><th>Descripción</th><th>Autor</th><th>Fecha</th><th class="text-end">Acciones</th></tr>
                </thead>
                <tbody id="versionsBody">
                    <tr><td colspan="5" class="text-center text-secondary py-3">Cargando versiones...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', loadVersions);

async function loadVersions() {
    const tbody = document.getElementById('versionsBody');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-secondary py-3">Cargando versiones...</td></tr>';
    try {
        const response = await fetchAPI('versioning.php?action=list');
        const versions = response.data || [];
        if (versions.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-secondary py-3">No hay snapshots registrados.</td></tr>';
            return;
        }
        tbody.innerHTML = versions.map(version => `
            <tr>
                <td><span class="badge bg-blue text-blue-fg">${escapeHtml(version.version_tag)}</span></td>
                <td>${escapeHtml(version.descripcion)}</td>
                <td class="text-secondary">${escapeHtml(version.autor_nombre || '-')}</td>
                <td class="text-secondary">${formatDateTime(version.fecha_creacion)}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-danger" type="button" onclick="rollbackVersion(${version.id}, '${escapeHtml(version.version_tag)}')">
                        <i class="ti ti-rotate-clockwise me-1"></i>Rollback
                    </button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">No se pudieron cargar las versiones.</td></tr>';
    }
}

async function createVersion() {
    const descripcion = prompt('Descripción del snapshot:');
    if (!descripcion) return;
    try {
        showLoading();
        const response = await fetchAPI('versioning.php?action=create', {
            method: 'POST',
            body: JSON.stringify({ descripcion })
        });
        hideLoading();
        if (response.success) {
            showSuccess('Snapshot creado correctamente');
            loadVersions();
        }
    } catch (error) {
        hideLoading();
        showError(error.message || 'Error al crear snapshot');
    }
}

async function rollbackVersion(id, tag) {
    const warning = '¿Ejecutar rollback a ' + tag + '?\n\nEsta acción modificará archivos del sistema y puede afectar a usuarios conectados. Úsela solo con respaldo verificado, ventana de mantenimiento y autorización explícita.\n\nEscriba ACEPTAR en la siguiente confirmación para continuar.';
    if (!confirm(warning)) return;
    if (prompt('Para confirmar rollback escriba ACEPTAR:') !== 'ACEPTAR') return;
    try {
        showLoading();
        const response = await fetchAPI('versioning.php?action=rollback&id=' + id, { method: 'POST', body: '{}' });
        hideLoading();
        if (response.success) {
            showSuccess('Rollback ejecutado correctamente');
            loadVersions();
        }
    } catch (error) {
        hideLoading();
        showError(error.message || 'Error al ejecutar rollback');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function formatDateTime(dateString) {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('es-CL', { dateStyle: 'short', timeStyle: 'short' });
}
</script>
