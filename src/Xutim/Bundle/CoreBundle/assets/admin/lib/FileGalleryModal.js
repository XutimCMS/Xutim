export default class FileGalleryModal {
    constructor({ galleryUrl, onSelect }) {
        this.galleryUrl = galleryUrl;
        this.onSelect = onSelect;
        this.pagination = {
            currentPage: 1,
            totalPages: 1,
            limit: 12,
            query: '',
        };

        /**
         * @type {Map<number, { items: Array, totalPages: number }>}
         */
        this.cache = new Map();
        this.dialog = this.#createModal();
        document.body.appendChild(this.dialog);
    }

    show() {
        this.#loadGalleryFiles();
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
                this.cache = new Map();
                this.pagination.query = e.target.value;
                this.pagination.currentPage = 1;
                this.#loadGalleryFiles();
            }, 500),
        );
        searchWrapper.appendChild(searchInput);

        this.gallery = document.createElement('div');
        this.gallery.id = 'media-gallery-container';
        this.gallery.className = 'p-3 overflow-auto d-grid';
        this.gallery.style.gridTemplateColumns = 'repeat(4, 1fr)';
        this.gallery.style.gap = '1rem';

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
                this.#loadGalleryFiles();
            }
        });

        this.nextButton = document.createElement('button');
        this.nextButton.textContent = '→';
        this.nextButton.className = 'btn btn-primary';
        this.nextButton.disabled = true;
        this.nextButton.addEventListener('click', () => {
            if (this.pagination.currentPage < this.pagination.totalPages) {
                this.pagination.currentPage++;
                this.#loadGalleryFiles();
            }
        });

        this.paginationControls.append(this.prevButton, this.nextButton);
        modalContent.append(
            header,
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

    #loadGalleryFiles() {
        const page = this.pagination.currentPage;

        const renderFiles = (data) => {
            this.gallery.innerHTML = '';
            data.items.forEach((file) => {
                const div = document.createElement('div');
                div.innerHTML = file.name + ' (' + file.size + ')';
                div.dataset.id = file.id;
                div.style.cursor = 'pointer';
                div.className = 'rounded';
                div.addEventListener('click', () => {
                    this.onSelect(file);
                    this.close();
                });
                this.gallery.appendChild(div);
            });

            this.pagination.totalPages = data.totalPages || 1;
            this.prevButton.disabled = page <= 1;
            this.nextButton.disabled = page >= this.pagination.totalPages;
        };

        const loadPage = (pageToLoad) => {
            if (this.cache.has(pageToLoad)) {
                return Promise.resolve(this.cache.get(pageToLoad));
            }

            const url = new URL(this.galleryUrl, window.location.origin);
            if (this.pagination.query) {
                url.searchParams.append('searchTerm', this.pagination.query);
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
                renderFiles(data);

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
}
