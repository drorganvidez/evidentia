/**
 * Single-key shortcuts of the interface (the "/" of the search), on by default; the person can turn them off
 * (WCAG 2.1.4). The choice is kept in localStorage and follows other tabs (storage event).
 *
 * Blade: x-bind:checked="$store.shortcuts.enabled" x-on:change="$store.shortcuts.toggle()" on a switch, or
 * x-bind:aria-checked="$store.shortcuts.enabled ? 'true' : 'false'" x-on:click="$store.shortcuts.toggle()" on a
 * menu item checkbox. shortcut.js reads enabled before acting on a key.
 */
const STORAGE_KEY = 'evidentia.shortcuts';

function readStored() {
    try {
        return localStorage.getItem(STORAGE_KEY) !== 'off';
    } catch {
        return true;
    }
}

function writeStored(enabled) {
    try {
        localStorage.setItem(STORAGE_KEY, enabled ? 'on' : 'off');
    } catch {
        // Private mode or storage disabled: the choice lives only in this page.
    }
}

export default {
    enabled: true,

    init() {
        this.enabled = readStored();

        window.addEventListener('storage', (event) => {
            if (event.key === STORAGE_KEY) {
                this.enabled = readStored();
            }
        });
    },

    toggle() {
        this.enabled = !this.enabled;
        writeStored(this.enabled);
    },
};
