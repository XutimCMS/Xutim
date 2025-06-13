import { Controller } from '@hotwired/stimulus';
import ImageGalleryModal from './../lib/ImageGalleryModal.js';

export default class extends Controller {
    static values = { url: String };
    static targets = ['imageInput', 'dropzone'];

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
        this.dropzoneTarget.innerHTML = `<img
            src="${url}"
            alt="Featured Image"
            style="max-width: auto; max-height: 300px;"
            class="p-1"
        />`;
    }
}
