// controllers/offcanvas_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.widthValue = this.widthValue || 300;
        this.openedCanvas = null;
        this.#setupSidebar();
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
    #setupSidebar() {
        this.element.style.position = 'fixed';
        this.element.style.top = '0';
        this.element.style.right = `-${this.widthValue}px`; // Initially hidden
        this.element.style.width = `${this.widthValue}px`;
        this.element.style.height = '100vh';
        this.element.style.transition = 'right 0.3s ease-in-out';
        this.element.style.overflowY = 'auto';
        this.element.style.overflowX = 'hidden';

        const content = document.getElementById('main-content');
        content.style.transition = 'margin-right 0.3s ease-in-out';

        if (!window.matchMedia('(max-width: 991.98px)').matches) {
            this.#showSidebar();
        }
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
        this.#hideSidebar();
    }

    toggle(event) {
        const isOpen = this.element.style.right === '0px';

        if (isOpen) {
            this.#hideSidebar();
        } else {
            this.#showSidebar();
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

    #hideSidebar() {
        const content = document.getElementById('main-content');

        this.element.style.right = `-${this.widthValue}px`;
        content.style.marginRight = '0';
    }

    #showSidebar() {
        const content = document.getElementById('main-content');

        this.element.style.right = '0px';
        content.style.marginRight = `${this.widthValue}px`;
    }
}
