import './styles.css';
import FileGalleryModal from './../../FileGalleryModal.js';

const LOADER_TIMEOUT = 500;

export default class XutimFileTool {
    static get toolbox() {
        return {
            title: 'File',
            icon: '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-paperclip"><path d="M15 7l-6.5 6.5a1.5 1.5 0 0 0 3 3l6.5 -6.5a3 3 0 0 0 -6 -6l-6.5 6.5a4.5 4.5 0 0 0 9 9l6.5 -6.5" /></svg>',
        };
    }

    static get isReadOnlySupported() {
        return true;
    }

    /**
     * Tool's CSS classes
     *
     * @returns {object}
     */
    get CSS() {
        return {
            baseClass: this.api.styles.block,
            apiButton: this.api.styles.button,
            loader: this.api.styles.loader,
            /**
             * Tool's classes
             */
            wrapper: 'cdx-attaches',
            wrapperWithFile: 'cdx-attaches--with-file',
            wrapperLoading: 'cdx-attaches--loading',
            button: 'cdx-attaches__button',
            title: 'cdx-attaches__title',
            size: 'cdx-attaches__size',
            downloadButton: 'cdx-attaches__download-button',
            fileInfo: 'cdx-attaches__file-info',
            fileIcon: 'cdx-attaches__file-icon',
            fileIconBackground: 'cdx-attaches__file-icon-background',
            fileIconLabel: 'cdx-attaches__file-icon-label',
        };
    }

    /**
     * Possible files' extension colors
     *
     * @returns {object}
     */
    get EXTENSIONS() {
        return {
            doc: '#1483E9',
            docx: '#1483E9',
            odt: '#1483E9',
            pdf: '#DB2F2F',
            rtf: '#744FDC',
            tex: '#5a5a5b',
            txt: '#5a5a5b',
            pptx: '#E35200',
            ppt: '#E35200',
            mp3: '#eab456',
            mp4: '#f676a6',
            xls: '#11AE3D',
            png: '#AA2284',
            jpg: '#D13359',
            jpeg: '#D13359',
            gif: '#f6af76',
            zip: '#4f566f',
            rar: '#4f566f',
            svg: '#bf5252',
            json: '#2988f0',
            csv: '#11AE3D',
        };
    }

    constructor({ data, config, api, block, readOnly }) {
        this.data = data || {};
        this.config = {
            buttonText: config.buttonText || 'Select file to upload',
            fetchFilesUrl: config.fetchFilesUrl || '',
            fetchFileUrl: config.fetchFileUrl || '',
        };
        this.api = api;
        this.block = block;
        this.nodes = {
            wrapper: null,
            button: null,
            title: null,
            modal: null,
        };
        this.readOnly = !!readOnly;
    }

    render() {
        const holder = document.createElement('div');
        holder.classList.add(this.CSS.baseClass);

        this.nodes.wrapper = document.createElement('div');
        this.nodes.wrapper.classList.add(this.CSS.wrapper);
        this.nodes.wrapper.style.cursor = 'pointer';

        if (this.data.file && this.data.file.id) {
            const fileId = this.data.file.id;

            const url = this.config.fetchFileUrl.replace(
                ':id:',
                encodeURIComponent(fileId),
            );

            fetch(url)
                .then((res) => {
                    if (!res.ok) throw new Error(`file ${fileId} not found`);
                    return res.json();
                })
                .then((file) => {
                    Object.assign(this.data.file, file);
                    this.#showFile(file);
                })
                .catch(() => {
                    this.api.notifier.show({
                        message: 'File not found.',
                        style: 'error',
                    });
                    this.#removeLoader();
                });
        } else {
            this.#prepareUploadButton();
        }

        holder.appendChild(this.nodes.wrapper);

        if (!this.readOnly) {
            this.nodes.wrapper.addEventListener('click', (_) => {
                this.openImageEditor();
            });
        }

        return holder;
    }
    #removeLoader() {
        setTimeout(
            () =>
                this.nodes.wrapper.classList.remove(
                    this.CSS.wrapperLoading,
                    this.CSS.loader,
                ),
            LOADER_TIMEOUT,
        );
    }
    #prepareUploadButton() {
        this.nodes.button = document.createElement('div');
        this.nodes.button.classList.add(this.CSS.apiButton);
        this.nodes.button.classList.add(this.CSS.button);
        this.nodes.button.innerHTML = `${XutimFileTool.toolbox.icon} ${this.config.buttonText}`;

        if (!this.readOnly) {
            this.nodes.button.addEventListener('click', (e) => {
                e.stopPropagation();
                this.openImageEditor();
            });
        }

        this.nodes.wrapper.appendChild(this.nodes.button);
    }

    appendFileIcon(file) {
        const extensionProvided = file.extension;
        const extension =
            extensionProvided || getExtensionFromFileName(file.name);
        const extensionColor = this.EXTENSIONS[extension];

        const wrapper = document.createElement('div');
        wrapper.classList.add(this.CSS.fileIcon);

        const background = document.createElement('div');
        background.classList.add(this.CSS.fileIconBackground);

        if (extensionColor) {
            background.style.backgroundColor = extensionColor;
        }

        wrapper.appendChild(background);

        if (extension) {
            let extensionVisible = extension;
            if (extension.length > 4) {
                extensionVisible = extension.substring(0, 4) + '…';
            }

            const extensionLabel = document.createElement('div');
            extensionLabel.classList.add(this.CSS.fileIconLabel);
            extensionLabel.textContent = extensionVisible;
            extensionLabel.title = extension;
            if (extensionColor) {
                extensionLabel.style.backgroundColor = extensionColor;
            }

            background.appendChild(extensionLabel);
        } else {
            background.innerHTML = IconFile;
        }

        this.nodes.wrapper.appendChild(wrapper);
    }

    openImageEditor() {
        if (this.readOnly) {
            return;
        }
        if (!this.nodes.modal) {
            this.nodes.modal = new FileGalleryModal({
                galleryUrl: this.config.fetchFilesUrl,
                onSelect: (file) => {
                    this.#selectFile(file);
                },
            });
        }

        this.nodes.modal.show();
    }

    #selectFile(file) {
        this.data.file = {
            id: file.id,
            url: file.url,
        };

        this.#showFile(file);

        this.api.blocks.update(this.block.id, this.data);
        this.closeModal();
    }

    #showFile(file) {
        this.nodes.wrapper.classList.add(this.CSS.wrapperWithFile);
        this.nodes.wrapper.innerHTML = '';
        this.nodes.wrapper.dataset.id = file.id;
        this.nodes.wrapper.dataset.url = file.url;
        this.appendFileIcon(file);

        const fileInfo = document.createElement('div');
        fileInfo.classList.add(this.CSS.fileInfo);

        this.nodes.title = document.createElement('div');
        this.nodes.title.classList.add(this.CSS.title);
        this.nodes.title.textContent = file.name || '';
        fileInfo.appendChild(this.nodes.title);

        if (file.size) {
            let sizePrefix;
            let formattedSize;
            const fileSize = document.createElement('div');
            fileSize.classList.add(this.CSS.size);

            if (Math.log10(+file.size) >= 6) {
                sizePrefix = 'MiB';
                formattedSize = file.size / Math.pow(2, 20);
            } else {
                sizePrefix = 'KiB';
                formattedSize = file.size / Math.pow(2, 10);
            }

            fileSize.textContent = formattedSize.toFixed(1);
            fileSize.setAttribute('data-size', sizePrefix);
            fileInfo.appendChild(fileSize);
        }

        this.nodes.wrapper.appendChild(fileInfo);

        if (file.url !== undefined) {
            const downloadIcon = document.createElement('a');
            downloadIcon.classList.add(this.CSS.downloadButton);
            downloadIcon.innerHTML =
                '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-down"><path d="M6 9l6 6l6 -6" /></svg>';
            downloadIcon.href = file.url;
            downloadIcon.target = '_blank';
            downloadIcon.rel = 'nofollow noindex noreferrer';

            this.nodes.wrapper.appendChild(downloadIcon);
        }
    }

    closeModal() {
        if (this.nodes.modal) {
            this.nodes.modal.close();
        }
    }

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
        let data = {};
        if (this.nodes.wrapper.dataset.id) {
            data = {
                url: this.nodes.wrapper.dataset.url,
                id: this.nodes.wrapper.dataset.id,
            };
        }

        return {
            file: data,
        };
    }
}
