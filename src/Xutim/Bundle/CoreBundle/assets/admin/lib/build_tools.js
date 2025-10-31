// Centralized Editor.js tools builder used across multiple controllers
import Header from '@editorjs/header';
import Paragraph from '@editorjs/paragraph';
import Quote from '@editorjs/quote';
import List from '@editorjs/list';
import Delimiter from '@editorjs/delimiter';
import Embed from '@editorjs/embed';

import MainHeader from './editorjs-plugins/header/main_header.js';
import Block from './editorjs-plugins/block/block.js';
import ImageRowTool from './editorjs-plugins/image-row/ImageRowTool.js';
import XutimImageTool from './editorjs-plugins/image/XutimImageTool.js';
import XutimFileTool from './editorjs-plugins/file/XutimFileTool.js';
import AlignmentBlockTune from './editorjs-plugins/alignment-tune/AlignmentBlockTune.js';
import XutimTagListTool from './editorjs-plugins/tag-list/XutimTagListTool.js';
import XutimAnchorTune from './editorjs-plugins/anchor-tune/XutimAnchorTune.js';
import XutimFootnoteInline from './editorjs-plugins/footnotes-tune/XutimFootnoteInline.js';
import FoldableStart from './editorjs-plugins/foldable/FoldableStart.js';
import FoldableEnd from './editorjs-plugins/foldable/FoldableEnd.js';

import createContentLink from './editorjs-plugins/content-link/content-link.js';
import createInternalLink from './editorjs-plugins/internal-inline-link/XutimInternalLinkInlineTool.js';

const pageLinkIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-folder" stroke="none" width="24" height="24" viewBox="0 0 24 24" stroke-width="0" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h4l3 3h7a2 2 0 0 1 2 2v8a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2v-11a2 2 0 0 1 2 -2"></path></svg>';
const articleLinkIcon =
    '<span><svg xmlns="http://www.w3.org/2000/svg"  width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="none"  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-news"><path d="M16 6h3a1 1 0 0 1 1 1v11a2 2 0 0 1 -4 0v-13a1 1 0 0 0 -1 -1h-10a1 1 0 0 0 -1 1v12a3 3 0 0 0 3 3h11" /><path d="M8 8l4 0" /><path d="M8 12l4 0" /><path d="M8 16l4 0" /></svg></span>';
const tagLinkIcon =
    '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-tag"><path d="M7.5 7.5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M3 6v5.172a2 2 0 0 0 .586 1.414l7.71 7.71a2.41 2.41 0 0 0 3.408 0l5.592 -5.592a2.41 2.41 0 0 0 0 -3.408l-7.71 -7.71a2 2 0 0 0 -1.414 -.586h-5.172a3 3 0 0 0 -3 3z" /></svg>';
const fileLinkIcon =
    '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-file"><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" /></svg>';

export function buildEditorTools({
    pageIdsUrl,
    articleIdsUrl,
    tagIdsUrl,
    fetchImagesUrl,
    fetchFilesUrl,
    fetchAllFilesUrl,
    fetchFileUrl,
    fetchAnchorSnippetsUrl,
    blockCodes,
    tags,
}) {
    return {
        alignment: { class: AlignmentBlockTune },

        xutimAnchor: {
            class: XutimAnchorTune,
            config: { snippetListUrl: fetchAnchorSnippetsUrl },
        },

        xutimFootnote: { class: XutimFootnoteInline },

        xutimInternalPageLink: {
            class: createInternalLink('Page link', pageLinkIcon),
            config: {
                listUrl: pageIdsUrl,
                title: 'Select a page',
                type: 'page',
            },
            shortcut: 'CMD+SHIFT+K',
        },

        xutimInternalArticleLink: {
            class: createInternalLink('Article link', articleLinkIcon),
            config: {
                listUrl: articleIdsUrl,
                title: 'Select an article',
                type: 'article',
            },
            shortcut: 'CMD+SHIFT+L',
        },

        xutimInternalTagLink: {
            class: createInternalLink('Tag link', tagLinkIcon),
            config: { listUrl: tagIdsUrl, title: 'Select a tag', type: 'tag' },
            shortcut: 'CMD+SHIFT+J',
        },

        xutimInternalFileLink: {
            class: createInternalLink('File link', fileLinkIcon),
            config: {
                listUrl: fetchAllFilesUrl,
                title: 'Select a file',
                type: 'file',
            },
            shortcut: 'CMD+SHIFT+H',
        },

        paragraph: {
            class: Paragraph,
            tunes: ['xutimAnchor', 'alignment'],
            inlineToolbar: [
                'link',
                'bold',
                'italic',
                'xutimFootnote',
                'xutimInternalPageLink',
                'xutimInternalArticleLink',
                'xutimInternalTagLink',
                'xutimInternalFileLink',
            ],
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
            config: { defaultStyle: 'unordered' },
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
            config: { fetchFilesUrl, fetchFileUrl },
            tunes: ['xutimAnchor'],
        },

        xutimImage: {
            class: XutimImageTool,
            config: { galleryUrl: fetchImagesUrl },
            tunes: ['xutimAnchor', 'alignment'],
        },

        imageRow: {
            class: ImageRowTool,
            config: {
                galleryUrl: fetchImagesUrl,
                allowedImagesPerRow: [2, 3, 4, 5],
                defaultImagesPerRow: 3,
            },
            tunes: ['xutimAnchor'],
        },

        pageLink: {
            class: createContentLink('Page link', pageLinkIcon),
            config: { listUrl: pageIdsUrl, title: 'Select a page' },
            tunes: ['xutimAnchor'],
        },

        articleLink: {
            class: createContentLink('Article link', articleLinkIcon),
            config: { listUrl: articleIdsUrl, title: 'Select an article' },
            tunes: ['xutimAnchor'],
        },

        block: {
            class: Block,
            config: { codes: blockCodes },
            tunes: ['xutimAnchor'],
        },

        xutimTag: {
            class: XutimTagListTool,
            config: { tags },
            tunes: ['xutimAnchor'],
        },

        foldableStart: {
            class: FoldableStart,
            tunes: ['xutimAnchor'],
        },

        foldableEnd: {
            class: FoldableEnd,
            tunes: ['xutimAnchor'],
        },
    };
}
