import './bootstrap.js';

import './styles/tailwind.css';

import './turbo/turbo-helper.js';

// Clear validation errors on input
document.addEventListener('input', (e) => {
    const field = e.target.closest('.mb-4');
    if (!field) return;
    field.querySelectorAll('.border-red-300, .border-red-500\\/50').forEach(el => {
        el.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-500', 'dark:border-red-500/50');
    });
    field.querySelectorAll('.text-red-600, .text-red-400').forEach(el => {
        if (el.closest('ul')) el.closest('ul').remove();
    });
});
