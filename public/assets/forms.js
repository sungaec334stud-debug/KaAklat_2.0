// Progressive enhancement only: every security/validation rule remains on the server.
const summary = document.querySelector('.error-summary, [role="alert"]');
if (summary) {
    summary.setAttribute('tabindex', '-1');
    summary.focus();
}
document.querySelectorAll('.field-error').forEach(error => {
    const field = document.getElementById(error.id.replace(/-error$/, ''));
    if (!field) return;
    field.setAttribute('aria-invalid', 'true');
    field.setAttribute('aria-describedby', [field.getAttribute('aria-describedby'), error.id].filter(Boolean).join(' '));
});

// Make native constraints understandable on all existing forms, including novalidate
// forms. Server validation remains authoritative, including image and policy checks.
let generatedFieldId = 0;
document.querySelectorAll('form[method="post"]').forEach(form => {
    form.addEventListener('submit', event => {
        const reject = event.submitter?.value === 'reject';
        const invalid = [...form.querySelectorAll('input, select, textarea')].filter(field => {
            if (reject && field.name === 'days') return false;
            return field.willValidate && !field.validity.valid;
        });
        form.querySelectorAll('[data-client-error]').forEach(node => node.remove());
        form.querySelectorAll('[data-client-invalid]').forEach(field => {
            field.removeAttribute('aria-invalid');
            const ids = (field.getAttribute('aria-describedby') || '').split(' ').filter(id => !id.endsWith('-client-error'));
            if (ids.length) field.setAttribute('aria-describedby', ids.join(' '));
            else field.removeAttribute('aria-describedby');
            field.removeAttribute('data-client-invalid');
        });
        if (!invalid.length) return;
        event.preventDefault();
        const box = document.createElement('div');
        box.className = 'flash flash-error error-summary';
        box.dataset.clientError = 'true';
        box.tabIndex = -1;
        box.setAttribute('role', 'alert');
        const heading = document.createElement('strong');
        heading.textContent = 'Please check the highlighted fields';
        box.append(heading);
        const list = document.createElement('ul');
        invalid.forEach((field, index) => {
            if (!field.id) {
                do { field.id = 'kaaklat-field-' + (++generatedFieldId); } while (document.querySelectorAll('#' + field.id).length > 1);
            }
            const error = document.createElement('p');
            error.className = 'field-error';
            error.id = field.id + '-client-error';
            error.dataset.clientError = 'true';
            error.textContent = field.validationMessage;
            field.after(error);
            field.setAttribute('aria-invalid', 'true');
            field.dataset.clientInvalid = 'true';
            field.setAttribute('aria-describedby', [field.getAttribute('aria-describedby'), error.id].filter(Boolean).join(' '));
            const item = document.createElement('li');
            const link = document.createElement('a');
            link.href = '#' + field.id;
            link.textContent = (field.labels?.[0]?.textContent.trim() || 'Field') + ': ' + field.validationMessage;
            item.append(link);
            list.append(item);
        });
        box.append(list);
        form.prepend(box);
        box.focus();
    });
    // Prevent browser popups from hiding the linked error summary.
    form.noValidate = true;
});
