import './styles.css';

export default class XutimFootnoteInline {
    static get isInline() {
        return true;
    }

    static get sanitize() {
        return {
            sup: {
                class: true,
                'data-footnote': true,
            },
            svg: {
                class: true,
            },
        };
    }

    #api;
    #button;

    constructor({ api }) {
        this.#api = api;
        this.#button = null;

        document.addEventListener('DOMContentLoaded', () =>
            this.#attachClickListeners(),
        );
        setTimeout(() => this.#attachClickListeners(), 500);
    }

    render() {
        this.#button = document.createElement('button');
        this.#button.type = 'button';
        this.#button.innerHTML =
            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-note"><path d="M13 20l7 -7" /><path d="M13 20v-6a1 1 0 0 1 1 -1h6v-7a2 2 0 0 0 -2 -2h-12a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7" /></svg>';
        this.#button.classList.add('ce-inline-tool');
        return this.#button;
    }

    surround(range) {
        if (!range || range.collapsed) return;

        const sup = document.createElement('sup');
        sup.dataset.footnote = '';
        sup.textContent = '*';

        range.collapse(false);
        range.insertNode(sup);

        sup.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.#editFootnote(sup);
        });

        sup.dataset._bound = 'true';
        setTimeout(() => this.#editFootnote(sup), 0);
    }

    checkState(selection) {
        const sup = this.#api.selection.findParentTag('sup');
        this.#button.classList.toggle(
            'cdx-inline-tool--active',
            !!sup?.dataset?.footnote,
        );
    }

    #editFootnote(sup) {
        this.#createDialog();

        const dialog = document.querySelector('#xutim-footnote-dialog');
        dialog._xutimFootnoteSup = sup; // attach as _-prefixed DOM property

        const textarea = dialog.querySelector('textarea');
        textarea.value = sup.dataset.footnote || '';

        dialog.showModal();
        setTimeout(() => textarea.focus(), 0);
    }

    #attachClickListeners() {
        const footnotes = document.querySelectorAll('sup[data-footnote]');
        footnotes.forEach((sup) => {
            if (!sup.dataset._bound) {
                sup.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.#editFootnote(sup);
                });
                sup.dataset._bound = 'true';
            }
        });
    }

    #createDialog() {
        if (document.querySelector('#xutim-footnote-dialog')) return;

        const dialog = document.createElement('dialog');
        dialog.id = 'xutim-footnote-dialog';
        dialog.innerHTML = `
            <form method="dialog" class="xutim-footnote-form">
                <label>
                    Footnote:
                    <textarea name="footnote" rows="4" placeholder="Write your footnote..."></textarea>
                </label>
                <menu>
                    <button value="cancel">Cancel</button>
                    <button value="delete" style="color:red" type="button" id="xutim-footnote-delete-btn">
                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg> Remove
                    </button>
                    <button value="default">Save</button>
                </menu>
            </form>
        `;
        document.body.appendChild(dialog);

        dialog._xutimFootnoteSup = null;

        let mouseDownInside = false;

        dialog.addEventListener('mousedown', (e) => {
            mouseDownInside =
                dialog.contains(e.target) && e.target.tagName !== 'DIALOG';
        });

        dialog.addEventListener('click', (e) => {
            if (!mouseDownInside && e.target === dialog) {
                dialog.close();
            }
        });

        dialog.addEventListener('close', () => {
            const value = dialog.returnValue;
            const textarea = dialog.querySelector('textarea');
            const text = textarea.value.trim();

            if (value === 'default' && dialog._xutimFootnoteSup) {
                dialog._xutimFootnoteSup.dataset.footnote = text;
            }

            dialog._xutimFootnoteSup = null;
        });
        const deleteBtn = dialog.querySelector('#xutim-footnote-delete-btn');
        deleteBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const sup = dialog._xutimFootnoteSup;
            if (sup) {
                sup.remove();
            }
            dialog._xutimFootnoteSup = null;
            dialog.close();
        });
    }
}
