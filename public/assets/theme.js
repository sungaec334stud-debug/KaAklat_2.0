/* Shared appearance preference only. Never stores identity, credentials or account data. */
(() => {
    'use strict';
    const key = 'kaaklat.appearance';
    const choices = ['system', 'light', 'dark'];
    const scheme = window.matchMedia('(prefers-color-scheme: dark)');
    let preference = 'system';
    let buttons = [];
    let status = null;
    try {
        const saved = window.localStorage.getItem(key);
        if (choices.includes(saved)) preference = saved;
    } catch { /* System appearance remains usable when browser storage is blocked. */ }

    function apply() {
        const theme = preference === 'system' ? (scheme.matches ? 'dark' : 'light') : preference;
        document.documentElement.dataset.theme = theme;
        document.documentElement.dataset.themePreference = preference;
        const label = theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
        buttons.forEach(button => {
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
        });
    }
    apply();
    scheme.addEventListener('change', apply);

    window.addEventListener('storage', event => {
        if (event.key !== key && event.key !== null) return;
        preference = choices.includes(event.newValue) ? event.newValue : 'system';
        apply();
    });
    document.addEventListener('DOMContentLoaded', () => {
        buttons = [...document.querySelectorAll('[data-theme-toggle]')];
        status = document.querySelector('#appearance-status');
        buttons.forEach(button => {
            button.hidden = false;
            button.disabled = false;
            button.addEventListener('click', () => {
                preference = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
                apply();
                let message = (preference === 'dark' ? 'Dark' : 'Light') + ' mode enabled and saved in this browser.';
                try { window.localStorage.setItem(key, preference); }
                catch { message = 'Appearance updated for this page, but could not be saved. Your browser may be blocking storage.'; }
                if (status) status.textContent = message;
            });
        });
        apply();
        document.documentElement.classList.add('appearance-ready');
    });
})();
