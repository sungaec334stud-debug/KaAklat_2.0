// Progressive navigation only. The server remains the owner of identity and permitted links.
(() => {
    const trigger = document.getElementById('navigation-toggle');
    const drawer = document.getElementById('navigation-drawer');
    const header = document.querySelector('.is-authenticated .site-header');
    const bar = document.querySelector('.mobile-navigation-bar');
    if (!trigger || !drawer || !header || !bar || typeof drawer.showModal !== 'function') return;

    const desktop = window.matchMedia('(min-width: 1100px)');
    const placeholder = document.createComment('Desktop navigation position');
    header.before(placeholder);
    const closeButton = drawer.querySelector('.navigation-close');
    function closedState() {
        document.documentElement.classList.remove('navigation-open');
        trigger.setAttribute('aria-expanded', 'false');
    }
    function closeDrawer(restoreFocus = true) {
        if (drawer.open) drawer.close();
        closedState();
        if (restoreFocus && !desktop.matches) trigger.focus({preventScroll: true});
    }
    function resizeNavigation() {
        const hadFocus = drawer.contains(document.activeElement);
        closeDrawer(false);
        if (desktop.matches) {
            placeholder.after(header);
            if (hadFocus) header.querySelector('[aria-current="page"]')?.focus({preventScroll: true});
        } else {
            drawer.append(header);
        }
    }
    trigger.addEventListener('click', () => {
        if (desktop.matches || drawer.open) return;
        drawer.showModal();
        document.documentElement.classList.add('navigation-open');
        trigger.setAttribute('aria-expanded', 'true');
        (header.querySelector('[aria-current="page"]') || closeButton).focus({preventScroll: true});
    });
    closeButton.addEventListener('click', () => closeDrawer());
    drawer.addEventListener('cancel', event => {
        event.preventDefault();
        closeDrawer();
    });
    drawer.addEventListener('close', closedState);
    drawer.addEventListener('click', event => {
        const rect = drawer.getBoundingClientRect();
        if (event.target === drawer && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) closeDrawer();
        // Native links navigate normally. No fetching, route rewriting or form mutation.
        if (event.target.closest('a[href]')) closeDrawer(false);
    });
    drawer.addEventListener('keydown', event => {
        if (event.key !== 'Tab' || !drawer.open) return;
        const stops = [...drawer.querySelectorAll('button:not([disabled]), a[href]')]
            .filter(item => item.getClientRects().length > 0);
        const first = stops[0];
        const last = stops[stops.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
    desktop.addEventListener('change', resizeNavigation);
    window.addEventListener('pagehide', () => closeDrawer(false));
    window.addEventListener('pageshow', resizeNavigation);
    resizeNavigation();
    document.body.classList.add('navigation-enhanced');
    bar.hidden = false;
})();
