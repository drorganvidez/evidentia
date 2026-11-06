import { afterRender } from '../alpine/afterRender';
import { announce } from '../alpine/announce';
import { normalize } from '../alpine/normalize';

/**
 * Single choice with a search box over a native <select> (<x-ui.field type="select">), after the APG editable
 * combobox with list autocomplete. The select stays the source of truth: the options and the chosen one are
 * read from it, every choice is written back to it with input and change events (so x-model, x-on:change and
 * the forms that depend on it keep working) and, without JS, it is the control itself. A change made to the
 * select from outside (a script, a test) is read back through its change event.
 *
 * The list is a manual popover in the top layer, pinned to the box from here: no card, dialog or table that
 * clips or stacks over its content can cover it.
 *
 * Blade: root x-data="selectField" with x-on:focusout="onFocusOut", x-on:click.outside="dismiss" and place on
 * the scroll (captured) and the resize of the window; x-ref="source" on the select (with x-on:change="read" and
 * x-on:focus="focusEntry"), x-ref="box" on the framed box, x-ref="entry" on the combobox input, x-ref="popup"
 * on the hidden popover="manual" and x-ref="list" on the listbox inside it. The placeholder option carries
 * data-placeholder and is never listed.
 */

/** The list shows up to 16rem of options of 44 px inside 12 px of padding, 4 px off the box and 8 px off the edge. */
const LIST_MAX = 256;
const OPTION_HEIGHT = 44;
const LIST_CHROME = 14;
const GAP = 4;
const EDGE = 8;
const ANNOUNCE_AFTER = 400;
const SUPPORTS_POPOVER =
    typeof HTMLElement !== 'undefined' && typeof HTMLElement.prototype.showPopover === 'function';

function results(count) {
    if (count === 0) {
        return 'Sin resultados';
    }

    return `${count} ${count === 1 ? 'resultado' : 'resultados'}`;
}

export default function selectField() {
    return {
        options: [],
        query: '',
        open: false,
        ready: false,
        activeIndex: -1,
        emptyText: '',
        pointing: false,
        timer: null,

        init() {
            const id = this.$refs.entry.id;

            this.options = Array.from(this.$refs.source.options)
                .map((element, index) => ({
                    index,
                    value: element.value,
                    label: element.text.trim(),
                    disabled: element.disabled,
                    placeholder: element.hasAttribute('data-placeholder'),
                    selected: false,
                    active: false,
                    id: `${id}-option-${index}`,
                    search: normalize(element.text),
                }))
                .filter((option) => !option.placeholder);
            this.emptyText = this.$refs.entry.getAttribute('placeholder') ?? '';
            this.read();
            this.ready = true;
            // x-model on the select sets its value once init() has run, without a change event
            this.$nextTick(() => this.read());
        },

        /** The label points to the combobox once it is there and to the select without JS. */
        get labelFor() {
            return this.ready ? this.$refs.entry.id : this.$refs.source.id;
        },

        /** While the list is open the chosen label waits in the placeholder of the emptied box. */
        get placeholderText() {
            const current = this.current();

            return this.open && current ? current.label : this.emptyText;
        },

        get filtered() {
            const term = normalize(this.query);
            const current = this.current();
            // The chosen label sitting in the box means "show everything"
            const needle = current && term === current.search ? '' : term;

            return needle === ''
                ? this.options
                : this.options.filter((option) => option.search.includes(needle));
        },

        get activeId() {
            const option = this.filtered[this.activeIndex];

            return this.open && option ? option.id : null;
        },

        current() {
            return this.options.find((option) => option.selected) ?? null;
        },

        /** Takes the chosen option from the select: the first load, x-model and any outside change. */
        read() {
            const index = this.$refs.source.selectedIndex;

            this.options.forEach((option) => {
                option.selected = option.index === index;
            });
            this.restore();
        },

        restore() {
            this.query = this.current()?.label ?? '';
        },

        /** The select is hidden once the combobox is there: whatever focuses it lands on the combobox. */
        focusEntry() {
            if (this.ready && !this.$refs.entry.disabled) {
                this.$refs.entry.focus();
            }
        },

        onPointerDown() {
            this.pointing = true;
        },

        /** From the keyboard the label comes selected, so the first key pressed starts a new search. */
        onFocus() {
            if (!this.pointing) {
                this.$refs.entry.select();
            }
        },

        /** A click or a tap opens the list with the box emptied for a search, or closes it. */
        toggle() {
            this.pointing = false;

            if (this.open) {
                this.dismiss();

                return;
            }

            this.query = '';
            this.show();
        },

        show() {
            if (this.open || this.$refs.entry.disabled) {
                return;
            }

            const popup = this.$refs.popup;

            this.open = true;
            popup.hidden = false;

            if (SUPPORTS_POPOVER && !popup.matches(':popover-open')) {
                popup.showPopover();
            }

            this.place();
            this.setActive(this.filtered.findIndex((option) => option.selected));
        },

        hide() {
            const popup = this.$refs.popup;

            this.open = false;

            if (SUPPORTS_POPOVER && popup.matches(':popover-open')) {
                popup.hidePopover();
            }

            popup.hidden = true;
            this.setActive(-1);
        },

        /**
         * Pins the list in the top layer to the box, below it or, when it does not fit there and there is more
         * room above, over it (anchored by its bottom, so the search can shorten it), and fits its height to
         * the room. Runs on open, on scroll and on resize.
         */
        place() {
            if (!this.open) {
                return;
            }

            const box = this.$refs.box.getBoundingClientRect();
            const viewport = document.documentElement.clientHeight;
            const below = viewport - box.bottom - GAP;
            const above = box.top - GAP;
            const wanted =
                Math.min(LIST_MAX, Math.max(this.options.length, 1) * OPTION_HEIGHT) + LIST_CHROME;
            const up = below < wanted && above > below;
            const room = (up ? above : below) - LIST_CHROME - EDGE;
            const style = this.$refs.popup.style;

            this.$refs.list.style.maxHeight = `${Math.max(OPTION_HEIGHT * 2, Math.min(LIST_MAX, room))}px`;
            style.left = `${box.left}px`;
            style.width = `${box.width}px`;
            style.top = up ? 'auto' : `${box.bottom + GAP}px`;
            style.bottom = up ? `${viewport - box.top + GAP}px` : 'auto';
        },

        /** Closes without choosing and puts the chosen label back. */
        dismiss() {
            if (this.open) {
                this.hide();
                this.restore();
            }
        },

        onInput(event) {
            this.query = event.target.value;
            this.show();

            const list = this.filtered;

            this.setActive(
                this.query.trim() === ''
                    ? list.findIndex((option) => option.selected)
                    : list.findIndex((option) => !option.disabled),
            );

            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                announce(this.$store.notices, results(this.filtered.length), 'status');
            }, ANNOUNCE_AFTER);
        },

        /** The pointer marks an option where it already is: only the keyboard scrolls the list. */
        setActive(index, scroll = true) {
            this.options.forEach((option) => {
                if (option.active) {
                    option.active = false;
                }
            });
            this.activeIndex = index;

            const option = this.filtered[index];

            if (option) {
                option.active = true;

                if (scroll) {
                    // The list may have just opened: x-show shows it on the next frame
                    this.$nextTick(() => afterRender(() => this.reveal(option.id)));
                }
            }
        },

        /** Scrolls the list, and only the list, until the active option is in sight. */
        reveal(id) {
            const list = this.$refs.list;
            const item = document.getElementById(id);

            if (!item || !list.contains(item)) {
                return;
            }

            if (item.offsetTop < list.scrollTop) {
                list.scrollTop = item.offsetTop;
            } else if (item.offsetTop + item.offsetHeight > list.scrollTop + list.clientHeight) {
                list.scrollTop = item.offsetTop + item.offsetHeight - list.clientHeight;
            }
        },

        activate(option) {
            const index = this.filtered.indexOf(option);

            if (index !== -1 && !option.disabled) {
                this.setActive(index, false);
            }
        },

        /** Next enabled option in the direction of step; at either end the active one stays. */
        move(step) {
            const list = this.filtered;

            for (
                let index = this.activeIndex + step;
                index >= 0 && index < list.length;
                index += step
            ) {
                if (!list[index].disabled) {
                    this.setActive(index);

                    return;
                }
            }
        },

        /** First (step 1) or last (step -1) enabled option. */
        edge(step) {
            const list = this.filtered;
            const index =
                step > 0
                    ? list.findIndex((option) => !option.disabled)
                    : list.findLastIndex((option) => !option.disabled);

            if (index !== -1) {
                this.setActive(index);
            }
        },

        choose(option) {
            if (option.disabled) {
                return;
            }

            const source = this.$refs.source;
            const changed = source.selectedIndex !== option.index;

            source.selectedIndex = option.index;
            this.options.forEach((item) => {
                item.selected = item.index === option.index;
            });
            this.query = option.label;
            this.hide();

            if (changed) {
                source.dispatchEvent(new Event('input', { bubbles: true }));
                source.dispatchEvent(new Event('change', { bubbles: true }));
            }
        },

        onKeydown(event) {
            switch (event.key) {
                case 'ArrowDown':
                case 'ArrowUp': {
                    const step = event.key === 'ArrowDown' ? 1 : -1;

                    event.preventDefault();

                    if (!this.open) {
                        // Opens on the chosen option, or on the first or the last without one
                        this.show();
                    } else if (this.activeIndex !== -1) {
                        this.move(step);
                    }

                    if (this.activeIndex === -1 && !event.altKey) {
                        this.edge(step);
                    }
                    break;
                }
                case 'Home':
                case 'End':
                    if (this.open) {
                        event.preventDefault();
                        this.edge(event.key === 'Home' ? 1 : -1);
                    }
                    break;
                case 'Enter':
                    if (this.open) {
                        event.preventDefault();

                        if (this.filtered[this.activeIndex]) {
                            this.choose(this.filtered[this.activeIndex]);
                        }
                    }
                    break;
                case 'Escape':
                    // Inside a dialog the first Escape closes the list, not the dialog
                    if (this.open || this.query !== (this.current()?.label ?? '')) {
                        event.preventDefault();
                        event.stopPropagation();
                        this.hide();
                        this.restore();
                        this.$refs.entry.focus();
                        this.$nextTick(() => this.$refs.entry.select());
                    }
                    break;
                case 'Tab':
                    this.dismiss();
                    break;
                default:
                    break;
            }
        },

        onFocusOut(event) {
            if (event.relatedTarget && this.$root.contains(event.relatedTarget)) {
                return;
            }

            this.hide();
            this.restore();
        },
    };
}
