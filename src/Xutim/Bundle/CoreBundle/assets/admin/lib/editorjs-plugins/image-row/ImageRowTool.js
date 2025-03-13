export default class ImageRowTool {
    static get toolbox() {
        return {
            title: 'Images in a row',
            icon: '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-layout-collage"><path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" /><path d="M10 4l4 16" /><path d="M12 12l-8 2" /></svg>',
        };
    }

    constructor({ data, config, api, block }) {
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

        for (let i = 0; i < this.imagesPerRow; i++) {
            const imageContainer = document.createElement('div');
            imageContainer.style.flex = '1';
            imageContainer.style.position = 'relative';
            imageContainer.style.border = '1px dashed #ccc';
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
                img.src = this.data.images[i].url;
                img.style.display = 'block';
                placeholderText.style.display = 'none';
            }

            imageContainer.appendChild(img);

            imageContainer.addEventListener('click', (event) => {
                this.currentImageIndex = i;
                this.openImageEditor(i);
            });

            this.wrapper.appendChild(imageContainer);
        }
        return this.wrapper;
    }

    openImageEditor(index) {
        if (!this.modal) {
            this.modal = this.createModal();
        }
        this.currentImageIndex = index;
        this.modal.showModal();
    }

    createModal() {
        const modal = document.createElement('modal');
        modal.setAttribute('data-controller', 'modal');
        modal.setAttribute(
            'data-action',
            'turbo:before-cache@window->modal#close',
        );
        const dialog = document.createElement('dialog');
        dialog.setAttribute('data-modal-target', 'dialog');
        dialog.setAttribute(
            'data-action',
            'close->modal#close click->modal#clickOutside',
        );
        dialog.id = 'pulse-dialog';
        dialog.className = 'shadow-lg dialog-md';
        modal.appendChild(dialog);

        const modalContent = document.createElement('div');
        modalContent.style.backgroundColor = 'white';
        modalContent.style.padding = '20px';
        modalContent.style.borderRadius = '5px';
        modalContent.style.width = '100%';

        const modalTitle = document.createElement('h3');
        modalTitle.textContent = 'Select Image from Gallery';
        modalContent.appendChild(modalTitle);

        const gallery = document.createElement('div');
        gallery.style.display = 'grid';
        gallery.style.gridTemplateColumns = 'repeat(4, minmax(100px, 1fr))';
        gallery.style.gap = '10px';
        gallery.style.marginTop = '20px';
        gallery.style.maxHeight = '400px';
        gallery.style.overflowY = 'auto';

        if (this.galleryUrl === '') {
            console.error('No "galleryUrl" config.');
        }

        fetch(this.galleryUrl)
            .then((response) => response.json())
            .then((imageUrls) => {
                imageUrls.forEach((imageUrl) => {
                    const galleryImage = document.createElement('img');
                    galleryImage.src = imageUrl;
                    galleryImage.style.width = '100%';
                    galleryImage.style.height = '100px';
                    galleryImage.style.objectFit = 'cover';
                    galleryImage.style.cursor = 'pointer';
                    galleryImage.addEventListener('click', () =>
                        this.selectImage(imageUrl),
                    );
                    gallery.appendChild(galleryImage);
                });
            })
            .catch((error) => {
                console.error('Error fetching gallery images:', error);
                gallery.textContent = 'Error loading gallery images.';
            });

        modalContent.appendChild(gallery);

        const closeButton = document.createElement('button');
        closeButton.textContent = 'Close';
        closeButton.style.marginTop = '20px';
        closeButton.addEventListener('click', () => this.closeModal());
        modalContent.appendChild(closeButton);

        dialog.appendChild(modalContent);

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                this.closeModal();
            }
        });
        document.body.appendChild(dialog);
        return dialog;
    }

    selectImage(imageUrl) {
        if (this.currentImageIndex !== undefined) {
            this.data.images[this.currentImageIndex] = { url: imageUrl };
            const imgElement =
                this.wrapper.querySelectorAll('img')[this.currentImageIndex];
            const placeholderText =
                this.wrapper.querySelectorAll('span')[this.currentImageIndex];

            imgElement.src = imageUrl;
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
        const imagesData = [];
        this.wrapper.querySelectorAll('img').forEach((img) => {
            if (img.src && img.style.display === 'block') {
                imagesData.push({ url: img.src });
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
