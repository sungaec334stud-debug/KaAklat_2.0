// The presentation enhancement must not replace or duplicate security-bearing forms.
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const script = fs.readFileSync(path.join(root, 'public/assets/workspace.js'), 'utf8');
assert(!/innerHTML|outerHTML|cloneNode|fetch\s*\(/.test(script), 'Use existing nodes and text, never clone forms or fetch private records');
assert(!/localStorage|sessionStorage|console\./.test(script), 'Do not persist or log private record context');
assert(script.includes('hashchange') && script.includes('.field-error'), 'Reveal the affected record for error links');
const css = fs.readFileSync(path.join(root, 'public/assets/workspace.css'), 'utf8');
assert(!/\.catalog-pagination|\.pagination-current|\.pagination-summary|\.pagination-gap/.test(css), 'Pagination appearance remains owned by the existing stylesheet');
console.log('Workspace presentation contract passed. Browser interaction checks remain separate.');
