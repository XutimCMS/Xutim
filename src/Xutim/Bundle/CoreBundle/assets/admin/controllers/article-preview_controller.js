import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
    };

    static targets = ['preview'];

    connect() {
        this.loadPreview();
    }

    loadPreview() {
        fetch(this.urlValue)
            .then((response) => response.text())
            .then((html) => {
                // Create a Shadow DOM
                const shadowRoot = this.previewTarget.attachShadow({
                    mode: 'open',
                });
                shadowRoot.innerHTML = html;
            })
            .catch((error) => {
                console.error(error);
                this.previewTarget.innerHTML = 'Failed to load preview.';
            });
    }
}
