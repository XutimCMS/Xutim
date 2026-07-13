// Centralized Editor.js tools builder used across multiple controllers
import Header from '@editorjs/header';
import Paragraph from '@editorjs/paragraph';
import Quote from '@editorjs/quote';
import List from '@editorjs/list';
import Delimiter from '@editorjs/delimiter';
import Embed from '@editorjs/embed';

import XutimHeroHeadingTool from './editorjs-plugins/hero-heading/hero-heading.js';
import XutimBlockTool from './editorjs-plugins/block/block.js';
import XutimImageRowTool from './editorjs-plugins/image-row/image-row.js';
import XutimImageTool from './editorjs-plugins/image/image.js';
import XutimFileTool from './editorjs-plugins/file/file.js';
import XutimAlignmentTune from './editorjs-plugins/alignment-tune/alignment-tune.js';
import XutimTagLinkTool from './editorjs-plugins/tag-link/tag-link.js';
import XutimAnchorTune from './editorjs-plugins/anchor-tune/anchor-tune.js';
import XutimFootnoteInline from './editorjs-plugins/footnotes-tune/footnotes-tune.js';
import XutimFoldableStartTool from './editorjs-plugins/foldable/foldable-start.js';
import XutimFoldableEndTool from './editorjs-plugins/foldable/foldable-end.js';
import XutimSectionTool from './editorjs-plugins/section/section.js';

import createContentLink from './editorjs-plugins/content-link/content-link.js';
import createInternalLink from './editorjs-plugins/internal-inline-link/internal-inline-link.js';

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
    xutimSections,
    xutimSectionFormUrl,
    xutimSectionSaveUrl,
    xutimSectionRefreshUrl,
    xutimSectionPreviewUrl,
    extraTools = {},
    headerExtraTunes = [],
    heroHeadingExtraTunes = [],
}) {
    return {
        xutimAlignment: { class: XutimAlignmentTune },

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
            tunes: ['xutimAnchor', 'xutimAlignment'],
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
            tunes: ['xutimAnchor', 'xutimAlignment', ...headerExtraTunes],
        },

        xutimHeroHeading: {
            class: XutimHeroHeadingTool,
            name: 'Hero heading',
            config: {
                placeholder: 'Enter a header',
                levels: [1, 2, 3],
                defaultLevel: 2,
            },
            tunes: ['xutimAnchor', ...heroHeadingExtraTunes],
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
            inlineToolbar: ['link', 'bold', 'italic'],
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
            inlineToolbar: ['link', 'bold', 'italic'],
            tunes: ['xutimAnchor', 'xutimAlignment'],
        },

        xutimImageRow: {
            class: XutimImageRowTool,
            config: {
                galleryUrl: fetchImagesUrl,
                allowedImagesPerRow: [2, 3, 4, 5],
                defaultImagesPerRow: 3,
            },
            tunes: ['xutimAnchor'],
        },

        xutimPageLink: {
            class: createContentLink('Page link', pageLinkIcon),
            config: { listUrl: pageIdsUrl, title: 'Select a page' },
            tunes: ['xutimAnchor'],
        },

        xutimArticleLink: {
            class: createContentLink('Article link', articleLinkIcon),
            config: { listUrl: articleIdsUrl, title: 'Select an article' },
            tunes: ['xutimAnchor'],
        },

        xutimBlock: {
            class: XutimBlockTool,
            config: { codes: blockCodes },
            tunes: ['xutimAnchor'],
        },

        xutimTagLink: {
            class: XutimTagLinkTool,
            config: { tags },
            tunes: ['xutimAnchor'],
        },

        xutimFoldableStart: {
            class: XutimFoldableStartTool,
            tunes: ['xutimAnchor'],
        },

        xutimFoldableEnd: {
            class: XutimFoldableEndTool,
            tunes: ['xutimAnchor'],
        },

        xutimSection: {
            class: XutimSectionTool,
            config: {
                sections: xutimSections || [],
                formUrl: xutimSectionFormUrl || '',
                saveUrl: xutimSectionSaveUrl || '',
                refreshUrl: xutimSectionRefreshUrl || '',
                previewUrl: xutimSectionPreviewUrl || '',
            },
            tunes: ['xutimAnchor'],
        },

        // Downstream-injected tools / tunes. Merged last so they
        // can override or extend the core tool set.
        ...extraTools,
    };
}
