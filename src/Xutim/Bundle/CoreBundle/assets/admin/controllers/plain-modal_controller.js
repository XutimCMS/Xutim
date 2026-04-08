import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['localeSelect'];

    initialize() {
        this.activate();
    }

    activate() {
        if (this.element.tagName === 'DIALOG') {
            this.element.showModal();
        } else {
            // Wrap in dialog if not already a dialog element
            const dialog = document.createElement('dialog');
            dialog.className = 'fixed inset-0 z-[60] m-auto max-w-lg rounded-xl border border-border bg-surface shadow-xl backdrop:bg-black/50 p-0';
            this.element.parentNode.insertBefore(dialog, this.element);
            dialog.appendChild(this.element);
            dialog.showModal();
            this._dialog = dialog;
        }
    }

    dismissModal() {
        const dialog = this.element.closest('dialog') || this._dialog;
        if (dialog) {
            dialog.close();
            dialog.remove();
        }
    }

    getResponse() {
        const url = this.localeSelectTarget.options[
            this.localeSelectTarget.selectedIndex
        ].dataset.urlValue;
        window.location = url;
    }
}
