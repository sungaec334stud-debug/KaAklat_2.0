// Observe real page navigation. Native links, forms and browser history remain owners.
(() => {
    const root = document.documentElement;
    const motion = window.matchMedia('(prefers-reduced-motion: no-preference)');
    const pages = ['/', '/index.php', '/login.php', '/register.php', '/about.php', '/reset_password.php', '/catalog.php', '/requests.php', '/profile.php', '/review_requests.php', '/pickups.php', '/returns.php', '/manage_books.php', '/students.php', '/staff.php', '/audit.php', '/recover_account.php', '/logout.php'];
    let pending = false;
    let activeTransition = null;
    function clearPending() {
        pending = false;
        root.classList.remove('page-navigation-pending');
    }
    document.addEventListener('click', event => {
        clearPending();
        if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        const link = event.target.closest?.('a[href]');
        if (!link || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
        const target = new URL(link.href, location.href);
        if (target.origin !== location.origin || !pages.includes(target.pathname)) return;
        if (target.pathname === location.pathname && target.search === location.search) return;
        pending = true;
        if (motion.matches) root.classList.add('page-navigation-pending');
        // Deliberately no preventDefault, timer, fetch, location assignment or input storage.
    });
    document.addEventListener('submit', clearPending);
    document.addEventListener('keydown', event => { if (event.key === 'Escape') clearPending(); });
    window.addEventListener('pageshow', clearPending);
    motion.addEventListener('change', () => {
        clearPending();
        if (!motion.matches) activeTransition?.skipTransition();
    });
    window.addEventListener('pageswap', event => {
        if (!event.viewTransition) return;
        event.viewTransition.ready.catch(() => {}); // A deliberately skipped transition may reject ready.
        if (!pending || !motion.matches) event.viewTransition.skipTransition();
    });
    window.addEventListener('pagereveal', event => {
        if (!event.viewTransition) return;
        event.viewTransition.ready.catch(() => {});
        if (!motion.matches) { event.viewTransition.skipTransition(); return; }
        activeTransition = event.viewTransition;
        // Keep this document's entrance suppressed after the crossfade, not just during it.
        root.classList.add('page-transition-arrived');
        root.classList.add('page-transition-active');
        const finished = () => {
            if (activeTransition !== event.viewTransition) return;
            activeTransition = null;
            root.classList.remove('page-transition-active');
        };
        event.viewTransition.finished.then(finished, finished);
    });
})();
