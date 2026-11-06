/**
 * Clickable row of <x-ui.table.row :href="...">: the whole row follows the link held in data-href.
 *
 * Blade: <tr x-data="rowLink" data-href="/evidence/33" x-on:click="go" x-on:auxclick="go">.
 * A pointer enhancement only: the link inside the primary cell remains the keyboard path, so the
 * row never takes a tabindex. The click is ignored when it already did something (defaultPrevented),
 * when it lands on a control of the row, or when the user has just selected text. Ctrl, Meta, Shift
 * and the middle button open the destination in a new tab.
 */
const INTERACTIVE =
    'a, button, input, label, select, textarea, summary, dialog, [role="menu"], [role="menuitem"]';

function hasSelection() {
    const selection = window.getSelection();

    return selection !== null && selection.type === 'Range' && selection.toString().trim() !== '';
}

export default function rowLink() {
    return {
        go(event) {
            if (event.defaultPrevented) {
                return;
            }

            if (event.type === 'auxclick' && event.button !== 1) {
                return;
            }

            if (event.target.closest(INTERACTIVE) || hasSelection()) {
                return;
            }

            const href = this.$root.dataset.href;

            if (!href) {
                return;
            }

            if (event.type === 'auxclick' || event.ctrlKey || event.metaKey || event.shiftKey) {
                window.open(href, '_blank', 'noopener');

                return;
            }

            window.location.assign(href);
        },
    };
}
