export default class XutimAnchorTune {
    static get isTune() {
        return true;
    }

    constructor({ api, data, config }) {
        this.api = api;
        this.config = config || {};
        this.snippetListUrl = this.config.snippetListUrl;

        // Type: 'snippet' or 'footnote'
        this.data = {
            anchor: data?.anchor || '',
            type: data?.type || 'snippet',
        };

        this.selectElement = null;
        this.wrapper = null;

        this.prefix = 'fn';
        this.initialFootCount = 10;

        this._CSS = {
            wrapper: 'cdx-search-field',
            icon: 'cdx-search-field__icon',
            select: 'cdx-search-field__input',
        };
    }

    get anchor() {
        return this.data.anchor || '';
    }

    set anchor(val) {
        this.data.anchor = val;
    }

    get type() {
        return this.data.type || 'snippet';
    }

    set type(val) {
        this.data.type = val;
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.classList.add(this._CSS.wrapper);

        const icon = document.createElement('div');
        // icon.classList.add(this._CSS.icon);
        icon.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 class="icon icon-tabler icon-tabler-anchor fw-bold me-2">
              <path d="M12 9v12m-8 -8a8 8 0 0 0 16 0m1 0h-2m-14 0h-2" />
              <path d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
            </svg>`;

        this.selectElement = document.createElement('select');
        this.selectElement.classList.add(this._CSS.select);

        this.selectElement.addEventListener('change', (event) => {
            const value = event.target.value;
            this.anchor = value;

            // Detect type by prefix
            this.type = value.startsWith(this.prefix) ? 'footnote' : 'snippet';

            // Dynamically extend footnotes if user picks a high number
            this.maybeExtendFootnotes();
        });

        // Start rendering
        this.populateInitialOptions();

        this.wrapper.appendChild(icon);
        this.wrapper.appendChild(this.selectElement);

        return this.wrapper;
    }

    /**
     * Populates both fetched snippet anchors and generated footnotes
     */
    populateInitialOptions() {
        this.addDefaultOption();

        if (this.snippetListUrl) {
            this.loadSnippets();
        }
    }

    addDefaultOption() {
        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = this.api.i18n.t('Select an anchor…');
        defaultOption.selected = !this.anchor;
        this.selectElement.appendChild(defaultOption);
    }

    /**
     * Load snippet anchors via AJAX
     */
    loadSnippets() {
        fetch(this.snippetListUrl)
            .then((response) => {
                if (!response.ok)
                    throw new Error(
                        `Failed to fetch snippets: ${response.statusText}`,
                    );
                return response.json();
            })
            .then((snippets) => {
                snippets.forEach((snippet) => {
                    const opt = document.createElement('option');
                    opt.value = snippet.code;
                    opt.textContent = snippet.code;
                    if (snippet.code === this.anchor) opt.selected = true;
                    this.selectElement.appendChild(opt);
                });
                this.populateFootnotes(this.initialFootCount);
            })
            .catch((error) => {
                console.error('Failed to load snippets:', error);
            });
    }

    /**
     * Generate footnote options: foot1, foot2, ..., footN
     */
    populateFootnotes(upTo) {
        const existing = new Set(
            Array.from(this.selectElement.options).map((opt) => opt.value),
        );

        for (let i = 1; i <= upTo; i++) {
            const val = `${this.prefix}${i}`;
            if (!existing.has(val)) {
                const opt = document.createElement('option');
                opt.value = val;
                opt.textContent = val;
                if (val === this.anchor) opt.selected = true;
                this.selectElement.appendChild(opt);
            }
        }
    }

    /**
     * When selecting a footnote, ensure 5+ more ahead are available
     */
    maybeExtendFootnotes() {
        const values = Array.from(this.selectElement.options)
            .map((opt) => opt.value)
            .filter((v) => v.startsWith(this.prefix));

        const currentNum = parseInt(this.anchor.replace(this.prefix, '')) || 0;
        const maxNum = Math.max(
            ...values.map((v) => parseInt(v.replace(this.prefix, '')) || 0),
        );

        const ahead = values.filter(
            (v) => parseInt(v.replace(this.prefix, '')) > currentNum,
        ).length;

        if (ahead < 5) {
            this.populateFootnotes(maxNum + 5);
        }
    }

    /**
     * Save both anchor and type
     */
    save() {
        return this.anchor
            ? { anchor: this.anchor, type: this.type }
            : undefined;
    }
}
