import { Controller } from '@hotwired/stimulus';
import axios from 'axios';
import * as Turbo from '@hotwired/turbo';

export default class extends Controller {
    #page = 0;
    #pageLength = 20;
    #searchTerm = '';
    #orderColumn = 0;
    #orderDirection = 0;

    static targets = [
        'cardContainer',
        'tableContainer',
        'cardBody',
        'cardFooter',
    ];

    static values = {
        url: String,
    };

    connect() {
        // Parse url
        const urlParams = this.#parseFromUrl();
        // set params
        this.#setUrlParams(urlParams);
        this.#refreshList();
    }

    performSearch(event) {
        // Add timer, so query is not executed after every single typed letter.
        if (this.timer) {
            clearTimeout(this.timer);
            this.timer = null;
        }
        const searchTerm = event.currentTarget.value;
        this.timer = setTimeout(() => {
            this.#addParamToUrl('searchTerm', searchTerm);
            this.#searchTerm = searchTerm;
            this.#refreshList();
        }, 500);
    }

    changePage(event) {
        const page = event.currentTarget.dataset.pageIndex;
        this.#addParamToUrl('page', page);
        this.#page = page;
        this.#refreshList();
    }

    changePageLength(event) {
        const length = event.currentTarget.value;
        this.#addParamToUrl('pageLength', length);
        this.#pageLength = length;
        this.#refreshList();
    }

    #addParamToUrl(param, value) {
        const urlParams = this.#parseFromUrl();
        urlParams.set(param, value);

        const url =
            window.location.origin +
            window.location.pathname +
            '?' +
            urlParams.toString();

        window.history.replaceState({}, '', url);
        Turbo.navigator.history.replace(new URL(url));
    }

    #refreshList() {
        axios
            .get(this.urlValue, {
                params: {
                    searchTerm: this.#searchTerm,
                    orderColumn: this.#orderColumn,
                    orderDir: this.#orderDirection,
                    pageLength: this.#pageLength,
                    page: this.#page,
                },
            })
            .then((response) => {
                const parser = new DOMParser();
                const cardElem = parser.parseFromString(
                    response.data,
                    'text/html',
                );
                const tableContainer = cardElem.querySelector(
                    '[data-table-target="tableContainer"]',
                );
                const cardBodyContainer = cardElem.querySelector(
                    '[data-table-target="cardBody"]',
                );
                const cardFooterContainer =
                    cardElem.querySelector('.card-footer');

                this.cardBodyTarget.innerHTML = cardBodyContainer.innerHTML;
                this.tableContainerTarget.innerHTML = tableContainer.innerHTML;
                this.cardFooterTarget.outerHTML = cardFooterContainer.outerHTML;

                // console.error(tableContainer, cardBodyContainer, cardFooterContainer);

                // this.cardContainerTarget.innerHTML = response.data;
            });
    }

    #parseFromUrl() {
        const queryString = window.location.search;

        return new URLSearchParams(queryString);
    }

    #setUrlParams(urlParams) {
        if (urlParams.has('searchTerm')) {
            this.#searchTerm = urlParams.get('searchTerm');
        }

        if (urlParams.has('orderColumn')) {
            this.#orderColumn = urlParams.get('orderColumn');
        }

        if (urlParams.has('orderDir')) {
            this.#orderDirection = urlParams.get('orderDir');
        }

        if (urlParams.has('pageLength')) {
            this.#pageLength = urlParams.get('pageLength');
        }

        if (urlParams.has('page')) {
            this.#page = urlParams.get('page');
        }
    }
}
