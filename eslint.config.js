import js from '@eslint/js';
import globals from 'globals';
import { defineConfig, globalIgnores } from 'eslint/config';

export default defineConfig([
    // node_modules/ and .git/ are ignored by default.
    globalIgnores([
        'public/',
        'vendor/',
        'storage/',
        'bootstrap/cache/',
        'docs/',
        'playwright-report/',
        'test-results/',
    ]),

    {
        files: ['**/*.{js,mjs}'],
        plugins: { js },
        extends: ['js/recommended'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: { ...globals.browser },
        },
    },

    // Inlined in <head> as a classic script: no modules and conservative syntax.
    {
        files: ['resources/js/theme-init.js'],
        languageOptions: {
            ecmaVersion: 2019,
            sourceType: 'script',
        },
    },

    // Files that run in Node, not in the browser.
    {
        files: ['*.config.js', 'scripts/**/*.mjs', 'tests/e2e/**/*.js'],
        languageOptions: {
            globals: { ...globals.node },
        },
    },
]);
