import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['checkbox', 'selectAll', 'actions', 'count'];

    connect() {
        this.update();
    }

    toggle() {
        this.update();
    }

    toggleAll() {
        const checked = this.selectAllTarget.checked;
        this.checkboxTargets.forEach(cb => cb.checked = checked);
        this.update();
    }

    update() {
        const selected = this.selectedIds;
        const hasSelection = selected.length > 0;

        this.actionsTarget.classList.toggle('d-none', !hasSelection);
        this.countTarget.textContent = selected.length;

        if (this.hasSelectAllTarget) {
            const allChecked = this.checkboxTargets.length > 0
                && this.checkboxTargets.every(cb => cb.checked);
            this.selectAllTarget.checked = allChecked;
            this.selectAllTarget.indeterminate = hasSelection && !allChecked;
        }
    }

    markRead(event) {
        this.submitBulk('read', event.target.closest('form'));
    }

    markUnread(event) {
        this.submitBulk('unread', event.target.closest('form'));
    }

    submitBulk(action, form) {
        const ids = this.selectedIds;
        if (ids.length === 0) return;

        form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());

        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });

        form.querySelector('input[name="action"]').value = action;
        form.requestSubmit();
    }

    get selectedIds() {
        return this.checkboxTargets
            .filter(cb => cb.checked)
            .map(cb => cb.value);
    }
}
