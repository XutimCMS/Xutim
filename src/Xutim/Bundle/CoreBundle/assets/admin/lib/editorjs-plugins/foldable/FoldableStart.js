export default class FoldableStart {
    constructor({ data, api, readOnly }) {
        this.api = api;
        this.data = {
            title: data.title || '',
            open: data.open !== undefined ? data.open : false,
        };
        this.readOnly = !!readOnly;
    }

    static get isReadOnlySupported() {
        return true;
    }

    static get toolbox() {
        return {
            title: 'Foldable Start',
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-fold-down"><path d="M12 11v8l3 -3m-6 0l3 3" /><path d="M9 7l1 0" /><path d="M14 7l1 0" /><path d="M19 7l1 0" /><path d="M4 7l1 0" /></svg>',
        };
    }

    render() {
        const wrapper = document.createElement('div');
        wrapper.classList.add('foldable-start-block');

        if (this.readOnly) {
            wrapper.classList.add('p-2', 'text-center');

            const content = document.createElement('div');
            content.classList.add(
                'd-flex',
                'align-items-center',
                'justify-content-center',
                'gap-2',
            );

            const icon = document.createElement('span');
            icon.innerHTML = '▼';
            icon.classList.add('text-muted');
            icon.style.fontSize = '0.75rem';

            const text = document.createElement('span');
            text.textContent = this.data.title || 'Untitled foldable section';
            text.classList.add('text-muted', 'small');

            content.appendChild(icon);
            content.appendChild(text);
            wrapper.appendChild(content);

            return wrapper;
        }

        wrapper.classList.add('p-3');

        const titleInput = document.createElement('input');
        titleInput.type = 'text';
        titleInput.value = this.data.title;
        titleInput.placeholder = 'Foldable section title...';
        titleInput.classList.add('form-control', 'form-control-sm', 'mb-2');

        titleInput.addEventListener('input', (e) => {
            this.data.title = e.target.value;
        });

        const checkboxWrapper = document.createElement('div');
        checkboxWrapper.classList.add('form-check');

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.checked = this.data.open;
        checkbox.id = `foldable-open-${Date.now()}`;
        checkbox.classList.add('form-check-input');

        checkbox.addEventListener('change', (e) => {
            this.data.open = e.target.checked;
        });

        const checkboxLabel = document.createElement('label');
        checkboxLabel.textContent = 'Open by default';
        checkboxLabel.htmlFor = checkbox.id;
        checkboxLabel.classList.add('form-check-label', 'small', 'text-muted');

        checkboxWrapper.appendChild(checkbox);
        checkboxWrapper.appendChild(checkboxLabel);

        wrapper.appendChild(titleInput);
        wrapper.appendChild(checkboxWrapper);

        const indicator = document.createElement('div');
        indicator.classList.add(
            'd-flex',
            'align-items-center',
            'justify-content-center',
            'gap-2',
            'mt-3',
            'pt-2',
            'border-top',
        );

        const icon = document.createElement('span');
        icon.innerHTML = '▼';
        icon.classList.add('text-muted');
        icon.style.fontSize = '0.75rem';

        const text = document.createElement('span');
        text.textContent = 'Start foldable';
        text.classList.add('text-muted', 'small');

        indicator.appendChild(icon);
        indicator.appendChild(text);
        wrapper.appendChild(indicator);

        return wrapper;
    }

    save(blockContent) {
        if (this.readOnly) {
            return this.data;
        }

        const input = blockContent.querySelector('input[type="text"]');
        const checkbox = blockContent.querySelector('input[type="checkbox"]');

        return {
            title: input ? input.value : this.data.title,
            open: checkbox ? checkbox.checked : this.data.open,
        };
    }

    validate(savedData) {
        if (!savedData.title || savedData.title.trim() === '') {
            return false;
        }
        return true;
    }
}
