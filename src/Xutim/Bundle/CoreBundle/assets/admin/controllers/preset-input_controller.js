import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button', 'input'];

    select(event) {
        const value = parseInt(event.currentTarget.dataset.value, 10);
        this.inputTarget.value = value;
        this.updateButtons(value);
    }

    inputChanged() {
        const value = parseInt(this.inputTarget.value, 10);
        this.updateButtons(value);
    }

    updateButtons(activeValue) {
        this.buttonTargets.forEach(btn => {
            const isActive = parseInt(btn.dataset.value, 10) === activeValue;
            btn.classList.toggle('active', isActive);
            btn.classList.toggle('btn-primary', isActive);
            btn.classList.toggle('btn-outline-primary', !isActive);
        });
    }
}
