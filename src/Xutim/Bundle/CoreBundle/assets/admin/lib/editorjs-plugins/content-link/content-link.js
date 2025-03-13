export default function createContentLink(title, icon) {
    return class ContentLink {
        constructor({ data, config, api }) {
            this.api = api;
            this.data = data;
            this.config = config || {};
        }

        static get toolbox() {
            return {
                title: title,
                icon: icon,
            };
        }

        render() {
            const url = this.config.listUrl;

            const wrapper = document.createElement('div');
            wrapper.classList.add('mt-4');
            wrapper.classList.add('form-floating');

            const select = document.createElement('select');
            select.name = 'linkSelect';
            select.id = 'linkSelect';
            select.classList.add('form-select');

            const label = document.createElement('label');
            label.textContent = this.config.title;
            label.htmlFor = 'linkSelect';

            const link = document.createElement('a');
            link.href = '#';
            link.target = '_blank';
            link.style.display = 'none';
            link.classList.add('d-block', 'mt-2');

            fetch(url)
                .then((response) => response.json())
                .then((data) => {
                    const entries = Object.entries(data);

                    entries.sort((a, b) => a[1].localeCompare(b[1]));
                    entries.forEach(([id, title]) => {
                        const option = document.createElement('option');
                        option.value = id;
                        option.textContent = title;
                        select.appendChild(option);
                    });

                    if (this.data.id) {
                        select.value = this.data.id;
                        link.href = `//${this.data.id}`;
                        link.textContent = `🔗 Go to ${select.options[select.selectedIndex].text}`;
                        link.style.display = 'inline';
                        select.style.display = 'none';
                        label.style.display = 'none';
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                });

            select.addEventListener('change', (event) => {
                const selectedId = event.target.value;
                this.data.id = selectedId;

                if (selectedId) {
                    link.href = `//${selectedId}`;
                    link.textContent = `🔗 Go to ${event.target.options[event.target.selectedIndex].text}`;
                    link.style.display = 'inline';
                    select.style.display = 'none';
                    label.style.display = 'none';
                }
            });

            link.addEventListener('click', (event) => {
                event.preventDefault();
                select.style.display = 'block';
                label.style.display = 'block';
                link.style.display = 'none';
            });

            wrapper.appendChild(select);
            wrapper.appendChild(label);
            wrapper.appendChild(link);

            // Save the selected value
            select.addEventListener('change', (event) => {
                this.data.id = event.target.value;
            });

            return wrapper;
        }

        save() {
            return {
                id: this.data.id || '',
            };
        }

        validate(savedData) {
            const idToValidate = savedData.id.trim();

            if (!idToValidate) {
                return false;
            }

            return true;
        }
    };
}
