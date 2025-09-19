export default class FileGalleryModal {
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

        this.breadcrumb = document.createElement('div');
        this.breadcrumb.className = 'px-3 py-2 border-top bg-light small';
        this.breadcrumb.textContent = 'Loading folders...';

        const searchWrapper = document.createElement('div');
        searchWrapper.className = 'p-3 border-top';

        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search files...';
        searchInput.className = 'form-control';
        searchInput.setAttribute('aria-label', 'Search files');

        searchInput.addEventListener(
            'input',
            this.#debounce((e) => {
                /**
                 * @type {Map<number, { items: Array, totalPages: number }>}
                 */
                this.cache = new Map();
                this.pagination.query = e.target.value;
                this.pagination.currentPage = 1;
                this.#loadGalleryFiles();
            }, 500),
        );
        searchWrapper.appendChild(searchInput);

        this.gallery = document.createElement('div');
        this.gallery.id = 'media-gallery-container';
        this.gallery.className = 'overflow-auto';

        this.folderBar = document.createElement('div');
        this.folderBar.className = 'p-2 d-flex gap-2';
        this.folderBar.style.overflowX = 'auto';

        this.fileTable = document.createElement('table');
        this.fileTable.className =
            'table table-hover table-sm card-table p-0 border-top';
        this.fileTable.innerHTML = `
            <thead class="bg-white">
              <tr>
                <th style="width:36px;"></th>
                <th>Name</th>
                <th style="width:120px;">Size</th>
              </tr>
            </thead>
            <tbody></tbody>
        `;
        this.fileTbody = this.fileTable.querySelector('tbody');

        this.paginationControls = document.createElement('div');
        this.paginationControls.id = 'pagination-controls';
        this.paginationControls.className =
            'd-flex justify-content-between align-items-center p-3 border-top';

        this.gallery.append(this.folderBar, this.fileTable);

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

    #loadGalleryFiles() {
        const page = this.pagination.currentPage;

        const renderContents = (data) => {
            // folders (chips)
            this.folderBar.innerHTML = '';
            const path = data.folderPath || [];
            const parentId = path.length >= 2 ? path[path.length - 2].id : null;

            if (this.pagination.folder !== null) {
                const back = document.createElement('button');
                back.type = 'button';
                back.className =
                    'btn btn-outline d-inline-flex align-items-center';
                back.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                      viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      class="me-2"><path d="M9 14l-4 -4l4 -4"/><path d="M5 10h11a4 4 0 1 1 0 8h-1"/></svg>
                    <span>Back</span>
                `;
                back.addEventListener('click', () => {
                    this.pagination.folder = parentId;
                    this.pagination.currentPage = 1;
                    this.cache.clear();
                    this.#loadGalleryFiles();
                });
                this.folderBar.appendChild(back);
            }

            (data.folders ?? []).forEach((folder) => {
                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className =
                    'btn btn-outline d-inline-flex align-items-center';
                chip.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                      viewBox="0 0 24 24" fill="none" stroke="currentColor"
                      stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                      class="me-2"><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"/></svg>
                    <span class="text-truncate" style="max-width:12rem">${folder.name}</span>
                `;
                chip.addEventListener('click', () => {
                    this.pagination.folder = folder.id;
                    this.pagination.currentPage = 1;
                    this.cache.clear();
                    this.#loadGalleryFiles();
                });
                this.folderBar.appendChild(chip);
            });

            this.fileTbody.innerHTML = '';
            (data.items ?? []).slice(0, 12).forEach((file) => {
                const tr = document.createElement('tr');
                tr.className = 'cursor-pointer';
                tr.innerHTML = `
                    <td class="text-center">${this.#iconFor(file.extension)}</td>
                    <td class="text-truncate" title="${file.name}">${file.name}</td>
                    <td>${this.#formatBytes(file.size)}</td>
                `;
                tr.addEventListener('click', () => {
                    this.onSelect(file);
                    this.close();
                });
                this.fileTbody.appendChild(tr);
            });

            // pagination state
            this.pagination.totalPages = data.totalPages || 1;
            this.prevButton.disabled = this.pagination.currentPage <= 1;
            this.nextButton.disabled =
                this.pagination.currentPage >= this.pagination.totalPages;

            this.#renderBreadcrumb(path);
        };

        const loadPage = (pageToLoad) => {
            if (this.cache.has(pageToLoad)) {
                return Promise.resolve(this.cache.get(pageToLoad));
            }
            const url = new URL(this.galleryUrl, window.location.origin);
            if (this.pagination.query)
                url.searchParams.append('searchTerm', this.pagination.query);
            if (this.pagination.folder)
                url.searchParams.set('folderId', this.pagination.folder);
            url.searchParams.set('page', pageToLoad);
            url.searchParams.set('pageLength', this.pagination.limit);

            return fetch(url.toString())
                .then((r) => r.json())
                .then((data) => {
                    data.items = Array.isArray(data.items) ? data.items : [];
                    data.folders = Array.isArray(data.folders)
                        ? data.folders
                        : [];
                    data.folderPath = Array.isArray(data.folderPath)
                        ? data.folderPath
                        : [];
                    this.cache.set(pageToLoad, data);
                    return data;
                });
        };

        this.fileTbody.innerHTML = `<tr><td colspan="4" class="text-secondary py-4">Loading…</td></tr>`;

        loadPage(page)
            .then((data) => {
                renderContents(data);
                if (page > 1) loadPage(page - 1).catch(() => {});
                if (page < this.pagination.totalPages)
                    loadPage(page + 1).catch(() => {});
            })
            .catch(() => {
                this.fileTbody.innerHTML = `<tr><td colspan="4" class="text-danger py-4">Error loading files.</td></tr>`;
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
            this.#loadGalleryFiles();
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
                    this.#loadGalleryFiles();
                });
                this.breadcrumb.appendChild(link);
            }
        });
    }
    #formatBytes(bytes) {
        if (bytes == null) return '–';
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        let i = 0,
            n = Number(bytes);
        while (n >= 1024 && i < units.length - 1) {
            n /= 1024;
            i++;
        }
        return `${n.toFixed(n < 10 && i > 0 ? 1 : 0)} ${units[i]}`;
    }

    #iconFor(ext) {
        const e = (ext || '').toLowerCase();

        if (e === 'pdf') {
            return `
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-type-pdf"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" /><path d="M5 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" /><path d="M17 18h2" /><path d="M20 15h-3v6" /><path d="M11 15v6h1a2 2 0 0 0 2 -2v-2a2 2 0 0 0 -2 -2h-1z" /></svg>
            `;
        }

        if (e === 'doc' || e === 'odt') {
            return `
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-file-type-doc" >
                <path stroke="none" d="M0 0h24v24H0z" fill="none" /> <path d="M14 3v4a1 1 0 0 0 1 1h4" /> <path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" /> <path d="M5 15v6h1a2 2 0 0 0 2 -2v-2a2 2 0 0 0 -2 -2h-1z" /> <path d="M20 16.5a1.5 1.5 0 0 0 -3 0v3a1.5 1.5 0 0 0 3 0" /> <path d="M12.5 15a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1 -3 0v-3a1.5 1.5 0 0 1 1.5 -1.5z" /> 
                </svg>;
            `;
        }
        if (e === 'docx') {
            return `
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-type-docx"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" /><path d="M2 15v6h1a2 2 0 0 0 2 -2v-2a2 2 0 0 0 -2 -2h-1z" /><path d="M17 16.5a1.5 1.5 0 0 0 -3 0v3a1.5 1.5 0 0 0 3 0" /><path d="M9.5 15a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1 -3 0v-3a1.5 1.5 0 0 1 1.5 -1.5z" /><path d="M19.5 15l3 6" /><path d="M19.5 21l3 -6" /></svg>
            `;
        }
        if (e === 'xls' || e === 'xlsx' || e === 'ods') {
            return `
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-type-xls"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" /><path d="M4 15l4 6" /><path d="M4 21l4 -6" /><path d="M17 20.25c0 .414 .336 .75 .75 .75h1.25a1 1 0 0 0 1 -1v-1a1 1 0 0 0 -1 -1h-1a1 1 0 0 1 -1 -1v-1a1 1 0 0 1 1 -1h1.25a.75 .75 0 0 1 .75 .75" /><path d="M11 15v6h3" /></svg>
            `;
        }
        if (e === 'zip') {
            return `
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-type-zip"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 12v-7a2 2 0 0 1 2 -2h7l5 5v4" /><path d="M16 18h1.5a1.5 1.5 0 0 0 0 -3h-1.5v6" /><path d="M12 15v6" /><path d="M5 15h3l-3 6h3" /></svg>
            `;
        }
        if (e === 'mp3') {
            return `
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file-music"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /><path d="M11 16m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M12 16l0 -5l2 1" /></svg>
            `;
        }

        if (e === 'mp4') {
            return `
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-video"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 10l4.553 -2.276a1 1 0 0 1 1.447 .894v6.764a1 1 0 0 1 -1.447 .894l-4.553 -2.276v-4z" /><path d="M3 6m0 2a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-8a2 2 0 0 1 -2 -2z" /></svg>
            `;
        }

        return `
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20"
              viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
              <path d="M14 3v4a1 1 0 0 0 1 1h4" />
              <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
            </svg>`;
    }
}
