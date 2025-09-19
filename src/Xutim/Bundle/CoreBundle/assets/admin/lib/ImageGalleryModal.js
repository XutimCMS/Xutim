export default class ImageGalleryModal {
    constructor({ galleryUrl, onSelect }) {
        this.galleryUrl = galleryUrl;
        this.onSelect = onSelect;
        this.pagination = {
            currentPage: 1,
            totalPages: 1,
            limit: 12,
            query: '',
            folder: null,
        };

        /**
         * @type {Map<number, { items: Array, totalPages: number }>}
         */
        this.cache = new Map();
        this.dialog = this.#createModal();
        document.body.appendChild(this.dialog);
    }

    show() {
        this.#loadGalleryImages();
        this.dialog.showModal();
    }

    close() {
        this.dialog.close();
    }

    #createModal() {
        const modal = document.createElement('div');
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
        dialog.className = 'shadow-lg dialog-md d-block';
        dialog.style.border = 'none';
        dialog.style.background = 'transparent';

        const modalDialog = document.createElement('div');
        modalDialog.className = 'modal-dialog modal-dialog-centered';

        const modalContent = document.createElement('div');
        modalContent.className =
            'modal-content h-100 d-flex flex-column shadow bg-white';

        const header = document.createElement('div');
        header.className = 'modal-header';
        const modalTitle = document.createElement('h3');
        modalTitle.className = 'modal-title m-0 fs-4';
        modalTitle.textContent = 'Select Image from Gallery';
        header.appendChild(modalTitle);

        this.breadcrumb = document.createElement('div');
        this.breadcrumb.className = 'px-3 py-2 border-top bg-light small';
        this.breadcrumb.textContent = 'Loading folders...';

        const searchWrapper = document.createElement('div');
        searchWrapper.className = 'p-3 border-top';

        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search images...';
        searchInput.className = 'form-control';
        searchInput.setAttribute('aria-label', 'Search images');

        searchInput.addEventListener(
            'input',
            this.#debounce((e) => {
                /**
                 * @type {Map<number, { items: Array, totalPages: number }>}
                 */
                this.cache = new Map();
                this.pagination.query = e.target.value;
                this.pagination.currentPage = 1;
                this.#loadGalleryImages();
            }, 500),
        );
        searchWrapper.appendChild(searchInput);

        this.gallery = document.createElement('div');
        this.gallery.id = 'media-gallery-container';
        this.gallery.className = 'p-3 overflow-auto d-grid';
        this.gallery.style.gridTemplateColumns = 'repeat(4, 1fr)';
        this.gallery.style.gap = '1rem';

        this.folderBar = document.createElement('div');
        this.folderBar.className = 'py-3 px-2 d-flex gap-2';
        this.folderBar.style.overflowX = 'auto';

        this.paginationControls = document.createElement('div');
        this.paginationControls.id = 'pagination-controls';
        this.paginationControls.className =
            'd-flex justify-content-between align-items-center p-3 border-top';

        this.prevButton = document.createElement('button');
        this.prevButton.textContent = '←';
        this.prevButton.className = 'btn btn-outline-primary';
        this.prevButton.disabled = true;
        this.prevButton.addEventListener('click', () => {
            if (this.pagination.currentPage > 1) {
                this.pagination.currentPage--;
                this.#loadGalleryImages();
            }
        });

        this.nextButton = document.createElement('button');
        this.nextButton.textContent = '→';
        this.nextButton.className = 'btn btn-primary';
        this.nextButton.disabled = true;
        this.nextButton.addEventListener('click', () => {
            if (this.pagination.currentPage < this.pagination.totalPages) {
                this.pagination.currentPage++;
                this.#loadGalleryImages();
            }
        });

        this.paginationControls.append(this.prevButton, this.nextButton);
        modalContent.append(
            header,
            this.breadcrumb,
            this.folderBar,
            searchWrapper,
            this.gallery,
            this.paginationControls,
        );
        modalDialog.appendChild(modalContent);
        dialog.appendChild(modalDialog);
        modal.appendChild(dialog);

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                this.close();
            }
        });

        return dialog;
    }

    #loadGalleryImages() {
        const page = this.pagination.currentPage;

        const renderImages = (data) => {
            this.gallery.innerHTML = '';
            data.items.forEach((image) => {
                const img = document.createElement('img');
                img.src = image.filteredUrl;
                img.dataset.id = image.id;
                img.dataset.url = image.fullSourceUrl;
                img.dataset.thumbnailUrl = image.filteredUrl;
                img.style.width = '100%';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                img.style.cursor = 'pointer';
                img.className = 'rounded';
                img.addEventListener('click', () => {
                    this.onSelect(image);
                    this.close();
                });
                this.gallery.appendChild(img);
            });

            this.pagination.totalPages = data.totalPages || 1;
            this.prevButton.disabled = page <= 1;
            this.nextButton.disabled = page >= this.pagination.totalPages;
        };
        const renderContents = (data) => {
            this.gallery.innerHTML = '';
            this.folderBar.innerHTML = '';

            const path = data.folderPath || [];
            const parentId = path.length >= 2 ? path[path.length - 2].id : null;
            if (this.pagination.folder !== null) {
                const back = document.createElement('button');
                back.type = 'button';
                back.className =
                    'btn btn-outline d-inline-flex align-items-center p-0 ps-2';
                back.style.flex = '0 0 auto';
                back.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M9 14l-4 -4l4 -4"/><path d="M5 10h11a4 4 0 1 1 0 8h-1"/></svg>`;
                back.addEventListener('click', () => {
                    this.pagination.folder = parentId;
                    this.pagination.currentPage = 1;
                    this.cache.clear();
                    this.#loadGalleryImages();
                });
                this.folderBar.appendChild(back);
            }

            (data.folders ?? []).forEach((folder) => {
                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className =
                    'btn btn-outline d-inline-flex align-items-center';
                chip.style.flex = '0 0 auto';
                chip.innerHTML = `
                    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-folder"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2" /></svg>
                    <span class="text-truncate" style="max-width:12rem">${folder.name}</span>
                `;
                chip.addEventListener('click', () => {
                    this.pagination.folder = folder.id;
                    this.pagination.currentPage = 1;
                    this.cache.clear();
                    this.#loadGalleryImages();
                });
                this.folderBar.appendChild(chip);
            });

            data.items.forEach((image) => {
                const img = document.createElement('img');
                img.src = image.filteredUrl;
                img.dataset.id = image.id;
                img.dataset.url = image.fullSourceUrl;
                img.dataset.thumbnailUrl = image.filteredUrl;
                img.style.width = '100%';
                img.style.height = '100px';
                img.style.objectFit = 'cover';
                img.style.cursor = 'pointer';
                img.className = 'rounded';
                img.addEventListener('click', () => {
                    this.onSelect(image);
                    this.close();
                });
                this.gallery.appendChild(img);
            });

            this.pagination.totalPages = data.totalPages || 1;
            this.prevButton.disabled = page <= 1;
            this.nextButton.disabled = page >= this.pagination.totalPages;

            this.#renderBreadcrumb(data.folderPath || []);
        };

        const loadPage = (pageToLoad) => {
            if (this.cache.has(pageToLoad)) {
                return Promise.resolve(this.cache.get(pageToLoad));
            }

            const url = new URL(this.galleryUrl, window.location.origin);
            if (this.pagination.query) {
                url.searchParams.append('searchTerm', this.pagination.query);
            }
            if (this.pagination.folder) {
                url.searchParams.set('folderId', this.pagination.folder);
            }
            url.searchParams.set('page', pageToLoad);
            url.searchParams.set('pageLength', this.pagination.limit);

            return fetch(url.toString())
                .then((response) => response.json())
                .then((data) => {
                    if (!Array.isArray(data.items))
                        throw new Error('Invalid data');
                    this.cache.set(pageToLoad, data);
                    return data;
                });
        };

        this.gallery.innerHTML = 'Loading...';

        loadPage(page)
            .then((data) => {
                renderContents(data);

                // Preload previous and next pages (non-blocking)
                if (page > 1) loadPage(page - 1).catch(() => {});
                if (page < this.pagination.totalPages)
                    loadPage(page + 1).catch(() => {});
            })
            .catch(() => {
                this.gallery.textContent = 'Error loading gallery images.';
            });
    }

    #debounce(fn, delay) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn(...args), delay);
        };
    }
    #renderBreadcrumb(path) {
        this.breadcrumb.innerHTML = '';

        const rootLink = document.createElement('a');
        rootLink.href = '#';
        rootLink.textContent = 'Media';
        rootLink.addEventListener('click', (e) => {
            e.preventDefault();
            this.pagination.folder = null;
            this.pagination.currentPage = 1;
            this.cache = new Map();
            this.#loadGalleryImages();
        });
        this.breadcrumb.appendChild(rootLink);

        path.forEach((folder, index) => {
            this.breadcrumb.append(' / ');
            if (index === path.length - 1) {
                this.breadcrumb.append(folder.name);
            } else {
                const link = document.createElement('a');
                link.href = '#';
                link.textContent = folder.name;
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.pagination.folder = folder.id;
                    this.pagination.currentPage = 1;
                    this.cache = new Map();
                    this.#loadGalleryImages();
                });
                this.breadcrumb.appendChild(link);
            }
        });
    }
}
