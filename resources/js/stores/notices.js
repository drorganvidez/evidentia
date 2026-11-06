/**
 * Notices shared by the whole page (Alpine store): the stack of visible notices, its queue and the two live regions.
 *
 * - push({ tone, text, timeout, href, action }) adds a notice; accepted and notice close themselves after 8 s and
 *   show the time left as a bar, rejected and pending stay until the person closes them (WCAG 2.2.1), and so does
 *   any notice with a link (href and its label action), because a timed action can be lost; a timeout overrides
 * - at most three notices are visible at once; the rest wait in the queue and come in, announced and with their
 *   own countdown, as the visible ones close
 * - pause(id, source) and resume(id, source) hold the countdown (and its bar) while the pointer or the focus is
 *   on the notice; each source counts apart, so the pointer leaving does not restart a notice that has the focus
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
const VISIBLE = 3;

/** Only links that stay inside the app: a relative path or an http(s) URL of this origin. */
function safeHref(href) {
    if (typeof href !== 'string' || href.trim() === '') {
        return null;
    }

    try {
        const url = new URL(href, window.location.origin);

        return ['http:', 'https:'].includes(url.protocol) && url.origin === window.location.origin
            ? url.href
            : null;
    } catch {
        return null;
    }
}

// Timer handles stay out of the reactive store
const timers = new Map();
const announceTimers = { statusText: null, alertText: null };

export default {
    items: [],
    statusText: '',
    alertText: '',
    nextId: 1,

    queue: [],

    push({ tone = 'notice', text = '', timeout, href, action } = {}) {
        if (typeof text !== 'string' || text.trim() === '') {
            return null;
        }

        const safeTone = TONES.includes(tone) ? tone : 'notice';
        const link = safeHref(href);
        const label = typeof action === 'string' && action.trim() !== '' ? action : 'Ver';
        const delay =
            timeout === undefined
                ? PERSISTENT.includes(safeTone) || link
                    ? null
                    : DEFAULT_TIMEOUT
                : timeout;
        const notice = {
            id: this.nextId++,
            tone: safeTone,
            text,
            href: link,
            action: link ? label : '',
            delay,
            // The bar shows the default countdown only; a custom timeout closes without it
            timed: delay === DEFAULT_TIMEOUT,
            paused: false,
        };

        if (this.items.length < VISIBLE) {
            this.show(notice);
        } else {
            this.queue.push(notice);
        }

        return notice.id;
    },

    show(notice) {
        this.items.push(notice);
        this.announce(notice.text, PERSISTENT.includes(notice.tone) ? 'alert' : 'status');

        if (notice.delay) {
            this.schedule(notice.id, notice.delay);
        }
    },

    dismiss(id) {
        const timer = timers.get(id);

        if (timer) {
            clearTimeout(timer.handle);
            timers.delete(id);
        }

        this.items = this.items.filter((item) => item.id !== id);
        this.queue = this.queue.filter((item) => item.id !== id);

        if (this.items.length < VISIBLE && this.queue.length > 0) {
            this.show(this.queue.shift());
        }
    },

    setPaused(id, paused) {
        const item = this.items.find((candidate) => candidate.id === id);

        if (item) {
            item.paused = paused;
        }
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
        this.setPaused(id, true);
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
        this.setPaused(id, false);
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
