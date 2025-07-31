import './styles.css';
import TomSelect from 'tom-select';

export default function createContentLink(title, icon) {
    return class XutimInternalLinkInlineTool {
        static optionsCache = {};

        static get isInline() {
            return true;
        }

        static get sanitize() {
            return {
                span: {
                    'data-internal-link-id': true,
                    'data-internal-link-type': true,
                },
            };
        }

        constructor({ api, config }) {
            this.api = api;
            this.config = config || {};
            this.wrapper = null;
            this.savedSelection = null;
        }

        /**
         * Create button for Inline Toolbar
         */
        render() {
            this.button = document.createElement('button');
            this.button.type = 'button';
            this.button.innerHTML = icon;
            this.button.classList.add(this.api.styles.inlineToolButton);
            return this.button;
        }

        /**
         * Render action bar UI
         */
        renderActions() {
            this.#saveCurrentSelection();
            this.wrapper = this.#createWrapper();
            const select = this.#createSelect();
            this.wrapper.appendChild(select);
            this.#addRemoveButton();

            const cacheKey = this.config.type;
            if (XutimInternalLinkInlineTool.optionsCache[cacheKey]) {
                this.#finalizeSelect(
                    select,
                    XutimInternalLinkInlineTool.optionsCache[cacheKey],
                );
            } else {
                this.#fetchOptionsList(cacheKey, select);
            }
            return this.wrapper;
        }
        #saveCurrentSelection() {
            this.savedSelection = null;
            const selection = window.getSelection();
            if (selection && selection.rangeCount > 0) {
                this.savedSelection = selection.getRangeAt(0).cloneRange();
            }
        }

        #createWrapper() {
            const wrapper = document.createElement('div');
            wrapper.style.display = 'block';
            wrapper.style.width = 'auto';
            wrapper.style.maxWidth = 'none';
            return wrapper;
        }

        #createSelect() {
            const select = document.createElement('select');
            select.classList.add('cdx-internal-link-select');
            select.disabled = true; // Until options load

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = this.config.title || 'Select a page';
            select.appendChild(defaultOption);

            return select;
        }

        #fetchOptionsList(cacheKey, select) {
            fetch(this.config.listUrl)
                .then((res) => res.json())
                .then((data) => {
                    XutimInternalLinkInlineTool.optionsCache[cacheKey] = data;
                    this.#finalizeSelect(select, data);
                });
        }

        #finalizeSelect(select, data) {
            this.#populateSelectOptions(select, data);
            this.#autosizeSelectWidth(select);

            select.disabled = false;

            if (!select.tomselect) {
                const ts = new TomSelect(select, {
                    dropdownParent: 'body',
                    maxOptions: null,
                    plugins: { remove_button: {} },
                });
                this.#syncSelectValue(ts);

                ts.on('change', this.#onTomSelectChange.bind(this));
            } else {
                this.#syncSelectValue(select.tomselect);
            }
        }

        #populateSelectOptions(select, data) {
            select.innerHTML = '';
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = this.config.title || 'Select a page';
            select.appendChild(defaultOption);

            const entries = Object.entries(data);
            entries.sort((a, b) => a[1].localeCompare(b[1]));
            for (const [id, label] of entries) {
                this.#appendOption(select, id, label);
            }
        }

        #appendOption(select, id, label) {
            const option = document.createElement('option');
            option.value = id;
            option.textContent = label;
            select.appendChild(option);
        }

        #autosizeSelectWidth(select) {
            const temp = document.createElement('span');
            temp.style.visibility = 'hidden';
            temp.style.position = 'absolute';
            temp.style.whiteSpace = 'nowrap';
            document.body.appendChild(temp);

            let maxWidth = 0;
            for (let i = 0; i < select.options.length; i++) {
                temp.textContent = select.options[i].text;
                maxWidth = Math.max(maxWidth, temp.offsetWidth);
            }
            document.body.removeChild(temp);
            select.style.width = `${maxWidth + 40}px`;
        }

        #syncSelectValue(ts) {
            this.#updateSelectedIdFromSelection();
            if (this.selectedId) {
                ts.setValue(this.selectedId, true);
            } else {
                ts.clear();
            }
        }

        #onTomSelectChange(selectedId) {
            if (!selectedId) return;
            this.#restoreSavedSelection();

            const selection = window.getSelection();
            if (!selection || selection.rangeCount === 0) return;
            const range = selection.getRangeAt(0);

            if (range.collapsed) return;
            this.applyInternalLink(range, selectedId);

            if (this.api.inlineToolbar) {
                this.api.inlineToolbar.close();
            }
        }

        #restoreSavedSelection() {
            if (this.savedSelection) {
                const selection = window.getSelection();
                selection.removeAllRanges();
                selection.addRange(this.savedSelection);
            }
        }

        #updateSelectedIdFromSelection() {
            const span = this.api.selection.findParentTag('SPAN');
            if (
                span?.dataset?.internalLinkId &&
                span?.dataset?.internalLinkType === this.config.type
            ) {
                this.selectedId = span.dataset.internalLinkId;
            } else {
                this.selectedId = null;
            }
        }

        #addRemoveButton() {
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.innerHTML =
                '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-trash"><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg> Remove link';
            removeBtn.style.marginTop = '2px';
            removeBtn.style.color = 'red';
            removeBtn.classList.add('btn', 'btn-sm');

            removeBtn.addEventListener('click', this.#onRemoveLink.bind(this));
            this.wrapper.appendChild(removeBtn);
        }

        #onRemoveLink() {
            const selection = window.getSelection();
            const span = this.api.selection.findParentTag('SPAN');
            if (
                span &&
                span.dataset.internalLinkId &&
                span.dataset.internalLinkType === this.config.type
            ) {
                const parent = span.parentNode;
                while (span.firstChild) {
                    parent.insertBefore(span.firstChild, span);
                }
                parent.removeChild(span);

                if (this.api.inlineToolbar) {
                    this.api.inlineToolbar.close();
                }
            }
        }

        applyInternalLink(range, id) {
            if (!id || range.collapsed) return;

            const existing = this.api.selection.findParentTag('SPAN');

            if (
                existing &&
                existing.dataset &&
                existing.dataset.internalLinkId &&
                existing.dataset.internalLinkType === this.config.type
            ) {
                existing.dataset.internalLinkId = id;
                existing.classList.add('cdx-internal-link');
                return;
            }

            const span = document.createElement('span');
            span.dataset.internalLinkId = id;
            span.dataset.internalLinkType = this.config.type;
            span.classList.add('cdx-internal-link');

            const contents = range.extractContents();
            span.appendChild(contents);
            range.insertNode(span);

            span.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                selectElement(span);
            });
            const parent = span.parentNode;
            const textNode = document.createTextNode('\u200B');

            if (span.nextSibling) {
                parent.insertBefore(textNode, span.nextSibling);
            } else {
                parent.appendChild(textNode);
            }

            const selection = window.getSelection();
            selection.removeAllRanges();
            range = document.createRange();
            range.setStart(textNode, 1);
            range.collapse(true);
            selection.addRange(range);
        }

        checkState() {
            const span = this.api.selection.findParentTag('SPAN');

            if (
                span?.dataset?.internalLinkId &&
                span?.dataset?.internalLinkType === this.config.type
            ) {
                this.selectedId = span.dataset.internalLinkId;

                this.button.classList.add(
                    this.api.styles.inlineToolButtonActive,
                );

                if (this.wrapper) {
                    const select = this.wrapper.querySelector('select');
                    if (select && select.value !== this.selectedId) {
                        select.value = this.selectedId;
                    }
                }
            } else {
                this.selectedId = null;
                this.button.classList.remove(
                    this.api.styles.inlineToolButtonActive,
                );

                if (this.wrapper) {
                    const select = this.wrapper.querySelector('select');
                    if (select && select.value !== '') {
                        select.value = '';
                    }
                }
            }
        }
    };
}

function selectElement(element) {
    const range = document.createRange();
    range.selectNodeContents(element);
    const selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(range);
}

export function decorateInternalLinks(container) {
    const spans = container.querySelectorAll('span[data-internal-link-id]');
    spans.forEach((span) => {
        span.style.color = '#0d6efd';
        span.style.textDecoration = 'underline';
        span.classList.add('cdx-internal-link');
        span.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            selectElement(span);
        });
    });
}
