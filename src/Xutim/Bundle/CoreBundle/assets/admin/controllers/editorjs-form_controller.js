import { Controller } from '@hotwired/stimulus';
import EditorJS from 'https://esm.sh/@editorjs/editorjs@2.31.0-rc.10';
import { buildEditorTools } from '../lib/build_tools.js';
import { decorateInternalLinks } from '../lib/editorjs-plugins/internal-inline-link/XutimInternalLinkInlineTool.js';

export default class extends Controller {
    static targets = ['editorHolder', 'contentInput', 'saveAction', 'primaryBtn'];
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
        publishLabel: String,
        draftLabel: String,
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

        if (this.hasPrimaryBtnTarget) {
            const action = localStorage.getItem('xutim.saveAction') || 'publish';
            this.primaryBtnTarget.textContent = this.#labelFor(action);
        }
    }

    save(event) {
        this.#editor
            .save()
            .then((outputData) => {
                this.contentInputTarget.value = JSON.stringify(outputData);
                if (this.hasSaveActionTarget) {
                    this.saveActionTarget.value = localStorage.getItem('xutim.saveAction') || 'publish';
                }
                this.element.submit();
            })
            .catch((error) => {
                console.warn('Saving failed: ', error);
            });
    }

    chooseSaveAction(event) {
        const action = event.params.action;
        localStorage.setItem('xutim.saveAction', action);
        this.primaryBtnTarget.textContent = this.#labelFor(action);
    }

    #labelFor(action) {
        return action === 'publish' ? this.publishLabelValue : this.draftLabelValue;
    }
}
