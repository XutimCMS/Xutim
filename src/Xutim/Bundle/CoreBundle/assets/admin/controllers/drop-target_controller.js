import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        id: String,
        moveUrl: String,
    };

    static targets = ['folderCard'];

    over(event) {
        event.preventDefault();
        this.folderCardTarget.classList.add('card-active');
    }

    leave(event) {
        this.folderCardTarget.classList.remove('card-active');
    }

    async drop(event) {
        event.preventDefault();
        this.folderCardTarget.classList.remove('card-active');

        const fileId = event.dataTransfer.getData('application/x-media-id');
        if (!fileId) return;

        try {
            const response = await fetch(this.moveUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    fileId: fileId,
                    targetFolderId: this.idValue,
                }),
            });

            if (response.ok) {
                // Option A: reload page
                Turbo.visit(window.location.href);
                // Option B: remove element from DOM instead of full reload
            } else {
                alert('Could not move file');
            }
        } catch (e) {
            console.error(e);
            alert('Error moving file');
        }
    }
}
