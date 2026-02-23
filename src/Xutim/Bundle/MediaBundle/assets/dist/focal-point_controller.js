import { Controller } from '@hotwired/stimulus';

const GRID_SIZE = 5;
const STEPS = [0.1, 0.3, 0.5, 0.7, 0.9];
const TOTAL_ZONES = GRID_SIZE * GRID_SIZE;

function zoneToCoords(index) {
    const col = index % GRID_SIZE;
    const row = Math.floor(index / GRID_SIZE);
    return { focalX: STEPS[col], focalY: STEPS[row] };
}

export default class extends Controller {
    static values = {
        activeZone: { type: Number, default: 12 },
        saveUrl: String,
    };

    static targets = ['zone'];

    select(event) {
        const index = parseInt(event.currentTarget.dataset.zoneIndex, 10);
        if (isNaN(index) || index < 0 || index >= TOTAL_ZONES) {
            return;
        }

        this.activeZoneValue = index;
        this.updateZones();
        this.save(zoneToCoords(index));
    }

    updateZones() {
        this.zoneTargets.forEach((el) => {
            const index = parseInt(el.dataset.zoneIndex, 10);
            el.classList.toggle('active', index === this.activeZoneValue);
        });
    }

    async save({ focalX, focalY }) {
        if (!this.hasSaveUrlValue) {
            return;
        }

        const response = await fetch(this.saveUrlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ focalX, focalY }),
        });

        if (!response.ok) {
            console.error('Failed to save focal point');
            return;
        }

        const data = await response.json();

        if (data.success) {
            this.dispatch('saved', { detail: data });

            const frame = this.element.closest('turbo-frame#modal');
            if (frame) {
                frame.innerHTML = '';
            }
        }
    }
}
