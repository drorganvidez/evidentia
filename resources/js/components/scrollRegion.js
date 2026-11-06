/**
 * Horizontal scroll region of <x-ui.table>: focusable only while its content overflows, so the keyboard can
 * scroll a wide table (axe rule scrollable-region-focusable) without adding a tab stop to the narrow ones.
 *
 * Blade: <div x-data="scrollRegion" x-bind:tabindex="tabindex" x-bind:role="role" x-bind:aria-label="label" class="overflow-x-auto">.
 * A ResizeObserver on the region and on its content re-measures after every reflow (filter, viewport, fonts).
 */
export default function scrollRegion() {
    return {
        scrollable: false,
        observer: null,

        init() {
            this.observer = new ResizeObserver(() => this.measure());
            this.observer.observe(this.$root);

            if (this.$root.firstElementChild) {
                this.observer.observe(this.$root.firstElementChild);
            }

            this.measure();
        },

        measure() {
            this.scrollable = this.$root.scrollWidth > this.$root.clientWidth;
        },

        get tabindex() {
            return this.scrollable ? 0 : null;
        },

        get role() {
            return this.scrollable ? 'group' : null;
        },

        get label() {
            return this.scrollable ? 'Tabla desplazable' : null;
        },

        destroy() {
            this.observer?.disconnect();
        },
    };
}
