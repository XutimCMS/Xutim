import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    reloadFrame(event) {
        const url =
            event.currentTarget.options[event.currentTarget.selectedIndex]
                .dataset.urlValue;
        const frame = this.element.parentNode;
        frame.src = url;
        frame.reload();
    }
}
