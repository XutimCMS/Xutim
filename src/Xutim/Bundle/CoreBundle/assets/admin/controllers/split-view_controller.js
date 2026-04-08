import { Controller } from '@hotwired/stimulus';
import EditorJS from 'https://esm.sh/@editorjs/editorjs@2.31.0-rc.10';
import { buildEditorTools } from '../lib/build_tools.js';

export default class extends Controller {
    static targets = [
        'root',
        'toggle',
        'container',
        'left',
        'right',
        'reference',
        'localeSelect',
        'localeSelectClipboard',
        'iconOn',
        'iconOff',
        'referenceHeader',
        'metaPretitle',
        'metaSlug',
        'metaTitle',
        'metaSubtitle',
        'metaDescription',
        'diffContainer',
        'referenceContainer',
        'changedBanner',
        'scrollLockBtn',
        'scrollLockIconLocked',
        'scrollLockIconUnlocked',
        'diffToggleBtn',
        'diffToggleBtnShowText',
        'diffToggleBtnHideText',
        'markReviewedBtn',
    ];
    static values = {
        referenceUrl: String,
        currentTranslationId: String,
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
        referenceDiffUrl: String,
        referenceHasChanged: Boolean,
    };

    connect() {
        this.isOn = localStorage.getItem('xutim.splitView') === '1';
        const stored = localStorage.getItem('xutim.splitViewScrollLock');
        this.scrollLocked = stored === null || stored === '1';

        if (this.isOn) {
            this.enable();
        } else {
            this.containerTarget.classList.remove('lg:grid-cols-2');
            this.rightTarget.classList.add('hidden');
            if (this.hasReferenceHeaderTarget)
                this.referenceHeaderTarget.classList.add('hidden');
            this.isOn = false;
        }
        this.#updateToggleButtons();

        this.#updateScrollLockButton();
    }

    async toggle() {
        if (this.isOn) {
            this.disable();
        } else {
            await this.enable();
        }
    }

    async enable() {
        this.isOn = true;
        localStorage.setItem('xutim.splitView', '1');

        this.containerTarget.classList.add('lg:grid-cols-2');
        this.rightTarget.classList.remove('hidden');
        if (this.hasReferenceHeaderTarget)
            this.referenceHeaderTarget.classList.remove('hidden');

        if (!this.refEditor) {
            await this.loadReference();
        }

        this.#updateToggleButtons();
        this.#applyScrollLock();
    }

    disable() {
        this.isOn = false;
        localStorage.setItem('xutim.splitView', '0');

        this.containerTarget.classList.remove('lg:grid-cols-2');
        this.rightTarget.classList.add('hidden');
        if (this.hasReferenceHeaderTarget)
            this.referenceHeaderTarget.classList.add('hidden');
        this.#updateToggleButtons();
    }

    #updateToggleButtons() {
        if (this.hasIconOnTarget && this.hasIconOffTarget) {
            this.iconOnTarget.classList.remove('hidden');
            this.iconOffTarget.classList.remove('hidden');

            this.iconOnTarget.classList.toggle('bg-surface-raised', this.isOn);
            this.iconOnTarget.classList.toggle('text-content-secondary', !this.isOn);

            this.iconOffTarget.classList.toggle('bg-surface-raised', !this.isOn);
            this.iconOffTarget.classList.toggle('text-content-secondary', this.isOn);
        }
    }

    async loadReference() {
        const url = this.localeSelectTarget?.value || this.referenceUrlValue;
        if (!url) return;

        const res = await fetch(url, {
            headers: { Accept: 'application/json' },
        });
        if (!res.ok) return;
        const data = await res.json();
        this.refData = data;
        const meta = data.meta ?? data.header ?? {};
        this.#updateReferenceMeta(meta);

        if (this.refEditor?.destroy) await this.refEditor.destroy();

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

        this.refEditor = new EditorJS({
            holder: this.referenceTarget,
            readOnly: true,
            data,
            tools: tools,
            onReady: () => this.decorateBlocksForCopy(),
        });
    }

    toolsConfig() {
        return window.XutimEditorTools || {};
    }

    async decorateBlocksForCopy() {
        const nodes = this.referenceTarget.querySelectorAll('.ce-block');
        const blocks = this.refData?.blocks ?? [];

        nodes.forEach((node, index) => {
            const content = this.#blockContent(node);
            if (!content) return;

            const b = blocks[index];
            if (!b) return;

            const visuallyEmpty =
                (content.innerText || '').trim().length === 0 &&
                content.querySelectorAll('img,video,figure,iframe,svg,embed')
                    .length === 0;

            let btn = node.querySelector(':scope > .x-copy-btn');
            if (!btn) {
                btn = document.createElement('button');
                btn.type = 'button';
                btn.className =
                    'x-copy-btn opacity-0 group-hover/block:opacity-100 transition-opacity cursor-pointer rounded-md p-1 hover:bg-surface-raised';
                btn.title = 'Copy';
                btn.innerHTML =
                    '<svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-content-tertiary" width="24" height="24" ' +
                    'viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
                    'stroke-linecap="round" stroke-linejoin="round"><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />' +
                    '<path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /></svg>';

                Object.assign(btn.style, {
                    position: 'absolute',
                    left: '-1.5rem',
                    top: '50%',
                    transform: 'translateY(-50%)',
                });
                node.style.position = 'relative';
                node.classList.add('group/block');
                node.appendChild(btn);

                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    try {
                        await this.#copySingleBlock(b, node);
                        this.flash('Copied');
                    } catch (err) {
                        console.error(err);
                        this.flash('Copy failed');
                    }
                });
            }

            btn.style.display = visuallyEmpty ? 'none' : 'inline-block';
        });
    }

    async copyAllBlocks() {
        const src = this.refData?.blocks ?? [];
        if (!src.length) return;

        // Build Editor.js ARRAY payload: [{ id, tool, data, tunes, time }]
        const payload = src.map((b) => ({
            id: b.id ?? this.#rid(10),
            tool: b.type,
            data: b.data ?? {},
            tunes: b.tunes ?? {},
            time: typeof b.time === 'number' ? b.time : 0.1,
        }));
        const payloadStr = JSON.stringify(payload);

        // Fallback HTML/text (sanitized) from the rendered readonly area
        const htmlText = this.#buildHtmlTextFromNode(this.referenceTarget);

        await this.#writeClipboardViaCopyEvent({
            'text/plain': htmlText.text,
            'text/html': htmlText.html,
            'application/x-editor-js': payloadStr,
            'application/editor-js': payloadStr,
        });

        this.flash('All blocks copied');
    }

    async #copySingleBlock(blockJson, blockNode) {
        const single = JSON.stringify([
            {
                id: blockJson.id ?? this.#rid(10),
                tool: blockJson.type,
                data: blockJson.data ?? {},
                tunes: blockJson.tunes ?? {},
                time: typeof blockJson.time === 'number' ? blockJson.time : 0.1,
            },
        ]);

        const htmlText = this.#buildHtmlTextFromNode(
            this.#blockContent(blockNode),
        );

        await this.#writeClipboardViaCopyEvent({
            'text/plain': htmlText.text,
            'text/html': htmlText.html,
            'application/x-editor-js': single,
            'application/editor-js': single,
        });
    }

    flash(text) {
        const el = document.createElement('div');
        el.className = 'fixed top-4 right-4 z-[70] flex items-center gap-2 rounded-lg border border-border bg-surface px-3 py-2 text-[13px] shadow-lg animate-[fadeIn_0.2s]';
        el.innerHTML = `${text}<button onclick="this.parentElement.remove()" class="ml-2 text-content-tertiary hover:text-content">&times;</button>`;
        document.body.appendChild(el);
        setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity 0.3s'; setTimeout(() => el.remove(), 300); }, 1500);
    }

    #updateReferenceMeta(meta = {}) {
        const get = (k) => meta[k] ?? '';
        if (this.hasMetaPretitleTarget)
            this.metaPretitleTarget.value = get('pretitle');
        if (this.hasMetaSlugTarget)
            this.metaSlugTarget.value = get('slug');
        if (this.hasMetaTitleTarget)
            this.metaTitleTarget.value = get('title');
        if (this.hasMetaSubtitleTarget)
            this.metaSubtitleTarget.value = get('subtitle');
        if (this.hasMetaDescriptionTarget) {
            this.metaDescriptionTarget.value = this.#asPlain(
                get('description'),
            );
        }
    }

    #asPlain(htmlOrText = '') {
        if (htmlOrText == null) return '';
        const tmp = document.createElement('div');
        tmp.innerHTML = String(htmlOrText);
        return (tmp.textContent || tmp.innerText || '').trim();
    }

    #blockContent(node) {
        return node.querySelector('.ce-block__content') || node;
    }

    #sanitizeForCopy(root) {
        const uiSelectors = [
            '.ce-toolbar',
            '.ce-block__actions',
            '.ce-settings',
            '.cdx-settings',
            '.cdx-settings-button',
            '.cdx-button',
            '[data-editorjs-ui]',
            '[data-noncontent]',
            '.image-row__add',
            '.image-tool__button',
            '.x-add-image',
        ];
        root.querySelectorAll(uiSelectors.join(',')).forEach((el) =>
            el.remove(),
        );
        root.querySelectorAll('button,a').forEach((el) => {
            const t = (el.innerText || '').trim();
            if (/^\+\s*add\b/i.test(t)) el.remove();
        });
        return root;
    }

    #buildHtmlTextFromNode(node) {
        const clone = node.cloneNode(true);
        this.#sanitizeForCopy(clone);
        const wrap = document.createElement('div');
        wrap.appendChild(clone);
        return { html: wrap.innerHTML, text: clone.innerText || '' };
    }

    async #writeClipboardViaCopyEvent(typeToValueMap) {
        const onCopy = (ev) => {
            ev.preventDefault();
            const dt = ev.clipboardData;
            for (const [type, value] of Object.entries(typeToValueMap)) {
                dt.setData(type, value);
            }
        };
        document.addEventListener('copy', onCopy, { once: true });
        const ok = document.execCommand('copy');
        document.removeEventListener('copy', onCopy);
        if (!ok) throw new Error('execCommand(copy) failed');
    }

    #rid(len = 10) {
        const bytes = new Uint8Array(len);
        crypto.getRandomValues(bytes);
        const alphabet =
            'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        return Array.from(bytes, (b) => alphabet[b % alphabet.length]).join('');
    }

    async showDiff() {
        if (!this.referenceDiffUrlValue) return;
        if (!this.hasDiffContainerTarget || !this.hasReferenceContainerTarget)
            return;

        const res = await fetch(this.referenceDiffUrlValue);
        if (!res.ok) return;

        this.diffContainerTarget.innerHTML = await res.text();
        this.referenceContainerTarget.classList.add('hidden');
        this.diffContainerTarget.classList.remove('hidden');
        this.#updateDiffButtonText(true);
    }

    showCurrent() {
        if (!this.hasDiffContainerTarget || !this.hasReferenceContainerTarget)
            return;

        this.diffContainerTarget.classList.add('hidden');
        this.referenceContainerTarget.classList.remove('hidden');
        this.#updateDiffButtonText(false);
    }

    #updateDiffButtonText(showingDiff) {
        if (this.hasDiffToggleBtnShowTextTarget) {
            this.diffToggleBtnShowTextTarget.classList.toggle(
                'hidden',
                showingDiff,
            );
        }
        if (this.hasDiffToggleBtnHideTextTarget) {
            this.diffToggleBtnHideTextTarget.classList.toggle(
                'hidden',
                !showingDiff,
            );
        }
    }

    async markReviewed(event) {
        const url = event.params.markReviewedUrl;
        if (!url) return;

        const btn = event.currentTarget;
        btn.disabled = true;

        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (res.ok) {
            if (this.hasChangedBannerTarget) {
                this.changedBannerTarget.remove();
            }
            if (this.hasDiffContainerTarget) {
                this.diffContainerTarget.classList.add('hidden');
            }
            if (this.hasReferenceContainerTarget) {
                this.referenceContainerTarget.classList.remove('hidden');
            }
            this.flash('Marked as reviewed');
        } else {
            btn.disabled = false;
            this.flash('Failed to mark as reviewed');
        }
    }

    toggleDiff() {
        if (!this.hasDiffContainerTarget) return;

        if (this.diffContainerTarget.classList.contains('hidden')) {
            this.showDiff();
        } else {
            this.showCurrent();
        }
    }

    toggleScrollLock() {
        this.scrollLocked = !this.scrollLocked;
        localStorage.setItem(
            'xutim.splitViewScrollLock',
            this.scrollLocked ? '1' : '0',
        );
        this.#applyScrollLock();
        this.#updateScrollLockButton();
    }

    #applyScrollLock() {
        if (!this.hasRightTarget) return;
        if (this.scrollLocked) {
            this.rightTarget.style.position = '';
            this.rightTarget.style.top = '';
            this.rightTarget.style.maxHeight = '';
            this.rightTarget.style.overflowY = '';
        } else {
            this.rightTarget.style.position = 'sticky';
            this.rightTarget.style.top = '0';
            this.rightTarget.style.maxHeight = '100vh';
            this.rightTarget.style.overflowY = 'auto';
        }
    }

    #updateScrollLockButton() {
        if (!this.hasScrollLockBtnTarget) return;
        if (this.scrollLocked) {
            this.scrollLockBtnTarget.classList.add('active');
        } else {
            this.scrollLockBtnTarget.classList.remove('active');
        }
        if (this.hasScrollLockIconLockedTarget) {
            this.scrollLockIconLockedTarget.classList.toggle(
                'hidden',
                !this.scrollLocked,
            );
        }
        if (this.hasScrollLockIconUnlockedTarget) {
            this.scrollLockIconUnlockedTarget.classList.toggle(
                'hidden',
                this.scrollLocked,
            );
        }
    }
}
