import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        if (document.visibilityState !== 'visible') return;

        this.maxScroll = 0;
        this.clicked = [];

        // Capture and persist the original referrer
        const storageKey = 'xutim_analytics_referrer';
        let originalReferrer = sessionStorage.getItem(storageKey);

        if (!originalReferrer && document.referrer) {
            // Only store if it's from a different domain
            const currentHost = window.location.hostname;
            const referrerHost = new URL(document.referrer).hostname;

            if (referrerHost !== currentHost) {
                originalReferrer = document.referrer;
                sessionStorage.setItem(storageKey, originalReferrer);
            }
        }

        this._onScroll = () => {
            const depth = Math.floor(
                ((window.scrollY + window.innerHeight) /
                    document.body.scrollHeight) *
                    100,
            );
            this.maxScroll = Math.max(this.maxScroll, depth);
        };
        this._onClick = (e) => {
            const el = e.target.closest('a,button,[role="button"]');
            if (!el) return;
            this.clicked.push({
                tag: el.tagName,
                id: el.id || undefined,
                class: el.className || undefined,
                role: el.getAttribute('role') || undefined,
                text: (el.textContent || '').trim().slice(0, 64) || undefined,
            });
        };

        window.addEventListener('scroll', this._onScroll, { passive: true });
        document.addEventListener('click', this._onClick);

        this._timer = setTimeout(() => {
            const payload = {
                path: location.pathname,
                screenSize: `${window.innerWidth}x${window.innerHeight}`,
                loadTime: Math.round(performance.now()),
                scrollDepth: this.maxScroll,
                clicks: this.clicked,
            };

            if (originalReferrer) {
                payload.referrer = originalReferrer;
            }

            navigator.sendBeacon(
                '/_analytics/collect',
                JSON.stringify(payload),
            );
            window.removeEventListener('scroll', this._onScroll);
            document.removeEventListener('click', this._onClick);
        }, 2000);
    }

    disconnect() {
        clearTimeout(this._timer);
        window.removeEventListener('scroll', this._onScroll);
        document.removeEventListener('click', this._onClick);
    }
}
