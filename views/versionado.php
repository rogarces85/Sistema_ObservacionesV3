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
            <button id="btnCreateVersion" class="btn btn-primary" type="button" onclick="createVersion()">
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
document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('click', (event) => {
        const target = event.target.closest('.js-rollback');
        if (!target) return;
        event.preventDefault();
        const id = target.getAttribute('data-version-id');
        const tag = target.getAttribute('data-version-tag') || '';
        rollbackVersion(parseInt(id, 10), tag);
    });
    loadVersions();
});

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
                    <button class="btn btn-sm btn-outline-danger js-rollback" type="button"
                        data-version-id="${version.id}" data-version-tag="${escapeHtml(version.version_tag)}">
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
    const button = document.getElementById('btnCreateVersion');
    if (button && button.disabled) return;

    const descripcion = await remPrompt({
        title: 'Crear snapshot',
        message: 'Ingrese una descripcion para identificar el snapshot.',
        label: 'Descripcion del snapshot',
        placeholder: 'Ej. Pre-deploy v2.1.0',
        confirmText: 'Crear',
        cancelText: 'Cancelar',
    });
    if (!descripcion) return;
    try {
        showLoading();
        if (button) button.disabled = true;

        const response = await fetchAPI('versioning.php?action=create', {
            method: 'POST',
            body: JSON.stringify({ descripcion })
        });

        if (response && response.success) {
            showSuccess('Snapshot creado correctamente');
            loadVersions();
        } else {
            showError((response && response.message) || 'Error al crear snapshot');
        }
    } catch (error) {
        showError(error.message || 'Error al crear snapshot');
    } finally {
        hideLoading();
        if (button) button.disabled = false;
    }
}

async function rollbackVersion(id, tag) {
    const firstConfirm = await remConfirm({
        title: 'Rollback a ' + tag,
        message: 'Esta acción modificará archivos del sistema y puede afectar a usuarios conectados. Úsela solo con respaldo verificado, ventana de mantenimiento y autorización explícita.',
        confirmText: 'Continuar',
        cancelText: 'Cancelar',
        danger: true,
    });
    if (!firstConfirm) return;

    const typed = await remPrompt({
        title: 'Confirmar rollback',
        message: 'Escribe ACEPTAR para confirmar el rollback a ' + tag + '.',
        label: 'Confirmación',
        placeholder: 'Escribe ACEPTAR',
        requireText: 'ACEPTAR',
        confirmText: 'Ejecutar rollback',
        cancelText: 'Cancelar',
    });
    if (typed !== 'ACEPTAR') return;

    const button = document.querySelector(`.js-rollback[data-version-id="${id}"]`);
    if (button && button.disabled) return;

    try {
        showLoading();
        if (button) button.disabled = true;

        const response = await fetchAPI('versioning.php?action=rollback&id=' + id, { method: 'POST', body: '{}' });
        if (response && response.success) {
            showSuccess('Rollback ejecutado correctamente');
            loadVersions();
        } else {
            showError((response && response.message) || 'Error al ejecutar rollback');
        }
    } catch (error) {
        showError(error.message || 'Error al ejecutar rollback');
    } finally {
        hideLoading();
        if (button) button.disabled = false;
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
