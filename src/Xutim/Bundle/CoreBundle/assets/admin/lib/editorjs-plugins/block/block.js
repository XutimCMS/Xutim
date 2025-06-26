export default class Block {
    constructor({ data, config, api }) {
        this.api = api;
        this.data = data;
        this.config = config || {};
    }

    static get toolbox() {
        return {
            title: 'Block',
            icon: '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-container"><path d="M20 4v.01" /><path d="M20 20v.01" /><path d="M20 16v.01" /><path d="M20 12v.01" /><path d="M20 8v.01" /><path d="M8 4m0 1a1 1 0 0 1 1 -1h6a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-6a1 1 0 0 1 -1 -1z" /><path d="M4 4v.01" /><path d="M4 20v.01" /><path d="M4 16v.01" /><path d="M4 12v.01" /><path d="M4 8v.01" /></svg>',
        };
    }

    render() {
        const wrapper = document.createElement('div');
        wrapper.classList.add('mt-4');
        wrapper.classList.add('form-floating');

        const select = document.createElement('select');
        select.name = 'codeSelect';
        select.id = 'codeSelect';
        select.classList.add('form-select');

        const label = document.createElement('label');
        label.textContent = 'Select a block';
        label.htmlFor = 'codeSelect';
        label.classList.add('z-0');

        // Populate the select element with options from config
        this.config.codes.forEach((code) => {
            const option = document.createElement('option');
            option.value = code.code;
            option.textContent = code.label;
            select.appendChild(option);
        });

        // Set the value to previously selected if available
        if (this.data.code) {
            // Ensure the value exists in options to avoid issues
            const optionExists = this.config.codes.some(
                (code) => code.code === this.data.code,
            );
            if (optionExists) {
                select.value = this.data.code;
            }
        }

        // Append the select element to the wrapper
        wrapper.appendChild(select);
        wrapper.appendChild(label);

        // Save the selected value
        select.addEventListener('change', (event) => {
            this.data.code = event.target.value;
        });

        return wrapper;
    }

    save(blockContent) {
        const input = blockContent.querySelector('select');

        return {
            code: input.value,
        };
    }

    validate(savedData) {
        const codeToValidate = savedData.code.trim();

        if (!codeToValidate) {
            return false;
        }

        for (const block of this.config.codes) {
            if (block.code === codeToValidate) {
                return true;
            }
        }

        return false;
    }
}
