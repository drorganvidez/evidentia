/**
 * Menu button of <x-ui.menu> (APG pattern): one tab stop on the trigger, roving focus with the arrows,
 * Home, End and type-ahead inside, Escape returns the focus to the trigger, Tab and an outside click close it.
 *
 * Blade: <div x-data="menu"> with the trigger on x-ref="button" x-on:click="toggle"
 * x-on:keydown.down.prevent="openAndFocusFirst", and the list on x-ref="menu" hidden x-bind:hidden="!open"
 * x-on:keydown="onKeydown" x-on:click.outside="onOutsideClick" x-on:click="onItemClick" x-on:focusout="onFocusOut".
 * The list toggles the hidden attribute (applied in the same flush) rather than x-show, whose display change
 * waits for the next animation frame and would race the focus of the first item; in exchange the outside
 * click handler has to ignore the trigger itself, which already reads as "outside" once the list is shown.
 */
export default function menu() {
    return {
        open: false,

        /** Enabled menu items, in DOM order. */
        items() {
            return Array.from(this.$refs.menu.querySelectorAll('[role="menuitem"]')).filter(
                (item) => !item.disabled && item.getAttribute('aria-disabled') !== 'true',
            );
        },

        show() {
            this.open = true;
        },

        toggle() {
            if (this.open) {
                this.close(true);
            } else {
                this.show();
            }
        },

        openAndFocusFirst() {
            this.show();
            this.$nextTick(() => this.focusItem(0));
        },

        openAndFocusLast() {
            this.show();
            this.$nextTick(() => this.focusItem(-1));
        },

        close(returnFocus = true) {
            if (!this.open) {
                return;
            }

            this.open = false;

            if (returnFocus && this.$refs.button) {
                this.$refs.button.focus();
            }
        },

        /** Wraps at both ends; -1 is the last item. */
        focusItem(index) {
            const items = this.items();

            if (items.length === 0) {
                return;
            }

            items[((index % items.length) + items.length) % items.length].focus();
        },

        onKeydown(event) {
            const items = this.items();
            const current = items.indexOf(document.activeElement);

            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault();
                    this.focusItem(current + 1);

                    return;
                case 'ArrowUp':
                    event.preventDefault();
                    this.focusItem(current - 1);

                    return;
                case 'Home':
                    event.preventDefault();
                    this.focusItem(0);

                    return;
                case 'End':
                    event.preventDefault();
                    this.focusItem(-1);

                    return;
                case 'Tab':
                    // Not prevented: the focus follows its natural course from here
                    this.close(false);

                    return;
                default:
                    break;
            }

            if (event.key.length !== 1 || event.altKey || event.ctrlKey || event.metaKey) {
                return;
            }

            // Type-ahead: the next item whose text starts with the key, wrapping around
            const key = event.key.toLowerCase();

            for (let step = 1; step <= items.length; step++) {
                const item = items[(current + step) % items.length];

                if (item.textContent.trim().toLowerCase().startsWith(key)) {
                    event.preventDefault();
                    item.focus();

                    return;
                }
            }
        },

        /** Activating an item closes the menu; links and forms carry on with their own behaviour. */
        onItemClick(event) {
            if (event.target.closest('[role="menuitem"]')) {
                this.close(true);
            }
        },

        /** The trigger sits outside the list but inside the root: its own click must not close what it opened. */
        onOutsideClick(event) {
            if (!this.$root.contains(event.target)) {
                this.close(false);
            }
        },

        onFocusOut(event) {
            if (!this.$root.contains(event.relatedTarget)) {
                this.close(false);
            }
        },
    };
}
