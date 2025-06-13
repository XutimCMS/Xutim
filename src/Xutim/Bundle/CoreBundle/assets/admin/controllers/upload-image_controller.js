import { Controller } from '@hotwired/stimulus';
import axios from 'axios';

export default class extends Controller {
    static values = {
        url: String,
        token: String,
    };

    static targets = ['imageOutput'];

    uploadImage(event) {
        /** @type HTMLButtonElement */
        const button = event.target;
        /** @type HTMLElement */
        const cardElem = button.parentElement.parentElement;
        /** @type HTMLInputElement */
        const fileElem = cardElem.querySelector('input[type=file]');
        /** @type HTMLElement */
        const blockElem = cardElem.querySelector('.block');

        const file = fileElem.files?.[0];

        if (!file) {
            return;
        }
        this.#displayImages(file);

        const formData = new FormData();
        formData.append('file', file);
        formData.append('block', blockElem.dataset.block);

        axios
            .post(this.urlValue, formData, {
                headers: {
                    'X-CSRF-Token': this.tokenValue,
                    'Content-Type': 'multipart/form-data',
                },
            })
            .then((response) => {
                const spinners =
                    this.element.getElementsByClassName('spinner-grow');
                for (let i = 0; i < spinners.length; i++) {
                    spinners[i].remove();
                }
            })
            .catch((error) => {
                const spinners =
                    this.element.getElementsByClassName('spinner-grow');
                for (let i = 0; i < spinners.length; i++) {
                    spinners[i].classList.remove('spinner-grow', 'text-yellow');
                    spinners[i].classList.add('text-danger');
                    spinners[i].innerHTML =
                        '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-alert-triangle" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"> <path stroke="none" d="M0 0h24v24H0z" fill="none"></path> <path d="M10.24 3.957l-8.422 14.06a1.989 1.989 0 0 0 1.7 2.983h16.845a1.989 1.989 0 0 0 1.7 -2.983l-8.423 -14.06a1.989 1.989 0 0 0 -3.4 0z"></path> <path d="M12 9v4"></path> <path d="M12 17h.01"></path> </svg>';
                    // spinners[i].title = 'The image could not be uploaded. Please try again.';
                }
            });
    }

    deleteImage(event) {
        event.preventDefault();
        /** @type HTMLButtonElement */
        const button = event.target;
        const image = button.closest('.col');
        image?.remove();
    }

    /**
     * @param {File} image - The image file to be displayed.
     * @returns {void}
     */
    #displayImages(image) {
        const reader = new FileReader();
        reader.onload = (event) => {
            const imageOutput = this.imageOutputTarget;

            const deleteButton = document.createElement('button');
            deleteButton.classList.add(
                'btn',
                'btn-sm-light',
                'btn-sm',
                'image-box-delete',
                'pe-0',
            );
            deleteButton.dataset.action = 'click->upload-image#deleteImage';
            deleteButton.innerHTML =
                '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"> <path stroke="none" d="M0 0h24v24H0z" fill="none"></path> <path d="M18 6l-12 12"></path> <path d="M6 6l12 12"></path> </svg>';

            const spinnerSpan = document.createElement('span');
            spinnerSpan.classList.add('visually-hidden');
            spinnerSpan.innerText = 'Loading...';

            const spinner = document.createElement('div');
            spinner.classList.add(
                'spinner-grow',
                'position-absolute',
                'top-50',
                'start-50',
                'text-yellow',
                'translate-middle',
            );
            spinner.role = 'status';
            spinner.appendChild(spinnerSpan);

            const img = document.createElement('div');
            img.classList.add(
                'img-responsive',
                'img-responsive-1x1',
                'rounded',
                'border',
            );
            img.style.backgroundImage = `url(${event.target?.result})`;
            img.appendChild(deleteButton);
            img.appendChild(spinner);

            const div = document.createElement('div');
            div.classList.add('col');
            div.appendChild(img);

            imageOutput.appendChild(div);
        };
        reader.readAsDataURL(image);
    }
}
