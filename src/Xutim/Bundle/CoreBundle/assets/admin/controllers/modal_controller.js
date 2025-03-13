import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['dialog', 'dynamicContent', 'loadingContent'];

    observer = null;

    connect() {
        if (this.hasDynamicContentTarget) {
            // when the content changes, call this.open()
            this.observer = new MutationObserver(() => {
                const shouldOpen =
                    this.dynamicContentTarget.innerHTML.trim().length > 0;

                if (shouldOpen && !this.dialogTarget.open) {
                    this.open();
                } else if (!shouldOpen && this.dialogTarget.open) {
                    this.close();
                }
            });

            this.observer.observe(this.dynamicContentTarget, {
                childList: true,
                charaterData: true,
                subtree: true,
            });
        }
    }

    disconnect() {
        if (this.observer) {
            this.observer.disconnect();
        }
        if (this.dialogTarget.open) {
            this.close();
        }
    }

    open() {
        this.dialogTarget.showModal();
        // Disable scroll
        //document.body.classList.add('overflow-hidden');
    }

    close() {
        this.dialogTarget.close();
        // Enable scroll
        //document.body.classList.remove('overflow-hidden');
    }

    clickOutside(event) {
        if (event.target === this.dialogTarget) {
            this.dialogTarget.close();
        }
    }

    showLoading() {
        if (this.dialogTarget.open) {
            return;
        }

        this.dynamicContentTarget.innerHTML =
            this.loadingContentTarget.innerHTML;
    }
}
