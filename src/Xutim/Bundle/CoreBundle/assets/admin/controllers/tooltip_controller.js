import { Controller } from '@hotwired/stimulus';
import { computePosition, flip, shift, offset } from '@floating-ui/dom';

const CLASSES = 'fixed z-[9999] rounded-md px-2 py-1 text-[11px] font-medium leading-snug whitespace-nowrap pointer-events-none opacity-0 transition-opacity duration-150 bg-[var(--tooltip-bg,#1a1a1a)] text-[var(--tooltip-text,#fff)] shadow-[0_2px_8px_rgba(0,0,0,0.2)]';

export default class extends Controller {
    connect() {
        const title = this.element.getAttribute('title');
        if (!title) return;

        this.element.removeAttribute('title');
        this._text = title;

        this._el = document.createElement('div');
        this._el.className = CLASSES;
        this._el.textContent = title;

        this._show = this.#show.bind(this);
        this._hide = this.#hide.bind(this);
        this.element.addEventListener('mouseenter', this._show);
        this.element.addEventListener('mouseleave', this._hide);
    }

    disconnect() {
        this.#hide();
        this.element.removeEventListener('mouseenter', this._show);
        this.element.removeEventListener('mouseleave', this._hide);
        if (this._text) {
            this.element.setAttribute('title', this._text);
        }
    }

    async #show() {
        document.body.appendChild(this._el);
        const { x, y } = await computePosition(this.element, this._el, {
            placement: 'top',
            middleware: [offset(6), flip(), shift({ padding: 8 })],
        });
        Object.assign(this._el.style, { left: `${x}px`, top: `${y}px` });
        requestAnimationFrame(() => { this._el.style.opacity = '1'; });
    }

    #hide() {
        this._el.style.opacity = '0';
        this._el.remove();
    }
}
