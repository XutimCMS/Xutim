// controllers/offcanvas_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.openedCanvas = null;
        this.updateSidebarPosition();

        const expandButton = document.querySelector('#sidebar-toggle');
        if (expandButton) {
            expandButton.addEventListener('click', this.toggle.bind(this));
        }
        window.addEventListener(
            'scroll',
            this.updateSidebarPosition.bind(this),
        );
        window.addEventListener(
            'resize',
            this.updateSidebarPosition.bind(this),
        );
    }
    disconnect() {
        const expandButton = document.querySelector('#sidebar-toggle');
        if (expandButton) {
            expandButton.removeEventListener('click', this.toggle.bind(this));
        }
        window.removeEventListener(
            'scroll',
            this.updateSidebarPosition.bind(this),
        );
        window.removeEventListener(
            'resize',
            this.updateSidebarPosition.bind(this),
        );
    }

    externalToggle() {
        const event = this.dispatch('toggle', { cancelable: true });
        this.toggle(event);
    }

    close(event) {
        const offcanvas = this.element;
        if (this.openedCanvas && this.openedCanvas !== offcanvas) {
            this.openedCanvas.classList.remove('show');
            this.openedCanvas.setAttribute('aria-hidden', 'true');
            this.openedCanvas = null;
        }

        if (offcanvas.classList.contains('show')) {
            offcanvas.classList.remove('show');
            offcanvas.setAttribute('aria-hidden', 'true');
        }
    }

    toggle(event) {
        const offcanvas = this.element;

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

        const mainNavbar = document.querySelector('#navbar-main');
        const localNavbar = document.querySelector('#navbar-local');

        let maxGap = 0;

        if (mainNavbar && mainNavbar.offsetHeight > 0) {
            maxGap += mainNavbar.offsetHeight;
        }
        if (localNavbar && localNavbar.offsetHeight > 0) {
            maxGap += localNavbar.offsetHeight;
        }

        if (maxGap === 0) {
            maxGap = 112;
        }

        const newTop = Math.max(0, maxGap - scrollPosition);
        this.element.style.top = `${newTop}px`;
    }
}
