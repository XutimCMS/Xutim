import { Controller } from '@hotwired/stimulus';
import { computePosition, flip, shift, offset } from '@floating-ui/dom';

export default class extends Controller {
    static values = {
        blockId: String,
        blockType: String,
        convertUrl: String,
        deleteUrl: String,
    };

    static targets = ['menu', 'trigger', 'turnIntoSubmenu'];

    connect() {
        this.isOpen = false;
        this.closeOnClickOutside = this.closeOnClickOutside.bind(this);
    }

    disconnect() {
        document.removeEventListener('click', this.closeOnClickOutside);
    }

    toggle(event) {
        event.preventDefault();
        event.stopPropagation();

        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    async open() {
        if (this.isOpen) return;
        this.isOpen = true;

        this.menuTarget.classList.remove('d-none');
        this.triggerTarget.classList.add('is-active');

        await this.updatePosition();

        setTimeout(() => {
            document.addEventListener('click', this.closeOnClickOutside);
        }, 0);
    }

    async updatePosition() {
        const { x, y } = await computePosition(this.triggerTarget, this.menuTarget, {
            placement: 'right-start',
            middleware: [
                offset(4),
                flip({ fallbackPlacements: ['right-end', 'left-start', 'left-end', 'bottom-start'] }),
                shift({ padding: 8 }),
            ],
        });

        Object.assign(this.menuTarget.style, {
            left: `${x}px`,
            top: `${y}px`,
        });
    }

    close() {
        if (!this.isOpen) return;
        this.isOpen = false;

        this.menuTarget.classList.add('d-none');
        this.triggerTarget.classList.remove('is-active');
        this.closeTurnIntoSubmenu();
        document.removeEventListener('click', this.closeOnClickOutside);
    }

    closeOnClickOutside(event) {
        if (!this.element.contains(event.target)) {
            this.close();
        }
    }

    showTurnIntoSubmenu(event) {
        event.preventDefault();
        event.stopPropagation();

        if (this.hasTurnIntoSubmenuTarget) {
            this.turnIntoSubmenuTarget.classList.remove('d-none');
            this.positionSubmenu();
        }
    }

    closeTurnIntoSubmenu() {
        if (this.hasTurnIntoSubmenuTarget) {
            this.turnIntoSubmenuTarget.classList.add('d-none');
        }
    }

    async positionSubmenu() {
        if (!this.hasTurnIntoSubmenuTarget) return;

        const parentItem = this.turnIntoSubmenuTarget.closest('.block-menu__item');
        if (!parentItem) return;

        const { x, y } = await computePosition(parentItem, this.turnIntoSubmenuTarget, {
            placement: 'right-start',
            middleware: [
                offset(0),
                flip({ fallbackPlacements: ['left-start'] }),
                shift({ padding: 8 }),
            ],
        });

        Object.assign(this.turnIntoSubmenuTarget.style, {
            left: `${x}px`,
            top: `${y}px`,
        });
    }

    async convertTo(event) {
        event.preventDefault();
        event.stopPropagation();

        const newType = event.currentTarget.dataset.blockType;
        if (!newType || newType === this.blockTypeValue) {
            this.close();
            return;
        }

        this.close();

        try {
            const response = await fetch(this.convertUrlValue, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/vnd.turbo-stream.html',
                },
                body: JSON.stringify({ type: newType }),
            });

            if (response.ok) {
                const html = await response.text();
                Turbo.renderStreamMessage(html);
            } else {
                console.error('Failed to convert block');
            }
        } catch (error) {
            console.error('Error converting block:', error);
        }
    }

    async delete(event) {
        event.preventDefault();
        event.stopPropagation();

        this.close();

        try {
            const response = await fetch(this.deleteUrlValue, {
                method: 'DELETE',
                headers: {
                    'Accept': 'text/vnd.turbo-stream.html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                const html = await response.text();
                if (html) Turbo.renderStreamMessage(html);
            } else {
                console.error('Failed to delete block');
            }
        } catch (error) {
            console.error('Error deleting block:', error);
        }
    }
}
