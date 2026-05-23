import '../css/app.css';

import Alpine from 'alpinejs';
import { createIcons } from 'lucide';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('ui', {
        sidebarOpen: false,
        dark: localStorage.getItem('taniai-dark') === '1',

        toggleDark() {
            this.dark = !this.dark;
            localStorage.setItem('taniai-dark', this.dark ? '1' : '0');
            document.documentElement.classList.toggle('dark', this.dark);
        },
    });
});

Alpine.start();

const renderIcons = () => {
    createIcons();
};

document.addEventListener('DOMContentLoaded', renderIcons);

renderIcons();

// dark mode init
if (localStorage.getItem('taniai-dark') === '1') {
    document.documentElement.classList.add('dark');
}
