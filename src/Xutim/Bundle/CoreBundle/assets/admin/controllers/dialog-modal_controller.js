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
    };
    connect() {
        if (this.openOnInitValue === true) {
            this.activate();
        }
    }

    activate() {
        let modalHTML = `
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-status bg-${this.dialogColorValue}"></div>
                    <div class="modal-body text-center py-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-${this.dialogColorValue} icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 9v2m0 4v.01"></path><path d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75"></path></svg>
                        <h3>${this.dialogValue}</h3>
                        <div class="text-muted">${this.helpTextValue}</div>
                    </div>
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

        // Append modal window to the body.
        document.body.appendChild(modalElem);

        // Activate and show modal window.
        const modal = new Modal(modalElem);
        modal.show();

        // Remove the modal window completely after it disappears.
        modalElem.addEventListener('hidden.bs.modal', function (event) {
            Modal.getInstance(event.currentTarget).hide();
            document.body.removeChild(event.currentTarget);
        });
    }
}
