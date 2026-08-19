(() => {
    const nativeConfirm = window.confirm.bind(window);
    let allowLegacyConfirmOnce = false;

    // Keep legacy form listeners working while the application migrates away
    // from browser-native confirm dialogs. A confirmed BizHR modal submission
    // is allowed through the old listener exactly once.
    window.confirm = (message) => {
        if (allowLegacyConfirmOnce) {
            allowLegacyConfirmOnce = false;
            return true;
        }

        return nativeConfirm(message);
    };

    document.addEventListener('DOMContentLoaded', () => {
        const dialog = document.querySelector('[data-app-confirm-dialog]');
        if (! dialog || ! window.bootstrap?.Modal) return;

        const modal = window.bootstrap.Modal.getOrCreateInstance(dialog, {
            backdrop: 'static',
            keyboard: true,
        });
        const title = dialog.querySelector('[data-confirm-title]');
        const message = dialog.querySelector('[data-confirm-message]');
        const iconWrap = dialog.querySelector('[data-confirm-icon-wrap]');
        const icon = dialog.querySelector('[data-confirm-icon]');
        const confirmButton = dialog.querySelector('[data-confirm-submit]');
        const confirmLabel = dialog.querySelector('[data-confirm-submit-label]');
        const approvedForms = new WeakSet();
        let pendingForm = null;
        let pendingSubmitter = null;

        const tones = {
            danger: {
                button: 'btn-danger',
                iconWrap: 'bg-danger-subtle text-danger',
                icon: 'fa-triangle-exclamation',
            },
            warning: {
                button: 'btn-warning',
                iconWrap: 'bg-warning-subtle text-warning-emphasis',
                icon: 'fa-triangle-exclamation',
            },
            success: {
                button: 'btn-success',
                iconWrap: 'bg-success-subtle text-success',
                icon: 'fa-circle-check',
            },
            primary: {
                button: 'btn-primary',
                iconWrap: 'bg-primary-subtle text-primary',
                icon: 'fa-circle-question',
            },
        };

        const resetTone = () => {
            confirmButton.classList.remove('btn-danger', 'btn-warning', 'btn-success', 'btn-primary');
            iconWrap.classList.remove(
                'bg-danger-subtle', 'text-danger',
                'bg-warning-subtle', 'text-warning-emphasis',
                'bg-success-subtle', 'text-success',
                'bg-primary-subtle', 'text-primary',
            );
            icon.classList.remove('fa-triangle-exclamation', 'fa-circle-check', 'fa-circle-question');
        };

        const inferAction = (form, submitter) => {
            const explicit = form.dataset.confirmAction || submitter?.dataset.confirmAction;
            if (explicit) return explicit;

            const text = submitter?.textContent?.trim();
            return text || 'Continue';
        };

        const showConfirmation = (form, submitter) => {
            const toneName = form.dataset.confirmTone || submitter?.dataset.confirmTone || 'danger';
            const tone = tones[toneName] || tones.danger;
            const action = inferAction(form, submitter);

            title.textContent = form.dataset.confirmTitle || submitter?.dataset.confirmTitle || `Confirm ${action.toLowerCase()}`;
            message.textContent = form.dataset.confirm || submitter?.dataset.confirm || 'This action may change or remove data. Please confirm before continuing.';
            confirmLabel.textContent = action;

            resetTone();
            confirmButton.classList.add(tone.button);
            tone.iconWrap.split(' ').forEach((className) => iconWrap.classList.add(className));
            icon.classList.add(tone.icon);

            pendingForm = form;
            pendingSubmitter = submitter;
            modal.show();
        };

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || ! form.matches('[data-confirm]')) return;

            if (approvedForms.has(form)) {
                approvedForms.delete(form);
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();
            showConfirmation(form, event.submitter || null);
        }, true);

        confirmButton.addEventListener('click', () => {
            if (! pendingForm) return;

            const form = pendingForm;
            const submitter = pendingSubmitter;
            pendingForm = null;
            pendingSubmitter = null;
            approvedForms.add(form);
            allowLegacyConfirmOnce = true;
            modal.hide();

            if (submitter instanceof HTMLElement && typeof form.requestSubmit === 'function') {
                form.requestSubmit(submitter);
                return;
            }

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }

            form.submit();
        });

        dialog.addEventListener('hidden.bs.modal', () => {
            pendingForm = null;
            pendingSubmitter = null;
        });
    });
})();
