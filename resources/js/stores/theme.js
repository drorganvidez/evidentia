/**
 * Light / dark / system theme, shared by every switch on the page (Alpine store).
 *
 * - 'light' and 'dark' are written to data-theme on <html> and persisted in localStorage
 * - 'system' removes the attribute and lets the CSS media query decide, so it works without JS
 * - the theme-color metas follow the explicit choice and go back to their media values in system mode
 * - stays in sync across tabs (storage event)
 *
 * Blade: x-data on the group, then x-on:click="$store.theme.select('dark')" and
 * x-bind:aria-pressed="$store.theme.is('dark') ? 'true' : 'false'". Every access to window,
 * document or localStorage happens here: the CSP build of Alpine forbids globals in attributes.
 * The storage key, the attribute and the colours must match resources/js/theme-init.js.
 */
const STORAGE_KEY = 'evidentia.theme';
const MODES = ['light', 'dark', 'system'];
const COLORS = { light: '#f3f7fe', dark: '#151922' };
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
    const root = document.documentElement;

    if (mode === 'system') {
        root.removeAttribute('data-theme');
    } else {
        root.setAttribute('data-theme', mode);
    }

    document.querySelectorAll('meta[name="theme-color"]').forEach((meta) => {
        const own = meta.media.includes('dark') ? COLORS.dark : COLORS.light;

        meta.setAttribute('content', mode === 'system' ? own : COLORS[mode]);
    });
}

export default {
    mode: 'system',
    systemDark: media.matches,

    init() {
        this.mode = readStoredMode();
        applyToDocument(this.mode);

        media.addEventListener('change', (event) => {
            this.systemDark = event.matches;
        });

        window.addEventListener('storage', (event) => {
            if (event.key !== STORAGE_KEY) {
                return;
            }

            this.mode = readStoredMode();
            applyToDocument(this.mode);
        });
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
};
