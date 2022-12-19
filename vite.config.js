import { viteStaticCopy } from 'vite-plugin-static-copy'

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        viteStaticCopy({
            targets: [{
                    src: 'resources/js/custom.js',
                    dest: 'js/custom'
                }
            ]
        })
    ],
});