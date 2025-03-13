import tippy from 'tippy.js';

function format_selection_menu() {
    return `
  <div class="py-2 px-3 bg-dark rounded-3" data-controller="format">
    <a class="text-white" data-action="mousedown->format#bold" href="#">
      Bold
    </a>
    <span class="mx-2 text-white">|</span>
    <a class="text-white" data-action="mousedown->format#italic" href="#">
      Italic
    </a>
    <span class="mx-2 text-white">|</span>
    <a class="text-white" data-action="mousedown->format#strikethrough" href="#">
      Strike
    </a>
    <span class="mx-2 text-white">|</span>
    <a class="text-white" data-action="mousedown->format#code" href="#">
      Code
    </a>
  </div>
  `;
}

export function show_format_selection_menu(element) {
    let box = window.getSelection().getRangeAt(0).getBoundingClientRect();
    return tippy(element, {
        allowHTML: true,
        content: format_selection_menu(),
        interactive: true,
        interactiveBorder: 100,
        inlinePositioning: true,
        maxWidth: 250,
        getReferenceClientRect: () => box,
        onHidden: (instance) => {
            instance.destroy();
        },
    }).show();
}
