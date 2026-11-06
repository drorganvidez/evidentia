import { afterRender } from '../alpine/afterRender';

/**
 * Agenda editor (<x-ui.agenda-editor>): the points of a meeting request as a list of inputs that are added,
 * reordered with buttons or Alt+arrows and removed in two steps. The hidden input receives the JSON array of
 * strings through x-bind:value="json".
 *
 * Blade: <fieldset x-data="agendaEditor" data-points='["Lectura del acta"]' data-max="50">. Every access to the
 * document lives here; the markup only calls methods with the x-for variables and $event.
 *
 * The root element is kept from init(): in the CSP build $root and $refs are resolved from the element whose
 * handler is running, and that element may already be out of the document when a row has just been removed.
 */
let counter = 0;

function makePoint(text) {
    counter += 1;

    return { key: `point-${counter}`, text, confirming: false };
}

export default function agendaEditor() {
    let root = null;

    return {
        points: [],
        draft: '',
        announcement: '',
        max: 50,

        init() {
            root = this.$root;

            const raw = JSON.parse(root.dataset.points || '[]');

            this.points = raw.filter((text) => typeof text === 'string').map(makePoint);
            this.max = Number.parseInt(root.dataset.max ?? '', 10) || 50;
        },

        /** Value of the hidden input: the trimmed, non-empty texts in their order. */
        get json() {
            return JSON.stringify(
                this.points.map((point) => point.text.trim()).filter((text) => text !== ''),
            );
        },

        add() {
            const text = this.draft.trim();

            if (text === '') {
                this.focusDraft();

                return;
            }

            if (this.isFull()) {
                return;
            }

            this.points.push(makePoint(text));
            this.draft = '';

            const index = this.points.length - 1;

            this.announce(`Punto ${index + 1} añadido`);
            this.focusLater(() => this.focusInput(index));
        },

        /** Enter inside a point opens an empty one right below it. */
        insertAfter(index) {
            if (this.isFull()) {
                return;
            }

            this.points.splice(index + 1, 0, makePoint(''));
            this.announce(`Punto ${index + 2} añadido`);
            this.focusLater(() => this.focusInput(index + 1));
        },

        isFull() {
            if (this.points.length < this.max) {
                return false;
            }

            this.announce(`No caben más puntos, el máximo es ${this.max}`);

            return true;
        },

        /**
         * Two steps: the first click turns «Quitar» into «Confirmar», the second one removes the point. The
         * confirmation waits as long as needed (no time limit): Escape or leaving the button takes it back.
         */
        remove(index) {
            const point = this.points[index];

            if (!point) {
                return;
            }

            if (!point.confirming) {
                this.points.forEach((other) => {
                    other.confirming = false;
                });
                point.confirming = true;
                this.announce(`Pulsa Confirmar para quitar el punto ${index + 1}`);
                this.focusLater(() => this.focusAction('confirm', index));

                return;
            }

            this.points.splice(index, 1);
            this.announce(`Punto ${index + 1} quitado, quedan ${this.points.length}`);
            this.focusLater(() => {
                if (!this.focusAction('remove', index)) {
                    this.focusDraft();
                }
            });
        },

        /** Escape on «Confirmar» brings «Quitar» back with the focus; leaving it brings it back where it was. */
        cancelRemove(index, refocus) {
            const point = this.points[index];

            if (!point || !point.confirming) {
                return;
            }

            point.confirming = false;

            if (refocus) {
                this.focusLater(() => this.focusAction('remove', index));
            }
        },

        moveUp(index) {
            this.move(index, index - 1, 'button');
        },

        moveDown(index) {
            this.move(index, index + 1, 'button');
        },

        move(from, to, focus) {
            if (to < 0 || to >= this.points.length || from === to) {
                return;
            }

            const [point] = this.points.splice(from, 1);

            this.points.splice(to, 0, point);
            this.announce(`Punto ${from + 1} movido a la posición ${to + 1}`);
            this.focusLater(() => {
                if (focus === 'input') {
                    this.focusInput(to);

                    return;
                }

                // The row moved in the DOM and lost the focus: back to the same button, or its opposite at an end
                const kind = to < from ? 'up' : 'down';

                if (!this.focusAction(kind, to)) {
                    this.focusAction(kind === 'up' ? 'down' : 'up', to);
                }
            });
        },

        /** Alt+arrows move the point, Enter adds one below, Backspace on an empty point removes it. */
        onItemKeydown(event, index) {
            if (event.altKey && (event.key === 'ArrowUp' || event.key === 'ArrowDown')) {
                event.preventDefault();
                this.move(index, event.key === 'ArrowUp' ? index - 1 : index + 1, 'input');

                return;
            }

            if (event.key === 'Enter') {
                event.preventDefault();
                this.insertAfter(index);

                return;
            }

            if (event.key === 'Backspace' && this.points[index].text === '') {
                event.preventDefault();
                this.points.splice(index, 1);
                this.announce(`Punto ${index + 1} quitado, quedan ${this.points.length}`);
                this.focusLater(() => {
                    if (this.points.length === 0) {
                        this.focusDraft();

                        return;
                    }

                    this.focusInput(Math.max(0, index - 1));
                });
            }
        },

        pointLabel(index) {
            return `Punto ${index + 1}`;
        },

        actionLabel(kind, index) {
            const n = index + 1;
            const total = this.points.length;

            switch (kind) {
                case 'up':
                    return `Subir el punto ${n} de ${total}`;
                case 'down':
                    return `Bajar el punto ${n} de ${total}`;
                case 'confirm':
                    return `Confirmar que quitas el punto ${n}`;
                default:
                    return `Quitar el punto ${n} de ${total}`;
            }
        },

        isFirst(index) {
            return index === 0;
        },

        isLast(index) {
            return index === this.points.length - 1;
        },

        /** Clears the status region first so the same text is announced again. */
        announce(text) {
            this.announcement = '';
            this.$nextTick(() => {
                this.announcement = text;
            });
        },

        /** Moves the focus once the DOM reflects the change and the shown elements are visible. */
        focusLater(callback) {
            this.$nextTick(() => afterRender(callback));
        },

        rows() {
            return root.querySelectorAll(':scope > ol > li');
        },

        action(kind, index) {
            return this.rows()[index]?.querySelector(`[data-action="${kind}"]`) ?? null;
        },

        focusAction(kind, index) {
            const button = this.action(kind, index);

            if (!button || button.disabled) {
                return false;
            }

            button.focus();

            return true;
        },

        focusInput(index) {
            this.rows()[index]?.querySelector('input')?.focus();
        },

        focusDraft() {
            root.querySelector('[data-field="draft"]')?.focus();
        },
    };
}
