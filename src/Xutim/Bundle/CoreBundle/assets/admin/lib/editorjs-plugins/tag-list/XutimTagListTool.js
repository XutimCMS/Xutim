export default class XutimTagListTool {
    constructor({ data, config, api }) {
        this.api = api;
        this.data = {
            id: data.id || '',
            layout: data.layout || 'regular',
        };
        this.config = config || {};
    }

    static get toolbox() {
        return {
            title: 'Tag list',
            icon: '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-tag"><path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z" /></svg>',
        };
    }

    render() {
        const wrapper = document.createElement('div');
        wrapper.classList.add('mt-4');
        wrapper.classList.add('form-floating');

        const select = document.createElement('select');
        select.name = 'idSelect';
        select.id = 'idSelect';
        select.classList.add('form-select');

        const label = document.createElement('label');
        label.textContent = 'Select a tag';
        label.htmlFor = 'idSelect';

        // Populate the select element with options from config
        this.config.tags.forEach((tag) => {
            const option = document.createElement('option');
            option.value = tag.id;
            option.textContent = tag.label;
            select.appendChild(option);
        });

        // Set the value to previously selected if available
        if (this.data.id) {
            // Ensure the value exists in options to avoid issues
            const optionExists = this.config.tags.some(
                (tag) => tag.id === this.data.id,
            );
            if (optionExists) {
                select.value = this.data.id;
            }
        }

        // Append the select element to the wrapper
        wrapper.appendChild(select);
        wrapper.appendChild(label);

        // Save the selected value
        select.addEventListener('change', (event) => {
            this.data.id = event.target.value;
        });

        return wrapper;
    }

    save(blockContent) {
        const input = blockContent.querySelector('select');

        return {
            id: input.value,
        };
    }

    validate(savedData) {
        const idToValidate = savedData.id.trim();

        if (!idToValidate) {
            return false;
        }

        for (const tag of this.config.tags) {
            if (tag.id === idToValidate) {
                return true;
            }
        }

        return false;
    }

    renderSettings() {
        const layouts = [
            { name: 'regular', icon: '🟦', label: 'Regular' },
            { name: 'compact', icon: '🔲', label: 'Compact' },
            { name: 'inline', icon: '⬛', label: 'Inline' },
        ];

        const wrapper = document.createElement('div');

        layouts.forEach((layout) => {
            const button = document.createElement('div');
            button.classList.add(this.api.styles.settingsButton);
            if (this.data.layout === layout.name) {
                button.classList.add(this.api.styles.settingsButtonActive);
            }

            button.innerHTML = layout.icon;
            button.title = layout.label;

            button.addEventListener('click', () => {
                this.data.layout = layout.name;

                // Refresh active state
                Array.from(wrapper.children).forEach((child) =>
                    child.classList.remove(
                        this.api.styles.settingsButtonActive,
                    ),
                );
                button.classList.add(this.api.styles.settingsButtonActive);
            });

            wrapper.appendChild(button);
        });

        return wrapper;
    }
}
