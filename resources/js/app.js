import '../css/app.css';
import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';

window.Alpine = Alpine;

// ── Icon rendering ─────────────────────────────────────────────────────────────
let iconTimer = null;
window.renderIcons = () => createIcons({ icons });
window.renderIconsDebounced = () => {
    clearTimeout(iconTimer);
    iconTimer = setTimeout(() => window.renderIcons(), 60);
};

// ── Alpine global store ────────────────────────────────────────────────────────
document.addEventListener('alpine:init', () => {
    Alpine.store('ui', {
        sidebarOpen: false,
    });
});

Alpine.start();

// ── Boot ───────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    window.renderIcons();

    // Re-render icons when DOM changes (modals, x-show transitions)
    const obs = new MutationObserver(() => window.renderIconsDebounced());
    obs.observe(document.body, { childList: true, subtree: true });
});
