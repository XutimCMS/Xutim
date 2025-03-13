import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
    static targets = ['localeSelect'];

    initialize() {
        this.activate();
    }

    activate() {
        const modal = new Modal(this.element);
        modal.show();

        // Remove the modal window completely after it disappears.
        // this.element.addEventListener('hidden.bs.modal', function (event) {
        //     Modal.getInstance(event.currentTarget).hide();
        //     document.body.removeChild(event.currentTarget);
        // });
    }

    dismissModal() {
        const modal = Modal.getInstance(this.element);
        this.element.classList.remove('fade');
        modal._backdrop._config.isAnimated = false;
        modal.hide();
        modal.dispose();
    }

    getResponse() {
        const url =
            this.localeSelectTarget.options[
                this.localeSelectTarget.selectedIndex
            ].dataset.urlValue;
        window.location = url;
    }
}
