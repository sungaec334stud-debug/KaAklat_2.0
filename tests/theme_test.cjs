// No dependencies: exercise the browser theme controller in a minimal DOM/storage harness.
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync(__dirname + '/../public/assets/theme.js', 'utf8');
let checks = 0;
function boot(saved, dark = false, storageFailure = false) {
    const listeners = {}, media = {matches: dark, addEventListener: (_, fn) => media.change = fn};
    const storage = {value: saved, getItem() {if (storageFailure) throw Error('blocked'); return this.value;}, setItem(key, value) {if (storageFailure) throw Error('blocked'); assert.equal(key, 'kaaklat.appearance'); this.value = value;}};
    const radios = ['system','light','dark'].map(value => ({value, checked: false, matches: selector => selector === '[name="appearance"]'}));
    const status = {textContent: ''}, fieldset = {disabled: true}, root = {dataset: {}};
    const doc = {documentElement: root, addEventListener: (name, fn) => listeners[name] = fn, querySelector: selector => selector === '#appearance-options' ? fieldset : selector === '#appearance-status' ? status : null, querySelectorAll: () => radios};
    vm.runInNewContext(source, {document: doc, window: {matchMedia: () => media, localStorage: storage}});
    listeners.DOMContentLoaded();
    return {root, storage, status, fieldset, radios, select(value) {listeners.change({target: radios.find(r => r.value === value)});}, media};
}
function check(fn) {fn(); checks++;}
const clean = boot(null);
check(() => assert.equal(clean.root.dataset.theme, 'light'));
check(() => assert.equal(clean.radios.find(r => r.checked).value, 'system'));
check(() => assert.equal(clean.fieldset.disabled, false));
clean.select('dark');
check(() => assert.equal(clean.root.dataset.theme, 'dark'));
check(() => assert.equal(clean.storage.value, 'dark'));
check(() => assert.equal(boot(clean.storage.value).root.dataset.theme, 'dark'));
clean.media.matches = true; clean.select('light'); clean.media.change();
check(() => assert.equal(clean.root.dataset.theme, 'light'));
clean.select('system');
check(() => assert.equal(clean.root.dataset.theme, 'dark'));
clean.media.matches = false; clean.media.change();
check(() => assert.equal(clean.root.dataset.theme, 'light'));
check(() => assert.equal(boot('<script>bad</script>', true).root.dataset.theme, 'dark'));
const blocked = boot(null, true, true); blocked.select('light');
check(() => assert.equal(blocked.root.dataset.theme, 'light'));
check(() => assert.match(blocked.status.textContent, /could not be saved/i));
console.log(`PASS: ${checks} theme preference, persistence, system and blocked-storage checks.`);
