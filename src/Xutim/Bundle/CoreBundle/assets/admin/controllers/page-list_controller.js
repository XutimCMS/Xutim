import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';
import axios from 'axios';

export default class extends Controller {
    static values = {
        index: Number,
        reorderUrl: String,
    };

    connect() {
        Sortable.create(this.element, {
            animation: 200,
            handle: '.draggable-handle',
            draggable: '.draggable-step',
            onEnd: (event) => {
                axios.post(
                    this.reorderUrlValue,
                    {},
                    {
                        params: {
                            startPos: event.oldIndex,
                            endPos: event.newIndex,
                        },
                    },
                );
            },
        });
    }
}
