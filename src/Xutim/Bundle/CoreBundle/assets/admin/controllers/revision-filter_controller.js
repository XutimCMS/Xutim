import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['checkbox'];
    static values = {
        baseUrl: String,
    };

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

        const url = new URL(`${this.baseUrlValue}/${oldId}/${newId}`, window.location.href);
        url.search = window.location.search;
        window.location.href = url.toString();
    }
}
