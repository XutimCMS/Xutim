import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        id: String,
        name: String,
    };

    start(event) {
        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('application/x-media-id', this.idValue);

        const dragPreview = document.createElement('div');
        dragPreview.textContent = this.nameValue;
        dragPreview.className =
            'd-inline-block px-2 py-1 bg-dark text-white rounded shadow-sm small';
        dragPreview.style.maxWidth = '240px';
        dragPreview.style.whiteSpace = 'nowrap';
        dragPreview.style.overflow = 'hidden';
        dragPreview.style.textOverflow = 'ellipsis';

        document.body.appendChild(dragPreview);

        event.dataTransfer.setDragImage(dragPreview, -10, -10);

        setTimeout(() => dragPreview.remove(), 0);

        this.element.classList.add('opacity-50');
    }

    end() {
        this.element.classList.remove('opacity-50');
    }
}
