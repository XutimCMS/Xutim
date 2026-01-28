import { Controller } from '@hotwired/stimulus';
import { Editor, Extension, Node } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import Placeholder from '@tiptap/extension-placeholder';
import BubbleMenu from '@tiptap/extension-bubble-menu';
import { Mark, mergeAttributes } from '@tiptap/core';

// Custom Document that only allows a single node type (for Notion-style editing)
const SingleNodeDocument = Node.create({
    name: 'doc',
    topNode: true,
    content: 'block',
});

const ArticleLink = Mark.create({
    name: 'articleLink',

    addOptions() {
        return {
            HTMLAttributes: {
                class: 'article-link',
            },
        };
    },

    addAttributes() {
        return {
            articleId: {
                default: null,
                parseHTML: element => element.getAttribute('data-article-id'),
                renderHTML: attributes => ({
                    'data-article-id': attributes.articleId,
                    'data-entity': 'article',
                }),
            },
            href: {
                default: null,
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'a[data-entity="article"]',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['a', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
    },
});

const PageLink = Mark.create({
    name: 'pageLink',

    addOptions() {
        return {
            HTMLAttributes: {
                class: 'page-link',
            },
        };
    },

    addAttributes() {
        return {
            pageId: {
                default: null,
                parseHTML: element => element.getAttribute('data-page-id'),
                renderHTML: attributes => ({
                    'data-page-id': attributes.pageId,
                    'data-entity': 'page',
                }),
            },
            href: {
                default: null,
            },
        };
    },

    parseHTML() {
        return [
            {
                tag: 'a[data-entity="page"]',
            },
        ];
    },

    renderHTML({ HTMLAttributes }) {
        return ['a', mergeAttributes(this.options.HTMLAttributes, HTMLAttributes), 0];
    },
});

export default class extends Controller {
    static values = {
        blockId: String,
        blockType: String,
        saveUrl: String,
        createUrl: String,
        deleteUrl: String,
        articleSearchUrl: String,
        pageSearchUrl: String,
    };

    static targets = ['content', 'preview'];

    connect() {
        this.editor = null;
        this.isEditing = false;
        this.bubbleMenuElement = null;
        this.originalHtml = '';
        this.handleEscape = this.handleEscape.bind(this);
    }

    disconnect() {
        this.destroyEditor();
        document.removeEventListener('keydown', this.handleEscape);
    }

    handleEscape(event) {
        if (event.key === 'Escape' && this.isEditing) {
            event.preventDefault();
            this.cancelEdit();
        }
    }

    cancelEdit() {
        if (!this.isEditing) return;

        // Restore original content
        if (this.hasPreviewTarget) {
            this.previewTarget.innerHTML = this.originalHtml;
            this.previewTarget.classList.remove('d-none');
        }

        // Destroy editor without saving
        this.destroyEditor();
        this.contentTarget.classList.add('d-none');
        this.isEditing = false;
        this.element.classList.remove('editor-block--editing');
        document.removeEventListener('keydown', this.handleEscape);
    }

    createBubbleMenu() {
        const menu = document.createElement('div');
        menu.className = 'tiptap-bubble-menu';
        menu.innerHTML = `
            <button type="button" class="tiptap-bubble-menu__btn" data-action="bold" title="Bold (Ctrl+B)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/></svg>
            </button>
            <button type="button" class="tiptap-bubble-menu__btn" data-action="italic" title="Italic (Ctrl+I)">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/></svg>
            </button>
            <button type="button" class="tiptap-bubble-menu__btn" data-action="strike" title="Strikethrough">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><path d="M16 6C16 6 14.5 4 12 4C9.5 4 7 6 7 8.5C7 11 9 12 12 12"/><path d="M8 18C8 18 9.5 20 12 20C14.5 20 17 18 17 15.5C17 13 15 12 12 12"/></svg>
            </button>
            <span class="tiptap-bubble-menu__separator"></span>
            <button type="button" class="tiptap-bubble-menu__btn" data-action="link" title="Add link">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
            </button>
            <button type="button" class="tiptap-bubble-menu__btn tiptap-bubble-menu__btn--article" data-action="articleLink" title="Link to article">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"/><path d="M18 14h-8"/><path d="M15 18h-5"/><path d="M10 6h8v4h-8V6Z"/></svg>
            </button>
            <button type="button" class="tiptap-bubble-menu__btn tiptap-bubble-menu__btn--page" data-action="pageLink" title="Link to page">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </button>
        `;

        menu.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('mousedown', (e) => {
                e.preventDefault();
                this.handleBubbleAction(btn.dataset.action);
            });
        });

        return menu;
    }

    handleBubbleAction(action) {
        if (!this.editor) return;

        switch (action) {
            case 'bold':
                this.editor.chain().focus().toggleBold().run();
                break;
            case 'italic':
                this.editor.chain().focus().toggleItalic().run();
                break;
            case 'strike':
                this.editor.chain().focus().toggleStrike().run();
                break;
            case 'link':
                const url = window.prompt('Enter URL:');
                if (url) {
                    this.editor.chain().focus().setLink({ href: url }).run();
                } else if (url === '') {
                    this.editor.chain().focus().unsetLink().run();
                }
                break;
            case 'articleLink':
                const articleId = window.prompt('Enter Article ID:');
                if (articleId) {
                    this.editor.chain().focus().setMark('articleLink', {
                        articleId,
                        href: `/article/${articleId}`
                    }).run();
                }
                break;
            case 'pageLink':
                const pageId = window.prompt('Enter Page ID:');
                if (pageId) {
                    this.editor.chain().focus().setMark('pageLink', {
                        pageId,
                        href: `/page/${pageId}`
                    }).run();
                }
                break;
        }

        this.updateBubbleMenuState();
    }

    updateBubbleMenuState() {
        if (!this.bubbleMenuElement || !this.editor) return;

        this.bubbleMenuElement.querySelectorAll('[data-action]').forEach(btn => {
            const action = btn.dataset.action;
            let isActive = false;

            switch (action) {
                case 'bold':
                    isActive = this.editor.isActive('bold');
                    break;
                case 'italic':
                    isActive = this.editor.isActive('italic');
                    break;
                case 'strike':
                    isActive = this.editor.isActive('strike');
                    break;
                case 'link':
                    isActive = this.editor.isActive('link');
                    break;
                case 'articleLink':
                    isActive = this.editor.isActive('articleLink');
                    break;
                case 'pageLink':
                    isActive = this.editor.isActive('pageLink');
                    break;
            }

            btn.classList.toggle('is-active', isActive);
        });
    }

    startEdit(event = null) {
        if (this.isEditing) return;
        this.isEditing = true;

        this.originalHtml = this.hasPreviewTarget
            ? this.previewTarget.innerHTML
            : this.contentTarget.innerHTML;

        this.element.classList.add('editor-block--editing');
        document.addEventListener('keydown', this.handleEscape);

        if (this.hasPreviewTarget) {
            this.previewTarget.classList.add('d-none');
        }
        this.contentTarget.classList.remove('d-none');
        this.contentTarget.innerHTML = '';

        this.bubbleMenuElement = this.createBubbleMenu();

        // Determine block type and configure StarterKit accordingly
        const blockType = this.hasBlockTypeValue ? this.blockTypeValue : 'paragraph';
        const starterKitConfig = this.getStarterKitConfig(blockType);
        const placeholderText = this.getPlaceholderText(blockType);
        const initialContent = this.getInitialContent(blockType);

        this.editor = new Editor({
            element: this.contentTarget,
            extensions: [
                // Use SingleNodeDocument to restrict to exactly one block
                SingleNodeDocument,
                StarterKit.configure({
                    document: false, // We use our custom SingleNodeDocument
                    ...starterKitConfig,
                }),
                Link.configure({
                    openOnClick: false,
                    HTMLAttributes: { class: 'external-link' },
                }),
                Placeholder.configure({
                    placeholder: placeholderText,
                    showOnlyWhenEditable: true,
                    showOnlyCurrent: false,
                }),
                BubbleMenu.configure({
                    element: this.bubbleMenuElement,
                    tippyOptions: {
                        duration: 100,
                        placement: 'top',
                        appendTo: () => document.body,
                    },
                }),
                ArticleLink,
                PageLink,
                Extension.create({
                    name: 'blockKeyboardHandler',
                    addKeyboardShortcuts: () => ({
                        'Enter': () => {
                            this.handleEnterKey();
                            return true;
                        },
                        'Escape': () => {
                            this.cancelEdit();
                            return true;
                        },
                        'Backspace': () => this.handleBackspaceKey(),
                        'ArrowUp': () => this.handleArrowUp(),
                        'ArrowDown': () => this.handleArrowDown(),
                    }),
                }),
            ],
            content: initialContent,
            autofocus: false,
            onSelectionUpdate: () => this.updateBubbleMenuState(),
            onBlur: ({ event }) => {
                if (event.relatedTarget?.closest('.tiptap-bubble-menu')) return;
                setTimeout(() => {
                    if (!this.element.contains(document.activeElement) &&
                        !document.activeElement?.closest('.tiptap-bubble-menu')) {
                        this.endEdit();
                    }
                }, 150);
            },
        });

        requestAnimationFrame(() => {
            this.editor?.commands.focus('end');
        });
    }

    getStarterKitConfig(blockType) {
        // Disable nodes that don't match the block type
        // This ensures only the correct node type is allowed
        const config = {
            // Disable all block-level nodes by default
            heading: false,
            paragraph: false,
            blockquote: false,
            bulletList: false,
            orderedList: false,
            listItem: false,
            codeBlock: false,
            horizontalRule: false,
        };

        // Enable only the node type matching our block
        switch (blockType) {
            case 'heading':
                config.heading = { levels: [2, 3, 4] };
                break;
            case 'quote':
                config.blockquote = true;
                // Blockquote needs paragraph for content inside
                config.paragraph = true;
                break;
            case 'paragraph':
            default:
                config.paragraph = true;
                break;
        }

        return config;
    }

    getPlaceholderText(blockType) {
        switch (blockType) {
            case 'heading':
                return 'Heading...';
            case 'quote':
                return 'Quote...';
            default:
                return 'Start typing...';
        }
    }

    getInitialContent(blockType) {
        // Extract inner HTML from the original content
        const temp = document.createElement('div');
        temp.innerHTML = this.originalHtml;
        const firstNode = temp.firstElementChild;
        const innerHtml = firstNode ? firstNode.innerHTML : this.originalHtml;

        // Wrap content in appropriate node type
        switch (blockType) {
            case 'heading':
                // Determine heading level from original content
                const level = firstNode?.tagName?.match(/H(\d)/)?.[1] || '2';
                return innerHtml ? `<h${level}>${innerHtml}</h${level}>` : `<h${level}></h${level}>`;
            case 'quote':
                return innerHtml ? `<blockquote><p>${innerHtml}</p></blockquote>` : '<blockquote><p></p></blockquote>';
            default:
                return innerHtml ? `<p>${innerHtml}</p>` : '<p></p>';
        }
    }

    endEdit() {
        if (!this.isEditing || !this.editor) return;

        const blockType = this.hasBlockTypeValue ? this.blockTypeValue : 'paragraph';
        const innerHtml = this.extractInnerHtml(blockType);

        this.save(innerHtml);

        if (this.hasPreviewTarget) {
            // Update only the content inside the existing preview element structure
            const previewElement = this.previewTarget.firstElementChild;
            if (previewElement) {
                previewElement.innerHTML = innerHtml;
            } else {
                // Fallback: reconstruct preview
                this.previewTarget.innerHTML = this.wrapContent(blockType, innerHtml);
            }
            this.previewTarget.classList.remove('d-none');
        }

        this.destroyEditor();
        this.contentTarget.classList.add('d-none');
        this.isEditing = false;
        this.element.classList.remove('editor-block--editing');
        document.removeEventListener('keydown', this.handleEscape);
    }

    extractInnerHtml(blockType) {
        const fullHtml = this.editor.getHTML();
        const temp = document.createElement('div');
        temp.innerHTML = fullHtml;

        switch (blockType) {
            case 'heading': {
                // Extract heading inner HTML
                const heading = temp.querySelector('h2, h3, h4');
                return heading ? heading.innerHTML : '';
            }
            case 'quote': {
                // Extract blockquote inner HTML (might have nested p)
                const blockquote = temp.querySelector('blockquote');
                if (!blockquote) return '';
                // If blockquote contains a p, get that p's content
                const p = blockquote.querySelector('p');
                return p ? p.innerHTML : blockquote.innerHTML;
            }
            default: {
                // Paragraph - get first p's innerHTML
                const p = temp.querySelector('p');
                return p ? p.innerHTML : '';
            }
        }
    }

    wrapContent(blockType, innerHtml) {
        switch (blockType) {
            case 'heading':
                return `<h2>${innerHtml}</h2>`;
            case 'quote':
                return `<blockquote>${innerHtml}</blockquote>`;
            default:
                return `<p>${innerHtml}</p>`;
        }
    }

    destroyEditor() {
        if (this.editor) {
            this.editor.destroy();
            this.editor = null;
        }
        if (this.bubbleMenuElement) {
            this.bubbleMenuElement.remove();
            this.bubbleMenuElement = null;
        }
    }

    async save(html) {
        this.element.classList.add('editor-block--saving');
        try {
            const response = await fetch(this.saveUrlValue, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ html }),
            });

            if (response.ok) {
                this.element.classList.add('editor-block--saved');
                setTimeout(() => this.element.classList.remove('editor-block--saved'), 1500);
            } else {
                console.error('Failed to save block');
                this.element.classList.add('editor-block--error');
                setTimeout(() => this.element.classList.remove('editor-block--error'), 3000);
            }
        } catch (error) {
            console.error('Error saving block:', error);
            this.element.classList.add('editor-block--error');
            setTimeout(() => this.element.classList.remove('editor-block--error'), 3000);
        } finally {
            this.element.classList.remove('editor-block--saving');
        }
    }

    isActive(format, attrs = {}) {
        return this.editor?.isActive(format, attrs) || false;
    }

    async handleEnterKey() {
        if (!this.editor || !this.hasCreateUrlValue) return;

        const { to } = this.editor.state.selection;
        const docSize = this.editor.state.doc.content.size;

        let textAfterCursor = '';
        if (to < docSize - 1) {
            const slice = this.editor.state.doc.slice(to, docSize - 1);
            textAfterCursor = slice.content.textBetween(0, slice.content.size, '', '');
            this.editor.chain().focus().deleteRange({ from: to, to: docSize - 1 }).run();
        }

        // Save extracted inner HTML, not full HTML
        const blockType = this.hasBlockTypeValue ? this.blockTypeValue : 'paragraph';
        const innerHtml = this.extractInnerHtml(blockType);
        await this.save(innerHtml);
        await this.createBlockBelow(textAfterCursor);
    }

    handleBackspaceKey() {
        if (!this.editor) return false;

        const isEmpty = this.editor.state.doc.textContent.trim() === '';
        const { $from } = this.editor.state.selection;
        const isAtStart = $from.pos === $from.start($from.depth);
        const isFirstBlock = $from.depth <= 1 || $from.index($from.depth - 1) === 0;

        if (isAtStart && isFirstBlock && isEmpty) {
            this.deleteBlockAndFocusPrevious();
            return true;
        }

        return false;
    }

    handleArrowUp() {
        if (!this.editor) return false;

        const { state, view } = this.editor;
        const { selection } = state;

        const cursorCoords = view.coordsAtPos(selection.from);
        const startCoords = view.coordsAtPos(1);

        if (Math.abs(cursorCoords.top - startCoords.top) < 5) {
            this.focusPreviousBlock();
            return true;
        }

        return false;
    }

    handleArrowDown() {
        if (!this.editor) return false;

        const { state, view } = this.editor;
        const { selection, doc } = state;
        const lastPos = doc.content.size - 1;

        const cursorCoords = view.coordsAtPos(selection.to);
        const endCoords = view.coordsAtPos(lastPos);

        if (Math.abs(cursorCoords.top - endCoords.top) < 5) {
            this.focusNextBlock();
            return true;
        }

        return false;
    }

    focusPreviousBlock() {
        this.endEdit();

        const currentFrame = this.element.closest('turbo-frame');
        const prevFrame = currentFrame?.previousElementSibling;

        if (prevFrame?.tagName === 'TURBO-FRAME') {
            this.focusBlockInFrame(prevFrame);
        }
    }

    focusNextBlock() {
        this.endEdit();

        const currentFrame = this.element.closest('turbo-frame');
        const nextFrame = currentFrame?.nextElementSibling;

        if (nextFrame?.tagName === 'TURBO-FRAME') {
            this.focusBlockInFrame(nextFrame);
        }
    }

    focusBlockInFrame(frame) {
        const tiptapElement = frame.querySelector('[data-controller*="tiptap-block"]');
        if (tiptapElement) {
            const tiptapController = this.application.getControllerForElementAndIdentifier(
                tiptapElement,
                'tiptap-block'
            );
            if (tiptapController) {
                tiptapController.startEdit();
                requestAnimationFrame(() => {
                    tiptapController.editor?.commands.focus('end');
                });
                return;
            }
        }

        const blockElement = frame.querySelector('[data-controller*="editor-block"]');
        if (blockElement) {
            const blockController = this.application.getControllerForElementAndIdentifier(
                blockElement,
                'editor-block'
            );
            blockController?.focus();
        }
    }

    async deleteBlockAndFocusPrevious() {
        if (!this.hasDeleteUrlValue) return;

        const currentFrame = this.element.closest('turbo-frame');
        const prevFrame = currentFrame?.previousElementSibling;

        try {
            const response = await fetch(this.deleteUrlValue, {
                method: 'DELETE',
                headers: {
                    'Accept': 'text/vnd.turbo-stream.html',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const html = await response.text();
                if (html) Turbo.renderStreamMessage(html);

                if (prevFrame?.tagName === 'TURBO-FRAME') {
                    const prevBlockElement = prevFrame.querySelector('[data-controller*="tiptap-block"]');
                    if (prevBlockElement) {
                        const tiptapController = this.application.getControllerForElementAndIdentifier(
                            prevBlockElement,
                            'tiptap-block'
                        );
                        tiptapController?.startEdit();
                        tiptapController?.editor?.commands.focus('end');
                    }
                }
            }
        } catch (error) {
            console.error('Error deleting block:', error);
        }
    }

    async createBlockBelow(initialContent = '') {
        if (!this.hasCreateUrlValue) return;

        try {
            const response = await fetch(this.createUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/vnd.turbo-stream.html',
                },
                body: JSON.stringify({
                    type: 'paragraph',
                    html: initialContent,
                    afterBlockId: this.blockIdValue,
                }),
            });

            if (response.ok) {
                const html = await response.text();
                Turbo.renderStreamMessage(html);
                this.endEdit();

                setTimeout(() => {
                    const currentFrame = this.element.closest('turbo-frame');
                    const nextFrame = currentFrame?.nextElementSibling;
                    if (nextFrame) {
                        const newBlockElement = nextFrame.querySelector('[data-controller*="tiptap-block"]');
                        if (newBlockElement) {
                            const tiptapController = this.application.getControllerForElementAndIdentifier(
                                newBlockElement,
                                'tiptap-block'
                            );
                            tiptapController?.startEdit();
                        }
                    }
                }, 100);
            }
        } catch (error) {
            console.error('Error creating block:', error);
        }
    }
}
