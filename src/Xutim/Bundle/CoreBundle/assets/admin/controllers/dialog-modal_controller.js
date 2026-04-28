import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
    static values = {
        dialog: String,
        action: String,
        csrfToken: String,
        helpText: String,
        cancelButtonLabel: String,
        confirmButtonLabel: String,
        dialogColor: String,
        openOnInit: Boolean,
        bulkCount: Number,
        bulkLabel: String,
        confirmButtonBulkLabel: String,
    };
    connect() {
        if (this.openOnInitValue === true) {
            this.activate();
        }
    }

    activate() {
        const hasBulk = this.bulkCountValue > 1 && this.bulkLabelValue !== '';
        const bulkRow = hasBulk
            ? `
            <div class="text-start px-2 pb-2">
                <label class="form-check">
                    <input id="dialog-bulk-toggle" class="form-check-input" type="checkbox">
                    <span class="form-check-label">${this.bulkLabelValue}</span>
                </label>
            </div>
        `
            : '';

        let modalHTML = `
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-status bg-${this.dialogColorValue}"></div>
                    <div class="modal-body text-center py-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-${this.dialogColorValue} icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 9v2m0 4v.01"></path><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"></path></svg>
                        <h3>${this.dialogValue}</h3>
                        <div class="text-muted">${this.helpTextValue}</div>
                    </div>
                    ${bulkRow}
                    <div class="modal-footer">
                        <div class="w-100">
                            <div class="row">
                                <div class="col">
                                    <button role="button" type="button" class="btn w-100" data-bs-dismiss="modal">
                                        ${this.cancelButtonLabelValue}
                                    </button>
                                </div>
                                <div class="col">
                                    <form id="dialog-form" name="form" method="post" action="${this.actionValue}">
                                        <div id="form">
                                            <input id="form__token" name="form[_token]" value="${this.csrfTokenValue}" type="hidden">
                                            <input id="dialog-apply-to-all" name="apply_to_all" value="0" type="hidden">
                                        </div>
                                            <button id="dialog-form-submit" type="submit" form="dialog-form" class="btn btn-${this.dialogColorValue} w-100" data-bs-dismiss="modal">
                                                ${this.confirmButtonLabelValue}
                                            </button>
                                        </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const modalElem = document.createElement('div');
        modalElem.id = 'modal-dialog-item';
        modalElem.classList.add('modal');
        modalElem.classList.add('fade');
        modalElem.tabIndex = -1;
        modalElem.role = 'dialog';
        modalElem.innerHTML = modalHTML;

        // Close any open <dialog> elements (top-layer blocks everything)
        const openDialog = document.querySelector('dialog[open]');
        if (openDialog) {
            openDialog.close();
        }

        document.body.appendChild(modalElem);

        if (hasBulk) {
            const toggle = modalElem.querySelector('#dialog-bulk-toggle');
            const hidden = modalElem.querySelector('#dialog-apply-to-all');
            const submit = modalElem.querySelector('#dialog-form-submit');
            const singleLabel = this.confirmButtonLabelValue;
            const bulkLabel = this.confirmButtonBulkLabelValue || singleLabel;
            toggle.addEventListener('change', () => {
                hidden.value = toggle.checked ? '1' : '0';
                submit.textContent = toggle.checked ? bulkLabel : singleLabel;
            });
        }

        const modal = new Modal(modalElem);
        modal.show();

        // Remove the modal window completely after it disappears.
        // Reopen the <dialog> if the user cancelled.
        modalElem.addEventListener('hidden.bs.modal', function (event) {
            Modal.getInstance(event.currentTarget).hide();
            document.body.removeChild(event.currentTarget);

            if (openDialog) {
                openDialog.showModal();
            }
        });
    }
}
