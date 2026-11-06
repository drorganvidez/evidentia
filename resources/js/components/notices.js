/**
 * Component of <x-ui.notices>: feeds the session messages into the notices store, listens to the ui-notice
 * window event and exposes the store to the template.
 *
 * Blade: <div x-data="notices" data-messages="[...]" x-on:ui-notice.window="receive"> with the live regions on
 * x-text="statusText" and x-text="alertText" and the stack on x-for="item in items". The messages arrive
 * escaped inside data-messages, never inside a <script>. The store announces every notice it receives.
 */
export default function notices() {
    return {
        init() {
            let messages;

            try {
                messages = JSON.parse(this.$root.dataset.messages || '[]');
            } catch {
                messages = [];
            }

            (Array.isArray(messages) ? messages : []).forEach((message) => this.add(message));
        },

        /** x-on:ui-notice.window: $dispatch('ui-notice', { tone, text, timeout }) from any component */
        receive(event) {
            this.add(event.detail ?? {});
        },

        add({ tone, text, timeout } = {}) {
            if (typeof text !== 'string' || text.trim() === '') {
                return;
            }

            this.$store.notices.push({ tone, text, timeout });
        },

        dismiss(id) {
            this.$store.notices.dismiss(id);
        },

        pause(id) {
            this.$store.notices.pause(id);
        },

        resume(id) {
            this.$store.notices.resume(id);
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
