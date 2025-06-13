import Header from '@editorjs/header';

export default class ColoredHeader extends Header {
    constructor({ data, config, api }) {
        super({ data, config, api });
        this.api = api;
        this.data.color = data.color || config.defaultColor || '#000000';
    }

    render() {
        const element = super.render();
        element.style.color = this.data.color;

        return element;
    }

    renderSettings() {
        const settings = super.renderSettings();

        settings.push({
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/></svg>',
            label: 'Text Color',
            onActivate: () => {
                const colorInput = document.createElement('input');
                colorInput.type = 'color';
                colorInput.value = this.data.color;
                colorInput.addEventListener('input', (event) => {
                    this.data.color = event.target.value;
                    if (this.wrapper) {
                        this.wrapper.style.color = this.data.color;
                    }
                });
                colorInput.click();
            },
        });

        return settings;
    }

    save() {
        return { ...super.save(), color: this.data.color };
    }
}
