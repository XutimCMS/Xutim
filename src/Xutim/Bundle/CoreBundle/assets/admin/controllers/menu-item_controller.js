import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'articleToggle',
        'pageToggle',
        'articleForm',
        'pageForm',
        'submitButton',
    ];

    static values = {
        isUpdate: Boolean,
    };

    connect() {
        if (this.isUpdateValue) {
            if (
                !this.articleFormTarget.getElementsByTagName('select')[0].value
            ) {
                this.#hideArticleForm();
            }
            if (!this.pageFormTarget.getElementsByTagName('select')[0].value) {
                this.#hidePageForm();
            }
        } else {
            this.#hidePageForm();
            this.#hideArticleForm();
            this.submitButtonTarget.hidden = true;
        }
    }

    showArticle() {
        this.#hidePageForm();
        this.#showArticleForm();
        this.#showSubmitButton();
    }

    showPage() {
        this.#showPageForm();
        this.#hideArticleForm();
        this.#showSubmitButton();
    }

    #hidePageForm() {
        this.pageFormTarget.hidden = true;
    }

    #hideArticleForm() {
        this.articleFormTarget.hidden = true;
    }

    #showArticleForm() {
        this.articleFormTarget.hidden = false;
    }

    #showPageForm() {
        this.pageFormTarget.hidden = false;
    }

    #showSubmitButton() {
        this.submitButtonTarget.hidden = false;
    }
}
