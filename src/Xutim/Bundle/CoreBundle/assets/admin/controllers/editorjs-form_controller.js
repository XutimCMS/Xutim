import { Controller } from '@hotwired/stimulus';
import EditorJS from 'https://esm.sh/@editorjs/editorjs@2.31.0-rc.10';
import { buildEditorTools } from '../lib/build_tools.js';
import { decorateInternalLinks } from '../lib/editorjs-plugins/internal-inline-link/XutimInternalLinkInlineTool.js';

export default class extends Controller {
    static targets = ['editorHolder', 'contentInput'];
    static values = {
        blockCodes: Array,
        tags: Array,
        pageIdsUrl: String,
        articleIdsUrl: String,
        tagIdsUrl: String,
        fetchImagesUrl: String,
        fetchFilesUrl: String,
        fetchAllFilesUrl: String,
        fetchFileUrl: String,
        fetchAnchorSnippetsUrl: String,
        disableEditing: Boolean,
    };

    #editor;

    connect() {
        if (this.disableEditingValue === true) {
            this.element.classList.add('editorjs-no-permission');
        }

        const tools = buildEditorTools({
            pageIdsUrl: this.pageIdsUrlValue,
            articleIdsUrl: this.articleIdsUrlValue,
            tagIdsUrl: this.tagIdsUrlValue,
            fetchImagesUrl: this.fetchImagesUrlValue,
            fetchFilesUrl: this.fetchFilesUrlValue,
            fetchAllFilesUrl: this.fetchAllFilesUrlValue,
            fetchFileUrl: this.fetchFileUrlValue,
            fetchAnchorSnippetsUrl: this.fetchAnchorSnippetsUrlValue,
            blockCodes: this.blockCodesValue,
            tags: this.tagsValue,
        });

        this.#editor = new EditorJS({
            holder: this.editorHolderTarget,
            placeholder: 'Start writing or type / to choose a block',
            tools: tools,
            data: JSON.parse(this.contentInputTarget.value),
            onReady: () => {
                decorateInternalLinks(this.editorHolderTarget);
            },
        });
    }

    save(event) {
        this.#editor
            .save()
            .then((outputData) => {
                this.contentInputTarget.value = JSON.stringify(outputData);
                this.element.submit();
            })
            .catch((error) => {
                console.warn('Saving failed: ', error);
            });
    }
}
