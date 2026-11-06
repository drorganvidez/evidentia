import { afterRender } from '../alpine/afterRender';
import { freshText } from '../alpine/announce';

/**
 * Minutes editor (<x-ui.minutes-editor>): points with title, description, duration and agreements, reordered with
 * buttons and removed in two steps. The hidden input receives, through x-bind:value="json", the JSON the secretary
 * controller reads from points_json: [{id, title, duration, description, agreements: [{id, description}]}], keeping
 * the ids of what already exists and dropping the empty agreements.
 *
 * Blade: <div x-data="minutesEditor" data-points='[...]' data-min="1" data-id="field-points-json">. On init the
 * module listens to the submit of the surrounding form and cancels it when a title is missing. Every access to the
 * document lives here; the markup only calls methods with the x-for variables.
 *
 * The root element is kept from init(): in the CSP build $root is resolved from the element whose handler is
 * running, and that element may already be out of the document when a point has just been removed.
 */
const TITLE_REQUIRED = 'El título es obligatorio';

let counter = 0;

function nextKey(prefix) {
    counter += 1;

    return `${prefix}-${counter}`;
}

function toInt(value) {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const number = Number(value);

    return Number.isFinite(number) ? number : null;
}

function makeAgreement(raw = {}) {
    const source = typeof raw === 'string' ? { description: raw } : (raw ?? {});

    return {
        key: nextKey('agreement'),
        id: toInt(source.id),
        description: String(source.description ?? ''),
    };
}

function makePoint(raw = {}) {
    const source = raw ?? {};

    return {
        key: nextKey('point'),
        id: toInt(source.id),
        title: String(source.title ?? source.point ?? ''),
        description: String(source.description ?? ''),
        duration: toInt(source.duration),
        agreements: Array.isArray(source.agreements) ? source.agreements.map(makeAgreement) : [],
        errors: {},
        confirming: false,
    };
}

export default function minutesEditor() {
    let root = null;
    let form = null;
    let onSubmit = null;

    return {
        points: [],
        announcement: '',
        minPoints: 0,
        baseId: 'field-points-json',

        init() {
            root = this.$root;
            this.points = JSON.parse(root.dataset.points || '[]').map(makePoint);
            this.minPoints = Number.parseInt(root.dataset.min ?? '', 10) || 0;
            this.baseId = root.dataset.id || this.baseId;
            form = root.closest('form');

            if (form) {
                // Capture phase: on the form itself it runs before the x-on:submit of busyForm, which then
                // sees the submit already cancelled and does not mark the form busy
                onSubmit = (event) => this.validate(event);
                form.addEventListener('submit', onSubmit, true);
            }
        },

        destroy() {
            if (form && onSubmit) {
                form.removeEventListener('submit', onSubmit, true);
            }
        },

        /** Value of the hidden input: ids kept, texts trimmed, empty agreements dropped. */
        get json() {
            return JSON.stringify(
                this.points.map((point) => ({
                    id: point.id,
                    title: point.title.trim(),
                    duration: Number.isFinite(point.duration) ? point.duration : null,
                    description: point.description.trim(),
                    agreements: point.agreements
                        .filter((agreement) => agreement.description.trim() !== '')
                        .map((agreement) => ({
                            id: agreement.id,
                            description: agreement.description.trim(),
                        })),
                })),
            );
        },

        addPoint() {
            this.points.push(makePoint());

            const i = this.points.length - 1;

            this.announce(`Punto ${i + 1} añadido`);
            this.focusLater(() => this.focusField(i, 'title'));
        },

        /**
         * Two steps: the first click turns «Quitar» into «Confirmar», the second one removes the point. The
         * confirmation waits as long as needed (no time limit): Escape or leaving the button takes it back.
         */
        removePoint(i) {
            const point = this.points[i];

            if (!point) {
                return;
            }

            if (!point.confirming) {
                this.points.forEach((other) => {
                    other.confirming = false;
                });
                point.confirming = true;
                this.announce(`Pulsa Confirmar para quitar el punto ${i + 1}`);
                this.focusLater(() => this.focusAction('confirm', i));

                return;
            }

            this.points.splice(i, 1);
            this.announce(`Punto ${i + 1} quitado, quedan ${this.points.length}`);
            this.focusLater(() => {
                if (!this.focusAction('remove', i)) {
                    this.focusRootAction('add-point');
                }
            });
        },

        /**
         * Leaving «Confirmar» for another control brings «Quitar» back. Safari, and Firefox on macOS, do not focus
         * a clicked button: the mousedown hands the focus to the editor (or to nothing) before the click arrives,
         * so a blur towards an ancestor or towards nothing is not a decision and keeps the confirmation.
         */
        leaveConfirm(event, i) {
            const next = event.relatedTarget;

            if (!(next instanceof Element) || next.contains(event.target)) {
                return;
            }

            this.cancelRemove(i, false);
        },

        /** Escape on «Confirmar» brings «Quitar» back with the focus; leaving it brings it back where it was. */
        cancelRemove(i, refocus) {
            const point = this.points[i];

            if (!point || !point.confirming) {
                return;
            }

            point.confirming = false;

            if (refocus) {
                this.focusLater(() => this.focusAction('remove', i));
            }
        },

        movePoint(i, direction) {
            const to = i + direction;

            if (to < 0 || to >= this.points.length) {
                return;
            }

            const [point] = this.points.splice(i, 1);

            this.points.splice(to, 0, point);
            this.announce(`Punto ${i + 1} movido a la posición ${to + 1}`);
            this.focusLater(() => {
                // The block moved in the DOM and lost the focus: back to the same button, or its opposite at an end
                const kind = direction < 0 ? 'up' : 'down';

                if (!this.focusAction(kind, to)) {
                    this.focusAction(kind === 'up' ? 'down' : 'up', to);
                }
            });
        },

        addAgreement(i) {
            const point = this.points[i];

            if (!point) {
                return;
            }

            point.agreements.push(makeAgreement());

            const j = point.agreements.length - 1;

            this.announce(`Acuerdo ${j + 1} añadido al punto ${i + 1}`);
            this.focusLater(() => this.focusAgreement(i, j));
        },

        removeAgreement(i, j) {
            const point = this.points[i];

            if (!point || !point.agreements[j]) {
                return;
            }

            point.agreements.splice(j, 1);
            this.announce(`Acuerdo ${j + 1} del punto ${i + 1} quitado`);
            this.focusLater(() => {
                const next = this.pointNode(i)?.querySelectorAll(
                    '[data-action="remove-agreement"]',
                )[j];

                if (next) {
                    next.focus();
                } else {
                    this.pointNode(i)?.querySelector('[data-action="add-agreement"]')?.focus();
                }
            });
        },

        /** Submit handler of the surrounding form: every point needs a title and there may be a minimum. */
        validate(event) {
            let invalid = 0;
            let first = -1;

            this.points.forEach((point, i) => {
                if (point.title.trim() === '') {
                    point.errors = { title: TITLE_REQUIRED };
                    invalid += 1;
                    first = first === -1 ? i : first;
                } else {
                    point.errors = {};
                }
            });

            if (this.points.length < this.minPoints) {
                event.preventDefault();
                this.announce(
                    this.minPoints === 1
                        ? 'Añade al menos un punto'
                        : `Añade al menos ${this.minPoints} puntos`,
                );
                this.focusLater(() => this.focusRootAction('add-point'));

                return false;
            }

            if (invalid === 0) {
                return true;
            }

            event.preventDefault();
            this.announce(invalid === 1 ? 'Revisa 1 punto' : `Revisa ${invalid} puntos`);
            this.focusLater(() => this.focusField(first, 'title'));

            return false;
        },

        clearError(i, field) {
            const point = this.points[i];

            if (point && point.errors[field] && point[field].trim() !== '') {
                point.errors = {};
            }
        },

        pointLegend(i) {
            const title = this.points[i]?.title.trim();

            return title ? `Punto ${i + 1}, ${title}` : `Punto ${i + 1}`;
        },

        errorId(i, field) {
            return `${this.baseId}-${i}-${field}-error`;
        },

        describedBy(i, field) {
            return this.points[i]?.errors[field] ? this.errorId(i, field) : null;
        },

        agreementLabel(i, j) {
            return `Acuerdo ${j + 1} del punto ${i + 1}`;
        },

        agreementAction(i, j) {
            return `Quitar el acuerdo ${j + 1} del punto ${i + 1}`;
        },

        actionLabel(kind, i) {
            const n = i + 1;
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

        isFirst(i) {
            return i === 0;
        },

        isLast(i) {
            return i === this.points.length - 1;
        },

        /** A repeated text changes too (freshText), so the status region reads it again. */
        announce(text) {
            this.announcement = freshText(this.announcement, text);
        },

        /** Moves the focus once the DOM reflects the change and the shown elements are visible. */
        focusLater(callback) {
            this.$nextTick(() => afterRender(callback));
        },

        pointNode(i) {
            return root.querySelectorAll(':scope > fieldset')[i] ?? null;
        },

        action(kind, i) {
            return this.pointNode(i)?.querySelector(`[data-action="${kind}"]`) ?? null;
        },

        focusAction(kind, i) {
            const button = this.action(kind, i);

            if (!button || button.disabled) {
                return false;
            }

            button.focus();

            return true;
        },

        focusRootAction(kind) {
            root.querySelector(`:scope > [data-action="${kind}"]`)?.focus();
        },

        focusField(i, field) {
            this.pointNode(i)?.querySelector(`[data-field="${field}"]`)?.focus();
        },

        focusAgreement(i, j) {
            this.pointNode(i)?.querySelectorAll('[data-field="agreement"]')[j]?.focus();
        },
    };
}
