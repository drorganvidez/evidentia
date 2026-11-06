import { announce } from '../alpine/announce';

/**
 * Order of <x-ui.table sortable> in the browser, and the «Ordenar por» control of the phone cards of every sortable
 * table, which orders in the browser or follows the sort link of the server.
 *
 * Blade: the section carries x-data="tableSort" data-sort-mode="browser|server"; every sortable header holds
 * <button type="button" x-on:click="toggle" data-column="2"> inside a th whose aria-sort tells the order, and the
 * phone control is <select data-table-sort x-on:change="choose"> with "2:asc" values (browser) or URLs (server).
 * A cell orders by its data-sort-value (x-ui.table.cell :sort-value), else by the first data-sort-value inside it
 * (hours, states, people), else by the datetime of its first <time>, else by its visible text. Dates and numbers
 * compare as such, texts without case or accents and with their numbers in order; empty cells (or a lone dash) go last and
 * ties keep the order of the server. A column of dates starts with the newest. The rows only move, so the quick
 * filter of the toolbar keeps its matches. Its state lives in the closure: the select runs inside the scope of
 * the quick filter, where this.$root is the toolbar.
 */
const collator = new Intl.Collator('es', { numeric: true, sensitivity: 'base' });
const ISO_DATE =
    /^\d{4}-\d{2}-\d{2}(?:[T ]\d{2}:\d{2}(?::\d{2}(?:\.\d+)?)?(?:Z|[+-]\d{2}:?\d{2})?)?$/;
const NUMBER = /^-?\d+(?:\.\d+)?$/;
const HIDDEN = '.sr-only, [aria-hidden="true"], [data-filter-ignore]';
// The dash some cells print for a missing value counts as empty
const EMPTY = new Set(['', '—', '–', '-']);

function textOf(cell, skipHidden) {
    const walker = document.createTreeWalker(cell, NodeFilter.SHOW_TEXT);
    const parts = [];

    for (let node = walker.nextNode(); node; node = walker.nextNode()) {
        const hidden = node.parentElement?.closest(skipHidden ? HIDDEN : '[data-filter-ignore]');

        if (!hidden || !cell.contains(hidden)) {
            parts.push(node.textContent);
        }
    }

    return parts.join(' ').replace(/\s+/g, ' ').trim();
}

/** Value of a cell to order by: { value: number|string|null, date: boolean }. */
export function sortValue(cell) {
    if (!cell) {
        return { value: null, date: false };
    }

    const given =
        cell.dataset.sortValue ??
        cell.querySelector('[data-sort-value]')?.dataset.sortValue ??
        cell.querySelector('time[datetime]')?.getAttribute('datetime');
    const raw = (given ?? (textOf(cell, true) || textOf(cell, false))).trim();

    if (EMPTY.has(raw)) {
        return { value: null, date: false };
    }

    if (ISO_DATE.test(raw)) {
        const time = Date.parse(raw);

        if (!Number.isNaN(time)) {
            return { value: time, date: true };
        }
    }

    return { value: NUMBER.test(raw) ? Number(raw) : raw, date: false };
}

export function compareValues(a, b) {
    if (typeof a === 'number' && typeof b === 'number') {
        return a - b;
    }

    return collator.compare(String(a), String(b));
}

export default function tableSort() {
    let root = null;
    let body = null;
    const position = new Map();

    function headers() {
        return root ? Array.from(root.querySelectorAll('thead th')) : [];
    }

    function sortBy(index, asked) {
        const rows = Array.from(body.rows);
        const keyed = rows.map((row) => ({
            row,
            ...sortValue(row.cells[index]),
            position: position.get(row) ?? 0,
        }));
        const filled = keyed.filter((item) => item.value !== null);
        const header = headers()[index];
        const current = header?.getAttribute('aria-sort');
        const first = filled.length > 0 && filled.every((item) => item.date) ? 'desc' : 'asc';
        let dir = asked;

        if (dir === null) {
            dir = first;
            if (current === 'ascending') {
                dir = 'desc';
            } else if (current === 'descending') {
                dir = 'asc';
            }
        }

        keyed.sort((a, b) => {
            if (a.value === null || b.value === null) {
                if (a.value === b.value) {
                    return a.position - b.position;
                }

                return a.value === null ? 1 : -1;
            }

            const order = compareValues(a.value, b.value);

            if (order === 0) {
                return a.position - b.position;
            }

            return dir === 'desc' ? -order : order;
        });

        body.append(...keyed.map((item) => item.row));

        headers().forEach((th, column) => {
            if (column === index) {
                th.setAttribute('aria-sort', dir === 'asc' ? 'ascending' : 'descending');
            } else {
                th.removeAttribute('aria-sort');
            }
        });

        const select = root.querySelector('select[data-table-sort]');

        if (select) {
            select.value = `${index}:${dir}`;
        }

        return { dir, label: header?.dataset.label ?? '' };
    }

    return {
        init() {
            root = this.$root;
            body = root.querySelector('tbody');
            Array.from(body?.rows ?? []).forEach((row, index) => position.set(row, index));
        },

        /** Header button: the other direction, or the first one of the column when it was not the current order. */
        toggle(event) {
            const index = Number(event.currentTarget.dataset.column);

            if (!body || Number.isNaN(index)) {
                return;
            }

            const { dir, label } = sortBy(index, null);

            announce(
                this.$store.notices,
                `Ordenada por ${label}, ${dir === 'asc' ? 'ascendente' : 'descendente'}`,
            );
        },

        /** Phone control: a URL of the server, or a column and a direction of the browser. */
        choose(event) {
            const { value } = event.target;

            if (value === '') {
                return;
            }

            if (root?.dataset.sortMode === 'server') {
                window.location.assign(value);

                return;
            }

            const [column, dir] = value.split(':');

            if (body && (dir === 'asc' || dir === 'desc')) {
                sortBy(Number(column), dir);
            }
        },
    };
}
