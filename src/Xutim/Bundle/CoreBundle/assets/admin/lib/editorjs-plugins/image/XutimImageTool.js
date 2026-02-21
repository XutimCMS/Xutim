import ImageGalleryModal from './../../ImageGalleryModal.js';

export default class XutimImageTool {
    static get toolbox() {
        return {
            title: 'Image',
            icon: '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><rect width="14" height="14" x="5" y="5" stroke="currentColor" stroke-width="2" rx="4"></rect><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.13968 15.32L8.69058 11.5661C9.02934 11.2036 9.48873 11 9.96774 11C10.4467 11 10.9061 11.2036 11.2449 11.5661L15.3871 16M13.5806 14.0664L15.0132 12.533C15.3519 12.1705 15.8113 11.9668 16.2903 11.9668C16.7693 11.9668 17.2287 12.1705 17.5675 12.533L18.841 13.9634"></path><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.7778 9.33331H13.7867"></path></svg>',
        };
    }

    static get isReadOnlySupported() {
        return true;
    }

    constructor({ data, config, api, block, readOnly }) {
        this.data = data || {};
        this.config = config || {};
        this.api = api;
        this.block = block;
        this.wrapper = null;
        this.modal = null;
        this.galleryUrl = this.config.galleryUrl || '';
        this.readOnly = !!readOnly;
    }

    render() {
        this.wrapper = document.createElement('div');

        this.wrapper.className = 'border rounded p-3 my-4';
        this.wrapper.style.cursor = 'pointer';
        if (!this.readOnly) {
            this.wrapper.style.cursor = 'pointer';
        }

        const placeholderText = document.createElement('span');
        placeholderText.textContent = '+ Add Image';
        placeholderText.style.color = '#aaa';
        this.wrapper.appendChild(placeholderText);

        const img = document.createElement('img');
        img.className = 'rounded';
        img.style.maxWidth = '100%';
        img.style.height = 'auto';
        img.style.display = 'block';

        if (this.data.file && this.data.file.url) {
            const fallbackUrl = this.data.file.url;
            this.wrapper.classList.remove('border', 'p-3');
            img.src = this.data.file.thumbnailUrl || fallbackUrl;
            img.onerror = () => {
                img.onerror = null;
                img.src = fallbackUrl;
            };
            img.dataset.id = this.data.file.id;
            img.dataset.url = this.data.file.url;
            img.dataset.thumbnailUrl =
                this.data.file.thumbnailUrl || fallbackUrl;
            placeholderText.style.display = 'none';
        }

        this.wrapper.appendChild(img);

        this.wrapper.addEventListener('click', (_) => {
            this.openImageEditor();
        });
        if (!this.readOnly) {
            this.wrapper.addEventListener('click', (_) => {
                this.openImageEditor();
            });
        }

        return this.wrapper;
    }

    openImageEditor() {
        if (this.readOnly) {
            return;
        }
        if (!this.modal) {
            this.modal = new ImageGalleryModal({
                galleryUrl: this.galleryUrl,
                onSelect: (image) => {
                    this.#selectImage(
                        image.fullSourceUrl,
                        image.filteredUrl,
                        image.id,
                    );
                },
            });
        }

        this.modal.show();
    }

    #selectImage(imageUrl, thumbnailUrl, id) {
        this.data.file = {
            url: imageUrl,
            id: id,
            thumbnailUrl: thumbnailUrl,
        };
        const imgElement = this.wrapper.querySelector('img');
        const placeholderText = this.wrapper.querySelector('span');

        this.wrapper.classList.remove('border', 'p-3');
        imgElement.src = thumbnailUrl || imageUrl;
        imgElement.dataset.url = imageUrl;
        imgElement.dataset.thumbnailUrl = thumbnailUrl;
        imgElement.dataset.id = id;
        imgElement.style.maxWidth = '100%';
        imgElement.style.height = 'auto';
        imgElement.style.display = 'block';
        placeholderText.style.display = 'none';
        this.api.blocks.update(this.block.id, this.data);
        this.closeModal();
    }

    closeModal() {
        if (this.modal) {
            this.modal.close();
        }
    }

    // renderSettings() {
    //     const wrapper = document.createElement('div');
    //     wrapper.classList.add('cdx-settings-popover');
    //
    //     this.allowedImagesPerRow.forEach((option) => {
    //         const button = document.createElement('button');
    //         button.classList.add('ce-popover-item');
    //         button.type = 'button';
    //         button.style.display = 'flex';
    //         button.style.flexDirection = 'row';
    //         button.style.alignItems = 'center';
    //         button.style.justifyContent = 'flex-start';
    //
    //         const iconSpan = document.createElement('span');
    //         iconSpan.classList.add(
    //             'ce-popover-item__icon',
    //             'ce-popover-item__icon--tool',
    //         );
    //         iconSpan.innerHTML = '';
    //
    //         const textSpan = document.createElement('span');
    //         textSpan.classList.add('ce-popover-item__title');
    //         textSpan.textContent = `${option} Images`;
    //
    //         if (option === this.imagesPerRow) {
    //             button.classList.add('ce-popover-item--active');
    //         }
    //
    //         button.addEventListener('click', (event) => {
    //             event.preventDefault();
    //             const selectedValue = parseInt(
    //                 button.textContent.split(' ')[0],
    //                 10,
    //             ); // Extract number from text
    //             this.setImagesPerRow(selectedValue);
    //             this.data.imagesPerRow = selectedValue;
    //             this.api.blocks.update(this.block.id, this.data);
    //             this.render();
    //         });
    //
    //         button.appendChild(iconSpan);
    //         button.appendChild(textSpan);
    //         wrapper.appendChild(button);
    //     });
    //
    //     return wrapper;
    // }

    validate(savedData) {
        if (
            Object.keys(savedData.file).length === 0 &&
            savedData.file.constructor === Object
        ) {
            return false;
        }

        return true;
    }

    save() {
        if (this.readOnly) {
            return {
                file: this.data && this.data.file ? this.data.file : {},
            };
        }
        const img = this.wrapper.querySelector('img');
        let data = {};
        if (img.dataset.url && img.style.display === 'block') {
            data = {
                url: img.dataset.url,
                id: img.dataset.id,
                thumbnailUrl: img.dataset.thumbnailUrl || img.dataset.url,
            };
        }

        return {
            file: data,
        };
    }
}
