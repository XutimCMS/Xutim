import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['search', 'grid'];

    connect() {
        if (this.hasSearchTarget) {
            this.searchTarget.focus();
        }

        document.addEventListener('keydown', this.handleKeyDown.bind(this));
    }

    disconnect() {
        document.removeEventListener('keydown', this.handleKeyDown.bind(this));
    }

    handleKeyDown(event) {
        if (event.key === 'Escape') {
            this.close();
        }
    }

    close() {
        const editor = this.element.closest('[data-controller="editor"]');
        if (editor) {
            const editorController = this.application.getControllerForElementAndIdentifier(
                editor,
                'editor'
            );
            if (editorController) {
                editorController.closePicker();
            }
        }

        const frame = this.element.closest('turbo-frame');
        if (frame) {
            frame.innerHTML = '';
        }
    }

    filter(event) {
        const query = event.target.value.toLowerCase().trim();

        this.gridTargets.forEach(grid => {
            const items = grid.querySelectorAll('.block-picker__item');
            let visibleCount = 0;

            items.forEach(item => {
                const type = item.dataset.blockType?.toLowerCase() || '';
                const label = item.dataset.blockLabel?.toLowerCase() || '';
                const description = item.querySelector('.block-picker__item-description')
                    ?.textContent?.toLowerCase() || '';

                const matches = query === ''
                    || type.includes(query)
                    || label.includes(query)
                    || description.includes(query);

                item.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            const category = grid.previousElementSibling;
            if (category?.classList.contains('block-picker__category-title')) {
                category.style.display = visibleCount > 0 ? '' : 'none';
            }
        });
    }
}
