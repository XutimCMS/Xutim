export default class XutimAnchorTune {
    static get isTune() {
        return true;
    }

    constructor({ api, data, config }) {
        this.api = api;
        this.config = config || {};
        this.snippetListUrl = this.config.snippetListUrl;

        this.data = {
            anchor: data?.anchor || '',
        };

        this._CSS = {
            classWrapper: 'cdx-search-field',
            classIcon: 'cdx-search-field__icon',
            classSelect: 'cdx-search-field__input',
        };

        this.selectElement = null;
        this.wrapper = null;
    }

    get anchor() {
        return this.data.anchor || '';
    }

    set anchor(anchor) {
        this.data.anchor = anchor;
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.classList.add(this._CSS.classWrapper);

        const wrapperIcon = document.createElement('div');
        // wrapperIcon.classList.add(this._CSS.classIcon);
        wrapperIcon.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" 
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" 
                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                 class="icon icon-tabler icons-tabler-outline icon-tabler-anchor fw-bold me-2">
                <path d="M12 9v12m-8 -8a8 8 0 0 0 16 0m1 0h-2m-14 0h-2" />
                <path d="M12 6m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
            </svg>`;

        const select = document.createElement('select');
        select.classList.add(this._CSS.classSelect);

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = this.api.i18n.t('Select an anchor...');
        select.appendChild(defaultOption);

        this.selectElement = select;

        select.addEventListener('change', (event) => {
            this.anchor = event.target.value;
        });

        this.wrapper.appendChild(wrapperIcon);
        this.wrapper.appendChild(select);

        // Load snippets in background
        this.loadSnippets();

        return this.wrapper;
    }

    loadSnippets() {
        if (!this.snippetListUrl) {
            console.warn('snippetListUrl is not defined in config');
            return;
        }

        fetch(this.snippetListUrl)
            .then((response) => {
                if (!response.ok) {
                    throw new Error(
                        `Failed to fetch snippets: ${response.statusText}`,
                    );
                }
                return response.json();
            })
            .then((snippets) => {
                snippets.forEach((snippet) => {
                    const option = document.createElement('option');
                    option.value = snippet.code;
                    option.textContent = snippet.code;

                    if (snippet.code === this.anchor) {
                        option.selected = true;
                    }

                    this.selectElement.appendChild(option);
                });
            })
            .catch((error) => {
                console.error('Failed to load snippets:', error);
            });
    }

    save() {
        if (!this.data.anchor) {
            return undefined;
        }

        return this.data;
    }
}
