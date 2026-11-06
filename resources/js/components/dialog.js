/**
 * Native <dialog> behaviour of <x-ui.dialog>: showModal() gives the top layer, the inert page, Escape and the
 * return of the focus for free; this module only keeps the trigger's aria-expanded in sync.
 *
 * Blade: <div x-data="dialog" [data-open]> with the trigger on x-on:click="show" and the dialog on
 * x-ref="dialog" x-on:close="onClose" x-on:click="onBackdropClick". data-open reopens it on load. The alert
 * variant leaves out the backdrop click and closedby: a destructive confirmation has no light dismiss.
 */
export default function dialog() {
    return {
        open: false,

        init() {
            if (this.$root.hasAttribute('data-open')) {
                // The refs of the children are registered after init()
                this.$nextTick(() => this.show());
            }
        },

        show() {
            const dialog = this.$refs.dialog;

            if (!dialog.open) {
                dialog.showModal();
            }

            this.open = true;
        },

        close() {
            this.$refs.dialog.close();
        },

        /** Escape and form method="dialog" close the element without passing through close() */
        onClose() {
            this.open = false;
        },

        /** The dialog has no padding, so a click whose target is the dialog itself landed on the backdrop */
        onBackdropClick(event) {
            if (event.target === this.$refs.dialog) {
                this.close();
            }
        },
    };
}
