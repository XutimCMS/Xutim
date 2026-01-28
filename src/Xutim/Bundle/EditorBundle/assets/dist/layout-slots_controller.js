import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        blockId: String,
        createUrl: String,
    };

    static targets = ['slot'];

    async addBlock(event) {
        event.preventDefault();
        const slotIndex = event.currentTarget.dataset.slotIndex;

        const type = this.selectBlockType();
        if (!type) return;

        await this.createBlockInSlot(slotIndex, type);
    }

    selectBlockType() {
        const types = ['paragraph', 'heading', 'quote', 'image'];
        const selected = window.prompt(`Select block type:\n${types.map((t, i) => `${i + 1}. ${t}`).join('\n')}\n\nEnter number:`);

        if (!selected) return null;
        const index = parseInt(selected, 10) - 1;
        return types[index] || null;
    }

    async createBlockInSlot(slotIndex, type) {
        if (!this.hasCreateUrlValue) return;

        try {
            const response = await fetch(this.createUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'text/vnd.turbo-stream.html',
                },
                body: JSON.stringify({
                    type,
                    parentBlockId: this.blockIdValue,
                    slot: parseInt(slotIndex, 10),
                }),
            });

            if (response.ok) {
                const html = await response.text();
                Turbo.renderStreamMessage(html);
            }
        } catch (error) {
            console.error('Error creating block in slot:', error);
        }
    }
}
