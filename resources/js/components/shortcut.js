/**
 * Keyboard shortcut of <x-ui.search>: the key held in data-key focuses the field, unless the person turned the
 * shortcuts off ($store.shortcuts), is typing somewhere else or the field is not rendered (the topbar form is
 * hidden on the phone).
 *
 * Blade: <form x-data="shortcut" data-key="/" x-on:keydown.window="onKey"> with the field on x-ref="input"
 * x-bind:aria-keyshortcuts="keys" and the hint on x-show="enabled".
 */
export default function shortcut() {
    return {
        /** Without the store (a page that does not register it) the shortcut stays on. */
        get enabled() {
            const store = this.$store.shortcuts;

            return store === undefined || store.enabled;
        },

        /** aria-keyshortcuts of the field: the key while the shortcut works, nothing once it is off. */
        get keys() {
            return this.enabled ? this.$root.dataset.key : null;
        },

        onKey(event) {
            if (
                !this.enabled ||
                event.defaultPrevented ||
                event.altKey ||
                event.ctrlKey ||
                event.metaKey
            ) {
                return;
            }

            if (event.key !== this.$root.dataset.key) {
                return;
            }

            const target = event.target;

            if (
                target instanceof Element &&
                (target.isContentEditable || target.closest('input, textarea, select'))
            ) {
                return;
            }

            const input = this.$refs.input;

            if (!input || input.getClientRects().length === 0) {
                return;
            }

            event.preventDefault();
            input.focus();
            input.select();
        },
    };
}
