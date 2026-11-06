/**
 * Component of <x-ui.notices>: feeds the session messages into the notices store, listens to the ui-notice
 * window event and exposes the store to the template.
 *
 * Blade: <div x-data="notices" data-messages="[...]" x-on:ui-notice.window="receive"> with the live regions on
 * x-text="statusText" and x-text="alertText" and the stack on x-for="item in items". The messages arrive
 * escaped inside data-messages, never inside a <script>. The store announces every notice it receives.
 *
 * The close button leaves with its notice, so dismiss() hands the focus to the next notice (or the previous one)
 * and, with none left, back to the control the focus came from when it entered the stack, or to <main>.
 */
export default function notices() {
    // Plain closure variables: a DOM node inside the reactive state would be proxied and could not take the focus
    let root = null;
    let origin = null;

    const remember = (event) => {
        if (event.relatedTarget instanceof HTMLElement && !root.contains(event.relatedTarget)) {
            origin = event.relatedTarget;
        }
    };

    return {
        init() {
            root = this.$root;
            root.addEventListener('focusin', remember);

            let messages;

            try {
                messages = JSON.parse(this.$root.dataset.messages || '[]');
            } catch {
                messages = [];
            }

            (Array.isArray(messages) ? messages : []).forEach((message) => this.add(message));
        },

        destroy() {
            root?.removeEventListener('focusin', remember);
        },

        /** x-on:ui-notice.window: $dispatch('ui-notice', { tone, text, timeout, href, action }) from any component */
        receive(event) {
            this.add(event.detail ?? {});
        },

        add({ tone, text, timeout, href, action } = {}) {
            if (typeof text !== 'string' || text.trim() === '') {
                return;
            }

            this.$store.notices.push({ tone, text, timeout, href, action });
        },

        dismiss(id) {
            const notice = document.activeElement?.closest('[role="group"]');
            const focused = Boolean(notice) && root.contains(notice);
            const stack = focused
                ? [...notice.parentElement.children].filter((child) =>
                      child.matches('[role="group"]'),
                  )
                : [];
            const neighbour = stack[stack.indexOf(notice) + 1] ?? stack[stack.indexOf(notice) - 1];

            this.$store.notices.dismiss(id);

            if (!focused) {
                return;
            }

            const back =
                origin?.isConnected && origin.checkVisibility?.() !== false ? origin : null;

            (
                neighbour?.querySelector('button') ??
                back ??
                document.getElementById('main')
            )?.focus();
        },

        /** x-on:mouseenter="pause(item.id, 'pointer')", x-on:focusin="pause(item.id, 'focus')" */
        pause(id, source) {
            this.$store.notices.pause(id, source);
        },

        resume(id, source) {
            this.$store.notices.resume(id, source);
        },

        get items() {
            return this.$store.notices.items;
        },

        get statusText() {
            return this.$store.notices.statusText;
        },

        get alertText() {
            return this.$store.notices.alertText;
        },
    };
}
