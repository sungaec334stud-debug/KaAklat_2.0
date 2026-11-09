/* Shared appearance preference only. Never stores identity, credentials or account data. */
(() => {
    'use strict';
    const key = 'kaaklat.appearance';
    const choices = ['system', 'light', 'dark'];
    const scheme = window.matchMedia('(prefers-color-scheme: dark)');
    let preference = 'system';
    try {
        const saved = window.localStorage.getItem(key);
        if (choices.includes(saved)) preference = saved;
    } catch { /* System appearance remains usable when browser storage is blocked. */ }

    function apply() {
        document.documentElement.dataset.theme = preference === 'system'
            ? (scheme.matches ? 'dark' : 'light') : preference;
        document.documentElement.dataset.themePreference = preference;
    }
    apply();
    scheme.addEventListener('change', apply);

    document.addEventListener('DOMContentLoaded', () => {
        const options = document.querySelector('#appearance-options');
        if (!options) return;
        const status = document.querySelector('#appearance-status');
        options.disabled = false;
        document.querySelectorAll('[name="appearance"]').forEach(radio => {
            radio.checked = radio.value === preference;
        });
        document.addEventListener('change', event => {
            if (!event.target.matches('[name="appearance"]') || !choices.includes(event.target.value)) return;
            preference = event.target.value;
            apply();
            try {
                window.localStorage.setItem(key, preference);
                status.textContent = 'Appearance updated and saved in this browser.';
            } catch {
                status.textContent = 'Appearance updated for this page, but could not be saved. Your browser may be blocking storage.';
            }
        });
    });
})();
