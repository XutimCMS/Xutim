import { Controller } from '@hotwired/stimulus';
import Masonry from 'masonry-layout';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        const msnry = new Masonry(this.element, {
            percentPosition: true,
            itemSelector: '.grid-item',
        });
    }
}
