/**
 * Notices shared by the whole page (Alpine store): the stack of visible notices and the two live regions.
 *
 * - push({ tone, text, timeout }) adds a notice and announces it, rejected and pending as an alert and the rest
 *   as a status (the visible stack is not live); accepted and notice close themselves after 8 s, rejected and
 *   pending stay until the person closes them (WCAG 2.2.1), unless a timeout is given
 * - pause(id, source) and resume(id, source) hold the countdown while the pointer or the focus is on the notice;
 *   each source counts apart, so the pointer leaving does not restart a notice that still has the focus
 * - announce(text, 'status' | 'alert') writes to the hidden live region and clears it 5 s later, so the
 *   same text can be announced again; on its own it is for messages without a visible notice
 *
 * From JS: Alpine.store('notices').push(...) or this.$store.notices.announce('Copiado'); from Blade,
 * $dispatch('ui-notice', { tone: 'accepted', text: 'Guardado' }) reaches the <x-ui.notices> component.
 */
const DEFAULT_TIMEOUT = 8000;
const ANNOUNCE_CLEAR = 5000;
const PERSISTENT = ['rejected', 'pending'];
const TONES = ['accepted', 'rejected', 'pending', 'notice'];

// Timer handles stay out of the reactive store
const timers = new Map();
const announceTimers = { statusText: null, alertText: null };

export default {
    items: [],
    statusText: '',
    alertText: '',
    nextId: 1,

    push({ tone = 'notice', text = '', timeout } = {}) {
        if (typeof text !== 'string' || text.trim() === '') {
            return null;
        }

        const id = this.nextId++;
        const safeTone = TONES.includes(tone) ? tone : 'notice';
        const delay =
            timeout === undefined
                ? PERSISTENT.includes(safeTone)
                    ? null
                    : DEFAULT_TIMEOUT
                : timeout;

        this.items.push({ id, tone: safeTone, text });
        this.announce(text, PERSISTENT.includes(safeTone) ? 'alert' : 'status');

        if (delay) {
            this.schedule(id, delay);
        }

        return id;
    },

    dismiss(id) {
        const timer = timers.get(id);

        if (timer) {
            clearTimeout(timer.handle);
            timers.delete(id);
        }

        this.items = this.items.filter((item) => item.id !== id);
    },

    pause(id, source = 'pointer') {
        const timer = timers.get(id);

        if (!timer) {
            return;
        }

        timer.holds.add(source);

        if (timer.handle === null) {
            return;
        }

        clearTimeout(timer.handle);
        timer.handle = null;
        timer.remaining = Math.max(1000, timer.due - Date.now());
    },

    resume(id, source = 'pointer') {
        const timer = timers.get(id);

        if (!timer) {
            return;
        }

        timer.holds.delete(source);

        if (timer.holds.size > 0 || timer.handle !== null) {
            return;
        }

        this.schedule(id, timer.remaining);
    },

    schedule(id, delay) {
        timers.set(id, {
            handle: setTimeout(() => this.dismiss(id), delay),
            due: Date.now() + delay,
            remaining: delay,
            holds: timers.get(id)?.holds ?? new Set(),
        });
    },

    announce(text, level = 'status') {
        const key = level === 'alert' ? 'alertText' : 'statusText';

        clearTimeout(announceTimers[key]);
        this[key] = text;
        announceTimers[key] = setTimeout(() => {
            this[key] = '';
        }, ANNOUNCE_CLEAR);
    },
};
