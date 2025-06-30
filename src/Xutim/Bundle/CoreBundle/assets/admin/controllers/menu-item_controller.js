import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'articleToggle',
        'pageToggle',
        'articleForm',
        'pageForm',
        'tagForm',
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
                this.articleFormTarget.getElementsByTagName('select')[0].value
            ) {
                this.#showArticleForm();
                this.#hidePageForm();
                this.#hideTagForm();
            }
            if (this.pageFormTarget.getElementsByTagName('select')[0].value) {
                if (
                    this.pageOverwriteLinkFormTarget.getElementsByTagName(
                        'select',
                    )[0].value
                ) {
                    this.#showPageOverwriteLink();
                } else {
                    this.#hidePageOverwriteLink();
                }
                this.#showPageForm();
                this.#hideArticleForm();
                this.#hideTagForm();
            }
            if (this.tagFormTarget.getElementsByTagName('select')[0].value) {
                this.#showTagForm();
                this.#hidePageForm();
                this.#hideArticleForm();
            }
        } else {
            this.#hidePageForm();
            this.#hidePageOverwriteLink();
            this.#hideArticleForm();
            this.#hideTagForm();
            this.submitButtonTarget.hidden = true;
        }
    }

    showArticle() {
        this.#hidePageForm();
        this.#showArticleForm();
        this.#hideTagForm();
        this.#showSubmitButton();
    }

    showPage() {
        this.#showPageForm();
        this.#hideArticleForm();
        this.#hideTagForm();
        this.#showSubmitButton();
    }

    showTag() {
        this.#showTagForm();
        this.#hideArticleForm();
        this.#hidePageForm();
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

    #hideTagForm() {
        this.tagFormTarget.hidden = true;
    }

    #showArticleForm() {
        this.articleFormTarget.hidden = false;
    }

    #showPageForm() {
        this.pageFormTarget.hidden = false;
    }

    #showTagForm() {
        this.tagFormTarget.hidden = false;
    }

    #showSubmitButton() {
        this.submitButtonTarget.hidden = false;
    }
}
