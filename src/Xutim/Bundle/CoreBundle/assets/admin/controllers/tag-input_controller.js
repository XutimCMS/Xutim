import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        pattern: String,
        max: Number,
        separators: { type: Array, default: [' ', ',', 'Enter'] },
    };

    connect() {
        this.input = this.element;
        if (this.input.tagName !== 'INPUT' || this.input.type !== 'text') {
            console.warn('tag-input expects a text <input> element.');
            return;
        }

        this.input.classList.add('tag-input__input');
        this.tags = this.parseInitial(this.input.value);
        this.regex = this.hasPatternValue && this.patternValue ? new RegExp(this.patternValue) : null;
        this.max = this.hasMaxValue && this.maxValue > 0 ? this.maxValue : null;

        this.buildShell();
        this.render();
        this.bindEvents();
    }

    parseInitial(raw) {
        if (!raw) return [];
        return raw.split(/[\s,]+/).map(s => s.trim().toLowerCase()).filter(Boolean);
    }

    buildShell() {
        const container = document.createElement('div');
        container.className = 'tag-input form-control d-flex flex-wrap align-items-center gap-1 p-1';
        container.style.minHeight = 'calc(1.4285714286em + 0.875rem + 2px)';
        container.style.cursor = 'text';

        const editor = document.createElement('input');
        editor.type = 'text';
        editor.className = 'tag-input__editor border-0 flex-grow-1';
        editor.style.outline = 'none';
        editor.style.minWidth = '8ch';
        editor.style.background = 'transparent';
        editor.setAttribute('autocomplete', 'off');
        editor.setAttribute('autocapitalize', 'off');
        editor.setAttribute('spellcheck', 'false');

        const errorBox = document.createElement('div');
        errorBox.className = 'invalid-feedback d-none';

        this.input.classList.add('d-none');
        this.input.setAttribute('aria-hidden', 'true');
        this.input.parentNode.insertBefore(container, this.input.nextSibling);
        container.parentNode.insertBefore(errorBox, container.nextSibling);

        this.shell = container;
        this.editor = editor;
        this.errorBox = errorBox;
        this.shell.appendChild(this.editor);

        this.shell.addEventListener('click', (e) => {
            if (e.target === this.shell) this.editor.focus();
        });
    }

    render() {
        this.shell.querySelectorAll('.tag-input__chip').forEach(c => c.remove());

        this.tags.forEach((tag, idx) => {
            const chip = document.createElement('span');
            chip.className = 'tag-input__chip badge bg-blue-lt d-inline-flex align-items-center gap-1';
            chip.dataset.index = String(idx);

            const label = document.createElement('span');
            label.textContent = tag;
            chip.appendChild(label);

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn-close btn-close-sm ms-1';
            remove.setAttribute('aria-label', 'Remove');
            remove.style.fontSize = '0.55rem';
            remove.addEventListener('click', () => this.removeTag(idx));
            chip.appendChild(remove);

            this.shell.insertBefore(chip, this.editor);
        });

        this.input.value = this.tags.join(',');
    }

    bindEvents() {
        this.editor.addEventListener('keydown', (e) => {
            if (this.separatorsValue.includes(e.key)) {
                e.preventDefault();
                this.commit();
                return;
            }
            if (e.key === 'Backspace' && this.editor.value === '' && this.tags.length > 0) {
                e.preventDefault();
                this.removeTag(this.tags.length - 1);
            }
        });

        this.editor.addEventListener('blur', () => this.commit());

        this.editor.addEventListener('paste', (e) => {
            const pasted = (e.clipboardData || window.clipboardData).getData('text');
            if (!pasted) return;
            e.preventDefault();
            const parts = pasted.split(/[\s,]+/).map(s => s.trim().toLowerCase()).filter(Boolean);
            parts.forEach(p => this.addTag(p));
            this.editor.value = '';
        });

        this.element.form?.addEventListener('submit', () => this.commit());
    }

    commit() {
        const raw = this.editor.value.trim().toLowerCase();
        if (raw === '') return;
        if (this.addTag(raw)) {
            this.editor.value = '';
        }
    }

    addTag(value) {
        if (!value) return false;
        if (this.tags.includes(value)) {
            this.flashError(`"${value}" already added.`);
            return false;
        }
        if (this.regex && !this.regex.test(value)) {
            this.flashError(`"${value}" is not valid.`);
            return false;
        }
        if (this.max !== null && this.tags.length >= this.max) {
            this.flashError(`Maximum ${this.max} entries.`);
            return false;
        }
        this.tags.push(value);
        this.render();
        this.clearError();
        return true;
    }

    removeTag(index) {
        this.tags.splice(index, 1);
        this.render();
        this.editor.focus();
    }

    flashError(msg) {
        this.errorBox.textContent = msg;
        this.errorBox.classList.remove('d-none');
        this.errorBox.classList.add('d-block');
        this.shell.classList.add('is-invalid');
        clearTimeout(this._errTimer);
        this._errTimer = setTimeout(() => this.clearError(), 2500);
    }

    clearError() {
        this.errorBox.classList.add('d-none');
        this.errorBox.classList.remove('d-block');
        this.shell.classList.remove('is-invalid');
    }
}
