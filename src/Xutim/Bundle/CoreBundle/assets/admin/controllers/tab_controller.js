import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['btn', 'panel'];

    switch(event) {
        event.preventDefault();
        const target = event.currentTarget.dataset.tabTarget || event.currentTarget.getAttribute('href');

        this.btnTargets.forEach(btn => {
            btn.classList.remove('active', 'border-b-2', 'border-accent', 'font-semibold');
            btn.classList.add('text-content-secondary');
        });
        event.currentTarget.classList.add('active', 'border-b-2', 'border-accent', 'font-semibold');
        event.currentTarget.classList.remove('text-content-secondary');

        this.panelTargets.forEach(panel => {
            panel.classList.add('hidden');
            if ('#' + panel.id === target || panel.id === target) {
                panel.classList.remove('hidden');
            }
        });
    }
}
