import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        title: String,
        modalWidth: String,
        closeButtonLabel: String,
        submitButtonLabel: String,
        submitButtonColor: String,
        redirectUrl: { type: String, default: '' },
    };

    openModal() {
        document.getElementById('modal-controller')?.remove();

        fetch(this.urlValue + '?ajax=1')
            .then(response => response.text())
            .then(body => {
                const colorClasses = {
                    danger: 'bg-red-600 hover:bg-red-700',
                    warning: 'bg-amber-500 hover:bg-amber-600',
                    success: 'bg-green-600 hover:bg-green-700',
                    primary: 'bg-accent hover:bg-accent-hover dark:text-black',
                };
                const btnColor = colorClasses[this.submitButtonColorValue] || 'bg-accent hover:bg-accent-hover dark:text-black';

                const dialog = document.createElement('dialog');
                dialog.id = 'modal-controller';
                dialog.className = 'fixed inset-0 z-[60] m-auto w-full rounded-xl border border-border bg-surface shadow-xl backdrop:bg-black/50 p-0 ' + (this.modalWidthValue || 'max-w-lg');
                dialog.innerHTML = `
                    <div class="flex items-center justify-between border-b border-border px-5 py-3.5">
                        <h3 class="text-[15px] font-semibold">${this.titleValue}</h3>
                        <button type="button" data-action="close" class="rounded-md p-1 hover:bg-surface-raised transition-colors">
                            <svg class="h-4 w-4 text-content-tertiary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="modal-body p-5">${body}</div>
                    <div class="flex items-center justify-end gap-2 border-t border-border px-5 py-3">
                        <button type="button" data-action="submit" class="rounded-md px-3 py-1.5 text-[13px] font-medium text-white ${btnColor} transition-colors">${this.submitButtonLabelValue}</button>
                    </div>
                `;

                document.body.appendChild(dialog);
                this.#fixModal(dialog);
                dialog.showModal();

                dialog.querySelector('[data-action="close"]').addEventListener('click', () => dialog.close());
                dialog.addEventListener('close', () => dialog.remove());
            });
    }

    #fixModal(dialog) {
        const footerBtn = dialog.querySelector('[data-action="submit"]');
        const formButtons = dialog.querySelectorAll('.modal-body button[type=submit]');
        if (formButtons.length > 0) {
            footerBtn.innerText = formButtons.item(0).innerText;
            formButtons.item(0).remove();
        }

        dialog.querySelector('.modal-body').addEventListener('submit', (e) => {
            e.preventDefault();
            this.#submitForm(dialog);
        });

        footerBtn.addEventListener('click', () => {
            this.#submitForm(dialog);
        });
    }

    #submitForm(dialog) {
        const form = dialog.querySelector('form');
        if (!form) return;

        const formData = new FormData(form);

        fetch(`${this.urlValue}?ajax=1`, {
            method: 'POST',
            body: formData,
        })
            .then(response => {
                if (response.status === 200 && response.redirected) {
                    Turbo.visit(response.url);
                    return;
                }
                if (response.status === 302) {
                    const location = response.headers.get('location');
                    if (location) {
                        Turbo.visit(location);
                    }
                    return;
                }

                return response.text().then(html => {
                    dialog.querySelector('.modal-body').innerHTML = html;
                    this.#fixModal(dialog);
                    if (response.status !== 200) {
                        dialog.close();
                        window.location.reload();
                    }
                });
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
}
