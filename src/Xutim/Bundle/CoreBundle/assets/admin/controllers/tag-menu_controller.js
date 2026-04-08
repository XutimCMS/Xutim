import { Controller } from '@hotwired/stimulus';
import { renderStreamMessage } from '@hotwired/turbo';

export default class extends Controller {
    static values = {
        csrfToken: String,
    };

    static targets = ['item', 'search', 'menu'];

    connect() {
        this._onClickOutside = this._onClickOutside.bind(this);
    }

    disconnect() {
        document.removeEventListener('click', this._onClickOutside);
    }

    toggle(event) {
        event.stopPropagation();
        const menu = this.menuTarget;
        const isHidden = menu.classList.contains('hidden');

        if (isHidden) {
            menu.classList.remove('hidden');
            document.addEventListener('click', this._onClickOutside);
            setTimeout(() => this.searchTarget?.focus(), 10);
        } else {
            this._close();
        }
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
            if (name.includes(query)) {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        });
    }

    _close() {
        this.menuTarget.classList.add('hidden');
        document.removeEventListener('click', this._onClickOutside);
    }

    _onClickOutside(event) {
        if (!this.element.contains(event.target)) {
            this._close();
        }
    }
}
