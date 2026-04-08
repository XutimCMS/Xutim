import { Controller } from '@hotwired/stimulus';

const XL_BREAKPOINT = 1280;

export default class extends Controller {
    static targets = ['panel', 'backdrop', 'openTab', 'closeTab'];

    connect() {
        this._onResize = () => this.#applyForBreakpoint();
        this._onGlobalToggle = () => this.toggle();

        if (window.innerWidth >= XL_BREAKPOINT) {
            this.#open(false);
        } else {
            this.#close();
        }

        window.addEventListener('resize', this._onResize);
        window.addEventListener('sidebar:toggle', this._onGlobalToggle);
    }

    disconnect() {
        window.removeEventListener('resize', this._onResize);
        window.removeEventListener('sidebar:toggle', this._onGlobalToggle);
        document.body.classList.remove('article-sidebar-open');
    }

    toggle() {
        if (this.#isOpen()) {
            this.#close();
        } else {
            this.#open(true);
        }
    }

    #isOpen() {
        return !this.panelTarget.classList.contains('translate-x-full');
    }

    #open(showBackdrop) {
        this.panelTarget.classList.remove('translate-x-full');
        document.body.classList.add('article-sidebar-open');
        this.openTabTarget.classList.add('hidden');
        this.closeTabTarget.classList.remove('hidden');

        if (showBackdrop && window.innerWidth < XL_BREAKPOINT) {
            this.backdropTarget.classList.remove('hidden');
        }
    }

    #close() {
        this.panelTarget.classList.add('translate-x-full');
        document.body.classList.remove('article-sidebar-open');
        this.backdropTarget.classList.add('hidden');
        this.openTabTarget.classList.remove('hidden');
        this.closeTabTarget.classList.add('hidden');
    }

    #applyForBreakpoint() {
        if (window.innerWidth >= XL_BREAKPOINT) {
            this.backdropTarget.classList.add('hidden');
        } else if (this.#isOpen()) {
            this.backdropTarget.classList.remove('hidden');
        }
    }
}
