const TurboHelper = class {
    constructor() {
        document.addEventListener('turbo:before-cache', () => {
            // Close any open dialogs
            document.querySelectorAll('dialog[open]').forEach(dialog => dialog.close());

            // Destroy tom-select instances
            document.querySelectorAll('select[data-controller="tom-select"]').forEach(el => {
                if (el.tomselect) {
                    el.tomselect.destroy();
                }
            });
        });
    }
};

export default new TurboHelper();
