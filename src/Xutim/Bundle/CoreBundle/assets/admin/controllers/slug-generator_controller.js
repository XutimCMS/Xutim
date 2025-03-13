import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

export default class extends Controller {
    static values = {
        url: String,
        enabled: Boolean,
        locale: String,
    };

    static targets = ['slugField', 'localeField', 'titleField'];

    fetchAndReplaceSlugOnTitle(event) {
        if (this.enabledValue === true) {
            this.#updateSlug(event.currentTarget.value, this.#getLocale());
        }
    }

    fetchAndReplaceSlugOnLocale(event) {
        this.#updateSlug(
            this.titleFieldTarget.value,
            event.currentTarget.value,
        );
    }

    unblockSlugField(event) {
        const attr = this.slugFieldTarget.getAttribute('readonly');
        if (attr === null) {
            this.#disableSlugField();
        } else {
            this.#enableSlugField();
        }
    }

    #updateSlug(title, locale) {
        axios
            .get(this.urlValue, {
                params: {
                    title: title,
                    locale: locale,
                },
            })
            .then((response) => {
                this.slugFieldTarget.value = response.data;
                this.slugFieldTarget.setAttribute('readonly', 'readonly');
                if (!this.slugFieldTarget.classList.contains('text-bg-light')) {
                    this.slugFieldTarget.classList.add('text-bg-light');
                }
            });
    }

    #disableSlugField() {
        this.slugFieldTarget.setAttribute('readonly', 'readonly');
        if (!this.slugFieldTarget.classList.contains('text-bg-light')) {
            this.slugFieldTarget.classList.add('text-bg-light');
        }
        this.enabledValue = false;
    }
    #enableSlugField() {
        this.slugFieldTarget.removeAttribute('readonly');
        this.slugFieldTarget.classList.remove('text-bg-light');
        this.enabledValue = true;
    }

    #getLocale() {
        if (this.localeValue) {
            return this.localeValue;
        }
        if (this.hasLocaleFieldTarget) {
            return this.localeFieldTarget.value;
        }

        return 'en';
    }
}
