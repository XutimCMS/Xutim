import { IconColor } from '@codexteam/icons';
import './styles.css';

export default class ColorTune {
    static get isTune() {
        return true;
    }

    constructor({ api, data, config, block }) {
        this.api = api;
        this.config = config || {};
        this.block = block;
        this.currentBlockData = data || {};
        console.log(this.currentBlockData);
        this.wrapper = null;
        this.allowedColors = this.config.colors || [
            '#000000',
            '#FF0000',
            '#00FF00',
            '#0000FF',
        ];
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.classList.add('color-tune-wrapper');

        this.allowedColors.forEach((color) => {
            const colorButton = document.createElement('div');
            colorButton.style.backgroundColor = color;
            colorButton.classList.add('color-button');
            colorButton.addEventListener('click', () => this.applyColor(color));
            this.wrapper.appendChild(colorButton);
        });

        return this.wrapper;

        this.wrapper = document.createElement('div');
        this.wrapper.classList.add('color-tune-wrapper');

        this.allowedColors.forEach((color) => {
            const colorButton = document.createElement('div');
            colorButton.style.backgroundColor = color;
            colorButton.classList.add('color-button');
            colorButton.addEventListener('click', () => this.applyColor(color));
            this.wrapper.appendChild(colorButton);
        });

        // Highlight the currently selected color
        this.highlightCurrentColor();

        return {
            el: this.wrapper, // The tune's UI element
            onActivate: () => {
                // Called when the tune is activated
                this.highlightCurrentColor();
            },
        };
    }

    applyColor(color) {
        this.currentBlockData.color = color; // Update data

        // Wrap the block content with a div and apply the color
        this.wrapBlock(color);

        this.highlightCurrentColor(); // Update UI
    }

    wrapBlock(color) {
        const blockContent =
            this.block.holder.querySelector('.ce-block__content'); // Get the block content element
        if (blockContent) {
            const wrapper = document.createElement('div');
            wrapper.style.color = color; // Apply color to the wrapper
            wrapper.classList.add('color-tune-block-wrapper'); // Add a class for styling
            // Move all children of blockContent to wrapper
            while (blockContent.firstChild) {
                wrapper.appendChild(blockContent.firstChild);
            }
            blockContent.appendChild(wrapper); // Add wrapper to the block content
        }

        console.log(this.currentBlockData);
    }

    highlightCurrentColor() {
        this.wrapper.querySelectorAll('.color-button').forEach((button) => {
            button.classList.remove('selected');
            if (button.style.backgroundColor === this.currentBlockData.color) {
                button.classList.add('selected');
            }
        });
    }

    save() {
        return this.currentBlockData;
    }

    // applyColor(color) {
    //     console.log(this.currentBlockIndex);
    //     console.log(this.currentBlockHolder);
    //     console.log(color);
    //     if (this.currentBlockIndex === null || !this.currentBlockHolder) return;
    //     console.log(color);
    //
    //     this.currentBlockHolder.style.color = color;
    //
    //     // *** Improved data updating ***
    //     const block = this.api.blocks.getBlockByIndex(this.currentBlockIndex);
    //     if (block) {
    //         // Check if block exists
    //         const data = { ...block.data, color }; // Create a new object with the color
    //         this.api.blocks.update(this.currentBlockIndex, data);
    //     }
    // }
    // render() {
    //     this.wrapper = document.createElement('div');
    //     this.wrapper.classList.add('color-tune-wrapper');
    //
    //     this.allowedColors.forEach((color) => {
    //         const colorButton = document.createElement('div');
    //         colorButton.style.backgroundColor = color;
    //         colorButton.classList.add('color-button');
    //         colorButton.addEventListener('click', () => this.applyColor(color));
    //         this.wrapper.appendChild(colorButton);
    //     });
    //
    //     return this.wrapper;
    // }

    onBlockSelected(index) {
        // Receive the block *index*
        console.log(index);
        this.currentBlockIndex = index;
        const block = this.api.blocks.getBlockByIndex(index);
        if (block) {
            this.currentBlockHolder = block.holder;
            const currentColor =
                block.data && block.data.color ? block.data.color : null;
            this.wrapper.querySelectorAll('.color-button').forEach((button) => {
                button.classList.remove('selected');
                if (button.style.backgroundColor === currentColor) {
                    button.classList.add('selected');
                }
            });
        }
    }

    onBlockUnselected() {
        this.currentBlockIndex = null;
        this.currentBlockHolder = null;
        this.wrapper
            .querySelectorAll('.color-button')
            .forEach((button) => button.classList.remove('selected'));
    }

    save() {
        return {}; // No need to save anything here. Color is saved in block.data in applyColor
    }

    static get pasteConfig() {
        return {
            tags: ['SPAN', 'P', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6'], // Specify which tags to handle
        };
    }

    // Handle pasted content. This is important to preserve styling when pasting.
    onPaste(event) {
        const pastedElement = event.detail.fragment.firstChild;

        if (pastedElement) {
            const color = pastedElement.style.color;
            if (color) {
                this.applyColor(color);
            }
        }
    }
}
