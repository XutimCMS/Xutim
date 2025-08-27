import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input'];
    static values = {
        url: String,
    };

    connect() {
        // Remember the initial values to detect changes
        this.inputTargets.forEach((el) => {
            el.dataset.savedValue = this.#normalize(el.value);
        });
    }

    save(event) {
        const input = event.target;
        const id = input.dataset.snippetId;
        const locale = input.dataset.locale;
        const value = input.value;
        const token = input.dataset.token;

        const current = this.#normalize(input.value);
        const lastSaved = input.dataset.savedValue ?? '';
        // No changes
        if (current === lastSaved) return;

        fetch(this.urlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify({ id, locale, value, _token: token }),
        })
            .then((response) => {
                if (response.ok) {
                    this.#flashSuccess(event.target);
                } else {
                    this.#flashError(event.target);
                }
            })
            .catch(() => this.#flashError());
    }

    #flashSuccess(elem) {
        elem.classList.remove('is-invalid');
        elem.classList.add('is-valid');

        setTimeout(() => {
            elem.classList.remove('is-valid');
        }, 2000);
    }

    #flashError(elem) {
        elem.classList.remove('is-valid');
        elem.classList.add('is-invalid');
    }

    #normalize(text) {
        return (text ?? '').replace(/\r\n/g, '\n');
    }
}
