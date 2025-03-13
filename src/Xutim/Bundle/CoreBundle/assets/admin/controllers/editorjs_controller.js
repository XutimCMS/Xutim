import { Controller } from '@hotwired/stimulus';
import EditorJS from 'https://esm.sh/@editorjs/editorjs@2.30.5';
import Header from '@editorjs/header';

export default class extends Controller {
    connect() {
        const editor = new EditorJS({
            holder: this.element,
            tools: {
                header: {
                    class: Header,
                    config: {
                        placeholder: 'Enter a header',
                        levels: [1, 2, 3],
                        defaultLevel: 2,
                    },
                },
            },
        });
    }
}
