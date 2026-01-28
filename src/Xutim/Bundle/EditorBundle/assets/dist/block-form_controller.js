import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.autoFocus();
    }

    autoFocus() {
        const firstInput = this.element.querySelector(
            'input:not([type="hidden"]), textarea, select'
        );
        if (firstInput) {
            firstInput.focus();

            if (firstInput.tagName === 'INPUT' || firstInput.tagName === 'TEXTAREA') {
                const length = firstInput.value.length;
                firstInput.setSelectionRange(length, length);
            }
        }
    }

    save(event) {
        const form = event.target;
        const submitButton = form.querySelector('button[type="submit"]');

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        }
    }
}
