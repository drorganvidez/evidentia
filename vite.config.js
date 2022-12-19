import { viteStaticCopy } from 'vite-plugin-static-copy'

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    server: {
        hmr: {
            host: 'localhost',
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/sass/app_light_mode.scss',
                'resources/sass/app_dark_mode.scss',
                'resources/js/app.js'
            ],
            refresh: true,
        }),
        viteStaticCopy({
            targets: [{
                src: 'resources/js/custom.js',
                dest: 'js'
            }]
        })
    ],
});