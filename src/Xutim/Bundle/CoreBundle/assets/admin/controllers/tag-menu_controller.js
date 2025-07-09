import { Controller } from '@hotwired/stimulus';
import { renderStreamMessage } from '@hotwired/turbo';

export default class extends Controller {
    static values = {
        csrfToken: String,
    };

    static targets = ['item', 'search'];

    connect() {
        this.element
            .querySelector('.dropdown-menu')
            ?.addEventListener('click', (e) => e.stopPropagation());

        const button = this.element.querySelector(
            '[data-bs-toggle="dropdown"]',
        );
        if (!button) return;

        this.observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (
                    mutation.attributeName === 'aria-expanded' &&
                    button.getAttribute('aria-expanded') === 'true'
                ) {
                    setTimeout(() => this.searchTarget?.focus(), 10);
                }
            });
        });

        this.observer.observe(button, { attributes: true });
    }

    disconnect() {
        this.observer?.disconnect();
    }

    submitChange(event) {
        event.preventDefault();
        event.stopPropagation();

        const url = event.target.dataset.updateUrl;
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.csrfTokenValue,
                Accept: 'text/vnd.turbo-stream.html',
            },
            body: '{}',
        })
            .then((response) => {
                if (!response.ok)
                    throw new Error('Network response was not ok');
                return response.text();
            })
            .then(renderStreamMessage)
            .catch((error) => console.error('Turbo update failed:', error));
    }

    filter(event) {
        const query = event.target.value.toLowerCase();

        this.itemTargets.forEach((el) => {
            const name = el.dataset.name || '';
            console.log(name.includes(query));
            if (name.includes(query)) {
                el.classList.remove('d-none');
            } else {
                el.classList.add('d-none');
            }
        });
    }
}
