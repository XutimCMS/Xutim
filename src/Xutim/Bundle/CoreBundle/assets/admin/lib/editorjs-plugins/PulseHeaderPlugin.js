import Header from '@editorjs/header';

export default class PulseHeader extends Header {
    constructor({ data, config, api }) {
        super({ data, config, api });

        // Set default label
        this.label = data.label || 'title';

        this.availableLabels = ['pretitle', 'title', 'subtitle'];
    }

    /**
     * Returns header block tunes config
     *
     * @returns {Array}
     */
    renderSettings() {
        const wrapper = document.createElement('div');

        // Create buttons for custom labels
        this.availableLabels.forEach((label) => {
            const button = document.createElement('button');
            button.classList.add(this.api.styles.settingsButton);
            button.innerHTML = label.charAt(0).toUpperCase() + label.slice(1);
            button.type = 'button';

            // Highlight button if it's the current label
            if (this.label === label) {
                button.classList.add(this.api.styles.settingsButtonActive);
            }

            button.addEventListener('click', () => {
                this._toggleLabel(label);
                this._updateToolbar(wrapper);
            });

            wrapper.appendChild(button);
        });

        // Add existing header settings (for levels 1-6)
        this.settings.forEach((tune) => {
            const button = this.createSettingButton(tune);
            wrapper.appendChild(button);
        });

        return wrapper;
        const levels = this.levels.map((level) => ({
            icon: level.svg,
            label: this.api.i18n.t(`Heading ${level.number}`),
            onActivate: () => this.setLevel(level.number),
            closeOnActivate: true,
            isActive: this.currentLevel.number === level.number,
            render: () => document.createElement('div'),
        }));

        return levels.concat(
            this.availableLabels.map((type) => ({
                icon: '',
                label: type.code,
                name: type.name,
                onActivate: () => this.setType(type.code),
                closeOnActivate: true,
                isActive: this.data.type === type.code,
                render: () => document.createElement('div'),
            })),
        );
    }

    /**
     * Callback for Block's settings buttons
     *
     * @param {number} level - level to set
     */
    setType(type) {
        this.data = {
            level: this.data.level,
            type: type,
            text: this.data.text,
        };
    }

    // renderSettings() {
    //     const wrapper = document.createElement('div');
    //
    //     // Create buttons for custom labels
    //     this.availableLabels.forEach((label) => {
    //         const button = document.createElement('button');
    //         button.classList.add(this.api.styles.settingsButton);
    //         button.innerHTML = label.charAt(0).toUpperCase() + label.slice(1);
    //         button.type = 'button';
    //
    //         // Highlight button if it's the current label
    //         if (this.label === label) {
    //             button.classList.add(this.api.styles.settingsButtonActive);
    //         }
    //
    //         button.addEventListener('click', () => {
    //             this._toggleLabel(label);
    //             this._updateToolbar(wrapper);
    //         });
    //
    //         wrapper.appendChild(button);
    //     });
    //
    //     // Add existing header settings (for levels 1-6)
    //     this.settings.forEach((tune) => {
    //         const button = this.createSettingButton(tune);
    //         wrapper.appendChild(button);
    //     });
    //
    //     return wrapper;
    // }

    // Method to toggle the label
    _toggleLabel(label) {
        this.label = label;
    }

    // Update toolbar to show active state
    _updateToolbar(wrapper) {
        Array.from(wrapper.children).forEach((button) => {
            if (button.innerText.toLowerCase() === this.label) {
                button.classList.add(this.api.styles.settingsButtonActive);
            } else {
                button.classList.remove(this.api.styles.settingsButtonActive);
            }
        });
    }

    save(blockContent) {
        const data = super.save(blockContent);
        return {
            ...data,
            label: this.label,
        };
    }
}
