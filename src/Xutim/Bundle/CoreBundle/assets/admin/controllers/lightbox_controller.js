// controllers/lightbox_controller.js
import { Controller } from '@hotwired/stimulus';
import GLightbox from 'glightbox';
import 'glightbox/dist/css/glightbox.css';

export default class extends Controller {
    static values = { url: String };

    open(event) {
        event.preventDefault();
        this.element.blur();

        GLightbox({
            elements: [
                {
                    href: this.urlValue,
                    type: 'image',
                },
            ],
        }).open();
    }
}
