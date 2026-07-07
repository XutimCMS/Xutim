import { Controller } from '@hotwired/stimulus';

// The language bar sits outside the DataTable's <turbo-frame>, so its links keep the
// query string from initial page load while in-frame sorting/filtering advances the
// URL. Rewrite each link's query string from the live location at interaction time so
// switching locale keeps the active filters.
export default class extends Controller {
    connect() {
        this.syncTarget = this.syncTarget.bind(this);
        this.element.addEventListener('click', this.syncTarget, true);
        this.element.addEventListener('auxclick', this.syncTarget, true);
        this.element.addEventListener('contextmenu', this.syncTarget, true);
    }

    disconnect() {
        this.element.removeEventListener('click', this.syncTarget, true);
        this.element.removeEventListener('auxclick', this.syncTarget, true);
        this.element.removeEventListener('contextmenu', this.syncTarget, true);
    }

    syncTarget(event) {
        const link = event.target.closest('a[href]');
        if (link === null || this.element.contains(link) === false) {
            return;
        }

        const url = new URL(link.getAttribute('href'), window.location.origin);
        url.search = window.location.search;
        link.setAttribute('href', url.pathname + url.search + url.hash);
    }
}
