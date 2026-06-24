/**
 * Modales de confirmacion y prompt consistentes.
 *
 * Reemplaza los confirm()/prompt() nativos con modales Bootstrap
 * que comparten el mismo estilo y son configurables.
 *
 * API:
 *   await remConfirm({ title, message, confirmText, cancelText, danger, requireText })
 *     -> true si confirma, false si cancela
 *
 *   await remPrompt({ title, message, label, placeholder, requireText, multiline })
 *     -> string con el valor, o null si cancela
 *
 *   remAlert({ title, message, variant })
 *     -> Promise (solo cierra)
 */

(function () {
    if (window.remConfirm) return;

    function ensureContainer() {
        let el = document.getElementById('remModalContainer');
        if (el) return el;
        el = document.createElement('div');
        el.id = 'remModalContainer';
        document.body.appendChild(el);
        return el;
    }

    function escape(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    function renderModal({ id, title, body, confirmText, cancelText, danger, hasInput, inputConfig }) {
        const container = ensureContainer();
        const existing = document.getElementById(id);
        if (existing) existing.remove();

        const btnClass = danger ? 'btn-danger' : 'btn-primary';
        const inputHtml = hasInput ? `
            <div class="mt-3">
                ${inputConfig.label ? `<label class="form-label">${escape(inputConfig.label)}</label>` : ''}
                ${inputConfig.multiline
                    ? `<textarea id="${id}-input" class="form-control" rows="3" placeholder="${escape(inputConfig.placeholder || '')}"></textarea>`
                    : `<input id="${id}-input" type="text" class="form-control" placeholder="${escape(inputConfig.placeholder || '')}">`}
                ${inputConfig.requireText ? `
                    <div class="form-hint text-danger">Escribe <strong>${escape(inputConfig.requireText)}</strong> para continuar.</div>
                ` : ''}
            </div>` : '';

        const html = `
        <div class="modal fade" id="${id}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">${escape(title)}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <p class="mb-0">${body}</p>
                ${inputHtml}
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal" data-rem-action="cancel">${escape(cancelText || 'Cancelar')}</button>
                <button type="button" class="btn ${btnClass}" data-rem-action="confirm" id="${id}-confirm">${escape(confirmText || 'Confirmar')}</button>
              </div>
            </div>
          </div>
        </div>`;
        container.insertAdjacentHTML('beforeend', html);
        return document.getElementById(id);
    }

    window.remConfirm = function (opts) {
        opts = opts || {};
        return new Promise((resolve) => {
            const id = 'remConfirmModal-' + Date.now();
            const body = `<p>${escape(opts.message || '')}</p>`;
            const modal = renderModal({
                id,
                title: opts.title || 'Confirmar',
                body,
                confirmText: opts.confirmText || 'Confirmar',
                cancelText: opts.cancelText || 'Cancelar',
                danger: !!opts.danger,
            });
            const bsModal = new bootstrap.Modal(modal, { backdrop: 'static' });
            const confirmBtn = modal.querySelector(`#${id}-confirm`);
            let resolved = false;
            const cleanup = (value) => {
                if (resolved) return;
                resolved = true;
                bsModal.hide();
                modal.addEventListener('hidden.bs.modal', () => modal.remove(), { once: true });
                resolve(value);
            };
            confirmBtn.addEventListener('click', () => cleanup(true));
            modal.addEventListener('hidden.bs.modal', () => cleanup(false), { once: true });
            bsModal.show();
        });
    };

    window.remPrompt = function (opts) {
        opts = opts || {};
        return new Promise((resolve) => {
            const id = 'remPromptModal-' + Date.now();
            const body = `<p>${escape(opts.message || '')}</p>`;
            const modal = renderModal({
                id,
                title: opts.title || 'Solicitar dato',
                body,
                confirmText: opts.confirmText || 'Aceptar',
                cancelText: opts.cancelText || 'Cancelar',
                danger: !!opts.danger,
                hasInput: true,
                inputConfig: {
                    label: opts.label,
                    placeholder: opts.placeholder,
                    requireText: opts.requireText,
                    multiline: !!opts.multiline,
                }
            });
            const bsModal = new bootstrap.Modal(modal, { backdrop: 'static' });
            const confirmBtn = modal.querySelector(`#${id}-confirm`);
            const inputEl = modal.querySelector(`#${id}-input`);
            let resolved = false;
            const cleanup = (value) => {
                if (resolved) return;
                resolved = true;
                bsModal.hide();
                modal.addEventListener('hidden.bs.modal', () => modal.remove(), { once: true });
                resolve(value);
            };
            setTimeout(() => inputEl && inputEl.focus(), 200);
            confirmBtn.addEventListener('click', () => {
                const val = (inputEl.value || '').trim();
                if (opts.requireText && val !== opts.requireText) {
                    inputEl.classList.add('is-invalid');
                    return;
                }
                cleanup(val);
            });
            modal.addEventListener('hidden.bs.modal', () => cleanup(null), { once: true });
            bsModal.show();
        });
    };

    window.remAlert = function (opts) {
        opts = opts || {};
        return new Promise((resolve) => {
            const id = 'remAlertModal-' + Date.now();
            const variant = opts.variant || 'info';
            const colorMap = { info: 'primary', success: 'success', danger: 'danger', warning: 'warning' };
            const c = colorMap[variant] || 'primary';
            const body = `<div class="alert alert-${c} mb-0">${escape(opts.message || '')}</div>`;
            const modal = renderModal({
                id,
                title: opts.title || 'Aviso',
                body,
                confirmText: 'Aceptar',
                cancelText: '',
                danger: variant === 'danger',
            });
            modal.querySelector('[data-rem-action="cancel"]')?.remove();
            const bsModal = new bootstrap.Modal(modal);
            const confirmBtn = modal.querySelector(`#${id}-confirm`);
            const cleanup = () => {
                bsModal.hide();
                modal.addEventListener('hidden.bs.modal', () => modal.remove(), { once: true });
                resolve();
            };
            confirmBtn.addEventListener('click', cleanup);
            modal.addEventListener('hidden.bs.modal', cleanup, { once: true });
            bsModal.show();
        });
    };
})();
