/**
 * Trigger of a dialog that lives outside the x-data of that dialog: the menu button of the topbar opens the
 * drawer, which sits at the end of the body. The click sends the window event the dialog listens to, and a
 * MutationObserver on the open attribute of the dialog named by aria-controls keeps aria-expanded in sync however
 * the dialog closes (Escape, its close button, the backdrop or a link inside).
 *
 * Blade: <button x-data="dialogTrigger" data-event="ui-drawer" aria-controls="drawer" aria-haspopup="dialog"
 * aria-expanded="false" x-bind:aria-expanded="open ? 'true' : 'false'" x-on:click="show">, with
 * x-on:ui-drawer.window="show" on the x-ui.dialog of the drawer.
 */
export default function dialogTrigger() {
    return {
        open: false,
        observer: null,

        init() {
            const dialog = document.getElementById(this.$root.getAttribute('aria-controls'));

            if (!(dialog instanceof HTMLDialogElement)) {
                return;
            }

            this.open = dialog.open;
            this.observer = new MutationObserver(() => {
                this.open = dialog.open;
            });
            this.observer.observe(dialog, { attributes: true, attributeFilter: ['open'] });
        },

        destroy() {
            if (this.observer) {
                this.observer.disconnect();
            }
        },

        show() {
            this.$dispatch(this.$root.dataset.event);
        },
    };
}
