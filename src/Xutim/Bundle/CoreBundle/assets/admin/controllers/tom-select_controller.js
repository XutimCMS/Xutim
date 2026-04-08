import TomSelect from 'tom-select';
import { Controller } from '@hotwired/stimulus';
import 'tom-select/dist/css/tom-select.default.min.css';

export default class extends Controller {
    connect() {
        const ts = new TomSelect(this.element, {
            maxOptions: null,
            plugins: {
                remove_button: {},
            },
        });

        ts.on('item_add', () => {
            ts.setTextboxValue('');
        });
    }
}
