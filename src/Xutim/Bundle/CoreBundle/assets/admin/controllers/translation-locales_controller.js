import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['localesList', 'radioGroup'];

    toggle() {
        const checked = this.radioGroupTarget.querySelector(
            'input[type="radio"]:checked',
        );
        this.localesListTarget.hidden = checked?.value === '1';
        this.clearErrors();
    }

    toggleBadge(event) {
        const label = event.target.closest('label');
        const checked = event.target.checked;
        label.classList.toggle('bg-primary', checked);
        label.classList.toggle('text-white', checked);
        label.classList.toggle('badge-outline', !checked);
        label.classList.toggle('text-primary', !checked);
        this.clearErrors();
    }

    clearErrors() {
        this.element.classList.remove('border', 'border-danger', 'bg-danger', 'bg-opacity-10');
        this.element.querySelectorAll('.invalid-feedback, .form-error-message').forEach(el => el.remove());
    }
}
