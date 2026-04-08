import { Controller } from '@hotwired/stimulus';
import Clipboard from 'clipboard';
import tippy from 'tippy.js';

export default class extends Controller {
    connect() {
        const clipboard = new Clipboard(this.element);
        const tip = tippy(this.element, {
            content: 'Copied!',
            trigger: 'manual',
        });

        clipboard.on('success', () => {
            tip.show();
            setTimeout(() => tip.hide(), 1200);
        });
    }
}
