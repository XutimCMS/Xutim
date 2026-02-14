import { Controller } from '@hotwired/stimulus';

/** stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        if (document.visibilityState !== 'visible') return;

        this.maxScroll = 0;
        this.clicked = [];

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
        this._onBeforeVisit = () => {
            sessionStorage.setItem('xutim_prev_path', location.href);
        };

        window.addEventListener('scroll', this._onScroll, { passive: true });
        document.addEventListener('click', this._onClick);
        document.addEventListener('turbo:before-visit', this._onBeforeVisit);

        this._timer = setTimeout(() => {
            const payload = {
                path: location.pathname,
                screenSize: `${window.innerWidth}x${window.innerHeight}`,
                loadTime: Math.round(performance.now()),
                scrollDepth: this.maxScroll,
                clicks: this.clicked,
            };

            const prevPath = sessionStorage.getItem('xutim_prev_path');
            if (prevPath) {
                try {
                    const prevUrl = new URL(prevPath);
                    if (prevUrl.pathname !== location.pathname) {
                        payload.referrer = prevPath;
                    }
                } catch {
                    // Invalid URL, skip
                }
            } else if (document.referrer) {
                payload.referrer = document.referrer;
            }

            if (location.search) {
                payload.queryString = location.search;
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
        document.removeEventListener('turbo:before-visit', this._onBeforeVisit);
    }
}
