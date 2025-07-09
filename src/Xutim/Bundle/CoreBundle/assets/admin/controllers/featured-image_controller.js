import { Controller } from '@hotwired/stimulus';
import ImageGalleryModal from './../lib/ImageGalleryModal.js';

export default class extends Controller {
    static values = { url: String };
    static targets = ['imageInput', 'removeButton', 'imageWrapper'];

    connect() {
        this.imageGalleryModal = new ImageGalleryModal({
            galleryUrl: this.urlValue,
            onSelect: this.selectImage.bind(this),
        });
    }

    openModal() {
        this.imageGalleryModal.show();
    }

    selectImage(image) {
        this.updateFeaturedImage(image.id, image.fullSourceUrl);
    }

    updateFeaturedImage(id, url) {
        this.imageInputTarget.value = id;
        this.imageWrapperTarget.innerHTML = `<img
            src="${url}"
            alt="Featured Image"
            style="max-width: auto; max-height: 300px;"
            class="p-1"
        />`;
        if (id) {
            this.removeButtonTarget.classList.remove('d-none');
        } else {
            this.removeButtonTarget.classList.add('d-none');
        }
    }

    removeImage(event) {
        event.stopPropagation(); // prevent triggering openModal
        this.imageInputTarget.value = '';
        this.imageWrapperTarget.innerHTML = `
            <span class="text-muted">
                ${this.element.dataset.defaultText || 'Click to select a featured image'}
            </span>
        `;
        this.removeButtonTarget.classList.add('d-none');
    }
}
