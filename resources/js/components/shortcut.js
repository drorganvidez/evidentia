/**
 * Keyboard shortcut of <x-ui.search>: the key held in data-key focuses the field, unless the person is
 * typing somewhere else or the field is not rendered (the topbar form is hidden on the phone).
 *
 * Blade: <form x-data="shortcut" data-key="/" x-on:keydown.window="onKey"> with the field on x-ref="input".
 */
export default function shortcut() {
    return {
        onKey(event) {
            if (event.defaultPrevented || event.altKey || event.ctrlKey || event.metaKey) {
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
