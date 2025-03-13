import ImageTool from '@editorjs/image';

export default class PulseImage extends ImageTool {
    render() {
        const wrapper = super.render();

        // Create a button to open the image selector
        const selectImageButton = document.createElement('button');
        selectImageButton.type = 'button';
        selectImageButton.innerText = 'Select Existing Image';
        selectImageButton.addEventListener(
            'click',
            this.openImageSelector.bind(this),
        );

        // Append the button to the wrapper
        wrapper.appendChild(selectImageButton);

        return wrapper;
    }

    openImageSelector() {
        // Your logic to open the image selector and get the selected image URL
        const selectedImageUrl = 'https://example.com/your-selected-image.jpg';

        // Insert the selected image into the editor
        this.insertImage(selectedImageUrl);
    }

    insertImage(url) {
        // Call the ImageTool's method to insert the image
        this.api.blocks.insert('image', { file: { url: url } });
    }
}
