import { Controller } from '@hotwired/stimulus';
import tippy from 'tippy.js';

export default class extends Controller {
    connect() {
        const content = this.element.getAttribute('data-bs-content') || this.element.getAttribute('data-content') || '';
        const title = this.element.getAttribute('data-bs-title') || this.element.getAttribute('title') || '';
        this._tippy = tippy(this.element, {
            content: title ? `<strong>${title}</strong><br>${content}` : content,
            allowHTML: true,
            interactive: true,
            trigger: 'click',
        });
        this.element.removeAttribute('title');
    }

    disconnect() {
        this._tippy?.destroy();
    }
}
