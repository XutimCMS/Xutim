import { Controller } from '@hotwired/stimulus';
import debounce from 'debounce';

export default class extends Controller {
    initialize() {
        this.debouncedSubmit = debounce(this.debouncedSubmit.bind(this), 500);
        this.abortController = null;
    }

    submit(e) {
        // Cancel any pending request
        if (this.abortController) {
            this.abortController.abort();
        }
        this.abortController = new AbortController();

        // Store current input value before submit
        const input = this.element.querySelector('input[type="text"]');
        const currentValue = input ? input.value : null;

        this.element.requestSubmit();

        // Restore input value after turbo replaces the content
        if (input && currentValue !== null) {
            requestAnimationFrame(() => {
                const newInput = this.element.querySelector('input[type="text"]');
                if (newInput && newInput.value !== currentValue) {
                    newInput.value = currentValue;
                    // Move cursor to end
                    newInput.setSelectionRange(currentValue.length, currentValue.length);
                }
            });
        }
    }

    debouncedSubmit() {
        this.submit();
    }
}
