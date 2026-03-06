import './bootstrap.js';

import './styles/app.css';
import './styles/revision.css';

import '@tabler/core/dist/css/tabler.min.css';
import '@popperjs/core';

import './turbo/turbo-helper.js';

document.addEventListener('input', (e) => {
    const field = e.target.closest('.is-invalid, .mb-3');
    if (!field) return;
    field.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    field.classList.remove('is-invalid');
    field.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
});
