import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['toggleBtn'];

    connect() {
        this.filterEnabled = false;
    }

    toggle() {
        this.filterEnabled = !this.filterEnabled;

        const unchangedBlocks =
            this.element.querySelectorAll('.block-unchanged');

        unchangedBlocks.forEach((block) => {
            if (this.filterEnabled) {
                block.classList.add('d-none');
            } else {
                block.classList.remove('d-none');
            }
        });

        if (this.hasToggleBtnTarget) {
            if (this.filterEnabled) {
                this.toggleBtnTarget.classList.remove('btn-outline-primary');
                this.toggleBtnTarget.classList.add('btn-primary');
            } else {
                this.toggleBtnTarget.classList.remove('btn-primary');
                this.toggleBtnTarget.classList.add('btn-outline-primary');
            }
        }
    }
}
