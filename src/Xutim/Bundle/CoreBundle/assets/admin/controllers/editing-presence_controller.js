import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        heartbeatUrl: String,
        stopUrl: String,
        mercureTopic: String,
        currentUserId: String,
        interval: { type: Number, default: 10000 },
        timeout: { type: Number, default: 30000 },
    };

    static targets = [
        'banner',
        'userName',
        'contentChangedBanner',
        'contentChangedUserName',
    ];

    #intervalId;
    #lastEventAt;
    #eventSource;
    #knownDraftUpdatedAt;
    #knownTranslationUpdatedAt;

    connect() {
        this.#sendHeartbeat();
        this.#intervalId = setInterval(
            () => this.#sendHeartbeat(),
            this.intervalValue,
        );
        this.#subscribeToMercure();
        this.#lastEventAt = null;
    }

    disconnect() {
        clearInterval(this.#intervalId);
        if (this.#eventSource) {
            this.#eventSource.close();
        }
        if (this.hasStopUrlValue && this.stopUrlValue) {
            navigator.sendBeacon(this.stopUrlValue);
        }
    }

    #sendHeartbeat() {
        fetch(this.heartbeatUrlValue, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((r) => r.json())
            .then((data) => this.#updateBanner(data))
            .catch(() => {});
    }

    #subscribeToMercure() {
        if (!this.hasMercureTopicValue || !this.mercureTopicValue) {
            return;
        }

        try {
            const url = new URL(this.mercureTopicValue);
            this.#eventSource = new EventSource(url);
        } catch {
            return;
        }

        this.#eventSource.onmessage = (event) => {
            const data = JSON.parse(event.data);
            if (data.userId === this.currentUserIdValue) {
                return;
            }

            if (data.type === 'stopped') {
                this.#hideBanner();
            } else {
                this.#showBanner(data.userName);
                this.#lastEventAt = Date.now();
            }
        };

        this.#eventSource.onerror = () => {
            this.#eventSource.close();
            this.#eventSource = null;
        };
    }

    #updateBanner(data) {
        if (data.isOtherUserEditing) {
            this.#showBanner(data.editingUser?.name);
        } else {
            this.#hideBanner();
        }

        this.#checkContentChanged(data);
    }

    #checkContentChanged(data) {
        if (this.#knownDraftUpdatedAt === undefined) {
            this.#knownDraftUpdatedAt = data.draftUpdatedAt;
            this.#knownTranslationUpdatedAt = data.translationUpdatedAt;
            return;
        }

        const draftChanged =
            data.draftUpdatedAt !== this.#knownDraftUpdatedAt &&
            data.draftUpdatedBy;
        const translationChanged =
            data.translationUpdatedAt !== this.#knownTranslationUpdatedAt;

        if (draftChanged) {
            this.#showContentChangedBanner(data.draftUpdatedBy);
        } else if (translationChanged) {
            this.#showContentChangedBanner(null);
        }
    }

    #showBanner(userName) {
        if (!this.hasBannerTarget) {
            return;
        }

        if (this.hasUserNameTarget) {
            this.userNameTarget.textContent = userName || '';
        }
        this.bannerTarget.classList.remove('d-none');
    }

    #hideBanner() {
        if (!this.hasBannerTarget) {
            return;
        }

        this.bannerTarget.classList.add('d-none');
    }

    #showContentChangedBanner(userName) {
        if (!this.hasContentChangedBannerTarget) {
            return;
        }

        if (this.hasContentChangedUserNameTarget) {
            this.contentChangedUserNameTarget.textContent = userName || '';
        }
        this.contentChangedBannerTarget.classList.remove('d-none');
    }

    reloadPage() {
        window.location.reload();
    }
}
