/**
 * Editor.js tool for xutimLayout compound blocks.
 *
 * Each layout is a typed set of fields (text, image, page ref, …) stored
 * inline in editor.js content under `data.values`, per-locale. The tool
 * delegates form rendering to the Symfony admin via fetched HTML, so all
 * existing field widgets (image picker, entity pickers, etc.) work.
 *
 * The editor form opens in a native `<dialog>` so field changes only
 * propagate to the preview after explicit Save. Styling comes from the
 * admin's shared dialog classes (`dialog-lg`, `overflow-y-auto`).
 *
 * Config expected from caller:
 *   layouts:   [{code, name, fields: [{name, translatable, type}]}, ...]
 *   formUrl:   URL template with `:code:` placeholder returning form HTML
 *   saveUrl:   URL template with `:code:` placeholder accepting POST, returns JSON
 */
export default class XutimLayoutTool {
    static get toolbox() {
        return {
            title: 'Layout',
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" /><path d="M3 9h18" /><path d="M9 9v12" /></svg>',
        };
    }

    static get isReadOnlySupported() {
        return true;
    }

    constructor({ data, config, api, block, readOnly }) {
        this.data = data && typeof data === 'object' ? data : {};
        if (!this.data.values || typeof this.data.values !== 'object') {
            this.data.values = {};
        }
        this.config = config || {};
        this.api = api;
        this.block = block;
        this.readOnly = !!readOnly;

        this.layouts = Array.isArray(this.config.layouts)
            ? this.config.layouts
            : [];
        this.formUrl = this.config.formUrl || '';
        this.saveUrl = this.config.saveUrl || '';
        this.refreshUrl = this.config.refreshUrl || '';
        this.previewUrl = this.config.previewUrl || '';

        // Fixed logical width used by the preview iframe so desktop
        // breakpoints trigger even inside a narrow editor column. The iframe
        // is CSS-scaled down to fit the actual editor width. 900 keeps the
        // layout clearly past the smartphone threshold while minimising how
        // much the content has to shrink.
        this.PREVIEW_DESKTOP_WIDTH = 900;

        this.wrapper = null;
        this.expandContainer = null;
        this.expandDialog = null;
        this.expandBody = null;
        this.previewIframe = null;
        this.previewWrap = null;
        this.previewScale = 1;
        this.previewWrapResizeObserver = null;
        this.previewContentResizeObserver = null;
        this.layoutPickerWrapper = null;

        this.messageHandler = this.handlePreviewMessage.bind(this);
        window.addEventListener('message', this.messageHandler);
    }

    destroy() {
        window.removeEventListener('message', this.messageHandler);
        if (this.previewWrapResizeObserver) {
            this.previewWrapResizeObserver.disconnect();
            this.previewWrapResizeObserver = null;
        }
        if (this.previewContentResizeObserver) {
            this.previewContentResizeObserver.disconnect();
            this.previewContentResizeObserver = null;
        }
        this.closeLayoutPicker();
        this.closeEditor();
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'xutim-layout-block border rounded my-4';

        this.renderBody();

        return this.wrapper;
    }

    renderBody() {
        this.wrapper.innerHTML = '';

        if (!this.data.layoutCode) {
            this.renderPicker();
            return;
        }

        this.renderPlaceholder();
    }

    renderPicker() {
        const picker = document.createElement('div');
        picker.className = 'p-3 d-flex align-items-center gap-2';

        if (this.layouts.length === 0) {
            picker.textContent = 'No layouts registered';
            this.wrapper.appendChild(picker);
            return;
        }

        const hint = document.createElement('span');
        hint.className = 'text-muted small';
        hint.textContent = 'No layout picked yet.';
        picker.appendChild(hint);

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-primary';
        btn.textContent = 'Choose a layout';
        btn.addEventListener('click', (event) => {
            event.preventDefault();
            this.openLayoutPicker();
        });
        picker.appendChild(btn);

        this.wrapper.appendChild(picker);
    }

    openLayoutPicker() {
        if (this.layoutPickerWrapper) return;

        const wrapper = document.createElement('div');
        wrapper.setAttribute('data-controller', 'modal');
        wrapper.setAttribute(
            'data-action',
            'turbo:before-cache@window->modal#close',
        );

        const dialog = document.createElement('dialog');
        dialog.className = 'shadow-lg dialog-lg overflow-y-auto';
        dialog.setAttribute('data-modal-target', 'dialog');
        dialog.setAttribute(
            'data-action',
            'close->modal#close mousedown->modal#mouseDown mouseup->modal#mouseUp',
        );

        dialog.appendChild(this.buildLayoutPickerContent());
        wrapper.appendChild(dialog);
        document.body.appendChild(wrapper);
        this.layoutPickerWrapper = wrapper;
        dialog.showModal();
        document.body.classList.add('overflow-hidden');

        dialog.addEventListener('close', () => {
            if (wrapper.parentNode) wrapper.parentNode.removeChild(wrapper);
            if (this.layoutPickerWrapper === wrapper)
                this.layoutPickerWrapper = null;
            document.body.classList.remove('overflow-hidden');
        });
    }

    buildLayoutPickerContent() {
        const container = document.createElement('div');
        container.className = 'p-4';

        const heading = document.createElement('h3');
        heading.className = 'mb-3';
        heading.textContent = 'Choose a layout';
        container.appendChild(heading);

        const search = document.createElement('input');
        search.type = 'search';
        search.className = 'form-control mb-3';
        search.placeholder = 'Search layouts…';
        container.appendChild(search);

        const categories = this.collectCategories();
        const chipsBar = document.createElement('div');
        chipsBar.className = 'd-flex flex-wrap gap-2 mb-3';
        const chipButtons = [];

        const makeChip = (label, value) => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'btn btn-sm btn-outline-secondary';
            chip.textContent = label;
            chip.dataset.category = value;
            chipsBar.appendChild(chip);
            chipButtons.push(chip);
            return chip;
        };

        const allChip = makeChip('All', '');
        allChip.classList.add('active');
        categories.forEach((cat) => makeChip(cat, cat));
        container.appendChild(chipsBar);

        const grid = document.createElement('div');
        grid.className = 'row g-3';
        const cardWrappers = this.layouts.map((layout) =>
            this.buildLayoutCard(layout),
        );
        cardWrappers.forEach((card) => grid.appendChild(card));
        container.appendChild(grid);

        let activeCategory = '';
        const applyFilter = () => {
            const query = search.value.trim().toLowerCase();
            cardWrappers.forEach((card) => {
                const matchesCategory =
                    activeCategory === '' ||
                    card.dataset.category === activeCategory;
                const haystack = card.dataset.haystack || '';
                const matchesQuery =
                    query === '' || haystack.indexOf(query) !== -1;
                card.hidden = !(matchesCategory && matchesQuery);
            });
        };

        search.addEventListener('input', applyFilter);
        chipButtons.forEach((chip) => {
            chip.addEventListener('click', (event) => {
                event.preventDefault();
                activeCategory = chip.dataset.category || '';
                chipButtons.forEach((other) =>
                    other.classList.toggle('active', other === chip),
                );
                applyFilter();
            });
        });

        return container;
    }

    buildLayoutCard(layout) {
        const col = document.createElement('div');
        col.className = 'col-12 col-sm-6 col-md-4';
        col.dataset.category = layout.category || 'Other';
        col.dataset.haystack = [
            layout.name || '',
            layout.description || '',
            layout.category || '',
        ]
            .join(' ')
            .toLowerCase();

        const card = document.createElement('button');
        card.type = 'button';
        card.className =
            'card h-100 w-100 text-start border-2 p-0 overflow-hidden';
        card.style.cursor = 'pointer';

        const hasImage = !!layout.previewImage;
        const thumb = document.createElement('div');
        thumb.className =
            (hasImage ? 'bg-white' : 'bg-light') +
            ' d-flex align-items-center justify-content-center overflow-hidden border-bottom';
        thumb.style.aspectRatio = '16 / 9';

        if (hasImage) {
            const img = document.createElement('img');
            img.src = layout.previewImage;
            img.alt = '';
            img.style.display = 'block';
            img.style.maxWidth = '100%';
            img.style.maxHeight = '100%';
            img.style.width = 'auto';
            img.style.height = 'auto';
            img.style.objectFit = 'contain';
            thumb.appendChild(img);
        } else {
            const empty = document.createElement('span');
            empty.className = 'text-muted small';
            empty.textContent = 'No preview';
            thumb.appendChild(empty);
        }
        card.appendChild(thumb);

        const body = document.createElement('div');
        body.className = 'card-body';

        const title = document.createElement('div');
        title.className = 'fw-semibold';
        title.textContent = layout.name || layout.code;
        body.appendChild(title);

        if (layout.description) {
            const desc = document.createElement('div');
            desc.className = 'small text-muted mt-1';
            desc.textContent = layout.description;
            body.appendChild(desc);
        }

        card.appendChild(body);
        card.addEventListener('click', (event) => {
            event.preventDefault();
            this.closeLayoutPicker();
            this.selectLayout(layout.code);
        });

        col.appendChild(card);
        return col;
    }

    collectCategories() {
        const set = new Set();
        this.layouts.forEach((l) => {
            if (l.category) set.add(l.category);
        });
        return Array.from(set).sort();
    }

    closeLayoutPicker() {
        if (!this.layoutPickerWrapper) return;
        const dialog = this.layoutPickerWrapper.querySelector('dialog');
        if (dialog && typeof dialog.close === 'function' && dialog.open) {
            dialog.close();
        }
    }

    selectLayout(code) {
        this.data.layoutCode = code;
        this.data.values = {};
        this.renderBody();
        if (!this.readOnly && this.hasFormFields()) {
            this.openEditor();
        }
    }

    hasFormFields() {
        const layout = this.layouts.find(
            (l) => l.code === this.data.layoutCode,
        );
        if (!layout) return false;
        const fields = Array.isArray(layout.fields) ? layout.fields : [];
        if (fields.length === 0) return false;
        return fields.some((f) => !f.inlineEditable);
    }

    renderPlaceholder() {
        const card = document.createElement('div');
        card.className = 'xutim-layout-block__card position-relative';

        const header = document.createElement('div');
        header.className =
            'd-flex justify-content-between align-items-center p-2 border-bottom bg-light';

        const label = document.createElement('div');
        label.innerHTML = `<span class="badge bg-purple-lt me-2">Layout</span><strong>${this.escape(this.layoutDisplayName())}</strong>`;
        header.appendChild(label);

        if (!this.readOnly && this.hasFormFields()) {
            const editBtn = document.createElement('button');
            editBtn.type = 'button';
            editBtn.className = 'btn btn-sm btn-primary';
            editBtn.textContent = 'Edit';
            editBtn.addEventListener('click', (event) => {
                event.preventDefault();
                this.openEditor();
            });
            header.appendChild(editBtn);
        }

        card.appendChild(header);

        if (this.previewUrl) {
            const previewWrap = document.createElement('div');
            previewWrap.className =
                'xutim-layout-block__preview position-relative';
            previewWrap.style.overflow = 'hidden';

            const iframe = document.createElement('iframe');
            iframe.className = 'border-0';
            iframe.setAttribute('title', 'Layout preview');
            iframe.style.display = 'block';
            iframe.style.border = '0';
            iframe.style.width = this.PREVIEW_DESKTOP_WIDTH + 'px';
            iframe.style.height = '120px';
            iframe.style.transformOrigin = 'top left';
            // In edit mode the iframe receives keystrokes via contenteditable
            // targets; read-only mode keeps clicks falling through to the block wrapper.
            iframe.style.pointerEvents = this.readOnly ? 'none' : 'auto';
            iframe.srcdoc = this.buildSpinnerSrcdoc();
            this.previewIframe = iframe;
            this.previewWrap = previewWrap;
            this.previewScale = 1;

            previewWrap.appendChild(iframe);
            card.appendChild(previewWrap);

            this.setupPreviewScaleObserver();
            this.refreshPreview();
        } else {
            const preview = document.createElement('div');
            preview.className = 'small text-muted text-truncate p-2';
            preview.textContent = this.previewText();
            card.appendChild(preview);
        }

        this.wrapper.appendChild(card);
    }

    refreshPreview() {
        if (!this.previewIframe || !this.previewUrl || !this.data.layoutCode)
            return;

        const base = this.buildUrl(this.previewUrl, this.data.layoutCode);
        const url = this.readOnly
            ? base
            : base + (base.includes('?') ? '&' : '?') + 'edit=1';

        fetch(url, {
            method: 'POST',
            body: JSON.stringify(this.data.values || {}),
            headers: {
                Accept: 'text/html',
                'Content-Type': 'application/json',
                // Marks this as an XHR so the Symfony WebProfilerBundle
                // skips toolbar injection in the iframe srcdoc.
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('preview failed (' + response.status + ')');
                }
                return response.text();
            })
            .then((html) => {
                if (!this.previewIframe) return;
                this.previewIframe.srcdoc = html;
                this.bindIframeAutoResize(this.previewIframe);
            })
            .catch((err) => {
                console.warn('xutim-layout preview failed', err);
            });
    }

    handlePreviewMessage(event) {
        const data = event.data;
        if (
            !data ||
            data.source !== 'xutim-layout' ||
            data.type !== 'field-update'
        )
            return;
        if (
            !this.previewIframe ||
            event.source !== this.previewIframe.contentWindow
        )
            return;

        const field = data.field;
        if (typeof field !== 'string' || field === '') return;
        if (field === '__proto__' || field === 'constructor' || field === 'prototype') return;

        const value = data.value;
        if (typeof value === 'string') {
            this.data.values[field] = value;
        } else if (Array.isArray(value)) {
            this.data.values[field] = value;
        } else {
            return;
        }

        if (this.block && typeof this.block.dispatchChange === 'function') {
            try {
                this.block.dispatchChange();
            } catch (_) {
                // editor.js older versions don't expose dispatchChange; ignore.
            }
        }
    }

    bindIframeAutoResize(iframe) {
        const resize = () => {
            try {
                const doc = iframe.contentDocument;
                if (!doc || !doc.body) return;
                const h = Math.max(
                    doc.body.scrollHeight,
                    doc.documentElement.scrollHeight,
                );
                if (h > 0) {
                    iframe.style.height = h + 'px';
                    this.updatePreviewWrapHeight();
                }
            } catch (e) {
                // Cross-origin or not ready yet — ignore.
            }
        };

        iframe.addEventListener('load', () => {
            resize();

            // Observe content mutations inside the iframe so the height
            // stays in sync with async-loaded images / fonts / etc.
            try {
                const doc = iframe.contentDocument;
                if (doc && window.ResizeObserver) {
                    if (this.previewContentResizeObserver) {
                        this.previewContentResizeObserver.disconnect();
                    }
                    this.previewContentResizeObserver = new ResizeObserver(resize);
                    this.previewContentResizeObserver.observe(doc.documentElement);
                }
                // Also re-measure when images finish loading.
                if (doc) {
                    doc.querySelectorAll('img').forEach((img) => {
                        if (!img.complete) img.addEventListener('load', resize);
                    });
                }
            } catch (e) {
                // ignore
            }
        });
    }

    setupPreviewScaleObserver() {
        if (!this.previewWrap || !this.previewIframe) return;

        const apply = () => {
            if (!this.previewWrap || !this.previewIframe) return;
            const containerWidth = this.previewWrap.clientWidth;
            if (containerWidth <= 0) return;
            // Cap at 1 so wide editors don't artificially zoom the preview in.
            const scale = Math.min(
                containerWidth / this.PREVIEW_DESKTOP_WIDTH,
                1,
            );
            this.previewScale = scale;
            this.previewIframe.style.transform = 'scale(' + scale + ')';
            this.updatePreviewWrapHeight();
        };

        if (window.ResizeObserver) {
            this.previewWrapResizeObserver = new ResizeObserver(apply);
            this.previewWrapResizeObserver.observe(this.previewWrap);
        }
        apply();
    }

    updatePreviewWrapHeight() {
        if (!this.previewWrap || !this.previewIframe) return;
        const iframeHeight = parseFloat(this.previewIframe.style.height) || 0;
        if (iframeHeight > 0) {
            this.previewWrap.style.height =
                iframeHeight * this.previewScale + 'px';
        }
    }

    layoutDisplayName() {
        const layout = this.layouts.find(
            (l) => l.code === this.data.layoutCode,
        );
        return layout ? layout.name : this.data.layoutCode;
    }

    previewText() {
        const layout = this.layouts.find(
            (l) => l.code === this.data.layoutCode,
        );
        if (!layout) return '';
        const textField = (layout.fields || []).find((f) => f.translatable);
        if (!textField) return '';
        const value = this.data.values[textField.name];
        if (typeof value !== 'string' || value === '') return '';
        return value;
    }

    openEditor() {
        if (this.readOnly || !this.data.layoutCode) return;

        if (this.expandContainer) {
            this.closeEditor();
            return;
        }

        // Mirror the Admin:Modal twig component so the shared `modal`
        // Stimulus controller binds automatically — outside-click close,
        // turbo:before-cache teardown, etc. come for free.
        const wrapper = document.createElement('div');
        wrapper.setAttribute('data-controller', 'modal');
        wrapper.setAttribute(
            'data-action',
            'turbo:before-cache@window->modal#close',
        );

        const dialog = document.createElement('dialog');
        // `overflow: visible` (not `overflow-y-auto`) so tom-select /
        // other absolutely-positioned dropdowns inside the form aren't
        // clipped at the dialog edge. If a layout form ever gets tall
        // enough to need scrolling, give its form body its own
        // scroll container.
        dialog.className = 'shadow-lg dialog-lg';
        dialog.style.overflow = 'visible';
        dialog.setAttribute('data-modal-target', 'dialog');
        dialog.setAttribute(
            'data-action',
            'close->modal#close mousedown->modal#mouseDown mouseup->modal#mouseUp',
        );

        const body = document.createElement('div');
        body.className = 'p-4';
        body.innerHTML = '<div class="text-muted small">Loading…</div>';
        dialog.appendChild(body);

        dialog.addEventListener('close', () => {
            if (this.expandContainer === wrapper) this.closeEditor();
        });

        wrapper.appendChild(dialog);
        document.body.appendChild(wrapper);
        this.expandContainer = wrapper;
        this.expandDialog = dialog;
        this.expandBody = body;
        dialog.showModal();
        document.body.classList.add('overflow-hidden');

        const url =
            this.buildUrl(this.formUrl, this.data.layoutCode) +
            '?values=' +
            encodeURIComponent(JSON.stringify(this.data.values || {}));

        fetch(url, {
            method: 'GET',
            headers: {
                Accept: 'text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error(
                        'Failed to load form (' + response.status + ')',
                    );
                }
                return response.text();
            })
            .then((html) => {
                if (this.expandContainer !== wrapper) return;
                body.innerHTML = html;
                this.attachExpandedFormHandlers();
            })
            .catch((err) => {
                if (this.expandContainer !== wrapper) return;
                body.innerHTML =
                    '<div class="text-danger small">' +
                    this.escape(err.message || 'Failed to load form') +
                    '</div>';
            });
    }

    attachExpandedFormHandlers() {
        if (!this.expandBody) return;

        const container = this.expandBody.querySelector(
            '[data-xutim-layout-form]',
        );
        if (!container) return;

        // Prevent editor.js from intercepting keyboard events inside the form
        const stop = (event) => event.stopPropagation();
        container.addEventListener('keydown', stop);
        container.addEventListener('keypress', stop);
        container.addEventListener('keyup', stop);

        // Enter inside single-line inputs should not submit anything
        container.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && event.target.tagName === 'INPUT') {
                event.preventDefault();
            }
        });

        const saveBtn = container.querySelector('[data-xutim-layout-save-btn]');
        if (saveBtn) {
            saveBtn.addEventListener('click', (event) => {
                event.preventDefault();
                this.submitForm(container);
            });
        }

        const cancelBtn = container.querySelector(
            '[data-xutim-layout-cancel-btn]',
        );
        if (cancelBtn) {
            cancelBtn.addEventListener('click', (event) => {
                event.preventDefault();
                this.closeEditor();
            });
        }

        this.bindCollectionHandlers(container);
        this.bindUnionTypeHandlers(container);
    }

    bindUnionTypeHandlers(container) {
        // Refresh the form on union [type] change — clear the sibling
        // [value] first so the old UUID doesn't fail validation against
        // the new type's choice list, then re-fetch so the value field
        // is rebuilt by the server with the right picker.
        container.addEventListener('change', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) return;
            if (
                !(target instanceof HTMLSelectElement) &&
                !(target instanceof HTMLInputElement)
            )
                return;

            const name = target.getAttribute('name') || '';
            if (!name.endsWith('[type]')) return;

            const prefix = name.slice(0, -'type]'.length);
            const valueName = prefix + 'value]';
            container
                .querySelectorAll(
                    `[name="${valueName}"], [name^="${valueName}["]`,
                )
                .forEach((input) => {
                    if (input instanceof HTMLSelectElement) {
                        input.value = '';
                        Array.from(input.options).forEach(
                            (opt) => (opt.selected = false),
                        );
                    } else if (
                        input instanceof HTMLInputElement ||
                        input instanceof HTMLTextAreaElement
                    ) {
                        input.value = '';
                    }
                });
            this.refreshForm(container);
        });
    }

    refreshForm(container) {
        if (!this.refreshUrl || !this.data.layoutCode) return;

        const formData = this.collectFormData(container);
        const url = this.buildUrl(this.refreshUrl, this.data.layoutCode);

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                Accept: 'text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('refresh failed (' + response.status + ')');
                }
                return response.text();
            })
            .then((html) => {
                if (!this.expandBody) return;
                this.expandBody.innerHTML = html;
                this.attachExpandedFormHandlers();
            })
            .catch((err) => {
                console.warn('xutim-layout refresh failed', err);
            });
    }

    bindCollectionHandlers(container) {
        // Add item buttons: clone the prototype into the target collection
        container
            .querySelectorAll('[data-xutim-layout-collection-add]')
            .forEach((btn) => {
                btn.addEventListener('click', (event) => {
                    event.preventDefault();
                    const target = btn.getAttribute(
                        'data-xutim-layout-collection-target',
                    );
                    const collection = container.querySelector(
                        `[data-xutim-layout-collection][data-xutim-layout-collection-prefix="${target}"]`,
                    );
                    if (!collection) return;

                    const prototype = collection.getAttribute('data-prototype');
                    if (!prototype) return;

                    const index = parseInt(
                        collection.getAttribute(
                            'data-xutim-layout-collection-index',
                        ) || '0',
                        10,
                    );
                    const html = prototype.replaceAll(
                        '__name__',
                        String(index),
                    );

                    const wrapper = document.createElement('div');
                    wrapper.className =
                        'xutim-layout-collection__item border rounded p-2 mb-2';
                    wrapper.setAttribute(
                        'data-xutim-layout-collection-item',
                        '',
                    );
                    wrapper.innerHTML = html;

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'btn btn-sm btn-outline-danger mt-1';
                    removeBtn.setAttribute(
                        'data-xutim-layout-collection-remove',
                        '',
                    );
                    removeBtn.textContent = 'remove';
                    wrapper.appendChild(removeBtn);

                    collection.appendChild(wrapper);
                    collection.setAttribute(
                        'data-xutim-layout-collection-index',
                        String(index + 1),
                    );
                });
            });

        // Remove buttons (delegated — also catches dynamically added ones)
        container.addEventListener('click', (event) => {
            const target = event.target;
            if (!(target instanceof HTMLElement)) return;
            const removeBtn = target.closest(
                '[data-xutim-layout-collection-remove]',
            );
            if (!removeBtn) return;
            event.preventDefault();
            const item = removeBtn.closest(
                '[data-xutim-layout-collection-item]',
            );
            if (item && item.parentNode) {
                item.parentNode.removeChild(item);
            }
        });
    }

    collectFormData(container) {
        const formData = new FormData();
        const inputs = container.querySelectorAll(
            'input[name], textarea[name], select[name]',
        );
        inputs.forEach((input) => {
            if (input.type === 'checkbox' || input.type === 'radio') {
                if (input.checked) formData.append(input.name, input.value);
                return;
            }
            if (input.tagName === 'SELECT' && input.multiple) {
                Array.from(input.selectedOptions).forEach((opt) => {
                    formData.append(input.name, opt.value);
                });
                return;
            }
            formData.append(input.name, input.value);
        });
        return formData;
    }

    submitForm(container) {
        const formData = this.collectFormData(container);
        const url = this.buildUrl(this.saveUrl, this.data.layoutCode);

        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((response) =>
                response
                    .json()
                    .then((body) => ({ status: response.status, body })),
            )
            .then(({ status, body }) => {
                if (body && body.ok === true) {
                    this.data.values = body.values || {};
                    this.closeEditor();
                    this.renderBody();
                    return;
                }
                this.renderFormErrors(container, body);
            })
            .catch((err) => {
                console.warn('xutim-layout save failed', err);
                this.renderFormErrors(container, {
                    errors: { _global: [err.message || 'Network error'] },
                });
            });
    }

    renderFormErrors(container, body) {
        let box = container.querySelector('.xutim-layout-form__errors');
        if (!box) {
            box = document.createElement('div');
            box.className = 'xutim-layout-form__errors alert alert-danger mt-2';
            container.prepend(box);
        }

        const errors = (body && body.errors) || {};
        const lines = [];
        Object.keys(errors).forEach((field) => {
            const msgs = errors[field];
            if (!Array.isArray(msgs)) return;
            msgs.forEach((msg) =>
                lines.push((field === '_global' ? '' : field + ': ') + msg),
            );
        });
        box.textContent =
            lines.length > 0 ? lines.join(' · ') : 'Validation failed';
    }

    closeEditor() {
        const wrapper = this.expandContainer;
        const dialog = this.expandDialog;
        this.expandContainer = null;
        this.expandDialog = null;
        this.expandBody = null;
        if (dialog && typeof dialog.close === 'function' && dialog.open) {
            dialog.close();
        }
        if (wrapper && wrapper.parentNode) {
            wrapper.parentNode.removeChild(wrapper);
        }
        document.body.classList.remove('overflow-hidden');
    }

    save() {
        return {
            layoutCode: this.data.layoutCode || '',
            values: this.data.values || {},
        };
    }

    validate(saved) {
        return typeof saved.layoutCode === 'string' && saved.layoutCode !== '';
    }

    buildUrl(template, code) {
        return (template || '').replaceAll(':code:', encodeURIComponent(code));
    }

    buildSpinnerSrcdoc() {
        return (
            '<!doctype html><html><head><style>' +
            'html,body{margin:0;padding:0;height:100%;}' +
            '.xl-spin{display:flex;align-items:center;justify-content:center;height:120px;}' +
            '.xl-spin::before{content:"";width:24px;height:24px;border:2px solid #e5e7eb;' +
            'border-top-color:#6b7280;border-radius:50%;animation:xl-spin 0.8s linear infinite;}' +
            '@keyframes xl-spin{to{transform:rotate(360deg);}}' +
            '</style></head><body><div class="xl-spin"></div></body></html>'
        );
    }

    escape(s) {
        return String(s).replace(
            /[&<>"']/g,
            (c) =>
                ({
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;',
                })[c],
        );
    }
}
