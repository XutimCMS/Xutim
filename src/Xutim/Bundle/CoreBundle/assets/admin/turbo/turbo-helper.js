import { Modal, Popover } from 'bootstrap';

const TurboHelper = class {
    constructor() {
        document.addEventListener('turbo:before-cache', (event) => {
            if (document.body.classList.contains('modal-open')) {
                const modalEl = document.querySelector('.modal');
                if (
                    modalEl.datalist.turboAllowCache === 'undefined' ||
                    modalEl.datalist.turboAllowCache !== 'true'
                ) {
                    const modal = Modal.getInstance(modalEl);
                    modalEl.classList.remove('fade');
                    modal._backdrop._config.isAnimated = false;
                    modal.hide();
                    modal.dispose();
                }
            }

            const popoverElem = document.querySelector('.popover');
            if (popoverElem) {
                const popover = Popover.getInstance(popoverElem);
                popover.hide();
            }

            const tomSelectElems = document.querySelectorAll(
                'select[data-controller="tom-select"]',
            );
            Array.from(tomSelectElems).forEach((element) => {
                const tomSelect = element.tomselect;
                if (tomSelect !== undefined) {
                    tomSelect.destroy();
                }
            });
        });
    }
};

export default new TurboHelper();
