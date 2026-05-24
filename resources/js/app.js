import '../css/app.css';

import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;

/**
 * Render Lucide icons safely (NO LOOP, NO OBSERVER)
 */
window.renderIcons = function () {
    createIcons({ icons });
};

/**
 * Alpine Store
 */
document.addEventListener('alpine:init', () => {
    Alpine.store('ui', {
        sidebarOpen: false,

        dark: localStorage.getItem('taniai-dark') === '1',

        toggleDark() {
            this.dark = !this.dark;

            localStorage.setItem('taniai-dark', this.dark ? '1' : '0');

            document.documentElement.classList.toggle('dark', this.dark);

            // re-render icons after theme change
            setTimeout(() => window.renderIcons(), 50);
        },
    });
});

/**
 * Start Alpine
 */
Alpine.start();

/**
 * Render icons once after page load
 */
document.addEventListener('DOMContentLoaded', () => {
    window.renderIcons();
});

/**
 * Apply dark mode immediately before render
 */
if (localStorage.getItem('taniai-dark') === '1') {
    document.documentElement.classList.add('dark');
}
