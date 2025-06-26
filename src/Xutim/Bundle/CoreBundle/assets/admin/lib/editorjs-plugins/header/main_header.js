export default class MainHeader {
    constructor({ data, config, api }) {
        this.api = api;
        this.data = data;
        this.config = config || {};
        this.availableTypes = [
            {
                id: 'pretitle',
                name: 'Pre-title',
            },
            {
                id: 'title',
                name: 'Title',
            },
            {
                id: 'subtitle',
                name: 'Sub-title',
            },
        ];
    }

    static get toolbox() {
        return {
            title: 'Main header',
            icon: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M9 7L9 12M9 17V12M9 12L15 12M15 7V12M15 17L15 12" stroke="black" stroke-width="2" stroke-linecap="round"/></svg>',
        };
    }

    #createInput($id) {
        const input = document.createElement('input');
        input.name = $id;
        input.id = $id;
        input.classList.add('form-control');

        return input;
    }

    #createLabel($id, $name) {
        const label = document.createElement('label');
        label.textContent = $name;
        label.htmlFor = $id;
        label.classList.add('z-0');

        return label;
    }

    render() {
        const wrapper = document.createElement('div');
        wrapper.classList.add('mt-4');
        wrapper.classList.add('border-1');

        this.availableTypes.forEach((type) => {
            const div = document.createElement('div');
            div.classList.add('mb-2');
            div.classList.add('form-floating');

            const input = this.#createInput(type.id);
            const label = this.#createLabel(type.id, type.name);
            // Set the value to previously selected if available
            if (this.data[type.id]) {
                input.value = this.data[type.id];
            }

            // Append the select element to the wrapper
            div.appendChild(input);
            div.appendChild(label);
            wrapper.appendChild(div);

            input.addEventListener('change', (event) => {
                this.data[type.id] = event.target.value;
            });
        });

        return wrapper;
    }

    save(blockContent) {
        return this.availableTypes.reduce((accumulator, currentVal) => {
            const input = blockContent.querySelector('input#' + currentVal.id);

            accumulator[currentVal.id] = input.value;

            return accumulator;
        }, {});
    }

    validate(savedData) {
        if (!savedData.pretitle && !savedData.title && !savedData.subtitle) {
            return false;
        }

        return true;
    }
}
