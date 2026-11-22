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

// Animate only the help body, keeping the native summary, focus and form actions intact.
document.querySelectorAll('.auth-main details.help').forEach((details) => {
    if (typeof details.animate !== 'function') return;
    const summary = details.querySelector(':scope > summary');
    if (!summary) return;
    const content = document.createElement('div');
    content.className = 'help-content';
    content.append(...[...details.childNodes].filter(node => node !== summary));
    details.append(content);
    const motion = window.matchMedia('(prefers-reduced-motion: no-preference)');
    let animation = null;
    let desiredOpen = details.open;

    function finish(open) {
        const previous = animation;
        animation = null;
        if (!open && content.contains(document.activeElement)) summary.focus({preventScroll: true});
        details.open = open;
        previous?.cancel();
        content.style.removeProperty('overflow');
    }

    summary.addEventListener('click', event => {
        if (!animation) desiredOpen = details.open;
        if (!motion.matches) return; // Native instant toggle for reduced motion.
        event.preventDefault();
        desiredOpen = !desiredOpen;
        // Closed native details can retain a child layout box behind hidden content.
        const from = details.open ? content.getBoundingClientRect().height : 0;
        animation?.cancel();
        details.open = true; // Keep content available until the closing animation finishes.
        const to = desiredOpen ? content.getBoundingClientRect().height : 0;
        content.style.overflow = 'hidden';
        const current = content.animate([{height: from + 'px'}, {height: to + 'px'}], {
            duration: 260,
            easing: 'cubic-bezier(.2,.7,.2,1)',
            fill: 'both'
        });
        animation = current;
        current.onfinish = () => { if (animation === current) finish(desiredOpen); };
    });
    // Changing motion preference or geometry must never leave a fixed-height panel.
    motion.addEventListener('change', () => { if (animation) finish(desiredOpen); });
    window.addEventListener('resize', () => { if (animation) finish(desiredOpen); });
    window.addEventListener('pagehide', () => { if (animation) finish(desiredOpen); });
});
