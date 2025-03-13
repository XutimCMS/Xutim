import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

export default class extends Controller {
    static values = {
        reorderUrl: String,
    };

    connect() {
        const url = this.reorderUrlValue;
        const baseUrl = window.location.origin;
        Sortable.create(this.element, {
            animation: 200,
            handle: '.draggable-handle',
            draggable: '.draggable-item',
            onEnd: (event) => {
                let urlWithParams = new URL(url, baseUrl);
                urlWithParams.searchParams.append('startPos', event.oldIndex);
                urlWithParams.searchParams.append('endPos', event.newIndex);
                fetch(urlWithParams, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({}),
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then((data) => {
                        // Handle the response data if needed
                    })
                    .catch((error) => {
                        console.error(
                            'There was a problem with the fetch operation:',
                            error,
                        );
                    });
            },
        });
    }
}
