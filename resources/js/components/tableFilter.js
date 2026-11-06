/**
 * Quick filter of <x-ui.table filterable>: hides the rows that do not contain the query.
 *
 * Blade: the toolbar carries x-data="tableFilter" data-count-label="filas", the field
 * <input type="search" x-model="query" x-on:input.debounce.150ms="filter"> and a
 * <span role="status" x-text="summary"> that announces the count.
 * Only for tables whose whole collection is in the DOM (never together with pagination). Each row is
 * matched on its data-search when the view gives one, otherwise on its text; accents and case are ignored.
 * The count follows the query last applied to the rows, not the one still waiting for the debounce.
 */
function normalize(text) {
    return text.normalize('NFD').replace(/\p{M}/gu, '').toLowerCase().replace(/\s+/g, ' ').trim();
}

export default function tableFilter() {
    return {
        query: '',
        applied: '',
        visible: 0,
        total: 0,
        rows: [],

        init() {
            const section = this.$root.closest('section');
            const rows = section ? section.querySelectorAll('tbody tr') : [];

            this.rows = Array.from(rows, (row) => ({
                row,
                text: normalize(row.dataset.search ?? row.textContent ?? ''),
            }));
            this.total = this.rows.length;
            this.visible = this.total;
        },

        /** «12 filas» without a query, «6 de 12 filas» with one. */
        get summary() {
            const label = this.$root.dataset.countLabel ?? 'filas';

            if (this.applied === '') {
                return `${this.total} ${label}`;
            }

            return `${this.visible} de ${this.total} ${label}`;
        },

        filter() {
            const needle = normalize(this.query);
            let visible = 0;

            this.rows.forEach(({ row, text }) => {
                const match = needle === '' || text.includes(needle);

                row.hidden = !match;

                if (match) {
                    visible += 1;
                }
            });

            this.applied = needle;
            this.visible = visible;
        },

        clear() {
            this.query = '';
            this.filter();
        },
    };
}
