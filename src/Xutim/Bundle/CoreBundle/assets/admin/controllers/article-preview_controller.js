import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['frame'];
    #widths = {
        mobile: 390,
        tablet: 768,
        desktop: 1024,
    };

    connect() {
        this.frameTarget.addEventListener('load', this.handleLoad);
    }

    disconnect() {
        this.frameTarget.removeEventListener('load', this.handleLoad);
        if (this.ro) this.ro.disconnect();
    }

    setWidth(event) {
        const size = event.currentTarget.dataset.size;

        if (size === 'full') {
            this.element.style.width = '100%';
            this.element.classList.remove('justify-content-center');
        } else if (this.#widths[size]) {
            this.element.style.width = `${this.#widths[size]}px`;
            this.element.classList.add('justify-content-center');
        }

        this.updateHeight();
    }

    handleLoad = () => {
        const doc = this.frameTarget.contentDocument;
        if (!doc) return;

        this.updateHeight();

        this.ro?.disconnect();
        this.ro = new ResizeObserver(this.updateHeight);
        this.ro.observe(doc.documentElement);
    };

    updateHeight = () => {
        const doc = this.frameTarget.contentDocument;
        if (!doc) return;

        const height = doc.body.scrollHeight + 200;

        this.frameTarget.style.height = `${height}px`;
    };
}
