const offlineNotice = document.querySelector('[data-offline-notice]');
const progressStatus = document.querySelector('[data-progress-status]');

function updateConnectionNotice() {
    if (offlineNotice) {
        offlineNotice.hidden = navigator.onLine;
    }
}

function resetSubmissionState() {
    document.querySelectorAll('form[data-submitting]').forEach((form) => {
        const button = form.querySelector('button[data-submitting]');
        const label = button?.querySelector('[data-original-label]');

        if (label) {
            label.textContent = label.dataset.originalLabel;
            delete label.dataset.originalLabel;
        }

        if (button) {
            button.disabled = false;
            delete button.dataset.submitting;
        }

        form.removeAttribute('aria-busy');
        delete form.dataset.submitting;
    });

    if (progressStatus) {
        progressStatus.textContent = '';
    }
}

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (event.defaultPrevented || !(form instanceof HTMLFormElement) || !form.matches('[data-submit-state]')) {
        return;
    }

    if (form.hasAttribute('data-submitting') || !navigator.onLine) {
        event.preventDefault();
        updateConnectionNotice();
        return;
    }

    if (form.dataset.confirm && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();
        return;
    }

    form.dataset.submitting = 'true';
    form.setAttribute('aria-busy', 'true');

    const button = event.submitter;
    const message = button?.dataset.loading || 'Saving…';

    if (button instanceof HTMLButtonElement) {
        const label = button.querySelector('[data-button-label]');
        if (label) {
            label.dataset.originalLabel = label.textContent;
            label.textContent = message;
        }
        button.dataset.submitting = 'true';
        button.disabled = true;
    }

    if (progressStatus) {
        progressStatus.textContent = message;
    }
});

document.addEventListener('invalid', (event) => {
    if (!(event.target instanceof HTMLElement) || !event.target.closest('form[data-submit-state]')) {
        return;
    }

    let disclosure = event.target.closest('details');
    while (disclosure) {
        disclosure.open = true;
        disclosure = disclosure.parentElement?.closest('details');
    }
}, true);

window.addEventListener('online', updateConnectionNotice);
window.addEventListener('offline', updateConnectionNotice);
window.addEventListener('pageshow', resetSubmissionState);

updateConnectionNotice();
document.querySelector('[data-error-summary]')?.focus();
