import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        regenerateUrl: String,
        mercureTopic: String,
    };

    static targets = ['button', 'progress'];

    #eventSource;
    #originalButtonHtml;
    #regenerating = false;

    connect() {
        if (this.#hasPendingBadges()) {
            this.#markBadgesGenerating();
            this.#subscribeToMercure();
        }

        this.element.closest('body')?.addEventListener('xutim-media--focal-point:saved', this.#onFocalPointSaved);
    }

    disconnect() {
        this.#closeEventSource();
        this.element.closest('body')?.removeEventListener('xutim-media--focal-point:saved', this.#onFocalPointSaved);
    }

    #onFocalPointSaved = () => {
        this.#regenerating = true;
        this.#disableButton();
        this.#markAllBadgesGenerating();
        this.#closeEventSource();
        this.#subscribeToMercure();
    };

    async regenerate(event) {
        event.preventDefault();

        this.#regenerating = true;
        this.#disableButton();
        this.#markAllBadgesGenerating();

        try {
            const response = await fetch(this.regenerateUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                this.#reset();
                return;
            }

            this.#closeEventSource();
            this.#subscribeToMercure();
        } catch {
            this.#reset();
        }
    }

    #subscribeToMercure() {
        if (!this.hasMercureTopicValue || !this.mercureTopicValue) {
            if (this.#regenerating) {
                this.#showBackgroundMessage();
            }
            return;
        }

        try {
            const url = new URL(this.mercureTopicValue);
            this.#eventSource = new EventSource(url, { withCredentials: true });
        } catch {
            if (this.#regenerating) {
                this.#showBackgroundMessage();
            }
            return;
        }

        this.#eventSource.onmessage = (event) => {
            const data = JSON.parse(event.data);

            if (data.type === 'preset_complete') {
                this.#markBadgeComplete(data.preset);
            } else if (data.type === 'complete') {
                this.#closeEventSource();
                window.location.reload();
            }
        };

        this.#eventSource.onerror = () => {
            this.#closeEventSource();
            if (this.#regenerating) {
                this.#showBackgroundMessage();
            }
        };
    }

    #hasPendingBadges() {
        return this.element.querySelector('[data-preset-badge].bg-warning') !== null;
    }

    #markBadgesGenerating() {
        this.element.querySelectorAll('[data-preset-badge].bg-warning').forEach((badge) => {
            badge.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 0.7rem; height: 0.7rem;"></span>';
        });
    }

    #markAllBadgesGenerating() {
        this.element.querySelectorAll('[data-preset-badge]').forEach((badge) => {
            badge.className = 'badge bg-warning text-white';
            badge.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 0.7rem; height: 0.7rem;"></span>';
        });
    }

    #markBadgeComplete(presetName) {
        const badge = this.element.querySelector(`[data-preset-badge="${presetName}"]`);
        if (badge) {
            badge.className = 'badge bg-success text-white';
            badge.innerHTML = '&#10003;';
        }
    }

    #disableButton() {
        if (this.hasButtonTarget) {
            this.#originalButtonHtml = this.buttonTarget.innerHTML;
            this.buttonTarget.disabled = true;
            this.buttonTarget.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1"></span>Regenerating...';
        }
    }

    #showBackgroundMessage() {
        if (this.hasProgressTarget) {
            this.progressTarget.classList.remove('d-none');
            this.progressTarget.innerHTML =
                'Processing in background... <a href="" class="ms-1">Reload page</a>';
        }
        if (this.hasButtonTarget) {
            this.buttonTarget.disabled = true;
            this.buttonTarget.innerHTML = 'Processing...';
        }
    }

    #reset() {
        this.#regenerating = false;
        if (this.hasButtonTarget) {
            this.buttonTarget.disabled = false;
            this.buttonTarget.innerHTML = this.#originalButtonHtml;
        }

        this.element.querySelectorAll('[data-preset-badge].bg-warning').forEach((badge) => {
            badge.textContent = 'Not generated';
        });
    }

    #closeEventSource() {
        if (this.#eventSource) {
            this.#eventSource.close();
            this.#eventSource = null;
        }
    }
}
