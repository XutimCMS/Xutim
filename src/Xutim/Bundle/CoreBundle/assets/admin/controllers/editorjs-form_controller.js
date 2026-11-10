import { Controller } from '@hotwired/stimulus';
import EditorJS from 'https://esm.sh/@editorjs/editorjs@2.31.0-rc.10';
import { buildEditorTools } from '../lib/build_tools.js';
import { decorateInternalLinks } from '../lib/editorjs-plugins/internal-inline-link/internal-inline-link.js';

export default class extends Controller {
    static targets = ['editorHolder', 'contentInput', 'saveAction', 'saveDialog', 'saveChoice'];
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
        xutimSections: Array,
        xutimSectionFormUrl: String,
        xutimSectionSaveUrl: String,
        xutimSectionRefreshUrl: String,
        xutimSectionPreviewUrl: String,
    };

    #editor;

    connect() {
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
            xutimSections: this.xutimSectionsValue,
            xutimSectionFormUrl: this.xutimSectionFormUrlValue,
            xutimSectionSaveUrl: this.xutimSectionSaveUrlValue,
            xutimSectionRefreshUrl: this.xutimSectionRefreshUrlValue,
            xutimSectionPreviewUrl: this.xutimSectionPreviewUrlValue,
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

    openSaveDialog() {
        this.saveDialogTarget.showModal();
    }

    closeSaveDialog() {
        this.saveDialogTarget.close();
    }

    save(event) {
        this.saveChoiceTargets.forEach((button) => (button.disabled = true));
        if (this.hasSaveActionTarget) {
            this.saveActionTarget.value = event.params.action;
        }

        this.#editor
            .save()
            .then((outputData) => {
                this.contentInputTarget.value = JSON.stringify(outputData);
                this.element.dispatchEvent(new CustomEvent('unsaved-changes:clear', { bubbles: true }));
                this.element.submit();
            })
            .catch((error) => {
                this.saveChoiceTargets.forEach((button) => (button.disabled = false));
                console.warn('Saving failed: ', error);
            });
    }
}
