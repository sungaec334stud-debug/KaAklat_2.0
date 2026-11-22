// Shared progressive disclosure motion. Native summaries, forms and server state stay authoritative.
(() => {
    const motion = window.matchMedia('(prefers-reduced-motion: no-preference)');
    const panels = [];
    document.querySelectorAll('details:not(.workspace-record)').forEach(details => {
        if (typeof details.animate !== 'function') return;
        const summary = details.querySelector(':scope > summary');
        if (!summary) return;
        const content = document.createElement('div');
        content.className = 'disclosure-content help-content';
        content.append(...[...details.childNodes].filter(node => node !== summary));
        details.append(content);
        details.classList.add('disclosure-enhanced');
        details.dataset.disclosureOpen = String(details.open);
        let animation = null;
        let desiredOpen = details.open;

        function finish(open) {
            const previous = animation;
            animation = null;
            if (!open && content.contains(document.activeElement)) summary.focus({preventScroll: true});
            details.open = open;
            desiredOpen = open;
            details.dataset.disclosureOpen = String(open);
            previous?.cancel();
            content.style.removeProperty('overflow');
        }
        summary.addEventListener('click', event => {
            // Links/controls inside a summary keep their own native action.
            if (event.defaultPrevented || event.target.closest('a,button,input,select,textarea')) return;
            if (!animation) desiredOpen = details.open;
            if (!motion.matches) return;
            event.preventDefault();
            desiredOpen = !desiredOpen;
            const from = details.open ? content.getBoundingClientRect().height : 0;
            animation?.cancel();
            details.open = true;
            details.dataset.disclosureOpen = String(desiredOpen);
            const to = desiredOpen ? content.getBoundingClientRect().height : 0;
            content.style.overflow = 'hidden';
            const current = content.animate([{height: from + 'px'}, {height: to + 'px'}], {
                duration: 260, easing: 'cubic-bezier(.2,.7,.2,1)', fill: 'both'
            });
            animation = current;
            current.onfinish = () => { if (animation === current) finish(desiredOpen); };
        });
        // External hash/error reveals and native reduced-motion toggles remain in sync.
        details.addEventListener('toggle', () => {
            if (animation && !details.open) finish(false);
            else if (!animation) details.dataset.disclosureOpen = String(details.open);
        });
        panels.push({details, content, finish, settle: () => { if (animation) finish(desiredOpen); }});
    });
    function settle() { panels.forEach(panel => panel.settle()); }
    motion.addEventListener('change', settle);
    window.addEventListener('resize', settle);
    window.addEventListener('pagehide', settle);
    document.addEventListener('invalid', event => {
        panels.forEach(panel => { if (panel.content.contains(event.target)) panel.finish(true); });
    }, true);
})();
