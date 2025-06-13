import { Controller } from '@hotwired/stimulus';
import ImageGalleryModal from './../lib/ImageGalleryModal.js';

export default class extends Controller {
    static targets = ['hiddenInput', 'preview', 'placeholder'];
    static values = {
        url: String,
        initialId: String,
        initialUrl: String,
    };

    connect() {
        if (this.initialUrlValue) {
            this.previewTarget.src = this.initialUrlValue;
            this.previewTarget.hidden = false;
            this.placeholderTarget.hidden = true;
        }

        this.modal = new ImageGalleryModal({
            galleryUrl: this.urlValue,
            onSelect: (image) => this.#setImage(image),
        });
    }

    openModal(event) {
        event.preventDefault();
        this.modal.show();
    }

    #setImage(image) {
        this.hiddenInputTarget.value = image.id;
        this.previewTarget.src = image.filteredUrl;
        this.previewTarget.hidden = false;
        this.placeholderTarget.hidden = true;
    }
}
