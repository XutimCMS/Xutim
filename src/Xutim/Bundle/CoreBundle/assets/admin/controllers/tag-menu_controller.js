import { Controller } from '@hotwired/stimulus';
import { renderStreamMessage } from '@hotwired/turbo';

export default class extends Controller {
    static values = {
        articleId: String,
        csrfToken: String,
    };
    submitChange(event) {
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
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then((html) => {
                renderStreamMessage(html); // renders turbo-stream response from server
            })
            .catch((error) => {
                console.error('Turbo update failed:', error);
            });
    }
}
