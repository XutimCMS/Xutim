import { Controller } from '@hotwired/stimulus';
import { computePosition, flip, shift, offset } from '@floating-ui/dom';

export default class extends Controller {
    static values = {
        createUrl: String,
        afterBlockId: String,
    };

    static targets = ['menu', 'trigger'];

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
        const isInline = this.hasAfterBlockIdValue;
        const placement = isInline ? 'right-start' : 'top';
        const fallbacks = isInline ? ['right-end', 'left-start', 'left-end'] : ['bottom', 'top'];

        const { x, y } = await computePosition(this.triggerTarget, this.menuTarget, {
            placement,
            middleware: [
                offset(8),
                flip({ fallbackPlacements: fallbacks }),
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
        document.removeEventListener('click', this.closeOnClickOutside);
    }

    closeOnClickOutside(event) {
        if (!this.element.contains(event.target)) {
            this.close();
        }
    }

    async addBlock(event) {
        event.preventDefault();
        event.stopPropagation();

        const type = event.currentTarget.dataset.blockType;

        this.close();

        const body = { type };
        if (this.hasAfterBlockIdValue) {
            body.afterBlockId = this.afterBlockIdValue;
        }

        try {
            const response = await fetch(this.createUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/vnd.turbo-stream.html',
                },
                body: JSON.stringify(body),
            });

            if (response.ok) {
                const html = await response.text();
                Turbo.renderStreamMessage(html);

                requestAnimationFrame(() => {
                    setTimeout(() => {
                        let newFrame;
                        if (this.hasAfterBlockIdValue) {
                            const currentBlock = document.getElementById(`block-${this.afterBlockIdValue}`);
                            newFrame = currentBlock?.nextElementSibling;
                        } else {
                            newFrame = this.element.previousElementSibling;
                        }

                        if (newFrame && newFrame.tagName === 'TURBO-FRAME') {
                            this.focusBlockInFrame(newFrame);
                        }
                    }, 50);
                });
            } else {
                const errorText = await response.text();
                console.error('Request failed:', response.status, errorText);
            }
        } catch (error) {
            console.error('Error adding block:', error);
        }
    }

    focusBlockInFrame(frame) {
        const tiptapElement = frame.querySelector('[data-controller*="tiptap-block"]');
        if (tiptapElement) {
            const tiptapController = this.application.getControllerForElementAndIdentifier(
                tiptapElement,
                'tiptap-block'
            );
            if (tiptapController) {
                tiptapController.startEdit();
                requestAnimationFrame(() => {
                    tiptapController.editor?.commands.focus('start');
                });
                return;
            }
        }

        const blockElement = frame.querySelector('[data-controller*="editor-block"]');
        if (blockElement) {
            const blockController = this.application.getControllerForElementAndIdentifier(
                blockElement,
                'editor-block'
            );
            if (blockController) {
                blockController.focus();
            }
        }
    }
}
