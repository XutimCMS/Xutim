import TomSelect from 'tom-select';
import { Controller } from '@hotwired/stimulus';
import 'tom-select/dist/css/tom-select.bootstrap5.min.css';
import '../styles/tom-select.css';

export default class extends Controller {
    connect() {
        const ts = new TomSelect(this.element, {
            maxOptions: null,
            plugins: {
                remove_button: {},
            },
            onDropdownOpen: (dropdown) => this.fitDropdown(ts, dropdown),
            onDropdownClose: () => this.releaseDropdown(ts),
        });

        ts.on('item_add', () => {
            ts.setTextboxValue('');
        });
    }

    fitDropdown(ts, dropdown) {
        const dialog = this.element.closest('dialog');
        const dialogScrolls = dialog !== null && this.scrollsWithout(dialog, dropdown);
        if (dialog !== null && !dialogScrolls) {
            dialog.style.setProperty('overflow', 'visible', 'important');
        }

        const bounds = dialogScrolls
            ? dialog.getBoundingClientRect()
            : { top: 0, bottom: window.innerHeight };
        const control = ts.control.getBoundingClientRect();
        const spaceBelow = bounds.bottom - control.bottom;
        const spaceAbove = control.top - bounds.top;

        ts.wrapper.classList.toggle(
            'dropdown-up',
            dropdown.offsetHeight > spaceBelow && spaceAbove > spaceBelow
        );
    }

    scrollsWithout(dialog, dropdown) {
        const display = dropdown.style.display;
        dropdown.style.display = 'none';
        const scrolls = dialog.scrollHeight > dialog.clientHeight + 1;
        dropdown.style.display = display;

        return scrolls;
    }

    releaseDropdown(ts) {
        ts.wrapper.classList.remove('dropdown-up');
        this.element.closest('dialog')?.style.removeProperty('overflow');
    }
}
