// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

import assert from 'node:assert/strict';
import test from 'node:test';
import { initNavigation } from '../source/_assets/js/main.js';

test('navigation initializes and toggles accessibility state', () => {
    let handler;
    const attributes = new Map([['aria-expanded', 'false']]);
    const classes = new Set();

    const button = {
        addEventListener: (_event, callback) => {
            handler = callback;
        },
        getAttribute: (name) => attributes.get(name),
        setAttribute: (name, value) => attributes.set(name, value),
    };
    const nav = {
        classList: {
            toggle: (name, enabled) => (enabled ? classes.add(name) : classes.delete(name)),
        },
    };
    const documentRef = {
        querySelector: (selector) => (selector === '.nav-toggle' ? button : nav),
    };

    assert.equal(initNavigation(documentRef), true);
    handler();
    assert.equal(attributes.get('aria-expanded'), 'true');
    assert.equal(classes.has('is-open'), true);

    handler();
    assert.equal(attributes.get('aria-expanded'), 'false');
    assert.equal(classes.has('is-open'), false);
});

test('navigation safely skips pages without the menu controls', () => {
    const documentRef = { querySelector: () => null };
    assert.equal(initNavigation(documentRef), false);
});
