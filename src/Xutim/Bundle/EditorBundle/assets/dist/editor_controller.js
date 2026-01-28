import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        draftId: String,
        moveUrl: String
    };

    static targets = ['blocks', 'picker'];

    connect() {
        this.pickerOpen = false;
    }

    openPicker(event) {
        this.pickerOpen = true;
        this.element.classList.add('content-editor--picker-open');
    }

    closePicker() {
        this.pickerOpen = false;
        this.element.classList.remove('content-editor--picker-open');

        const pickerFrame = this.element.querySelector('#block-picker');
        if (pickerFrame) pickerFrame.innerHTML = '';
    }

    onSortEnd(event) {
        const { item, oldIndex, newIndex } = event.detail;

        if (oldIndex === newIndex) return;

        const blockId = item.querySelector('[data-editor-block-id-value]')?.dataset.editorBlockIdValue;
        if (!blockId) return;

        const url = this.moveUrlValue
            .replace('__ID__', blockId)
            .replace('__POSITION__', newIndex);

        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).catch(error => console.error('Error moving block:', error));
    }
}
