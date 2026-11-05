/**
 * Light / dark / system theme switch.
 *
 * - persists the choice in localStorage (same key as theme-init.js)
 * - follows the system preference while mode === 'system'
 * - stays in sync across tabs (storage event)
 *
 * Blade: x-data="theme", x-on:click="select('dark')", x-bind:aria-pressed="is('dark') ? 'true' : 'false'"
 * Every access to window, document or localStorage happens here: the CSP build of Alpine
 * forbids globals inside attribute expressions.
 */
const STORAGE_KEY = 'evidentia.theme';
const MODES = ['light', 'dark', 'system'];
const media = window.matchMedia('(prefers-color-scheme: dark)');

function readStoredMode() {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);

        return MODES.includes(stored) ? stored : 'system';
    } catch {
        return 'system';
    }
}

function writeStoredMode(mode) {
    try {
        if (mode === 'system') {
            localStorage.removeItem(STORAGE_KEY);
        } else {
            localStorage.setItem(STORAGE_KEY, mode);
        }
    } catch {
        // Private mode or storage disabled: the theme lives only in this page.
    }
}

function applyToDocument(mode) {
    const resolved = mode === 'system' ? (media.matches ? 'dark' : 'light') : mode;

    document.documentElement.setAttribute('data-theme', resolved);
}

export default function theme() {
    let onMediaChange;
    let onStorage;

    return {
        mode: 'system',
        systemDark: media.matches,

        init() {
            this.mode = readStoredMode();
            applyToDocument(this.mode);

            onMediaChange = (event) => {
                this.systemDark = event.matches;

                if (this.mode === 'system') {
                    applyToDocument('system');
                }
            };

            onStorage = (event) => {
                if (event.key !== STORAGE_KEY) {
                    return;
                }

                this.mode = readStoredMode();
                applyToDocument(this.mode);
            };

            media.addEventListener('change', onMediaChange);
            window.addEventListener('storage', onStorage);
        },

        destroy() {
            media.removeEventListener('change', onMediaChange);
            window.removeEventListener('storage', onStorage);
        },

        /** Effective theme, 'light' or 'dark'. */
        get resolved() {
            return this.mode === 'system' ? (this.systemDark ? 'dark' : 'light') : this.mode;
        },

        get isDark() {
            return this.resolved === 'dark';
        },

        is(mode) {
            return this.mode === mode;
        },

        select(mode) {
            if (!MODES.includes(mode)) {
                return;
            }

            this.mode = mode;
            writeStoredMode(mode);
            applyToDocument(mode);
        },

        cycle() {
            this.select(MODES[(MODES.indexOf(this.mode) + 1) % MODES.length]);
        },
    };
}
