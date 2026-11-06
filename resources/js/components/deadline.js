/**
 * Time left of <x-ui.deadline>: refreshes the badge every minute, turns urgent under the warning days and
 * announces the closing once through the notices store.
 *
 * Blade: <div x-data="deadline" data-closes-at="2026-11-19T20:00:00+01:00" data-warn-days="3"> with the badge
 * bound by x-text="remaining" and x-bind:data-tone="tone". The server prints the same texts first, so the
 * widget reads right without JS; the rounding here mirrors the Blade one (days, then hours, then minutes).
 */
const MINUTE = 60_000;
const DAY = 86_400;

const TEXTS = {
    none: 'Sin plazo',
    closed: 'Plazo cerrado',
};

function plural(n, singular, pluralForm) {
    return `${n} ${n === 1 ? singular : pluralForm}`;
}

export function remainingText(seconds) {
    if (seconds >= DAY) {
        return plural(Math.round(seconds / DAY), 'día', 'días');
    }

    if (seconds >= 3600) {
        return plural(Math.round(seconds / 3600), 'hora', 'horas');
    }

    return plural(Math.max(1, Math.round(seconds / 60)), 'minuto', 'minutos');
}

export default function deadline() {
    return {
        remaining: '',
        tone: 'neutral',
        closed: false,
        closesAt: null,
        warnDays: 3,
        timer: null,
        interval: null,

        init() {
            const { closesAt, warnDays } = this.$root.dataset;

            this.closesAt = closesAt ? Date.parse(closesAt) : null;
            this.warnDays = Number(warnDays ?? 3);
            this.update(false);

            if (this.closesAt === null || this.closed) {
                return;
            }

            // Aligned to the minute: never a per-second refresh
            this.timer = setTimeout(
                () => {
                    this.update(true);
                    this.interval = setInterval(() => this.update(true), MINUTE);
                },
                MINUTE - (Date.now() % MINUTE),
            );
        },

        update(announce) {
            if (this.closesAt === null) {
                this.remaining = TEXTS.none;
                this.tone = 'neutral';

                return;
            }

            const seconds = Math.floor((this.closesAt - Date.now()) / 1000);

            if (seconds <= 0) {
                this.remaining = TEXTS.closed;
                this.tone = 'draft';

                if (!this.closed) {
                    this.closed = true;
                    this.stop();

                    if (announce) {
                        this.announce();
                    }
                }

                return;
            }

            this.remaining = remainingText(seconds);
            this.tone = seconds < this.warnDays * DAY ? 'rejected' : 'pending';
        },

        get closesVerb() {
            return this.closed ? 'Cerró el' : 'Cierra el';
        },

        get closesVerbLower() {
            return this.closed ? 'cerró el' : 'cierra el';
        },

        /** Through the notices store when the page registers it; silent otherwise. */
        announce() {
            const notices = this.$store.notices;

            if (notices && typeof notices.announce === 'function') {
                notices.announce(TEXTS.closed);
            }
        },

        stop() {
            clearTimeout(this.timer);
            clearInterval(this.interval);
        },

        destroy() {
            this.stop();
        },
    };
}
