import './styles.css';

export default function createContentLink(title, icon) {
    return class XutimInternalLinkInlineTool {
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
            this.selectedId = null;
            this.wrapper = null;
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

            // Move cursor after inserted span
            const selection = window.getSelection();
            selection.removeAllRanges();
            const newRange = document.createRange();
            newRange.selectNodeContents(span);
            newRange.collapse(false);
            selection.addRange(newRange);
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

        renderActions() {
            this.wrapper = document.createElement('div');
            this.wrapper.style.display = 'block';
            this.wrapper.style.width = 'auto';
            this.wrapper.style.maxWidth = 'none';

            const select = document.createElement('select');
            select.classList.add('cdx-internal-link-select');

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = this.config.title || 'Select a page';
            select.appendChild(defaultOption);

            if (this.selectedId) {
                select.value = this.selectedId;
            }

            fetch(this.config.listUrl)
                .then((res) => res.json())
                .then((data) => {
                    const entries = Object.entries(data);
                    entries.sort((a, b) => a[1].localeCompare(b[1]));

                    entries.forEach(([id, label]) => {
                        const option = document.createElement('option');
                        option.value = id;
                        option.textContent = label;
                        select.appendChild(option);
                    });

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

                    if (this.selectedId) {
                        select.value = this.selectedId;
                    }
                });

            select.addEventListener('change', () => {
                const selectedId = select.value;
                if (!selectedId) return;
                const selection = window.getSelection();
                if (!selection || selection.rangeCount === 0) return;
                const range = selection.getRangeAt(0);
                this.applyInternalLink(range, selectedId);
            });

            this.wrapper.appendChild(select);
            return this.wrapper;
        }
    };
}

export function decorateInternalLinks(container) {
    const spans = container.querySelectorAll('span[data-internal-link-id]');
    spans.forEach((span) => {
        span.style.color = '#0d6efd';
        span.style.textDecoration = 'underline';
        span.classList.add('cdx-internal-link');
    });
}
