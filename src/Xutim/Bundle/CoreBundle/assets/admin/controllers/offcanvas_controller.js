// controllers/offcanvas_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['offcanvas'];

    connect() {
        this.openedCanvas = null;
    }

    toggle(event) {
        const targetId = event.currentTarget.dataset.target;
        const offcanvas = document.querySelector(targetId);

        if (this.openedCanvas && this.openedCanvas !== offcanvas) {
            this.openedCanvas.classList.remove('show');
            this.openedCanvas.setAttribute('aria-hidden', 'true');
            this.openedCanvas = null;
        }

        if (offcanvas.classList.contains('show')) {
            offcanvas.classList.remove('show');
            offcanvas.setAttribute('aria-hidden', 'true');
        } else {
            offcanvas.classList.add('show');
            offcanvas.setAttribute('aria-hidden', 'false');
            this.openedCanvas = offcanvas;
        }
    }
}
