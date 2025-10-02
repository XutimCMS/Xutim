import ImageGalleryModal from './../../ImageGalleryModal.js';

export default class ImageRowTool {
    static get toolbox() {
        return {
            title: 'Images in a row',
            icon: '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-layout-collage"><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M10 4l4 16" /><path d="M12 12l-8 2" /></svg>',
        };
    }

    static get isReadOnlySupported() {
        return true;
    }

    constructor({ data, config, api, block, readOnly }) {
        this.data = data || {};
        this.data.images = this.data.images || [];
        this.config = config || {};
        this.api = api;
        this.block = block;
        this.wrapper = null;
        this.currentImageIndex = undefined;
        this.modal = null;
        this.galleryUrl = this.config.galleryUrl || '';

        this.allowedImagesPerRow = this.config.allowedImagesPerRow || [
            2, 3, 4, 5,
        ];
        this.defaultImagesPerRow = this.config.defaultImagesPerRow || 5;
        this.setImagesPerRow(data.imagesPerRow);
        this.readOnly = !!readOnly;
    }

    setImagesPerRow(imagesPerRowValue) {
        if (
            this.allowedImagesPerRow.includes(parseInt(imagesPerRowValue, 10))
        ) {
            this.imagesPerRow = parseInt(imagesPerRowValue, 10);
        } else {
            this.imagesPerRow = this.defaultImagesPerRow;
        }
    }

    render() {
        this.wrapper = document.createElement('div');
        this.wrapper.style.display = 'flex';
        this.wrapper.style.flexWrap = 'nowrap';
        this.wrapper.style.overflowX = 'auto';
        this.wrapper.style.gap = '10px';

        if (!this.readOnly) {
            this.wrapper.style.cursor = 'pointer';
        }

        for (let i = 0; i < this.imagesPerRow; i++) {
            const imageContainer = document.createElement('div');
            imageContainer.className = 'border rounded';
            imageContainer.style.flex = '1';
            imageContainer.style.position = 'relative';
            imageContainer.style.height = '100px';
            imageContainer.style.display = 'flex';
            imageContainer.style.alignItems = 'center';
            imageContainer.style.justifyContent = 'center';
            imageContainer.style.cursor = 'pointer'; // Indicate it's clickable

            const placeholderText = document.createElement('span');
            placeholderText.textContent = '+ Add Image';
            placeholderText.style.color = '#aaa';
            imageContainer.appendChild(placeholderText);

            const img = document.createElement('img');
            img.className = 'rounded';
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
            img.style.display = 'block';
            img.style.position = 'absolute';
            img.style.top = '0';
            img.style.left = '0';
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.display = 'none';

            if (this.data.images[i] && this.data.images[i].url) {
                img.src = this.data.images[i].thumbnailUrl;
                img.dataset.id = this.data.images[i].id;
                img.dataset.url = this.data.images[i].url;
                img.style.display = 'block';
                placeholderText.style.display = 'none';
            }

            imageContainer.appendChild(img);

            imageContainer.addEventListener('click', (event) => {
                this.currentImageIndex = i;
                this.openImageEditor(i);
            });

            if (!this.readOnly) {
                imageContainer.style.cursor = 'pointer';
                imageContainer.addEventListener('click', (event) => {
                    this.currentImageIndex = i;
                    this.openImageEditor(i);
                });
            }

            this.wrapper.appendChild(imageContainer);
        }
        return this.wrapper;
    }

    openImageEditor(index) {
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
        if (this.currentImageIndex !== undefined) {
            this.data.images[this.currentImageIndex] = {
                url: imageUrl,
                id: id,
                thumbnailUrl: thumbnailUrl,
            };
            const imgElement =
                this.wrapper.querySelectorAll('img')[this.currentImageIndex];
            const placeholderText =
                this.wrapper.querySelectorAll('span')[this.currentImageIndex];

            imgElement.src = imageUrl;
            imgElement.dataset.thumbnailUrl = thumbnailUrl;
            imgElement.dataset.id = id;
            imgElement.style.display = 'block';
            placeholderText.style.display = 'none';
            this.api.blocks.update(this.block.id, this.data);
        }
        this.closeModal();
    }

    closeModal() {
        if (this.modal) {
            this.modal.close();
        }
    }

    renderSettings() {
        if (this.readOnly) {
            return null;
        }
        const wrapper = document.createElement('div');
        wrapper.classList.add('cdx-settings-popover');

        this.allowedImagesPerRow.forEach((option) => {
            const button = document.createElement('button');
            button.classList.add('ce-popover-item');
            button.type = 'button';
            button.style.display = 'flex';
            button.style.flexDirection = 'row';
            button.style.alignItems = 'center';
            button.style.justifyContent = 'flex-start';

            const iconSpan = document.createElement('span');
            iconSpan.classList.add(
                'ce-popover-item__icon',
                'ce-popover-item__icon--tool',
            );
            iconSpan.innerHTML = '';

            const textSpan = document.createElement('span');
            textSpan.classList.add('ce-popover-item__title');
            textSpan.textContent = `${option} Images`;

            if (option === this.imagesPerRow) {
                button.classList.add('ce-popover-item--active');
            }

            button.addEventListener('click', (event) => {
                event.preventDefault();
                const selectedValue = parseInt(
                    button.textContent.split(' ')[0],
                    10,
                ); // Extract number from text
                this.setImagesPerRow(selectedValue);
                this.data.imagesPerRow = selectedValue;
                this.api.blocks.update(this.block.id, this.data);
                this.render();
            });

            button.appendChild(iconSpan);
            button.appendChild(textSpan);
            wrapper.appendChild(button);
        });

        return wrapper;
    }

    save() {
        if (this.readOnly) {
            return {
                images: this.data.images || [],
                imagesPerRow: this.data.imagesPerRow,
            };
        }
        const imagesData = [];
        this.wrapper.querySelectorAll('img').forEach((img) => {
            if (img.dataset.url && img.style.display === 'block') {
                imagesData.push({
                    url: img.dataset.url,
                    id: img.dataset.id,
                    thumbnailUrl: img.getAttribute('src'),
                });
            } else {
                imagesData.push({});
            }
        });

        return {
            images: imagesData,
            imagesPerRow: this.data.imagesPerRow,
        };
    }
}
