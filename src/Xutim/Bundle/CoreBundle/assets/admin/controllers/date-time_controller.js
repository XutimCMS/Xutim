import { Controller } from '@hotwired/stimulus';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

export default class extends Controller {
    static values = {
        format: String,
    };

    connect() {
        flatpickr(this.element, {
            dateFormat: this.formatValue,
            enableTime: true,
            time_24hr: true,
            allowInput: true,
        });
    }
}
