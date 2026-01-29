import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['toggleBtn', 'checkbox'];
    static values = {
        baseUrl: String,
    };

    connect() {
        const params = new URLSearchParams(window.location.search);
        this.filterEnabled = params.get('filter') === 'changed';

        if (this.filterEnabled) {
            this.#applyFilter();
            this.#updateToggleButton();
        }
    }

    toggle() {
        this.filterEnabled = !this.filterEnabled;
        this.#applyFilter();
        this.#updateToggleButton();
        this.#updateFilterUrl();
    }

    selectRevision(event) {
        const checkbox = event.target;
        const checked = this.checkboxTargets.filter((cb) => cb.checked);

        if (checked.length > 2) {
            // Deselect the oldest (first checked that isn't the current one)
            const toUncheck = checked.find((cb) => cb !== checkbox);
            if (toUncheck) toUncheck.checked = false;
        }

        const nowChecked = this.checkboxTargets.filter((cb) => cb.checked);
        if (nowChecked.length === 2) {
            this.#navigateToComparison(nowChecked);
        }
    }

    #navigateToComparison(checked) {
        // Sort by data-index to determine old vs new
        const sorted = [...checked].sort(
            (a, b) =>
                parseInt(a.dataset.index, 10) - parseInt(b.dataset.index, 10),
        );
        const oldId = sorted[0].value;
        const newId = sorted[1].value;

        let url = `${this.baseUrlValue}/${oldId}/${newId}`;
        if (this.filterEnabled) {
            url += '?filter=changed';
        }
        window.location.href = url;
    }

    #applyFilter() {
        const unchangedBlocks =
            this.element.querySelectorAll('.block-unchanged');

        unchangedBlocks.forEach((block) => {
            block.classList.toggle('d-none', this.filterEnabled);
        });
    }

    #updateToggleButton() {
        if (!this.hasToggleBtnTarget) return;

        this.toggleBtnTarget.classList.toggle(
            'btn-outline-primary',
            !this.filterEnabled,
        );
        this.toggleBtnTarget.classList.toggle(
            'btn-primary',
            this.filterEnabled,
        );
    }

    #updateFilterUrl() {
        const url = new URL(window.location.href);
        if (this.filterEnabled) {
            url.searchParams.set('filter', 'changed');
        } else {
            url.searchParams.delete('filter');
        }
        window.history.replaceState({}, '', url.toString());
    }
}
