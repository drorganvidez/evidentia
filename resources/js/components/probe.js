/**
 * Probe of the Alpine CSP grammar the component library relies on (section "Sonda CSP" of /_ui).
 * Every kind of attribute expression the components use is exercised there, so a parser limitation
 * shows up in the browser console before a component depends on it.
 */
export default function probe() {
    return {
        items: [
            {
                key: 'k-a',
                title: 'Uno',
                duration: 10,
                children: [
                    { key: 'c-1', title: 'Hijo 1' },
                    { key: 'c-2', title: 'Hijo 2' },
                ],
            },
            { key: 'k-b', title: 'Dos', duration: 20, children: [] },
        ],
        open: false,
        ok: true,
        count: 0,
        query: '',
        filtered: 0,
        last: '',
        mode: 'light',

        get expandedAttr() {
            return this.open ? 'true' : 'false';
        },

        /** Object for x-bind:style: Alpine applies it with setProperty, which the policy allows. */
        get barStyle() {
            return { width: `${Math.min(this.count * 10, 100)}%` };
        },

        get summary() {
            return this.items
                .map(
                    (item) =>
                        `${item.title}:${item.duration}:${item.children.map((child) => child.title).join('|')}`,
                )
                .join(' / ');
        },

        toggle() {
            this.open = !this.open;
        },

        close() {
            this.open = false;
        },

        increment() {
            this.count += 1;
        },

        add() {
            const n = this.items.length + 1;

            this.items.push({
                key: `k-${n}-${this.count}`,
                title: `Nuevo ${n}`,
                duration: 0,
                children: [],
            });
        },

        remove(index) {
            this.items.splice(index, 1);
        },

        removeChild(index, j) {
            this.items[index].children.splice(j, 1);
        },

        filter() {
            this.filtered = this.query.trim().length;
        },

        onKey(event) {
            if (event.key === 'k' && !event.target.matches('input, textarea, select')) {
                this.last = 'tecla k';
            }
        },

        receive(event) {
            this.last = event.detail.text;
        },

        is(mode) {
            return this.mode === mode;
        },

        select(mode) {
            this.mode = mode;
        },
    };
}
