import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';
import axios from 'axios';

export default class extends Controller {
    static values = {
        url: String,
        title: String,
        modalWidth: String,
        closeButtonLabel: String,
        submitButtonLabel: String,
        submitButtonColor: String,
        // In case the request should be redirected after a successfull
        // response. If it is not set, nothing will be redirected.
        redirectUrl: null | String,
    };

    openModal(event) {
        document.getElementById('modal-controller')?.remove();
        axios.get(this.urlValue, { params: { ajax: 1 } }).then((response) => {
            document.body.insertAdjacentHTML(
                'beforeend',
                this.renderTemplate(response.data),
            );
            const modalElem = document.getElementById('modal-controller');
            this.fixModal(modalElem);
            const modalInstance = new Modal(modalElem);
            modalInstance.show();
        });
    }

    renderTemplate(body) {
        return `
            <div class="modal fade" tabindex="-1" id="modal-controller">
                <div class="modal-dialog modal-dialog-centered ${this.modalWidthValue}">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${this.titleValue}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">${body}</div>
                        <div class="modal-footer">
                            <button class="btn btn-${this.submitButtonColorValue}" type="button" role="button">${this.submitButtonLabelValue}</button>
                        </div>
                    </div>
                </div>
            </div>`;
    }

    fixModal(modalElem) {
        const footerBtn = modalElem.querySelector('.modal-footer button.btn');
        const buttons = modalElem.querySelectorAll('button[type=submit]');
        if (buttons.length > 0) {
            footerBtn.innerText = buttons.item(0).innerText;
            buttons.item(0).remove();
        }

        // When hitting enter form isn't going to be submitted without adding the event listener.
        modalElem
            .querySelector('.modal-body')
            .addEventListener('submit', () => {
                this.submitForm(modalElem);
            });

        footerBtn.addEventListener('click', () => {
            this.submitForm(modalElem);
        });
    }

    submitForm(modalElem) {
        event.preventDefault();
        const form = modalElem.querySelector('form');
        const formData = new FormData(form);
        const params = new URLSearchParams({ ajax: 1 });

        fetch(`${this.urlValue}?${params.toString()}`, {
            method: 'POST',
            body: formData,
        })
            .then((response) => {
                if (response.status === 200 && response.redirected) {
                    Turbo.visit(response.url);
                }
                if (response.status === 302) {
                    const location = response.headers.get('location');
                    if (location) {
                        Turbo.visit(response.headers['location']);
                    }
                }

                const modalBody =
                    modalElem.getElementsByClassName('modal-body')[0];
                const responseData = response.text();

                modalBody.innerHTML = responseData;
                this.fixModal(modalElem);
                if (response.status !== 200) {
                    const modalInstance = Modal.getInstance(modalElem);
                    modalInstance.hide();
                    window.location.reload();
                }
            })
            .catch((error) => {
                console.error('Error:', error);
            });
    }

    // submitForm(modalElem) {
    //     event.preventDefault();
    //     const form = modalElem.querySelector('form');
    //     axios
    //         .postForm(this.urlValue, new FormData(form), {
    //             params: { ajax: 1 },
    //         })
    //         .then((response) => {
    //             const modalBody =
    //                 modalElem.getElementsByClassName('modal-body')[0];
    //             modalBody.innerHTML = response.data;
    //             this.fixModal(modalElem);
    //             console.error(response.status);
    //             if (response.status === 302) {
    //                 window.location.href = response.headers['location'];
    //             }
    //             if (response.status !== 200) {
    //                 const modalInstance = Modal.getInstance(modalElem);
    //                 modalInstance.hide();
    //                 window.location.reload();
    //             }
    //         });
    // }
}
