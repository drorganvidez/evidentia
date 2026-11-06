/**
 * Brings the current link of a scrolling pill of x-ui.tabs into view when the page loads, so that on the phone the
 * section named in the address is never hidden past the edge of the pill.
 *
 * Blade: <x-ui.tabs x-data="currentInView" label="Secciones del expediente" :items="$tabs" />.
 * Only the list scrolls, never the page, and nothing moves while the whole pill fits.
 */
export default function currentInView() {
    return {
        init() {
            const list = this.$root.querySelector('ul');
            const current = this.$root.querySelector('[aria-current="page"]');

            if (!list || !current || list.scrollWidth <= list.clientWidth) {
                return;
            }

            const offset = current.getBoundingClientRect().left - list.getBoundingClientRect().left;

            list.scrollLeft += offset - (list.clientWidth - current.offsetWidth) / 2;
        },
    };
}
