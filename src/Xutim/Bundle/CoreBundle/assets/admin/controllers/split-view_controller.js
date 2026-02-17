import { Controller } from '@hotwired/stimulus';
import * as bootstrap from 'bootstrap';
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
            this.localeSelectTarget.hidden = false;
            this.localeSelectClipboardTarget.hidden = false;
            if (this.hasScrollLockBtnTarget)
                this.scrollLockBtnTarget.hidden = false;
        } else {
            this.leftTarget.classList.remove('col-lg-6');
            this.rightTarget.classList.add('d-none');

            if (this.hasIconOnTarget)
                this.iconOnTarget.classList.remove('d-none');
            if (this.hasIconOffTarget)
                this.iconOffTarget.classList.add('d-none');

            this.isOn = false;

            this.localeSelectTarget.hidden = true;
            this.localeSelectClipboardTarget.hidden = true;
            if (this.hasScrollLockBtnTarget)
                this.scrollLockBtnTarget.hidden = true;
        }

        this.#updateScrollLockButton();
    }

    async toggle() {
        if (this.isOn) {
            this.disable();
            this.localeSelectTarget.hidden = true;
            this.localeSelectClipboardTarget.hidden = true;
            if (this.hasScrollLockBtnTarget)
                this.scrollLockBtnTarget.hidden = true;
        } else {
            await this.enable();
            this.localeSelectTarget.hidden = false;
            this.localeSelectClipboardTarget.hidden = false;
            if (this.hasScrollLockBtnTarget)
                this.scrollLockBtnTarget.hidden = false;
        }
    }

    async enable() {
        this.isOn = true;
        localStorage.setItem('xutim.splitView', '1');

        this.leftTarget.classList.add('col-lg-6');
        this.rightTarget.classList.remove('d-none');

        if (!this.refEditor) {
            await this.loadReference();
        }

        this.iconOnTarget.classList.add('d-none');
        this.iconOffTarget.classList.remove('d-none');

        this.#applyScrollLock();
    }

    disable() {
        this.isOn = false;
        localStorage.setItem('xutim.splitView', '0');

        this.leftTarget.classList.remove('col-lg-6');
        this.rightTarget.classList.add('d-none');

        this.iconOnTarget.classList.remove('d-none');
        this.iconOffTarget.classList.add('d-none');
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
                    'x-copy-btn btn btn-sm btn-link p-0 text-center';
                btn.title = 'Copy';
                btn.innerHTML =
                    '<svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" width="24" height="24" ' +
                    'viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
                    'stroke-linecap="round" stroke-linejoin="round"><path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />' +
                    '<path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z" /></svg>';

                Object.assign(btn.style, {
                    position: 'absolute',
                    left: '-1rem',
                    top: '0rem',
                    fontSize: '12px',
                    marginLeft: '-0.5rem',
                    top: '50%',
                    transform: 'translateY(-50%)',
                });
                node.style.position = 'relative';
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
        el.className =
            'toast align-items-center text-bg-secondary border-0 position-fixed top-0 end-0 m-3';
        el.role = 'alert';
        el.innerHTML = `<div class="d-flex">
          <div class="toast-body">${text}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;
        document.body.appendChild(el);
        const t = new bootstrap.Toast(el, { delay: 1500 });
        t.show();
        t._element.addEventListener('hidden.bs.toast', () => el.remove());
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
        this.referenceContainerTarget.classList.add('d-none');
        this.diffContainerTarget.classList.remove('d-none');
        this.#updateDiffButtonText(true);
    }

    showCurrent() {
        if (!this.hasDiffContainerTarget || !this.hasReferenceContainerTarget)
            return;

        this.diffContainerTarget.classList.add('d-none');
        this.referenceContainerTarget.classList.remove('d-none');
        this.#updateDiffButtonText(false);
    }

    #updateDiffButtonText(showingDiff) {
        if (this.hasDiffToggleBtnShowTextTarget) {
            this.diffToggleBtnShowTextTarget.classList.toggle(
                'd-none',
                showingDiff,
            );
        }
        if (this.hasDiffToggleBtnHideTextTarget) {
            this.diffToggleBtnHideTextTarget.classList.toggle(
                'd-none',
                !showingDiff,
            );
        }
    }

    toggleDiff() {
        if (!this.hasDiffContainerTarget) return;

        if (this.diffContainerTarget.classList.contains('d-none')) {
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
            this.rightTarget.style.overflow = 'hidden';
            this.rightTarget.style.position = 'static';
        } else {
            this.rightTarget.style.overflow = '';
            this.rightTarget.style.position = '';
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
                'd-none',
                !this.scrollLocked,
            );
        }
        if (this.hasScrollLockIconUnlockedTarget) {
            this.scrollLockIconUnlockedTarget.classList.toggle(
                'd-none',
                this.scrollLocked,
            );
        }
    }
}
