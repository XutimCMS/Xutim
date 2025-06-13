import { Controller } from '@hotwired/stimulus';
import debounce from 'debounce';

export default class extends Controller {
    static values = {
        url: String,
    };

    static targets = [
        'newFileField',
        'newFileFields',
        'existingFileField',
        'searchInput',
        'form',
    ];

    connect() {
        this.debouncedFetch = debounce(this.fetchData.bind(this), 500);
    }

    search(event) {
        this.debouncedFetch();
    }

    fetchData() {
        const query = this.searchInputTarget.value.trim();

        const fetchUrl = `${this.urlValue}?searchTerm=${encodeURIComponent(query)}`;

        fetch(fetchUrl, {
            headers: { Accept: 'text/vnd.turbo-stream.html' },
        })
            .then((response) => response.text())
            .then((html) => {
                Turbo.renderStreamMessage(html);
            })
            .catch((error) => console.error('Error fetching data:', error));
    }

    toggleExistingFile(event) {
        if (event.currentTarget.value) {
            this.newFileFieldTarget.hidden = true;
        }
    }

    toggleNewFile(event) {
        if (event.currentTarget.value) {
            console.error('hide');
            this.newFileFieldsTarget.hidden = false;
            this.existingFileFieldTarget.hidden = true;
        } else {
            console.error('show');
            this.newFileFieldsTarget.hidden = true;
            this.existingFileFieldTarget.hidden = false;
        }
    }
}
