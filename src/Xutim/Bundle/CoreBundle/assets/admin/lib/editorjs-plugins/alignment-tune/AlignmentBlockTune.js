import './styles.css';

export default class AlignmentBlockTune {
    static get DEFAULT_ALIGNMENT() {
        return 'left';
    }

    static get isTune() {
        return true;
    }

    getAlignment() {
        if (
            !!this.settings?.blocks &&
            this.settings.blocks.hasOwnProperty(this.block.name)
        ) {
            return this.settings.blocks[this.block.name];
        }
        if (!!this.settings?.default) {
            return this.settings.default;
        }
        return AlignmentBlockTune.DEFAULT_ALIGNMENT;
    }

    constructor({ api, data, config, block }) {
        this.api = api;
        this.block = block;
        /**
         config:{
            default: "right",
            blocks: {
              header: 'center',
              list: 'right'
            }
          },
         */
        this.settings = config;
        this.data = data || { alignment: this.getAlignment() };
        this.alignmentSettings = [
            {
                name: 'left',
                icon: `<svg xmlns="http://www.w3.org/2000/svg" id="Layer" enable-background="new 0 0 64 64" height="20" viewBox="0 0 64 64" width="20"><path d="m54 8h-44c-1.104 0-2 .896-2 2s.896 2 2 2h44c1.104 0 2-.896 2-2s-.896-2-2-2z"/><path d="m54 52h-44c-1.104 0-2 .896-2 2s.896 2 2 2h44c1.104 0 2-.896 2-2s-.896-2-2-2z"/><path d="m10 23h28c1.104 0 2-.896 2-2s-.896-2-2-2h-28c-1.104 0-2 .896-2 2s.896 2 2 2z"/><path d="m54 30h-44c-1.104 0-2 .896-2 2s.896 2 2 2h44c1.104 0 2-.896 2-2s-.896-2-2-2z"/><path d="m10 45h28c1.104 0 2-.896 2-2s-.896-2-2-2h-28c-1.104 0-2 .896-2 2s.896 2 2 2z"/></svg>`,
            },
            {
                name: 'center',
                icon: `<svg xmlns="http://www.w3.org/2000/svg" id="Layer" enable-background="new 0 0 64 64" height="20" viewBox="0 0 64 64" width="20"><path d="m54 8h-44c-1.104 0-2 .896-2 2s.896 2 2 2h44c1.104 0 2-.896 2-2s-.896-2-2-2z"/><path d="m54 52h-44c-1.104 0-2 .896-2 2s.896 2 2 2h44c1.104 0 2-.896 2-2s-.896-2-2-2z"/><path d="m46 23c1.104 0 2-.896 2-2s-.896-2-2-2h-28c-1.104 0-2 .896-2 2s.896 2 2 2z"/><path d="m54 30h-44c-1.104 0-2 .896-2 2s.896 2 2 2h44c1.104 0 2-.896 2-2s-.896-2-2-2z"/><path d="m46 45c1.104 0 2-.896 2-2s-.896-2-2-2h-28c-1.104 0-2 .896-2 2s.896 2 2 2z"/></svg>`,
            },
            {
                name: 'right',
                icon: `<svg xmlns="http://www.w3.org/2000/svg" id="Layer" enable-background="new 0 0 64 64" height="20" viewBox="0 0 64 64" width="20"><path d="m54 8h-44c-1.104 0-2 .896-2 2s.896 2 2 2h44c1.104 0 2-.896 2-2s-.896-2-2-2z"/><path d="m54 52h-44c-1.104 0-2 .896-2 2s.896 2 2 2h44c1.104 0 2-.896 2-2s-.896-2-2-2z"/><path d="m54 19h-28c-1.104 0-2 .896-2 2s.896 2 2 2h28c1.104 0 2-.896 2-2s-.896-2-2-2z"/><path d="m54 30h-44c-1.104 0-2 .896-2 2s.896 2 2 2h44c1.104 0 2-.896 2-2s-.896-2-2-2z"/><path d="m54 41h-28c-1.104 0-2 .896-2 2s.896 2 2 2h28c1.104 0 2-.896 2-2s-.896-2-2-2z"/></svg>`,
            },
        ];
        this._CSS = {
            alignment: {
                left: 'ce-tune-alignment--left',
                center: 'ce-tune-alignment--center',
                right: 'ce-tune-alignment--right',
            },
        };
    }

    wrap(blockContent) {
        this.wrapper = document.createElement('div');
        this.wrapper.classList.toggle(this._CSS.alignment[this.data.alignment]);
        this.wrapper.append(blockContent);
        return this.wrapper;
    }

    render() {
        const wrapper = document.createElement('div');
        wrapper.classList.add('ce-tune-alignment--wrapper');
        this.alignmentSettings
            .map((align) => {
                const button = document.createElement('button');
                button.classList.add(this.api.styles.settingsButton);
                button.innerHTML = align.icon;
                button.type = 'button';

                button.classList.toggle(
                    this.api.styles.settingsButtonActive,
                    align.name === this.data.alignment,
                );
                wrapper.appendChild(button);
                return button;
            })
            .forEach((element, index, elements) => {
                element.addEventListener('click', () => {
                    this.data = {
                        alignment: this.alignmentSettings[index].name,
                    };
                    elements.forEach((el, i) => {
                        const { name } = this.alignmentSettings[i];
                        el.classList.toggle(
                            this.api.styles.settingsButtonActive,
                            name === this.data.alignment,
                        );
                        //toggle alignment style class for block
                        this.wrapper.classList.toggle(
                            this._CSS.alignment[name],
                            name === this.data.alignment,
                        );
                    });
                });
            });
        return wrapper;
    }

    save() {
        return this.data;
    }
}
