import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { local } from 'laravel-vite-plugin/fonts';

export default defineConfig({
    build: {
        // Fonts and small images must stay files: font-src/img-src 'self' forbids data: URIs.
        assetsInlineLimit: 0,
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            // Self-hosted variable fonts (OFL, latin subset). The @fonts directive prints the
            // preload link and a nonce'd <style> with the @font-face rules and --font-ui-* variables.
            fonts: [
                local('Plus Jakarta Sans Variable', {
                    alias: 'ui-sans',
                    variants: [
                        {
                            src: 'resources/fonts/plus-jakarta-sans-latin-wght-normal.woff2',
                            weight: '200 800',
                        },
                    ],
                    display: 'swap',
                    preload: [{ weight: '200 800' }],
                    fallbacks: ['system-ui', 'sans-serif'],
                    optimizedFallbacks: true,
                }),
                local('JetBrains Mono Variable', {
                    alias: 'ui-mono',
                    variants: [
                        {
                            src: 'resources/fonts/jetbrains-mono-latin-wght-normal.woff2',
                            weight: '100 800',
                        },
                    ],
                    display: 'swap',
                    preload: false,
                    // fontaine cannot tell this family is monospace and would pick Arial; the
                    // metric-compatible fallback is declared by hand in resources/css/app.css.
                    fallbacks: ['"JetBrains Mono Variable fallback"', 'ui-monospace', 'monospace'],
                    optimizedFallbacks: false,
                }),
            ],
        }),
    ],
});
