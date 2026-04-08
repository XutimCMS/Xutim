import { Controller } from '@hotwired/stimulus';
import { computePosition, flip, shift, offset } from '@floating-ui/dom';

export default class extends Controller {
    static targets = ['panel'];

    toggle() {
        const isHidden = this.panelTarget.classList.toggle('hidden');
        if (!isHidden) {
            this.#updatePosition();
        }
    }

    async #updatePosition() {
        const { x, y } = await computePosition(this.element, this.panelTarget, {
            placement: 'bottom-start',
            middleware: [offset(6), flip(), shift({ padding: 8 })],
        });
        Object.assign(this.panelTarget.style, { left: `${x}px`, top: `${y}px` });
    }

    connect() {
        this.panelTarget.style.position = 'absolute';
        this._clickOutside = (e) => {
            if (!this.element.contains(e.target)) {
                this.panelTarget.classList.add('hidden');
            }
        };
        document.addEventListener('click', this._clickOutside);
    }

    disconnect() {
        document.removeEventListener('click', this._clickOutside);
    }
}
