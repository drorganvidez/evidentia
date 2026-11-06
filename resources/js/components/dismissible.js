/**
 * Block that its own button closes (<x-ui.alert dismissible>). The button leaves with the block, so close()
 * hands the focus to the next control after it (or the previous one, or <main>) instead of letting it fall back
 * to the start of the page.
 *
 * Blade: <div x-data="dismissible" x-show="open"> ... <button x-on:click="close">Cerrar aviso</button></div>
 */
const CONTROLS = 'a[href], button, input, select, textarea, summary, [tabindex]';

function reachable(control) {
    return (
        control.tabIndex >= 0 &&
        !control.disabled &&
        !control.closest('[inert]') &&
        control.checkVisibility?.() !== false
    );
}

export default function dismissible() {
    return {
        open: true,

        close() {
            const root = this.$root;
            const focused = root.contains(document.activeElement);

            this.open = false;

            if (!focused) {
                return;
            }

            const controls = [...document.querySelectorAll(CONTROLS)].filter(
                (control) => !root.contains(control) && reachable(control),
            );
            const next = controls.find(
                (control) =>
                    root.compareDocumentPosition(control) & Node.DOCUMENT_POSITION_FOLLOWING,
            );
            const previous = controls.findLast(
                (control) =>
                    root.compareDocumentPosition(control) & Node.DOCUMENT_POSITION_PRECEDING,
            );

            (next ?? previous ?? document.getElementById('main'))?.focus();
        },
    };
}
