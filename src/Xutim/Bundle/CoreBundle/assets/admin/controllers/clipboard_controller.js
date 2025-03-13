import { Controller } from '@hotwired/stimulus';
import Clipboard from 'clipboard';
import { Popover } from 'bootstrap';

export default class extends Controller {
    connect() {
        const clipboard = new Clipboard(this.element);
        const popover = new Popover(this.element, {
            delay: {
                hide: 800,
            },
        });

        clipboard.on('success', function () {
            popover.show();
        });
    }
}
