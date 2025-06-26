import { Controller } from '@hotwired/stimulus';
import EditorJS from 'https://esm.sh/@editorjs/editorjs@2.31.0-rc.10';
import Header from '@editorjs/header';
import Paragraph from '@editorjs/paragraph';
import MainHeader from '../lib/editorjs-plugins/header/main_header.js';
import Quote from '@editorjs/quote';
import List from '@editorjs/list';
import Block from './../lib/editorjs-plugins/block/block.js';
import createContentLink from './../lib/editorjs-plugins/content-link/content-link.js';
import Delimiter from '@editorjs/delimiter';
import Embed from '@editorjs/embed';
import ImageRowTool from '../lib/editorjs-plugins/image-row/ImageRowTool.js';
import XutimImageTool from '../lib/editorjs-plugins/image/XutimImageTool.js';
import XutimFileTool from '../lib/editorjs-plugins/file/XutimFileTool.js';
import AlignmentBlockTune from '../lib/editorjs-plugins/alignment-tune/AlignmentBlockTune.js';
import XutimTagListTool from '../lib/editorjs-plugins/tag-list/XutimTagListTool.js';
import XutimAnchorTune from '../lib/editorjs-plugins/anchor-tune/XutimAnchorTune.js';
//import createInternalLink from '../lib/editorjs-plugins/internal-inline-link/XutimInternalLinkInlineTool.js';

export default class extends Controller {
    static targets = ['editorHolder', 'contentInput'];
    static values = {
        blockCodes: Array,
        tags: Array,
        pageIdsUrl: String,
        articleIdsUrl: String,
        fetchImagesUrl: String,
        fetchFilesUrl: String,
        fetchFileUrl: String,
        fetchAnchorSnippetsUrl: String,
        disableEditing: Boolean,
    };

    #editor;

    connect() {
        if (this.disableEditingValue === true) {
            this.element.classList.add('editorjs-no-permission');
        }
        const pageLinkIcon =
            '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder" stroke="none" width="24" height="24" viewBox="0 0 24 24" stroke-width="0" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path></svg>';
        const articleLinkIcon =
            '<span><svg xmlns="http://www.w3.org/2000/svg"  width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="none"  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-news"><path d="M16 6h3a1 1 0 0 1 1 1v11a2 2 0 0 1 -4 0v-13a1 1 0 0 0 -1 -1h-10a1 1 0 0 0 -1 1v12a3 3 0 0 0 3 3h11" /><path d="M8 8l4 0" /><path d="M8 12l4 0" /><path d="M8 16l4 0" /></svg></span>';

        this.#editor = new EditorJS({
            holder: this.editorHolderTarget,
            placeholder: 'Start writing or type / to choose a block',
            tools: {
                alignment: {
                    class: AlignmentBlockTune,
                },
                xutimAnchor: {
                    class: XutimAnchorTune,
                    config: {
                        snippetListUrl: this.fetchAnchorSnippetsUrlValue,
                    },
                },
                // xutimInternalLink: {
                //     class: createInternalLink('Page link', pageLinkIcon),
                //     config: {
                //         listUrl: this.pageIdsUrlValue,
                //         title: 'Select a page',
                //     },
                //     shortcut: 'CMD+SHIFT+K',
                // },
                paragraph: {
                    class: Paragraph,
                    tunes: ['xutimAnchor', 'alignment'],
                },
                header: {
                    class: Header,
                    config: {
                        placeholder: 'Enter a header',
                        levels: [2, 3, 4],
                        defaultLevel: 2,
                    },
                    tunes: ['xutimAnchor', 'alignment'],
                },
                mainHeader: {
                    class: MainHeader,
                    name: 'Main header',
                    config: {
                        placeholder: 'Enter a header',
                        levels: [1, 2, 3],
                        defaultLevel: 2,
                    },
                    tunes: ['xutimAnchor'],
                },
                quote: {
                    class: Quote,
                    inlineToolbar: true,
                    config: {
                        quotePlaceholder: 'Enter a quote',
                        captionPlaceholder: "Quote's author",
                    },
                    tunes: ['xutimAnchor'],
                },
                list: {
                    class: List,
                    inlineToolbar: true,
                    config: {
                        defaultStyle: 'unordered',
                    },
                    tunes: ['xutimAnchor'],
                },
                delimiter: {
                    class: Delimiter,
                    tunes: ['xutimAnchor'],
                },
                embed: {
                    class: Embed,
                    inlineToolbar: true,
                    config: {
                        services: {
                            youtube: true,
                            instagram: true,
                            facebook: true,
                            twitter: true,
                        },
                    },
                    tunes: ['xutimAnchor'],
                },
                xutimFile: {
                    class: XutimFileTool,
                    config: {
                        fetchFilesUrl: this.fetchFilesUrlValue,
                        fetchFileUrl: this.fetchFileUrlValue,
                    },
                    tunes: ['xutimAnchor'],
                },
                xutimImage: {
                    class: XutimImageTool,
                    config: {
                        galleryUrl: this.fetchImagesUrlValue,
                    },
                    tunes: ['xutimAnchor', 'alignment'],
                },
                imageRow: {
                    class: ImageRowTool,
                    config: {
                        galleryUrl: this.fetchImagesUrlValue,
                        allowedImagesPerRow: [2, 3, 4, 5],
                        defaultImagesPerRow: 3,
                    },
                    tunes: ['xutimAnchor'],
                },
                pageLink: {
                    class: createContentLink('Page link', pageLinkIcon),
                    config: {
                        listUrl: this.pageIdsUrlValue,
                        title: 'Select a page',
                    },
                    tunes: ['xutimAnchor'],
                },
                articleLink: {
                    class: createContentLink('Article link', articleLinkIcon),
                    config: {
                        listUrl: this.articleIdsUrlValue,
                        title: 'Select an article',
                    },
                    tunes: ['xutimAnchor'],
                },
                block: {
                    class: Block,
                    config: {
                        codes: this.blockCodesValue,
                    },
                    tunes: ['xutimAnchor'],
                },
                xutimTag: {
                    class: XutimTagListTool,
                    config: {
                        tags: this.tagsValue,
                    },
                    tunes: ['xutimAnchor'],
                },
            },
            data: JSON.parse(this.contentInputTarget.value),
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
                console.log('Saving failed: ', error);
            });
    }
}
