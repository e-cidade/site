// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

export function initNavigation(documentRef = document) {
    const button = documentRef.querySelector('.nav-toggle');
    const nav = documentRef.querySelector('#main-nav');

    if (!button || !nav) {
        return false;
    }

    button.addEventListener('click', () => {
        const expanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        nav.classList.toggle('is-open', !expanded);
    });

    return true;
}

if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => initNavigation(document));
}
