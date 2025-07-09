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

        const link = document.createElement('a');
        link.href = '#';
        link.target = '_blank';
        link.style.display = 'none';
        link.classList.add('d-block', 'mt-2');

        // Populate the select element with options from config
        this.config.codes.forEach((code) => {
            const option = document.createElement('option');
            option.value = code.code;
            option.textContent = code.label;
            select.appendChild(option);
        });

        select.setAttribute('data-controller', 'tom-select');
        // Set the value to previously selected if available
        if (this.data.code) {
            select.value = this.data.code;
            const selectedText = select.options[select.selectedIndex].text;

            select.tomselect?.destroy();
            select.removeAttribute('data-controller');

            select.value = this.data.code;
            link.href = `//${this.data.code}`;
            link.textContent = `🧱 Block ${selectedText}`;
            link.style.display = 'inline';
            select.style.display = 'none';
            label.style.display = 'none';
        }

        select.addEventListener('change', (event) => {
            const selectedCode = event.target.value;
            const selectedText =
                event.target.options[event.target.selectedIndex].text;
            this.data.code = selectedCode;

            if (selectedCode) {
                select.tomselect?.destroy();
                select.removeAttribute('data-controller');
                select.value = selectedCode;
                link.href = `//${selectedCode}`;
                link.textContent = `🧱 Block: ${selectedText}`;
                link.style.display = 'inline';
                select.style.display = 'none';
                label.style.display = 'none';
            }
        });

        link.addEventListener('click', (event) => {
            event.preventDefault();
            select.style.display = 'block';
            label.style.display = 'block';
            link.style.display = 'none';

            select.setAttribute('data-controller', 'tom-select');
        });

        // Append the select element to the wrapper
        wrapper.appendChild(select);
        wrapper.appendChild(label);
        wrapper.appendChild(link);

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
