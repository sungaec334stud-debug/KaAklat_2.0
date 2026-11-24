// Progressive enhancement only. Forms, CSRF and authentication remain server-owned.
'use strict';
document.querySelectorAll('[data-password]').forEach((button) => {
    const field = document.getElementById(button.dataset.password);
    if (!field || field.type !== 'password') return;
    const showLabel = button.getAttribute('aria-label');
    button.hidden = false;
    button.addEventListener('click', () => {
        const reveal = field.type === 'password';
        field.type = reveal ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(reveal));
        const label = reveal ? showLabel.replace(/^Show /, 'Hide ') : showLabel;
        button.setAttribute('aria-label', label);
        button.setAttribute('title', label);
    });
});

// A cached pre-login entry must make a fresh GET, never replay a cached POST.
// Other pages and ordinary browser Back/Forward remain entirely native.
if (['/login.php', '/register.php'].includes(location.pathname)) {
    window.addEventListener('pageshow', event => {
        if (event.persisted) location.replace(location.href);
    });
}
