import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { message: String };

    #dirtyForms = new Set();
    #submittedForms = new WeakSet();
    #revertingHistory = false;

    markDirty(event) {
        const form = event.target.form ?? event.target.closest?.('form');
        if (this.#isTracked(form)) {
            this.#dirtyForms.add(form);
        }
    }

    markSubmitted(event) {
        if (!event.defaultPrevented) {
            this.markClean(event);
        }
    }

    markClean(event) {
        this.#dirtyForms.delete(event.target);
        this.#submittedForms.add(event.target);
    }

    restoreOnFailure(event) {
        if (!event.detail.success && this.#isTracked(event.target)) {
            this.#dirtyForms.add(event.target);
        }
    }

    confirmVisit(event) {
        if (this.#hasUnsavedChanges() && !window.confirm(this.messageValue)) {
            event.preventDefault();
        }
    }

    confirmHistoryVisit(event) {
        const index = event.state?.turbo?.restorationIndex;
        if (index === undefined) {
            return;
        }

        if (this.#revertingHistory) {
            this.#revertingHistory = false;
            event.stopImmediatePropagation();
            return;
        }

        if (this.#hasUnsavedChanges() && !window.confirm(this.messageValue)) {
            event.stopImmediatePropagation();
            this.#revertingHistory = true;
            history.go(window.Turbo.session.history.currentIndex - index);
        }
    }

    warnBeforeUnload(event) {
        if (this.#hasUnsavedChanges()) {
            event.preventDefault();
            event.returnValue = '';
        }
    }

    #isTracked(form) {
        return form instanceof HTMLFormElement
            && form.method === 'post'
            && !form.hasAttribute('data-unsaved-changes-ignore');
    }

    #isOnScreen(form) {
        return form.isConnected && form.checkVisibility();
    }

    #hasUnsavedChanges() {
        const dirtyForm = [...this.#dirtyForms].some((form) => this.#isOnScreen(form));

        const rejectedForm = [...this.element.querySelectorAll('form')].some(
            (form) => this.#isTracked(form)
                && !this.#submittedForms.has(form)
                && this.#isOnScreen(form)
                && form.querySelector('.is-invalid'),
        );

        return dirtyForm || rejectedForm;
    }
}
