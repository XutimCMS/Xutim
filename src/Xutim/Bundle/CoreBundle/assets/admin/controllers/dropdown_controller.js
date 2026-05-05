import { Controller } from '@hotwired/stimulus';
import { computePosition, flip, shift, offset } from '@floating-ui/dom';

export default class extends Controller {
    static targets = ['menu'];
    static values = { placement: { type: String, default: 'bottom-start' } };

    toggle(event) {
        event.preventDefault();
        event.stopPropagation();
        const isHidden = this.menuTarget.classList.toggle('hidden');
        if (!isHidden) {
            this.#updatePosition();
        } else {
            this.#resetPosition();
        }
    }

    hide() {
        this.menuTarget.classList.add('hidden');
        this.#resetPosition();
    }

    async #updatePosition() {
        const { x, y } = await computePosition(this.element, this.menuTarget, {
            placement: this.placementValue,
            middleware: [offset(6), flip(), shift({ padding: 8 })],
        });
        // Reset right/bottom so the Tailwind placement classes
        // (right-0, bottom-full, …) on the menu don't fight with
        // floating-ui's computed left/top.
        Object.assign(this.menuTarget.style, {
            left: `${x}px`,
            top: `${y}px`,
            right: 'auto',
            bottom: 'auto',
        });
    }

    #resetPosition() {
        Object.assign(this.menuTarget.style, {
            left: '',
            top: '',
            right: '',
            bottom: '',
        });
    }

    connect() {
        this.menuTarget.style.position = 'absolute';
        this._clickOutside = (e) => {
            if (!this.element.contains(e.target)) {
                this.hide();
            }
        };
        document.addEventListener('click', this._clickOutside);
    }

    disconnect() {
        document.removeEventListener('click', this._clickOutside);
    }
}
