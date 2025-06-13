import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'articleToggle',
        'pageToggle',
        'articleForm',
        'pageForm',
        'overwriteCheckbox',
        'pageOverwriteLinkForm',
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
                if (
                    this.pageOverwriteLinkFormTarget.getElementsByTagName(
                        'select',
                    )[0].value
                ) {
                    this.#showPageOverwriteLink();
                } else {
                    this.#hidePageOverwriteLink();
                }
            }
            if (!this.pageFormTarget.getElementsByTagName('select')[0].value) {
                this.#hidePageForm();
            }
        } else {
            this.#hidePageForm();
            this.#hidePageOverwriteLink();
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

    togglePageOverwriteLink() {
        if (this.pageOverwriteLinkFormTarget.hidden == true) {
            this.#showPageOverwriteLink();
        } else {
            this.#hidePageOverwriteLink();
        }
    }

    #showPageOverwriteLink() {
        this.pageOverwriteLinkFormTarget.hidden = false;
        this.overwriteCheckboxTarget.getElementsByTagName('input')[0].checked =
            true;
    }

    #hidePageOverwriteLink() {
        this.pageOverwriteLinkFormTarget.hidden = true;
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
