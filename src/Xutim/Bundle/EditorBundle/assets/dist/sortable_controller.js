import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

export default class extends Controller {
    static values = {
        handle: { type: String, default: '.sortable-handle' },
        animation: { type: Number, default: 150 }
    };

    connect() {
        this.sortable = Sortable.create(this.element, {
            animation: this.animationValue,
            handle: this.handleValue,
            draggable: '> turbo-frame',
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            onEnd: this.onEnd.bind(this)
        });
    }

    disconnect() {
        if (this.sortable) this.sortable.destroy();
    }

    onEnd(event) {
        this.element.dispatchEvent(new CustomEvent('sortable:end', {
            bubbles: true,
            detail: {
                item: event.item,
                oldIndex: event.oldIndex,
                newIndex: event.newIndex
            }
        }));
    }
}
