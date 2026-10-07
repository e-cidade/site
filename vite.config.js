// SPDX-FileCopyrightText: 2026 e-Cidade community
// SPDX-License-Identifier: AGPL-3.0-or-later

import jigsaw from '@tighten/jigsaw-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    base: '/assets/build/',
    plugins: [
        jigsaw({
            input: [
                'source/_assets/scss/main.scss',
                'source/_assets/js/main.js',
            ],
            refresh: true,
        }),
    ],
});
