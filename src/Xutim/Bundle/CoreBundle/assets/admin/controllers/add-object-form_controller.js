import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        token: String,
    };

    static targets = ['newFileField', 'newFileFields', 'existingFileField'];

    toggleExistingFile(event) {
        if (event.currentTarget.value) {
            this.newFileFieldTarget.hidden = true;
        }
    }

    toggleNewFile(event) {
        if (event.currentTarget.value) {
            this.newFileFieldsTarget.hidden = false;
            this.existingFileFieldTarget.hidden = true;
        } else {
            this.newFileFieldsTarget.hidden = true;
            this.existingFileFieldTarget.hidden = false;
        }
    }
}
