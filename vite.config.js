import jigsaw from '@tighten/jigsaw-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    base: '/assets/build/',
    plugins: [
        jigsaw({
            input: [
                'source/_assets/css/main.css',
                'source/_assets/js/main.js',
            ],
            refresh: true,
        }),
    ],
});
