import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.visible = new Set(
            JSON.parse(localStorage.getItem('snippet.visibleLangs') || '[]'),
        );
        this.apply();
    }

    toggle(event) {
        event.preventDefault();
        const pill = event.currentTarget;
        const lang = pill.dataset.lang;
        if (!lang) return;

        if (this.visible.has(lang)) this.visible.delete(lang);
        else this.visible.add(lang);

        this.#persist();
        this.#applyPill(pill, this.visible.has(lang));
        this.#applyColumns();
    }

    apply() {
        if (this.visible.size === 0) {
            this.element.querySelectorAll('[data-lang]').forEach((pill) => {
                const isActive = pill.classList.contains('bg-outline-primary');
                if (isActive) this.visible.add(pill.dataset.lang);
            });
            this.#persist();
        }

        this.element.querySelectorAll('[data-lang]').forEach((pill) => {
            this.#applyPill(pill, this.visible.has(pill.dataset.lang));
        });

        this.#applyColumns();
    }

    #applyColumns() {
        document.querySelectorAll('[data-lang]').forEach((el) => {
            if (el.closest("[data-controller='lang-bar']") === this.element)
                return;
            el.style.display = this.visible.has(el.dataset.lang) ? '' : 'none';
        });
    }

    #applyPill(pill, on) {
        const span = pill.querySelector('span');
        const onClasses = ['bg-outline-primary', 'border-3', 'border-primary'];
        const offClasses = ['text-secondary'];
        span.classList.remove(...(on ? offClasses : onClasses));
        span.classList.add(...(on ? onClasses : offClasses));
        pill.setAttribute('aria-pressed', on ? 'true' : 'false');
    }

    #persist() {
        localStorage.setItem(
            'snippet.visibleLangs',
            JSON.stringify([...this.visible]),
        );
    }
}
