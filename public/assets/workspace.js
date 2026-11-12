// Optional presentation only. The original server forms remain the no-script fallback.
(() => {
    const page = document.querySelector('main > section.panel');
    if (!page) return;
    page.classList.add('workspace-page');
    const route = location.pathname;
    function watchCover(image) {
        image.addEventListener('error', () => { image.hidden = true; });
        if (image.complete && image.naturalWidth === 0) image.hidden = true;
    }
    page.querySelectorAll('.circulation-cover img').forEach(watchCover);
    const selector = ['/students.php'].includes(route) ? ':scope > article.student-card'
        : ['/staff.php', '/review_requests.php', '/pickups.php', '/returns.php'].includes(route)
            ? ':scope > article.management-section' : null;
    const records = selector ? [...page.querySelectorAll(selector)] : [];
    if (!records.length) return;

    const layout = document.createElement('div');
    layout.className = 'workspace-directory';
    const list = document.createElement('div');
    list.className = 'workspace-list';
    const inspector = document.createElement('section');
    inspector.className = 'workspace-inspector';
    inspector.id = 'workspace-inspector';
    inspector.setAttribute('aria-label', 'Selected record details and actions');
    inspector.hidden = true;
    const back = document.createElement('button');
    back.type = 'button';
    back.className = 'button-secondary workspace-back';
    back.textContent = 'Back to list';
    layout.append(list, inspector);
    records[0].before(layout);
    const entries = [];
    let selected = null;

    function select(entry, focus = false) {
        if (selected) {
            selected.details.append(selected.record);
            selected.details.open = false;
        }
        selected = entry;
        inspector.hidden = !entry;
        layout.classList.toggle('has-selection', !!entry);
        if (!entry) return;
        entry.details.open = true;
        inspector.append(back, entry.record);
        // A lazy image moved out of a closed disclosure may retain a suspended load.
        // The selected record is visible and intentionally opened: load its cover now.
        entry.record.querySelectorAll('.circulation-cover img').forEach(image => { image.loading = 'eager'; });
        const heading = entry.record.querySelector('h2, h3');
        if (heading && focus) {
            heading.tabIndex = -1;
            heading.focus();
        }
    }

    records.forEach(record => {
        const details = document.createElement('details');
        details.className = 'workspace-record';
        const summary = document.createElement('summary');
        summary.setAttribute('aria-controls', inspector.id);
        const mark = document.createElement('span');
        mark.setAttribute('aria-hidden', 'true');
        const title = record.querySelector('h2, h3')?.textContent.trim() || 'Record';
        const cover = record.querySelector('.circulation-cover');
        if (cover) {
            mark.className = 'circulation-cover';
            const source = cover.querySelector('img');
            if (source) {
                const image = document.createElement('img');
                image.src = source.getAttribute('src');
                image.alt = '';
                image.width = 48;
                image.height = 68;
                image.loading = 'lazy';
                mark.append(image);
                watchCover(image);
            }
            const fallback = document.createElement('span');
            fallback.className = 'circulation-cover-fallback';
            fallback.textContent = 'No cover';
            mark.append(fallback);
        } else {
            mark.className = 'workspace-monogram';
            mark.textContent = title.split(/\s+/).slice(0, 2).map(word => [...word][0]).join('');
        }
        const text = document.createElement('span');
        text.className = 'workspace-record-label';
        const name = document.createElement('strong');
        name.textContent = title;
        const meta = document.createElement('span');
        meta.textContent = record.querySelector('p')?.textContent.trim() || 'View details and available actions';
        const action = document.createElement('span');
        action.className = 'workspace-view';
        action.textContent = 'View →';
        text.append(name, meta);
        summary.append(mark, text, action);
        details.append(summary, record);
        list.append(details);
        const entry = { details, summary, record };
        entries.push(entry);
        summary.addEventListener('click', event => {
            event.preventDefault();
            select(entry, true);
        });
    });
    back.addEventListener('click', () => {
        const previous = selected;
        select(null);
        previous?.summary.focus();
    });

    function reveal(target) {
        if (!target) return;
        const entry = entries.find(item => item.record.contains(target));
        if (entry && selected !== entry) select(entry);
        for (let parent = target.parentElement; parent; parent = parent.parentElement) {
            if (parent.tagName === 'DETAILS') parent.open = true;
        }
    }
    function hashTarget() {
        try { return document.getElementById(decodeURIComponent(location.hash.slice(1))); }
        catch { return null; }
    }
    const invalidEntry = entries.find(entry => entry.record.querySelector('.field-error, [aria-invalid="true"]'));
    // Start narrow screens with the list, rather than a long first-record form.
    select(invalidEntry || (window.matchMedia('(min-width: 821px)').matches ? entries[0] : null));
    reveal(hashTarget());
    document.addEventListener('click', event => {
        const link = event.target.closest('a[href^="#"]');
        if (!link) return;
        try { reveal(document.getElementById(decodeURIComponent(link.hash.slice(1)))); }
        catch { /* Ignore malformed fragments; normal navigation remains available. */ }
    });
    window.addEventListener('hashchange', () => reveal(hashTarget()));
})();
