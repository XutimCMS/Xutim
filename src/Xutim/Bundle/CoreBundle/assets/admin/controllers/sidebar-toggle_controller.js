import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['sidebar'];

    toggle() {
        const sidebar = this.sidebarTarget || document.getElementById('admin-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const isOpen = !sidebar.classList.contains('-translate-x-full');
        sidebar.classList.toggle('-translate-x-full');
        backdrop.classList.toggle('hidden', isOpen);
    }

    close() {
        const sidebar = this.sidebarTarget || document.getElementById('admin-sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
    }
}
