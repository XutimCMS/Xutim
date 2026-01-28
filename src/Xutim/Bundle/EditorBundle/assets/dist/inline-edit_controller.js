import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        blockId: String,
        saveUrl: String,
        field: { type: String, default: 'html' }
    };

    static targets = ['content'];

    connect() {
        this.saveTimeout = null;
        this.lastSavedContent = this.contentTarget.innerHTML;
    }

    focus() {
        this.element.classList.add('editor-block--editing');
    }

    blur() {
        this.element.classList.remove('editor-block--editing');
        this.save();
    }

    input() {
        // Debounced auto-save
        clearTimeout(this.saveTimeout);
        this.saveTimeout = setTimeout(() => this.save(), 1000);
    }

    keydown(event) {
        // Ctrl+S or Cmd+S = save immediately
        if ((event.ctrlKey || event.metaKey) && event.key === 's') {
            event.preventDefault();
            this.save();
            return;
        }

        // Enter = create new block (unless Shift+Enter for line break)
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            this.save().then(() => {
                this.dispatch('newblock', { detail: { afterId: this.blockIdValue } });
            });
            return;
        }

        // Backspace at start = merge with previous
        if (event.key === 'Backspace') {
            const selection = window.getSelection();
            if (selection.isCollapsed && selection.anchorOffset === 0) {
                const textBefore = this.getTextBeforeCursor();
                if (textBefore === '') {
                    event.preventDefault();
                    this.dispatch('mergeblock', { detail: { blockId: this.blockIdValue } });
                }
            }
        }
    }

    getTextBeforeCursor() {
        const selection = window.getSelection();
        if (!selection.rangeCount) return '';

        const range = selection.getRangeAt(0).cloneRange();
        range.selectNodeContents(this.contentTarget);
        range.setEnd(selection.anchorNode, selection.anchorOffset);
        return range.toString();
    }

    async save() {
        const content = this.contentTarget.innerHTML;

        if (content === this.lastSavedContent) {
            return;
        }

        this.element.classList.add('editor-block--saving');

        try {
            const response = await fetch(this.saveUrlValue, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    [this.fieldValue]: content
                })
            });

            if (response.ok) {
                this.lastSavedContent = content;
                this.element.classList.add('editor-block--saved');
                setTimeout(() => {
                    this.element.classList.remove('editor-block--saved');
                }, 1000);
            }
        } catch (error) {
            console.error('Failed to save block:', error);
            this.element.classList.add('editor-block--error');
        } finally {
            this.element.classList.remove('editor-block--saving');
        }
    }
}
