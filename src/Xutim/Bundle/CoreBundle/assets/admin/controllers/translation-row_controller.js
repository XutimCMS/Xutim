import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        token: String,
    };

    static targets = ['imageOutput'];

    connect() {}
}
