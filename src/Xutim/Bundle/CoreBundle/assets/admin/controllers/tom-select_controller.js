import TomSelect from 'tom-select';
import { Controller } from '@hotwired/stimulus';
import 'tom-select/dist/css/tom-select.bootstrap5.min.css';
import '../styles/tom-select.css';

export default class extends Controller {
    connect() {
        new TomSelect(this.element, {
            maxOptions: null,
            plugins: {
                remove_button: {},
                no_backspace_delete: {},
            },
        });
    }
}
