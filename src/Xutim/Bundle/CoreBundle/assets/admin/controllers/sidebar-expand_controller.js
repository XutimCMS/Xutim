// controllers/offcanvas_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.openedCanvas = null;
        this.updateSidebarPosition();
        const expandButton = document.querySelector('#sidebar-toggle');
        if (expandButton) {
            console.log('activated');
            expandButton.addEventListener('click', this.toggle.bind(this));
        }
    }
    disconnect() {
        const expandButton = document.querySelector('#sidebar-toggle');
        if (expandButton) {
            expandButton.removeEventListener('click', this.toggle.bind(this));
        }
    }

    externalToggle() {
        const event = this.dispatch('toggle', { cancelable: true });
        this.toggle(event);
    }

    toggle(event) {
        const targetId = event.currentTarget.dataset.target;
        const offcanvas = this.element;
        console.error('yes');

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

    updateSidebarPosition(event) {
        const scrollPosition = window.scrollY;

        const maxGap = 112;
        const newTop = Math.max(0, maxGap - scrollPosition);
        this.element.style.top = `${newTop}px`;
    }
}
