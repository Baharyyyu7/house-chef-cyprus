import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

test('public startup registers ingredient and cooking components before Alpine scans the DOM', () => {
    const listeners = new Map();
    const components = new Map();
    let atStart;
    const document = {
        body: { dataset: { alpineOwner: 'app' } },
        querySelector: () => null,
        querySelectorAll: () => [],
        addEventListener(name, callback) {
            listeners.set(name, [...(listeners.get(name) || []), callback]);
        },
    };
    const Alpine = {
        data: (name, factory) => components.set(name, factory),
        start() {
            for (const callback of listeners.get('alpine:init') || []) callback();
            atStart = [...components.keys()];
        },
    };
    const window = { addEventListener() {} };
    // Only library imports are substituted; execute the actual bootstrap and registrations.
    const source = readFileSync(new URL('../../resources/js/app.js', import.meta.url), 'utf8')
        .replace(/^import .*;$/gm, '');
    runInNewContext(source, { document, window, Alpine, Chart: { register() {} }, registerables: [] });
    assert.ok(atStart.includes('hcIngredientPicker'));
    assert.ok(atStart.includes('hcRecipeSteps'));
    const picker = components.get('hcIngredientPicker')([{ id: 'onion', name: 'Soğan', search: 'sogan' }], []);
    picker.query = 'soğan';
    picker.commit();
    assert.deepEqual([...picker.selected], ['onion']);
});
