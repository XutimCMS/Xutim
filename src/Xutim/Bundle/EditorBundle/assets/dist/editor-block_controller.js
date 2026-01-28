import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        id: String,
        type: String,
        deleteUrl: String,
    };

    connect() {
        this.handleKeydown = this.handleKeydown.bind(this);
        this.element.setAttribute('tabindex', '0');
    }

    focus() {
        this.element.focus();
        this.element.addEventListener('keydown', this.handleKeydown);
    }

    blur() {
        this.element.removeEventListener('keydown', this.handleKeydown);
    }

    handleKeydown(event) {
        switch (event.key) {
            case 'ArrowUp':
                event.preventDefault();
                this.focusPreviousBlock();
                break;
            case 'ArrowDown':
                event.preventDefault();
                this.focusNextBlock();
                break;
            case 'Escape':
                event.preventDefault();
                this.element.blur();
                break;
            case 'Backspace':
            case 'Delete':
                event.preventDefault();
                this.deleteAndFocusPrevious();
                break;
        }
    }

    select(event) {
        if (event.target.closest('button, a, input, textarea, select')) return;
        this.focus();
    }

    edit(event) {
        if (event.target.closest('button, a, input, textarea, select')) return;

        const editLink = this.element.querySelector('a[data-turbo-frame^="block-"]');
        if (editLink) editLink.click();
    }

    delete(event) {
        event.preventDefault();

        const deleteUrl = event.currentTarget.dataset.editorBlockDeleteUrlValue || this.deleteUrlValue;
        if (!deleteUrl) return;

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'Accept': 'text/vnd.turbo-stream.html',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) return response.text();
            throw new Error('Failed to delete block');
        })
        .then(html => {
            if (html) Turbo.renderStreamMessage(html);
        })
        .catch(error => console.error('Error deleting block:', error));
    }

    deleteAndFocusPrevious() {
        const deleteUrl = this.deleteUrlValue;
        if (!deleteUrl) return;

        const currentFrame = this.element.closest('turbo-frame');
        const prevFrame = currentFrame?.previousElementSibling;

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'Accept': 'text/vnd.turbo-stream.html',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) return response.text();
            throw new Error('Failed to delete block');
        })
        .then(html => {
            if (html) Turbo.renderStreamMessage(html);
            if (prevFrame?.tagName === 'TURBO-FRAME') {
                this.focusBlockInFrame(prevFrame);
            }
        })
        .catch(error => console.error('Error deleting block:', error));
    }

    focusPreviousBlock() {
        const currentFrame = this.element.closest('turbo-frame');
        const prevFrame = currentFrame?.previousElementSibling;

        if (prevFrame?.tagName === 'TURBO-FRAME') {
            this.focusBlockInFrame(prevFrame);
        }
    }

    focusNextBlock() {
        const currentFrame = this.element.closest('turbo-frame');
        const nextFrame = currentFrame?.nextElementSibling;

        if (nextFrame?.tagName === 'TURBO-FRAME') {
            this.focusBlockInFrame(nextFrame);
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
                    tiptapController.editor?.commands.focus('end');
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
            blockController?.focus();
        }
    }
}
