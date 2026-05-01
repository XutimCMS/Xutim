import { Controller } from '@hotwired/stimulus';

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
        const colorClasses = {
            danger: 'bg-red-600 hover:bg-red-700',
            warning: 'bg-amber-500 hover:bg-amber-600',
            success: 'bg-green-600 hover:bg-green-700',
        };
        const btnColor = colorClasses[this.dialogColorValue] || 'bg-accent hover:bg-accent-hover';

        const hasBulk = this.bulkCountValue > 1 && this.bulkLabelValue !== '';
        const bulkRow = hasBulk
            ? `
                <label class="mt-4 flex items-center gap-2 text-[13px] text-content-secondary">
                    <input id="dialog-bulk-toggle" type="checkbox" class="rounded border-border text-accent focus:ring-accent">
                    <span>${this.bulkLabelValue}</span>
                </label>
            `
            : '';

        const dialog = document.createElement('dialog');
        dialog.className = 'fixed inset-0 z-[60] m-auto max-w-sm rounded-xl border border-border bg-surface shadow-xl backdrop:bg-black/50 p-0';
        dialog.innerHTML = `
            <div class="p-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-50 dark:bg-red-500/10 mb-3">
                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                </div>
                <h3 class="text-[15px] font-semibold">${this.dialogValue}</h3>
                ${this.helpTextValue ? `<p class="mt-2 text-[13px] text-content-secondary leading-relaxed">${this.helpTextValue}</p>` : ''}
                ${bulkRow}
            </div>
            <div class="flex items-center justify-end gap-2 border-t border-border px-5 py-3">
                <button type="button" data-action="cancel" class="rounded-md border border-border px-3 py-1.5 text-[13px] font-medium hover:bg-surface-raised transition-colors">
                    ${this.cancelButtonLabelValue}
                </button>
                <form method="post" action="${this.actionValue}">
                    <input type="hidden" name="form[_token]" value="${this.csrfTokenValue}">
                    <input id="dialog-apply-to-all" type="hidden" name="apply_to_all" value="0">
                    <button id="dialog-form-submit" type="submit" class="rounded-md px-3 py-1.5 text-[13px] font-medium text-white ${btnColor} transition-colors">
                        ${this.confirmButtonLabelValue}
                    </button>
                </form>
            </div>
        `;

        // Close any open <dialog> elements
        const openDialog = document.querySelector('dialog[open]');
        if (openDialog) {
            openDialog.close();
        }

        document.body.appendChild(dialog);
        dialog.showModal();

        dialog.querySelector('[data-action="cancel"]').addEventListener('click', () => {
            dialog.close();
        });

        if (hasBulk) {
            const toggle = dialog.querySelector('#dialog-bulk-toggle');
            const hidden = dialog.querySelector('#dialog-apply-to-all');
            const submit = dialog.querySelector('#dialog-form-submit');
            const singleLabel = this.confirmButtonLabelValue;
            const bulkLabel = this.confirmButtonBulkLabelValue || singleLabel;
            toggle.addEventListener('change', () => {
                hidden.value = toggle.checked ? '1' : '0';
                submit.textContent = toggle.checked ? bulkLabel : singleLabel;
            });
        }

        dialog.addEventListener('close', () => {
            dialog.remove();
            if (openDialog) {
                openDialog.showModal();
            }
        });
    }
}
