import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['frame'];

    #widths = { mobile: 390, tablet: 768, desktop: 1024 };

    connect() {
        this.handleLoad = this.handleLoad.bind(this);
        this.updateHeight = this.updateHeight.bind(this);

        this.frameTarget.addEventListener('load', this.handleLoad);
        // In case the iframe is already loaded (BFCache etc.)
        this.updateHeight();
    }

    disconnect() {
        this.frameTarget.removeEventListener('load', this.handleLoad);
        this.#cleanupObservers();
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

    handleLoad() {
        const doc = this.frameTarget.contentDocument;
        if (!doc) return;

        this.#cleanupObservers();

        // Recalculate when the iframe itself resizes (e.g., width buttons)
        this.iframeRO = new ResizeObserver(this.updateHeight);
        this.iframeRO.observe(this.frameTarget);

        // Recalculate on inner document reflows/resizes
        this.docRO = new ResizeObserver(this.updateHeight);
        this.docRO.observe(doc.documentElement);
        this.docRO.observe(doc.body);

        // Recalculate on DOM changes (late content, editors, etc.)
        this.mo = new MutationObserver(this.updateHeight);
        this.mo.observe(doc.documentElement, {
            childList: true,
            subtree: true,
            attributes: true,
        });

        // Recalculate after fonts/images load
        doc.fonts?.ready.then(this.updateHeight).catch(() => {});
        doc.querySelectorAll('img').forEach((img) => {
            if (!img.complete)
                img.addEventListener('load', this.updateHeight, { once: true });
        });

        this.updateHeight();
    }

    updateHeight() {
        const doc = this.frameTarget.contentDocument;
        if (!doc) return;

        // Wait one frame to capture layout after reflow
        requestAnimationFrame(() => {
            const body = doc.body;
            const html = doc.documentElement;
            const height = Math.max(
                body.scrollHeight,
                body.offsetHeight,
                body.clientHeight,
                html.scrollHeight,
                html.offsetHeight,
                html.clientHeight,
            );

            if (height) this.frameTarget.style.height = `${height}px`;
        });
    }

    #cleanupObservers() {
        this.iframeRO?.disconnect();
        this.docRO?.disconnect();
        this.mo?.disconnect();
    }
}
