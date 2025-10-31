export default class FoldableEnd {
    constructor({ data, api, readOnly }) {
        this.api = api;
        this.data = data || {};
        this.readOnly = !!readOnly;
    }

    static get isReadOnlySupported() {
        return true;
    }

    static get toolbox() {
        return {
            title: 'Foldable End',
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-fold-up"><path d="M12 13v-8l-3 3m6 0l-3 -3" /><path d="M9 17l1 0" /><path d="M14 17l1 0" /><path d="M19 17l1 0" /><path d="M4 17l1 0" /></svg>',
        };
    }

    render() {
        const wrapper = document.createElement('div');
        wrapper.classList.add('foldable-end-block', 'text-center');

        const content = document.createElement('div');
        content.classList.add(
            'd-flex',
            'align-items-center',
            'justify-content-center',
            'gap-2',
        );

        const icon = document.createElement('span');
        icon.innerHTML = '▲';
        icon.classList.add('text-muted');
        icon.style.fontSize = '0.75rem';

        const text = document.createElement('span');
        text.textContent = 'End foldable';
        text.classList.add('text-muted', 'small');

        content.appendChild(icon);
        content.appendChild(text);
        wrapper.appendChild(content);

        return wrapper;
    }

    save() {
        return {};
    }

    validate() {
        return true;
    }
}
